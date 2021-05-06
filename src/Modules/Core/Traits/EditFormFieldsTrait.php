<?php

namespace RefinedDigital\CMS\Modules\Core\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait EditFormFieldsTrait
{
    /**
     * Boot the is page trait for a model.
     *
     * @return void
     */
    public static function bootEditFormFieldsTrait()
    {
    }


    public function getFormFields()
    {
        if (method_exists($this, 'setFormFields')) {
            $fields = $this->setFormFields();
        } else {
            $fields = $this->formFields;
        }

        if (isset($this->isPage) && $this->isPage) {
            $fields[] = [
                'name' => 'Meta Data',
                'fields' => [
                    [
                        [ 'label' => 'URL', 'name' => 'meta[uri]', 'type' => 'url'],
                    ],
                    [
                        [ 'label' => 'Page Title', 'name' => 'meta[title]', 'type' => 'Title', 'note' => 'This area appears in the title of the browser<br/>A maximum of 70 characters is allowed<br/><img src="'.asset('vendor/refined/core/img/ui/meta-title.png').'"/>'],
                        [ 'label' => 'Meta Description', 'name' => 'meta[description]', 'type' => 'textarea', 'note' => 'This area is used to describe the business to search engines<br>A maximum of <code>160</code> characters is allowed' ],
                    ]
                ]
            ];
        }

        // check if we have a module config
        $module = strtolower(class_basename($this));
        $config = config($module);
        if (!$config) {
            $config = config(str_plural($module));
        }
        $fields = $this->fieldsMergeConfig($fields, $config);

        // check if there are any extra fields
        $fields = $this->fieldsAddExtra($fields, $config);

        $newFields = [];
        foreach ($fields as $field) {
            $newFields[] = $field;
        }

        return json_decode(json_encode($newFields));
    }

    public function fieldsMergeConfig($fields, $config)
    {
        if (isset($config['fields'])) {
            $configFields = $config['fields'];
            if (is_array($configFields) && sizeof($configFields)) {
                foreach ($configFields as $name => $data) {
                    $key = $this->findFieldKey($fields, $name);
                    $field =  array_get($fields, $key);
                    if ($field) {
                        $newField = array_merge($field, $data);
                        array_set($fields, $key, $newField);
                    }

                }
            }
        }

        return $fields;
    }

    public function findFieldKey($fields, $name)
    {
        $fieldKeys = $this->array_find_deep($fields, $name);
        array_pop($fieldKeys);
        $key = implode('.', $fieldKeys);

        return $key;
    }

    public function fieldsAddExtra($defaultFields, $config)
    {
        if (isset($config['extra_fields']) && is_array($config['extra_fields'])) {
            $tabs = [];
            $sections = [];
            $extraFields = [];

            foreach ($config['extra_fields'] as $field) {
                // update the field names
                $field['name'] = 'data__'.$field['name'];
                if (isset($field['section'])) {

                    if ($field['section']['type'] == 'tab') {
                        $tabs[] = [
                            'name' => $field['section']['name'],
                            'fields' => $field['section']['fields']
                        ];
                    } else {

                        $f = [
                            'name' => $field['section']['name'] ?: 'Not Set',
                            'fields' => $field,
                        ];
                        unset($f['fields']['section']);

                        if (isset($field['insertBefore'])) {
                            $f['insertBefore'] = $field['insertBefore'];
                            unset($f['fields']['insertBefore']);
                        }
                        if (isset($field['insertAfter'])) {
                            $f['insertAfter'] = $f['fields']['insertAfter'];
                            unset($f['fields']['insertAfter']);
                        }

                        if ($field['section']['type'] == 'section') {
                            $f['fields'] = [[ $f['fields'] ]];
                            $sections[] = $f;
                        }

                    }
                } else {
                    $extraFields[] = $field;
                }
            }

            $defaultFields = $this->insertTabs($defaultFields, $tabs);
            $defaultFields = $this->insertToFields($defaultFields, $sections, '(blocks\.[\d]\.name)');
            $defaultFields = $this->insertToFields($defaultFields, $extraFields, '(fields\.[\d]\.[\d]\.name)');
        }

        return $defaultFields;
    }

    private function insertTabs($fields, $tabs)
    {
        // tabs is always the first level
        if (is_array($tabs) && sizeof($tabs)) {
            foreach ($tabs as $tab) {
                if (isset($tab['insertBefore'])) {
                    $type = 'Before';
                }
                if (isset($tab['insertAfter'])) {
                    $type = 'After';
                }

                // find the location in the field tabs
                if (isset($type)) {
                    foreach ($fields as $key => $field) {
                        if (isset($field['name']) && strtolower($field['name']) == strtolower($tab['insert'.$type])) {
                            if ($type == 'Before') {
                                $position = $key - 1;
                                if ($position == -1) {
                                    array_prepend($fields, $tab);
                                } else {
                                    array_splice($fields, $position + 1, 0, [$tab]);
                                }
                            }

                            if ($type == 'After') {
                                $position = $key + 1;
                                if ($position > sizeof($fields) - 1) {
                                    array_push($fields, $tab);
                                } else {
                                    array_splice($fields, $position, 0, [$tab]);
                                }
                            }
                        }
                    }
                } else {
                    // add it to the end of the array
                    array_push($fields, $tab);
                }
            }
        }

        return $fields;
    }

    private function insertToFields($fields, $data, $regex)
    {

        if (sizeof($data)) {
            $dots = array_dot($fields);
            if (sizeof($dots)) {
                foreach ($data as $replace) {
                    $bit = false;
                    if (isset($replace['insertBefore'])) {
                        $type = 'Before';
                    }
                    if (isset($replace['insertAfter'])) {
                        $type = 'After';
                    }

                    if (isset($type)) {
                        foreach ($dots as $path => $value) {
                            if (isset($replace['insert'.$type]) && preg_match($regex, $path) && strtolower($replace['insert'.$type]) == strtolower($value)) {
                                $bit = $path;
                                break;
                            }
                        }

                        // insert the section into the given parent
                        if ($bit) {
                            $fields = $this->replaceChild($fields, $bit, $type, $replace);
                        }

                    } else {
                        $fields = $this->addToFields($fields, $replace);
                    }
                }
            }
        }

        return $fields;

    }

    private function replaceChild($fields, $bit, $type, $replace)
    {
        // go back up two steps in the array
        $bit = explode('.', $bit);
        array_pop($bit);array_pop($bit);
        $bit = implode('.', $bit);

        // grab the data for the given child
        $data = array_get($fields, $bit);

        // recreate the child array
        $newBlocks = [];
        if (is_array($data) && sizeof($data)) {
            foreach ($data as $d) {
                if ($type == 'Before' && strtolower($d['name']) == strtolower($replace['insert'.$type])) {
                    $newBlocks[] = $replace;
                }

                $newBlocks[] = $d;

                if ($type == 'After' && strtolower($d['name']) == strtolower($replace['insert'.$type])) {
                    $newBlocks[] = $replace;
                }
            }
        }

        // throw the child back into the array
        if (sizeof($newBlocks)) {
            array_set($fields, $bit, $newBlocks);
        }

        return $fields;
    }

    private function addToFields($fields, $replace)
    {
        $dots = array_dot($fields);

        $haveSections = false;
        // check if there are any sections
        foreach ($dots as $path => $value) {
            if (preg_match('(sections)', $path)) {
                $haveSections = $path;
                break;
            }
        }

        if ($haveSections) {
            // go back up two steps in the array
            $haveSections = explode('.', $haveSections);
            array_pop($haveSections);array_pop($haveSections);
            $haveSections = implode('.', $haveSections);

            // grab the data for the given child
            $data = array_get($fields, $haveSections);
            $data[] = $replace;
            array_set($fields, $haveSections, $data);
        } else {
            // todo: check on a section that doesn't have sections
        }

        return $fields;
    }

    private function array_find_deep($array, $search, $keys = array())
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $sub = $this->array_find_deep($value, $search, array_merge($keys, array($key)));
                if (count($sub)) {
                    return $sub;
                }
            } else if ($value === $search) {
                return array_merge($keys, array($key));
            }
        }

        return array();
    }

    public function findImageFields() {
        $fields = [];
        if ($this->formFields) {
            $dots = array_dot($this->formFields);
            if (is_array($dots) && sizeof($dots)) {
                $foundImageKeys = [];
                // find all the image types
                foreach ($dots as $key => $value) {
                    if ($value === 'image') {
                        $foundImageKeys[] = $key;
                    }
                }

                // now find the field names
                if (sizeof($foundImageKeys)) {
                    foreach ($foundImageKeys as $key) {
                        $keyToFind = str_replace('.type', '.name', $key);
                        if (isset($dots[$keyToFind])) {
                            $fields[] = $dots[$keyToFind];
                        }
                    }
                }
            }
        }

        return $fields;
    }

}

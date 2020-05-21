<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Repositories;

use Carbon\Carbon;
use RefinedDigital\CMS\Modules\Core\Mail\Notification;
use RefinedDigital\CMS\Modules\Core\Models\EmailSubmission;
use Mail;

class EmailRepository extends CoreRepository {

    protected $tableOpen = '<table rules="all" style="border:1px solid #bbbbbb;" cellpadding="10">';
    protected $tableClose = '</table>';

    protected $thOpen = '<th align="left" valign="top" style="border-bottom:1px solid #bbbbbb; border-right:1px solid #bbbbbb; background: #dedede; padding:10px;font-family:arial;width: 300px;color: #111;">';
    protected $thClose = '</th>';

    protected $tdOpen = '<td valign="top" style="border-bottom:1px solid #bbbbbb; padding:10px;font-family:arial;">';
    protected $tdClose = '</td>';

    public function send($settings)
    {
        $email = new Notification($settings);
        Mail::to($settings->to)->send($email);

        $submissionData = [
            'to'    => $settings->to,
            'from'  => config('mail.from.address'),
            'ip'    => help()->getClientIP(),
            'form_id' => isset($settings->form_id) ? $settings->form_id : null,
            'data'  => $settings
        ];

        $item = EmailSubmission::create($submissionData);

        $userId = auth()->check() ? auth()->user()->id : null;

        activity()
            ->performedOn($item)
            ->causedBy($userId)
            ->withProperties(['Email has been sent'])
            ->log('Email has been sent')
        ;
    }

    private function generateFieldHtml($data, $fullWidth = false)
    {
        $fields = '';
        // add the field data, if any
        if (sizeof($data)) {
            $fields = $this->tableOpen;
            if ($fullWidth) {
                $fields = str_replace('style="', 'style="width:100%;', $fields);
            }
            foreach ($data as $field) {
                $fields .= '<tr>';
                    $fields .= $this->thOpen.$field->name.$this->thClose;
                    $fields .= $this->tdOpen.$field->value.$this->tdClose;
                $fields .= '</tr>';
            }
            $fields .= $this->tableClose;
        }

        return $fields;
    }

    public function generateHtml($request, $form, $fullWidth = false)
    {
        $data = $this->formatFields($request, $form);
        return $this->generateFieldHtml($data, $fullWidth);
    }

    public function generateHtmlViaRequest($request, $form, $fullWidth = false)
    {
        $data = [];
        if ($form->fields->count()) {
            foreach ($form->fields as $field) {
                if (isset($request[$field->field_name])) {
                    $val = $this->formatField($request, $field);
                    $value = new \stdClass();
                    $value->name = $field->name;
                    $value->value = $val;
                    $data[] = $value;
                }
            }
        }

        return $this->generateFieldHtml($data, $fullWidth);

    }

    public function makeHtml($request, $form, $type = 'message')
    {
        $html = '';
        $fields = $this->generateHtml($request, $form);

        // the form builder message
        if (isset($form->{$type}) && $form->{$type}) {
            $html = $form->{$type};
        }

        // replace the keys with the field data
        $search = ['[[fields]]', '{{fields}}'];
        $replace = [$fields, $fields];

        $html = str_replace($search, $replace, $html);

        return $html;
    }


    public function settingsFromForm($form, $request = false)
    {
        $settings = new \stdClass();
        $settings->to = $form->email_to;
        $settings->subject = $form->subject;

        if ($form->reply_to) {
            $settings->reply_to = $this->getReplyTo($form, $request);
        }
        if ($form->cc) {
            $settings->cc = $form->cc;
        }
        if ($form->bcc) {
            $settings->bcc = $form->bcc;
        }

        if ($request && !is_array($request) && $request->allFiles()) {
            $allFiles = $request->allFiles();
            $formFiles = [];
            $data = $request->all();
            if (sizeof($allFiles)) {
                foreach ($allFiles as $key => $file) {
                    if (isset($data[$key])) {
                        if (is_array($file)) {
                            foreach ($file as $f) {
                                $formFiles[] = $f;
                            }
                        } else {
                            $formFiles[] = $file;
                        }
                    }
                }
            }

            if (sizeof($formFiles)) {
                $settings->files = $formFiles;
            }
        }

        return $settings;
    }


    public function formatFields($request, $form)
    {
        $data = [];
        $dontAdd = [10, 11, 19];
        if ($form->fields && $form->fields->count()) {
            foreach ($form->fields as $field) {
                if (!in_array($field->form_field_type_id, $dontAdd)) {
                    $fieldData = new \stdClass();
                    $fieldData->name = $field->name;
                    $fieldData->value = $this->formatField($request, $field);
                    $data[$field->id] = $fieldData;
                }
            }
        }

        return $data;
    }

    private function formatField($request, $field)
    {
        // convert request to an easy to use array
        if (!is_array($request)) {
            $request = $request->all();
        }

        $fieldName = $field->field_name;
        $data = isset($request[$fieldName]) ? $request[$fieldName] : null;

        // if there are files, we need the names
        if (request()->hasFile($fieldName)) {
            $uploadedFiles = request()->file($fieldName);
            if (is_array($uploadedFiles) && sizeof($uploadedFiles)) {
                $files = [];
                foreach ($uploadedFiles as $file) {
                    $files[] = $file->getClientOriginalName();
                }

                $data = 'Attached: '.implode('<br/>', $files);
            } else {
                $data = 'Attached: '.$uploadedFiles->getClientOriginalName();
            }
        }

        // do some validation on some fields
        switch ($field->form_field_type_id) {
            case 2: // textarea
                $data = nl2br($data);
                break;
            case 3: // select
            case 4: // radio
            case 5: // checkbox
                $data = $this->getOptionName($field, $data);
                break;
            case 6: // single checkbox
            case 13: // yes no select
                $data = $data ? 'Yes' : 'No';
                break;
            case 14: // country select
                $data = $this->getCountryName($data);
                break;
            case 15: // date
                if ($data) {
                    $data = Carbon::createFromFormat('Y-m-d', $data)->format(config('form-builder.date_format'));
                }
                break;
            case 16: // datetime
                if ($data) {
                    $data = Carbon::createFromFormat('Y-m-d\TH:i', $data)->format(config('form-builder.datetime_format'));
                }
                break;
        }

        if ($field->form_field_type_id == 20) {
            $class = forms()->getFieldClass($field);
            $data = $class->formatData($field, $request);
        }

        // if we have an array, make it a string
        if (is_array($data)) {
            $d = [];
            foreach ($data as $dd) {
                if (is_object($dd)) {
                    $d[] = implode(', ', (array) $dd);
                } else {
                    $d[] = $dd;
                }
            }
            $data = implode(', ', $d);
        }

        if (is_object($data)) {
            $data = implode(', ', (array) $data);
        }

        return $data;
    }

    private function getOptionName($field, $data)
    {
        $options = $field->select_options;
        $value = null;
        if (is_array($data)) {
            $value = [];
            foreach ($data as $d) {
                if (isset($options[$d])) {
                    $value[] = $options[$d];
                }
            }
            $value = implode(', ', $value);
        } else {
            if (isset($options[$data])) {
                $value = $options[$data];
            }
        }

        return $value;
    }

    private function getCountryName($data)
    {
        $countries = forms()->getCountries();

        $value = null;
        if (isset($countries[$data])) {
            $value = $countries[$data];
        }

        return $value;
    }

    private function getReplyTo($form, $request)
    {
        $replyTo = false;

        if (!is_array($request)) {
            $request = $request->all();
        }

        if ($form->reply_to) {
            $field = 'field'.$form->reply_to;
            if (is_numeric($form->reply_to) && $request && isset($request[$field])) {
                // we have a field, now validate it to be an email
                $validator = \Validator::make(['email' => $request[$field]], ['email' => 'required|email']);
                if($validator->passes()) {
                    $replyTo = $request[$field];
                }
            } else {
                $replyTo = $form->reply_to;
            }
        }

        return $replyTo;
    }

    public function setDataForDb($request)
    {
        $data = $request->toArray();
        $files = $request->allFiles();
        if (is_array($files) && sizeof($files)) {
            foreach ($files as $field => $file) {
                if (isset($data[$field])) {
                    if (is_array($file)) {
                        $data[$field] = [];
                        foreach ($file as $k => $f) {
                            $data[$field][] = $f->getClientOriginalName();
                        }
                    } else {
                        $data[$field] = $file->getClientOriginalName();
                    }
                }
            }
        }

        return $data;
    }


    public function getFormSubmissions($formId)
    {
        return EmailSubmission::whereFormId($formId)->get();
    }
}

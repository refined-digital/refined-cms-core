@php
    $value = '';
    if (isset($data->{ $field->name })) {
        $value = $data->{ $field->name };
    }
    if(old($field->name)) {
        $value = old($field->name);
    }


    $choices = [];

    if (isset($field->options) && !is_array($field->options) && is_numeric(strpos($field->options, 'session.'))) {
        $key = str_replace('session.', '', $field->options);
        if (session()->has($key)) {
            $options = session()->get($key);
            if (is_array($options) && sizeof($options)) {
                foreach ($options as $id => $v) {
                  if ($id == 0) continue;
                  $choices[] = [
                      'id' => $id,
                      'name' => $v,
                  ];
                }
            }
        }
    } else {
        $choices = $field->options;
    }

    $showAllowCreate = false;

@endphp
<rd-tags
  :field="{{ json_encode($field) }}"
  :values="{{ json_encode($value) }}"
  :dont-allow-create="{{ $field->options->allowCreate ?? 'true' }}"
  :as-select="true"
  value-field="id"
  @if (sizeof($choices))
  :choices="{{ json_encode($choices) }}"
  @endif
  value-type="id"
></rd-tags>

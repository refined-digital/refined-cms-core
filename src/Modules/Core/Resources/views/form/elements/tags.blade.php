@php
    $value = '';
    if (isset($data->modelTags)) {
        $value = $data->modelTags;
    }
    if(old('modelTags')) {
        $value = old('modelTags');
    }

    $showAllowCreate = false;
    if (isset($field->options->allowCreate)) {
      $showAllowCreate = true;
    }

    $choices = [];
    if (isset($field->options->options) && $field->options->options) {
      $dataSet = $field->options->type::active()->orderBy('name','asc')->get();
      $choices = [];
      if ($dataSet->count()) {
          foreach ($dataSet as $post) {
            if (!isset($data->id) || (isset($data->id) && $data->id !== $post['id'])) {
              $choices[] = [
                  'id' => $post->id,
                  'name' => $post->name,
              ];
            }
          }
      }
    }

    if (isset($field->tagType) && $field->tagType == 'pages') {
      $choices = [];
      $showAllowCreate = true;
      $repo = new \RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository();
      $dataSet = $repo->getForSelect('name');
      if (sizeof($dataSet) > 1) {
          foreach ($dataSet as $id => $name) {
              if ($id == 0) {
                  continue;
              }
              $choices[] = [
                  'id' => $id,
                  'name' => $name,
              ];
          }
      }
    }


    $valueField = false;
    if (isset($field->options->valueField)) {
      $valueField = $field->options->valueField;
    }

@endphp
<rd-tags
  :field="{{ json_encode($field) }}"
  :values="{{ json_encode($value) }}"
  @if ($showAllowCreate)
    :dont-allow-create="{{ $field->options->allowCreate ?? 'true' }}"
  @endif
  @if ($valueField)
    value-field="{{ $valueField }}"
  @endif
  @if (sizeof($choices))
  :choices="{{ json_encode($choices) }}"
  @endif
  type="{{ $field->tagType }}"
></rd-tags>

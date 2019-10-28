<?php
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
      $posts = blog()->getForSelect();
      if (sizeof($posts)) {
        foreach ($posts as $post) {
          if (!isset($data->id) || (isset($data->id) && $data->id !== $post['id'])) {
            $post['value'] = $post['id'];
            $choices[] = $post;
          }
        }
      }
    }

    $valueField = false;
    if (isset($field->options->valueField)) {
      $valueField = $field->options->valueField;
    }
?>
<rd-tags
  :field="{{ json_encode($field) }}"
  :values="{{ json_encode($value) }}"
  @if ($showAllowCreate)
    :dont-allow-create="{{ $field->options->allowCreate || false }}"
  @endif
  @if ($valueField)
    value-field="{{ $valueField }}"
  @endif
  @if (sizeof($choices))
  :choices="{{ json_encode($choices) }}"
  @endif
  type="{{ $field->tagType }}"
></rd-tags>

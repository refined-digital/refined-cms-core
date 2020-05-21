<?php
  $value = '';

  if (isset($data->groups) && $data->groups->count()) {
      $v = [];
      foreach ($data->groups as $group) {
          $v[] = $group->id;
      }
      $value = join(',', $v);
  }

  if(old('groups')) {
      $value = old('groups');
  }

  $dataSet = users()->getUserGroups();
  $choices = [];
  foreach ($dataSet as $label) {
      $choices[] = [
          'id' => $label->id,
          'name' => $label->name
      ];
  }
?>
<rd-select-as-tags
  :field="{{ json_encode($field) }}"
  :values="{{ json_encode($value) }}"
  :choices="{{ json_encode($choices) }}"
></rd-select-as-tags>

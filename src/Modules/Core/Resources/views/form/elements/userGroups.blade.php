<?php
  $value = '';

  if (isset($data->user_groups) && $data->user_groups->count()) {
      $v = [];
      foreach ($data->user_groups as $group) {
          $v[] = $group->id;
      }
      $value = join(',', $v);
  }

  if(old('user_groups')) {
      $value = old('user_groups');
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

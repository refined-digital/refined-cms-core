@php
  if(
    isset($field->setType, $d->modelTags[$field->setType]) &&
    is_array($d->modelTags[$field->setType]) &&
    sizeof($d->modelTags[$field->setType])
  ) {
    $v = [];
    foreach ($d->modelTags[$field->setType] as $type) {
        $v[] = $type['name'];
    }

    echo implode(', ', $v);
  }
@endphp

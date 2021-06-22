<?php
    $value = 0;
    $model = new stdClass();

    if (isset($data->{ $field->name })) {
        $fieldData = json_decode($data->{$field->name});
        if (is_object($fieldData) && isset($fieldData->id)) {
            $value = $fieldData->id;
        } else {
            $value = $fieldData;
        }
    }
    if(old($field->name)) {
        $fieldData = json_decode(old($field->name));

        if (is_object($fieldData) && isset($fieldData->id)) {
            if (isset($fieldData->alt)) {
              $model->oldAlt = $fieldData->alt;
            }

            $value = $fieldData->id;
        }
    }

    if (isset($data)) {
        $model->id = $data->id;
        $model->name = get_class($data);
        $model->alts = \RefinedDigital\CMS\Modules\Media\Models\MediaAltText::whereTypeDetails($model->name)
                                                                                   ->whereTypeId($data->id)
                                                                                   ->whereFieldName($field->name)
                                                                                   ->get()
                                                                                   ->toArray();
    }
?>
<rd-image :value="{{ $value }}" name="{{ $field->name }}" :model="{{ json_encode($model) }}"></rd-image>

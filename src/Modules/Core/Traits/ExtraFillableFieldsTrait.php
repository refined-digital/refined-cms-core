<?php

namespace RefinedDigital\CMS\Modules\Core\Traits;

trait ExtraFillableFieldsTrait {

  // If you change this trait name, this method should match
  // 'initialize' . class_name
  // For example if you change this trait's name to MyTrait
  // You should this method name to  initializeMyTrait
  // So Laravel can see you want to run this method on
  // every model instantiation
  public function initializeExtraFillableFieldsTrait()
  {

      $fields = $this->fillable;
      $config = config($this->config.'.extra_fields');

      // todo: update this to dynamiclly pull in all fields
      if ($config && isset($config[0]['section']['fields'])) {
          foreach ($config[0]['section']['fields'] as $fieldGroup) {
              foreach ($fieldGroup as $field) {
                  if (!isset($field['count'])) {
                      $fields[] = $field['name'];
                  }
              }
          }
      }

      $this->fillable = $fields;
  }

}

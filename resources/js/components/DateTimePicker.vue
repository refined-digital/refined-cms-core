<template>
    <div>
      <label :for="'form--'+field.name" class="form__label">{{ field.label }}</label>
      <input type="text" class="form__control" :name="field.name" required :id="'form--'+field.name" autocomplete="off">
    </div>
</template>

<script>
  import Flatpickr from 'flatpickr';
  import moment from 'moment';
  import 'flatpickr/dist/themes/material_blue.css';

  export default {

    props: [ 'field', 'value' ],

    data() {
      return {
        datePicker: null,
        dateFormat: 'YYYY-MM-DD HH:mm:ss',
        config: {
          altInput: true,
          altFormat: 'F j, Y h:iK',
          dateFormat: 'Y-m-d H:i:S',
          enableTime: true,
          defaultDate: ''
        }
      }
    },

    mounted() {
      this.config.defaultDate = this.value ? moment(this.value, this.dateFormat).format(this.dateFormat) : moment().format(this.dateFormat);
      this.datePicker = new Flatpickr('#form--'+this.field.name, this.config);
    }

  }
</script>

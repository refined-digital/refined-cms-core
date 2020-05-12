<template>
    <div class="form__control--tags">
      <input type="text" class="form__control" :value="tags" :name="field.name" required :id="'form--'+field.name" autocomplete="off">
    </div>
</template>

<script>
  import Selectize from 'vue2-selectize';

  export default {

    props: [ 'field', 'values', 'choices' ],

    data() {
      return {
        value : [],
        tags: '',
        placeholder: 'Select',
        options: [],
      }
    },

    created () {
      this.placeholder = `Select ${this.field.label}`;

      if (this.choices) {
        this.choices.forEach(choice => {
          this.options.push({ ...choice });
        });
      }

      // add the initial tags
      if (typeof this.values != 'undefined') {
        if (Array.isArray(this.values)) {
          this.values.forEach(tag => {
            this.value.push(tag);
          });
        } else {
          this.tags = this.values;
        }
      }
    },

    mounted() {
      this.loadSelect();
    },

    watch: {
      value(val) {
        let v = [];
        val.forEach(i => {
          v.push(i.name);
        });
        this.tags = v.join(',');
      }
    },

    methods: {
      loadSelect() {
        let element = this.$el.querySelector('.form__control');
        let selectizeOptions = {
          plugins: ['restore_on_backspace', 'remove_button', 'drag_drop'],
          delimiter: ',',
          persist: true,
          maxItems: null,
          placeholder: this.placeholder,
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          options: this.options,
          create: false,
          onChange: value => {
            this.tags = value;
          }
        };

        if (this.valueField) {
          selectizeOptions.valueField = this.valueField;
        }

        $(element)
          .selectize(selectizeOptions)

      }

    }


  }
</script>

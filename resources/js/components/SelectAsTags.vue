<template>
    <div class="form__control--tags">
      <input type="text" class="form__control" :value="tags" :name="field.name" required :id="'form--'+field.name" autocomplete="off">
    </div>
</template>

<script>
  import Selectize from 'vue2-selectize';

  export default {

    props: [ 'field', 'values', 'choices', 'value' ],

    data() {
      return {
        internalValue : [],
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
      const initialValue = this.value || this.values;
      if (typeof initialValue != 'undefined') {
        if (Array.isArray(initialValue)) {
          initialValue.forEach(tag => {
            this.internalValue.push(tag);
          });
        } else {
          this.tags = initialValue;
        }
      }
    },

    mounted() {
      this.loadSelect();
    },

    watch: {
      internalValue(val) {
        let v = [];
        val.forEach(i => {
          v.push(i.name);
        });
        this.tags = v.join(',');
      },
      tags(val) {
        this.$emit('input', val);
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

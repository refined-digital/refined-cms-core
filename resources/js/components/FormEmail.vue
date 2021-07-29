<template>
    <div class="form__control--email-tags" ref="row">
      <input type="text" class="form__control" :value="tags" :name="field.name" required :id="'form--'+field.name" ref="control" autocomplete="offer">
    </div>
</template>

<script>
  import Selectize from 'vue2-selectize';
  const REGEX_EMAIL = "([a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@" + "(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)";

  export default {

    props: [ 'field', 'value' ],

    data() {
      return {
        tags: '',
        options: [],
      }
    },

    created() {
      this.tags = this.value;
      this.options = this.value.split(',').map(item => { return { email: item }})
    },

    mounted () {
      this.loadSelect();
    },

    watch: {
      value(val) {
        const v = val.map(item => { return { email: item }});
        this.tags = v.join(',');
      }
    },

    methods: {
      loadSelect() {
        const self = this;
        const element = this.$refs.control;
        const row = this.$refs.row;
        const klass = 'form__control--email-tags-active';
        const selectizeOptions = {
          plugins: ['restore_on_backspace', 'remove_button'],
          delimiter: ',',
          persist: true,
          maxItems: null,
          valueField: 'name',
          labelField: 'name',
          searchField: 'name',
          options: this.options,
          create: true,
          createFilter: function (input) {
            const regex = new RegExp("^" + REGEX_EMAIL + "$", "i");
            const match = input.match(regex);
            if (match) return !this.options.hasOwnProperty(match[0]);

            return false;
          },
          onChange: function(value) {
            self.tags = value;
          },
        };

        if (this.tags) {
          selectizeOptions.items = this.tags.split(',')
        }

        $(element)
          .selectize(selectizeOptions)
      }
    }
  }
</script>

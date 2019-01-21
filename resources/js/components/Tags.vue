<template>
    <div class="form__control--tags">
      <input type="text" class="form__control" :value="tags" :name="'modelTags[' + field.name + ']'" required :id="'form--'+field.name" autocomplete="off">
    </div>
</template>

<script>
  import Selectize from 'vue2-selectize';

  export default {

    props: [ 'field', 'type', 'values' ],

    data() {
      return {
        value : [],
        tags: '',
        setType: this.type || 'tags',
        placeholder: 'Select Tags',
        options: [],
      }
    },

    created () {
      this.placeholder = 'Select ' + this.setType;

      // add the initial tags
      if (typeof this.values != 'undefined') {
        if (Array.isArray(this.values[this.setType])) {
          this.values[this.setType].forEach(tag => {
            this.value.push(tag);
          });
        } else {
          this.tags = this.values[this.setType];
        }
      }

      // get the available tags
      axios.get('/refined/tags/get-all-tags')
        .then(r => {
          if (r.status == 200) {
            let data = r.data;

            if (data.length) {
              data.forEach(d => {
                if (d.type == this.setType) {
                  this.options.push({
                    id: d.id,
                    name: d.name
                  })
                }
              });
            }
          }

          this.loadSelect();
        });
    },

    watch: {
      value(val) {
        let v = [];
        val.forEach(i => {
          v.push(i.name);
        })
        this.tags = v.join(',');
      }
    },

    methods: {
      loadSelect() {
        var self = this;
        let element = this.$el.querySelector('.form__control');
        let selectizeOptions = {
          plugins: ['restore_on_backspace', 'remove_button'],
          delimiter: ',',
          persist: true,
          maxItems: null,
          placeholder: this.placeholder,
          valueField: 'name',
          labelField: 'name',
          searchField: 'name',
          options: this.options,
          create: function(input) {
            return {
              id: 't-'+new Date().getTime(),
              name: input
            }
          },
          onChange: function(value) {
            self.tags = value;
          }
        };

        $(element)
          .selectize(selectizeOptions)

      }

    }


  }
</script>

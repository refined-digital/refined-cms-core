import swal from 'sweetalert';

export const PagesRepeatableMixin = {
  data() {
    return {
      pageContentAsArray: [
        9
      ]
    }
  },
  methods: {
    addRepeatable(data, fields) {

      let row = {};
      if (fields.length) {
        fields.forEach((field, index) => {

          let note = this.getRepeatableFieldNote(field);
          let d = {
            page_content_type_id: field.page_content_type_id,
            content: this.pageContentAsArray.includes(field.page_content_type_id) ? [] : '',
            key: this.getRepeatableFieldIndex(field, index),
            note,
            id: `-${_.kebabCase(field.name)}-id-${Date.now()}`,
            show: true
          };
          if (typeof field.options !== 'undefined') {
            d.options = field.options;
          }
          if (typeof field.width !== 'undefined') {
            d.width = field.width;
          }
          if (typeof field.height !== 'undefined') {
            d.height = field.height;
          }

          row[field.field] = d;
        });
      }

      data.push(row)
    },

    removeRepeatable(data, index) {
      if (typeof data !== 'undefined') {
        swal({
          title: 'Are you sure?',
          icon: 'warning',
          buttons: true,
          dangerMode: true,
        })
          .then((value) => {
            if (value) {
              data.splice(index, 1);
            }
          });
      }
    },

    getRepeatableFieldIndex(field, index) {
      return `${this.page.id ? this.page.id : 'new'}-${field.field}-${field.page_content_type_id}-${field.id ? `${field.id}-` : ''}${index}`;
    },

    getRepeatableFieldNote(field) {
      let note = '';
      if (field.field === 'image' && field.config) {
        note = this.getImageNote(field.config);
      } else if (field.note) {
        note = field.note;
      }

      return note;
    },

    getRepeatableConfigField(fields, index) {
      let configField = null;

      if (Array.isArray(fields)) {
        fields.forEach(field => {
          if (field.field == index) {
            configField = field;
          }
        });
      }

      return configField;
    },
  }
}

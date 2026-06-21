import { unref } from 'vue';
import kebabCase from 'lodash.kebabcase';
import swal from 'sweetalert';

// `page` may be a ref or a plain reactive object. `getImageNote` is supplied by
// usePagesImageNote so the two composables compose the way the old mixins did.
export function usePagesRepeatable(page, { getImageNote } = {}) {
  const pageContentAsArray = [9];

  const getRepeatableFieldIndex = (field, index) => {
    const p = unref(page);
    return `${p.id ? p.id : 'new'}-${field.field}-${field.page_content_type_id}-${field.id ? `${field.id}-` : ''}${index}`;
  };

  const getRepeatableFieldNote = (field) => {
    let note = '';
    if (field.field === 'image' && field.config && getImageNote) {
      note = getImageNote(field.config);
    } else if (field.note) {
      note = field.note;
    }

    return note;
  };

  const getRepeatableConfigField = (fields, index) => {
    let configField = null;

    if (Array.isArray(fields)) {
      fields.forEach((field) => {
        if (field.field == index) {
          configField = field;
        }
      });
    }

    return configField;
  };

  const addRepeatable = (data, fields) => {
    const row = {};
    if (fields.length) {
      fields.forEach((field, index) => {
        const note = getRepeatableFieldNote(field);
        const d = {
          page_content_type_id: field.page_content_type_id,
          content: pageContentAsArray.includes(field.page_content_type_id) ? [] : '',
          key: getRepeatableFieldIndex(field, index),
          note,
          id: `-${kebabCase(field.name)}-id-${Date.now()}`,
          show: true,
        };
        if (typeof field.options !== 'undefined') {
          d.options = field.options;
        }
        if (field.colour) {
          d.colour = field.colour;
          d.content_colour = '';
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

    data.push(row);
  };

  const removeRepeatable = (data, index) => {
    if (typeof data !== 'undefined') {
      swal({
        title: 'Are you sure?',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
      }).then((value) => {
        if (value) {
          data.splice(index, 1);
        }
      });
    }
  };

  return {
    pageContentAsArray,
    addRepeatable,
    removeRepeatable,
    getRepeatableFieldIndex,
    getRepeatableFieldNote,
    getRepeatableConfigField,
  };
}

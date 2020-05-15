<template>
  <div class="form__control--options">
    <div class="form__row">
      <div class="form__label">Select your Variation Types</div>
      <input type="text" class="form__control form__control--tags" v-model="variationTypes" :name="`${name}[types]`" autocomplete="off"/>

    </div>

    <input type="hidden" :name="name" v-model="values"/>
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th class="data-table__cell data-table__cell--sort">&nbsp;</th>
            <th class="data-table__cell">Content</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable(item)"></i></th>
          </tr>
        </thead>
        <tbody v-sortable-repeatable-table>
          <tr v-for="(item, index) in items" class="form__control--options-row" :data-index="index">
            <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort" v-if="items.length > 1"></i></td>
            <td class="data-table__cell">
              <template
                v-for="(cell, cellKey, index) of item"
                v-if="cell.hide_field"
              >
                <input type="hidden" v-model="cell.content"/>
              </template>
              <div class="data-table__repeatable" :class="`data-table__repeatable--${name}`">
                <div
                  class="data-table__cell--repeatable"
                  :class="[
                    `data-table__cell--repeatable-${cell.field}`,
                    { 'data-table__cell--repeatable-has-error' : errors.includes(item) }
                  ]"
                  v-for="(cell, cellKey, index) of item"
                  v-if="!cell.hide_field && cell.field !== 'price' && cell.field !== 'sale_price'"
                >
                  <label class="form__label" v-if="!cell.hide_label">{{ cell.name }}</label>
                  <rd-content-editor-field :item="cell"></rd-content-editor-field>
                </div>
                <div class="data-table__cell--repeatable data-table__cell--repeatable-break"></div>
                <div
                  class="data-table__cell--repeatable"
                  :class="`data-table__cell--repeatable-${cell.field}`"
                  v-for="(cell, cellKey, index) of item"
                  v-if="!cell.hide_field && (cell.field === 'price' || cell.field === 'sale_price')"
                >
                  <label class="form__label" v-if="!cell.hide_label">{{ cell.name }}</label>
                  <rd-content-editor-field :item="cell"></rd-content-editor-field>
                </div>

                <div class="form__note form__note--error" v-if="errors.includes(item)">You have used this combination already</div>
              </div>
            </td>
            <td class="data-table__cell data-table__cell--options-delete"><i class="fa fa-times" @click="removeRepeatable(item, index)"></i></td>
          </tr>
        </tbody>
        <tfoot v-if="items && items.length > 1">
          <tr>
            <th class="data-table__cell data-table__cell--sort"></th>
            <th class="data-table__cell"></th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable(item)"></i></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script>
  import Selectize from 'vue2-selectize';

  export default {

    props: ['item', 'name', 'value', 'variations', 'types', 'field', 'statuses'],

    data() {
        return {
          items: [],
          values: [],
          variationTypes: [],
          errors: []
        }
    },

    created() {

      eventBus.$on('content-editor.select.changed', item => {
        this.checkForDuplicates();
      });

      if (typeof this.value.variationTypes !== 'undefined') {
        this.variationTypes = this.value.variationTypes;
      }

      if (typeof this.value.items !== 'undefined') {
        this.items = this.value.items;
      }
    },

    mounted() {
      this.loadSelect();
    },

    watch: {
      items: {
        handler() {
          const data = {
            variationTypes: this.variationTypes,
            items: this.items
          };
          this.$emit('input', data);
          this.values = JSON.stringify(data);
        },
        deep: true
      }
    },

    methods: {
      addRepeatable() {
        const item = this.formatItem();
        this.items.push(item);

      },

      removeRepeatable(item, index) {
        swal({
          title: 'Are you sure?',
          icon: 'warning',
          buttons: true,
          dangerMode: true,
        }).then(value => {
          if (value) {
            this.items.splice(index, 1);
          }
        });
      },

      loadSelect() {
        const element = this.$el.querySelector('.form__control--tags');
        const selectizeOptions = {
          plugins: ['restore_on_backspace', 'remove_button', 'drag_drop'],
          delimiter: ',',
          persist: true,
          maxItems: null,
          placeholder: 'Select Variation Types',
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          options: this.types,
          create: false,
          onChange: value => {
            const ids = value.split(',').map(v => parseInt(v, 10));
            this.variationTypes = this.variations.filter(variation => {
              return ids.includes(variation.id);
            });

            this.adjustVariationTypes();
          }
        };

        const value = this.variationTypes.map(item => item.id).join(',');
        $(element)
          .val(value)
          .selectize(selectizeOptions);

      },

      formatItem() {
        const item = {};
        if (this.variationTypes.length) {
          this.variationTypes.forEach(type => {
            item[`type_${type.id}`] = this.formatType(type);
          })
        }

        item.price = {
          content: '',
          field: 'price',
          name: 'Price',
          page_content_type_id: 8
        };

        item.sale_price = {
          content: '',
          field: 'sale_price',
          name: 'Sale Price',
          page_content_type_id: 8
        };

        item.product_status_id = {
          content: '',
          field: 'product_status_id',
          name: 'Product Status Id',
          page_content_type_id: 6,
          options: [...this.statuses]
        };

        return item;
      },

      formatType(type) {
        return {
          content: 0,
          field: `type_${type.id}`,
          name: `${type.name}${type.display_name ? `(${type.display_name})` : ''}`,
          page_content_type_id: 6,
          options: type.values.map(v => {
            return {
              value: v.id,
              label: v.name
            }
          })
        }
      },

      adjustVariationTypes() {
        this.items = this.items.map(item => {
          const newItem = this.formatItem();

          // add in any values that might exist
          for (const key in newItem) {
            if (typeof item[key] !== 'undefined') {
              newItem[key].content = item[key].content
            }
          }

          return newItem;
        })
      },

      checkForDuplicates() {
        const duplicateCheck = [];
        const dontCheck = ['price','sale_price'];
        const errors = [];
        this.items.forEach(item => {
          const check = [];
          for (const key in item) {
            if (!dontCheck.includes(key)) {
              check.push(`${key}:${item[key].content}`);
            }
          }
          const checkValue = check.join(',');
          if (duplicateCheck.includes(checkValue)) {
            errors.push(item);
          } else {
            duplicateCheck.push(checkValue);
          }
        });

        this.errors = errors;
      }
    }
  }
</script>

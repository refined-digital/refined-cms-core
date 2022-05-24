<template>
  <div class="form__control--options" v-if="item.type == 'repeatable'">
    <input type="hidden" :name="name" v-model="values"/>
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th class="data-table__cell data-table__cell--sort">&nbsp;</th>
            <th class="data-table__cell">{{ item.repeatableName || 'Content' }}</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable(item)"></i></th>
          </tr>
        </thead>
        <tbody v-sortable-repeatable-table :data-id="id">
          <tr v-for="(item, index) in items" class="form__control--options-row" :data-index="index" :key="item._key">
            <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort" v-if="items.length > 1"></i></td>
            <td class="data-table__cell">
              <template
                v-for="(cell, cellKey, index) of item"
                v-if="cell.hide_field && cellKey !== '_key'"
              >
                <input type="hidden" v-model="cell.content"/>
              </template>
              <div class="data-table__repeatable" :class="`data-table__repeatable--${name}`">
                <div
                  class="data-table__cell--repeatable"
                  :class="`data-table__cell--repeatable-${childIndex}`"
                  v-for="(cell, cellKey, childIndex) of item"
                  :key="cell._key"
                  v-if="!cell.hide_field"
                >
                  <label class="form__label" v-if="!cell.hide_label">{{ cell.name }}</label>
                  <rd-content-editor-field :item="cell"></rd-content-editor-field>
                </div>
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
  import _ from 'lodash';

  export default {

    props: ['item', 'name', 'value'],

    data() {
        return {
          items: [],
          values: [],
          id: Date.now()
        }
    },

    created() {
      let value = this.value;
      // convert the value to an object, if it is a string
      if (value && typeof value === 'string') {
        value = JSON.parse(value);
      }
      if (value && value.length > 0) {
        this.items = [];
        value.forEach(item => {
          this.items.push(item);
        });
      }

      this.items = this.items.map(item => {
        for (const key in item) {
          if (key === '_key') {
            continue;
          }

          item[key]._key = `repeatable_${key}_${this.uid()}`
        }

        item._key = `repeatable_${this.uid()}`
        return item;
      })

      eventBus.$on('sortable-repeatable-table.dragend', data => {
        if (data.id === this.id.toString()) {
          const orderedArray = [];
          data.indexes.forEach(index => {
            orderedArray.push(this.items[index]);
          })

          orderedArray.forEach((item, index) => {
            this.$set(this.items, index, item);
          })
        }
      })
    },

    watch: {
      items: {
        handler() {
          this.$emit('input', this.items);
          this.values = JSON.stringify(this.items);
        },
        deep: true
      }
    },

    methods: {
      uid() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
      },

      addRepeatable(item) {
        let data = {};
        item.fields.forEach(field => {
          let f = JSON.parse(JSON.stringify(field));
          f.content = '';
          data[f.field] = f;
        });

        this.items.push(data);

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
    }
  }
</script>

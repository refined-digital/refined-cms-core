<template>
  <div class="form__control--options" v-if="item.type === 'repeatable' || (typeof item.page_content_type_id !== 'undefined' && item.page_content_type_id == 9)">
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th class="data-table__cell data-table__cell--sort">&nbsp;</th>
            <th class="data-table__cell">{{ heading }}</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable()"></i></th>
          </tr>
        </thead>
        <draggable
          :list="data"
          handle=".data-table__cell--sort"
          tag="tbody"
        >
          <tr v-for="(row, index) of data" class="form__control--options-row" :data-index="index" :key="`${item.key}_${index}`">
            <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort" v-if="data.length > 1"></i></td>
            <td class="data-table__cell">
              <div class="data-table__repeatable" :class="`data-table__repeatable--${item.name}`">
                <div
                  class="data-table__cell--repeatable"
                  :class="`data-table__cell--repeatable-${index}`"
                  v-for="(cell, cellKey, index) of fields"
                  v-show="row[cell.field].show"
                >
                  <label class="form__label" :for="`form--content-${row[cell.field].id}`" v-if="!cell.hide_label">{{ cell.name }}</label>
                  <rd-content-editor-field :item="getItem(row, cell)" :options="cell" :key="`field_${row[cell.field].id}`"></rd-content-editor-field>
                </div>
              </div>
            </td>
            <td class="data-table__cell data-table__cell--options-delete"><i class="fa fa-times" @click="removeRepeatable(row, index)"></i></td>
          </tr>
        </draggable>
        <tfoot v-if="data && data.length > 0">
          <tr>
            <th class="data-table__cell data-table__cell--sort"></th>
            <th class="data-table__cell"></th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable()"></i></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script>
  import draggable from 'vuedraggable'
  import { keyBy } from 'lodash'

  export default {
    props: ['item', 'data', 'fields', 'heading'],

    data() {
      return {
        parent: null,
        fieldsByKey: {}
      }
    },

    created() {
      this.parent = this.getParent();
      this.fieldsByKey = keyBy(this.fields, 'field')


      eventBus.$on('content-editor.select.changed', (item) => {
        this.data.forEach(row => {
          if (!item.options || (item.options && !row[item.options.field])) {
            return;
          }
          if (item.item.id === row[item.options.field].id) {
            const keyCheck = `${item.options.field}:${item.item.content}`;
            for (const field in row) {
              const f = row[field];
              const fDetails = this.fieldsByKey[field];
              if (fDetails.showOn) {
                f.show = keyCheck === fDetails.showOn
              }
            }

          }
        })
      });
    },

    components: {
      draggable,
    },

    methods:  {
      addRepeatable() {
        this.parent.addRepeatable(this.data, this.fields);
      },

      removeRepeatable(item, index) {
        swal({
          title: 'Are you sure?',
          icon: 'warning',
          buttons: true,
          dangerMode: true,
        })
        .then((value) => {
          if (value) {
            this.data.splice(index, 1);
          }
        });
      },

      getParent(name = 'Pages') {
        let parent = this.$parent;
        while(typeof parent !== 'undefined'){
          if (parent.hasOwnProperty('addRepeatable')) {
            return parent;
          } else {
            parent = parent.$parent;
          }
        }
        return false;
      },

      getItem(row, cell) {
        const item = row[cell.field];

        // attach the width and height to the image item;
        if (item.page_content_type_id === 4 && (cell.width || cell.height)) {
          item.width = cell.width || null;
          item.height = cell.height || null;
        }

        return item;
      }
    }
  }
</script>

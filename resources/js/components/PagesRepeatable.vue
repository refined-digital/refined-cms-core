<template>
  <div class="form__control--options" v-if="item.type === 'repeatable' || (typeof item.page_content_type_id !== 'undefined' && item.page_content_type_id == 9)">
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th class="data-table__cell data-table__cell--sort">&nbsp;</th>
            <th class="data-table__cell">Content</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable()"></i></th>
          </tr>
        </thead>
        <draggable
          :list="data"
          handle=".data-table__cell--sort"
          tag="tbody"
        >
          <tr v-for="(row, index) of data" class="form__control--options-row" :data-index="index">
            <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort" v-if="data.length > 1"></i></td>
            <td class="data-table__cell">
              <div class="data-table__repeatable" :class="`data-table__repeatable--${item.name}`">
                <div
                  class="data-table__cell--repeatable"
                  :class="`data-table__cell--repeatable-${index}`"
                  v-for="(cell, cellKey, index) of fields"
                >
                  <label class="form__label" v-if="!cell.hide_label">{{ cell.name }}</label>
                  <rd-content-editor-field :item="row[cell.field]" :options="cell"></rd-content-editor-field>
                </div>
              </div>
            </td>
            <td class="data-table__cell data-table__cell--options-delete"><i class="fa fa-times" @click="removeRepeatable(item, index)"></i></td>
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

  export default {
    props: ['item', 'data', 'fields',],

    data() {
      return {
        parent: null
      }
    },

    created() {
      this.parent = this.getParent();
    },

    components: {
      draggable,
    },

    methods:  {
      addRepeatable() {
        this.parent.addRepeatable(this.data, this.fields);
      },

      removeRepeatable(item, index) {
        this.parent.removeRepeatable(this.data, item, index);
      },

      getParent(name = 'Pages') {
        let parent = this.$parent;
        while(typeof parent !== 'undefined'){
          if (parent.$options.name === name) {
            return parent;
          } else {
            parent = parent.$parent;
          }
        }
        return false;
      }
    }
  }
</script>

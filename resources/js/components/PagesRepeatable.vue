<template>
  <div class="form__control--options" v-if="item.type == 'repeatable'">
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
          <tr v-for="(row, index) of page.data[item.tab]" class="form__control--options-row" :data-index="index">
            <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort" v-if="page.data[item.tab].length > 1"></i></td>
            <td class="data-table__cell">
              <div class="data-table__cell--repeatable" v-for="cell of item.fields">
                <label class="form__label" v-if="!cell.hide_label">{{ cell.name }}</label>
                <rd-content-editor-field :item="row[cell.field]"></rd-content-editor-field>
              </div>
            </td>
            <td class="data-table__cell data-table__cell--options-delete"><i class="fa fa-times" @click="removeRepeatable(item, index)"></i></td>
          </tr>
        </tbody>
        <tfoot v-if="page.data[item.tab] && page.data[item.tab].length > 0">
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

  export default {
    props: ['item', 'page'],

    methods:  {
      addRepeatable(item) {
        this.$parent.addRepeatable(item);
      },

      removeRepeatable(item, index) {
        this.$parent.removeRepeatable(item, index);
      },
    }
  }
</script>

<template>
  <div class="form__control--options">
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th class="data-table__cell data-table__cell--sort"></th>
            <th class="data-table__cell">Value</th>
            <th class="data-table__cell">Label</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="add"></i></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in items">
            <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort" v-if="items.length > 1"></i></td>
            <td class="data-table__cell data-table__cell--options-value"><input type="text" class="form__control" :name="name+'['+index+'][value]'" v-model="item.value"/></td>
            <td class="data-table__cell data-table__cell--options-label"><input type="text" class="form__control" :name="name+'['+index+'][label]'" v-model="item.label"/></td>
            <td class="data-table__cell data-table__cell--options-delete"><i class="fa fa-times" @click="remove(index)"></i></td>
          </tr>
        </tbody>
        <tfoot v-if="items.length > 4">
          <tr>
            <th class="data-table__cell data-table__cell--sort"></th>
            <th class="data-table__cell">Value</th>
            <th class="data-table__cell">Label</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="add"></i></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script>

    export default {

        props: ['name', 'value'],

        data() {
            return {
              items: [
                {
                  value: '',
                  label: ''
                }
              ],
              sortable: null
            }
        },

        created() {
          if (this.value && this.value.length > 0) {
            this.items = [];
            this.value.forEach(item => {
              this.items.push(item);
            });
          }
        },

        mounted() {
          this.initSort();
        },

        methods:  {

          updateFile(data) {
            this.$emit('input', this.image);
          },

          add() {
            this.items.push({
              value: '',
              label: '',
            });
          },

          remove(index) {
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

          initSort() {
            if (!this.sortable) {
              this.sortable = dragula([this.$el.querySelector('tbody')], {
                direction: 'vertical'
              });
            }
          }

        },

    }
</script>
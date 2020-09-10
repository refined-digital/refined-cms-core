<template>
  <div class="form__control--options">
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th class="data-table__cell data-table__cell--sort">&nbsp;</th>
            <th class="data-table__cell">Content</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable()"></i></th>
          </tr>
        </thead>
        <tbody v-sortable-repeatable-table>
          <tr v-for="(item, index) of content" class="form__control--options-row" :data-index="index">
            <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort" v-if="content.length > 1"></i></td>
            <td class="data-table__cell">
              <div class="data-table__repeatable" :class="`data-table__repeatable--${item.name}`">
                <div
                  class="data-table__cell--repeatable"
                  :class="`data-table__cell--repeatable-${index}`"
                >
                  <rd-image v-model="item.content" :value="item.content"></rd-image>
                  <div class="form__note" v-if="note" v-html="note"></div>
                </div>
              </div>
            </td>
            <td class="data-table__cell data-table__cell--options-delete"><i class="fa fa-times" @click="removeRepeatable(item, index)"></i></td>
          </tr>
        </tbody>
        <tfoot v-if="content.length > 0">
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

  export default {
    props: ['name', 'content', 'note' ],

    data() {
      return {
        data: []
      }
    },

    created() {
      this.data = [... this.content];
    },

    methods:  {
      addRepeatable() {
        const item = {
          content: ''
        }
        this.content.push(item);
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
            this.content.splice(index, 1);
          }
        });
      },
    }
  }
</script>

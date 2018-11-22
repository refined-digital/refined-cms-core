<template>
  <div class="form__control--options" v-if="item.type == 'repeatable'">
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th class="data-table__cell data-table__cell--sort"></th>
            <th class="data-table__cell">Content</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable(item)"></i></th>
          </tr>
        </thead>
        <draggable v-model="page.data[item.tab]" :options="options" element="tbody" @end="dragEnd">
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
        </draggable>
        <tfoot v-if="page.data[item.tab] && page.data[item.tab].length > 4">
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
  import naturalSort from 'javascript-natural-sort';
  import draggable from 'vuedraggable'

  export default {
    components: {
      draggable,
    },

    props: ['item', 'page'],

    data() {
        return {
          sortable: null,
          dragGhost: {},
          options: {
            //handle: '.fa-sort',
            draggable: '.form__control--options-row',
            setData: (dataTransfer, dragEl) => {
              // Create the clone (with content)
              this.dragGhost = dragEl.cloneNode(true);
              // Stylize it
              this.dragGhost.classList.add('sortable-drag-ghost');
              // Place it into the DOM tree
              document.body.appendChild(this.dragGhost);
              // Set the new stylized "drag image" of the dragged element
              dataTransfer.setDragImage(this.dragGhost, 0, 0);
            },
          }
        }
    },

    methods:  {
      addRepeatable(item) {
        this.$parent.addRepeatable(item);
      },

      removeRepeatable(item, index) {
        this.$parent.removeRepeatable(item, index);
      },

      dragEnd() {
        this.dragGhost = {};
        let elements = document.body.querySelectorAll('.sortable-drag-ghost');
        if (elements.length) {
          elements.forEach(field => {
            field.remove();
          });
        }
      }
    }
  }
</script>
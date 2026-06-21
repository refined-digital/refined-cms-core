<template>
  <div class="form__control--options" ref="root">
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

<script setup>
  import { ref, watch, onMounted } from 'vue';

  const props = defineProps(['name', 'value']);
  const emit = defineEmits(['input']);

  const root = ref(null);

  const items = ref([
    {
      value: '',
      label: ''
    }
  ]);
  let sortable = null;

  watch(items, () => {
    emit('input', items.value);
  });

  function add() {
    items.value.push({
      value: '',
      label: '',
    });
  }

  function remove(index) {
    swal({
      title: 'Are you sure?',
      icon: 'warning',
      buttons: true,
      dangerMode: true,
    }).then(value => {
      if (value) {
        items.value.splice(index, 1);
      }
    });
  }

  function initSort() {
    if (!sortable) {
      sortable = dragula([root.value.querySelector('tbody')], {
        direction: 'vertical'
      });
    }
  }

  // created
  if (props.value && props.value.length > 0) {
    items.value = [];
    props.value.forEach(item => {
      items.value.push(item);
    });
  }

  onMounted(() => {
    initSort();
  });
</script>
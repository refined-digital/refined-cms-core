<template>
  <div class="form__control--options">
    <rd-repeatable-rows
      :rows="content"
      add-label="Add image"
      @add="addRepeatable()"
      @remove="removeRepeatable($event.row, $event.index)"
    >
      <template #row="{ row }">
        <div class="repeatable-rows__field">
          <rd-image v-model="row.content" :value="row.content"></rd-image>
          <div class="form__note" v-if="note" v-html="note"></div>
        </div>
      </template>
    </rd-repeatable-rows>
  </div>
</template>

<script setup>
  const props = defineProps(['name', 'content', 'note']);

  function addRepeatable() {
    props.content.push({ content: '' });
  }

  function removeRepeatable(item, index) {
    swal({
      title: 'Are you sure?',
      icon: 'warning',
      buttons: true,
      dangerMode: true,
    }).then((value) => {
      if (value) {
        props.content.splice(index, 1);
      }
    });
  }
</script>

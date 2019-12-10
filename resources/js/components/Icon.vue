<template>
  <div v-html="require('!!html-loader!./../../svg/' + this.src)"></div>
</template>

<script>
  export default {
    props: ['src'],
  }
</script>

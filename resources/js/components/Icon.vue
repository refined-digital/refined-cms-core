<template>
  <div v-html="svg"></div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps(['src']);

// eagerly inline every svg so a dynamic `src` resolves at runtime (replaces the
// old webpack `require('!!html-loader!...')` call which Vite does not support).
const svgs = import.meta.glob('../../svg/**/*.svg', { query: '?raw', import: 'default', eager: true });

const svg = computed(() => svgs[`../../svg/${props.src}`] || '');
</script>

<template>
  <div class="colour-set" ref="colourSet">
    <button type="button" class="form__control colour-set__toggle" @click="open = !open">
      <span class="colour-set__swatch" :style="swatchStyle(value)"></span>
      <span class="colour-set__label">{{ selectedLabel }}</span>
      <span class="colour-set__arrow" :class="{ 'colour-set__arrow--open': open }"></span>
    </button>
    <ul class="colour-set__options" v-show="open">
      <li v-for="option in colourOptions" :key="option.value">
        <button
          type="button"
          class="colour-set__option"
          :class="{ 'colour-set__option--selected': option.value === (value || '') }"
          @click="select(option)"
        >
          <span class="colour-set__swatch" :style="swatchStyle(option.value)"></span>
          <span class="colour-set__label">{{ option.label }}</span>
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue';
  import { useConfigStore } from '../stores/config';

  const props = defineProps(['value', 'options', 'allowEmpty']);
  const emit = defineEmits(['input']);

  const config = useConfigStore();

  const colourSet = ref(null);
  const open = ref(false);

  const colourOptions = computed(() => {
    let options = [];

    if (Array.isArray(props.options) && props.options.length) {
      options = props.options;
    } else {
      // fall back to the globally configured colour set
      const colours = config.colourSet || {};
      options = Object.keys(colours).map(value => ({ value, label: colours[value] }));
    }

    if (props.allowEmpty) {
      options = [{ value: '', label: 'Default' }, ...options];
    }

    return options;
  });

  const selectedLabel = computed(() => {
    const selected = colourOptions.value.find(option => option.value === (props.value || ''));
    return selected ? selected.label : 'Select a colour';
  });

  function swatchStyle(value) {
    return { background: value ? `var(--${value})` : 'transparent' };
  }

  function select(option) {
    open.value = false;
    emit('input', option.value);
  }

  function onDocumentClick(event) {
    if (colourSet.value && !colourSet.value.contains(event.target)) {
      open.value = false;
    }
  }

  if (!props.allowEmpty && !props.value && colourOptions.value.length) {
    emit('input', colourOptions.value[0].value);
  }

  onMounted(() => {
    document.addEventListener('click', onDocumentClick);
  });

  onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
  });
</script>

<style lang="scss">
  .field-colour {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 8px;

    &__label {
      font-size: 0.85em;
      font-weight: 700;
    }

    .colour-set {
      min-width: 180px;
    }
  }

  .colour-set {
    position: relative;

    &__toggle {
      display: flex;
      align-items: center;
      gap: 10px;
      text-align: left;
      cursor: pointer;
    }

    &__swatch {
      flex-shrink: 0;
      width: 20px;
      height: 20px;
      border: 1px solid #d6d6d6;
      border-radius: 3px;
    }

    &__label {
      flex-grow: 1;
    }

    &__arrow {
      flex-shrink: 0;
      width: 8px;
      height: 8px;
      border-right: 2px solid currentColor;
      border-bottom: 2px solid currentColor;
      transform: rotate(45deg) translateY(-2px);
      transition: transform 150ms linear;

      &--open {
        transform: rotate(225deg) translateY(-2px);
      }
    }

    &__options {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      z-index: 50;
      margin: 2px 0 0;
      padding: 0;
      list-style: none;
      background: #fff;
      border: 1px solid #d6d6d6;
      border-radius: 3px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      max-height: 240px;
      overflow-y: auto;
    }

    &__option {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
      padding: 8px 12px;
      border: 0;
      background: transparent;
      text-align: left;
      cursor: pointer;

      &:hover {
        background: #f4f4f4;
      }

      &--selected {
        background: #ececec;
      }
    }
  }
</style>

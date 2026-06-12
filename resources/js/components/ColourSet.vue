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

<script>
  export default {

    props: [ 'value', 'options', 'allowEmpty' ],

    data() {
      return {
        open: false,
      }
    },

    computed: {
      colourOptions() {
        let options = [];

        if (Array.isArray(this.options) && this.options.length) {
          options = this.options;
        } else {
          // fall back to the globally configured colour set
          const colours = (window.app && window.app.colourSet) || {};
          options = Object.keys(colours).map(value => ({ value, label: colours[value] }));
        }

        if (this.allowEmpty) {
          options = [{ value: '', label: 'Default' }, ...options];
        }

        return options;
      },

      selectedLabel() {
        const selected = this.colourOptions.find(option => option.value === (this.value || ''));
        return selected ? selected.label : 'Select a colour';
      },
    },

    created() {
      if (!this.allowEmpty && !this.value && this.colourOptions.length) {
        this.$emit('input', this.colourOptions[0].value);
      }
    },

    mounted() {
      document.addEventListener('click', this.onDocumentClick);
    },

    beforeDestroy() {
      document.removeEventListener('click', this.onDocumentClick);
    },

    methods: {
      swatchStyle(value) {
        return { background: value ? `var(--${value})` : 'transparent' };
      },

      select(option) {
        this.open = false;
        this.$emit('input', option.value);
      },

      onDocumentClick(event) {
        if (this.$refs.colourSet && !this.$refs.colourSet.contains(event.target)) {
          this.open = false;
        }
      },
    },

  }
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

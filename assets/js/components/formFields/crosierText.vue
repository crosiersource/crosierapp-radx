<template>
  <div :class="`col-${cols}`">
    <div class="form-group">
      <label for="$attrs.id">{{ label }}</label>
      <InputText
        :class="'form-control ' + (error ? 'is-invalid' : '')"
        type="text"
        v-bind="$attrs"
      />
      <div class="invalid-feedback">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script>
import InputText from "primevue/inputtext";

export default {
  components: {
    InputText,
  },
  props: {
    cols: {
      required: false,
      default: 3,
      type: Number,
    },
    error: {
      required: false,
      default: null,
      type: String,
    },
    label: {
      required: false,
      default: "",
      type: String,
    },
  },
};
</script>

<style lang="scss" scoped></style>

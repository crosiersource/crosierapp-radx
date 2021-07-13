<template>
  <div :class="`col-${cols}`">
    <label for="$attrs.id">{{ label }}</label>
    <InputNumber
      v-if="!decimal && !moeda"
      :class="'form-control ' + (error ? 'is-invalid' : '')"
      type="text"
      inputClass="text-right"
      v-bind="$attrs"
    />
    <InputNumber
      v-if="decimal && !moeda"
      :class="'form-control ' + (error ? 'is-invalid' : '')"
      type="text"
      inputClass="text-right"
      v-bind="$attrs"
      mode="decimal"
      :minFractionDigits="fractionDigits"
      :maxFractionDigits="fractionDigits"
    />
    <InputNumber
      v-if="moeda"
      :class="'form-control ' + (error ? 'is-invalid' : '')"
      type="text"
      inputClass="text-right"
      v-bind="$attrs"
      mode="currency"
      currency="BRL"
    />
    <div class="invalid-feedback">
      {{ error }}
    </div>
  </div>
</template>

<script>
import InputNumber from "primevue/inputnumber";

export default {
  components: {
    InputNumber,
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
    decimal: {
      required: false,
      default: false,
      type: Boolean,
    },
    moeda: {
      required: false,
      default: false,
      type: Boolean,
    },
  },
};
</script>

<style lang="scss" scoped>
.form-control {
  padding: 0px !important;
  border: none !important;
  margin-bottom: 12px;
}
.p-inputtext {
  border: 1px solid #ced4da !important;
}
</style>

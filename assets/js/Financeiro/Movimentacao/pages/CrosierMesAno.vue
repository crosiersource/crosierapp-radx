<template>
  <div :class="'col-md-' + this.col">
    <div class="form-group">
      <div class="form-row">
        <div class="col-7">
          <label v-if="this.showLabel" :for="this.id + '_mes'">{{ label }}</label>
          <Dropdown
            :class="'form-control ' + (this.error ? 'is-invalid' : '')"
            :id="this.id + '_mes'"
            :appendTo="this.appendTo"
            v-model="this.mes"
            @update:modelValue="this.emit"
            :options="this.meses"
            optionLabel="label"
            optionValue="value"
            :placeholder="this.showClear ? 'Selecione' : null"
            :showClear="true"
            :disabled="this.disabled"
            :filter="true"
            @focus="this.$emit('focus')"
            @blur="this.$emit('blur')"
          />
        </div>
        <div class="col-3">
          <label v-if="this.showLabel" :for="this.id + '_ano'"
            ><span style="color: transparent">...</span></label
          >
          <InputNumber
            :class="'form-control ' + (this.error ? 'is-invalid' : '')"
            inputClass="text-right"
            :id="this.id + '_ano'"
            v-model="this.ano"
            @update:modelValue="this.emit"
            :disabled="this.disabled"
            :min="this.min"
            :max="this.max"
            @focus="this.$emit('focus')"
            @blur="this.$emit('blur')"
          />
        </div>
        <div class="col-1">
          <label v-if="this.showLabel" :for="this.id + '_btns'"
            ><span style="color: transparent">...</span></label
          >
          <button
            :id="this.id + '_btns'"
            type="button"
            class="btn btn-block btn-sm btn-info"
            @click="this.trocaMes(false)"
            :disabled="!this.ano || !this.mes"
          >
            <i class="fas fa-angle-left"></i>
          </button>
        </div>
        <div class="col-1">
          <label v-if="this.showLabel" :for="this.id + '_btns'"
            ><span style="color: transparent">...</span></label
          >
          <button
            type="button"
            class="btn btn-block btn-sm btn-info"
            @click="this.trocaMes(true)"
            :disabled="!this.ano || !this.mes"
          >
            <i class="fas fa-angle-right"></i>
          </button>
        </div>
      </div>

      <small v-if="this.helpText" :id="this.id + '_help'" class="form-text text-muted">{{
        this.helpText
      }}</small>
      <div class="invalid-feedbackk blink" v-show="this.error">
        {{ this.error }}
      </div>
    </div>
  </div>
</template>

<script>
import Dropdown from "primevue/dropdown";
import InputNumber from "primevue/inputnumber";
import moment from "moment";

export default {
  name: "CrosierMesAno",

  components: {
    Dropdown,
    InputNumber,
  },

  emits: ["update:modelValue", "change", "focus", "blur"],

  data() {
    return {
      mes: null,
      ano: null,
    };
  },

  props: {
    modelValue: {
      type: [String, Object],
      default: null,
    },
    id: {
      type: String,
      required: true,
    },
    col: {
      type: String,
      default: "12",
    },
    error: {
      type: String,
      default: null,
    },
    label: {
      type: String,
      required: false,
      default: "Período",
    },
    meses: {
      type: Array,
      default: () => [
        { label: "Janeiro", value: "01" },
        { label: "Fevereiro", value: "02" },
        { label: "Março", value: "03" },
        { label: "Abril", value: "04" },
        { label: "Maio", value: "05" },
        { label: "Junho", value: "06" },
        { label: "Julho", value: "07" },
        { label: "Agosto", value: "08" },
        { label: "Setembro", value: "09" },
        { label: "Outubro", value: "10" },
        { label: "Novembro", value: "11" },
        { label: "Dezembro", value: "12" },
      ],
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    helpText: {
      type: String,
    },
    appendTo: {
      type: String,
      default: "body",
    },
    showLabel: {
      type: Boolean,
      default: true,
    },
  },

  mounted() {
    if (this.modelValue) {
      this.mes = moment(this.modelValue).format("MM");
      this.ano = moment(this.modelValue).format("YYYY");
      this.emit();
    }
  },

  methods: {
    moment(date) {
      return moment(date);
    },

    trocaMes(proximo) {
      const primeiroDia = `${this.ano.toString().padStart(4, "0")}-${this.mes}-01`;
      const mesTrocado = proximo
        ? moment(primeiroDia).add(1, "M")
        : moment(primeiroDia).subtract(1, "M");
      this.mes = mesTrocado.format("MM");
      this.ano = mesTrocado.format("YYYY");
      this.emit();
    },

    emit() {
      this.$nextTick(() => {
        if (this.mes && this.ano) {
          const $event = {
            value: null,
          };
          $event.value = `${this.ano.toString().padStart(4, "0")}-${this.mes}-01`;
          this.$emit("update:modelValue", $event.value);
        }
      });
    },
  },
};
</script>
<style scoped>
.invalid-feedbackk {
  width: 100%;
  margin-top: 0.25rem;
  font-size: 80%;
  color: #e55353;
}
</style>

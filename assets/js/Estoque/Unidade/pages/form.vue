<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Unidade">
    <div class="form-row">
      <CrosierInputInt label="Id" col="3" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierInputText
        label="Label"
        col="4"
        id="label"
        v-model="this.fields.label"
        :error="this.formErrors.label"
      />

      <CrosierInputText
        label="Descrição"
        col="5"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.formErrors.descricao"
      />
    </div>
    <div class="form-row">
      <CrosierInputInt
        label="Casas Decimais"
        col="8"
        id="casasDecimais"
        v-model="this.fields.casasDecimais"
        :error="this.formErrors.casasDecimais"
      />

      <CrosierDropdownBoolean
        label="Utilizado"
        col="4"
        id="atual"
        v-model="this.fields.atual"
        :error="this.formErrors.atual"
      />
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import * as yup from "yup";
import { mapGetters, mapMutations } from "vuex";
import {
  CrosierFormS,
  submitForm,
  CrosierDropdownBoolean,
  CrosierInputText,
  CrosierInputInt,
  SetFocus,
} from "crosier-vue";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdownBoolean,
    CrosierInputText,
    CrosierInputInt,
  },

  data() {
    return {
      criarVincularFields: false,
      schemaValidator: {},
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.$store.dispatch("loadData");
    this.schemaValidator = yup.object().shape({
      label: yup.string().required().typeError(),
      descricao: yup.string().required().typeError(),
      casasDecimais: yup.number().required().typeError(),
      atual: yup.boolean().required().typeError(),
    });

    SetFocus("label", 100);

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/est/unidade",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        // fnBeforeSave: (formData) => {
        //
        // },
      });
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),
  },
};
</script>

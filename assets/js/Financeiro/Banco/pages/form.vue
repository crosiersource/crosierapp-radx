<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Banco">
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierInputInt
        label="Código"
        col="2"
        id="codigoBanco"
        v-model="this.fields.codigoBanco"
        :error="this.formErrors.codigoBanco"
      />

      <CrosierInputText
        label="Nome"
        col="5"
        id="nome"
        v-model="this.fields.nome"
        :error="this.formErrors.nome"
      />

      <CrosierDropdownBoolean
        label="Utilizado"
        col="2"
        id="utilizado"
        v-model="this.fields.utilizado"
      />
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import * as yup from "yup";
import {
  CrosierFormS,
  submitForm,
  CrosierDropdownBoolean,
  CrosierInputText,
  CrosierInputInt,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

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

    this.$store.dispatch("loadData");
    this.schemaValidator = yup.object().shape({
      codigoBanco: yup.number().required().typeError(),
      nome: yup.string().required().typeError(),
      utilizado: yup.boolean().required().typeError(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/banco",
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

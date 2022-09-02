<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Lista de Preço">
    <div class="form-row">
      <CrosierInputInt label="Id" col="4" id="id" v-model="this.fields.id" disabled />

      <CrosierInputText
        label="Descrição"
        col="8"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.formErrors.descricao"
      />
    </div>
    <div class="form-row">
      <CrosierCalendar
        label="Vigência (desde)"
        col="6"
        id="dtVigenciaIni"
        v-model="this.fields.dtVigenciaIni"
        :error="this.formErrors.dtVigenciaIni"
      />
      <CrosierCalendar
        label="Vigência (até)"
        col="6"
        id="dtVigenciaFim"
        v-model="this.fields.dtVigenciaFim"
        :error="this.formErrors.dtVigenciaFim"
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
  CrosierCalendar,
  CrosierInputText,
  CrosierInputInt,
  SetFocus,
} from "crosier-vue";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierCalendar,
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
      descricao: yup.string().required().typeError(),
      dtVigenciaIni: yup.date().required().typeError(),
      dtVigenciaFim: yup.date().required().typeError(),
    });

    SetFocus("descricao", 100);

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/est/listaPreco",
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

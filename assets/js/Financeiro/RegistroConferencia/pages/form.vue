<template>
  <Toast position="bottom-right" class="mb-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Registro para Conferência">
    <div class="form-row">
      <CrosierInputInt label="Id" col="3" id="id" v-model="this.fields.id" disabled />

      <CrosierInputText
        label="Descrição"
        col="9"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.formErrors.descricao"
        ref="descricao"
      />
    </div>
    <div class="form-row">
      <CrosierCalendar
        label="Dt do Registro"
        col="3"
        inputClass="crsr-date"
        id="dtRegistro"
        v-model="this.fields.dtRegistro"
        :error="this.formErrors.dtRegistro"
      />

      <CrosierDropdownEntity
        col="6"
        v-model="this.fields.carteira"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ atual: true }"
        label="Carteira"
        id="carteira"
      />

      <CrosierCurrency label="Valor" col="3" id="valor" v-model="this.fields.valor" />
    </div>
    <div class="form-row">
      <CrosierInputTextarea label="Obs" id="obs" v-model="this.fields.obs" />
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import * as yup from "yup";
import {
  CrosierCalendar,
  CrosierCurrency,
  CrosierDropdownEntity,
  CrosierFormS,
  CrosierInputInt,
  CrosierInputText,
  CrosierInputTextarea,
  submitForm,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdownEntity,
    CrosierInputText,
    CrosierInputTextarea,
    CrosierInputInt,
    CrosierCalendar,
    CrosierCurrency,
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
      dtRegistro: yup.date().required().typeError(),
      carteira: yup.mixed().required().typeError(),
      valor: yup.number().required().typeError(),
    });

    document.getElementById("descricao").focus();

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/registroConferencia",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          formData.carteira = formData?.carteira ? formData.carteira["@id"] : null;
        },
      });
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),
  },
};
</script>

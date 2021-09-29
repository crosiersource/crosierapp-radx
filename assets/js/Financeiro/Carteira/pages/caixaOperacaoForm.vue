<template>
  <Toast class="mt-5" />

  <ConfirmDialog></ConfirmDialog>

  <CrosierFormS listUrl="/fin/carteira/list" @submitForm="this.submitForm" :titulo="this.titulo">
    <div class="form-row">
      <CrosierInputInt
        id="id"
        label="Id"
        col="3"
        v-model="this.fields.carteira.id"
        :disabled="true"
      />

      <CrosierInputText
        id="descricao"
        label="Descrição"
        col="9"
        v-model="this.fields.carteira.descricaoMontada"
        :disabled="true"
      />
    </div>
    <div class="form-row">
      <CrosierInputText
        id="operacao"
        label="Operação"
        col="3"
        v-model="this.fields.operacao"
        :disabled="true"
      />

      <CrosierCalendar
        id="operacao"
        label="Operação"
        inputClass="crsr-datetime"
        col="3"
        v-model="this.fields.dtOperacao"
        :disabled="true"
      />

      <CrosierInputText
        id="responsavel_nome"
        label="Responsável"
        col="3"
        v-model="this.fields.responsavel.nome"
        :disabled="true"
      />

      <CrosierCurrency
        id="valor"
        label="Valor"
        col="3"
        v-model="this.fields.valor"
        :error="this.formErrors.valor"
      />
    </div>

    <div class="form-row">
      <CrosierInputText id="obs" label="Obs" col="12" v-model="this.fields.obs" />
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import * as yup from "yup";
import {
  submitForm,
  CrosierFormS,
  CrosierCalendar,
  CrosierInputText,
  CrosierInputInt,
  CrosierCurrency,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import { nextTick } from "vue";

export default {
  components: {
    CrosierFormS,
    ConfirmDialog,
    Toast,
    CrosierCalendar,
    CrosierInputText,
    CrosierCurrency,
    CrosierInputInt,
  },

  data() {
    return {
      agora: null,
      criarVincularFields: false,
      schemaValidator: {},
      validDate: new Date(),
    };
  },

  async mounted() {
    this.setLoading(true);

    this.$store.dispatch("loadData");
    this.schemaValidator = yup.object().shape({
      valor: yup.string().required().typeError(),
    });

    nextTick(() => document.getElementById("valor").focus());

    window.setInterval(() => {
      this.fields.dtOperacao = new Date();
    }, 1000);

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);

      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          await submitForm({
            apiResource: "/api/fin/caixaOperacao",
            schemaValidator: this.schemaValidator,
            $store: this.$store,
            formDataStateName: "fields",
            $toast: this.$toast,
            fnBeforeSave: (formData) => {
              formData.carteira = formData.carteira["@id"];
              formData.responsavel = `/api/core/security/user/${formData.responsavel.id}`;
            },
          });
        },
      });

      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),

    titulo() {
      return this.fields.operacao === "ABERTURA" ? "Abertura de Caixa" : "Fechamento de Caixa";
    },
  },
};
</script>

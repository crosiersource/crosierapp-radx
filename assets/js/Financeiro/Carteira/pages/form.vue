<template>
  <Toast class="mt-5" />
  <CrosierFormS listUrl="/fin/carteira/list" @submitForm="this.submitForm" titulo="Carteiras">
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierInputInt
        label="Código"
        col="2"
        id="codigo"
        v-model="this.fields.codigo"
        :error="this.formErrors.codigo"
      />

      <CrosierInputText
        label="Descrição"
        col="3"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.formErrors.descricao"
      />

      <CrosierCalendar
        label="Dt Consolidado"
        col="3"
        inputClass="crsr-date"
        id="dtConsolidado"
        v-model="this.fields.dtConsolidado"
        :error="this.formErrors.dtConsolidado"
      />

      <CrosierDropdown label="Atual" col="2" id="atual" v-model="this.fields.atual" />
    </div>

    <div class="form-row">
      <CrosierDropdown
        label="Concreta"
        col="3"
        id="concreta"
        helpText="Somente carteiras
      concretas podem conter movimentações com status 'REALIZADA'"
        v-model="this.fields.concreta"
        :error="this.formErrors.concreta"
      />

      <CrosierDropdown
        label="Abertas"
        col="3"
        id="abertas"
        helpText="Podem conter movimentação a pagar/receber (status 'ABERTA')"
        v-model="this.fields.abertas"
        :error="this.formErrors.abertas"
      />

      <CrosierDropdown
        label="Caixa"
        col="3"
        id="caixa"
        helpText="As datas de vencimento, pagamento e movimentação sempre coincidem"
        v-model="this.fields.caixa"
        :error="this.formErrors.caixa"
      />

      <CrosierDropdown
        label="Cheques"
        col="3"
        id="cheque"
        helpText="A carteira possui talão de cheques"
        v-model="this.fields.cheques"
        :error="this.formErrors.cheques"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownBanco col="4" id="banco" v-model="this.fields.banco" />

      <CrosierInputText label="Agência" col="2" id="agencia" v-model="this.fields.agencia" />

      <CrosierInputText label="Conta" col="3" id="conta" v-model="this.fields.conta" />

      <CrosierCurrency label="Limite" col="3" id="Limite" v-model="this.fields.limite" />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        v-model="this.fields.operadoraCartao"
        entity-uri="/api/fin/operadoraCartao"
        optionLabel="descricao"
        :orderBy="{ descricao: 'ASC' }"
        label="Operadora Cartão"
        id="operadoraCartao"
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
  CrosierCalendar,
  CrosierCurrency,
  CrosierDropdown,
  CrosierDropdownBanco,
  CrosierDropdownEntity,
  CrosierInputText,
  CrosierInputInt,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    CrosierDropdownEntity,
    CrosierCurrency,
    CrosierDropdownBanco,
    Toast,
    CrosierFormS,
    CrosierDropdown,
    CrosierInputText,
    CrosierInputInt,
    CrosierCalendar,
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
      codigo: yup.string().required().typeError(),
      descricao: yup.string().required().typeError(),
      atual: yup.boolean().required().typeError(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/carteira",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
      });
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),
  },
};
</script>

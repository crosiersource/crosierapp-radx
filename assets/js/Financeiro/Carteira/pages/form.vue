<template>
  <Toast position="bottom-right" class="mb-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Carteira">
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" disabled />

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

      <CrosierSwitch label="Atual" col="2" id="atual" v-model="this.fields.atual" />
    </div>

    <div class="form-row">
      <CrosierDropdownBoolean
        label="Concreta"
        col="3"
        id="concreta"
        helpText="Somente carteiras
      concretas podem conter movimentações com status 'REALIZADA'"
        v-model="this.fields.concreta"
        :error="this.formErrors.concreta"
      />

      <CrosierDropdownBoolean
        label="Abertas"
        col="3"
        id="abertas"
        helpText="Podem conter movimentação a pagar/receber (status 'ABERTA')"
        v-model="this.fields.abertas"
        :error="this.formErrors.abertas"
      />

      <CrosierDropdownBoolean
        label="Caixa"
        col="3"
        id="caixa"
        helpText="As datas de vencimento, pagamento e movimentação sempre coincidem"
        v-model="this.fields.caixa"
        :error="this.formErrors.caixa"
      />

      <CrosierDropdownBoolean
        label="Cheques"
        col="3"
        id="cheque"
        helpText="A carteira possui talão de cheques"
        v-model="this.fields.cheque"
        :error="this.formErrors.cheque"
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
        :filters="{ ativa: true }"
        label="Operadora Cartão"
        id="operadoraCartao"
      />
    </div>

    <div class="form-row" v-if="this.fields.caixa">
      <CrosierInputText
        id="caixaStatus"
        label="Status"
        col="5"
        v-model="this.fields.caixaStatus"
        disabled
      />

      <CrosierInputText
        v-if="this.fields.caixaResponsavel?.nome"
        id="caixaResponsavel"
        label="Responsável Atual"
        col="7"
        v-model="this.fields.caixaResponsavel.nome"
        disabled
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
  CrosierDropdownBoolean,
  CrosierDropdownBanco,
  CrosierDropdownEntity,
  CrosierInputText,
  CrosierInputInt,
  CrosierSwitch,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    CrosierDropdownEntity,
    CrosierCurrency,
    CrosierDropdownBanco,
    Toast,
    CrosierFormS,
    CrosierDropdownBoolean,
    CrosierInputText,
    CrosierInputInt,
    CrosierCalendar,
    CrosierSwitch,
  },

  data() {
    return {
      schemaValidator: {},
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.$store.dispatch("loadData");
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
        fnBeforeSave: (formData) => {
          formData.caixaResponsavel = formData?.caixaResponsavel
            ? formData.caixaResponsavel["@id"]
            : null;
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

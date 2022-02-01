<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Modo">
    <div class="form-row">
      <CrosierInputInt label="Id" col="3" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierInputInt
        label="Código"
        col="3"
        id="codigo"
        v-model="this.fields.codigo"
        :error="this.formErrors.codigo"
      />

      <CrosierInputText
        label="Descrição"
        col="6"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.formErrors.descricao"
      />
    </div>

    <div class="form-row">
      <CrosierDropdown
        label="Transf Própria"
        col="4"
        id="modoDeTransfPropria"
        v-model="this.fields.modoDeTransfPropria"
        helpText="Informa se este modo é aceito para transferências próprias (entre carteiras)"
      />

      <CrosierDropdown
        label="Moviment Agrup"
        col="4"
        id="modoDeMovimentAgrup"
        v-model="this.fields.modoDeMovimentAgrup"
        helpText="Informa se este modo é aceito em movimentações em grupo de movimentações"
      />

      <CrosierDropdown
        label="Cartão"
        col="4"
        id="modoDeCartao"
        v-model="this.fields.modoDeCartao"
        helpText="Informa se este modo é aceito em movimentações de cartões de crédito/débito"
      />
    </div>

    <div class="form-row">
      <CrosierDropdown
        label="Cheque"
        col="4"
        id="modoDeCheque"
        v-model="this.fields.modoDeCheque"
        helpText="Informa se este modo é aceito em movimentações que utilizam cheque"
      />

      <CrosierDropdown
        label="Moviment Agrup"
        col="4"
        id="modoDeMovimentAgrup"
        v-model="this.fields.modoDeMovimentAgrup"
        helpText="Informa se este modo é aceito em movimentações em grupo de movimentações"
      />

      <CrosierDropdown
        label="Cartão"
        col="4"
        id="modoDeCartao"
        v-model="this.fields.modoDeCartao"
        helpText="Informa se este modo é aceito em movimentações de cartões de crédito/débito"
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
  CrosierDropdown,
  CrosierInputText,
  CrosierInputInt,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdown,
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
      codigo: yup.number().required().typeError(),
      descricao: yup.string().required().typeError(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/modo",
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

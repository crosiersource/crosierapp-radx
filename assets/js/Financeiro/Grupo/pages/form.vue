<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Grupo de Movimentação">
    <div class="form-row">
      <CrosierInputInt label="Id" col="3" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierInputText
        label="Descrição"
        col="9"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.formErrors.descricao"
      />
    </div>

    <div class="form-row">
      <CrosierInputInt
        label="Dia Vencto"
        col="4"
        id="diaVencto"
        v-model="this.fields.diaVencto"
        :error="this.formErrors.diaVencto"
      />

      <CrosierInputInt
        label="Dia Início"
        col="4"
        id="diaInicioAprox"
        v-model="this.fields.diaInicioAprox"
        :error="this.formErrors.diaInicioAprox"
      />

      <CrosierDropdownBoolean
        label="Utilizado"
        col="4"
        id="utilizado"
        v-model="this.fields.ativo"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="5"
        v-model="this.fields.carteiraPagantePadrao"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Carteira Pagante Padrão"
        id="carteiraPagantePadrao"
      />

      <CrosierDropdownEntity
        col="7"
        v-model="this.fields.categoriaPadrao"
        entity-uri="/api/fin/categoria"
        optionLabel="descricaoMontadaTree"
        :optionValue="null"
        :orderBy="{ codigoOrd: 'ASC' }"
        label="Categoria Padrão"
        id="categoriaPadrao"
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
  CrosierDropdownEntity,
  SetFocus,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdownBoolean,
    CrosierInputText,
    CrosierInputInt,
    CrosierDropdownEntity,
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
      descricao: yup.string().required().typeError(),
      diaVencto: yup.number().required().typeError(),
      diaInicioAprox: yup.number().required().typeError(),
      ativo: yup.boolean().required().typeError(),
    });

    SetFocus("descricao", 40);

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/grupo",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          formData.categoriaPadrao = formData.categoriaPadrao["@id"];
          formData.carteiraPagantePadrao = formData.carteiraPagantePadrao["@id"];
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
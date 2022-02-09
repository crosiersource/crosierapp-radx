<template>
  <Toast position="bottom-right" class="mb-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Regra para Importação de Linha">
    <div class="form-row">
      <CrosierInputInt label="Id" col="3" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierInputText
        label="Regex"
        col="5"
        id="regraRegexJava"
        v-model="this.fields.regraRegexJava"
        :error="this.formErrors.regraRegexJava"
      />

      <CrosierDropdownEntity
        col="4"
        v-model="this.fields.tipoLancto"
        entity-uri="/api/fin/tipoLancto"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Tipo Lancto"
        id="tipoLancto"
        :error="this.formErrors.tipoLancto"
      />
    </div>
    <div class="form-row">
      <CrosierDropdownEntity
        col="5"
        v-model="this.fields.centroCusto"
        :error="this.formErrors.centroCusto"
        entity-uri="/api/fin/centroCusto"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Centro de Custo"
        id="centroCusto"
      />

      <CrosierDropdownEntity
        col="7"
        v-model="this.fields.modo"
        :error="this.formErrors.modo"
        entity-uri="/api/fin/modo"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Modo"
        id="modo"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="6"
        v-model="this.fields.categoria"
        :error="this.formErrors.categoria"
        entity-uri="/api/fin/categoria"
        optionLabel="descricaoMontadaTree"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Categoria"
        id="categoria"
      />

      <CrosierDropdown
        col="3"
        v-model="this.fields.sinalValor"
        :options="[
          { label: 'Positivo', value: 1 },
          { label: 'Negativo', value: -1 },
          { label: 'Ambos', value: 0 },
        ]"
        label="Sinal"
        id="sinalValor"
      />

      <CrosierInputText
        label="Padrão da Descrição"
        col="3"
        id="padraoDescricao"
        v-model="this.fields.padraoDescricao"
        :error="this.formErrors.padraoDescricao"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="6"
        v-model="this.fields.carteira"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Carteira"
        id="carteira"
      />

      <CrosierDropdownEntity
        col="6"
        v-model="this.fields.carteiraDestino"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Carteira Destino"
        id="carteiraDestino"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="4"
        v-model="this.fields.chequeBanco"
        entity-uri="/api/fin/banco"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Cheque - Banco"
        id="chequeBanco"
      />

      <CrosierInputText
        label="Agência"
        col="2"
        id="chequeAgencia"
        v-model="this.fields.chequeAgencia"
        :error="this.formErrors.chequeAgencia"
      />

      <CrosierInputText
        label="Conta"
        col="3"
        id="chequeConta"
        v-model="this.fields.chequeConta"
        :error="this.formErrors.chequeConta"
      />

      <CrosierInputText
        label="Núm Cheque"
        col="3"
        id="chequeNumCheque"
        v-model="this.fields.chequeNumCheque"
        :error="this.formErrors.chequeNumCheque"
      />
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import * as yup from "yup";
import {
  CrosierDropdownEntity,
  CrosierDropdown,
  CrosierFormS,
  CrosierInputInt,
  CrosierInputText,
  submitForm,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdownEntity,
    CrosierDropdown,
    CrosierInputText,
    CrosierInputInt,
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
      regraRegexJava: yup.string().required(),
      sinalValor: yup.number().required(),
      padraoDescricao: yup.string().required(),
      tipoLancto: yup.mixed().required(),
      centroCusto: yup.mixed().required(),
      modo: yup.mixed().required(),
      categoria: yup.mixed().required(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/regraImportacaoLinha",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          formData.tipoLancto =
            formData.tipoLancto && formData.tipoLancto["@id"] ? formData.tipoLancto["@id"] : null;
          formData.centroCusto =
            formData.centroCusto && formData.centroCusto["@id"]
              ? formData.centroCusto["@id"]
              : null;
          formData.modo = formData.modo && formData.modo["@id"] ? formData.modo["@id"] : null;
          formData.categoria =
            formData.categoria && formData.categoria["@id"] ? formData.categoria["@id"] : null;
          formData.carteira =
            formData.carteira && formData.carteira["@id"] ? formData.carteira["@id"] : null;
          formData.carteiraDestino =
            formData.carteiraDestino && formData.carteiraDestino["@id"]
              ? formData.carteiraDestino["@id"]
              : null;
          formData.chequeBanco =
            formData.chequeBanco && formData.chequeBanco["@id"]
              ? formData.chequeBanco["@id"]
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

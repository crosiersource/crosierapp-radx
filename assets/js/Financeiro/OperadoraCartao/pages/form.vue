<template>
  <Toast position="bottom-right" class="mb-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Operadora Cartão">
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" disabled />

      <CrosierInputText
        label="Descrição"
        col="4"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.formErrors.descricao"
      />

      <CrosierDropdownEntity
        col="4"
        v-model="this.fields.carteira"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Carteira"
        id="carteira"
      />

      <CrosierDropdownBoolean
        label="Ativa"
        col="2"
        id="ativa"
        v-model="this.fields.ativa"
        :error="this.formErrors.ativa"
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
  CrosierDropdownEntity,
  CrosierInputText,
  CrosierInputInt,
  CrosierDropdownBoolean,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdownEntity,
    CrosierInputText,
    CrosierDropdownBoolean,
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
      descricao: yup.string().required(),
      carteira: yup.mixed().required(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/operadoraCartao",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          formData.carteira = formData.carteira["@id"] ?? null;
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

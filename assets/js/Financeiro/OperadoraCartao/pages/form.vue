<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS
    listUrl="/fin/operadoraCartao/list"
    @submitForm="this.submitForm"
    titulo="Operadora Cartão"
  >
    <div class="form-row">
      <CrosierInputInt label="Id" col="3" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierInputText
        label="Descrição"
        col="5"
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
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdownEntity,
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

    this.$store.dispatch("loadData");
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

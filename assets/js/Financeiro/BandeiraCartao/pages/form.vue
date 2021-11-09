<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS
    listUrl="/fin/bandeiraCartao/list"
    @submitForm="this.submitForm"
    titulo="Bandeira Cartão"
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
        v-model="this.fields.modo"
        entity-uri="/api/fin/modo"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ codigo: [9, 10] }"
        label="Modo"
        id="modo"
      />
    </div>

    <div class="form-row">
      <CrosierInputTextarea
        label="Labels"
        id="labels"
        v-model="this.fields.labels"
        :error="this.formErrors.labels"
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
  CrosierInputTextarea,
  CrosierInputInt,
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
      labels: yup.string().required(),
      modo: yup.mixed().required(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/bandeiraCartao",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          formData.modo = formData.modo["@id"] ?? null;
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

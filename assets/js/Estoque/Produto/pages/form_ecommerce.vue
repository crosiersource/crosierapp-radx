<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS :withoutCard="true" :disabledSubmit="true">
    <div class="form-row">
      <CrosierDropdownBoolean
        label="Integrado"
        id="integrado"
        v-model="this.fields.ecommerce"
        :disabled="true"
      />
    </div>

    <div class="form-row">
      <CrosierInputText
        id="ecommerce_integr_por"
        label="Integrado por"
        v-model="this.fields.jsonData['ecommerce_integr_por']"
        col="6"
        :disabled="true"
      />

      <CrosierCalendar
        label="Dt Última Integração"
        col="4"
        :showTime="true"
        :showSeconds="true"
        id="dtUltIntegracaoEcommerce"
        v-model="this.fields.dtUltIntegracaoEcommerce"
        :disabled="true"
      />

      <div class="col-2">
        <label class="transparente">.</label>
        <button
          id="btnIntegrarAoEcommerce"
          type="button"
          @click="this.integrarAoEcommerce"
          class="btn btn-block btn-sm btn-warning"
          title="Integrar ao E-commerce"
        >
          <i class="fas fa-cloud-upload-alt"></i> Integrar
        </button>
      </div>
    </div>
  </CrosierFormS>
</template>

<script>
import axios from "axios";
import Toast from "primevue/toast";
import * as yup from "yup";
import { mapGetters, mapMutations, mapActions } from "vuex";
import {
  CrosierCalendar,
  CrosierDropdownBoolean,
  CrosierFormS,
  CrosierInputText,
  SetFocus,
  submitForm,
} from "crosier-vue";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdownBoolean,
    CrosierInputText,
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

    this.schemaValidator = yup.object().shape({
      nome: yup.string().required().typeError(),
      status: yup.string().required().typeError(),
      depto: yup.mixed().required().typeError(),
      grupo: yup.mixed().required().typeError(),
      subgrupo: yup.mixed().required().typeError(),
      fornecedor: yup.mixed().required().typeError(),
      unidadePadrao: yup.mixed().required().typeError(),
      qtdeTotal: yup.number().required().typeError(),
    });

    SetFocus("codigo", 100);

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),
    ...mapActions(["loadData"]),

    async buscarProxCodigo() {
      const rs = await axios.get("/api/est/produto/findProxCodigo");
      this.fields.codigo = rs?.data?.DATA?.prox;
    },

    integrarAoEcommerce() {
      this.$confirm.require({
        message: "Confirmar operação?",
        header: "Confirmação",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          const rs = await axios.get(
            // eslint-disable-next-line max-len
            `/api/est/produto/ecommerce/integrarProduto/${this.fields.id}`,
            {
              validateStatus(status) {
                return status < 500;
              },
            }
          );
          if (rs?.data?.RESULT === "OK") {
            this.$toast.add({
              severity: "success",
              summary: "Sucesso",
              detail: "Produto integrado ao e-commerce com sucesso",
              life: 5000,
            });
            this.loadData();
          } else {
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: "Erro ao integrar ao e-commerce",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),
  },
};
</script>

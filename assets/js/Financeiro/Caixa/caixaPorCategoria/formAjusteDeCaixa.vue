<template>
  <CrosierFormS
    withoutCard
    @submitForm="this.submitForm"
    :formUrl="null"
    :listUrl="null"
    semBotaoSalvar
  >
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.movimentacao.id" disabled />

      <CrosierDropdown
        col="7"
        v-model="this.movimentacao.categoria"
        :error="this.movimentacaoErrors.categoria"
        label="Categoria"
        id="categoria"
        :options="[
          { label: 'Positivo (Diferença)', value: { '@id': '/api/fin/categoria/151' } },
          { label: 'Negativo (Diferença)', value: { '@id': '/api/fin/categoria/251' } },
          { label: 'Positivo (Complemento)', value: { '@id': '/api/fin/categoria/152' } },
        ]"
      />

      <CrosierCurrency
        col="3"
        id="valor"
        label="Valor"
        v-model="this.movimentacao.valor"
        :error="this.movimentacaoErrors.valor"
      />
    </div>

    <div class="form-row mt-2" v-if="this.$store.state.exibirCampos?.obs">
      <CrosierInputTextarea label="Obs" id="obs" v-model="this.movimentacao.obs" />
    </div>

    <Rodape />
  </CrosierFormS>
</template>

<script>
import * as yup from "yup";
import {
  CrosierCurrency,
  CrosierFormS,
  CrosierInputInt,
  CrosierDropdown,
  CrosierInputTextarea,
  submitForm,
} from "crosier-vue";
import { mapGetters, mapMutations, mapActions } from "vuex";
import moment from "moment";
import Rodape from "./rodape.vue";

export default {
  components: {
    CrosierCurrency,
    CrosierFormS,
    CrosierInputInt,
    CrosierInputTextarea,
    CrosierDropdown,
    Rodape,
  },

  data() {
    return {
      schemaValidator: {},
    };
  },

  async mounted() {
    this.schemaValidator = yup.object().shape({
      categoria: yup.mixed().required().typeError(),
      valor: yup.number().required().typeError(),
    });
  },

  methods: {
    ...mapMutations(["setLoading", "setMovimentacao", "setMovimentacaoErrors"]),
    ...mapActions(["doFilter", "salvarUltimaMovimentacaoNoLocalStorage"]),

    moment(date) {
      return moment(date);
    },

    async submitForm() {
      this.setLoading(true);
      try {
        const rs = await submitForm({
          apiResource: "/api/fin/movimentacao",
          schemaValidator: this.schemaValidator,
          $store: this.$store,
          formDataStateName: "movimentacao",
          $toast: this.$toast,
          setUrlId: false,
          fnBeforeSave: (formData) => {
            formData.descricao = "AJUSTE DE CAIXA";
            formData.modo = "/api/fin/modo/1";
            formData.tipoLancto = "/api/fin/tipoLancto/20";
            formData.dtMoviment = moment(this.filters.dtMoviment).format();
            formData.carteira = this.filters.carteira;
            formData.centroCusto =
              formData.centroCusto && formData.centroCusto["@id"]
                ? formData.centroCusto["@id"]
                : null;
            formData.categoria =
              formData.categoria && formData.categoria["@id"] ? formData.categoria["@id"] : null;
            formData.operadoraCartao = null;
            formData.bandeiraCartao = null;
            delete formData.carteiraDestino;
            delete formData.cadeia;
            delete formData.fatura;
          },
        });
        if ([200, 201].includes(rs?.status)) {
          this.doFilter();
          this.$store.state.exibeDialogMovimentacao = false;
          this.salvarUltimaMovimentacaoNoLocalStorage();
        }
      } catch (e) {
        console.error(e);
      }
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({
      movimentacao: "getFields",
      movimentacaoErrors: "getFieldsErrors",
      filters: "getFilters",
    }),
  },
};
</script>
<style scoped>
.camposEmpilhados {
  margin-top: -15px;
}
</style>

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

      <CrosierDropdownEntity
        col="6"
        v-model="this.movimentacao.carteiraDestino"
        :error="this.movimentacaoErrors.carteiraDestino"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ concreta: true, atual: true }"
        label="Carteira (Destino)"
        id="carteira"
        @change="this.onChangeCarteiras"
      />

      <CrosierCurrency
        label="Valor"
        col="4"
        id="valorTotal"
        v-model="this.movimentacao.valorTotal"
        :error="this.movimentacaoErrors.valorTotal"
      />
    </div>

    <div class="form-row">
      <CrosierInputText
        label="Descrição"
        id="descricao"
        v-model="this.movimentacao.descricao"
        :error="this.movimentacaoErrors.descricao"
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
  CrosierDropdownEntity,
  CrosierFormS,
  CrosierInputInt,
  CrosierInputText,
  CrosierInputTextarea,
  submitForm,
  SetFocus,
} from "crosier-vue";
import { mapGetters, mapMutations, mapActions } from "vuex";
import moment from "moment";
import Rodape from "./rodape.vue";

export default {
  components: {
    CrosierDropdownEntity,
    CrosierCurrency,
    CrosierFormS,
    CrosierInputText,
    CrosierInputInt,
    CrosierInputTextarea,
    Rodape,
  },

  data() {
    return {
      schemaValidator: {},
      sacadosOuCedentes: null,
    };
  },

  async mounted() {
    this.schemaValidator = yup.object().shape({
      carteiraDestino: yup.mixed().required().typeError(),
      valorTotal: yup.number().required().typeError(),
    });
    SetFocus("descricao", 40);
    this.movimentacao.categoria = {
      "@id": "/api/fin/categoria/299",
      codigoSuper: 2,
    };
  },

  methods: {
    ...mapMutations(["setLoading", "setMovimentacao", "setMovimentacaoErrors"]),
    ...mapActions(["doFilter", "salvarUltimaMovimentacaoNoLocalStorage"]),

    moment(date) {
      return moment(date);
    },

    async submitForm() {
      this.setLoading(true);
      const rs = await submitForm({
        apiResource: "/api/fin/movimentacao",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "movimentacao",
        $toast: this.$toast,
        setUrlId: false,
        fnBeforeSave: (formData) => {
          delete formData.cadeia;
          delete formData.centroCusto;
          delete formData.movimentacaoOposta;
          delete formData.grupoItem;
          delete formData.fatura;

          formData.descricao =
            formData.descricao || `RETIRADA P/ ${formData.carteiraDestino.descricao}`;
          formData.dtMoviment = moment(this.filters.dtMoviment).format();
          formData.tipoLancto = "/api/fin/tipoLancto/60";
          formData.categoria = "/api/fin/categoria/299";
          formData.valor = formData.valorTotal;
          formData.modo = "/api/fin/modo/11";
          formData.carteiraDestino = formData.carteiraDestino["@id"];
          formData.carteira = formData.carteira["@id"];
        },
      });
      if ([200, 201].includes(rs?.status)) {
        this.doFilter();
        this.$store.state.exibeDialogMovimentacao = false;
        this.salvarUltimaMovimentacaoNoLocalStorage();
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

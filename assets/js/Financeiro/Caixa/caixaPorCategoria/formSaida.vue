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
        col="7"
        v-model="this.movimentacao.categoria"
        :error="this.movimentacaoErrors.categoria"
        entity-uri="/api/fin/categoria"
        optionLabel="descricaoMontadaTree"
        :optionValue="null"
        :orderBy="{ codigoOrd: 'ASC' }"
        :filters="{ codigoSuper: 2 }"
        label="Categoria"
        id="categoria"
      />

      <CrosierCurrency
        col="3"
        id="valor"
        label="Valor"
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

    <div class="form-row">
      <CrosierDropdownEntity
        v-model="this.movimentacao.modo"
        :error="this.movimentacaoErrors.modo"
        :filters="[{ 'codigo[]': 1 }, { 'codigo[]': 3 }, { 'codigo[]': 4 }]"
        entity-uri="/api/fin/modo"
        :optionValue="null"
        optionLabel="descricaoMontada"
        :orderBy="{ codigo: 'ASC' }"
        label="Modo"
        id="modo"
      />
    </div>

    <SacadoCedente v-if="this.$store.state.exibirCampos?.sacadoCedente" />

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
import SacadoCedente from "../../Movimentacao/pages/sacadoCedente";
import Rodape from "./rodape.vue";

export default {
  components: {
    CrosierDropdownEntity,
    CrosierCurrency,
    CrosierFormS,
    CrosierInputText,
    CrosierInputInt,
    CrosierInputTextarea,
    SacadoCedente,
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
      modo: yup.mixed().required().typeError(),
      categoria: yup.mixed().required().typeError(),
      carteira: yup.mixed().required().typeError(),
      descricao: yup.mixed().required().typeError(),
      valorTotal: yup.number().required().typeError(),
    });
    SetFocus("descricao", 40);
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
          delete formData.operadoraCartao;
          delete formData.bandeiraCartao;
          delete formData.carteiraDestino;

          formData.dtMoviment = moment(this.filters.dtMoviment).format();
          formData.carteira = this.filters.carteira;
          formData.tipoLancto = "/api/fin/tipoLancto/20";
          formData.categoria = formData.categoria["@id"];
          formData.valor = formData.valorTotal;
          formData.modo = formData.modo["@id"];
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

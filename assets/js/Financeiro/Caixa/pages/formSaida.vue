<template>
  <CrosierFormS withoutCard @submitForm="this.submitForm" :formUrl="null" :listUrl="null">
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.movimentacao.id" disabled />

      <CrosierInputText
        col="6"
        label="Descrição"
        id="descricao"
        v-model="this.movimentacao.descricao"
        :error="this.movimentacaoErrors.descricao"
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

    <SacadoCedente />

    <div class="form-row mt-2">
      <CrosierInputTextarea label="Obs" id="obs" v-model="this.movimentacao.obs" />
    </div>
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

export default {
  components: {
    CrosierDropdownEntity,
    CrosierCurrency,
    CrosierFormS,
    CrosierInputText,
    CrosierInputInt,
    CrosierInputTextarea,
    SacadoCedente,
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
    this.movimentacao.categoria = {
      "@id": "/api/fin/categoria/299",
      codigoSuper: 2,
    };
  },

  methods: {
    ...mapMutations(["setLoading", "setMovimentacao", "setMovimentacaoErrors"]),
    ...mapActions(["doFilter"]),

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

          formData.dtMoviment = moment(this.filters.dtMoviment).format();
          formData.carteira = this.filters.carteira;
          formData.tipoLancto = "/api/fin/tipoLancto/60";
          formData.categoria = formData.categoria["@id"];
          formData.valor = formData.valorTotal;
          formData.modo = formData.modo["@id"];
        },
      });
      if ([200, 201].includes(rs?.status)) {
        this.doFilter();
        this.$store.state.exibeDialogMovimentacao = false;
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

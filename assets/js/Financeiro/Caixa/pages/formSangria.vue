<template>
  <CrosierFormS withoutCard @submitForm="this.submitForm" :formUrl="null" :listUrl="null">
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
        :filters="{ concreta: true }"
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
  CrosierCalendar,
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
      carteiraDestino: yup.mixed().required().typeError(),
      valorTotal: yup.number().required().typeError(),
    });
    SetFocus("descricao", 40);
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

          formData.descricao = "SANGRIA";
          formData.dtMoviment = moment(this.filters.dtMoviment).format();
          formData.tipoLancto = "/api/fin/tipoLancto/60";
          formData.categoria = "/api/fin/categoria/299";
          formData.valor = formData.valorTotal;
          formData.modo = "/api/fin/modo/11";
          formData.carteiraDestino = formData.carteiraDestino["@id"];
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

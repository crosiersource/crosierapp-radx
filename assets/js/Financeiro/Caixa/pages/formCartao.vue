<template>
  <CrosierFormS withoutCard @submitForm="this.submitForm" :formUrl="null" :listUrl="null">
    <div class="form-row">
      <CrosierInputInt label="Id" col="3" id="id" v-model="this.movimentacao.id" disabled />

      <CrosierDropdownEntity
        col="9"
        v-model="this.movimentacao.categoria"
        :error="this.movimentacaoErrors.categoria"
        entity-uri="/api/fin/categoria"
        optionLabel="descricaoMontadaTree"
        :optionValue="null"
        :orderBy="{ codigoOrd: 'ASC' }"
        :filters="{ codigoSuper: 1 }"
        label="Categoria"
        id="categoria"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="8"
        v-model="this.movimentacao.modo"
        :error="this.movimentacaoErrors.modo"
        entity-uri="/api/fin/modo"
        :optionValue="null"
        optionLabel="descricaoMontada"
        :filters="[
          { 'codigo[]': 9 }, // CARTÃO DE CRÉDITO
          { 'codigo[]': 10 }, // CARTÃO DE DÉBITO
        ]"
        :orderBy="{ codigo: 'ASC' }"
        label="Modo"
        id="modo"
        @update:modelValue="this.onChangeModo"
      />

      <CrosierDropdownEntity
        col="4"
        v-model="this.movimentacao.centroCusto"
        entity-uri="/api/fin/centroCusto"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Centro de Custo"
        id="centroCusto"
      />
    </div>

    <div class="form-row">
      <CrosierInputText
        col="9"
        label="Descrição"
        id="descricao"
        v-model="this.movimentacao.descricao"
        :error="this.movimentacaoErrors.descricao"
      />

      <CrosierCurrency
        col="3"
        id="valor"
        label="Valor"
        v-model="this.movimentacao.valor"
        :error="this.movimentacaoErrors.valor"
      />
    </div>

    <SacadoCedente v-if="this.$store.state.exibirCampos?.sacadoCedente" />

    <div class="card mt-3 mb-3" v-show="[9, 10].includes(this.movimentacao?.modo?.codigo)">
      <div class="card-body">
        <h5 class="card-title">Dados Cartão</h5>

        <div class="form-row">
          <CrosierDropdownEntity
            :col="this.movimentacao?.modo?.codigo === 9 ? 3 : 5"
            v-model="this.movimentacao.operadoraCartao"
            entity-uri="/api/fin/operadoraCartao"
            optionLabel="descricao"
            :optionValue="null"
            :filters="{ ativa: true }"
            :orderBy="{ descricao: 'ASC' }"
            label="Operadora"
            id="operadoraCartao"
          />

          <CrosierDropdownEntity
            ref="bandeiraCartao"
            :col="this.movimentacao?.modo?.codigo === 9 ? 3 : 4"
            v-model="this.movimentacao.bandeiraCartao"
            entity-uri="/api/fin/bandeiraCartao"
            optionLabel="descricao"
            :optionValue="null"
            :filters="{ modo: this.movimentacao?.modo ? this.movimentacao?.modo['@id'] : null }"
            :orderBy="{ descricao: 'ASC' }"
            label="Bandeira"
            id="bandeiraCartao"
            @change="this.onChangeBandeira"
          />

          <CrosierInputText
            label="Últ 4 Dígitos"
            col="3"
            id="numCartao"
            v-model="this.movimentacao.numCartao"
          />

          <CrosierInputInt
            v-if="this.movimentacao?.modo?.codigo === 9"
            label="Parcelas"
            col="3"
            id="qtdeParcelas"
            v-model="this.movimentacao.qtdeParcelas"
          />
        </div>
      </div>
    </div>

    <div class="form-row mt-2" v-if="this.$store.state.exibirCampos?.obs">
      <CrosierInputTextarea label="Obs" id="obs" v-model="this.movimentacao.obs" />
    </div>

    <div class="form-row">
      <ToggleButton
        v-model="this.$store.state.exibirCampos.sacadoCedente"
        @click="this.salvarExibirCampos"
        onLabel="Esconder campos Sacado/Cedente"
        offLabel="Exibir campos Sacado/Cedente"
        onIcon="pi pi-check"
        offIcon="pi pi-times"
      />
      <ToggleButton
        v-model="this.$store.state.exibirCampos.obs"
        @click="this.salvarExibirCampos"
        onLabel="Esconder campo Obs"
        offLabel="Exibir campo Obs"
        onIcon="pi pi-check"
        offIcon="pi pi-times"
        class="ml-1"
      />
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
} from "crosier-vue";
import { mapGetters, mapMutations, mapActions } from "vuex";
import moment from "moment";
import ToggleButton from "primevue/togglebutton";
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
    ToggleButton,
  },

  data() {
    return {
      schemaValidator: {},
    };
  },

  async mounted() {
    this.schemaValidator = yup.object().shape({
      categoria: yup.mixed().required().typeError(),
      modo: yup.mixed().required().typeError(),
      descricao: yup.mixed().required().typeError(),
      valor: yup.number().required().typeError(),
    });
  },

  methods: {
    ...mapMutations(["setLoading", "setMovimentacao", "setMovimentacaoErrors"]),
    ...mapActions(["doFilter", "salvarUltimaMovimentacaoNoLocalStorage", "salvarExibirCampos"]),

    moment(date) {
      return moment(date);
    },

    onChangeModo() {
      this.$nextTick(async () => {
        await this.$refs.bandeiraCartao.load();
      });
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
            formData.modo = formData.modo && formData.modo["@id"] ? formData.modo["@id"] : null;
            formData.categoria = formData.categoria["@id"];
            formData.tipoLancto = "/api/fin/tipoLancto/63";
            formData.dtMoviment = moment(this.filters.dtMoviment).format();
            formData.carteira = this.filters.carteira;
            formData.centroCusto =
              formData.centroCusto && formData.centroCusto["@id"]
                ? formData.centroCusto["@id"]
                : null;
            formData.operadoraCartao =
              formData.operadoraCartao && formData.operadoraCartao["@id"]
                ? formData.operadoraCartao["@id"]
                : null;
            formData.bandeiraCartao =
              formData.bandeiraCartao && formData.bandeiraCartao["@id"]
                ? formData.bandeiraCartao["@id"]
                : null;
            delete formData.cadeia;
            delete formData.fatura;
            delete formData.carteiraDestino;
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

    onChangeBandeira() {
      this.$nextTick(() => {
        this.movimentacao.descricao = this.movimentacao.bandeiraCartao.descricao;
      });
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

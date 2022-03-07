<template>
  <CrosierFormS :withoutCard="true" @submitForm="this.submitForm">
    <div class="form-row">
      <CrosierDropdownEntity
        col="6"
        v-model="this.preco.lista"
        :error="this.precoErrors.lista"
        entity-uri="/api/est/listaPreco"
        optionLabel="descricao"
        :orderBy="{ descricao: 'ASC' }"
        label="Lista de Preço"
        id="lista"
      />

      <CrosierDropdownEntity
        col="3"
        v-model="this.preco.unidade"
        :error="this.precoErrors.unidade"
        entity-uri="/api/est/unidade"
        optionLabel="label"
        :orderBy="{ label: 'ASC' }"
        :filters="{ atual: true }"
        label="Unidade"
        id="unidade"
      />

      <CrosierDropdownBoolean
        label="Atual"
        col="3"
        id="atual"
        v-model="this.preco.atual"
        :error="this.precoErrors.atual"
      />
    </div>

    <div class="form-row">
      <CrosierInputDecimal
        label="Margem"
        col="4"
        id="margem"
        v-model="this.preco.margem"
        :error="this.precoErrors.margem"
        append="%"
      />

      <CrosierInputInt
        label="Prazo de Compra"
        col="4"
        id="prazo"
        v-model="this.preco.prazo"
        :error="this.precoErrors.prazo"
        append="dias"
      />

      <CrosierInputDecimal
        label="Custo Financeiro"
        col="4"
        id="custoFinanceiro"
        v-model="this.preco.custoFinanceiro"
        :error="this.precoErrors.custoFinanceiro"
        append="%"
      />
    </div>

    <div class="form-row">
      <CrosierCalendar
        label="Dt Preço Custo"
        col="6"
        id="nome"
        v-model="this.preco.dtCusto"
        :error="this.precoErrors.dtCusto"
      />

      <CrosierCurrency
        label="Preço de Custo"
        col="6"
        id="precoCusto"
        v-model="this.preco.precoCusto"
        :error="this.precoErrors.precoCusto"
      />
    </div>

    <div class="form-row">
      <CrosierCalendar
        label="Dt Preço Venda"
        col="6"
        id="nome"
        v-model="this.preco.dtPrecoVenda"
        :error="this.precoErrors.dtPrecoVenda"
      />

      <CrosierCurrency
        label="Preço de Venda"
        col="6"
        id="precoCusto"
        v-model="this.preco.precoPrazo"
        :error="this.precoErrors.precoPrazo"
      />
    </div>

    <div class="form-row">
      <CrosierCurrency
        label="Preço Promo"
        col="6"
        id="precoPromo"
        v-model="this.preco.precoPromo"
        :error="this.precoErrors.precoPromo"
      />

      <CrosierCurrency
        label="Preço à Vista"
        col="6"
        id="precoVista"
        v-model="this.preco.precoVista"
        :error="this.precoErrors.precoVista"
      />
    </div>
  </CrosierFormS>

  <PrecosList v-if="this.produto?.id" ref="precosList" />
</template>

<script>
import * as yup from "yup";
import { mapGetters, mapMutations } from "vuex";
import {
  CrosierFormS,
  submitForm,
  CrosierDropdownEntity,
  CrosierInputDecimal,
  CrosierInputInt,
  CrosierCurrency,
  CrosierCalendar,
  CrosierDropdownBoolean,
} from "crosier-vue";
import PrecosList from "./precos_list";

export default {
  components: {
    CrosierFormS,
    CrosierDropdownBoolean,
    CrosierCalendar,
    CrosierCurrency,
    CrosierDropdownEntity,
    CrosierInputDecimal,
    CrosierInputInt,
    PrecosList,
  },

  data() {
    return {
      criarVincularPreco: false,
      schemaValidator: {},
    };
  },

  async mounted() {
    this.setLoading(true);

    this.schemaValidator = yup.object().shape({
      lista: yup.mixed().required().typeError(),
      unidade: yup.mixed().required().typeError(),
      atual: yup.boolean().required().typeError(),
      dtCusto: yup.date().required().typeError(),
      precoCusto: yup.number().required().typeError(),
      prazo: yup.number().required().typeError(),
      custoFinanceiro: yup.number().required().typeError(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setPreco", "setPrecoErrors"]),

    async submitForm() {
      this.setLoading(true);
      const rs = await submitForm({
        setUrlId: false,
        commitFormDataAfterSave: false,
        apiResource: "/api/est/produtoPreco",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "preco",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          formData.produto = this.produto["@id"];
          formData.margem /= 100.0;
        },
      });
      if ([200, 201].includes(rs?.status)) {
        await this.$store.dispatch("loadData");
        this.setPreco({});
      }
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({
      preco: "getPreco",
      precoErrors: "getPrecoErrors",
      produto: "getFields",
    }),
  },
};
</script>

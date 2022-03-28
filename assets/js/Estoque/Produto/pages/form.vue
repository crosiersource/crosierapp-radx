<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS :withoutCard="true" @submitForm="this.submitForm">
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierInputText
        label="Código"
        inputClass="notuppercase"
        col="2"
        id="codigo"
        v-model="this.fields.codigo"
      />

      <div class="col-md-1">
        <div class="form-group">
          <label for="btnBuscarProxCodigo" style="color: transparent">...</label>
          <button
            id="btnBuscarProxCodigo"
            type="button"
            @click="this.buscarProxCodigo"
            class="btn btn-block btn-sm btn-outline-secondary"
            title="Obter o próximo código"
          >
            <i class="fas fa-arrow-alt-circle-right"></i>
          </button>
        </div>
      </div>

      <CrosierInputText
        label="Nome"
        col="5"
        id="nome"
        v-model="this.fields.nome"
        :error="this.formErrors.nome"
      />

      <CrosierDropdown
        label="Status"
        col="2"
        id="status"
        v-model="this.fields.status"
        :options="[
          { label: 'Ativo', value: 'ATIVO' },
          { label: 'Inativo', value: 'INATIVO' },
        ]"
        :error="this.formErrors.status"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="4"
        v-model="this.fields.depto"
        :error="this.formErrors.depto"
        entity-uri="/api/est/depto"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Depto"
        id="depto"
        @update:modelValue="this.onChangeDepto"
      />

      <CrosierDropdownEntity
        col="4"
        ref="grupo"
        v-if="this.fields?.depto?.id"
        v-model="this.fields.grupo"
        :error="this.formErrors.grupo"
        entity-uri="/api/est/grupo"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ depto: this.fields.depto['@id'] }"
        label="Grupo"
        id="grupo"
        @update:modelValue="this.onChangeGrupo"
      />
      <div class="col-md-4" v-else>
        <div class="form-group">
          <label>Grupo</label>
          <Skeleton class="form-control" height="2rem" />
        </div>
      </div>

      <CrosierDropdownEntity
        col="4"
        ref="subgrupo"
        v-if="this.fields?.grupo?.id"
        v-model="this.fields.subgrupo"
        :error="this.formErrors.subgrupo"
        entity-uri="/api/est/subgrupo"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ grupo: this.fields.grupo['@id'] }"
        label="Subgrupo"
        id="subgrupo"
      />
      <div class="col-md-4" v-else>
        <div class="form-group">
          <label>Subgrupo</label>
          <Skeleton class="form-control" height="2rem" />
        </div>
      </div>
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="8"
        v-model="this.fields.fornecedor"
        :error="this.formErrors.fornecedor"
        entity-uri="/api/est/fornecedor"
        optionLabel="nomeFantasiaMontado"
        :orderBy="{ nomeFantasia: 'ASC' }"
        :filters="{ utilizado: true }"
        label="Fornecedor"
        id="fornecedor"
      />

      <CrosierDropdownEntity
        col="4"
        v-model="this.fields.unidadePadrao"
        :error="this.formErrors.unidadePadrao"
        entity-uri="/api/est/unidade"
        optionLabel="label"
        :optionValue="null"
        :orderBy="{ label: 'ASC' }"
        :filters="{ atual: true }"
        label="Unidade"
        id="unidadePadrao"
      />
    </div>

    <div class="form-row">
      <CrosierInputText
        label="EAN"
        col="3"
        id="ean"
        v-model="this.fields.ean"
        helpText="Código de barras"
      />
      <CrosierInputText
        label="Referência"
        col="4"
        id="referencia"
        v-model="this.fields.referencia"
      />
      <CrosierInputText label="Marca" col="5" id="marca" v-model="this.fields.marca" />
    </div>

    <div class="form-row">
      <CrosierInputDecimal
        v-if="this.fields?.unidadePadrao"
        label="Qtde Total"
        col="6"
        id="qtdeTotal"
        :decimais="this.fields.unidadePadrao.casasDecimais"
        v-model="this.fields.qtdeTotal"
        :error="this.formErrors.qtdeTotal"
        :disabled="true"
      />
      <div class="col-md-6" v-else>
        <div class="form-group">
          <label>Qtde Total</label>
          <Skeleton class="form-control" height="2rem" />
        </div>
      </div>

      <CrosierInputDecimal
        v-if="this.fields?.unidadePadrao"
        label="Qtde Mínima"
        col="6"
        id="qtdeMinima"
        :decimais="this.fields.unidadePadrao.casasDecimais"
        v-model="this.fields.qtdeMinima"
      />
      <div class="col-md-6" v-else>
        <div class="form-group">
          <label>Qtde Mínima</label>
          <Skeleton class="form-control" height="2rem" />
        </div>
      </div>
    </div>

    <div class="form-row">
      <CrosierCurrency
        label="Preço E-commerce"
        col="3"
        id="preco_ecommerce"
        v-model="this.fields.jsonData['preco_ecommerce']"
        :disabled="true"
      />

      <CrosierCurrency
        label="Preço Tabela"
        col="3"
        id="preco_tabela"
        v-model="this.fields.jsonData['preco_tabela']"
        :disabled="true"
      />

      <CrosierCurrency
        label="Preço c/ Desconto"
        col="3"
        id="preco_venda_com_desconto"
        v-model="this.fields.jsonData['preco_venda_com_desconto']"
        :disabled="true"
      />

      <CrosierCurrency
        label="Preço Promoção"
        col="3"
        id="preco_promocao"
        v-model="this.fields.jsonData['preco_promocao']"
        :disabled="true"
      />
    </div>
  </CrosierFormS>
</template>

<script>
import axios from "axios";
import Toast from "primevue/toast";
import Skeleton from "primevue/skeleton";
import * as yup from "yup";
import { mapGetters, mapMutations } from "vuex";
import {
  CrosierFormS,
  submitForm,
  CrosierDropdown,
  CrosierDropdownEntity,
  CrosierInputText,
  CrosierInputInt,
  SetFocus,
  CrosierInputDecimal,
  CrosierCurrency,
} from "crosier-vue";
import moment from "moment";

export default {
  components: {
    Toast,
    Skeleton,
    CrosierFormS,
    CrosierDropdown,
    CrosierDropdownEntity,
    CrosierInputText,
    CrosierInputInt,
    CrosierInputDecimal,
    CrosierCurrency,
  },

  data() {
    return {
      criarVincularFields: false,
      schemaValidator: {},
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.$store.dispatch("loadData");

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

    moment(date) {
      return moment(date);
    },

    async buscarProxCodigo() {
      const rs = await axios.get("/api/est/produto/findProxCodigo");
      this.fields.codigo = rs?.data?.DATA?.prox;
    },

    onChangeDepto() {
      this.$nextTick(async () => {
        this.fields.grupo = null;
        this.setLoading(true);
        if (this.$refs?.grupo) {
          await this.$refs.grupo.load();
        }
        this.setLoading(false);
      });
    },

    onChangeGrupo() {
      this.$nextTick(async () => {
        this.fields.subgrupo = null;
        this.setLoading(true);
        if (this.$refs?.subgrupo) {
          await this.$refs.subgrupo.load();
        }
        this.setLoading(false);
      });
    },

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/est/produto",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          formData.depto = formData.depto["@id"];
          formData.grupo = formData.grupo["@id"];
          formData.subgrupo = formData.subgrupo["@id"];
          formData.unidadePadrao = formData.unidadePadrao["@id"];

          delete formData.dtUltIntegracaoEcommerce;
          delete formData.precos;
          delete formData.saldos;
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

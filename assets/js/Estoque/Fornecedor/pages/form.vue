<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Fornecedor">
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" disabled />

      <CrosierInputText
        label="Código"
        inputClass="notuppercase"
        col="2"
        id="codigo"
        v-model="this.fields.codigo"
        :error="this.formErrors.codigo"
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
        col="6"
        id="nome"
        v-model="this.fields.nome"
        :error="this.formErrors.nome"
      />

      <CrosierSwitch col="1" id="ativo" label="Ativo" v-model="this.fields.utilizado" />
    </div>

    <div class="form-row">
      <CrosierInputCpfCnpj
        col="4"
        id="documento"
        v-model="this.fields.documento"
        :error="this.formErrors.documento"
      />

      <CrosierInputText
        label="Nome Fantasia"
        col="5"
        id="nomeFantasia"
        v-show="this.pj"
        v-model="this.fields.nomeFantasia"
        :error="this.formErrors.nomeFantasia"
      />

      <CrosierInputText
        label="Inscrição Estadual"
        col="3"
        id="inscricaoEstadual"
        v-show="this.pj"
        v-model="this.fields.inscricaoEstadual"
        :error="this.formErrors.inscricaoEstadual"
      />
    </div>

    <div class="form-row">
      <CrosierInputTelefone
        id="fone1"
        v-model="this.fields.fone1"
        :error="this.formErrors?.fone1"
        col="3"
        label="Fone 1"
      />

      <CrosierInputTelefone
        id="fone2"
        v-model="this.fields.fone2"
        :error="this.formErrors?.fone2"
        col="3"
        label="Fone 2"
      />

      <CrosierInputTelefone
        id="fone3"
        v-model="this.fields.fone3"
        :error="this.formErrors?.fone3"
        col="3"
        label="Fone 3"
      />

      <CrosierInputTelefone
        id="fone4"
        v-model="this.fields.fone4"
        :error="this.formErrors?.fone4"
        col="3"
        label="Fone 4"
      />
    </div>

    <div class="form-row">
      <CrosierInputText
        col="5"
        label="Logradouro"
        id="logradouro"
        v-model="this.fields.logradouro"
      />

      <CrosierInputText col="3" label="Número" id="numero" v-model="this.fields.numero" />

      <CrosierInputText
        col="4"
        label="Complemento"
        id="complemento"
        v-model="this.fields.complemento"
      />
    </div>

    <div class="form-row">
      <CrosierInputText col="3" label="Bairro" id="bairro" v-model="this.fields.bairro" />

      <CrosierInputCep col="2" v-model="this.fields.cep" @consultaCep="this.consultaCep" />

      <CrosierInputText col="4" label="Cidade" id="cidade" v-model="this.fields.cidade" />

      <CrosierDropdownUf id="estado" v-model="this.fields.estado" col="3" />
    </div>
  </CrosierFormS>
</template>

<script>
import axios from "axios";
import Toast from "primevue/toast";
import * as yup from "yup";
import { mapGetters, mapMutations } from "vuex";
import {
  CrosierDropdownUf,
  CrosierFormS,
  CrosierInputCep,
  CrosierInputCpfCnpj,
  CrosierInputInt,
  CrosierInputTelefone,
  CrosierInputText,
  CrosierSwitch,
  SetFocus,
  submitForm,
} from "crosier-vue";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierInputText,
    CrosierInputInt,
    CrosierInputCpfCnpj,
    CrosierInputTelefone,
    CrosierInputCep,
    CrosierDropdownUf,
    CrosierSwitch,
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
      codigo: yup.number().required().typeError(),
      nome: yup.string().required().typeError(),
      utilizado: yup.boolean().required().typeError(),
    });

    SetFocus("codigo", 100);

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async buscarProxCodigo() {
      const rs = await axios.get("/api/est/fornecedor/findProxCodigo");
      this.fields.codigo = rs?.data?.DATA?.prox;
    },

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/est/fornecedor",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        // fnBeforeSave: (formData) => {
        //
        // },
      });
      this.setLoading(false);
    },

    consultaCep(rs) {
      if (rs) {
        this.setFields({
          ...this.fields,
          ...{
            bairro: rs?.bairro,
            cep: rs?.cep,
            cidade: rs?.localidade,
            estado: rs?.uf,
            logradouro: rs?.logradouro,
          },
        });
        SetFocus("numero");
      }
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),

    pj() {
      return this.fields.documento && this.fields.documento.length > 14;
    },
  },
};
</script>

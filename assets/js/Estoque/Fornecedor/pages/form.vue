<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Fornecedor">
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" :disabled="true" />

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
        col="5"
        id="nome"
        v-model="this.fields.nome"
        :error="this.formErrors.nome"
      />

      <CrosierDropdownBoolean
        label="Utilizado"
        col="2"
        id="utilizado"
        v-model="this.fields.utilizado"
        :error="this.formErrors.utilizado"
      />
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
        v-model="this.fields.nomeFantasia"
        :error="this.formErrors.nomeFantasia"
      />

      <CrosierInputText
        label="Inscrição Estadual"
        col="3"
        id="inscricaoEstadual"
        v-model="this.fields.inscricaoEstadual"
        :error="this.formErrors.inscricaoEstadual"
      />
    </div>

    <div class="row mt-4">
      <CrosierInputText
        col="5"
        label="Logradouro"
        id="logradouro"
        v-model="this.fields.jsonData.endereco.logradouro"
      />
      <CrosierInputText
        col="3"
        label="Número"
        id="numero"
        v-model="this.fields.jsonData.endereco.numero"
      />
      <CrosierInputText
        col="4"
        label="Complemento"
        id="complemento"
        v-model="this.fields.jsonData.complemento"
      />
    </div>
    <div class="row">
      <CrosierInputText
        label="Bairro"
        col="3"
        id="bairro"
        v-model="this.fields.jsonData.endereco.bairro"
      />

      <CrosierInputCep
        col="2"
        v-model="this.fields.jsonData.endereco.cep"
        @consultaCep="this.consultaCep"
      />

      <CrosierInputText
        label="Cidade"
        col="4"
        id="cidade"
        v-model="this.fields.jsonData.endereco.cidade"
      />

      <CrosierDropdownUf id="uf" col="3" v-model="this.fields.jsonData.endereco.estado" />
    </div>
  </CrosierFormS>
</template>

<script>
import axios from "axios";
import Toast from "primevue/toast";
import * as yup from "yup";
import { mapGetters, mapMutations } from "vuex";
import {
  CrosierFormS,
  submitForm,
  CrosierDropdownBoolean,
  CrosierInputText,
  CrosierInputInt,
  CrosierInputCep,
  CrosierDropdownUf,
  SetFocus,
  CrosierInputCpfCnpj,
} from "crosier-vue";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdownBoolean,
    CrosierInputText,
    CrosierInputInt,
    CrosierInputCpfCnpj,
    CrosierInputCep,
    CrosierDropdownUf,
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
        this.fields.jsonData = {
          ...this.fields.jsonData,
          ...{
            endereco: {
              bairro: rs?.bairro,
              cep: rs?.cep,
              cidade: rs?.localidade,
              estado: rs?.uf,
              logradouro: rs?.logradouro,
            },
          },
        };
      }
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),
  },
};
</script>

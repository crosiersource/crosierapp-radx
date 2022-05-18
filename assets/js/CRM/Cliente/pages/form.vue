<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Cliente">
    <div class="form-row">
      <CrosierInputId col="2" id="id" v-model="this.fields.id" />

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
        :label="this.pj ? 'Razão Social' : 'Nome'"
        col="5"
        id="nome"
        v-model="this.fields.nome"
        :error="this.formErrors.nome"
      />

      <CrosierSwitch col="2" v-model="this.fields.ativo" label="Ativo" />
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

    <div class="row mt-4">
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
    <div class="row">
      <CrosierInputText label="Bairro" col="3" id="bairro" v-model="this.fields.bairro" />

      <CrosierInputCep col="2" v-model="this.fields.cep" @consultaCep="this.consultaCep" />

      <CrosierInputText label="Cidade" col="4" id="cidade" v-model="this.fields.cidade" />

      <CrosierDropdownUf id="uf" col="3" v-model="this.fields.estado" />
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import * as yup from "yup";
import {
  CrosierFormS,
  submitForm,
  CrosierInputText,
  CrosierInputId,
  CrosierInputCep,
  CrosierDropdownUf,
  CrosierInputCpfCnpj,
  CrosierInputTelefone,
  CrosierSwitch,
  SetFocus,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import axios from "axios";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierInputText,
    CrosierInputId,
    CrosierSwitch,
    CrosierInputCpfCnpj,
    CrosierInputTelefone,
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
      codigo: yup.string().required().typeError(),
      nome: yup.string().required().typeError(),
      ativo: yup.boolean().required().typeError(),
    });

    SetFocus("codigo");

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/crm/cliente",
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

    async buscarProxCodigo() {
      const rs = await axios.get("/api/est/cliente/findProxCodigo");
      this.fields.codigo = rs?.data?.DATA?.prox;
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

<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS
    listUrl="/fin/movimentacao/aPagarReceber/list"
    formUrl="/fin/movimentacao/aPagarReceber/form"
    @submitForm="this.submitForm"
    titulo="Movimentação a Pagar/Receber"
  >
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierDropdownEntity
        col="10"
        v-model="this.fields.categoria"
        entity-uri="/api/fin/categoria"
        optionLabel="descricaoMontadaTree"
        :optionValue="null"
        :orderBy="{ codigoOrg: 'ASC' }"
        label="Categoria"
        id="categoria"
      />
    </div>
    <div class="form-row">
      <CrosierDropdownEntity
        col="6"
        v-model="this.fields.carteira"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ abertas: true }"
        label="Carteira"
        id="carteira"
      />

      <CrosierDropdownEntity
        col="3"
        v-model="this.fields.modo"
        entity-uri="/api/fin/modo"
        optionLabel="descricaoMontada"
        :orderBy="{ codigo: 'ASC' }"
        label="Modo"
        id="modo"
      />

      <CrosierDropdownEntity
        col="3"
        v-model="this.fields.centroCusto"
        entity-uri="/api/fin/centroCusto"
        optionLabel="descricaoMontada"
        :orderBy="{ codigo: 'ASC' }"
        label="Centro de Custo"
        id="centroCusto"
      />
    </div>

    <div class="form-row" v-if="this.fields.categoria && this.fields.categoria.codigoSuper === 1">
      <!-- Em um recebimento, o sacado é um terceiro paganado para uma das filiais (cedente) -->
      <CrosierDropdown
        col="6"
        v-model="this.fields.cedente"
        :options="this.filiais"
        :orderBy="{ codigo: 'ASC' }"
        label="Cedente"
        id="dd_cedente"
        helpText="Quem recebe o valor"
      />
      
      <CrosierAutoComplete
        label="Sacado"
        id="ac_sacado"
        col="6"
        v-model="this.fields.sacado"
        :values="this.sacados"
        @complete="this.pesquisarSacado"
        field="text"
        helpText="Quem paga o valor"
      >
        <template #item="r"> {{ r.item.text }}</template>
      </CrosierAutoComplete>
    </div>

    <div class="form-row" v-if="this.fields.categoria && this.fields.categoria.codigoSuper === 2">
      <!-- Em um pagamento, o sacado é uma das filiais pagando para um terceiro (cedente) -->
      <CrosierAutoComplete
        col="6"
        label="Cedente"
        id="ac_cedente"
        v-model="this.fields.cedente"
        :values="this.cedentes"
        @complete="this.pesquisarCedente"
        field="text"
        helpText="Quem recebe o valor"
      >
        <template #item="r"> {{ r.item.text }}</template>
      </CrosierAutoComplete>
      
      <CrosierDropdown
        col="6"
        v-model="this.fields.sacado"
        :options="this.filiais"
        :orderBy="{ codigo: 'ASC' }"
        label="Sacado"
        id="sacado"
        helpText="Quem paga o valor"
      />
      
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import * as yup from "yup";
import {
  CrosierAutoComplete,
  CrosierCalendar,
  CrosierCurrency,
  CrosierDropdown,
  CrosierDropdownBanco,
  CrosierDropdownEntity,
  CrosierFormS,
  CrosierInputInt,
  CrosierInputText,
  submitForm,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import axios from "axios";

export default {
  components: {
    CrosierDropdownEntity,
    CrosierCurrency,
    CrosierDropdownBanco,
    Toast,
    CrosierFormS,
    CrosierDropdown,
    CrosierInputText,
    CrosierInputInt,
    CrosierCalendar,
    CrosierAutoComplete,
  },

  data() {
    return {
      schemaValidator: {},
      sacados: null,
      cedentes: null,
      filiais: null,
    };
  },

  async mounted() {
    this.setLoading(true);

    this.$store.dispatch("loadData");
    this.schemaValidator = yup.object().shape({
      categoria: yup.mixed().required().typeError(),
    });

    const rs = await axios.get("/api/fin/movimentacao/filiais/", {
      headers: {
        "Content-Type": "application/ld+json",
      },
      validateStatus(status) {
        return status < 500;
      },
    });
    if (rs?.data?.RESULT === "OK") {
      this.filiais = rs.data.DATA;
    } else {
      console.error(rs?.data?.MSG);
      this.$toast.add({
        severity: "error",
        summary: "Erro",
        detail: rs?.data?.MSG,
        life: 5000,
      });
    }

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async pesquisarSacado(event) {
      console.log(event);
      try {
        const response = await axios.get(
          `/api/fin/movimentacao/findSacadoOuCedente/?term=${event.query}`
        );

        console.log(response);
        if (response.status === 200) {
          this.sacados = response.data.DATA;
        }
      } catch (err) {
        console.error(err);
      }
    },

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/movimentacao",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        // fnBeforeSave: (formData) => {
        //   formData.caixaResponsavel = formData?.caixaResponsavel
        //     ? formData.caixaResponsavel["@id"]
        //     : null;
        // },
      });
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),
  },
};
</script>

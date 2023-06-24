<template>
  <Toast position="bottom-right" class="mb-5" />
  <ConfirmDialog></ConfirmDialog>

  <CrosierFormS
    @submitForm="this.submitForm"
    :listUrl="null"
    titulo="Movimentação"
    subtitulo="Transferência entre Carteiras"
  >
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" disabled />

      <CrosierInputText
        col="6"
        label="Descrição"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.fieldsErrors.descricao"
      />

      <CrosierDropdownEntity
        col="4"
        v-model="this.fields.modo"
        :error="this.fieldsErrors.modo"
        entity-uri="/api/fin/modo"
        :optionValue="null"
        optionLabel="descricaoMontada"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ modoDeTransfPropria: true }"
        label="Modo"
        id="modo"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="6"
        v-model="this.fields.carteira"
        :error="this.fieldsErrors.carteira"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ concreta: true, atual: true }"
        label="Carteira (Origem)"
        id="carteira"
        @change="this.onChangeCarteiras"
      />

      <CrosierDropdownEntity
        col="6"
        v-model="this.fields.carteiraDestino"
        :error="this.fieldsErrors.carteiraDestino"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ concreta: true, atual: true }"
        label="Carteira (Destino)"
        id="carteiraDestino"
        @change="this.onChangeCarteiras"
      />
    </div>

    <div class="form-row">
      <CrosierCalendar
        label="Dt Moviment"
        col="7"
        id="dtMoviment"
        v-model="this.fields.dtMoviment"
        :error="this.fieldsErrors.dtMoviment"
        @focus="this.onDtMovimentFocus"
      />

      <CrosierCurrency
        label="Valor"
        col="5"
        id="valorTotal"
        v-model="this.fields.valorTotal"
        :error="this.fieldsErrors.valorTotal"
      />
    </div>

    <div class="form-row mt-2">
      <CrosierInputTextarea label="Obs" id="obs" v-model="this.fields.obs" />
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
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
  api,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import moment from "moment";

export default {
  components: {
    CrosierDropdownEntity,
    CrosierCurrency,
    CrosierCalendar,
    Toast,
    CrosierFormS,
    CrosierInputText,
    CrosierInputInt,
    CrosierInputTextarea,
    ConfirmDialog,
  },

  data() {
    return {
      schemaValidator: {},
      sacadosOuCedentes: null,
      filiais: null,
      dtVencto_cache: null,
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.$store.dispatch("loadData");
    this.schemaValidator = yup.object().shape({
      carteira: yup.mixed().required().typeError(),
      carteiraDestino: yup.mixed().required().typeError(),
      descricao: yup.mixed().required().typeError(),
      dtMoviment: yup.date().required().typeError(),
      valorTotal: yup.number().required().typeError(),
    });

    SetFocus("descricao");

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    moment(date) {
      return moment(date);
    },

    onChangeCarteiras() {
      this.$nextTick(() => {
        if (this.fields?.carteira?.id === this.fields?.carteiraDestino?.id) {
          this.fields.carteiraDestino = null;
        }
      });
    },

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/movimentacao",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          delete formData.cadeia;
          delete formData.centroCusto;
          delete formData.movimentacaoOposta;
          delete formData.grupoItem;
          delete formData.fatura;

          formData.tipoLancto = formData.tipoLancto["@id"];
          formData.categoria = formData.categoria["@id"];
          formData.valor = formData.valorTotal;
          formData.modo = formData.modo["@id"];
          formData.carteira = formData.carteira["@id"];
          formData.carteiraDestino = formData.carteiraDestino["@id"];
        },
      });
      this.setLoading(false);
    },

    onDtMovimentFocus() {
      if (!this.fields.dtMoviment) {
        this.fields.dtMoviment = new Date();
      }
    },

    teste() {
      this.$toast.add({
        severity: "success",
        summary: "Sucesso",
        detail: "teste",
        life: 5000,
      });
    },

    deletar() {
      this.$confirm.require({
        acceptLabel: "Sim",
        rejectLabel: "Não",
        message: "Confirmar a operação?",
        header: "Atenção!",
        icon: "pi pi-exclamation-triangle",
        group: "confirmDialog_crosierListS",
        accept: async () => {
          this.setLoading(true);
          try {
            const deleteUrl = `${this.apiResource}/${this.fields.id}`;
            const rsDelete = await api.delete(deleteUrl);
            if (!rsDelete) {
              throw new Error("rsDelete n/d");
            }
            if (rsDelete?.status === 204) {
              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                detail: "Registro deletado com sucesso",
                life: 5000,
              });
              await this.doFilter();
            } else if (rsDelete?.data && rsDelete.data["hydra:description"]) {
              throw new Error(`status !== 204: ${rsDelete?.data["hydra:description"]}`);
            } else if (rsDelete?.statusText) {
              throw new Error(`status !== 204: ${rsDelete?.statusText}`);
            } else {
              throw new Error("Erro ao deletar (erro n/d, status !== 204)");
            }
          } catch (e) {
            console.error(e);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: "Ocorreu um erro ao deletar",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", fieldsErrors: "getFieldsErrors" }),
  },
};
</script>
<style scoped>
.camposEmpilhados {
  margin-top: -15px;
}
</style>

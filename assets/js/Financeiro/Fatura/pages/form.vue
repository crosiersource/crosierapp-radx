<template>
  <Toast position="bottom-right" class="mb-5" />
  <ConfirmDialog group="confirmDialog_crosierListS"></ConfirmDialog>

  <CrosierFormS @submitForm="this.submitForm" titulo="Fatura">
    <template #btns>
      <a
        v-if="this.fatura?.id"
        role="button"
        class="btn btn-outline-primary ml-1"
        title="Lançar uma nova movimentação nesta fatura"
        :href="'/v/fin/movimentacao/aPagarReceber/form?fatura=' + this.fatura.id"
      >
        <i class="fas fa-plus"></i> Nova Movimentação
      </a>
    </template>
    <div class="form-row">
      <CrosierInputId label="Id da Fatura" col="2" id="id" v-model="this.fatura.id" disabled />

      <CrosierInputText col="7" label="Descrição" v-model="this.fatura.descricao" disabled />

      <CrosierCurrency label="Total da Fatura" col="3" v-model="this.fatura.valorTotal" disabled />
    </div>

    <div class="form-row">
      <CrosierInputTextarea
        col="6"
        label="Observações"
        id="obs"
        v-model="this.fatura.obs"
        inputClass="uppercase"
        disabled
      />

      <CrosierSwitch col="1" label="Quitada" v-model="this.fatura.quitada" disabled />

      <CrosierSwitch col="1" label="Fechada" v-model="this.fatura.fechada" disabled />

      <CrosierSwitch col="1" label="Cancelada" v-model="this.fatura.cancelada" disabled />

      <CrosierCurrency label="Saldo" col="3" v-model="this.fatura.saldo" disabled />
    </div>

    <div class="form-row">
      <CrosierInputText
        col="6"
        label="Sacado"
        v-model="this.fatura.sacado"
        disabled
        helpText="Quem paga o valor"
      />

      <CrosierInputText
        col="6"
        label="Cedente"
        v-model="this.fatura.cedente"
        disabled
        helpText="Quem recebe o valor"
      />
    </div>

    <Movimentacoes v-if="this.fatura?.id" />
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
// import * as yup from "yup";
import {
  api,
  CrosierInputId,
  CrosierCurrency,
  CrosierFormS,
  CrosierInputText,
  CrosierInputTextarea,
  CrosierSwitch,
  submitForm,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import axios from "axios";
import moment from "moment";
import Movimentacoes from "./movimentacoes";

export default {
  components: {
    CrosierInputId,
    CrosierCurrency,
    Toast,
    CrosierFormS,
    CrosierInputText,
    CrosierInputTextarea,
    ConfirmDialog,
    CrosierSwitch,
    Movimentacoes,
  },

  data() {
    return {
      // schemaValidator: {},
      sacadosOuCedentes: null,
      filiais: null,
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.$store.dispatch("loadData");

    if (!this.fatura.dtMoviment) {
      this.fatura.dtMoviment = new Date();
    }

    // this.schemaValidator = yup.object().shape({
    // });

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
    ...mapMutations(["setLoading", "setFatura", "setFaturaErrors"]),

    moment(date) {
      return moment(date);
    },

    async submitForm() {
      this.setLoading(true);
      try {
        await submitForm({
          apiResource: "/api/fin/fatura",
          // schemaValidator: this.schemaValidator,
          $store: this.$store,
          formDataStateName: "fatura",
          $toast: this.$toast,
          fnBeforeSave: (formData) => {
            delete formData.movimentacoes;
          },
        });
      } catch (e) {
        console.error(e);
      }
      this.setLoading(false);
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
            const deleteUrl = `${this.apiResource}/${this.fatura.id}`;
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
    ...mapGetters({ fatura: "getFatura", faturaErrors: "getFaturaErrors" }),
  },
};
</script>
<style scoped>
.camposEmpilhados {
  margin-top: -15px;
}
</style>

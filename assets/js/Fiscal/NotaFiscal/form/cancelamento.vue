<template>
  <Dialog
    position="top"
    style="margin-top: 80px"
    v-model:visible="this.$store.state.exibirDialogCancelamento"
    :style="{ width: '55vw' }"
    modal
    ref="dialog"
  >
    <template #header>
      <div class="w-100">
        <div class="row">
          <div class="col-6">
            <h3>Cancelamento</h3>
          </div>
        </div>
      </div>
    </template>

    <CrosierFormS @submitForm="this.cancelar" withoutCard semBotaoSalvar>
      <div class="form-row">
        <CrosierInputText
          id="motivoCancelamento"
          v-model="this.notaFiscal.motivoCancelamento"
          :error="this.errors.motivoCancelamento"
          label="Motivo"
        />
      </div>

      <div class="row mt-3">
        <div class="col text-right">
          <button class="btn btn-sm btn-warning" style="width: 12rem" type="submit">
            <i class="fas fa-ban"></i> Cancelar
          </button>
        </div>
      </div>
    </CrosierFormS>
  </Dialog>
</template>

<script>
import Dialog from "primevue/dialog";
import * as yup from "yup";
import { mapActions, mapGetters, mapMutations } from "vuex";
import { api, CrosierFormS, CrosierInputText } from "crosier-vue";

export default {
  name: "cancelamento",

  components: {
    Dialog,
    CrosierFormS,
    CrosierInputText,
  },

  data() {
    return {
      schemaValidator: {},
    };
  },

  async mounted() {
    this.schemaValidator = yup.object().shape({
      motivoCancelamento: yup.string().required().typeError(),
    });
  },

  methods: {
    ...mapMutations(["setLoading", "setNotaFiscalErrors"]),

    ...mapActions(["loadData"]),

    async cancelar() {
      const el =
        "body > div.p-dialog-mask.p-component-overlay.p-component-overlay-enter.p-dialog-top";

      const elemento = document.querySelector(el);
      if (elemento) {
        elemento.style.setProperty("z-index", "1002", "important");
      }

      if (
        !this.notaFiscal.motivoCancelamento ||
        this.notaFiscal.motivoCancelamento.length < 15 ||
        this.notaFiscal.motivoCancelamento.length > 255
      ) {
        this.$toast.add({
          severity: "error",
          summary: "Erro",
          detail: "Motivo deve ter entre 15 e 255 caracteres",
          life: 5000,
        });
        return;
      }

      this.setLoading(true);

      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        group: "confirmDialog_crosierListS",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          try {
            const rs = await api.post(`/api/fis/notaFiscal/cancelar/${this.notaFiscal.id}`, {
              motivoCancelamento: this.notaFiscal.motivoCancelamento,
            });
            console.log(rs);
            if (rs?.status === 200) {
              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                detail: "Cancelado com sucesso!",
                life: 5000,
              });
              await this.loadData();
              this.$store.state.exibirDialogCancelamento = false;
            }
          } catch (e) {
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: e?.response?.data?.EXCEPTION_MSG ?? "Ocorreu um erro ao efetuar a operação",
              life: 5000,
            });
          }
        },
      });

      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      notaFiscal: "getNotaFiscal",
      errors: "getNotaFiscalErrors",
    }),
  },
};
</script>

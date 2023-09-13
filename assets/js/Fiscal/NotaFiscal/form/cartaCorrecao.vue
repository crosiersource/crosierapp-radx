<template>
  <Dialog
    position="top"
    style="margin-top: 80px"
    v-model:visible="this.$store.state.exibirDialogCartaCorrecao"
    :style="{ width: '55vw' }"
    modal
    ref="dialog"
  >
    <template #header>
      <div class="w-100">
        <div class="row">
          <div class="col-6">
            <h3>Carta de Correção</h3>
          </div>
        </div>
      </div>
    </template>

    <CrosierFormS @submitForm="this.enviar" withoutCard semBotaoSalvar>
      <div class="form-row">
        <CrosierInputTextarea
          id="cartaCorrecao"
          v-model="this.notaFiscalCartaCorrecao.cartaCorrecao"
          :error="this.errors.cartaCorrecao"
          label="Descrição da Correção"
        />
      </div>

      <div class="row mt-3" v-if="this.notaFiscal.permiteCartaCorrecao">
        <div class="col text-right">
          <button class="btn btn-sm btn-primary" style="width: 12rem" type="submit">
            <i class="fas fa-save"></i> Enviar
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
import { submitForm, CrosierFormS, CrosierInputTextarea } from "crosier-vue";

export default {
  name: "cartaCorrecao",

  components: {
    Dialog,
    CrosierFormS,
    CrosierInputTextarea,
  },

  data() {
    return {
      schemaValidator: {},
    };
  },

  async mounted() {
    this.schemaValidator = yup.object().shape({
      cartaCorrecao: yup.string().required().typeError(),
    });
  },

  methods: {
    ...mapMutations([
      "setLoading",
      "setNotaFiscalCartaCorrecao",
      "setNotaFiscalCartaCorrecaoErrors",
    ]),

    ...mapActions(["loadData"]),

    async enviar() {
      const el =
        "body > div.p-dialog-mask.p-component-overlay.p-component-overlay-enter.p-dialog-top";

      const elemento = document.querySelector(el);
      if (elemento) {
        elemento.style.setProperty("z-index", "1002", "important");
      }

      if (
        !this.notaFiscalCartaCorrecao.cartaCorrecao ||
        this.notaFiscalCartaCorrecao.cartaCorrecao.length < 15 ||
        this.notaFiscalCartaCorrecao.cartaCorrecao.length > 1000
      ) {
        this.$toast.add({
          severity: "error",
          summary: "Erro",
          detail: "A 'Descrição da Correção' deve ter entre 15 e 1000 caracteres",
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
          const rs = await submitForm({
            apiResource: "/api/fis/notaFiscalCartaCorrecao",
            schemaValidator: this.schemaValidator,
            $store: this.$store,
            formDataStateName: "notaFiscalCartaCorrecao",
            $toast: this.$toast,
            setUrlId: false,
            fnBeforeSave: (formData) => {
              formData.notaFiscal = this.notaFiscal["@id"];
            },
          });

          if ([200, 201].includes(rs?.status)) {
            this.loadData();
            this.$store.state.dtCartasCorrecaoKey++;
            this.$store.state.exibirDialogCartaCorrecao = false;
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
      notaFiscalCartaCorrecao: "getNotaFiscalCartaCorrecao",
      errors: "getNotaFiscalCartaCorrecaoErrors",
    }),
  },
};
</script>

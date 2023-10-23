<template>
  <div class="card mt-2">
    <div class="card-body" v-if="!this.notaFiscal?.jsonData?.fin_fatura_id">
      <h5 class="card-title">Lançar Movimentações</h5>

      <div class="form-row">
        <CrosierDropdownEntity
          label="Carteira"
          col="4"
          v-model="this.carteira"
          entity-uri="/api/fin/carteira"
          optionValue="id"
          optionLabel="descricaoMontada"
          :orderBy="{ codigo: 'ASC' }"
          :filters="{ abertas: true, atual: true }"
          selectFirst
          id="carteira"
        />

        <CrosierDropdownEntity
          col="7"
          v-model="this.categoria"
          entity-uri="/api/fin/categoria"
          optionValue="id"
          optionLabel="descricaoMontadaTree"
          :orderBy="{ codigoOrd: 'ASC' }"
          label="Categoria"
          id="categoria"
        />

        <CrosierButton
          @click="this.lancar"
          cor="outline-success"
          icon="fas fa-save"
          label="Lançar"
        />
      </div>
    </div>

    <div v-else class="card-body">
      <h5 class="card-title">Fatura (Financeiro)</h5>
      <div class="form-row">
        <CrosierInputText
          col="4"
          label="Fatura"
          v-model="this.notaFiscal.jsonData.fin_fatura_id"
          appendButtonIcon="fas fa-file-invoice"
          appendButtonLinkHref="'/v/fin/fatura/form/' + this.notaFiscal.jsonData.fin_fatura_id"
          disabled
        />

        <CrosierButton
          col="2"
          @click="this.enviarFaturaParaReprocessamento"
          cor="warning"
          icon="fas fa-sync"
          label="Reprocessar Fatura"
          title="Enviar fatura para reprocessamento"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapMutations, mapActions } from "vuex";
import { CrosierDropdownEntity, CrosierButton, CrosierInputText, api } from "crosier-vue";

export default {
  components: {
    CrosierDropdownEntity,
    CrosierButton,
    CrosierInputText,
  },

  data() {
    return {
      carteira: null,
      categoria: null,
    };
  },

  methods: {
    ...mapMutations(["setLoading"]),
    ...mapActions(["loadData"]),

    lancar() {
      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        group: "confirmDialog_crosierListS",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          try {
            const rs = await api.post(
              `/api/fis/notaFiscal/lancarDuplicatas/${this.notaFiscal.id}/${this.carteira}/${this.categoria}`
            );
            if (rs?.status === 200) {
              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                detail: "Duplicatas lançadas com sucesso!",
                life: 5000,
              });
              await this.loadData();
            } else {
              throw new Error();
            }
          } catch (e) {
            console.error("Erro ao lançar duplicatas", e);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: e?.response?.data?.EXCEPTION_MSG ?? "Ocorreu um erro ao efetuar a operação",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },

    enviarFaturaParaReprocessamento() {
      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        group: "confirmDialog_crosierListS",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          try {
            const rs = await api.post(
              `/api/fis/notaFiscal/enviarFaturaParaReprocessamento/${this.notaFiscal.id}`
            );
            if (rs?.status === 200) {
              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                detail: "Fatura enviada para reprocessamento com sucesso!",
                life: 5000,
              });
              await this.loadData();
            } else {
              throw new Error();
            }
          } catch (e) {
            console.error("Erro ao lançar duplicatas", e);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: e?.response?.data?.EXCEPTION_MSG ?? "Ocorreu um erro ao efetuar a operação",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },
  },

  computed: {
    ...mapGetters({
      notaFiscal: "getNotaFiscal",
    }),
  },
};
</script>

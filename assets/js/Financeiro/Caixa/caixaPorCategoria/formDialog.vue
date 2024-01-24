<template>
  <Dialog
    :breakpoints="{ '960px': '75vw', '640px': '100vw' }"
    :style="{ width: '50vw' }"
    :header="titulo"
    v-model:visible="this.$store.state.exibeDialogMovimentacao"
    :modal="true"
    :autoZIndex="false"
  >
    <FormSelecionarTipoMovimentacao v-if="!this.tipoMovimentacao" />
    <FormEmEspecie v-if="this.tipoMovimentacao === 'EM_ESPECIE'" />
    <FormCartao v-if="this.tipoMovimentacao === 'CARTAO'" />
    <FormEntradaPorTransf v-if="this.tipoMovimentacao === 'ENTRADA_POR_TRANSF'" />
    <FormSaida v-if="this.tipoMovimentacao === 'SAIDA'" />
    <FormSangria v-if="this.tipoMovimentacao === 'SANGRIA'" />
    <FormAjusteDeCaixa v-if="this.tipoMovimentacao === 'AJUSTE_DE_CAIXA'" />
  </Dialog>
</template>

<script>
import Dialog from "primevue/dialog";
import { mapGetters } from "vuex";
import FormSelecionarTipoMovimentacao from "./formSelecionarTipoMovimentacao.vue";
import FormEmEspecie from "./formEmEspecie.vue";
import FormCartao from "./formCartao.vue";
import FormEntradaPorTransf from "./formEntradaPorTransf.vue";
import FormSaida from "./formSaida.vue";
import FormSangria from "./formSangria.vue";
import FormAjusteDeCaixa from "./formAjusteDeCaixa.vue";

export default {
  components: {
    Dialog,
    FormSelecionarTipoMovimentacao,
    FormEmEspecie,
    FormCartao,
    FormEntradaPorTransf,
    FormSaida,
    FormSangria,
    FormAjusteDeCaixa,
  },

  methods: {},

  computed: {
    ...mapGetters({ tipoMovimentacao: "getTipoMovimentacao" }),

    titulo() {
      switch (this.tipoMovimentacao) {
        case "EM_ESPECIE":
          return "Lançamento de Entrada em Espécie";
        case "CARTAO":
          return "Lançamento de Entrada por Cartão";
        case "ENTRADA_POR_TRANSF":
          return "Lançamento de Entrada por Transferência/Depósito";
        case "SAIDA":
          return "Lançamento de Saída";
        case "SANGRIA":
          return "Lançamento de Sangria";
        case "AJUSTE_DE_CAIXA":
          return "Lançamento de Ajuste de Caixa";
        default:
          return "Selecione o tipo de movimentação";
      }
    },
  },
};
</script>

<template>
  <button
    type="button"
    class="col-12 mb-2 btn btn-outline-success"
    @click="setTipoMovimentacao('EM_ESPECIE')"
  >
    <i class="fas fa-hand-holding-usd fa-lg"></i> Entrada em Espécie
  </button>

  <button
    type="button"
    class="col-12 mb-2 btn btn-outline-primary"
    @click="setTipoMovimentacao('CARTAO')"
  >
    <i class="fas fa-credit-card fa-lg"></i> Entrada por Cartão
  </button>

  <button
    type="button"
    class="col-12 mb-2 btn btn-outline-info"
    @click="setTipoMovimentacao('ENTRADA_POR_TRANSF')"
  >
    <i class="fas fa-level-down-alt fa-rotate-270"></i> Entrada por Transferência
  </button>

  <button class="col-12 mb-2 btn btn-outline-danger" @click="setTipoMovimentacao('SAIDA')">
    <i class="fas fa-money-bill-wave-alt"></i> Saída em Espécie
  </button>

  <button
    type="button"
    class="col-12 mb-2 btn btn-outline-secondary"
    @click="setTipoMovimentacao('SANGRIA')"
  >
    <i class="fas fa-external-link-alt fa-lg"></i> Sangria
  </button>

  <button
    type="button"
    class="col-12 mb-2 btn btn-outline-dark"
    @click="setTipoMovimentacao('AJUSTE_DE_CAIXA')"
  >
    <i class="fas fa-check-double"></i> Ajuste de Caixa
  </button>
</template>

<script>
import { mapMutations } from "vuex";

export default {
  name: "formTipoMovimentacao",

  methods: {
    ...mapMutations(["setTipoMovimentacao"]),
  },
};
</script>
<style scoped>
.camposEmpilhados {
  margin-top: -15px;
}
</style>

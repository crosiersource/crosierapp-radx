<template>
  <div class="row">
    <button
      type="button"
      class="col-2 m-3 btn btn-lg btn-outline-success"
      @click="setTipoMovimentacao('EM_ESPECIE')"
    >
      <i class="fas fa-hand-holding-usd fa-lg"></i> <br />Entrada em Espécie
    </button>

    <button
      type="button"
      class="col-2 m-3 btn btn-lg btn-outline-primary"
      @click="setTipoMovimentacao('CARTAO')"
    >
      <i class="fas fa-credit-card fa-lg"></i><br />Entrada por Cartão
    </button>

    <button
      type="button"
      class="col-2 m-3 btn btn-lg btn-outline-info"
      @click="setTipoMovimentacao('ENTRADA_POR_TRANSF')"
    >
      <i class="fas fa-level-down-alt fa-rotate-270"></i><br />Entrada por Transferência
    </button>

    <button
      type="button"
      class="col-2 m-3 btn btn-lg btn-outline-danger"
      @click="setTipoMovimentacao('SAIDA')"
    >
      <i class="fas fa-money-bill-wave-alt"></i><br />Saída em Espécie
    </button>

    <button
      type="button"
      class="col-2 m-3 btn btn-lg btn-outline-secondary"
      @click="setTipoMovimentacao('SANGRIA')"
    >
      <i class="fas fa-external-link-alt fa-lg"></i><br />Sangria
    </button>
  </div>
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

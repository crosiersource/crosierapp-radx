<template>
  <CrosierBlock :loading="this.loading" />
  <div class="container">
    <GraficoVendas />

    <div class="row">
      <div class="col-12 col-md-6">
        <ClientesDesconectados />
      </div>
      <div class="col-12 col-md-6">
        <ClientesComMaisPerguntas />
      </div>
    </div>

    <div class="row">
      <div class="col-12 col-md-6">
        <MelhoresVendedores />
      </div>
      <div class="col-12 col-md-6">
        <MelhoresPointSales />
      </div>
    </div>
  </div>
</template>

<script>
import { CrosierBlock } from "crosier-vue";
import { mapGetters } from "vuex";
import GraficoVendas from "./graficoVendas";
import ClientesDesconectados from "./clientesDesconectados";
import MelhoresPointSales from "./melhoresPointSales";
import MelhoresVendedores from "./melhoresVendedores";
import ClientesComMaisPerguntas from "./clientesComMaisPerguntas";

export default {
  name: "dashboard",

  components: {
    GraficoVendas,
    ClientesDesconectados,
    MelhoresPointSales,
    MelhoresVendedores,
    ClientesComMaisPerguntas,
    CrosierBlock,
  },

  data() {
    return {};
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
    }),
  },
};
</script>

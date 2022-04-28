<template>
  <CrosierBlock :loading="this.loading" />
  <div class="container">
    <GraficoVendas @mudouPeriodo="this.mudouPeriodo" />

    <div class="row">
      <div class="col-12 col-md-6">
        <MelhoresVendedores ref="melhoresVendedores" />
      </div>
      <div class="col-12 col-md-6">
        <MelhoresPointSales ref="melhoresPointSales" />
      </div>
    </div>

    <div class="row">
      <div class="col-12 col-md-6">
        <ClientesDesconectados ref="clientesDesconectados" />
      </div>
      <div class="col-12 col-md-6">
        <ClientesComMaisPerguntas ref="clientesComMaisPerguntas" />
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

  methods: {
    mudouPeriodo() {
      this.$refs.melhoresVendedores.doFilter();
      this.$refs.melhoresPointSales.doFilter();
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
    }),
  },
};
</script>

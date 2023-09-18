<template>
  <CartaCorrecaoForm v-if="this.notaFiscal.id" />

  <div class="d-flex justify-content-end mb-1" v-if="this.notaFiscal.permiteCartaCorrecao">
    <button
      type="button"
      class="btn btn-outline-info"
      @click="novo()"
      title="Nova carta de correção"
    >
      <i class="fas fa-file" aria-hidden="true"></i>
    </button>
  </div>

  <CrosierListS
    :comPaginador="false"
    withoutCard
    :key="this.$store.state.dtCartasCorrecaoKey"
    v-if="this.notaFiscal.id"
    :comFiltragem="false"
    apiResource="/api/fis/notaFiscalCartaCorrecao/"
    ref="dt"
    dtStateName="cartasCorrecaoList"
    filtersStoreName="cartasCorrecaoFilters"
  >
    <template v-slot:columns>
      <Column field="seq" header="#" sortable />
      <Column field="dtCartaCorrecao" header="Dt/Hr" sortable>
        <template #body="r">
          {{ new Date(r.data.updated).toLocaleString() }}
        </template>
      </Column>
      <Column field="cartaCorrecao" header="Descrição" sortable />
      <Column field="updated" header="">
        <template #body="r">
          <a
            target="_blank"
            v-if="r.data.msgRetorno"
            role="button"
            class="btn btn-sm btn-outline-success"
            title="Imprimir carta de correção"
            :href="'/api/fis/notaFiscal/imprimirCartaCorrecao/' + r.data.id"
          >
            <i class="fas fa-print" aria-hidden="true"></i> Imprimir
          </a>
        </template>
      </Column>
    </template>
  </CrosierListS>
</template>

<script>
import { mapGetters, mapMutations, mapActions } from "vuex";
import { CrosierListS } from "crosier-vue";
import Column from "primevue/column";
import CartaCorrecaoForm from "./cartaCorrecao.vue";

export default {
  components: {
    CrosierListS,
    Column,
    CartaCorrecaoForm,
  },

  methods: {
    ...mapMutations(["setLoading", "setNotaFiscalCartaCorrecao"]),
    ...mapActions(["loadNotaFiscalCartaCorrecao"]),

    novo() {
      this.$store.state.exibirDialogCartaCorrecao = true;
    },
  },

  computed: {
    ...mapGetters({
      notaFiscal: "getNotaFiscal",
    }),
  },
};
</script>

<template>
  <CrosierListS
    :comPaginador="false"
    withoutCard
    :key="this.$store.state.dtHistoricoKey"
    v-if="this.notaFiscal.id"
    :comFiltragem="false"
    apiResource="/api/fis/notaFiscalHistorico"
    ref="dt"
    dtStateName="historicoList"
    filtersStoreName="historicoFilters"
    sortField="dtHistorico"
  >
    <template v-slot:columns>
      <Column field="dtHistorico" header="Dt/Hr">
        <template #body="r">
          {{ new Date(r.data.updated).toLocaleString() }}
        </template>
      </Column>
      <Column field="descricao" header="Descrição" />
      <Column field="obs" header="Obs" />
    </template>
  </CrosierListS>
</template>

<script>
import { mapGetters, mapMutations, mapActions } from "vuex";
import { CrosierListS } from "crosier-vue";
import Column from "primevue/column";

export default {
  components: {
    CrosierListS,
    Column,
  },

  computed: {
    ...mapGetters({
      notaFiscal: "getNotaFiscal",
    }),
  },
};
</script>

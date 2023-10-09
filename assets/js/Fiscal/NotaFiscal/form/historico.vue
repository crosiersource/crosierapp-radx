<template>
  <CrosierInputText label="Permite salvar?" disabled v-model="this.notaFiscal.msgPermiteSalvar" />
  <CrosierInputText
    label="Permite reimpressão?"
    disabled
    v-model="this.notaFiscal.msgPermiteReimpressao"
  />
  <CrosierInputText
    label="Permite cancelamento?"
    disabled
    v-model="this.notaFiscal.msgPermiteCancelamento"
  />
  <CrosierInputText
    label="Permite carta de correção?"
    disabled
    v-model="this.notaFiscal.msgPermiteCartaCorrecao"
  />
  <CrosierInputText
    label="Permite reimpressão de cancelamento?"
    disabled
    v-model="this.notaFiscal.msgPermiteReimpressaoCancelamento"
  />
  <CrosierInputText
    label="Permite faturamento?"
    disabled
    v-model="this.notaFiscal.msgPermiteFaturamento"
  />
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
      <Column field="descricao" header="Descrição">
        <template #body="r">
          <div style="white-space: pre-wrap">
            {{ r.data.descricao }}
          </div>
        </template>
      </Column>
      <Column field="obs" header="Obs" />
    </template>
  </CrosierListS>
</template>

<script>
import { mapGetters } from "vuex";
import { CrosierInputText, CrosierListS } from "crosier-vue";
import Column from "primevue/column";

export default {
  components: {
    CrosierListS,
    CrosierInputText,
    Column,
  },

  computed: {
    ...mapGetters({
      notaFiscal: "getNotaFiscal",
    }),
  },
};
</script>

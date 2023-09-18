<template>
  <DataTable
    v-if="this.notaFiscal.id"
    stateStorage="local"
    class="p-datatable-sm p-datatable-striped"
    :value="this.notaFiscal.dadosDuplicatas"
    :paginator="false"
    resizableColumns
    columnResizeMode="fit"
    responsiveLayout="scroll"
    rowHover
  >
    <Column field="numero" header="Número" />
    <Column field="vencimento" header="Dt Vencto" sortable>
      <template #body="r">
        <div class="text-center">
          {{ new Date(r.data.vencimento + "T12:00:00-03:00").toLocaleString().substring(0, 10) }}
        </div>
      </template>
    </Column>
    <Column field="valor" header="Valor" sortable>
      <template #body="r">
        <div class="text-right">
          {{
            parseFloat(r.data.valor ?? 0).toLocaleString("pt-BR", {
              style: "currency",
              currency: "BRL",
            })
          }}
        </div>
      </template>
    </Column>
  </DataTable>
</template>

<script>
import { mapGetters, mapMutations, mapActions } from "vuex";
import { CrosierListS } from "crosier-vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

export default {
  components: {
    CrosierListS,
    Column,
    DataTable,
  },

  computed: {
    ...mapGetters({
      notaFiscal: "getNotaFiscal",
    }),
  },
};
</script>

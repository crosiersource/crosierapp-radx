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

  <FormLancto
    v-if="
      Array.isArray(this.notaFiscal.dadosDuplicatas) && this.notaFiscal.dadosDuplicatas.length > 0
    "
  />
</template>

<script>
import { mapGetters } from "vuex";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import FormLancto from "./duplicatas_lancto.vue";

export default {
  components: {
    Column,
    DataTable,
    FormLancto,
  },

  computed: {
    ...mapGetters({
      notaFiscal: "getNotaFiscal",
    }),
  },
};
</script>

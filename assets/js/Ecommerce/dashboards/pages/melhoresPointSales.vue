<template>
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Melhores Marketplaces</h5>
      <h6 class="card-subtitle mb-2 text-muted">Últimos 90 dias</h6>
      <DataTable
        scrollable
        scrollHeight="300px"
        stripedRows
        rowHover
        :value="results"
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <Column field="point_sale" header="Marketplace"></Column>
        <Column field="valor_total" header="Total" class="text-right">
          <template #body="r">
            {{
              r.data.valor_total.toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL",
              })
            }}
            <br />
            {{
              r.data.porcent.toLocaleString("pt-BR", {
                style: "decimal",
                minimumSignificantDigits: 2,
                maximumSignificantDigits: 2,
              })
            }}%
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>

<script>
import { mapMutations } from "vuex";
import { api } from "crosier-vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

export default {
  name: "melhoresPointSales",

  components: {
    DataTable,
    Column,
  },

  data() {
    return {
      results: null,
    };
  },

  async mounted() {
    this.setLoading(true);

    const rs = await api.get({
      apiResource: "/api/dashboard/melhoresPointSales",
    });

    this.results = rs.data.DATA;
    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading"]),
  },
};
</script>

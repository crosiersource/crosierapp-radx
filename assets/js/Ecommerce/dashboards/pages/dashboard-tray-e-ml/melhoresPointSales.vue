<template>
  <div class="card mb-4">
    <div class="card-body">
      <div>
        <h4 class="card-title mb-0">Melhores Marketplaces</h4>
      </div>
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
import { mapGetters, mapMutations } from "vuex";
import { api } from "crosier-vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import moment from "moment";

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
    await this.doFilter();
    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading"]),

    moment(date) {
      moment.locale("pt-br");
      return moment(date);
    },

    async doFilter() {
      const rs = await api.get({
        apiResource: "/api/dashboard/melhoresPointSales",
        filters: this.periodo,
      });

      this.results = rs.data.DATA;
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      periodo: "getPeriodoFormatted",
    }),
  },
};
</script>

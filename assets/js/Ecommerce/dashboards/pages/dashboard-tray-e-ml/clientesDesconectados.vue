<template>
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Desconexões</h5>
      <DataTable
        scrollable
        scrollHeight="300px"
        @row-click="onRowClick"
        stripedRows
        rowHover
        :value="clientes"
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <Column field="cliente.nome" header="Cliente"></Column>
        <Column field="jsonData" header="Desconectado em">
          <template #body="r">
            {{ r.data.jsonData["dt_desativado"] }}
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
  name: "clientesDesconectados",

  components: {
    DataTable,
    Column,
  },

  data() {
    return {
      clientes: null,
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.doFilter();

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading"]),

    onRowClick($event) {
      this.setLoading(true);
      window.location = `/v/ecommerce/clienteConfig/form?id=${$event.data.id}`;
    },

    async doFilter() {
      const rs = await api.get({
        apiResource: "/api/ecommerce/clienteConfig",
        allRows: true,
        filters: {
          ativo: false,
          "cliente.ativo": true,
        },
        order: { updated: "desc" },
        properties: ["id", "cliente.nome", "jsonData"],
      });

      this.clientes = rs.data["hydra:member"];
    },
  },
};
</script>

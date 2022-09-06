<template>
  <CrosierListS
    titulo="Vendas"
    subtitulo="Tray"
    filtrosNaSidebar
    apiResource="/api/ecommerce/trayVenda/"
    :columns="columns"
    ref="list"
    :defaultOrder="{ dtVenda: 'DESC' }"
    @beforeFilter="this.beforeFilter"
    :properties="[
      'id',
      'idTray',
      'clienteConfig.id',
      'clienteConfig.cliente.id',
      'clienteConfig.cliente.nome',
      'dtVenda',
      'statusTray',
      'pointSale',
      'valorTotal',
      'updated',
    ]"
  >
    <template v-slot:filter-fields>
      <div class="form-row">
        <CrosierDropdown
          label="Cliente"
          col="12"
          id="cliente"
          v-model="this.filters['clienteConfig.cliente']"
          :options="this.clientes"
          optionValue="@id"
          optionLabel="nome"
        />
      </div>
      <div class="form-row">
        <CrosierInputInt label="Id Tray" col="6" id="idTray" v-model="this.filters.idTray" />

        <CrosierInputText
          label="Status"
          col="6"
          id="statusTray"
          v-model="this.filters.statusTray"
        />
      </div>
      <div class="form-row">
        <CrosierCalendar
          col="6"
          label="Entre..."
          id="dtVendaIni"
          v-model="this.filters['dtVenda[after]']"
        />
        <CrosierCalendar
          col="6"
          label="e..."
          id="dtVendaFim"
          v-model="this.filters['dtVenda[before]']"
        />
      </div>
    </template>
    <template v-slot:columns>
      <Column field="id" header="Id" :sortable="true" />
      <Column field="idTray" header="Id Venda Tray" :sortable="true" />
      <Column field="clienteConfig.cliente.nome" header="Loja" :sortable="true" />
      <Column field="dtVenda" header="Dt Venda" :sortable="true">
        <template class="text-right" #body="r">
          {{ this.moment(r.data.dtVenda).format("DD/MM/YYYY HH:mm") }}
        </template>
      </Column>
      <Column field="statusTray" header="Status" :sortable="true"> </Column>
      <Column field="pointSale" header="Canal" :sortable="true"> </Column>
      <Column field="valorTotal" header="Valor Total" :sortable="true">
        <template class="text-right" #body="r">
          {{
            r.data.valorTotal.toLocaleString("pt-BR", {
              style: "currency",
              currency: "BRL",
            })
          }}
        </template>
      </Column>
      <Column field="updated" header="" :sortable="true">
        <template class="text-right" #body="r">
          <div class="row mt-1 d-flex justify-content-end">
            <span v-if="r.data.updated" class="badge badge-info">
              {{ new Date(r.data.updated).toLocaleString() }}
            </span>
          </div>
        </template>
      </Column>
    </template>
  </CrosierListS>
</template>

<script>
import Column from "primevue/column";
import moment from "moment";
import { mapMutations, mapGetters } from "vuex";
import {
  CrosierListS,
  CrosierDropdown,
  CrosierInputInt,
  CrosierInputText,
  CrosierCalendar,
} from "crosier-vue";

export default {
  components: {
    CrosierListS,
    CrosierCalendar,
    Column,
    CrosierDropdown,
    CrosierInputText,
    CrosierInputInt,
  },

  data() {
    return {
      tableData: [],
      columns: [],
    };
  },

  mounted() {
    this.setLoading(true);
    this.$store.dispatch("loadClientes");
    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading"]),

    moment(date) {
      return moment(date);
    },

    beforeFilter() {
      if (this.filters["dtVenda[after]"]) {
        this.filters["dtVenda[after]"].setHours(0, 0, 0, 0);
        this.filters["dtVenda[after]"] = moment(this.filters["dtVenda[after]"]).format();
      }
      if (this.filters["dtVenda[before]"]) {
        this.filters["dtVenda[before]"].setHours(23, 59, 59, 999);
        this.filters["dtVenda[before]"] = moment(this.filters["dtVenda[before]"]).format();
      }
    },
  },

  computed: {
    ...mapGetters({
      filters: "getFilters",
      clientes: "getClientes",
    }),
  },
};
</script>

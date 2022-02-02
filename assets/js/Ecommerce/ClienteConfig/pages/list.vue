<template>
  <CrosierListS
    apiResource="/api/ecommerce/clienteConfig/"
    formUrl="/ecommerce/clienteConfig/form"
    titulo="Configurações"
    subtitulo="Clientes"
  >
    <template v-slot:columns>
      <Column field="id" header="Id" :sortable="true" />
      <Column field="cliente.nome" header="Loja" :sortable="true" />
      <Column field="ativo" header="Ativo" :sortable="true">
        <template class="text-center" #body="r">
          {{ r.data.ativo ? "Sim" : "Não" }}
        </template>
      </Column>
      <Column field="jsonData.url_loja" header="URL" :sortable="true" />
      <Column field="mercadolivreExpiraEm" header="Dt Expiração (ML)" :sortable="true">
        <template class="text-right" #body="r">
          <span v-if="r.data.mercadolivreExpiraEm">
            {{ this.moment(r.data.mercadolivreExpiraEm).format("DD/MM/YYYY HH:mm") }}
          </span>
        </template>
      </Column>
      <Column field="trayDtExpRefreshToken" header="Dt Expiração (Tray)" :sortable="true">
        <template class="text-right" #body="r">
          <span v-if="r.data.trayDtExpRefreshToken">
            {{ this.moment(r.data.trayDtExpRefreshToken).format("DD/MM/YYYY HH:mm") }}
          </span>
        </template>
      </Column>
      <Column field="updated" header="" :sortable="true">
        <template class="text-right" #body="r">
          <div class="d-flex justify-content-end">
            <a
              role="button"
              class="btn btn-primary btn-sm"
              title="Editar registro"
              :href="'/ecommerce/clienteConfig/form?id=' + r.data.id"
              ><i class="fas fa-wrench" aria-hidden="true"></i
            ></a>
          </div>
          <div class="d-flex justify-content-end mt-1">
            <span
              v-if="r.data.updated"
              class="badge badge-info"
              title="Última alteração do registro"
            >
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
import { mapGetters, mapMutations } from "vuex";
import { CrosierListS } from "crosier-vue";

export default {
  components: {
    CrosierListS,
    Column,
  },

  data() {
    return {
      tableData: [],
      columns: [],
    };
  },

  methods: {
    ...mapMutations(["setLoading"]),

    moment(date) {
      return moment(date);
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

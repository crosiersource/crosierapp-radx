<template>
  <div :class="this.containerClass">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>{{ titulo }}</h3>
            <h6 v-if="subtitulo">{{ subtitulo }}</h6>
          </div>
          <div class="d-sm-flex flex-nowrap ml-auto">
            <a
              v-show="this.formUrl"
              type="button"
              class="btn btn-info"
              :href="this.formUrl"
              title="Novo"
            >
              <i class="fas fa-file" aria-hidden="true"></i>
            </a>
            <slot name="headerButtons"></slot>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div>
          <Accordion :multiple="true" :activeIndex="this.isFiltered ? '[0]' : null">
            <AccordionTab>
              <template #header>
                <span>Filtrar</span>
                <i class="pi pi-filter"></i>
              </template>
              <form @submit.prevent="this.doFilter()" class="notSubmit">
                <slot name="filter-fields"></slot>
                <div class="row mt-3">
                  <div class="col-3">
                    <InlineMessage severity="info">
                      {{ totalRecords }} registro(s) encontrado(s).</InlineMessage
                    >
                  </div>
                  <div class="col text-right">
                    <Button
                      label="Filtrar"
                      type="submit"
                      icon="fas fa-search"
                      class="p-button-primary p-button-sm mr-2"
                    />
                    <Button
                      label="Limpar"
                      icon="fas fa-backspace"
                      class="p-button-secondary p-button-sm mr-2"
                      @click="this.doClearFilters()"
                    />
                  </div>
                </div>
              </form>
            </AccordionTab>
          </Accordion>
        </div>
        <DataTable
          stateStorage="local"
          class="p-datatable-sm p-datatable-striped"
          :stateKey="this.dataTableStateKey"
          :value="tableData"
          :totalRecords="totalRecords"
          :lazy="true"
          :paginator="true"
          :rows="10"
          @page="onPage($event)"
          @sort="onSort($event)"
          removableSort
          sortField="id"
          sortOrder="1"
          ref="dt"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink
           LastPageLink CurrentPageReport RowsPerPageDropdown"
          :rowsPerPageOptions="[5, 10, 25, 50, 1000]"
          currentPageReportTemplate="{first}-{last} de {totalRecords}"
          v-model:selection="this.selectedItems"
          dataKey="id"
          @row-select="onSelectChange"
          @row-unselect="onSelectChange"
        >
          <template #footer>
            <div style="text-align: right">
              <Button
                class="p-button-rounded p-button-success p-button-text"
                icon="pi pi-file-excel"
                label="Exportar"
                @click="exportCSV($event)"
              />
            </div>
          </template>
          <slot name="columns"></slot>
        </DataTable>
      </div>
    </div>
  </div>
  <ConfirmPopup></ConfirmPopup>
  <Toast class="mt-5" />
</template>

<script>
import DataTable from "primevue/datatable";
import Accordion from "primevue/accordion";
import AccordionTab from "primevue/accordiontab";
import Button from "primevue/button";
import ConfirmPopup from "primevue/confirmpopup";
import InlineMessage from "primevue/inlinemessage";
import Toast from "primevue/toast";
import { fetchTableData } from "@/services/ApiDataFetchService";
import { deleteEntityData } from "@/services/ApiDeleteService";
import listSelectStore from "../store/listSelectStore";

export default {
  name: "CrosierListS",
  components: {
    Accordion,
    AccordionTab,
    Button,
    ConfirmPopup,
    DataTable,
    InlineMessage,
    Toast,
  },
  props: {
    titulo: {
      type: String,
      required: true,
    },
    subtitulo: {
      type: String,
      required: false,
    },
    formUrl: {
      type: String,
      required: false,
    },
    pesquisar: {
      type: String,
      required: false,
    },
    apiResource: {
      type: String,
      required: true,
    },
    filtersStoreName: {
      type: String,
      required: false,
      default: "filters",
    },
    containerClass: {
      type: String,
      required: false,
      default: "container-fluid",
    },
  },

  data() {
    return {
      savedFilter: {},
      isFiltered: false,
      totalRecords: 0,
      tableData: null,
      selectedItems: [],
    };
  },

  async mounted() {
    this.$store.state.loading = true;
    console.log("mounted");
    const uri = window.location.search.substring(1);
    const params = new URLSearchParams(uri);

    this.savedFilter = params.get("filters") || localStorage.getItem(this.localStorageName);
    if (this.savedFilter) {
      console.log("tem savedFilter");
      console.log(this.savedFilter);
      this.$store.state[this.filtersStoreName] = JSON.parse(this.savedFilter);
    } else {
      console.log("NÃO tem savedFilter");
    }
    let page = 1;
    let rows = 10;
    const order = new Map();
    const lsItem = localStorage.getItem(this.dataTableStateKey);
    if (lsItem) {
      console.log("já tem lsItem");
      console.log(lsItem);
      const dtStateLS = JSON.parse(lsItem);
      page = Math.ceil((dtStateLS.first + 1) / dtStateLS.rows);
      rows = dtStateLS.rows;
      const sorterOrder = {
        1: "ASC",
        "-1": "DESC",
      };

      if (dtStateLS?.sortOrder && sorterOrder[dtStateLS.sortOrder]) {
        order.set(dtStateLS.sortField, sorterOrder[dtStateLS.sortOrder]);
      }
    }

    console.log("fetchTableData.....");
    console.log(this.filters);

    // make request passing
    const response = await this.fetchTableData({
      apiResource: this.apiResource,
      page,
      rows,
      order,
      filters: this.filters,
    });

    this.totalRecords = response.data["hydra:totalItems"];
    this.tableData = response.data["hydra:member"];

    this.$emit("afterFilter", this.tableData);
    this.$store.state.loading = false;
  },
  methods: {
    fetchTableData,
    deleteEntityData,

    async lazyFetch(event) {
      this.$store.state.loading = true;
      const page = event ? Math.ceil((event.first + 1) / event.rows) : 1;
      const rows = event ? event.rows : 10;
      const filters = this.filters;
      const sorterOrder = {
        1: "ASC",
        "-1": "DESC",
      };

      const order = new Map();
      if (sorterOrder[event.sortOrder]) {
        order.set(event.sortField, sorterOrder[event.sortOrder]);
      }

      const response = await this.fetchTableData({
        apiResource: this.apiResource,
        page,
        rows,
        order,
        filters,
      });

      this.totalRecords = response.data["hydra:totalItems"];
      this.tableData = response.data["hydra:member"];

      this.$emit("afterFilter", this.tableData);
      this.$store.state.loading = false;
    },

    redirectForm(id = "") {
      window.location.href = `form${id ? `?id=${id}` : ""}`;
    },

    async onPage(event) {
      await this.lazyFetch(event);
    },

    async onSort(event) {
      await this.lazyFetch(event);
    },

    async doFilter() {
      this.$store.state.loading = true;

      // get filters
      const filters = this.filters;
      console.log("doFilter: ");
      console.log(filters);

      // get from api
      const response = await this.fetchTableData({
        apiResource: this.apiResource,
        filters,
      });

      this.totalRecords = response.data["hydra:totalItems"];
      this.tableData = response.data["hydra:member"];

      // save filters on localstorage
      localStorage.setItem(this.localStorageName, JSON.stringify(this.filters));
      console.log("afterFilter no mounted");
      this.$emit("afterFilter", this.tableData);
      this.$store.state.loading = false;
    },

    doClearFilters() {
      this.$store.state[this.filtersStoreName] = {};
      this.$emit("clearFilters");
    },

    async delete(event, id) {
      this.$confirm.require({
        target: event.currentTarget,
        message: "Tem certeza que deseja deletar?",
        icon: "pi pi-exclamation-triangle",
        acceptLabel: "Sim",
        rejectLabel: "Não",
        accept: async () => {
          try {
            this.$store.state.loading = true;

            const response = await this.deleteEntityData({
              apiResource: `${this.apiResource}${id}`,
            });
            if (response.status === 204) {
              this.showSuccess("Deletado com sucesso.");
              document.location.reload(true);
            } else {
              this.showError("Erro ao deletar");
            }
          } catch (err) {
            this.showError("Erro ao deletar");
            console.log(err);
          }
          this.$store.state.loading = false;
        },
      });
    },

    showSuccess(message) {
      this.$toast.add({
        severity: "success",
        summary: "Mensagem de sucesso",
        detail: message,
        life: 3000,
      });
    },

    showError(message) {
      this.$toast.add({
        severity: "error",
        summary: "Mensagem de erro",
        detail: message,
        life: 3000,
      });
    },

    exportCSV() {
      this.$refs.dt.exportCSV();
    },

    // eslint-disable-next-line no-unused-vars
    onSelectChange(e) {
      listSelectStore.dispatch("updateSelectedRows", this.selectedItems);
    },
  },
  computed: {
    filters() {
      return this.$store.state[this.filtersStoreName];
    },
    stored_selectedItems() {
      return listSelectStore.state.selectedItems.length;
    },
    localStorageName() {
      return `filter-state_${this.apiResource}_${this.filtersStoreName}`;
    },
    dataTableStateKey() {
      return `dataTable-state${this.apiResource}`;
    },
  },
};
</script>

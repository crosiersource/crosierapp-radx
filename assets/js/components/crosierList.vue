<template>
  <div class="container-fluid">
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
              <form @submit.prevent="this.$emit('handleFilter')" class="notSubmit">
                <slot name="filter-fields"></slot>
                <div class="row mt-3">
                  <div class="col-3">
                    <InlineMessage severity="info">
                      {{ totalRecords }} registro(s) encontrado(s).
                    </InlineMessage>
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
                      @click="this.$emit('clearFilter')"
                    />
                  </div>
                </div>
              </form>
            </AccordionTab>
          </Accordion>
        </div>
        <data-table
          stateStorage="local"
          :stateKey="'dt-state' + this.apiResource"
          class="p-datatable-sm p-datatable-striped"
          :value="tableData"
          :totalRecords="totalRecords"
          :lazy="true"
          :paginator="true"
          :rows="10"
          :loading="loading"
          @page="onPage($event)"
          @sort="onSort($event)"
          removableSort
          sortField="id"
          :sortOrder="1"
          ref="dt"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink
           LastPageLink CurrentPageReport RowsPerPageDropdown"
          :rowsPerPageOptions="[5, 10, 25, 50, 1000]"
          currentPageReportTemplate="Exibindo do {first}° até {last}° de
           {totalRecords} registros."
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
        </data-table>
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
  name: "CrosierList",
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
  },
  data() {
    return {
      savedFilter: {},
      isFiltered: false,
      loading: false,
      totalRecords: 0,
      tableData: null,
      selectedItems: [],
    };
  },
  async mounted() {
    this.loading = true;

    const uri = window.location.search.substring(1);
    const params = new URLSearchParams(uri);

    // get filters
    // from query params
    this.savedFilter =
      params.get("saved_filter") || localStorage.getItem(`filter-state${this.apiResource}`);
    if (this.savedFilter) {
      // save on store to reflect in form filter fields
      this.$store.commit("setFilterFields", JSON.parse(this.savedFilter));
    }

    // get localstorage to this resource
    const dtStateLS = JSON.parse(localStorage.getItem(`dt-state${this.apiResource}`));

    // get page
    // from localstorage
    const page = dtStateLS ? Math.ceil((dtStateLS.first + 1) / dtStateLS.rows) : 1;

    // get rows
    // from localstorage
    const rows = dtStateLS ? dtStateLS.rows : 10;

    // get sorts
    // from localstorage
    const sorterOrder = {
      1: "ASC",
      "-1": "DESC",
    };

    const order = new Map();
    if (dtStateLS && sorterOrder[dtStateLS.sortOrder]) {
      order.set(dtStateLS.sortField, sorterOrder[dtStateLS.sortOrder]);
    }

    // make request passing
    const response = await this.fetchTableData({
      apiResource: this.apiResource,
      page,
      rows,
      order,
      filters: this.$store.state.filterFields,
    });

    this.totalRecords = response.data["hydra:totalItems"];
    this.tableData = response.data["hydra:member"];

    // eslint-disable-next-line no-restricted-syntax
    for (const key in this.$store.state.filterFields) {
      if (
        this.$store.state.filterFields[key] !== null &&
        this.$store.state.filterFields[key] !== ""
      )
        this.isFiltered = true;
    }

    this.loading = false;
  },
  methods: {
    fetchTableData,
    deleteEntityData,
    async lazyFetch(event) {
      // lazyFetch is called when sort or change page of datatables
      // this mathod receive an event param that contains props used
      // to fetch new data
      // the process is same of the fetching on mounted but the sort and page are
      // just taked from event (doesn't from LS and url).
      this.loading = true;

      // get page
      const page = event ? Math.ceil((event.first + 1) / event.rows) : 1;

      // get rows
      const rows = event ? event.rows : 10;

      // get filters
      const filters = this.$store.state.filterFields;

      // get sort field
      const sorterOrder = {
        1: "ASC",
        "-1": "DESC",
      };

      const order = new Map();
      if (sorterOrder[event.sortOrder]) {
        order.set(event.sortField, sorterOrder[event.sortOrder]);
      }

      // get from api
      const response = await this.fetchTableData({
        apiResource: this.apiResource,
        page,
        rows,
        order,
        filters,
      });

      this.totalRecords = response.data["hydra:totalItems"];
      this.tableData = response.data["hydra:member"];
      this.loading = false;
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
    async onFilter() {
      this.loading = true;

      // get filters
      const filters = this.$store.state.filterFields;

      // get from api
      const response = await this.fetchTableData({
        apiResource: this.apiResource,
        filters,
      });

      this.totalRecords = response.data["hydra:totalItems"];
      this.tableData = response.data["hydra:member"];

      // save filters on localstorage
      localStorage.setItem(
        `filter-state${this.apiResource}`,
        JSON.stringify(this.$store.state.filterFields)
      );

      this.loading = false;
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
            this.loading = true;

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
          this.loading = false;
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
    stored_selectedItems() {
      return listSelectStore.state.selectedItems.length;
    },
  },
};
</script>

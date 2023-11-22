<template>
  <ConfirmDialog />
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <CrosierBlock :loading="this.loading" />

  <Sidebar class="p-sidebar-lg" v-model:visible="this.visibleRight" position="right">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title"><i class="fas fa-search"></i> Filtros</h5>
        <form @submit.prevent="this.doFilter()" class="notSubmit">
          <div class="form-row">
            <CrosierCalendar
              label="Desde..."
              col="6"
              inputClass="crsr-date"
              id="dt"
              :baseZIndex="10000"
              v-model="this.filters['dtVenda[after]']"
            />

            <CrosierCalendar
              label="até..."
              col="6"
              inputClass="crsr-date"
              id="dt"
              :baseZIndex="10000"
              v-model="this.filters['dtVenda[before]']"
            />
          </div>

          <div class="form-row">
            <CrosierInputInt
              col="6"
              label="Código Vendedor"
              id="codVendedor_i"
              v-model="this.filters['codVendedor[i]']"
            />

            <CrosierInputInt
              col="6"
              label="Código Vendedor"
              id="codVendedor_f"
              v-model="this.filters['codVendedor[f]']"
            />
          </div>

          <div class="form-row mt-2">
            <div class="col-6">
              <button type="submit" class="btn btn-primary btn-sm btn-block">
                <i class="fas fa-search"></i> Filtrar
              </button>
            </div>
            <div class="col-6">
              <button
                type="button"
                class="btn btn-sm btn-secondary btn-block"
                @click="this.doClearFilters()"
              >
                <i class="fas fa-backspace"></i> Limpar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </Sidebar>

  <div class="container">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Resultado Mensal</h3>
          </div>

          <div class="ml-auto"></div>
          <div>
            <CrosierCalendar
              @date-select="this.doFilterNextTick"
              col="8"
              label="Desde..."
              inputClass="crsr-date"
              id="dt"
              :baseZIndex="10000"
              v-model="this.filters['dtVenda[after]']"
            />
          </div>
          <div>
            <CrosierCalendar
              @date-select="this.doFilterNextTick"
              col="8"
              label="até..."
              inputClass="crsr-date"
              id="dt"
              :baseZIndex="10000"
              v-model="this.filters['dtVenda[before]']"
            />
          </div>
          <div>
            <button
              type="button"
              class="ml-1 btn btn-info"
              title="Período anterior"
              @click="this.trocaPeriodo(false)"
            >
              <i class="fas fa-angle-left"></i>
            </button>

            <button
              type="button"
              class="ml-1 btn btn-info"
              title="Próximo período"
              @click="this.trocaPeriodo(true)"
            >
              <i class="fas fa-angle-right"></i>
            </button>
          </div>

          <div class="d-sm-flex flex-nowrap">
            <button
              type="button"
              :class="'btn btn-' + (!this.isFiltering ? 'outline-' : '') + 'warning ml-1'"
              @click="this.toggleFiltros"
            >
              <i class="fas fa-search"></i>
            </button>

            <button
              type="button"
              class="btn btn-outline-secondary ml-1"
              title="Limpar filtros"
              @click="this.doClearFilters"
            >
              <i class="fas fa-sync-alt"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <DataTable
          stateStorage="local"
          class="p-datatable-sm p-datatable-striped"
          :stateKey="this.dataTableStateKey"
          :value="tableData"
          :paginator="false"
          :multiSortMeta="multiSortMeta"
          :removable-sort="true"
          dataKey="id"
          :resizableColumns="true"
          columnResizeMode="fit"
          responsiveLayout="scroll"
          :first="firstRecordIndex"
          ref="dt"
          :rowHover="true"
        >
          <Column field="id" header="Vendedor" sortable>
            <template #body="r">
              {{ ("00" + r.data?.vendedor?.jsonData?.codigo).slice(-2) }} -
              {{ r.data.vendedor.nome }}
            </template>
          </Column>

          <Column field="total" header="Total" sortable>
            <template #body="r">
              <div class="text-right">
                {{
                  parseFloat(r.data.total ?? 0).toLocaleString("pt-BR", {
                    style: "currency",
                    currency: "BRL",
                  })
                }}
              </div>
            </template>
          </Column>

          <template #footer>
            <div class="text-right">
              {{
                parseFloat(this.total ?? 0).toLocaleString("pt-BR", {
                  style: "currency",
                  currency: "BRL",
                })
              }}
            </div>
          </template>
        </DataTable>

        <DataTable
          class="mt-5 p-datatable-sm p-datatable-striped"
          :value="compas"
          :paginator="false"
          :multiSortMeta="multiSortMeta"
          :removable-sort="true"
          dataKey="id"
          :resizableColumns="true"
          columnResizeMode="fit"
          responsiveLayout="scroll"
          :first="firstRecordIndex"
          ref="dt"
          :rowHover="true"
        >
          <Column field="periodo" header="Período" sortable />

          <Column field="total" header="Total" sortable>
            <template #body="r">
              <div class="text-right">
                {{
                  parseFloat(r.data.dados.total ?? 0).toLocaleString("pt-BR", {
                    style: "currency",
                    currency: "BRL",
                  })
                }}
              </div>
            </template>
          </Column>

          <Column field="dados.fator" header="Fator" sortable>
            <template #body="r">
              <div class="text-center">
                {{ r.data.dados.fator ?? 0 }}
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
  </div>
</template>

<script>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import Sidebar from "primevue/sidebar";
import { mapGetters, mapMutations } from "vuex";
import { CrosierBlock, CrosierCalendar, CrosierInputInt } from "crosier-vue";
import moment from "moment-timezone";
import axios from "axios";
import printJS from "print-js";

export default {
  name: "resultMensal",

  components: {
    ConfirmDialog,
    CrosierBlock,
    DataTable,
    Column,
    Toast,
    Sidebar,
    CrosierCalendar,
    CrosierInputInt,
  },

  data() {
    return {
      savedFilter: {},
      totalRecords: 0,
      tableData: null,
      compas: null,
      total: 0,
      firstRecordIndex: 0,
      multiSortMeta: [],
      accordionActiveIndex: null,
      visibleRight: false,
    };
  },

  async mounted() {
    this.setLoading(true);

    const uri = window.location.search.substring(1);
    const params = new URLSearchParams(uri);

    this.savedFilter = params.get("filters") || localStorage.getItem(this.filtersOnLocalStorage);
    if (this.savedFilter) {
      try {
        const filtersParsed = JSON.parse(this.savedFilter);
        this.setFilters(filtersParsed);
      } catch (e) {
        console.error(`Não foi possível recuperar os filtros (${this.savedFilter})`);
        console.error(e);
      }
    }

    await this.doFilter();
    this.accordionActiveIndex = this.isFiltering ? 0 : null;
    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading"]),

    moment(date) {
      return moment(date);
    },

    setFilters(filters) {
      const mutationName = "setFilters";
      try {
        this.$store.commit(mutationName, filters);
      } catch (e) {
        console.error(`list_recorrente: |${mutationName}| n/d`);
        console.error(e);
      }
    },

    toggleFiltros() {
      this.accordionActiveIndex = this.accordionActiveIndex === 0 ? null : 0;
      this.visibleRight = !this.visibleRight;
    },

    async trocaPeriodo(proximo) {
      const ini = moment(this.filters["dtVenda[after]"]).format("YYYY-MM-DD");
      const fim = moment(this.filters["dtVenda[before]"]).format("YYYY-MM-DD");

      const rs = await axios.get(
        `/base/diaUtil/incPeriodo/?ini=${ini}&fim=${fim}&futuro=${proximo}&comercial=false&financeiro=false`
      );

      this.filters["dtVenda[after]"] = new Date(`${rs.data.dtIni}T00:00:00-03:00`);
      this.filters["dtVenda[before]"] = new Date(`${rs.data.dtFim}T23:59:59-03:00`);

      this.doFilter();
    },

    doFilterNextTick() {
      this.$nextTick(() => {
        this.doFilter();
      });
    },

    async doFilter() {
      this.setLoading(true);

      if (!this.filters["dtVenda[after]"]) {
        this.filters["dtVenda[after]"] = `${this.moment()
          .subtract(7, "days")
          .format("YYYY-MM-DD")}T00:00:00-03:00`;
      } else {
        this.filters["dtVenda[after]"] = `${this.moment(this.filters["dtVenda[after]"]).format(
          "YYYY-MM-DD"
        )}T00:00:00-03:00`;
      }

      if (!this.filters["dtVenda[before]"]) {
        this.filters["dtVenda[before]"] = `${this.moment().format("YYYY-MM-DD")}T23:59:59-03:00`;
      } else {
        this.filters["dtVenda[before]"] = `${this.moment(this.filters["dtVenda[before]"]).format(
          "YYYY-MM-DD"
        )}T23:59:59-03:00`;
      }

      this.filters["codVendedor[i]"] = this.filters["codVendedor[i]"] ?? 0;
      this.filters["codVendedor[f]"] = this.filters["codVendedor[f]"] ?? 99;

      const response = await axios.get(
        `/api/ven/vendasResults/vendasPorPeriodo?dtVenda[after]=${this.filters["dtVenda[after]"]}&dtVenda[before]=${this.filters["dtVenda[before]"]}&codVendedor[i]=${this.filters["codVendedor[i]"]}&codVendedor[f]=${this.filters["codVendedor[f]"]}`
      );

      this.tableData = response.data.DATA.dados.rs;
      this.total = response.data.DATA.dados.total;
      this.compas = response.data.DATA.compa;

      // salva os filtros no localStorage
      localStorage.setItem(this.filtersOnLocalStorage, JSON.stringify(this.filters));

      this.setFilters(this.filters);

      this.visibleRight = false;

      this.setLoading(false);
    },

    doClearFilters() {
      this.setFilters({});
      localStorage.setItem(this.filtersOnLocalStorage, null);
      this.$refs.dt.resetPage();
      this.doFilter({ event: { first: 0 } });
      this.visibleRight = false;
    },

    async imprimir() {
      this.setLoading(true);
      const pdf = await axios.post("/fin/movimentacao/aPagarReceber/rel", {
        tableData: JSON.stringify(this.tableData),
        filters: JSON.stringify(this.filters),
        saldos: JSON.stringify(Object.fromEntries(this.saldos)),
        totalGeral: this.totalGeral,
      });
      printJS({
        printable: pdf.data,
        type: "pdf",
        base64: true,
        targetStyles: "*",
      });
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      filters: "getFilters",
      defaultFilters: "getDefaultFilters",
    }),

    filtersOnLocalStorage() {
      return "filters_vendas_resultMensal";
    },

    dataTableStateKey() {
      return "dt-state_vendas_resultMensal";
    },
  },
};
</script>

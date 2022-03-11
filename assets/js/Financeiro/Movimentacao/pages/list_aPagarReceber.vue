<template>
  <ConfirmDialog group="confirmDialog_aPagarReceberList" />
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <CrosierBlock :loading="this.loading" />

  <Sidebar
    class="p-sidebar-lg"
    :baseZIndex="1000"
    v-model:visible="this.visibleRight"
    position="right"
  >
    <div class="card">
      <div class="card-body">
        <h5 class="card-title"><i class="fas fa-search"></i> Filtros</h5>
        <form @submit.prevent="this.doFilter()" class="notSubmit">
          <div class="form-row">
            <CrosierMultiSelectEntity
              style="z-index: 99999"
              v-model="this.filters.carteira"
              entity-uri="/api/fin/carteira"
              optionLabel="descricaoMontada"
              :orderBy="{ codigo: 'ASC' }"
              :filters="{ abertas: true }"
              label="Carteiras"
              id="carteiras"
            />
          </div>

          <div class="form-row">
            <CrosierCalendar
              label="Desde..."
              col="5"
              inputClass="crsr-date"
              id="dt"
              :baseZIndex="10000"
              v-model="this.filters['dtVenctoEfetiva[after]']"
            />

            <CrosierCalendar
              label="até..."
              col="5"
              inputClass="crsr-date"
              id="dt"
              :baseZIndex="10000"
              v-model="this.filters['dtVenctoEfetiva[before]']"
            />
            <div class="col-1">
              <div class="form-group">
                <label for="btnAnterior" style="color: transparent">.</label>
                <button
                  type="button"
                  class="ml-1 btn btn-sm btn-info btn-block"
                  title="Mês anterior"
                  @click="this.trocaPeriodo(false)"
                >
                  <i class="fas fa-angle-left"></i>
                </button>
              </div>
            </div>
            <div class="col-1">
              <div class="form-group">
                <label for="btnAnterior" style="color: transparent">.</label>
                <button
                  type="button"
                  class="ml-1 btn btn-sm btn-info btn-block"
                  title="Próximo mês"
                  @click="this.trocaPeriodo(true)"
                >
                  <i class="fas fa-angle-right"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="form-row">
            <CrosierInputText label="Descrição" id="descricao" v-model="this.filters.descricao" />
          </div>

          <div class="form-row">
            <CrosierCurrency
              label="Valor Total entre..."
              col="6"
              id="dt"
              v-model="this.filters['valorTotal[gte]']"
            />

            <CrosierCurrency
              label="e..."
              col="6"
              id="dt"
              v-model="this.filters['valorTotal[lte]']"
            />
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <InlineMessage severity="info"
                ><small>
                  {{ totalRecords }} registro(s) encontrado(s)
                  <span v-show="this.isFiltering">(com filtros aplicados)</span>.
                </small>
              </InlineMessage>
            </div>
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

  <div class="container-fluid">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Contas a Pagar/Receber</h3>
            <h6>
              Entre {{ moment(this.filters["dtVenctoEfetiva[after]"]).format("DD/MM/YYYY") }} e
              {{ moment(this.filters["dtVenctoEfetiva[before]"]).format("DD/MM/YYYY") }}
            </h6>
          </div>
          <div class="d-sm-flex flex-nowrap ml-auto">
            <a type="button" class="btn btn-outline-info" href="form" title="Novo">
              <i class="fas fa-file" aria-hidden="true"></i>
            </a>
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
              <i class="fas fa-backspace"></i>
            </button>

            <button
              type="button"
              class="btn btn-outline-dark ml-1"
              @click="this.imprimir"
              title="Imprimir listagem"
              id="btnImprimir"
              name="btnImprimir"
            >
              <i class="fas fa-print"></i>
            </button>

            <button
              type="button"
              class="btn btn-outline-dark ml-1"
              @click="this.imprimirFicha"
              title="Imprimir fichas de movimentação"
              id="btnImprimirFicha"
              name="btnImprimirFicha"
            >
              <i class="far fa-file-alt"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <DataTable
          rowGroupMode="subheader"
          groupRowsBy="dtVenctoEfetiva"
          stateStorage="local"
          class="p-datatable-sm p-datatable-striped"
          :stateKey="this.dataTableStateKey"
          :value="tableData"
          :totalRecords="totalRecords"
          :lazy="true"
          :paginator="false"
          @page="doFilter($event)"
          @sort="doFilter($event)"
          :multiSortMeta="multiSortMeta"
          :removable-sort="true"
          v-model:selection="this.selection"
          @update:selection="this.onUpdateSelection($event)"
          selectionMode="multiple"
          :metaKeySelection="false"
          dataKey="id"
          @rowSelect="this.onRowSelect"
          @rowUnselect="this.onRowUnselect"
          :resizableColumns="true"
          columnResizeMode="fit"
          responsiveLayout="scroll"
          :first="firstRecordIndex"
          ref="dt"
          :rowHover="true"
        >
          <Column field="id">
            <template #header>
              <Checkbox
                :binary="true"
                @change="this.tudoSelecionadoClick()"
                v-model="this.tudoSelecionado"
                onIcon="pi pi-check"
                offIcon="pi pi-times"
              />&nbsp; Id
            </template>
            <template #body="r">
              {{ ("0".repeat(8) + r.data.id).slice(-8) }}
            </template>
          </Column>

          <Column field="carteira.codigo">
            <template #header> Carteira<br />Categoria<br />Modo</template>
            <template class="text-right" #body="r">
              <b>{{ r.data.carteira.descricaoMontada }}</b
              ><br />
              {{ r.data.categoria.descricaoMontada }}<br />
              {{ r.data.modo.descricaoMontada }}
            </template>
          </Column>

          <Column field="descricao" header="Descrição">
            <template class="text-right" #body="r">
              <div style="max-width: 50em; white-space: pre-wrap">
                <b>{{ r.data.descricaoMontada }}</b>

                <div v-if="r.data.categoria.codigoSuper === 1 && r.data.sacado">
                  <small>{{ r.data.sacado }}</small>
                </div>
                <div v-if="r.data.categoria.codigoSuper === 2 && r.data.cedente">
                  <small>{{ r.data.sacado }}</small>
                </div>

                <div class="text-right w-100">
                  <template v-if="r.data.chequeNumCheque">
                    <span class="ml-1 badge badge-pill badge-danger"
                      ><i class="fas fa-money-check-alt"></i> Cheque</span
                    >
                  </template>

                  <template v-if="r.data.recorrente">
                    <span class="ml-1 badge badge-pill badge-info"
                      ><i class="fas fa-redo"></i> Recorrente</span
                    >
                  </template>

                  <template v-if="r.data.parcelamento">
                    <span class="ml-1 badge badge-pill badge-info"
                      ><i class="fas fa-align-justify"></i> Parcelamento</span
                    >
                  </template>

                  <template v-if="r.data?.cadeia?.id && !r.data.recorrente && !r.data.parcelamento">
                    <a
                      class="ml-1 badge badge-pill badge-success"
                      :href="'/v/fin/cadeia/exibirMovimentacoes?id=' + r.data?.cadeia?.id"
                      target="_blank"
                      style="text-decoration: none; color: white"
                      ><i class="fas fa-link"></i> Em cadeia</a
                    >
                  </template>

                  <span
                    v-if="
                      r.data.transferenciaEntreCarteiras &&
                      r.data.movimentacaoOposta &&
                      r.data.movimentacaoOposta.categoria
                    "
                    class="ml-1 badge badge-pill badge-secondary"
                  >
                    <span v-if="r.data?.movimentacaoOposta?.categoria?.codigo === 199"
                      ><i class="fas fa-sign-out-alt"></i> Para:
                    </span>
                    <span v-if="r.data?.movimentacaoOposta?.categoria?.codigo === 299"
                      ><i class="fas fa-sign-out-alt"></i> De:
                    </span>
                    {{ r.data.movimentacaoOposta.carteira.descricaoMontada }}
                  </span>
                </div>
              </div>
            </template>
          </Column>

          <!-- Não sei pq se colocar a dtVenctoEfetiva ele não renderiza a coluna -->
          <Column field="dtVencto" header="Dt Vencto">
            <template #body="r">
              <div
                class="text-center"
                :title="'Dt Vencto: ' + new Date(r.data.dtVencto).toLocaleString().substring(0, 10)"
              >
                {{ new Date(r.data.dtVenctoEfetiva).toLocaleString().substring(0, 10) }}
                <div class="clearfix"></div>
                <span
                  v-if="r.data.status === 'REALIZADA'"
                  :class="
                    'text-center badge badge-pill badge-' +
                    (r.data.categoria.codigoSuper === 1 ? 'success' : 'danger')
                  "
                  style="width: 82px"
                >
                  <i class="fas fa-check-double" title="Movimentação realizada"></i> Realizada</span
                >

                <span v-else class="text-center badge badge-pill badge-info" style="width: 82px">
                  '<i class="fas fa-hourglass-half" title="Movimentação abera"></i> Aberta</span
                >
              </div>
            </template>
          </Column>

          <Column field="valor" header="Valor">
            <template #body="r">
              <div
                class="text-right"
                :style="
                  'font-weight: bolder; color: ' +
                  (r.data.categoria.codigoSuper === 1 ? 'blue' : 'red')
                "
              >
                {{
                  parseFloat(r.data.valor ?? 0).toLocaleString("pt-BR", {
                    style: "currency",
                    currency: "BRL",
                  })
                }}
              </div>
            </template>
          </Column>

          <Column field="updated" header="">
            <template class="text-right" #body="r">
              <div class="d-flex justify-content-end">
                <a
                  v-if="r.data.status === 'ABERTA'"
                  role="button"
                  class="btn btn-warning btn-sm"
                  :href="'/v/fin/movimentacao/aPagarReceber/form?rPagamento=S&id=' + r.data.id"
                  title="Registro de Pagamento"
                >
                  <i class="fas fa-dollar-sign"></i
                ></a>

                <a
                  role="button"
                  class="btn btn-primary btn-sm ml-1"
                  title="Editar registro"
                  :href="'/v/fin/movimentacao/aPagarReceber/form?id=' + r.data.id"
                  ><i class="fas fa-wrench" aria-hidden="true"></i
                ></a>
                <a
                  role="button"
                  class="btn btn-danger btn-sm ml-1"
                  title="Deletar registro"
                  @click="this.deletar(r.data.id)"
                  ><i class="fas fa-trash" aria-hidden="true"></i
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

          <template #groupheader="r">
            <h5>{{ new Date(r.data.dtVenctoEfetiva).toLocaleString().substring(0, 10) }}</h5>
          </template>

          <template #groupfooter="r">
            <td colspan="4">
              <div class="text-right">Total:</div>
            </td>
            <td
              class="text-right"
              :style="
                'font-weight: bolder; color: ' +
                (this.somatorios.get(r.data.dtVenctoEfetiva) >= 0 ? 'blue' : 'red')
              "
            >
              {{
                parseFloat(this.somatorios.get(r.data.dtVenctoEfetiva)).toLocaleString("pt-BR", {
                  style: "currency",
                  currency: "BRL",
                })
              }}
            </td>
            <td></td>
          </template>

          <template #footer v-if="this.somatorios.size > 1">
            <div class="h5 text-right">
              Total Geral:
              <span
                class="text-right"
                :style="'font-weight: bolder; color: ' + (this.totalGeral >= 0 ? 'blue' : 'red')"
              >
                {{
                  parseFloat(this.totalGeral).toLocaleString("pt-BR", {
                    style: "currency",
                    currency: "BRL",
                  })
                }}
              </span>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </div>
</template>

<script>
import Checkbox from "primevue/checkbox";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import InlineMessage from "primevue/inlinemessage";
import Sidebar from "primevue/sidebar";
import { mapGetters, mapMutations } from "vuex";
import {
  api,
  CrosierBlock,
  CrosierCalendar,
  CrosierCurrency,
  CrosierMultiSelectEntity,
  CrosierInputText,
} from "crosier-vue";
import moment from "moment";
import axios from "axios";
import printJS from "print-js";

export default {
  name: "list_aPagarReceber",

  components: {
    Checkbox,
    ConfirmDialog,
    CrosierBlock,
    DataTable,
    Column,
    InlineMessage,
    Toast,
    Sidebar,
    CrosierMultiSelectEntity,
    CrosierCalendar,
    CrosierCurrency,
    CrosierInputText,
  },

  emits: [
    "beforeFilter",
    "afterFilter",
    "row-select",
    "row-unselect",
    "update:selection",
    "tudoSelecionadoClick",
  ],

  data() {
    return {
      savedFilter: {},
      totalRecords: 0,
      tableData: null,
      somatorios: new Map(),
      totalGeral: 0,
      firstRecordIndex: 0,
      multiSortMeta: [],
      accordionActiveIndex: null,
      tudoSelecionado: false,
      visibleRight: false,
      apiResource: "/api/fin/movimentacao",
      selection: [],
    };
  },

  created() {
    if (this.preselecao) {
      localStorage.removeItem(this.dataTableStateKey);
    }
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
        console.error(`aPagarReceberList: |${mutationName}| n/d`);
        console.error(e);
      }
    },

    toggleFiltros() {
      this.accordionActiveIndex = this.accordionActiveIndex === 0 ? null : 0;
      this.visibleRight = !this.visibleRight;
    },

    async doFilter() {
      this.setLoading(true);

      this.filters["dtVenctoEfetiva[after]"] = this.filters["dtVenctoEfetiva[after]"]
        ? `${moment(this.filters["dtVenctoEfetiva[after]"]).format("YYYY-MM-DD")}T00:00:00-03:00`
        : `${this.moment().format("YYYY-MM")}-01T00:00:00-03:00`;
      this.filters["dtVenctoEfetiva[before]"] = this.filters["dtVenctoEfetiva[before]"]
        ? `${moment(this.filters["dtVenctoEfetiva[before]"]).format("YYYY-MM-DD")}T23:59:59-03:00`
        : `${this.moment().endOf("month").format("YYYY-MM-DD")}T23:59:59-03:00`;

      // const diff = moment(this.filters["dtVenctoEfetiva[before]"]).diff(
      //   moment(this.filters["dtVenctoEfetiva[after]"]),
      //   "days"
      // );
      // if (diff > 31) {
      //   this.filters["dtVenctoEfetiva[after]"] = `${this.moment().format(
      //     "YYYY-MM"
      //   )}-01T00:00:00-03:00`;
      //   this.filters["dtVenctoEfetiva[before]"] = `${this.moment()
      //     .endOf("month")
      //     .format("YYYY-MM-DD")}T23:59:59-03:00`;
      //   this.$toast.add({
      //     severity: "warn",
      //     summary: "Atenção",
      //     detail: "Não é possível pesquisar com período superior a 31 dias",
      //     life: 5000,
      //   });
      // }

      const rows = 500; // Number.MAX_SAFE_INTEGER;
      const page = 1;

      const response = await api.get({
        apiResource: this.apiResource,
        page,
        rows,
        order: { dtVenctoEfetiva: "ASC", "categoria.codigoSuper": "ASC", valor: "ASC" },
        filters: this.filters,
        defaultFilters: this.defaultFilters,
        properties: [
          "id",
          "descricao",
          "status",
          "descricaoMontada",
          "dtVencto",
          "dtVenctoEfetiva",
          "valorFormatted",
          "categoria.descricaoMontada",
          "categoria.codigoSuper",
          "carteira.descricaoMontada",
          "modo.descricaoMontada",
          "updated",
          "sacado",
          "cedente",
          "chequeNumCheque",
          "recorrente",
          "parcelamento",
          "cadeia.id",
          "transferenciaEntreCarteiras",
          "movimentacaoOposta.categoria.codigo",
          "movimentacaoOposta.carteira.descricaoMontada",
        ],
      });

      this.totalRecords = response.data["hydra:totalItems"];
      this.tableData = response.data["hydra:member"];

      this.somatorios = new Map();

      this.tableData.forEach((m) => {
        const total = this.somatorios.get(m.dtVenctoEfetiva) || 0;
        const valor = m.categoria.codigoSuper === 1 ? m.valor : -m.valor;
        this.somatorios.set(m.dtVenctoEfetiva, total + valor);
      });

      this.tableData.forEach((m) => {
        this.totalGeral += m.categoria.codigoSuper === 1 ? m.valor : -m.valor;
      });

      // salva os filtros no localStorage
      localStorage.setItem(this.filtersOnLocalStorage, JSON.stringify(this.filters));

      this.totalRecords = response.data["hydra:totalItems"];
      this.tableData = response.data["hydra:member"];
      this.setFilters(this.filters);

      this.$emit("afterFilter", this.tableData);
      this.handleTudoSelecionado();

      this.visibleRight = false;

      this.selection = null;

      this.setLoading(false);
    },

    doClearFilters() {
      this.setFilters({});
      localStorage.setItem(this.filtersOnLocalStorage, null);
      this.$refs.dt.resetPage();
      this.doFilter({ event: { first: 0 } });
    },

    tudoSelecionadoClick() {
      this.selection = this.tudoSelecionado ? [...this.tableData] : [];
    },

    onUpdateSelection($event) {
      this.handleTudoSelecionado();
      this.$emit("update:selection", $event);
    },

    handleTudoSelecionado() {
      this.$nextTick(() => {
        if (this.selection && this.tableData) {
          try {
            const selectionIds = this.selection.map((e) => e.id).sort();
            const values = this.tableData;
            const valuesIds = values.map((e) => e.id).sort();
            this.tudoSelecionado = JSON.stringify(selectionIds) === JSON.stringify(valuesIds);
          } catch (e) {
            console.error("Erro - handleTudoSelecionado");
            console.error(e);
          }
        }
      });
    },

    onRowSelect($event) {
      this.$emit("row-select", $event);
      this.handleTudoSelecionado();
    },

    onRowUnselect($event) {
      this.$emit("row-unselect", $event);
      this.handleTudoSelecionado();
    },

    deletar(id) {
      this.$confirm.require({
        acceptLabel: "Sim",
        rejectLabel: "Não",
        message: "Confirmar a operação?",
        header: "Atenção!",
        icon: "pi pi-exclamation-triangle",
        group: "confirmDialog_aPagarReceberList",
        accept: async () => {
          this.setLoading(true);
          try {
            const deleteUrl = `${this.apiResource}${id}`;
            const rsDelete = await api.delete(deleteUrl);
            if (!rsDelete) {
              throw new Error("rsDelete n/d");
            }
            if (rsDelete?.status === 204) {
              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                detail: "Registro deletado com sucesso",
                life: 5000,
              });
              await this.doFilter();
            } else if (rsDelete?.data && rsDelete.data["hydra:description"]) {
              throw new Error(`status !== 204: ${rsDelete?.data["hydra:description"]}`);
            } else if (rsDelete?.statusText) {
              throw new Error(`status !== 204: ${rsDelete?.statusText}`);
            } else {
              throw new Error("Erro ao deletar (erro n/d, status !== 204)");
            }
          } catch (e) {
            console.error(e);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: "Ocorreu um erro ao deletar",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },

    async imprimir() {
      this.setLoading(true);
      const pdf = await axios.post("/fin/movimentacao/aPagarReceber/rel", {
        tableData: JSON.stringify(this.tableData),
        filters: JSON.stringify(this.filters),
        somatorios: JSON.stringify(Object.fromEntries(this.somatorios)),
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

    async imprimirFicha() {
      if (!this.selection || this.selection.length < 1) {
        this.$toast.add({
          severity: "warn",
          summary: "Atenção",
          detail: "Nenhuma movimentação selecionada",
          life: 5000,
        });
        return;
      }
      this.setLoading(true);
      const pdf = await axios.post("/fin/movimentacao/aPagarReceber/fichaMovimentacao", {
        movsSelecionadas: JSON.stringify(this.selection),
      });
      printJS({
        printable: pdf.data,
        type: "pdf",
        base64: true,
        targetStyles: "*",
      });
      this.setLoading(false);
    },

    async trocaPeriodo(proximo) {
      const ini = moment(this.filters["dtVenctoEfetiva[after]"]).format("YYYY-MM-DD");
      const fim = moment(this.filters["dtVenctoEfetiva[before]"]).format("YYYY-MM-DD");

      const rs = await axios.get(
        `/base/diaUtil/incPeriodo/?ini=${ini}&fim=${fim}&futuro=${proximo}&comercial=false&financeiro=false`
      );

      this.filters["dtVenctoEfetiva[after]"] = new Date(`${rs.data.dtIni}T00:00:00-03:00`);
      this.filters["dtVenctoEfetiva[before]"] = new Date(`${rs.data.dtFim}T23:59:59-03:00`);

      this.doFilter();
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      filters: "getFilters",
      defaultFilters: "getDefaultFilters",
    }),

    filtersOnLocalStorage() {
      return "filters_list_aPagarReceber";
    },

    dataTableStateKey() {
      return "dt-state_list_aPagarReceber";
    },

    isFiltering() {
      if (this.filters && Object.keys(this.filters).length > 0) {
        // eslint-disable-next-line no-restricted-syntax
        for (const [, value] of Object.entries(this.filters)) {
          if (value ?? false) {
            return true;
          }
        }
      }
      return false;
    },
  },
};
</script>
<style>
.p-multiselect .p-multiselect-label {
  padding: 0 !important;
  margin: 0 !important;
}

.p-multiselect.p-multiselect-chip .p-multiselect-token {
  padding: 0 0.5rem 0 0.5rem !important;
}
</style>

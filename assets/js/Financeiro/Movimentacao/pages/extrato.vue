<template>
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <CrosierBlock :loading="this.loading" />

  <Sidebar class="p-sidebar-lg" v-model:visible="this.visibleRight" position="right">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title"><i class="fas fa-search"></i> Filtros</h5>
        <form @submit.prevent="this.doFilter()" class="notSubmit">
          <div class="form-row">
            <CrosierDropdownEntity
              v-model="this.filters.carteira"
              entity-uri="/api/fin/carteira"
              optionLabel="descricaoMontada"
              :optionValue="null"
              :orderBy="{ codigo: 'ASC' }"
              :filters="{ concreta: true }"
              label="Carteira"
              id="carteira"
            />
          </div>

          <div class="form-row">
            <CrosierCalendar
              label="Desde..."
              col="6"
              inputClass="crsr-date"
              id="dt"
              :baseZIndex="10000"
              v-model="this.filters['dtPagto[after]']"
            />

            <CrosierCalendar
              label="até..."
              col="6"
              inputClass="crsr-date"
              id="dt"
              :baseZIndex="10000"
              v-model="this.filters['dtPagto[before]']"
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
            <h3>Extrato</h3>
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
              v-model="this.filters['dtPagto[after]']"
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
              v-model="this.filters['dtPagto[before]']"
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
          <div>
            <CrosierDropdownEntity
              v-model="this.filters.carteira"
              entity-uri="/api/fin/carteira"
              optionLabel="descricaoMontada"
              :optionValue="null"
              :orderBy="{ codigo: 'ASC' }"
              :filters="{ concreta: true }"
              label="Carteira"
              id="carteira"
              @change="this.doFilterNextTick"
            />
          </div>

          <div class="d-sm-flex flex-nowrap">
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
              <i class="fas fa-sync-alt"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <DataTable
          v-if="this.saldos"
          rowGroupMode="subheader"
          groupRowsBy="dtPagto"
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

          <Column field="id">
            <template #header>Categoria<br />Modo</template>
            <template class="text-right" #body="r">
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

          <Column field="id" header="Dt Vencto" style="width: 1% !important">
            <template #body="r">
              <div
                class="text-center"
                :title="
                  'Dt Vencto: ' + new Date(r.data.dtVenctoEfetiva).toLocaleString().substring(0, 10)
                "
              >
                {{ new Date(r.data.dtVencto).toLocaleString().substring(0, 10) }}
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

          <Column field="valor" header="Valor" style="width: 1% !important">
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

          <Column field="updated" header="" style="width: 1% !important">
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
                  :href="'form?id=' + r.data.id"
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

          <template #header>
            <div class="h5 text-right">
              {{
                parseFloat(
                  this.saldos.get(
                    this.moment(this.tableData[0].dtPagto).subtract(1, "days").format("YYYY-MM-DD")
                  )["SALDO_POSTERIOR_REALIZADAS"]
                ).toLocaleString("pt-BR", { style: "currency", currency: "BRL" })
              }}
            </div>
          </template>

          <template #groupheader="r">
            <div class="h5 float-left" style="font-weight: bolder">
              {{ new Date(r.data.dtPagto).toLocaleString().substring(0, 10) }}
            </div>
          </template>

          <template #groupfooter="r">
            <td
              class="h5 text-right"
              colspan="4"
              :style="
                'font-weight: bolder; color: ' +
                (this.saldos.get(this.moment(r.data.dtPagto).format('YYYY-MM-DD'))[
                  'SALDO_POSTERIOR_REALIZADAS'
                ] >= 0
                  ? 'blue'
                  : 'red')
              "
            >
              Saldo em {{ this.moment(r.data.dtPagto).format("DD/MM/YYYY") }}:
            </td>
            <td
              class="text-right h5"
              :style="
                'font-weight: bolder; color: ' +
                (this.saldos.get(this.moment(r.data.dtPagto).format('YYYY-MM-DD'))[
                  'SALDO_POSTERIOR_REALIZADAS'
                ] >= 0
                  ? 'blue'
                  : 'red')
              "
            >
              {{
                parseFloat(
                  this.saldos.get(this.moment(r.data.dtPagto).format("YYYY-MM-DD"))[
                    "SALDO_POSTERIOR_REALIZADAS"
                  ]
                ).toLocaleString("pt-BR", { style: "currency", currency: "BRL" })
              }}
            </td>
            <td></td>
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
import { api, CrosierBlock, CrosierDropdownEntity, CrosierCalendar } from "crosier-vue";
import moment from "moment";
import axios from "axios";
import printJS from "print-js";

export default {
  name: "list_recorrente",

  components: {
    Checkbox,
    ConfirmDialog,
    CrosierBlock,
    DataTable,
    Column,
    InlineMessage,
    Toast,
    Sidebar,
    CrosierDropdownEntity,
    CrosierCalendar,
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
      saldos: null, // deve ficar como null até ser preenchido para poder exibir a DataTable corretamente
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
    ...mapMutations(["setLoading", "setFilters"]),

    moment(date) {
      return moment(date);
    },

    toggleFiltros() {
      this.accordionActiveIndex = this.accordionActiveIndex === 0 ? null : 0;
      this.visibleRight = !this.visibleRight;
    },

    async trocaPeriodo(proximo) {
      const ini = moment(this.filters["dtPagto[after]"]).format("YYYY-MM-DD");
      const fim = moment(this.filters["dtPagto[before]"]).format("YYYY-MM-DD");

      const rs = await axios.get(
        `/base/diaUtil/incPeriodo/?ini=${ini}&fim=${fim}&futuro=${proximo}&comercial=false&financeiro=false`
      );

      this.filters["dtPagto[after]"] = new Date(`${rs.data.dtIni}T00:00:00-03:00`);
      this.filters["dtPagto[before]"] = new Date(`${rs.data.dtFim}T23:59:59-03:00`);

      this.doFilter();
    },

    doFilterNextTick() {
      this.$nextTick(() => {
        this.doFilter();
      });
    },

    async doFilter() {
      this.setLoading(true);

      if (typeof this.filters.carteira === "string" || this.filters.carteira instanceof String) {
        const rCarteira = await axios.get(this.filters.carteira);
        this.filters.carteira = rCarteira.data;
      }

      if (!this.filters["dtPagto[after]"]) {
        this.filters["dtPagto[after]"] = `${this.moment()
          .subtract(7, "days")
          .format("YYYY-MM-DD")}T00:00:00-03:00`;
      } else {
        this.filters["dtPagto[after]"] = `${this.moment(this.filters["dtPagto[after]"]).format(
          "YYYY-MM-DD"
        )}T00:00:00-03:00`;
      }

      if (!this.filters["dtPagto[before]"]) {
        this.filters["dtPagto[before]"] = `${this.moment().format("YYYY-MM-DD")}T23:59:59-03:00`;
      } else {
        this.filters["dtPagto[before]"] = `${this.moment(this.filters["dtPagto[before]"]).format(
          "YYYY-MM-DD"
        )}T23:59:59-03:00`;
      }

      if (!this.filters.carteira) {
        const rsCarteiras = await api.get({
          apiResource: "/api/fin/carteira",
          allRows: true,
          order: { codigo: "ASC" },
          filters: { concreta: true },
          properties: ["id", "descricaoMontada"],
        });
        this.filters.carteira = rsCarteiras.data["hydra:member"][0];
      }

      const filters = { ...this.filters };
      filters.carteira = filters.carteira["@id"];

      const rows = Number.MAX_SAFE_INTEGER;
      const page = 1;

      const response = await api.get({
        apiResource: this.apiResource,
        page,
        rows,
        order: { dtPagto: "ASC", "categoria.codigoSuper": "ASC", valor: "ASC" },
        filters,
        defaultFilters: this.defaultFilters,
        properties: [
          "id",
          "descricao",
          "status",
          "descricaoMontada",
          "dtVencto",
          "dtVenctoEfetiva",
          "dtUtil",
          "dtPagto",
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

      const saldos = new Map();
      this.saldos = null;

      this.tableData.forEach(async (m) => {
        const dtPagtoF = this.moment(m.dtPagto).format("YYYY-MM-DD");
        saldos.set(dtPagtoF, {});
      });

      for (const [key, value] of saldos.entries()) {
        const rsSaldo = await axios.get(
          `/api/fin/movimentacao/extrato/saldos/${this.filters.carteira.id}/${key}`
        );
        saldos.set(key, rsSaldo?.data?.DATA);
      }

      const dtAnteriorSaldo = this.moment(this.tableData[0].dtPagto)
        .subtract(1, "days")
        .format("YYYY-MM-DD");

      const rsSaldoInicial = await axios.get(
        `/api/fin/movimentacao/extrato/saldos/${this.filters.carteira.id}/${dtAnteriorSaldo}`
      );
      saldos.set(dtAnteriorSaldo, rsSaldoInicial?.data?.DATA);

      this.saldos = saldos;

      // salva os filtros no localStorage
      localStorage.setItem(this.filtersOnLocalStorage, JSON.stringify(this.filters));

      this.totalRecords = response.data["hydra:totalItems"];
      this.tableData = response.data["hydra:member"];
      this.setFilters(this.filters);

      this.$emit("afterFilter", this.tableData);
      this.handleTudoSelecionado();

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
        group: "confirmDialog_crosierListS",
        accept: async () => {
          this.setLoading(true);
          try {
            const deleteUrl = `${this.apiResource}/${id}`;
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

    processar() {
      if (!this.selection || this.selection.length < 1) {
        this.$toast.add({
          severity: "error",
          summary: "Erro",
          detail: "Nenhuma movimentação selecionada para processar",
          life: 5000,
        });
        return;
      }
      this.$confirm.require({
        acceptLabel: "Sim",
        rejectLabel: "Não",
        message: "Confirmar a operação?",
        header: "Atenção!",
        icon: "pi pi-exclamation-triangle",
        group: "confirmDialog_crosierListS",
        accept: async () => {
          this.setLoading(true);
          try {
            const processarUrl = "/fin/movimentacao/recorrente/processar";
            const rs = await api.post(processarUrl, this.selection);

            if (rs?.status === 200) {
              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                detail: "Movimentações processadas com sucesso",
                life: 5000,
              });
              await this.doFilter();
            } else {
              throw new Error("Erro ao processar (status <> 200)");
            }
          } catch (e) {
            console.error(e);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: "Ocorreu um erro ao processar",
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
      return "filters_list_recorrente";
    },

    dataTableStateKey() {
      return "dt-state_list_recorrente";
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

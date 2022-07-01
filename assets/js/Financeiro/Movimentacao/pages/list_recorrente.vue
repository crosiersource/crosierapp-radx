<template>
  <ConfirmDialog />
  <Toast position="bottom-right" class="mb-5" />
  <CrosierBlock :loading="this.loading" />

  <div class="container-fluid">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Movimentações Recorrentes</h3>
            <h5 v-if="this.filters.mesAno">
              {{ moment(this.filters["dtVencto[after]"]).format("MMMM") }} de
              {{ moment(this.filters["dtVencto[after]"]).format("YYYY") }}
            </h5>
          </div>

          <div class="ml-auto"></div>
          <div class="mt-3">
            <CrosierMesAno
              :showLabel="false"
              mesCorrenteInicial
              v-model="this.filters.mesAno"
              id="mesAno"
              @update:modelValue="this.doFilter"
            />
          </div>

          <div>
            <a type="button" class="btn btn-outline-info" href="form" title="Novo">
              <i class="fas fa-file" aria-hidden="true"></i>
            </a>

            <button
              type="button"
              class="btn btn-outline-success ml-1"
              @click="this.processar"
              title="Processar"
              id="btnProcessar"
              name="btnProcessar"
            >
              <i class="fas fa-cog"></i> Processar
            </button>

            <button
              class="btn btn-danger ml-1"
              title="Deletar registros selecionados"
              @click="this.deletarRegistrosSelecionados"
            >
              <i class="fas fa-trash" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <DataTable
          rowGroupMode="subheader"
          groupRowsBy="dtVencto"
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

          <!-- Não sei pq se colocar a dtVencto ele não renderiza a coluna -->
          <Column field="id" header="Dt Vencto">
            <template #body="r">
              <div
                class="text-center"
                :title="'Dt Vencto: ' + new Date(r.data.dtVencto).toLocaleString().substring(0, 10)"
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

          <Column field="valorTotal" header="Valor">
            <template #body="r">
              <div
                class="text-right"
                :style="
                  'font-weight: bolder; color: ' +
                  (r.data.categoria.codigoSuper === 1 ? 'blue' : 'red')
                "
              >
                {{
                  parseFloat(r.data.valorTotal ?? 0).toLocaleString("pt-BR", {
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

          <template #groupheader="r">
            <h4>{{ new Date(r.data.dtVencto).toLocaleString().substring(0, 10) }}</h4>
          </template>

          <template #groupfooter="r">
            <td colspan="4">
              <div class="text-right">Total:</div>
            </td>
            <td
              class="text-right"
              :style="
                'font-weight: bolder; color: ' +
                (this.somatorios.get(r.data.dtVencto) >= 0 ? 'blue' : 'red')
              "
            >
              {{
                parseFloat(this.somatorios.get(r.data.dtVencto)).toLocaleString("pt-BR", {
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
import { mapGetters, mapMutations } from "vuex";
import { api, CrosierBlock, CrosierMesAno } from "crosier-vue";
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
    Toast,
    CrosierMesAno,
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
        console.error(`list_recorrente: |${mutationName}| n/d`);
        console.error(e);
      }
    },

    toggleFiltros() {
      this.accordionActiveIndex = this.accordionActiveIndex === 0 ? null : 0;
      this.visibleRight = !this.visibleRight;
    },

    trocaMes(proximo) {
      this.filters.mesAno = proximo
        ? moment(this.filters.mesAno).add(1, "M")
        : moment(this.filters.mesAno).subtract(1, "M");
      this.doFilter();
    },

    async doFilter() {
      this.setLoading(true);

      this.filters.mesAno = this.filters.mesAno ?? `${moment().format("YYYY-MM")}-01`;

      this.filters["dtVencto[after]"] = `${moment(this.filters.mesAno).format(
        "YYYY-MM-DD"
      )}T00:00:00-03:00`;
      this.filters["dtVencto[before]"] = `${moment(this.filters.mesAno)
        .endOf("month")
        .format("YYYY-MM-DD")}T23:59:59-03:00`;

      const rows = Number.MAX_SAFE_INTEGER;
      const page = 1;

      const response = await api.get({
        apiResource: this.apiResource,
        page,
        rows,
        order: { dtVencto: "ASC", "categoria.codigoSuper": "ASC", valorTotal: "ASC" },
        filters: this.filters,
        defaultFilters: this.defaultFilters,
        properties: [
          "id",
          "descricao",
          "status",
          "descricaoMontada",
          "dtVencto",
          "dtVencto",
          "valorTotalFormatted",
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
        const total = this.somatorios.get(m.dtVencto) || 0;
        const valorTotal = m.categoria.codigoSuper === 1 ? m.valorTotal : -m.valorTotal;
        this.somatorios.set(m.dtVencto, total + valorTotal);
      });

      this.totalGeral = 0;

      this.tableData.forEach((m) => {
        const valorTotal = m.categoria.codigoSuper === 1 ? m.valorTotal : -m.valorTotal;
        this.totalGeral += parseFloat(valorTotal);
      });

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

    deletarRegistrosSelecionados() {
      this.$confirm.require({
        acceptLabel: "Sim",
        rejectLabel: "Não",
        message: "Confirmar a operação?",
        header: "Atenção!",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          try {
            this.selection.forEach(async (e) => {
              const deleteUrl = `${this.apiResource}/${e.id}`;
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
                delete this.selection[this.selection.indexOf(e)];
              } else if (rsDelete?.data && rsDelete.data["hydra:description"]) {
                throw new Error(`status !== 204: ${rsDelete?.data["hydra:description"]}`);
              } else if (rsDelete?.statusText) {
                throw new Error(`status !== 204: ${rsDelete?.statusText}`);
              } else {
                throw new Error("Erro ao deletar (erro n/d, status !== 204)");
              }
            });
          } catch (e) {
            console.error(e);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: "Ocorreu um erro ao deletar",
              life: 5000,
            });
          }
          await this.doFilter();
          this.setLoading(false);
        },
      });
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

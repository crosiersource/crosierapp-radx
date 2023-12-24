<template>
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <CrosierBlock :loading="this.loading" />

  <div class="container-fluid">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Movimentações de Caixas</h3>
          </div>

          <div class="ml-auto"></div>
          <div>
            <CrosierCalendar
              label="Período"
              v-model="this.filters.periodo"
              range
              comBotoesPeriodo
              maxRange="59"
              @date-select="this.doFilter"
            />
          </div>

          <div>
            <CrosierMultiSelectEntity
              style="z-index: 99999"
              v-model="this.filters.carteirasIds"
              entity-uri="/api/fin/carteira"
              optionLabel="descricaoMontada"
              optionValue="id"
              :orderBy="{ codigo: 'ASC' }"
              :filters="{ caixa: true, atual: true }"
              label="Caixas"
              id="carteiras"
            />
          </div>

          <div class="d-sm-flex flex-nowrap">
            <button
              type="button"
              class="btn btn-success ml-1 mt-3"
              @click="this.doFilter"
              title="Filtrar relatório do extrato"
            >
              <i class="fas fa-search"></i> Filtrar
            </button>

            <button
              class="btn btn-danger btn-sm ml-1 mt-3"
              title="Deletar registros selecionados"
              @click="this.deletarRegistrosSelecionados"
            >
              <i class="fas fa-trash" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <Table />
      </div>
    </div>
  </div>
</template>

<script>
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import { mapGetters, mapMutations } from "vuex";
import {
  api,
  CrosierBlock,
  CrosierDropdownEntity,
  CrosierMultiSelectEntity,
  CrosierCalendar,
} from "crosier-vue";
import moment from "moment";
import axios from "axios";
import printJS from "print-js";
import Table from "./table";

export default {
  name: "extrato",

  components: {
    ConfirmDialog,
    CrosierBlock,
    Table,
    Toast,
    CrosierDropdownEntity,
    CrosierMultiSelectEntity,
    CrosierCalendar,
  },

  data() {
    return {
      savedFilter: {},
      totalRecords: 0,
      tableData: null,
      saldos: null, // deve ficar como null até ser preenchido para poder exibir a DataTable corretamente
      saldoAnterior: 0,
      saldoFinal: 0,
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

    if (!this.filters.periodo) {
      this.filters.periodo = [new Date(this.moment().subtract(7, "days")), new Date()];
    }

    // await this.doFilter();
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

    doFilterNextTick() {
      this.$nextTick(() => {
        this.doFilter();
      });
    },

    async doFilter() {
      if (!this.filters.carteirasIds) {
        this.$toast.add({
          group: "mainToast",
          severity: "error",
          summary: "Erro",
          detail: "Selecione a(s) carteira(s)!",
          life: 5000,
        });
        return;
      }
      this.setLoading(true);

      try {
        const dtIni = this.filters.periodo[0];
        const dtFim = this.filters.periodo[1];

        this.filters["dtUtil[after]"] = `${this.moment(dtIni).format("YYYY-MM-DD")}T00:00:00-03:00`;
        this.filters["dtUtil[before]"] = `${this.moment(dtFim).format(
          "YYYY-MM-DD"
        )}T23:59:59-03:00`;

        const diff = moment(this.filters["dtUtil[before]"]).diff(
          moment(this.filters["dtUtil[after]"]),
          "days"
        );
        if (diff > 62) {
          this.filters["dtUtil[after]"] = `${this.moment().format("YYYY-MM")}-01T00:00:00-03:00`;
          this.filters["dtUtil[before]"] = `${this.moment()
            .endOf("month")
            .format("YYYY-MM-DD")}T23:59:59-03:00`;
          this.$toast.add({
            severity: "warn",
            summary: "Atenção",
            group: "mainToast",
            detail: "Não é possível pesquisar com período superior a 2 meses",
            life: 5000,
          });
        }

        if (!this.filters.carteirasIds) {
          const rsCarteiras = await api.get({
            apiResource: "/api/fin/carteira",
            allRows: true,
            order: { codigo: "ASC" },
            filters: { caixa: true, atual: true },
            properties: ["id", "descricaoMontada"],
          });
          this.filters.carteirasIds = [rsCarteiras.data["hydra:member"][0].id];
        }

        const filters = { ...this.filters };

        const rows = Number.MAX_SAFE_INTEGER;
        const page = 1;

        const response = await api.get({
          apiResource: this.apiResource,
          page,
          rows,
          order: { dtUtil: "ASC", "categoria.codigoSuper": "ASC", valorTotal: "ASC" },
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

        if (this.exibeSaldos) {
          const umDiaAntes = `${this.moment(this.filters["dtUtil[after]"])
            .subtract(1, "days")
            .format("YYYY-MM-DD")}T00:00:00-03:00`;

          const rsSaldos = await axios.get(
            `/api/fin/saldo?carteira=/api/fin/carteira/${this.filters.carteirasIds[0]}&dtSaldo[after]=${umDiaAntes}&dtSaldo[before]=${this.filters["dtUtil[before]"]}&properties[]=id&properties[]=dtSaldo&properties[]=totalRealizadas&properties[]=totalPendencias&properties[]=totalComPendentes`
          );

          this.saldos = rsSaldos.data["hydra:member"];
          this.saldoAnterior = this.saldos[0].totalRealizadas;
          this.saldoFinal = this.saldos[this.saldos.length - 1].totalRealizadas;
        }

        // salva os filtros no localStorage
        localStorage.setItem(this.filtersOnLocalStorage, JSON.stringify(this.filters));

        this.totalRecords = response.data["hydra:totalItems"];
        this.tableData = response.data["hydra:member"];
        this.setFilters(this.filters);

        this.$emit("afterFilter", this.tableData);
        this.handleTudoSelecionado();

        this.visibleRight = false;
      } catch (e) {
        console.error(e);
        this.$toast.add({
          group: "mainToast",
          severity: "error",
          summary: "Erro",
          detail: "Ocorreu um erro ao efetuar a operação",
          life: 5000,
        });
      }

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
                group: "mainToast",
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
              group: "mainToast",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },

    deletarRegistrosSelecionados() {
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
                  group: "mainToast",
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
              group: "mainToast",
              life: 5000,
            });
          }
          await this.doFilter();
          this.setLoading(false);
        },
      });
    },

    /**
     * Pode pegar o saldo através de um Date ou da string 'ANTERIOR' para o saldo anterior.
     *
     * @param d
     * @returns {*|number|null}
     */
    getSaldo(d) {
      let saldo = null;
      if (d === "ANTERIOR") {
        if (this.tableData && this.tableData[0] && this.tableData[0].dtUtil) {
          saldo = this.saldos.find((e) => {
            return (
              this.moment(e.dtSaldo).format("YYYY-MM-DD") ===
              this.moment(this.tableData[0].dtUtil).subtract(1, "days").format("YYYY-MM-DD")
            );
          });
        }
      } else {
        saldo = this.saldos.find(
          (e) => this.moment(e.dtSaldo).format("YYYY-MM-DD") === this.moment(d).format("YYYY-MM-DD")
        );
      }
      return saldo?.totalRealizadas ?? 0;
    },

    getSaldoFormatted(saldo) {
      const valor = this.getSaldo(saldo);
      return this.getValorFormatted(valor);
    },

    getValorFormatted(valor) {
      return parseFloat(valor).toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL",
      });
    },

    async delay(ms) {
      return new Promise((resolve) => setTimeout(resolve, ms));
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      filters: "getFilters",
      defaultFilters: "getDefaultFilters",
    }),

    filtersOnLocalStorage() {
      return "filters_extrato";
    },

    dataTableStateKey() {
      return "dt-state_extrato";
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

    exibeSaldos() {
      return this.filters?.carteirasIds?.length === 1;
    },
  },
};
</script>
<style>
.dp__pointer.dp__input.dp__input_icon_pad {
  height: 31.1562px;
}
</style>

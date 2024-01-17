<template>
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <CrosierBlock :loading="this.loading" />

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
              label="Período"
              v-model="this.filters.periodo"
              range
              comBotoesPeriodo
              maxRange="59"
              @date-select="this.doFilter"
            />
          </div>

          <div>
            <CrosierDropdownEntity
              v-model="this.filters.carteira"
              entity-uri="/api/fin/carteira"
              optionLabel="descricaoMontada"
              :optionValue="null"
              :orderBy="{ codigo: 'ASC' }"
              :filters="{ concreta: true, caixa: false, atual: true }"
              label="Carteira"
              id="carteira"
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
        <div v-if="!this.saldos" class="row col">Selecione os filtros.</div>
        <DataTable
          v-else
          rowGroupMode="subheader"
          groupRowsBy="dtUtil"
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
          <template #empty> Nenhum dado a exibir.</template>
          <Column field="id">
            <template #body="r">
              {{ ("0".repeat(8) + r.data.id).slice(-8) }}
            </template>
          </Column>

          <Column field="id">
            <template #header>Categoria<br />Modo</template>
            <template #body="r">
              <span title="{{ r.data.modo.descricaoMontada }}">{{
                r.data.categoria.descricaoMontada
              }}</span>
            </template>
          </Column>

          <Column field="descricao" header="Descrição">
            <template #body="r">
              <div style="max-width: 50em; white-space: pre-wrap">
                <b>{{ r.data.descricaoMontada }}</b>

                <div v-if="r.data.categoria.codigoSuper === 1 && r.data.sacado">
                  <small>{{ r.data.sacado }}</small>
                </div>
                <div v-if="r.data.categoria.codigoSuper === 2 && r.data.cedente">
                  <small>{{ r.data.cedente }}</small>
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

          <Column field="id" header="Data" style="width: 1% !important">
            <template #body="r">
              <div
                class="text-center"
                :title="
                  'Dt Vencto: ' + new Date(r.data.dtVenctoEfetiva).toLocaleString().substring(0, 10)
                "
              >
                {{ new Date(r.data.dtUtil).toLocaleString().substring(0, 10) }}
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

                <span
                  v-if="r.data.status === 'ABERTA'"
                  class="text-center badge badge-pill badge-info"
                  style="width: 82px"
                >
                  <i class="fas fa-hourglass-half" title="Movimentação aberta"></i> Aberta</span
                >

                <span
                  v-if="r.data.status === 'ESTORNADA'"
                  class="text-center badge badge-pill badge-dark"
                  style="width: 82px"
                >
                  <i class="fas fa-hourglass-half" title="Movimentação estornada"></i>
                  Estornada</span
                >
              </div>
            </template>
          </Column>

          <Column field="valorTotal" header="Valor" style="width: 1% !important">
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

          <Column field="updated" header="" style="width: 1% !important">
            <template #body="r">
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
                  :href="'/fin/movimentacao/edit/' + r.data.id"
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
            <div class="h5 text-right mr-5" v-if="this.saldoAnterior">
              Saldo anterior: {{ this.getValorFormatted(this.saldoAnterior) }}
            </div>
          </template>

          <template #footer>
            <div class="h5 text-right mr-5" v-if="this.saldoFinal && totalRecords === 0">
              Saldo posterior: {{ this.getValorFormatted(this.saldoFinal) }}
            </div>
          </template>

          <template #groupheader="r">
            <div class="h5 float-left" style="font-weight: bolder">
              {{ new Date(r.data.dtUtil).toLocaleString().substring(0, 10) }}
            </div>
          </template>

          <template #groupfooter="r">
            <td
              class="h5 text-right"
              colspan="4"
              :style="
                'font-weight: bolder; color: ' + (this.getSaldo(r.data.dtUtil) >= 0)
                  ? 'blue'
                  : 'red'
              "
            >
              Saldo em {{ this.moment(r.data.dtUtil).format("DD/MM/YYYY") }}:
            </td>
            <td
              class="text-right h5"
              :style="
                'font-weight: bolder; color: ' + (this.getSaldo(r.data.dtUtil) >= 0)
                  ? 'blue'
                  : 'red'
              "
            >
              {{ this.getSaldoFormatted(r.data.dtUtil) }}
            </td>
            <td>
              <button
                type="button"
                @click="this.consolidar(r.data.dtUtil)"
                class="btn btn-sm btn-block btn-outline-primary"
                title="Consolidar carteira nesta data"
              >
                <i class="fas fa-check"></i>
              </button>
            </td>
          </template>
        </DataTable>

        <div class="d-flex justify-content-end">
          <button
            class="btn btn-outline-info btn-sm ml-1 mt-3"
            title="Limpar configurações da tabela"
            @click="this.limparConfiguracoesDaTabela"
          >
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import { mapGetters, mapMutations } from "vuex";
import { api, CrosierBlock, CrosierCalendar, CrosierDropdownEntity } from "crosier-vue";
import moment from "moment";
import axios from "axios";
import printJS from "print-js";

export default {
  name: "extrato",

  components: {
    ConfirmDialog,
    CrosierBlock,
    DataTable,
    Column,
    Toast,
    CrosierDropdownEntity,
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
      if (!this.filters.carteira) {
        this.$toast.add({
          group: "mainToast",
          severity: "error",
          summary: "Erro",
          detail: "Selecione a carteira!",
          life: 5000,
        });
        return;
      }
      this.setLoading(true);

      try {
        if (typeof this.filters.carteira === "string" || this.filters.carteira instanceof String) {
          const rCarteira = await axios.get(this.filters.carteira);
          this.filters.carteira = rCarteira.data;
        }

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

        if (!this.filters.carteira) {
          const rsCarteiras = await api.get({
            apiResource: "/api/fin/carteira",
            allRows: true,
            order: { codigo: "ASC" },
            filters: { concreta: true, atual: true },
            properties: ["id", "descricaoMontada"],
          });
          this.filters.carteira = rsCarteiras.data["hydra:member"][0];
        }

        const filters = { ...this.filters };
        filters.carteira = filters.carteira["@id"];
        filters.status = "ABERTA"; // RTA

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

        const umDiaAntes = `${this.moment(this.filters["dtUtil[after]"])
          .subtract(1, "days")
          .format("YYYY-MM-DD")}T00:00:00-03:00`;

        const rsSaldos = await axios.get(
          `/api/fin/saldo?carteira=${this.filters.carteira["@id"]}&dtSaldo[after]=${umDiaAntes}&dtSaldo[before]=${this.filters["dtUtil[before]"]}&properties[]=id&properties[]=dtSaldo&properties[]=totalRealizadas&properties[]=totalPendencias&properties[]=totalComPendentes`
        );

        this.saldos = rsSaldos.data["hydra:member"];
        this.saldoAnterior = this.saldos[0].totalRealizadas;
        this.saldoFinal = this.saldos[this.saldos.length - 1].totalRealizadas;

        // salva os filtros no localStorage
        localStorage.setItem(this.filtersOnLocalStorage, JSON.stringify(this.filters));

        this.totalRecords = response.data["hydra:totalItems"];
        this.tableData = response.data["hydra:member"];
        this.setFilters(this.filters);

        this.$emit("afterFilter", this.tableData);
        this.handleTudoSelecionado();

        this.$nextTick(() => {
          this.corrigirLinhasSaldos();
        });

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

    async consolidar(data) {
      this.$confirm.require({
        acceptLabel: "Sim",
        rejectLabel: "Não",
        message: "Confirmar?",
        header: "Atenção!",
        group: "confirmDialog_crosierListS",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          const url = `/api/fin/carteira/consolidar/${this.filters.carteira.id}/${data.substring(
            0,
            10
          )}`;
          try {
            const rs = await axios.get(url, {
              validateStatus(status) {
                return status < 500; // Resolve only if the status code is less than 500
              },
              responseType: "json",
            });

            if (![200, 201].includes(rs?.status)) {
              this.$toast.add({
                severity: "error",
                summary: "Erro",
                group: "mainToast",
                detail: rs?.data?.EXCEPTION_MSG || rs?.data?.MSG || "Ocorreu um erro",
                life: 5000,
              });
            } else {
              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                group: "mainToast",
                detail: "Carteira consolidada com sucesso",
                life: 5000,
              });
            }
          } catch (e) {
            console.error(e);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              group: "mainToast",
              detail: "Ocorreu um erro na chamada",
              life: 5000,
            });
          }

          this.setLoading(false);
        },
      });
    },

    async delay(ms) {
      return new Promise((resolve) => setTimeout(resolve, ms));
    },

    async corrigirLinhasSaldos() {
      // this.doCorrigeLinhasSaldos();
      // await this.delay(1500); // RTA demais... Aguarda e chama de novo
      // this.doCorrigeLinhasSaldos();
    },

    limparConfiguracoesDaTabela() {
      this.$confirm.require({
        acceptLabel: "Sim",
        rejectLabel: "Não",
        message: "Confirmar a operação?",
        header: "Atenção!",
        icon: "pi pi-exclamation-triangle",
        group: "confirmDialog_crosierListS",
        accept: () => {
          localStorage.removeItem(this.dataTableStateKey);
          // now refresh the page
          window.location.reload();
        },
      });
    },

    async doCorrigeLinhasSaldos() {
      // Obtém o elemento <tr> com a classe "p-rowgroup-footer"
      const rows = document.querySelectorAll(".p-rowgroup-footer");

      rows.forEach((row) => {
        // Obtém o conteúdo do primeiro <td>
        const saldoTexto = row.querySelector("td").textContent.trim();

        // Divide o conteúdo em partes
        const parts = saldoTexto.split(":");
        const descricao = parts[0]?.trim() ?? "";
        const saldoValor = parts[1]?.trim() ?? "";

        // Cria os elementos e atribui as classes e conteúdos necessários
        const td1 = document.createElement("td");
        td1.classList.add("h5", "text-right");
        td1.setAttribute("colspan", "4");
        td1.textContent = descricao;

        const td2 = document.createElement("td");
        td2.classList.add("text-right", "h5");
        td2.textContent = saldoValor;

        const td3 = document.createElement("td");

        // Remove os elementos <td> existentes
        row.innerHTML = "";

        // Insere os elementos criados na estrutura HTML existente
        row.appendChild(td1);
        row.appendChild(td2);
        row.appendChild(td3);
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
  },
};
</script>
<style>
.dp__pointer.dp__input.dp__input_icon_pad {
  height: 31.1562px;
}
</style>

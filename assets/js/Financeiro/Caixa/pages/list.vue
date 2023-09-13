<template>
  <ConfirmDialog />
  <Toast position="bottom-right" class="mb-5" />
  <CrosierBlock :loading="this.loading" />

  <DialogMovimentacao />

  <div class="container-fluid">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Movimentações de Caixa</h3>
          </div>

          <div class="ml-auto"></div>
          <div>
            <CrosierCalendar
              label="Data"
              v-model="this.filters.dtMoviment"
              @date-select="this.doFilterNextTick"
            />
          </div>

          <div>
            <CrosierDropdownEntity
              v-model="this.filters.carteira"
              entity-uri="/api/fin/carteira"
              optionLabel="descricaoMontada"
              :orderBy="{ codigo: 'ASC' }"
              :filters="{ caixa: true, atual: true }"
              label="Caixa"
              id="carteira"
              @change="this.doFilterNextTick"
            />
          </div>

          <button
            type="button"
            class="btn btn-success ml-1 mt-3"
            @click="this.doFilterNextTick"
            title="Filtrar relatório do extrato"
          >
            <i class="fas fa-search"></i> Filtrar
          </button>

          <button
            type="button"
            class="btn btn-outline-info ml-1 mt-3"
            @click="novo"
            title="Lançar nova movimentação"
          >
            <i class="fas fa-file" aria-hidden="true"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <DataTable
          v-model:expandedRows="expandedRows"
          stateStorage="local"
          class="p-datatable-sm p-datatable-striped"
          :stateKey="this.dataTableStateKey"
          :value="this.data"
          :paginator="false"
          :multiSortMeta="multiSortMeta"
          removable-sort
          dataKey="categoriaCodigo"
          resizableColumns
          columnResizeMode="fit"
          responsiveLayout="scroll"
          ref="dt"
          rowHover
        >
          <template #empty> Nenhum dado a exibir.</template>

          <template #header>
            <div class="h5 text-right mr-5">
              Saldo anterior:
              {{
                parseFloat(this.saldoAnterior).toLocaleString("pt-BR", {
                  style: "currency",
                  currency: "BRL",
                })
              }}
            </div>
          </template>

          <template #footer>
            <div class="h5 text-right mr-5">
              Saldo posterior:
              {{
                parseFloat(this.saldoPosterior).toLocaleString("pt-BR", {
                  style: "currency",
                  currency: "BRL",
                })
              }}
            </div>
          </template>

          <Column expander style="width: 5rem" />

          <Column field="categoriaDescricaoMontada">
            <template #body="r">
              <h6
                :style="
                  'font-weight: bolder; color: ' +
                  (r.data.categoriaCodigoSuper === 1 ? 'blue' : 'red')
                "
              >
                {{ r.data.categoriaDescricaoMontada }}
              </h6>
            </template>
          </Column>

          <Column field="totalCategoria" header="Valor" style="width: 1% !important">
            <template #body="r">
              <h6
                class="text-right"
                :style="
                  'font-weight: bolder; color: ' +
                  (r.data.categoriaCodigoSuper === 1 ? 'blue' : 'red')
                "
              >
                {{
                  parseFloat(r.data.totalCategoria ?? 0).toLocaleString("pt-BR", {
                    style: "currency",
                    currency: "BRL",
                  })
                }}
              </h6>
            </template>
          </Column>

          <template #expansion="r">
            <div class="p-3">
              <h5>Movimentações</h5>
              <DataTable
                :value="r.data.movimentacoes"
                resizableColumns
                columnResizeMode="fit"
                responsiveLayout="scroll"
                rowHover
                rowGroupMode="subheader"
                groupRowsBy="modo.codigo"
              >
                <template #groupheader="r">
                  <h6 class="font-weight-bold">{{ r.data.modo.descricaoMontada }}</h6>
                </template>

                <template #groupfooter="r">
                  <td colspan="2"></td>
                  <td class="text-right">
                    <div
                      class="h6 text-right"
                      :style="
                        'font-weight: bolder; color: ' +
                        (r.data.categoria.codigoSuper === 1 ? 'blue' : 'red')
                      "
                    >
                      {{
                        parseFloat(
                          totalPorCategoriaEModo(r.data.categoria.codigo, r.data.modo.codigo)
                        ).toLocaleString("pt-BR", {
                          style: "currency",
                          currency: "BRL",
                        })
                      }}
                    </div>
                  </td>
                </template>

                <Column field="id" style="width: 10%">
                  <template #body="r">
                    {{ ("0".repeat(8) + r.data.id).slice(-8) }}
                  </template>
                </Column>

                <Column field="descricao" header="Descrição" style="width: 60%">
                  <template #body="r">
                    <b>{{ r.data.descricaoMontada }}</b>
                  </template>
                </Column>

                <Column field="valorTotal" header="Valor" style="width: 20%">
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

                <Column field="updated" header="" style="width: 10%">
                  <template #body="r">
                    <div class="d-flex justify-content-end">
                      <button
                        type="button"
                        class="btn btn-primary btn-sm ml-1"
                        title="Editar registro"
                        @click="this.editar(r.data)"
                      >
                        <i class="fas fa-wrench" aria-hidden="true"></i>
                      </button>

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
              </DataTable>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </div>
</template>

<script>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Row from "primevue/row";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import { mapActions, mapGetters, mapMutations } from "vuex";
import { api, CrosierBlock, CrosierDropdownEntity, CrosierCalendar } from "crosier-vue";
import moment from "moment";
import axios from "axios";
import printJS from "print-js";
import DialogMovimentacao from "./formDialog.vue";

export default {
  name: "caixa",

  components: {
    ConfirmDialog,
    CrosierBlock,
    DataTable,
    Column,
    Row,
    Toast,
    CrosierDropdownEntity,
    CrosierCalendar,
    DialogMovimentacao,
  },

  data() {
    return {
      expandedRows: null,
      saldos: null, // deve ficar como null até ser preenchido para poder exibir a DataTable corretamente
    };
  },

  async mounted() {
    await this.doFilter();
  },

  methods: {
    ...mapMutations([
      "setLoading",
      "setFilters",
      "setNovaMovimentacao",
      "setMovimentacao",
      "setTipoMovimentacao",
    ]),
    ...mapActions(["doFilter"]),

    moment(date) {
      return moment(date);
    },

    doFilterNextTick() {
      this.$nextTick(async () => {
        localStorage.setItem("caixaListFilters", JSON.stringify(this.filters));
        await this.doFilter();
      });
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
            const deleteUrl = `/api/fin/movimentacao/${id}`;
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

    async editar(mov) {
      this.setLoading(true);
      try {
        const rsMov = await api.get({ apiResource: `/api/fin/movimentacao/${mov.id}` });
        if (rsMov.data.id === mov.id) {
          this.setMovimentacao(rsMov.data);
          this.$store.state.exibeDialogMovimentacao = true;
        } else {
          this.$toast.add({
            severity: "error",
            summary: "Erro",
            detail: "Ocorreu um erro ao editar",
            life: 5000,
          });
        }
      } catch (e) {
        console.error(e);
      }
      this.setLoading(false);
    },

    totalPorCategoriaEModo(categoriaCodigo, modoCodigo) {
      return this.data[categoriaCodigo].movimentacoes
        .filter((e) => e.modo.codigo === modoCodigo)
        .reduce((a, b) => a + b.valorTotal, 0);
    },

    novo() {
      this.setNovaMovimentacao();
      this.$store.state.exibeDialogMovimentacao = true;
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      filters: "getFilters",
      data: "getData",
      saldoAnterior: "getSaldoAnterior",
      saldoPosterior: "getSaldoPosterior",
    }),
  },
};
</script>

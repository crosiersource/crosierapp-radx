<template>
  <ConfirmDialog />
  <Toast position="bottom-right" class="mb-5" />
  <CrosierBlock :loading="this.loading" />

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
            class="btn btn-outline-danger ml-1 mt-3"
            @click="this.abrirFecharCaixa"
            :title="this.caixaAberto ? 'Fechar caixa' : 'Abrir caixa'"
          >
            <i
              :class="this.caixaAberto ? 'fas fa-lock' : 'fas fa-lock-open'"
              aria-hidden="true"
            ></i>
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

                    <div class="muted" v-if="r.data.sacado">
                      {{ r.data.sacado }}
                    </div>
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
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import { mapActions, mapGetters, mapMutations } from "vuex";
import { api, CrosierBlock, CrosierDropdownEntity, CrosierCalendar } from "crosier-vue";
import moment from "moment";
import axios from "axios";
import printJS from "print-js";

export default {
  name: "list",

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
      expandedRows: null,
      saldos: null, // deve ficar como null até ser preenchido para poder exibir a DataTable corretamente
    };
  },

  async mounted() {
    await this.doFilter();
  },

  methods: {
    ...mapMutations(["setLoading", "setFilters"]),
    ...mapActions(["doFilter"]),

    moment(date) {
      return moment(date);
    },

    doFilterNextTick() {
      this.$nextTick(async () => {
        await this.doFilter();
      });
    },

    abrirFecharCaixa() {
      this.$confirm.require({
        acceptLabel: "Sim",
        rejectLabel: "Não",
        message: "Confirmar a operação?",
        header: "Atenção!",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);

          try {
            const rs = await api.post(
              `/api/fin/caixaOperacao/abrirFechar/${this.filters.carteira.id}`
            );

            if (rs?.status === 200) {
              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                detail: "Caixa blablabla com sucesso!",
                life: 5000,
              });
              await this.loadData();
            } else {
              throw new Error();
            }
          } catch (e) {
            console.error("Erro ao blablabla caixa", e);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: e?.response?.data?.EXCEPTION_MSG ?? "Ocorreu um erro ao efetuar a operação",
              life: 5000,
            });
          }

          this.setLoading(false);
        },
      });
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      filters: "getFilters",
    }),

    caixaAberto() {
      return true;
    },
  },
};
</script>

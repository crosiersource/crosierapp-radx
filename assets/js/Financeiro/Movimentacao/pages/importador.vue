<template>
  <CrosierBlock :loading="this.loading" />
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <Toast position="bottom-right" class="mb-5" />
  <ConfirmDialog />

  <div class="container">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Importação de Movimentações</h3>
          </div>

          <div class="ml-auto"></div>
        </div>
      </div>
      <div class="card-body">
        <div id="campos">
          <div class="form-row">
            <CrosierDropdown
              col="5"
              v-model="this.fields.tipoImportacao"
              :error="this.fieldsErrors.tipoImportacao"
              label="Tipo da Importação"
              :options="[
                { label: 'Extrato Simples', value: 'EXTRATO_SIMPLES' },
                { label: 'Extrato de Cartão', value: 'EXTRATO_CARTAO' },
                { label: 'Lista de Movimentações Agrupadas', value: 'MOVS_AGRUPADAS' },
              ]"
              id="tipoImportacao"
            />

            <CrosierDropdownEntity
              col="5"
              v-model="this.fields.carteira"
              :error="this.fieldsErrors.carteira"
              entity-uri="/api/fin/carteira"
              optionLabel="descricaoMontada"
              :optionValue="null"
              :orderBy="{ codigo: 'ASC' }"
              :filters="{ abertas: true }"
              label="Carteira"
              id="carteira"
            />

            <div class="col-2">
              <label for="btnImportar" class="transparente">...</label>
              <button
                type="button"
                id="btnImportar"
                class="btn btn-sm btn-block btn-primary"
                @click="this.importar"
              >
                <i class="fas fa-file-import"></i> Importar
              </button>
            </div>
          </div>

          <div class="form-row">
            <CrosierInputTextarea
              v-model="this.fields.linhasImportacao"
              id="linhasImportacao"
              label="Conteúdo para Importação"
            />
          </div>
        </div>

        <DataTable
          v-if="this.movimentacoesImportadas"
          stateStorage="local"
          class="p-datatable-sm p-datatable-striped"
          :stateKey="this.dataTableStateKey"
          :value="this.movimentacoesImportadas"
          :totalRecords="totalRecords"
          :paginator="false"
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

          <Column field="id" header="Dt Vencto">
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
        </DataTable>

        <div class="card mt-3" v-if="this.results">
          <div class="card-body">
            <div class="alert alert-success d-flex align-items-center" role="alert">
              <h3><i class="far fa-check-circle"></i> Sucesso!</h3>
            </div>
            <p class="card-text">
              {{ this.results.movs.length }} movimentações importadas para a carteira
              <b>{{ this.fields.carteira.descricaoMontada }}</b
              >.
            </p>

            <pre>{{ this.results.LINHAS_RESULT }}</pre>

            <button type="button" class="btn btn-secondary" @click="this.visualizarMovimentacoes">
              <i class="fas fa-link"></i> Visualizar movimentações
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapMutations } from "vuex";
import {
  CrosierDropdownEntity,
  CrosierDropdown,
  CrosierBlock,
  CrosierInputTextarea,
  submitForm,
} from "crosier-vue";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import moment from "moment";
import * as yup from "yup";

export default {
  components: {
    Column,
    Toast,
    ConfirmDialog,
    CrosierBlock,
    CrosierDropdownEntity,
    CrosierDropdown,
    CrosierInputTextarea,
  },

  data() {
    return {
      movimentacoesImportadas: null,
      movimentacoesSelecionadas: null,
      results: null,
    };
  },

  async mounted() {
    this.setLoading(true);

    this.schemaValidator = yup.object().shape({
      tipoImportacao: yup.string().required(),
      carteira: yup.mixed().required(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    moment(date) {
      return moment(date);
    },

    async importar() {
      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);

          try {
            const rs = await submitForm({
              apiResource: "/api/fin/movimentacao/importar",
              schemaValidator: this.schemaValidator,
              $store: this.$store,
              formDataStateName: "fields",
              $toast: this.$toast,
              setUrlId: false,
              commitFormDataAfterSave: false,
              fnBeforeSave: (formData) => {
                formData.carteira = formData.carteira.id;
              },
            });
            if (!rs?.data?.RESULT === "OK") {
              throw new Error();
            }
            this.results = rs.data.DATA;
          } catch (e) {
            console.error("Eroooooo");
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
      });
    },

    visualizarMovimentacoes() {
      if (this.fields.tipoImportacao === "EXTRATO_SIMPLES") {
        window.location = `/v/fin/movimentacao/extrato?filters={"dtPagto[after]":"${this.results.menorData}T00:00:00-03:00","dtPagto[before]":"${this.results.maiorData}T23:59:59-03:00","carteira":"${this.fields.carteira["@id"]}"}`;
      }
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      filters: "getFilters",
      fields: "getFields",
      fieldsErrors: "getFieldsErrors",
    }),
  },
};
</script>

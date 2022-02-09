<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog></ConfirmDialog>

  <CrosierFormS
    @submitForm="this.submitForm"
    titulo="Parcelamento"
    :disabledSubmit="true"
    formUrl="formParcelamento"
  >
    <template #btns>
      <div class="dropdown ml-2 float-right">
        <button
          class="btn btn-secondary dropdown-toggle"
          type="button"
          id="dropdownMenuButton"
          data-toggle="dropdown"
          aria-expanded="false"
        >
          <i class="fas fa-cog" aria-hidden="true"></i> Opções
        </button>

        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <button
            type="button"
            class="dropdown-item"
            role="button"
            title="Exibe os campos lançamento normal"
            @click="this.setLanctoNormal"
          >
            <i class="fas fa-dollar-sign"></i> Lançamento Normal
          </button>

          <button
            type="button"
            class="dropdown-item"
            role="button"
            title="Exibe os campos para lançamento de cheque de terceiros"
            @click="this.setExibirCamposChequeTerceiros"
          >
            <i class="fas fa-money-check"></i> Lançamento de Cheque de Terceiros
          </button>

          <button
            type="button"
            class="dropdown-item"
            role="button"
            title="Exibe os campos para lançamento de cheque próprio"
            @click="this.setExibirCamposChequeProprio"
          >
            <i class="fas fa-money-check-alt"></i> Lançamento de Cheque de Próprio
          </button>
        </div>
      </div>
    </template>

    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierDropdownEntity
        col="10"
        v-model="this.fields.categoria"
        :error="this.parcelasErrors['[0].categoria']"
        entity-uri="/api/fin/categoria"
        optionLabel="descricaoMontadaTree"
        :optionValue="null"
        :orderBy="{ codigoOrd: 'ASC' }"
        label="Categoria"
        id="categoria"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        v-if="!this.exibirCamposChequeProprio"
        col="6"
        v-model="this.fields.carteira"
        :error="this.parcelasErrors['[0].carteira']"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ abertas: true }"
        label="Carteira"
        id="carteira"
      />

      <CrosierDropdownEntity
        v-if="this.exibirCamposChequeProprio"
        col="4"
        v-model="this.fields.carteira"
        :error="this.parcelasErrors['[0].carteira']"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        :filters="{ cheque: true }"
        label="Carteira"
        id="carteira"
      />

      <CrosierInputText
        v-if="this.exibirCamposChequeProprio"
        label="Núm Cheque"
        col="2"
        id="chequeNumCheque"
        v-model="this.fields.chequeNumCheque"
      />

      <CrosierDropdownEntity
        v-if="!this.exibirCamposChequeProprio && !this.exibirCamposChequeTerceiros"
        col="3"
        v-model="this.fields.modo"
        :error="this.parcelasErrors['[0].modo']"
        entity-uri="/api/fin/modo"
        :optionValue="null"
        optionLabel="descricaoMontada"
        :orderBy="{ codigo: 'ASC' }"
        label="Modo"
        id="modo"
      />

      <CrosierDropdownEntity
        v-if="this.exibirCamposChequeProprio"
        :filters="{ codigo: 3 }"
        col="3"
        v-model="this.fields.modo"
        :error="this.parcelasErrors['[0].modo']"
        entity-uri="/api/fin/modo"
        :optionValue="null"
        optionLabel="descricaoMontada"
        label="Modo"
        id="modo"
      />

      <CrosierDropdownEntity
        v-if="this.exibirCamposChequeTerceiros"
        :filters="{ codigo: 4 }"
        col="3"
        v-model="this.fields.modo"
        :error="this.parcelasErrors['[0].modo']"
        entity-uri="/api/fin/modo"
        :optionValue="null"
        optionLabel="descricaoMontada"
        label="Modo"
        id="modo"
      />

      <CrosierDropdownEntity
        col="3"
        v-model="this.fields.centroCusto"
        entity-uri="/api/fin/centroCusto"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Centro de Custo"
        id="centroCusto"
      />
    </div>

    <div class="form-row" v-if="!this.fields.categoria">
      <div class="col-md-6">
        <div class="form-group">
          <label>Sacado</label>
          <div class="input-group">
            <Skeleton class="form-control" height="2rem" />
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Cedente</label>
          <div class="input-group">
            <Skeleton class="form-control" height="2rem" />
          </div>
        </div>
      </div>
    </div>

    <div class="form-row" v-if="this.fields.categoria && this.fields.categoria.codigoSuper === 1">
      <!-- Em um RECEBIMENTO, o sacado é um terceiro paganado para uma das filiais (cedente) -->
      <CrosierDropdown
        col="6"
        v-model="this.fields.cedente"
        :options="this.filiais"
        :optionValue="id"
        :orderBy="{ codigo: 'ASC' }"
        label="Cedente"
        id="dd_cedente"
        helpText="Quem recebe o valor"
      />

      <CrosierAutoComplete
        label="Sacado"
        id="ac_sacado"
        col="6"
        v-model="this.fields.sacado"
        :values="this.sacadosOuCedentes"
        @complete="this.pesquisarSacadoOuCedente"
        field="id"
        helpText="Quem paga o valor"
      >
        <template #item="r"> {{ r.item.text }}</template>
      </CrosierAutoComplete>
    </div>

    <div class="form-row" v-if="this.fields.categoria && this.fields.categoria.codigoSuper === 2">
      <!-- Em um PAGAMENTO, o sacado é uma das filiais pagando para um terceiro (cedente) -->
      <CrosierAutoComplete
        col="6"
        label="Cedente"
        id="ac_cedente"
        v-model="this.fields.cedente"
        :values="this.sacadosOuCedentes"
        @complete="this.pesquisarSacadoOuCedente"
        field="text"
        helpText="Quem recebe o valor"
      >
        <template #item="r"> {{ r.item.text }}</template>
      </CrosierAutoComplete>

      <CrosierDropdown
        col="6"
        v-model="this.fields.sacado"
        :options="this.filiais"
        :optionValue="id"
        :orderBy="{ codigo: 'ASC' }"
        label="Sacado"
        id="sacado"
        helpText="Quem paga o valor"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="8"
        v-model="this.fields.documentoBanco"
        entity-uri="/api/fin/banco"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Banco (Documento)"
        id="documentoBanco"
      />

      <CrosierInputText
        label="Número (Documento)"
        col="4"
        id="documentoNum"
        v-model="this.fields.documentoNum"
      />
    </div>

    <div class="card mt-3 mb-3" v-if="this.exibirCamposChequeTerceiros">
      <div class="card-body">
        <h5 class="card-title">Cheque</h5>

        <div class="form-row">
          <CrosierDropdownEntity
            col="4"
            v-model="this.fields.chequeBanco"
            entity-uri="/api/fin/banco"
            optionLabel="descricaoMontada"
            :optionValue="null"
            :orderBy="{ codigo: 'ASC' }"
            label="Banco"
            id="chequeBanco"
          />

          <CrosierInputText
            label="Agência"
            col="2"
            id="chequeAgencia"
            v-model="this.fields.chequeAgencia"
          />

          <CrosierInputText
            label="Conta"
            col="3"
            id="chequeConta"
            v-model="this.fields.chequeConta"
          />

          <CrosierInputText
            label="Número"
            col="3"
            id="chequeNumCheque"
            v-model="this.fields.chequeNumCheque"
          />
        </div>
      </div>
    </div>

    <div class="form-row">
      <CrosierInputInt
        col="2"
        label="Qtde Parcelas"
        id="cadeiaQtde"
        v-model="this.fields.cadeiaQtde"
        :error="this.parcelasErrors['[0].cadeiaQtde']"
      />

      <CrosierInputText
        col="10"
        label="Descrição"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.parcelasErrors['[0].descricao']"
      />
    </div>

    <div class="form-row">
      <CrosierCalendar
        label="Dt Moviment"
        col="3"
        id="dtMoviment"
        v-model="this.fields.dtMoviment"
        :error="this.parcelasErrors['[0].dtMoviment']"
        @focus="this.onDtMovimentFocus"
      />
      <CrosierCalendar
        label="Vencto (1ª)"
        col="3"
        id="dtVencto"
        v-model="this.fields.dtVencto"
        :error="this.parcelasErrors['[0].dtVencto']"
      />

      <CrosierCurrency label="Valor (Total)" col="3" id="valorTotal" v-model="this.valorTotal" />

      <CrosierCurrency label="Valor (Parcela)" col="3" id="valor" v-model="this.valorParcela" />
    </div>

    <div class="form-row mt-2">
      <CrosierInputTextarea label="Obs" id="obs" v-model="this.fields.obs" />
    </div>

    <div class="row mt-3">
      <div class="col text-right">
        <button
          v-if="this.fields.cadeiaQtde > 1"
          class="btn btn-sm btn-warning"
          style="width: 12rem"
          type="button"
          @click="this.gerarParcelas"
        >
          <i class="fas fa-save"></i> Gerar Parcelas
        </button>
      </div>
    </div>

    <div class="row col-12 mt-3 mb-3" v-if="this.parcelas.length > 1">
      <DataTable :value="this.parcelas" responsiveLayout="scroll">
        <Column field="cadeiaOrdem" header="#"></Column>
        <Column
          field="documentoNum"
          :header="
            this.exibirCamposChequeTerceiros || this.exibirCamposChequeProprio
              ? 'Núm Cheque'
              : 'Núm Doc'
          "
        >
          <template #body="r">
            <CrosierInputText
              v-if="!(this.exibirCamposChequeTerceiros || this.exibirCamposChequeProprio)"
              :showLabel="false"
              :id="'documentoNum_' + r.data.cadeiaOrdem"
              v-model="r.data.documentoNum"
            />

            <CrosierInputText
              v-if="this.exibirCamposChequeTerceiros || this.exibirCamposChequeProprio"
              :showLabel="false"
              :id="'chequeNumCheque_' + r.data.cadeiaOrdem"
              v-model="r.data.chequeNumCheque"
            />
          </template>
        </Column>
        <Column field="dtVencto" header="Dt Vencto">
          <template #body="r">
            <CrosierCalendar
              :showLabel="false"
              :id="'dtVencto_' + r.data.cadeiaOrdem"
              v-model="r.data.dtVencto"
            />
          </template>
        </Column>
        <Column field="dtVenctoEfetiva" header="Dt Vencto Efet">
          <template #body="r">
            <CrosierCalendar
              @focus="this.onFocusDtVenctoEfet(r.data.cadeiaOrdem)"
              :showLabel="false"
              :id="'dtVenctoEfetiva_' + r.data.cadeiaOrdem"
              v-model="r.data.dtVenctoEfetiva"
            />
          </template>
        </Column>

        <Column field="valor" header="Valor">
          <template #body="r">
            <CrosierCurrency
              :showLabel="false"
              :id="'valor_' + r.data.cadeiaOrdem"
              v-model="r.data.valor"
              @input="this.calcTotalParcelas"
            />
          </template>
        </Column>

        <template #footer>
          <div class="h6 text-right mr-4">
            Total:
            <strong>
              {{
                parseFloat(this.totalParcelas).toLocaleString("pt-BR", {
                  style: "currency",
                  currency: "BRL",
                })
              }}
            </strong>
          </div>
        </template>
      </DataTable>
    </div>

    <div class="row mt-3" v-if="this.parcelas.length > 1">
      <div class="col text-right">
        <button
          class="btn btn-sm btn-primary"
          style="width: 12rem"
          type="button"
          @click="this.submitForm"
        >
          <i class="fas fa-save"></i> Salvar
        </button>
      </div>
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import Skeleton from "primevue/skeleton";
import ConfirmDialog from "primevue/confirmdialog";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import * as yup from "yup";
import {
  CrosierCurrency,
  CrosierDropdown,
  CrosierDropdownEntity,
  CrosierAutoComplete,
  CrosierFormS,
  CrosierInputInt,
  CrosierInputText,
  CrosierInputTextarea,
  CrosierCalendar,
  submitForm,
  validateFormData,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import axios from "axios";
import moment from "moment";
import { cacheAdapterEnhancer } from "axios-extensions";

export default {
  components: {
    CrosierDropdownEntity,
    CrosierCurrency,
    CrosierCalendar,
    Toast,
    CrosierFormS,
    CrosierDropdown,
    CrosierInputText,
    CrosierInputInt,
    CrosierAutoComplete,
    CrosierInputTextarea,
    Skeleton,
    ConfirmDialog,
    DataTable,
    Column,
  },

  data() {
    return {
      schemaValidator: {},
      sacadosOuCedentes: null,
      filiais: null,
      dtVencto_cache: null,
      exibirDtPagto: false,
      valorParcela: null,
      valorTotal: null,
      api: null,
      totalParcelas: null,
      exibirCamposChequeTerceiros: false,
      exibirCamposChequeProprio: false,
      carteiraChequeProprio: null,
    };
  },

  async mounted() {
    this.setLoading(true);

    this.schemaValidator = yup
      .array()
      .ensure()
      .of(
        yup.object().shape({
          categoria: yup.mixed().required().typeError(),
          carteira: yup.mixed().required().typeError(),
          modo: yup.mixed().required().typeError(),
          descricao: yup.mixed().required().typeError(),
          dtMoviment: yup.date().required().typeError(),
          dtVencto: yup.date().required().typeError(),
          valor: yup.number().required().typeError(),
          cadeiaQtde: yup.number().min(1).max(720).required().typeError(),
        })
      );

    const rs = await axios.get("/api/fin/movimentacao/filiais/", {
      headers: {
        "Content-Type": "application/ld+json",
      },
      validateStatus(status) {
        return status < 500;
      },
    });
    if (rs?.data?.RESULT === "OK") {
      this.filiais = rs.data.DATA;
    } else {
      console.error(rs?.data?.MSG);
      this.$toast.add({
        severity: "error",
        summary: "Erro",
        detail: rs?.data?.MSG,
        life: 5000,
      });
    }

    const rPagamento = new URLSearchParams(window.location.search.substring(1)).get("rPagamento");
    if (rPagamento) {
      this.exibirDtPagto = true;
    }

    this.api = axios.create({
      baseURL: "/",
      headers: { "Cache-Control": "no-cache" },
      // cache will be enabled by default
      adapter: cacheAdapterEnhancer(axios.defaults.adapter),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setparcelasErrors"]),

    moment(date) {
      return moment(date);
    },

    async pesquisarSacadoOuCedente(event) {
      try {
        const response = await axios.get(
          `/api/fin/movimentacao/findSacadoOuCedente/?term=${event.query}`
        );

        if (response.status === 200) {
          this.sacadosOuCedentes = response.data.DATA;
        }
      } catch (err) {
        console.error(err);
      }
    },

    async submitForm() {
      if (
        !validateFormData({
          $store: this.$store,
          formDataStateName: "parcelas",
          schemaValidator: this.schemaValidator,
          $toast: this.$toast,
        })
      ) {
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

          const rs = await submitForm({
            setUrlId: false,
            commitFormDataAfterSave: false,
            apiResource: "/api/fin/movimentacao",
            $store: this.$store,
            formDataStateName: "fields",
            $toast: this.$toast,
            fnBeforeSave: (formData) => {
              formData.categoria = formData.categoria["@id"];
              formData.carteira = formData.carteira["@id"];
              formData.modo = formData.modo["@id"];
              formData.centroCusto =
                formData.centroCusto && formData.centroCusto["@id"]
                  ? formData.centroCusto["@id"]
                  : null;
              formData.documentoBanco =
                formData.documentoBanco && formData.documentoBanco["@id"]
                  ? formData.documentoBanco["@id"]
                  : null;

              formData.chequeBanco =
                formData.chequeBanco && formData.chequeBanco["@id"]
                  ? formData.chequeBanco["@id"]
                  : null;

              formData.centroCusto =
                formData.centroCusto && formData.centroCusto["@id"]
                  ? formData.centroCusto["@id"]
                  : null;

              if (formData.cedente && formData.cedente.text) {
                formData.cedente = formData.cedente.text;
              }

              if (formData.sacado && formData.sacado.text) {
                formData.sacado = formData.sacado.text;
              }

              const dadosParcelamento = this.parcelas.map((e) => {
                return {
                  dtVencto: e.dtVencto,
                  dtVenctoEfetiva: e.dtVenctoEfetiva,
                  valor: e.valor,
                  documentoNum: e.documentoNum,
                  chequeNumCheque: e.chequeNumCheque,
                };
              });

              formData.jsonData = {
                dadosParcelamento,
              };

              delete formData.tipoLancto;
            },
          });

          if (rs?.status === 201) {
            window.location = `/v/fin/cadeia/exibirMovimentacoes?id=${rs.data.cadeia.id}`;
          } else {
            console.error(rs);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: "Ocorreu um erro ao salvar",
              life: 5000,
            });

            this.setLoading(false);
          }
        },
      });
    },

    onDtMovimentFocus() {
      if (!this.fields.dtMoviment) {
        this.fields.dtMoviment = new Date();
      }
    },

    gerarParcelas() {
      this.parcelas.splice(1);
      const valorParcela = this.valorParcela ?? this.valorTotal / this.fields.cadeiaQtde;
      this.fields.valor = valorParcela;
      this.fields.cadeiaOrdem = 1;
      this.fields.descricao = this.fields.descricao ? this.fields.descricao.toUpperCase() : null;
      this.onFocusDtVenctoEfet(1);
      let totalSomado = this.fields.valor;
      for (let i = 2; i <= this.fields.cadeiaQtde; i++) {
        const parcela = { ...this.fields };
        if (this.exibirCamposChequeTerceiros || this.exibirCamposChequeProprio) {
          parcela.chequeNumCheque = Number(parcela.chequeNumCheque) + i - 1;
        }
        parcela.cadeiaOrdem = i;
        parcela.dtVencto = moment(this.fields.dtVencto)
          .add(i - 1, "month")
          .toDate();
        parcela.valor = Number(valorParcela.toFixed(2));
        parcela.valorTotal = Number(valorParcela.toFixed(2));
        totalSomado += parcela.valor;
        this.parcelas.push(parcela);
        this.onFocusDtVenctoEfet(i);
      }
      if (this.valorTotal && totalSomado !== this.valorTotal) {
        this.parcelas[0].valor =
          Number(this.parcelas[0].valor) + Number((this.valorTotal - totalSomado).toFixed(2));
        this.parcelas[0].valorTotal = this.parcelas[0].valor;
      }
      this.calcTotalParcelas();
    },

    calcTotalParcelas() {
      this.totalParcelas = this.parcelas.reduce((sum, e) => sum + e.valor, 0);
    },

    async onFocusDtVenctoEfet(numParcela) {
      const i = numParcela - 1;
      if (this.parcelas[i].dtVencto) {
        const route = `/base/diaUtil/findDiaUtil/?financeiro=true&dt=${moment(
          this.parcelas[i].dtVencto
        ).format("YYYY-MM-DD")}`;

        const rs = await this.api.get(route);

        if (rs?.data?.diaUtil) {
          this.parcelas[i].dtVenctoEfetiva = new Date(moment(rs.data.diaUtil));
        }
      }
    },

    setExibirCamposChequeTerceiros() {
      this.exibirCamposChequeTerceiros = true;
      this.exibirCamposChequeProprio = false;
    },

    setExibirCamposChequeProprio() {
      this.exibirCamposChequeTerceiros = false;
      this.exibirCamposChequeProprio = true;
    },

    setLanctoNormal() {
      this.exibirCamposChequeTerceiros = false;
      this.exibirCamposChequeProprio = false;
    },
  },

  computed: {
    ...mapGetters({
      parcelas: "getParcelas",
      parcelasErrors: "getParcelasErrors",
      fields: "getFields",
      fieldsErrors: "getFieldsErrors",
    }),

    habilitarGerarParcelas() {
      return (
        this.fields.cadeiaQtde && this.fields.dtVencto && (this.valorParcela || this.valorTotal)
      );
    },
  },
};
</script>
<style scoped>
.camposEmpilhados {
  margin-top: -15px;
}
</style>

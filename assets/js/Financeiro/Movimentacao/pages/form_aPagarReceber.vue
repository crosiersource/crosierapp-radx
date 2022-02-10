<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog></ConfirmDialog>

  <CrosierFormS @submitForm="this.submitForm" :titulo="this.titulo" :subtitulo="this.subtitulo">
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
          <a
            v-if="this.fields.id"
            class="dropdown-item"
            :href="'/v/fin/movimentacao/recorrente/form?id=' + this.fields.id"
            title="Transformar esta movimentação em recorrente"
          >
            <i class="fas fa-undo" aria-hidden="true"></i> Transformar em Recorrente
          </a>

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

          <button
            v-if="this.fields.id"
            type="button"
            class="dropdown-item"
            @click="this.clonar"
            title="Clonar esta movimentação"
          >
            <i class="far fa-clone"></i> Clonar
          </button>

          <button
            v-if="this.fields.id"
            type="button"
            class="dropdown-item"
            role="button"
            title="Registrar pagamento desta movimentação"
            @click="this.setarParaPagto"
          >
            <i class="fas fa-dollar-sign"></i> Registrar pagamento
          </button>

          <button
            v-if="this.fields.id"
            type="button"
            class="dropdown-item"
            @click="this.deletar"
            title="Deletar movimentação"
          >
            <i class="fa fa-trash" aria-hidden="true"></i> Deletar
          </button>

          <button
            type="button"
            class="dropdown-item"
            @click="this.imprimirFicha"
            title="Imprimir ficha de movimentação"
            id="btnImprimirFicha"
            name="btnImprimirFicha"
          >
            <i class="far fa-file-alt"></i> Imprimir ficha
          </button>
        </div>
      </div>
    </template>

    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" :disabled="true" />

      <CrosierDropdownEntity
        col="10"
        v-model="this.fields.categoria"
        :error="this.fieldsErrors.categoria"
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
        :error="this.fieldsErrors.carteira"
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
        :error="this.fieldsErrors.carteira"
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
        :error="this.fieldsErrors.modo"
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
        :error="this.fieldsErrors.modo"
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
        :error="this.fieldsErrors.modo"
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
      <CrosierInputText
        label="Descrição"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.fieldsErrors.descricao"
      />
    </div>

    <div class="form-row">
      <div class="col-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Datas</h5>

            <div class="form-row form-group">
              <label class="col-form-label col-sm-3" for="dtMoviment">Dt Moviment</label>
              <CrosierCalendar
                :showLabel="false"
                col="9"
                id="dtMoviment"
                v-model="this.fields.dtMoviment"
                :error="this.fieldsErrors.dtMoviment"
                @focus="this.onDtMovimentFocus"
              />
            </div>

            <div class="form-row form-group camposEmpilhados">
              <label class="col-form-label col-sm-3" for="dtVencto">Dt Vencto</label>
              <CrosierCalendar
                :showLabel="false"
                col="9"
                id="dtVencto"
                v-model="this.fields.dtVencto"
                :error="this.fieldsErrors.dtVencto"
              />
            </div>

            <div class="form-row form-group camposEmpilhados">
              <label class="col-form-label col-sm-3" for="dtVenctoEfetiva">Dt Vencto Efet</label>
              <CrosierCalendar
                :showLabel="false"
                @focus="this.onFocusDtVenctoEfet"
                col="9"
                id="dtVenctoEfetiva"
                v-model="this.fields.dtVenctoEfetiva"
                :error="this.fieldsErrors.dtVenctoEfetiva"
              />
            </div>

            <div class="form-row form-group camposEmpilhados" v-if="this.exibirDtPagto">
              <label class="col-form-label col-sm-3" for="dtPagto">Dt Pagto</label>
              <CrosierCalendar
                :showLabel="false"
                @focus="this.onFocusDtPagto"
                col="9"
                id="dtPagto"
                v-model="this.fields.dtPagto"
                :error="this.fieldsErrors.dtPagto"
                helpText="Movimentação com Dt Pagto é considerada como 'REALIZADA'"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="col-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Valores</h5>

            <div class="form-row form-group">
              <label class="col-form-label col-sm-3" for="valor">Valor</label>
              <CrosierCurrency
                :showLabel="false"
                col="9"
                id="valor"
                label="Valor"
                v-model="this.fields.valor"
                :error="this.fieldsErrors.valor"
                @blur="this.calcPeloValor"
              />
            </div>

            <div class="form-row form-group camposEmpilhados">
              <label class="col-form-label col-sm-3" for="dtVencto">Descontos</label>
              <CrosierCurrency
                :showLabel="false"
                col="9"
                id="valor"
                label="Descontos"
                v-model="this.fields.descontos"
                @blur="this.calcPeloValor"
              />
            </div>

            <div class="form-row form-group camposEmpilhados">
              <label class="col-form-label col-sm-3" for="acrescimos">Acréscimos</label>
              <CrosierCurrency
                :showLabel="false"
                col="9"
                id="valor"
                label="Acréscimos"
                v-model="this.fields.acrescimos"
                @blur="this.calcPeloValor"
              />
            </div>

            <div class="form-row form-group camposEmpilhados">
              <label class="col-form-label col-sm-3" for="valorTotal">Valor Total</label>
              <CrosierCurrency
                :showLabel="false"
                col="9"
                id="valor"
                label="Valor Total"
                v-model="this.fields.valorTotal"
                :error="this.fieldsErrors.valorTotal"
                @blur="this.calcPeloValorTotal"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-row mt-2">
      <CrosierInputTextarea label="Obs" id="obs" v-model="this.fields.obs" />
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import Skeleton from "primevue/skeleton";
import ConfirmDialog from "primevue/confirmdialog";
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
  SetFocus,
  api,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import axios from "axios";
import moment from "moment";
import printJS from "print-js";

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
  },

  data() {
    return {
      schemaValidator: {},
      sacadosOuCedentes: null,
      filiais: null,
      dtVencto_cache: null,
      exibirDtPagto: false,
      exibirCamposChequeTerceiros: false,
      exibirCamposChequeProprio: false,
      carteiraChequeProprio: null,
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.$store.dispatch("loadData");
    this.schemaValidator = yup.object().shape({
      categoria: yup.mixed().required().typeError(),
      carteira: yup.mixed().required().typeError(),
      modo: yup.mixed().required().typeError(),
      descricao: yup.mixed().required().typeError(),
      dtMoviment: yup.date().required().typeError(),
      dtVencto: yup.date().required().typeError(),
      dtVenctoEfetiva: yup.date().required().typeError(),
      valor: yup.number().required().typeError(),
      valorTotal: yup.number().required().typeError(),
    });

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
    if (rPagamento || this.fields.dtPagto) {
      this.setarParaPagto();
    }

    if (this.fields?.modo?.codigo === 3) {
      this.setExibirCamposChequeProprio();
    } else if (this.fields?.modo?.codigo === 4) {
      this.setExibirCamposChequeTerceiros();
    }

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

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
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/movimentacao",
        schemaValidator: this.schemaValidator,
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

          formData.centroCusto =
            formData.centroCusto && formData.centroCusto["@id"]
              ? formData.centroCusto["@id"]
              : null;

          formData.chequeBanco =
            formData.chequeBanco && formData.chequeBanco["@id"]
              ? formData.chequeBanco["@id"]
              : null;

          if (formData.cedente && formData.cedente.text) {
            formData.cedente = formData.cedente.text;
          }

          if (formData.sacado && formData.sacado.text) {
            formData.sacado = formData.sacado.text;
          }

          delete formData.tipoLancto;
          delete formData.cadeia;
        },
      });
      this.setLoading(false);
    },

    calcPeloValor() {
      this.$nextTick(() => {
        this.fields.valorTotal =
          this.fields.valor - (this.fields.descontos || 0) + (this.fields.acrescimos || 0);
      });
    },

    calcPeloValorTotal() {
      this.$nextTick(() => {
        this.fields.descontos = 0;
        this.fields.acrescimos = 0;
        if (this.fields.valorTotal > this.fields.valor) {
          this.fields.acrescimos = this.fields.valorTotal - this.fields.valor;
        } else if (this.fields.valorTotal < this.fields.valor) {
          this.fields.descontos = this.fields.valor - this.fields.valorTotal;
        } else {
          this.fields.valor = this.fields.valorTotal;
        }
      });
    },

    async onFocusDtVenctoEfet() {
      if (this.fields.dtVencto) {
        if (this.fields.dtVencto === this.dtVencto_cache) return;
        this.dtVencto_cache = this.fields.dtVencto;
        const route = `/base/diaUtil/findDiaUtil/?financeiro=true&dt=${moment(
          this.fields.dtVencto
        ).format("YYYY-MM-DD")}`;
        const rs = await axios.get(route, {
          cache: {
            maxAge: 2 * 60 * 1000,
          },
        });
        if (rs?.data?.diaUtil) {
          this.fields.dtVenctoEfetiva = new Date(moment(rs.data.diaUtil));
        }
      }
    },

    onDtMovimentFocus() {
      if (!this.fields.dtMoviment) {
        this.fields.dtMoviment = new Date();
      }
    },

    setarParaPagto() {
      this.exibirDtPagto = true;
      SetFocus("dtPagto", 80);
    },

    onFocusDtPagto() {
      if (!this.fields.dtPagto) {
        this.fields.dtPagto = new Date();
      }
    },

    clonar() {
      this.$confirm.require({
        acceptLabel: "Sim",
        rejectLabel: "Não",
        message: "Confirmar a operação?",
        header: "Atenção!",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          window.location = `/fin/movimentacao/clonar/${this.fields.id}`;
        },
      });
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

    deletar() {
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
            const deleteUrl = `${this.apiResource}/${this.fields.id}`;
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

    async imprimirFicha() {
      this.setLoading(true);
      const pdf = await axios.post("/fin/movimentacao/aPagarReceber/fichaMovimentacao", {
        movsSelecionadas: JSON.stringify([this.fields]),
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
    ...mapGetters({ fields: "getFields", fieldsErrors: "getFieldsErrors" }),

    titulo() {
      if (!this.fields?.categoria) {
        return "Conta a Pagar/Receber";
      }
      if (this.fields?.categoria?.codigoSuper === 1) {
        return "Conta a Receber";
      }
      return "Conta a Pagar";
    },

    subtitulo() {
      if (this.exibirCamposChequeProprio) {
        return "Lançamento de Cheque Próprio";
      }
      if (this.exibirCamposChequeTerceiros) {
        return "Lançamento de Cheque de Terceiros";
      }
      if (this.exibirDtPagto) {
        return "Registro de Pagamento";
      }
      return "Lançamento de Movimentação";
    },
  },
};
</script>
<style scoped>
.camposEmpilhados {
  margin-top: -15px;
}
</style>

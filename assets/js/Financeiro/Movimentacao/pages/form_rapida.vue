<template>
  <Toast position="bottom-right" class="mb-5" />
  <ConfirmDialog />

  <CrosierFormS
    @submitForm="this.submitForm"
    titulo="Movimentação"
    subtitulo="Lançamento Rápido"
    formUrl="/v/fin/movimentacao/rapida"
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
          <a
            v-if="this.fields.id"
            class="dropdown-item"
            :href="'/v/fin/movimentacao/recorrente/form?id=' + this.fields.id"
            title="Transformar esta movimentação em recorrente"
          >
            <i class="fas fa-undo" aria-hidden="true"></i> Transformar em Recorrente
          </a>

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
            @click="this.deletar"
            title="Deletar movimentação"
          >
            <i class="fa fa-trash" aria-hidden="true"></i> Deletar
          </button>
        </div>
      </div>
    </template>

    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.fields.id" disabled />

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
        :filters="{ atual: true, concreta: true, caixa: false }"
        label="Carteira"
        id="carteira"
      />

      <CrosierDropdownEntity
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

    <SacadoCedente />

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

    <div class="form-row">
      <CrosierInputText
        col="9"
        label="Descrição"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.fieldsErrors.descricao"
      />
      <CrosierCalendar
        label="Dt Moviment"
        col="3"
        id="dtMoviment"
        v-model="this.fields.dtPagto"
        :error="this.fieldsErrors.dtPagto"
      />
    </div>

    <div class="form-row">
      <div class="col-6"></div>

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
import ConfirmDialog from "primevue/confirmdialog";
import * as yup from "yup";
import {
  CrosierCurrency,
  CrosierDropdownEntity,
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
import SacadoCedente from "./sacadoCedente";

export default {
  components: {
    CrosierDropdownEntity,
    CrosierCurrency,
    CrosierCalendar,
    Toast,
    CrosierFormS,
    CrosierInputText,
    CrosierInputInt,
    CrosierInputTextarea,
    ConfirmDialog,
    SacadoCedente,
  },

  data() {
    return {
      schemaValidator: {},
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
      dtPagto: yup.date().required().typeError(),
      valor: yup.number().required().typeError(),
      valorTotal: yup.number().required().typeError(),
    });

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

          formData.chequeBanco =
            formData.chequeBanco && formData.chequeBanco["@id"]
              ? formData.chequeBanco["@id"]
              : null;

          delete formData.tipoLancto;
          delete formData.cadeia;
          delete formData.fatura;
        },
      });
      localStorage.setItem(
        "dadosUltimaMovimentacao",
        JSON.stringify({
          categoria: this.fields?.categoria,
          carteira: this.fields?.carteira,
          modo: this.fields?.modo,
          centroCusto: this.fields?.centroCusto,
          grupo: this.fields?.grupo,
          descricao: this.fields?.descricao,
        })
      );

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
          this.fields.acrescimos = Math.abs(this.fields.valorTotal - this.fields.valor);
        } else if (this.fields.valorTotal < this.fields.valor) {
          this.fields.descontos = Math.abs(this.fields.valor - this.fields.valorTotal);
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

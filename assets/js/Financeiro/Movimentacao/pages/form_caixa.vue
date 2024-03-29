<template>
  <Toast position="bottom-right" class="mb-5" />
  <ConfirmDialog></ConfirmDialog>

  <CrosierFormS
    @submitForm="this.submitForm"
    formUrl="caixa"
    :listUrl="null"
    titulo="Movimentação em Caixa"
  >
    <template #divCima>
      <div>
        <CrosierDropdownEntity
          v-model="this.fields.carteira"
          entity-uri="/api/fin/carteira"
          optionLabel="descricaoMontada"
          :orderBy="{ codigo: 'ASC' }"
          :filters="{ caixa: true, atual: true }"
          :noLabel="true"
          :selectFirst="true"
          id="carteira"
        />
      </div>
      <div>
        <CrosierCalendar
          :showLabel="false"
          id="dtMoviment"
          v-model="this.fields.dtMoviment"
          :error="this.fieldsErrors.dtMoviment"
        />
      </div>
    </template>
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
            title="Exibe os campos para lançamento de cheque de terceiros"
            @click="this.setExibirCamposChequeTerceiros"
          >
            <i class="fas fa-money-check"></i> Lançamento de Cheque de Terceiros
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
        col="7"
        v-model="this.fields.categoria"
        :error="this.fieldsErrors.categoria"
        entity-uri="/api/fin/categoria"
        optionLabel="descricaoMontadaTree"
        :optionValue="null"
        :orderBy="{ codigoOrd: 'ASC' }"
        label="Categoria"
        id="categoria"
      />

      <CrosierCurrency
        col="3"
        id="valor"
        label="Valor"
        v-model="this.fields.valor"
        :error="this.fieldsErrors.valor"
      />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="3"
        v-model="this.fields.modo"
        :error="this.fieldsErrors.modo"
        entity-uri="/api/fin/modo"
        :optionValue="null"
        optionLabel="descricaoMontada"
        :filters="[
          { 'codigo[]': 1 }, // EM ESPÉCIE
          { 'codigo[]': 4 }, // CHEQUE TERCEIROS
          { 'codigo[]': 5 }, // DEPÓSITO BANCÁRIO
          { 'codigo[]': 7 }, // TRANSF. OU PIX
          { 'codigo[]': 9 }, // CARTÃO DE CRÉDITO
          { 'codigo[]': 10 }, // CARTÃO DE DÉBITO
          { 'codigo[]': 11 }, // TRANSF. ENTRE CONTAS
        ]"
        :orderBy="{ codigo: 'ASC' }"
        label="Modo"
        id="modo"
        @update:modelValue="this.onChangeModo"
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

      <CrosierInputText
        col="6"
        label="Descrição"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.fieldsErrors.descricao"
      />
    </div>

    <SacadoCedente />

    <div class="card mt-3 mb-3" v-if="this.fields?.modo?.codigo === 4">
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

    <div class="card mt-3 mb-3" v-show="[9, 10].includes(this.fields?.modo?.codigo)">
      <div class="card-body">
        <h5 class="card-title">Dados Cartão</h5>

        <div class="form-row">
          <CrosierDropdownEntity
            :col="this.fields?.modo?.codigo === 9 ? 3 : 5"
            v-model="this.fields.operadoraCartao"
            entity-uri="/api/fin/operadoraCartao"
            optionLabel="descricao"
            :optionValue="null"
            :orderBy="{ descricao: 'ASC' }"
            label="Operadora"
            id="operadoraCartao"
          />

          <CrosierDropdownEntity
            ref="bandeiraCartao"
            :col="this.fields?.modo?.codigo === 9 ? 3 : 4"
            v-model="this.fields.bandeiraCartao"
            entity-uri="/api/fin/bandeiraCartao"
            optionLabel="descricao"
            :optionValue="null"
            :filters="{ modo: this.fields?.modo ? this.fields?.modo['@id'] : null }"
            :orderBy="{ descricao: 'ASC' }"
            label="Bandeira"
            id="bandeiraCartao"
          />

          <CrosierInputText
            label="Últ 4 Dígitos"
            col="3"
            id="numCartao"
            v-model="this.fields.numCartao"
          />

          <CrosierInputInt
            v-if="this.fields?.modo?.codigo === 9"
            label="Parcelas"
            col="3"
            id="qtdeParcelas"
            v-model="this.fields.qtdeParcelas"
          />
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
  api,
  CrosierCalendar,
  CrosierCurrency,
  CrosierDropdownEntity,
  CrosierFormS,
  CrosierInputInt,
  CrosierInputText,
  CrosierInputTextarea,
  submitForm,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import moment from "moment";
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
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.$store.dispatch("loadData");

    if (!this.fields.dtMoviment) {
      this.fields.dtMoviment = new Date();
    }

    this.schemaValidator = yup.object().shape({
      categoria: yup.mixed().required().typeError(),
      carteira: yup.mixed().required().typeError(),
      modo: yup.mixed().required().typeError(),
      descricao: yup.mixed().required().typeError(),
      dtMoviment: yup.date().required().typeError(),
      valor: yup.number().required().typeError(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    moment(date) {
      return moment(date);
    },

    onChangeModo() {
      this.$nextTick(async () => {
        await this.$refs.bandeiraCartao.load();
      });
    },

    async submitForm() {
      this.setLoading(true);
      try {
        await submitForm({
          apiResource: "/api/fin/movimentacao",
          schemaValidator: this.schemaValidator,
          $store: this.$store,
          formDataStateName: "fields",
          $toast: this.$toast,
          fnBeforeSave: (formData) => {
            formData.categoria = formData.categoria["@id"];
            formData.modo = formData.modo["@id"];

            formData.centroCusto =
              formData.centroCusto && formData.centroCusto["@id"]
                ? formData.centroCusto["@id"]
                : null;

            formData.operadoraCartao =
              formData.operadoraCartao && formData.operadoraCartao["@id"]
                ? formData.operadoraCartao["@id"]
                : null;

            if (formData.operadoraCartao) {
              formData.tipoLancto = "/api/fin/tipoLancto/63";
            }

            formData.bandeiraCartao =
              formData.bandeiraCartao && formData.bandeiraCartao["@id"]
                ? formData.bandeiraCartao["@id"]
                : null;

            formData.documentoBanco =
              formData.documentoBanco && formData.documentoBanco["@id"]
                ? formData.documentoBanco["@id"]
                : null;

            formData.chequeBanco =
              formData.chequeBanco && formData.chequeBanco["@id"]
                ? formData.chequeBanco["@id"]
                : null;

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
          })
        );
      } catch (e) {
        console.error(e);
      }
      this.setLoading(false);
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
  },

  computed: {
    ...mapGetters({ fields: "getFields", fieldsErrors: "getFieldsErrors" }),
  },
};
</script>
<style scoped>
.camposEmpilhados {
  margin-top: -15px;
}
</style>

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
                { label: 'Extrato de Grupo', value: 'EXTRATO_GRUPO' },
              ]"
              id="tipoImportacao"
            />

            <CrosierDropdownEntity
              v-if="this.fields.tipoImportacao === 'EXTRATO_SIMPLES'"
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

            <CrosierDropdownEntity
              v-if="this.fields.tipoImportacao === 'EXTRATO_CARTAO'"
              col="5"
              v-model="this.fields.operadoraCartao"
              :error="this.fieldsErrors.operadoraCartao"
              entity-uri="/api/fin/operadoraCartao"
              optionLabel="descricao"
              :optionValue="null"
              :orderBy="{ descricao: 'ASC' }"
              :filters="{ ativa: true }"
              label="Operadora"
              id="operadoraCartao"
            />

            <div class="col-md-5" v-else>
              <div class="form-group">
                <label class="transparente">.</label>
                <Skeleton class="form-control" height="2rem" />
              </div>
            </div>

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

          <div class="form-row" v-if="this.fields.tipoImportacao === 'EXTRATO_GRUPO'">
            <CrosierDropdownEntity
              col="6"
              v-model="this.filters.grupo"
              entity-uri="/api/fin/grupo"
              optionLabel="descricao"
              :orderBy="{ descricao: 'ASC' }"
              :filters="{ ativo: true }"
              :properties="['id', 'descricao']"
              id="grupo"
              label="Grupo"
            />
            <CrosierDropdownEntity
              col="6"
              v-if="this.filters.grupo"
              v-model="this.filters.grupoItem"
              entity-uri="/api/fin/grupoItem"
              optionLabel="descricaoMontada"
              :optionValue="null"
              :orderBy="{ dtVencto: 'DESC' }"
              :filters="{ pai: this.filters.grupo }"
              :properties="['id', 'descricaoMontada', 'pai']"
              id="grupoItem"
              label="Fatura"
            />
            <div class="col-md-6" v-else>
              <div class="form-group">
                <label>Fatura</label>
                <Skeleton class="form-control" height="2rem" />
              </div>
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

        <div class="card mt-3" v-if="this.results">
          <div class="card-body">
            <div class="alert alert-success d-flex align-items-center" role="alert">
              <h3><i class="far fa-check-circle"></i> Sucesso!</h3>
            </div>
            <p class="card-text" v-if="this.fields.tipoImportacao === 'EXTRATO_SIMPLES'">
              {{ this.results.qtdeImportadas }} movimentações importadas para a carteira
              <b>{{ this.fields.carteira.descricaoMontada }}</b
              >.
            </p>
            <p class="card-text" v-if="this.fields.tipoImportacao === 'EXTRATO_CARTAO'">
              {{ this.results.qtdeImportadas }} movimentações importadas para a operadora
              <b>{{ this.fields.operadoraCartao.descricao }}</b
              >.
            </p>
            <p class="card-text" v-if="this.fields.tipoImportacao === 'EXTRATO_GRUPO'">
              {{ this.results.qtdeImportadas }} movimentações importadas para a operadora
              <b>{{ this.fields.grupoItem.descricaoMontada }}</b
              >.
            </p>

            <pre>{{ this.results.LINHAS_RESULT }}</pre>

            <button
              v-if="this.retornouAsDatas"
              type="button"
              class="btn btn-secondary"
              @click="this.visualizarMovimentacoes"
            >
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
import Toast from "primevue/toast";
import Skeleton from "primevue/skeleton";
import ConfirmDialog from "primevue/confirmdialog";
import moment from "moment";
import * as yup from "yup";

export default {
  components: {
    Toast,
    Skeleton,
    ConfirmDialog,
    CrosierBlock,
    CrosierDropdownEntity,
    CrosierDropdown,
    CrosierInputTextarea,
  },

  data() {
    return {
      retornouAsDatas: false,
      results: null,
    };
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
                formData.carteira =
                  formData.carteira && formData.carteira["@id"] ? formData.carteira["@id"] : null;

                formData.operadoraCartao =
                  formData.operadoraCartao && formData.operadoraCartao["@id"]
                    ? formData.operadoraCartao["@id"]
                    : null;

                formData.grupoItem =
                  formData.grupoItem && formData.grupoItem["@id"]
                    ? formData.grupoItem["@id"]
                    : null;
              },
            });

            if (!rs?.data?.RESULT === "OK") {
              throw new Error();
            }
            this.results = rs.data.DATA;
            this.retornouAsDatas = this.results.menorData && this.results.maiorData;
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

    schemaValidator() {
      if (this.fields.tipoImportacao === "EXTRATO_SIMPLES") {
        return yup.object().shape({
          tipoImportacao: yup.string().required(),
          carteira: yup.mixed().required(),
        });
      }
      if (this.fields.tipoImportacao === "EXTRATO_CARTAO") {
        return yup.object().shape({
          tipoImportacao: yup.string().required(),
          operadoraCartao: yup.mixed().required(),
        });
      }
      if (this.fields.tipoImportacao === "EXTRATO_GRUPO") {
        return yup.object().shape({
          tipoImportacao: yup.string().required(),
          grupoItem: yup.mixed().required(),
        });
      }

      return null;
    },
  },
};
</script>

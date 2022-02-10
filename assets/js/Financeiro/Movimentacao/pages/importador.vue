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

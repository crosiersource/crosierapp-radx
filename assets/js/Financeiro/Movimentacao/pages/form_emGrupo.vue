<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <Toast position="bottom-right" class="mb-5" />
  <ConfirmDialog></ConfirmDialog>

  <CrosierFormS
    @submitForm="this.submitForm"
    titulo="Movimentação"
    subtitulo="Lançamento para grupo de movimentações"
  >
    <template #btns>
      <div class="dropdown ml-2 float-right">
        <button
          v-if="this.fields.id"
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
            class="dropdown-item"
            :href="'/v/fin/movimentacao/recorrente/form?id=' + this.fields.id"
            title="Transformar esta movimentação em recorrente"
          >
            <i class="fas fa-undo" aria-hidden="true"></i> Transformar em Recorrente
          </a>
          <button
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

    <div class="form-row">
      <CrosierDropdownEntity
        col="6"
        v-model="this.aux.grupo"
        entity-uri="/api/fin/grupo"
        optionLabel="descricao"
        :orderBy="{ descricao: 'ASC' }"
        :filters="{ ativo: true }"
        label="Grupo"
        id="grupo"
      />

      <CrosierDropdownEntity
        v-if="this.aux.grupo"
        col="6"
        v-model="this.fields.grupoItem"
        :error="this.fieldsErrors.grupoItem"
        entity-uri="/api/fin/grupoItem"
        optionLabel="descricaoMontada"
        :orderBy="{ descricao: 'ASC' }"
        :filters="{ pai: this.aux.grupo }"
        id="grupoItem"
        @change="this.doFilterNextTick"
        label="Fatura"
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
      <CrosierInputText
        label="Descrição"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.fieldsErrors.descricao"
      />
    </div>

    <div class="form-row">
      <CrosierCalendar
        label="Dt Moviment"
        col="7"
        id="dtMoviment"
        v-model="this.fields.dtMoviment"
        :error="this.fieldsErrors.dtMoviment"
        @focus="this.onDtMovimentFocus"
      />

      <CrosierCurrency
        label="Valor"
        col="5"
        id="valorTotal"
        v-model="this.fields.valorTotal"
        :error="this.fieldsErrors.valorTotal"
      />
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
  api,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import axios from "axios";
import moment from "moment";

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
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.$store.dispatch("loadData");
    this.schemaValidator = yup.object().shape({
      categoria: yup.mixed().required().typeError(),
      descricao: yup.mixed().required().typeError(),
      dtMoviment: yup.date().required().typeError(),
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
          formData.tipoLancto = "/api/fin/tipoLancto/20";
          formData.categoria = formData.categoria["@id"];

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

          delete formData.cadeia;
          delete formData.carteira;
          delete formData.modo;
          delete formData.fatura;
        },
      });
      this.setLoading(false);
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
    ...mapGetters({ fields: "getFields", fieldsErrors: "getFieldsErrors", aux: "getAux" }),
  },
};
</script>
<style scoped>
.camposEmpilhados {
  margin-top: -15px;
}
</style>

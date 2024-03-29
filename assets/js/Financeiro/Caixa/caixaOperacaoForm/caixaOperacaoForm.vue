<template>
  <Toast position="bottom-right" class="mb-5" />

  <ConfirmDialog></ConfirmDialog>

  <CrosierFormS @submitForm="this.submitForm" :titulo="this.operacaoASerExecutada" :formUrl="null">
    <div class="form-row">
      <CrosierDropdownEntity
        v-model="this.fields.carteira"
        entity-uri="/api/fin/carteira"
        optionLabel="descricao"
        :optionValue="null"
        :orderBy="{ descricao: 'ASC' }"
        :filters="{ caixa: true, atual: true }"
        label="Caixa"
        id="caixa"
        @change="this.onChangeCaixa"
      />
    </div>

    <div v-if="this.fields.carteira?.id">
      <div class="form-row">
        <CrosierInputText
          id="statusAtualDoCaixa"
          label="Status Atual"
          col="6"
          v-model="this.fields.carteira.caixaStatus"
          disabled
        />

        <CrosierInputText
          id="operacao"
          label="Executar..."
          col="6"
          v-model="this.operacaoASerExecutada"
          disabled
        />

        <CrosierCalendar
          id="operacao"
          label="Dt Última Operação"
          showTime
          inputClass="crsr-datetime"
          col="6"
          v-model="this.fields.carteira.caixaDtUltimaOperacao"
          disabled
        />

        <CrosierInputText
          v-if="this.fields.carteira.caixaResponsavel"
          id="caixaResponsavel"
          label="Responsável Atual"
          col="6"
          v-model="this.fields.carteira.caixaResponsavel.nome"
          disabled
        />
      </div>

      <div class="form-row">
        <CrosierInputText
          id="operacao"
          label="Operação"
          col="3"
          v-model="this.fields.operacao"
          disabled
        />

        <CrosierCalendar
          id="operacao"
          label="Dt Operação"
          showTime
          inputClass="crsr-datetime"
          col="3"
          v-model="this.fields.dtOperacao"
        />

        <CrosierInputText
          id="responsavel_nome"
          label="Responsável"
          col="3"
          v-model="this.fields.responsavel.nome"
          disabled
        />

        <CrosierCurrency id="valor" label="Valor" col="3" v-model="this.fields.valor" disabled />
      </div>

      <div class="form-row">
        <CrosierInputText id="obs" label="Obs" col="12" v-model="this.fields.obs" />
      </div>
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
  CrosierFormS,
  CrosierInputText,
  CrosierDropdownEntity,
  submitForm,
} from "crosier-vue";
import { mapGetters, mapMutations, mapActions } from "vuex";
import { nextTick } from "vue";

export default {
  components: {
    CrosierFormS,
    ConfirmDialog,
    Toast,
    CrosierCalendar,
    CrosierInputText,
    CrosierCurrency,
    CrosierDropdownEntity,
  },

  data() {
    return {
      agora: null,
      criarVincularFields: false,
      schemaValidator: {},
      validDate: new Date(),
    };
  },

  async mounted() {
    this.setLoading(true);

    this.schemaValidator = yup.object().shape({
      dtOperacao: yup.date().required().typeError(),
    });

    const me = await api.get({
      apiResource: "/api/whoami",
    });

    this.fields.responsavel = me.data;

    this.loadData();

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),
    ...mapActions(["loadData"]),

    onChangeCaixa() {
      nextTick(() => {
        if (!this.fields?.carteira?.caixaStatus) {
          this.fields.carteira.caixaStatus = "FECHADO";
        }
        this.fields.operacao =
          this.fields.carteira.caixaStatus === "FECHADO" ? "ABERTURA" : "FECHAMENTO";
        this.fields.dtOperacao = new Date();
      });
    },

    async submitForm() {
      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          if (
            await submitForm({
              apiResource: "/api/fin/caixaOperacao",
              schemaValidator: this.schemaValidator,
              $store: this.$store,
              formDataStateName: "fields",
              $toast: this.$toast,
              setUrlId: false,
              fnBeforeSave: (formData) => {
                formData.carteira = formData.carteira["@id"];
                formData.responsavel = `/api/sec/user/${formData.responsavel.id}`;
                formData.valor = formData.valor ?? 0.0;
              },
            })
          ) {
            // eslint-disable-next-line no-restricted-globals
            history.go(0);
          }
          this.setLoading(false);
        },
      });
    },
  },

  computed: {
    ...mapGetters({
      fields: "getFields",
      formErrors: "getFieldsErrors",
    }),

    operacaoASerExecutada() {
      return this.fields.carteira?.caixaStatus === "ABERTO"
        ? "Fechamento de Caixa"
        : "Abertura de Caixa";
    },
  },
};
</script>

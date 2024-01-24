<template>
  <Toast position="bottom-right" class="mb-5" />

  <ConfirmDialog />

  <CrosierFormS
    @submitForm="this.submitForm"
    titulo="Lançamento de Sangria"
    :semBotaoSalvar="!this.fields?.carteira?.id"
  >
    <div class="form-row">
      <CrosierDropdownEntity
        col="4"
        v-model="this.fields.carteira"
        entity-uri="/api/fin/carteira"
        optionLabel="descricao"
        :optionValue="null"
        :orderBy="{ nomePrefixado: 'ASC' }"
        :filters="{ caixa: true, atual: true }"
        label="Caixa Origem"
        id="caixa"
        @change="this.onChangeCarteiraOrigem"
      />

      <CrosierCurrency col="3" id="saldoOrigem" label="Saldo" v-model="this.saldoOrigem" disabled />

      <CrosierInputText
        col="2"
        v-model="this.fields.carteira.caixaStatus"
        label="Status"
        disabled
      />

      <CrosierCalendar
        col="3"
        label="Em..."
        v-model="this.fields.carteira.caixaDtUltimaOperacao"
        disabled
      />
    </div>

    <div class="form-row" v-if="this.fields?.carteira?.id">
      <CrosierDropdownEntity
        col="4"
        v-model="this.fields.carteiraDestino"
        entity-uri="/api/fin/carteira"
        optionLabel="descricao"
        :optionValue="null"
        :orderBy="{ nomePrefixado: 'ASC' }"
        :filters="{ caixa: true, atual: true, destinoDeSangrias: true }"
        label="Destino"
        id="caixa"
      />

      <CrosierCurrency
        id="saldoDestino"
        label="Saldo"
        col="3"
        v-model="this.saldoDestino"
        disabled
      />

      <CrosierCurrency
        id="valor"
        label="Valor da Sangria"
        col="3"
        v-model="this.fields.valor"
        :disabled="!this.fields?.carteira?.id"
      />
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import {
  CrosierCalendar,
  CrosierCurrency,
  CrosierDropdownEntity,
  CrosierFormS,
  CrosierInputText,
  submitForm,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import { nextTick } from "vue";
import axios from "axios";
import moment from "moment-timezone";

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
      saldoOrigem: 0,
      saldoDestino: 0,
    };
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    moment(date) {
      return moment(date);
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
              apiResource: "/api/fin/movimentacao",
              schemaValidator: this.schemaValidator,
              $store: this.$store,
              formDataStateName: "fields",
              $toast: this.$toast,
              setUrlId: false,
              fnBeforeSave: (formData) => {
                formData.status = "REALIZADO";
                formData.carteira = formData.carteira["@id"];
                formData.carteiraDestino = formData.carteiraDestino["@id"];
                formData.dtMoviment = this.fields.carteira.caixaDtUltimaOperacao;
                formData.categoria = "/api/fin/categoria/299";
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

    onChangeCarteiraOrigem() {
      nextTick(async () => {
        this.saldoOrigem = await this.loadSaldo(this.fields.carteira);
      });
    },

    onChangeCarteiraDestino() {
      nextTick(async () => {
        this.saldoDestino = await this.loadSaldo(this.fields.carteiraDestino);
      });
    },

    async loadSaldo(carteira) {
      this.setLoading(true);

      const data = this.fields.carteira.caixaDtUltimaOperacao;

      const rs = await axios.get(
        `/api/fin/saldo?carteira=${this.fields.carteira["@id"]}&dtSaldo=${data}`
      );

      let saldo = 0;

      if (rs?.status === 200) {
        saldo = rs.data[0]?.totalRealizadas ?? 0.0;
      } else {
        this.$toast.add({
          severity: "warn",
          summary: "Atenção",
          detail: `Não foi possível obter o saldo para o caixa`,
          life: 5000,
        });
      }

      this.setLoading(false);

      return saldo;
    },
  },

  computed: {
    ...mapGetters({
      fields: "getFields",
      formErrors: "getFieldsErrors",
    }),
  },
};
</script>

<template>
  <Toast class="mt-4" />
  <ConfirmDialog></ConfirmDialog>
  <CrosierFormS @submitForm="this.submitForm" titulo="Configurações do Cliente">
    <div class="form-row">
      <CrosierInputId col="3" v-model="this.clienteConfig.id" disabled />
      <CrosierDropdown
        col="8"
        label="Cliente"
        id="cliente"
        v-model="this.clienteConfig.cliente"
        :error="this.formErrors.cliente"
        :options="this.clientes"
        optionLabel="nome"
        :optionValue="null"
        @change="this.onChangeCliente"
      />
      <CrosierSwitch col="1" label="Ativo" v-model="this.clienteConfig.ativo" />
    </div>
    <div class="form-row">
      <CrosierInputText
        label="URL Loja"
        inputClass="notuppercase"
        col="8"
        id="nome"
        v-model="this.clienteConfig.jsonData['url_loja']"
        :error="this.formErrors['jsonData.url_loja']"
        helpText="ATENÇÃO: Manter sempre com a última barra (/)"
      />

      <CrosierInputText
        col="4"
        label="E-mails dos Responsáveis"
        id="emailsResponsaveis"
        v-model="this.clienteConfig.jsonData['emailDests']"
        helpText="E-mails que recebem o link para ativação do Mercado Livre. Informar separados por vírgulas."
        inputClass="lowercase"
      />
    </div>

    <div class="card mt-3">
      <div class="card-body">
        <div class="d-sm-flex flex-nowrap">
          <div>
            <h5 class="card-title">Tray</h5>
            <h6 class="card-subtitle mb-2 text-muted">Configurações</h6>
          </div>

          <div class="ml-auto">
            <button type="button" class="btn btn-sm btn-success" @click="this.obterNovoCodigo">
              <i class="fas fa-link"></i>
              Obter novo código
            </button>

            <button
              type="button"
              :disabled="!this.permiteAutorizar"
              class="btn btn-sm btn-danger ml-1"
              @click="this.autorizarNaTray"
            >
              <i class="fas fa-link"></i>
              Autorizar
            </button>

            <button
              type="button"
              v-if="this.permiteRefreshToken"
              class="btn btn-sm btn-warning ml-1"
              @click="this.renewAccessTokenTray"
            >
              <i class="fas fa-sync-alt"></i>
              Refresh Token
            </button>
          </div>
        </div>
        <div class="form-row mt-3">
          <CrosierInputText
            label="Store Id"
            col="3"
            inputClass="notuppercase"
            id="tray_store_id"
            type="text"
            v-model="this.clienteConfig.jsonData.tray.store_id"
          />
          <CrosierInputText
            label="Code"
            col="5"
            inputClass="notuppercase"
            id="tray_code"
            type="text"
            v-model="this.clienteConfig.jsonData['tray']['code']"
            helpText="Retorno da chamada 'auth'"
          />

          <CrosierCalendar
            label="Integrar a partir de"
            col="2"
            id="pedidos_integrados_ate"
            v-model="this.clienteConfig.jsonData['tray']['pedidos_integrados_ate']"
            helpText="Só pesquisará pedidos modificados a partir desta data (inclusive)"
          />
        </div>
        <div class="form-row mt-3">
          <CrosierInputText
            col="8"
            label="Access Token"
            inputClass="notuppercase"
            id="tray_access_token"
            type="text"
            v-model="this.clienteConfig.jsonData['tray']['access_token']"
          />
          <CrosierCalendar
            showSeconds
            label="Dt Expiração"
            col="4"
            disabled
            id="tray_dt_exp_access_token"
            v-model="this.clienteConfig.trayDtExpAccessToken"
          />
        </div>
        <div class="form-row mt-3">
          <CrosierInputText
            label="Refresh Token"
            col="8"
            inputClass="notuppercase"
            id="tray_refresh_token"
            v-model="this.clienteConfig.jsonData['tray']['refresh_token']"
          />
          <CrosierCalendar
            showSeconds
            col="4"
            label="Dt Expiração"
            disabled
            id="tray_dt_exp_refresh_token"
            v-model="this.clienteConfig.jsonData['tray']['dt_exp_refresh_token']"
          />
        </div>
        <div class="form-row mt-3">
          <div class="col-md-12 text-right">
            <button type="button" class="btn btn-sm btn-danger" @click="this.excluir">
              <i class="far fa-trash-alt"></i> Excluir
            </button>
          </div>
        </div>
      </div>
    </div>

    <MercadoLivre v-if="Array.isArray(this.clienteConfig?.jsonData?.mercadolivre)" />
  </CrosierFormS>
</template>

<script>
import ConfirmDialog from "primevue/confirmdialog";
import Toast from "primevue/toast";
import Calendar from "primevue/calendar";
import * as yup from "yup";
import axios from "axios";
import { mapGetters, mapMutations } from "vuex";
import {
  api,
  CrosierFormS,
  CrosierInputId,
  CrosierInputText,
  CrosierSwitch,
  CrosierDropdown,
  CrosierCalendar,
  submitForm,
} from "crosier-vue";
import MercadoLivre from "./form_mercadoLivre";

export default {
  components: {
    CrosierCalendar,
    ConfirmDialog,
    CrosierFormS,
    CrosierInputId,
    CrosierInputText,
    CrosierSwitch,
    CrosierDropdown,
    Toast,
    MercadoLivre,
  },

  data() {
    return {
      schemaValidator: {},
      validDate: new Date(),
    };
  },

  async mounted() {
    this.setLoading(true);
    await this.$store.dispatch("loadData");
    await this.$store.dispatch("loadClientes");
    this.schemaValidator = yup.object().shape({
      cliente: yup.mixed().required().typeError(),
      jsonData: yup.object().shape({
        url_loja: yup.string().required().typeError(),
        mercadolivre: yup.mixed().required().typeError(),
        tray: yup.mixed().required().typeError(),
      }),
    });
    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setNewClienteConfig", "setClienteConfig"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/ecommerce/clienteConfig",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "clienteConfig",
        $toast: this.$toast,
        setUrlId: true,
        fnBeforeSave: (formData) => {
          formData.cliente = formData.cliente["@id"];
          if (formData?.jsonData?.tray?.pedidos_integrados_ate) {
            formData.jsonData.tray.pedidos_integrados_ate =
              formData.jsonData.tray.pedidos_integrados_ate.toISOString().substring(0, 10);
          }
        },
      });
      this.setLoading(false);
    },

    onChangeCliente() {
      this.$nextTick(async () => {
        this.setLoading(true);
        const cliente = { ...this.clienteConfig.cliente };
        const response = await api.get({
          apiResource: `/api/ecommerce/clienteConfig`,
          filters: { cliente: this.clienteConfig.cliente["@id"] },
        });
        if (response.data["hydra:member"].length) {
          // eslint-disable-next-line max-len
          window.location = `form?id=${response.data["hydra:member"][0].id}`;
        } else {
          this.setNewClienteConfig();
          const clienteConfig = { ...this.clienteConfig };
          clienteConfig.cliente = cliente;
          this.setClienteConfig(clienteConfig);
          // eslint-disable-next-line no-restricted-globals
          history.pushState({}, null, "form");
          this.setLoading(false);
        }
      });
    },

    async autorizarNaTray() {
      this.$confirm.require({
        message: "Confirmar operação?",
        header: "Confirmação",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          this.submitForm();
          window.location = `/api/ecommerce/clienteConfig/autorizarNaTray/${this.clienteConfig.id}`;
        },
      });
    },

    async renewAccessTokenTray() {
      this.$confirm.require({
        message: "Confirmar operação?",
        header: "Confirmação",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          const rs = await axios.get(
            // eslint-disable-next-line max-len
            `/api/ecommerce/tray/renewAccessToken/${this.clienteConfig.id}`,
            {
              validateStatus(status) {
                return status < 500;
              },
            }
          );
          if (rs?.data?.RESULT === "OK") {
            window.location = `form?id=${this.clienteConfig.id}`;
          } else {
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: "Erro ao renovar access token na tray",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },

    async excluir() {
      this.$confirm.require({
        message: "Confirmar operação?",
        header: "Confirmação",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          const rs = await api.delete(
            // eslint-disable-next-line max-len
            `/api/ecommerce/clienteConfig/${this.clienteConfig.id}`,
            {
              validateStatus(status) {
                return status < 500;
              },
            }
          );
          if (rs.status === 400) {
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: "Erro ao deletar registro",
              life: 5000,
            });
          } else {
            window.location = "form";
          }
          this.setLoading(false);
        },
      });
    },

    obterNovoCodigo() {
      window.open(
        `${
          this.clienteConfig.jsonData.url_loja
          // eslint-disable-next-line max-len
        }auth.php?response_type=code&consumer_key=941cf91385f289a72cf395e8b5272ef77f730650418b1257ac4193bd567f0463&callback=https://radx.demo.crosier.com.br/ecommerce/tray/endpoint`,
        "_blank"
      );
    },
  },
  computed: {
    ...mapGetters({
      clienteConfig: "getClienteConfig",
      clientes: "getClientes",
      formErrors: "getClienteConfigErrors",
    }),

    permiteAutorizar() {
      return this.clienteConfig?.jsonData?.tray?.code;
    },

    permiteRefreshToken() {
      return (
        this.clienteConfig?.jsonData?.tray?.code &&
        this.clienteConfig?.jsonData?.tray?.access_token &&
        this.clienteConfig?.jsonData?.tray?.refresh_token
      );
    },
  },
};
</script>

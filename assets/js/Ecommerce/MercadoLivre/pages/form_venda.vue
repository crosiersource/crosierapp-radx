<template>
  <Toast class="mt-4" />
  <ConfirmDialog></ConfirmDialog>
  <CrosierFormS @submitForm="this.submitForm" titulo="Configurações do Cliente">
    <div class="form-row">
      <div class="col-md-3">
        <div class="form-group">
          <label for="id">ID</label>
          <InputText
            class="form-control"
            id="id"
            type="text"
            v-model="this.clienteConfig.id"
            disabled
          />
        </div>
      </div>
      <div class="col-md-7">
        <div class="form-group">
          <label for="cliente">Cliente</label>
          <Dropdown
            :class="{ 'form-control': true, 'is-invalid': this.formErrors['cliente'] }"
            id="cliente"
            inputId="cliente"
            v-model="this.clienteConfig.cliente"
            :options="this.clientes"
            optionLabel="nome"
            placeholder="--"
            @change="this.onChangeCliente"
            :filter="true"
          />
          <div class="invalid-feedback">
            {{ this.formErrors["cliente"] }}
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label for="id">Ativo</label>
          <br />
          <InputSwitch v-model="this.clienteConfig.ativo" />
        </div>
      </div>
    </div>
    <div class="form-row">
      <div class="col-md-8">
        <div class="form-group">
          <label for="name">URL Loja</label>
          <InputText
            :class="{
              'form-control notuppercase ': true,
              'is-invalid': this.formErrors['jsonData.url_loja'],
            }"
            id="nome"
            type="text"
            v-model="this.clienteConfig.jsonData['url_loja']"
          />
          <small class="form-text text-muted">Manter com a última barra (/)</small>
          <div class="invalid-feedback">
            {{ this.formErrors["jsonData.url_loja"] }}
          </div>
        </div>
      </div>

      <CrosierInputText
        col="4"
        label="E-mails dos Responsáveis (Crosier)"
        id="emailsResponsaveis"
        v-model="this.clienteConfig.jsonData['emailDests']"
        helpText="Separados por vírgulas"
        inputClass="lowercase"
      />
    </div>

    <div class="card mt-3">
      <div class="card-body">
        <h5 class="card-title">Tray</h5>
        <h6 class="card-subtitle mb-2 text-muted">Configurações</h6>
        <div class="form-row mt-3">
          <div class="col-md-3">
            <div class="form-group">
              <label for="tray_store_id">Store Id</label>
              <InputText
                class="form-control notuppercase"
                id="tray_store_id"
                type="text"
                v-model="this.clienteConfig.jsonData.tray.store_id"
              />
            </div>
          </div>
          <div class="col-md-5">
            <div class="form-group">
              <label for="tray_code">Code</label>
              <InputText
                class="form-control notuppercase"
                id="tray_code"
                type="text"
                v-model="this.clienteConfig.jsonData['tray']['code']"
              />
              <small v-if="this.clienteConfig.jsonData['tray']['code']" class="form-text text-muted"
                >Retorno da chamada 'auth'</small
              >
              <a
                :href="
                  this.clienteConfig.jsonData['url_loja'] +
                  // eslint-disable-next-line max-len
                  'auth.php?response_type=code&consumer_key=941cf91385f289a72cf395e8b5272ef77f730650418b1257ac4193bd567f0463&callback=https://radx.crosier.conectamaisvc.com.br/ecommerce/tray/endpoint/' +
                  this.clienteConfig.id
                "
              >
                <small class="form-text">Obter novo código</small></a
              >
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="pedidos_integrados_ate">Integrar a partir de</label>
              <Calendar
                id="pedidos_integrados_ate"
                :showIcon="true"
                :showOnFocus="true"
                inputClass="form-control crsr-date"
                v-model="this.clienteConfig.jsonData['tray']['pedidos_integrados_ate']"
                dateFormat="dd/mm/yy"
              />
              <small class="form-text text-muted"
                >Só pesquisa pedidos modificados a partir desta data (inclusive)</small
              >
            </div>
          </div>
          <div class="col-md-2">
            <button
              type="button"
              v-if="this.clienteConfig.jsonData['tray']['code']"
              class="btn btn-block btn-sm btn-danger mt-4"
              @click="this.autorizarNaTray"
            >
              <i class="fas fa-link"></i>
              Autorizar
            </button>

            <button
              type="button"
              v-if="
                this.clienteConfig.jsonData['tray']['code'] &&
                this.clienteConfig.jsonData['tray']['access_token'] &&
                this.clienteConfig.jsonData['tray']['refresh_token']
              "
              class="btn btn-block btn-sm btn-warning mt-4"
              @click="this.renewAccessTokenTray"
            >
              <i class="fas fa-sync-alt"></i>
              Refresh Token
            </button>
          </div>
        </div>
        <div class="form-row mt-3">
          <div class="col-md-8">
            <div class="form-group">
              <label for="tray_access_token">Access Token</label>
              <InputText
                class="form-control notuppercase"
                id="tray_access_token"
                type="text"
                v-model="this.clienteConfig.jsonData['tray']['access_token']"
              />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="tray_dt_exp_access_token">Dt Expiração</label>
              <InputText
                class="form-control notuppercase"
                disabled="disabled"
                id="tray_dt_exp_access_token"
                type="text"
                v-model="this.clienteConfig.trayDtExpAccessToken"
              />
            </div>
          </div>
        </div>
        <div class="form-row mt-3">
          <div class="col-md-8">
            <div class="form-group">
              <label for="tray_refresh_token">Refresh Token</label>
              <InputText
                class="form-control notuppercase"
                id="tray_refresh_token"
                type="text"
                v-model="this.clienteConfig.jsonData['tray']['refresh_token']"
              />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="tray_dt_exp_refresh_token">Dt Expiração</label>
              <InputText
                disabled="disabled"
                class="form-control"
                id="tray_dt_exp_refresh_token"
                type="text"
                v-model="this.clienteConfig.jsonData['tray']['dt_exp_refresh_token']"
              />
            </div>
          </div>
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
import InputText from "primevue/inputtext";
import InputSwitch from "primevue/inputswitch";
import Toast from "primevue/toast";
import Dropdown from "primevue/dropdown";
import Calendar from "primevue/calendar";
import * as yup from "yup";
import axios from "axios";
import { mapGetters, mapMutations } from "vuex";
import { api, CrosierFormS, submitForm, CrosierInputText } from "crosier-vue";
import MercadoLivre from "./form_mercadoLivre";

export default {
  components: {
    Calendar,
    ConfirmDialog,
    CrosierFormS,
    InputText,
    InputSwitch,
    Dropdown,
    Toast,
    MercadoLivre,
    CrosierInputText,
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

    async onChangeCliente() {
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
  },
  computed: {
    ...mapGetters({
      clienteConfig: "getClienteConfig",
      clientes: "getClientes",
      formErrors: "getClienteConfigErrors",
    }),
  },
};
</script>

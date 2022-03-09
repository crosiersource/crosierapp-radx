import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import ConfirmationService from "primevue/confirmationservice";
import deepmerge from "deepmerge";
import { api } from "crosier-vue";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import Page from "./pages/form_venda";
import "primeflex/primeflex.css";
import "primevue/resources/themes/bootstrap4-light-blue/theme.css"; // theme
import "primevue/resources/primevue.min.css"; // core css
import "primeicons/primeicons.css";
import "crosier-vue/src/yup.locale.pt-br.js";

const app = createApp(Page);

app.use(PrimeVue, primevueOptions);
app.use(ConfirmationService);
app.use(ToastService);

// Create a new store instance.
const store = createStore({
  state() {
    return {
      loading: 0,
      origClienteConfig: {
        jsonData: {
          url_loja: null,
          mercadolivre: [],
          tray: {
            store_id: null,
            pedidos_integrados_ate: null,
          },
        },
      },
      clienteConfig: {
        jsonData: {
          url_loja: null,
          mercadolivre: [],
          tray: {
            store_id: null,
            pedidos_integrados_ate: null,
          },
        },
      },
      clientes: [],
      clienteConfigErrors: {},
    };
  },
  getters: {
    isLoading(state) {
      return state.loading > 0;
    },

    getClienteConfig(state) {
      const { clienteConfig } = state;
      return clienteConfig;
    },

    getClienteConfigErrors(state) {
      const { clienteConfigErrors } = state;
      return clienteConfigErrors;
    },

    getClientes(state) {
      const { clientes } = state;
      return clientes;
    },
  },

  mutations: {
    setLoading(state, loading) {
      if (loading) {
        state.loading++;
      } else {
        state.loading--;
      }
    },

    setClienteConfig(state, clienteConfig) {
      if (clienteConfig?.jsonData?.tray?.pedidos_integrados_ate) {
        clienteConfig.jsonData.tray.pedidos_integrados_ate = new Date(
          `${clienteConfig.jsonData.tray.pedidos_integrados_ate}T12:00:00.000-03:00`
        );
      }

      state.clienteConfig = clienteConfig;
    },

    setNewClienteConfig(state) {
      state.clienteConfig = state.origClienteConfig;
    },

    setClienteConfigErrors(state, clienteConfigErrors) {
      state.clienteConfigErrors = clienteConfigErrors;
    },

    setClientes(state, clientes) {
      state.clientes = clientes;
    },
  },

  actions: {
    async loadData(context) {
      context.commit("setLoading", true);
      const id = new URLSearchParams(window.location.search.substring(1)).get("id");
      if (id) {
        try {
          const response = await api.get({
            apiResource: `/api/ecommerce/clienteConfig/${id}}`,
          });

          if (response.data["@id"]) {
            const clienteConfig = deepmerge.all([context.state.origClienteConfig, response.data]);

            if (
              clienteConfig.jsonData?.mercadolivre &&
              !Array.isArray(clienteConfig.jsonData?.mercadolivre)
            ) {
              const vAnterior = { ...clienteConfig.jsonData.mercadolivre };
              clienteConfig.jsonData.mercadolivre = [];
              clienteConfig.jsonData.mercadolivre.push(vAnterior);
            }

            context.commit("setClienteConfig", clienteConfig);
          } else {
            console.error("Id não encontrado");
          }
        } catch (err) {
          console.error(err);
        }
      }
      context.commit("setLoading", false);
    },

    async loadClientes(context) {
      context.commit("setLoading", true);

      try {
        const response = await api.get({
          apiResource: "/api/crm/cliente",
          allRows: true,
          order: { nome: "ASC" },
        });
        if (response.data["hydra:member"]) {
          context.commit("setClientes", response.data["hydra:member"]);
        } else {
          console.error("Não encontrado");
        }
      } catch (err) {
        console.error(err);
      }
      context.commit("setLoading", false);
    },
  },
});

app.use(store);

app.mount("#app");

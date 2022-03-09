import { createApp } from "vue";
import { createStore } from "vuex";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import { api } from "crosier-vue";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import Page from "./pages/list_perguntas";
import "primeflex/primeflex.css";
import "primevue/resources/themes/saga-blue/theme.css"; // theme
import "primevue/resources/primevue.min.css"; // core css
import "primeicons/primeicons.css";

const app = createApp(Page);

app.use(PrimeVue, primevueOptions);

app.use(ToastService);

// Create a new store instance.
const store = createStore({
  state() {
    return {
      loading: 0,
      filters: {},
      clientes: {},
    };
  },

  getters: {
    isLoading(state) {
      return state.loading > 0;
    },

    getFilters(state) {
      return state.filters;
    },

    getClientes(state) {
      return state.clientes;
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

    setFilters(state, filters) {
      if (filters["dtPergunta[before]"]) {
        filters["dtPergunta[before]"] = new Date(filters["dtPergunta[before]"]);
      }
      if (filters["dtPergunta[after]"]) {
        filters["dtPergunta[after]"] = new Date(filters["dtPergunta[after]"]);
      }

      state.filters = filters;
    },

    setClientes(state, clientes) {
      state.clientes = clientes;
    },
  },

  actions: {
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
app.use(ConfirmationService);

app.directive("tooltip", Tooltip);

app.mount("#app");

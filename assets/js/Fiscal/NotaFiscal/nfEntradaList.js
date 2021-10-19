import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import { createStore } from "vuex";
import Page from "./pages/nfEntradaList";
import "primeflex/primeflex.css";
import "primevue/resources/themes/saga-blue/theme.css"; // theme
import "primevue/resources/primevue.min.css"; // core css
import "primeicons/primeicons.css";

const app = createApp(Page);

app.use(PrimeVue);

app.use(ToastService);

// Create a new store instance.
const store = createStore({
  state() {
    return {
      loading: 0,
      filters: {
        xNomeEmitente: null,
      },
      defaultFilters: {
        entradaSaida: "E",
      },
    };
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
      filters["dtEmissao[after]"] = filters["dtEmissao[after]"]
        ? new Date(filters["dtEmissao[after]"])
        : null;
      filters["dtEmissao[before]"] = filters["dtEmissao[before]"]
        ? new Date(filters["dtEmissao[before]"])
        : null;
      state.filters = filters;
    },
  },

  getters: {
    isLoading(state) {
      return state.loading > 0;
    },

    getFilters(state) {
      return state.filters;
    },

    getDefaultFilters: (state) => state.defaultFilters,
  },
});

app.use(store);
app.use(ConfirmationService);

app.directive("tooltip", Tooltip);

app.mount("#app");

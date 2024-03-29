import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import { createStore } from "vuex";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import Page from "./pages/resultMensal";
import "primeflex/primeflex.css";
import "primevue/resources/themes/saga-blue/theme.css"; // theme
import "primevue/resources/primevue.min.css"; // core css
import "primeicons/primeicons.css";
import "crosier-vue/src/momentjs.locale.ptbr.js";

const app = createApp(Page);

app.use(PrimeVue, primevueOptions);

app.use(ToastService);

// Create a new store instance.
const store = createStore({
  state() {
    return {
      loading: 0,
      filters: {},
      defaultFilters: {
        status: "REALIZADA",
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
      state.filters["dtVenda[after]"] = state.filters["dtVenda[after]"]
        ? new Date(state.filters["dtVenda[after]"])
        : null;
      state.filters["dtVenda[before]"] = state.filters["dtVenda[before]"]
        ? new Date(state.filters["dtVenda[before]"])
        : null;
      state.filters = filters;
    },
  },

  getters: {
    isLoading: (state) => state.loading > 0,

    getFilters: (state) => state.filters,

    getDefaultFilters: (state) => state.defaultFilters,
  },
});

app.use(store);
app.use(ConfirmationService);

app.directive("tooltip", Tooltip);

app.mount("#app");

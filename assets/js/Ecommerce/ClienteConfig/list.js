import { createApp } from "vue";
import { createStore } from "vuex";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import Page from "./pages/list";
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
    };
  },

  getters: {
    isLoading(state) {
      return state.loading > 0;
    },

    getFilters(state) {
      return state.filters;
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
      if (filters["dtVenda[before]"]) {
        filters["dtVenda[before]"] = new Date(filters["dtVenda[before]"]);
      }
      if (filters["dtVenda[after]"]) {
        filters["dtVenda[after]"] = new Date(filters["dtVenda[after]"]);
      }

      state.filters = filters;
    },
  },
});

app.use(store);
app.use(ConfirmationService);

app.directive("tooltip", Tooltip);

app.mount("#app");

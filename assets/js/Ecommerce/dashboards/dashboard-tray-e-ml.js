import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import ConfirmationService from "primevue/confirmationservice";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import moment from "moment";
import Page from "./pages/dashboard-tray-e-ml/dashboard";
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
      filters: {},
    };
  },
  getters: {
    isLoading(state) {
      return state.loading > 0;
    },

    getFilters: (state) => state.filters,

    getPeriodoFormatted(state) {
      if (!state.filters?.periodo) {
        state.filters.periodo = [new Date(moment().subtract(12, "months")), new Date()];
      }

      const periodo = [];

      periodo[0] =
        state.filters?.periodo && new Date(state.filters?.periodo[0]).valueOf()
          ? new Date(state.filters.periodo[0])
          : new Date(moment().subtract(12, "months"));
      periodo[0] = moment(periodo[0]).format("YYYY-MM-DD");

      periodo[1] =
        state.filters?.periodo && new Date(state.filters?.periodo[1]).valueOf()
          ? new Date(state.filters.periodo[1])
          : new Date();
      periodo[1] = moment(periodo[1]).format("YYYY-MM-DD");

      return { periodo };
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

    async setFilters(state, filters) {
      state.filters["dtUtil[after]"] = state.filters["dtUtil[after]"]
        ? new Date(state.filters["dtUtil[after]"])
        : null;
      state.filters["dtUtil[before]"] = state.filters["dtUtil[before]"]
        ? new Date(state.filters["dtUtil[before]"])
        : null;
      state.filters = filters;
    },
  },

  actions: {},
});

app.use(store);

app.mount("#app");

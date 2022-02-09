import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import { createStore } from "vuex";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import Page from "./pages/importador";
import "primeflex/primeflex.css";
import "primevue/resources/themes/saga-blue/theme.css"; // theme
import "primevue/resources/primevue.min.css"; // core css
import "primeicons/primeicons.css";

import "crosier-vue/src/yup.locale.pt-br.js";

const app = createApp(Page);

app.use(PrimeVue, primevueOptions);

app.use(ToastService);

// Create a new store instance.
const store = createStore({
  state() {
    return {
      loading: 0,
      fields: {},
      fieldsErrors: {},
      filters: {},
    };
  },

  getters: {
    isLoading: (state) => state.loading > 0,
    getFields: (state) => state.fields,
    getFieldsErrors: (state) => state.fieldsErrors,
    getFilters: (state) => state.filters,
  },

  mutations: {
    setLoading(state, loading) {
      if (loading) {
        state.loading++;
      } else {
        state.loading--;
      }
    },

    setFields(state, fields) {
      fields.dtMoviment = fields.dtMoviment ? new Date(fields.dtMoviment) : null;
      fields.dtVencto = fields.dtVencto ? new Date(fields.dtVencto) : null;
      fields.dtVenctoEfetiva = fields.dtVenctoEfetiva ? new Date(fields.dtVenctoEfetiva) : null;
      state.fields = fields;
    },

    setFieldsErrors(state, formErrors) {
      state.fieldsErrors = formErrors;
    },

    setFilters(state, filters) {
      state.filters["dtUtil[after]"] = state.filters["dtUtil[after]"]
        ? new Date(state.filters["dtUtil[after]"])
        : null;
      state.filters["dtUtil[before]"] = state.filters["dtUtil[before]"]
        ? new Date(state.filters["dtUtil[before]"])
        : null;
      state.filters = filters;
    },
  },
});

app.use(store);
app.use(ConfirmationService);

app.directive("tooltip", Tooltip);

app.mount("#app");

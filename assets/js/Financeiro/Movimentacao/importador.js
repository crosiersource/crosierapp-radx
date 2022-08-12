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
    };
  },

  getters: {
    isLoading: (state) => state.loading > 0,
    getFields: (state) => state.fields,
    getFieldsErrors: (state) => state.fieldsErrors,
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
      state.fields["dtUtil[after]"] = state.fields["dtUtil[after]"]
        ? new Date(state.fields["dtUtil[after]"])
        : null;
      state.fields["dtUtil[before]"] = state.fields["dtUtil[before]"]
        ? new Date(state.fields["dtUtil[before]"])
        : null;

      state.fields = fields;
    },

    setFieldsErrors(state, formErrors) {
      state.fieldsErrors = formErrors;
    },
  },
});

app.use(store);
app.use(ConfirmationService);

app.directive("tooltip", Tooltip);

app.mount("#app");

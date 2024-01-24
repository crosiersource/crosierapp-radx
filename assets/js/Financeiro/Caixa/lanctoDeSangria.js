import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import ConfirmationService from "primevue/confirmationservice";
import Page from "./sangria/lanctoDeSangria";
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
      fields: {
        carteira: {},
        carteiraDestino: {},
      },
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
      state.fields = fields;
    },

    setFieldsErrors(state, formErrors) {
      state.fieldsErrors = formErrors;
    },
  },
});

app.use(store);

app.mount("#app");

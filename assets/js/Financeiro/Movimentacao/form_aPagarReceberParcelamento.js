import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import ConfirmationService from "primevue/confirmationservice";
import Page from "./pages/form_aPagarReceberParcelamento";
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
      parcelas: [{}],
      parcelasErrors: [{}],
    };
  },

  getters: {
    isLoading: (state) => state.loading > 0,
    getParcelas: (state) => state.parcelas,

    // RTA p/ funcionar com o submitForm
    getFields: (state) => state.parcelas[0],
    getFieldsErrors: (state) => state.parcelasErrors[0],

    getParcelasErrors: (state) => state.parcelasErrors,
  },

  mutations: {
    setLoading(state, loading) {
      if (loading) {
        state.loading++;
      } else {
        state.loading--;
      }
    },

    setParcelasErrors(state, formErrors) {
      state.parcelasErrors = formErrors;
    },
  },
});

app.use(store);

app.mount("#app");

import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import { api } from "crosier-vue";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import ConfirmationService from "primevue/confirmationservice";
import Page from "./pages/cleanLocalStorage";
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
      clinica: {},
      clinicaErrors: [],
    };
  },
  getters: {
    isLoading(state) {
      return state.loading > 0;
    },
    getClinica(state) {
      const { clinica } = state;
      return clinica;
    },
    getClinicaErrors(state) {
      const { clinicaErrors } = state;
      return clinicaErrors;
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

    setClinica(state, clinica) {
      state.clinica = clinica;
    },

    setClinicaErrors(state, formErrors) {
      state.clinicaErrors = formErrors;
    },
  },

  actions: {
    async loadData(context) {
      context.commit("setLoading", true);
      const id = new URLSearchParams(window.location.search.substring(1)).get("id");
      if (id) {
        try {
          const response = await api.get({
            apiResource: `/api/clin/clinica/${id}`,
          });

          if (response.data["@id"]) {
            context.commit("setClinica", response.data);
          } else {
            console.error("Id não encontrado");
          }
        } catch (err) {
          console.error(err);
        }
      }
      context.commit("setLoading", false);
    },
  },
});

app.use(store);

app.mount("#app");

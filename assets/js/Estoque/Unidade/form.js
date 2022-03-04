import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import { api } from "crosier-vue";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import ConfirmationService from "primevue/confirmationservice";
import Page from "./pages/form";
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
        jsonData: {
          endereco: {},
        },
      },
      fieldsErrors: {},
    };
  },
  getters: {
    isLoading: (state) => state.loading > 0,
    getFields(state) {
      const { fields } = state;
      return fields;
    },
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
      if (!fields.jsonData) {
        fields.jsonData = {};
      }
      if (!fields.jsonData.endereco) {
        fields.jsonData.endereco = {};
      }
      state.fields = fields;
    },

    setFieldsErrors(state, formErrors) {
      state.fieldsErrors = formErrors;
    },
  },

  actions: {
    async loadData(context) {
      context.commit("setLoading", true);
      const id = new URLSearchParams(window.location.search.substring(1)).get("id");
      if (id) {
        try {
          const response = await api.get({
            apiResource: `/api/est/unidade/${id}`,
          });

          if (response.data["@id"]) {
            context.commit("setFields", response.data);
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

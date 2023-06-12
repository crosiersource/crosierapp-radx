import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import { api } from "crosier-vue";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import ConfirmationService from "primevue/confirmationservice";
import Page from "./pages/form_aPagarReceber";
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
      fields.dtMoviment = fields.dtMoviment ? new Date(fields.dtMoviment) : null;
      fields.dtVencto = fields.dtVencto ? new Date(fields.dtVencto) : null;
      fields.dtVenctoEfetiva = fields.dtVenctoEfetiva ? new Date(fields.dtVenctoEfetiva) : null;
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
            apiResource: `/api/fin/movimentacao/${id}`,
          });

          if (response.data["@id"]) {
            context.commit("setFields", response.data);
          } else {
            console.error("Id não encontrado");
          }
        } catch (err) {
          console.error(err);
        }
      } else {
        const faturaId = new URLSearchParams(window.location.search.substring(1)).get("fatura");
        if (faturaId) {
          try {
            const response = await api.get({
              apiResource: `/api/fin/fatura/${faturaId}`,
            });

            if (response.data["@id"]) {
              context.commit("setFields", {
                fatura: response.data,
              });
            } else {
              console.error("Fatura não encontrada");
            }
          } catch (err) {
            console.error(err);
          }
        } else {
          const dadosUltimaMovimentacao = JSON.parse(
            localStorage.getItem("dadosUltimaMovimentacao") ?? "{}"
          );
          delete dadosUltimaMovimentacao.grupo;
          delete dadosUltimaMovimentacao.grupoItem;
          context.commit("setFields", dadosUltimaMovimentacao);
        }
      }
      context.commit("setLoading", false);
    },
  },
});

app.use(store);

app.mount("#app");

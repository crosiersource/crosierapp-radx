import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import ConfirmationService from "primevue/confirmationservice";
import Page from "./pages/caixaOperacaoForm";
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
        carteira: {
          caixaResponsavel: {
            nome: null,
          },
        },
        responsavel: {},
      },
      fieldsErrors: {
        carteira: {
          caixaResponsavel: {
            nome: null,
          },
        },
        responsavel: {},
      },
    };
  },
  getters: {
    isLoading(state) {
      return state.loading > 0;
    },
    getFields(state) {
      const { fields } = state;
      return fields;
    },
    getFieldsErrors(state) {
      const { fieldsErrors } = state;
      return fieldsErrors;
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

    setFields(state, fields) {
      fields.dtConsolidado = fields.dtConsolidado ? new Date(fields.dtConsolidado) : null;
      fields.operadoraCartao = fields.operadoraCartao ? fields.operadoraCartao["@id"] : null;
      if (!fields?.carteira?.caixaResponsavel) {
        fields.carteira = {
          ...fields.carteira,
          ...{
            caixaResponsavel: {},
          },
        };
      }
      state.fields = fields;
    },

    setFieldsErrors(state, formErrors) {
      state.fieldsErrors = formErrors;
    },
  },
});

app.use(store);

app.mount("#app");

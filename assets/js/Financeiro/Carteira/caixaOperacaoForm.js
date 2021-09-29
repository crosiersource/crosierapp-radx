import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import { api } from "crosier-vue";
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
        carteira: {},
        responsavel: {},
      },
      fieldsErrors: {
        carteira: {},
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
      if (!id) {
        // eslint-disable-next-line no-alert
        alert("Carteira não encontrada (id)");
        window.location = "/fin/carteira/list";
      }

      try {
        const rsCarteira = await api.get({
          apiResource: `/api/fin/carteira/${id}}`,
        });

        if (!rsCarteira.data["@id"]) {
          // eslint-disable-next-line no-alert
          alert("Carteira não encontrada (id)");
          window.location = "/fin/carteira/list";
        }
        // else...
        if (rsCarteira.data.caixa !== true) {
          // eslint-disable-next-line no-alert
          alert("Carteira não é caixa");
          window.location = "/fin/carteira/list";
        }

        const operacao = rsCarteira.data.caixaStatus === "ABERTO" ? "FECHAMENTO" : "ABERTURA";

        const me = await api.get({
          apiResource: "/api/whoami",
        });

        const fields = {
          carteira: rsCarteira.data,
          operacao,
          responsavel: me.data,
        };

        context.commit("setFields", fields);
      } catch (err) {
        console.error(err);
      }

      context.commit("setLoading", false);
    },
  },
});

app.use(store);

app.mount("#app");

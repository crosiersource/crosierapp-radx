import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import { api } from "crosier-vue";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import ConfirmationService from "primevue/confirmationservice";
import Page from "./form/tabview";
import "primeflex/primeflex.css";
import "primevue/resources/themes/bootstrap4-light-blue/theme.css"; // theme
import "primevue/resources/primevue.min.css"; // core css
import "primeicons/primeicons.css";

import "crosier-vue/src/yup.locale.pt-br.js";

import { fiscalStore } from "../fiscalStore";

const app = createApp(Page);

app.use(PrimeVue, primevueOptions);
app.use(ConfirmationService);
app.use(ToastService);

// Create a new store instance.
const store = createStore({
  modules: { fiscalStore },

  state() {
    return {
      loading: 0,
      exibirDialogItem: false,
      exibirDialogCancelamento: false,
      exibirDialogCartaCorrecao: false,
      dtItensKey: 0,
      dtCartasCorrecaoKey: 0,
      dtHistoricoKey: 0,
      notaFiscal: {
        permiteSalvar: true,
        nossaEmissao: true,
        idDest: null,
        entradaSaida: "S",
        finalidadeNf: "NORMAL",
        transpModalidadeFrete: "SEM_FRETE",
        indicadorFormaPagto: "VISTA",
        jsonData: {},
        dtEmissao: new Date(),
        dtSaiEnt: new Date(),
        foneDestinatario: "(00) 00000-0000",
      },
      notaFiscalErrors: {},
      notaFiscalItem: {},
      notaFiscalItemErrors: {},

      notaFiscalCartaCorrecao: {},
      notaFiscalCartaCorrecaoErrors: {},

      defaultItensFilters: {},
      defaultCartasCorrecaoFilters: {},
      defaultHistoricoFilters: {},
    };
  },

  getters: {
    isLoading: (state) => state.loading > 0,
    getNotaFiscal: (state) => state.notaFiscal,
    getNotaFiscalErrors: (state) => state.notaFiscalErrors,
    getNotaFiscalItem: (state) => state.notaFiscalItem,
    getNotaFiscalItemErrors: (state) => state.notaFiscalItemErrors,

    getDefaultItensFilters(state) {
      state.defaultItensFilters = {
        ...state.defaultItensFilters,
        notaFiscal: state.notaFiscal["@id"],
      };
      return state.defaultItensFilters;
    },

    getDefaultCartasCorrecaoFilters(state) {
      state.defaultCartasCorrecaoFilters = {
        ...state.defaultCartasCorrecaoFilters,
        notaFiscal: state.notaFiscal["@id"],
      };
      return state.defaultCartasCorrecaoFilters;
    },

    getDefaultHistoricoFilters(state) {
      state.defaultHistoricoFilters = {
        ...state.defaultHistoricoFilters,
        notaFiscal: state.notaFiscal["@id"],
      };
      return state.defaultHistoricoFilters;
    },

    getNotaFiscalCartaCorrecao: (state) => state.notaFiscalCartaCorrecao,
    getNotaFiscalCartaCorrecaoErrors: (state) => state.notaFiscalCartaCorrecaoErrors,
  },

  mutations: {
    setLoading(state, loading) {
      if (loading) {
        state.loading++;
      } else {
        state.loading--;
      }
    },

    setNotaFiscal(state, payload) {
      if (!payload.jsonData) {
        payload.jsonData = {};
      }
      state.notaFiscal = payload;
    },

    setNotaFiscalErrors(state, payload) {
      state.notaFiscalErrors = payload;
    },

    setDefaultItensFilters(state, payload) {
      state.defaultItensFilters = payload;
    },

    setNotaFiscalItem(state, payload) {
      state.notaFiscalItem = payload;
    },

    setNotaFiscalItemErrors(state, payload) {
      state.notaFiscalItemErrors = payload;
    },

    setNotaFiscalCartaCorrecao(state, payload) {
      state.notaFiscalCartaCorrecao = payload;
    },

    setNotaFiscalCartaCorrecaoErrors(state, payload) {
      state.notaFiscalCartaCorrecaoErrors = payload;
    },

    setDefaultCartasCorrecaoFilters(state, payload) {
      state.defaultCartasCorrecaoFilters = payload;
    },

    setDefaultHistoricoFilters(state, payload) {
      state.defaultHistoricoFilters = payload;
    },
  },

  actions: {
    async loadData(context) {
      context.commit("setLoading", true);
      await context.dispatch("fiscalStore/loadContribuintes");
      const id = new URLSearchParams(window.location.search.substring(1)).get("id");
      if (id) {
        try {
          const response = await api.get({
            apiResource: `/api/fis/notaFiscal/${id}`,
          });

          if (response.data["@id"]) {
            context.commit("setNotaFiscal", response.data);
          } else {
            console.error("Id não encontrado");
          }

          context.state.dtCartasCorrecaoKey++;
          context.state.dtItensKey++;
          context.state.dtHistoricoKey++;
        } catch (err) {
          console.error(err);
        }
      }
      context.commit("setLoading", false);
    },

    async loadNotaFiscalItem(context, id) {
      context.commit("setLoading", true);
      try {
        const response = await api.get({
          apiResource: `/api/fis/notaFiscalItem/${id}`,
        });

        if (response.data["@id"]) {
          context.commit("setNotaFiscalItem", response.data);
        } else {
          console.error("Id não encontrado");
        }
      } catch (err) {
        console.error(err);
      }
      context.commit("setLoading", false);
    },
  },
});

app.use(store);

app.mount("#app");

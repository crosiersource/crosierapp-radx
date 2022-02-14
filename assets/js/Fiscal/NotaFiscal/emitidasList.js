import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import { createStore } from "vuex";
import axios from "axios";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import Page from "./pages/emitidasList";
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
      filters: {
        xNomeEmitente: null,
        documentoEmitente: null,
      },
      contribuintes: [],
    };
  },

  mutations: {
    setLoading(state, loading) {
      if (loading) {
        state.loading++;
      } else {
        state.loading--;
      }
    },

    setEmitidasFilters(state, filters) {
      filters["dtEmissao[after]"] = filters["dtEmissao[after]"]
        ? new Date(filters["dtEmissao[after]"])
        : null;
      filters["dtEmissao[before]"] = filters["dtEmissao[before]"]
        ? new Date(filters["dtEmissao[before]"])
        : null;

      if (!state.filters.documentoEmitente && state.contribuintes[0]) {
        state.filters.documentoEmitente = state.contribuintes[0].cnpj;
      }

      state.filters = filters;
    },

    setContribuintes(state, contribuintes) {
      if (!state.filters.documentoEmitente) {
        let cnpj = contribuintes[0].cnpj;
        const ls = JSON.parse(localStorage.getItem("filters/api/fis/notaFiscal/_emitidasFilters"));
        if (ls?.documentoEmitente) {
          cnpj = ls.documentoEmitente;
        }
        state.filters.documentoEmitente = cnpj;
      }
      state.contribuintes = contribuintes;
    },
  },

  getters: {
    isLoading(state) {
      return state.loading > 0;
    },

    getEmitidasFilters(state) {
      return state.filters;
    },

    getContribuintes(state) {
      return state.contribuintes;
    },
  },

  actions: {
    async loadData(context) {
      const rs = await axios.get("/api/fis/nfeUtils/getContribuintes", {
        headers: {
          "Content-Type": "application/ld+json",
        },
        validateStatus(status) {
          return status < 500;
        },
      });
      if (rs?.data?.RESULT === "OK") {
        context.commit("setContribuintes", rs.data.DATA);
      } else {
        console.error(rs?.data?.MSG);
        this.$toast.add({
          severity: "error",
          summary: "Erro",
          detail: rs?.data?.MSG,
          life: 5000,
        });
      }
    },
  },
});

app.use(store);
app.use(ConfirmationService);

app.directive("tooltip", Tooltip);

app.mount("#app");

import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import { createStore } from "vuex";
import axios from "axios";
import Page from "./pages/nfEntradaList";
import "primeflex/primeflex.css";
import "primevue/resources/themes/saga-blue/theme.css"; // theme
import "primevue/resources/primevue.min.css"; // core css
import "primeicons/primeicons.css";

const app = createApp(Page);

app.use(PrimeVue);

app.use(ToastService);

// Create a new store instance.
const store = createStore({
  state() {
    return {
      loading: 0,
      filters: {
        xNomeEmitente: null,
        documentoDestinatario: null,
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

    setFilters(state, filters) {
      filters["dtEmissao[after]"] = filters["dtEmissao[after]"]
        ? new Date(filters["dtEmissao[after]"])
        : null;
      filters["dtEmissao[before]"] = filters["dtEmissao[before]"]
        ? new Date(filters["dtEmissao[before]"])
        : null;

      state.filters = filters;
    },

    setContribuintes(state, contribuintes) {
      state.contribuintes = contribuintes;
    },
  },

  getters: {
    isLoading(state) {
      return state.loading > 0;
    },

    getFilters(state) {
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
      console.log(rs);
      if (rs?.data?.RESULT === "OK") {
        context.commit("setContribuintes", rs.data.DATA);
        if (!context.state.filters.documentoDestinatario) {
          console.log(`setando: ${rs.data.DATA[0].cnpj}`);
          context.state.filters.documentoDestinatario = rs.data.DATA[0].cnpj;
        } else {
          console.log(`já tem : |${context.state.filters.documentoDestinatario}|`);
        }
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
import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import { createStore } from "vuex";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import { api } from "crosier-vue";
import Page from "./pages/cadeia_exibirMovimentacoes";
import "primeflex/primeflex.css";
import "primevue/resources/themes/saga-blue/theme.css"; // theme
import "primevue/resources/primevue.min.css"; // core css
import "primeicons/primeicons.css";

const app = createApp(Page);

app.use(PrimeVue, primevueOptions);

app.use(ToastService);

// Create a new store instance.
const store = createStore({
  state() {
    return {
      loading: 0,
      cadeia: {
        movimentacoes: [],
      },
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

    setCadeia(state, cadeia) {
      state.cadeia = cadeia;
    },
  },

  getters: {
    isLoading: (state) => state.loading > 0,
    getCadeia: (state) => state.cadeia,
  },

  actions: {
    async loadData(context) {
      context.commit("setLoading", true);
      const id = new URLSearchParams(window.location.search.substring(1)).get("id");
      if (id) {
        try {
          const response = await api.get({
            apiResource: `/api/fin/cadeia/${id}`,
            properties: [
              "id",
              "movimentacoes.id",
              "movimentacoes.descricao",
              "movimentacoes.status",
              "movimentacoes.descricaoMontada",
              "movimentacoes.dtVencto",
              "movimentacoes.dtVenctoEfetiva",
              "movimentacoes.valor",
              "movimentacoes.categoria.descricaoMontada",
              "movimentacoes.categoria.codigoSuper",
              "movimentacoes.carteira.codigo",
              "movimentacoes.carteira.descricaoMontada",
              "movimentacoes.modo.descricaoMontada",
              "movimentacoes.updated",
              "movimentacoes.sacado",
              "movimentacoes.cedente",
              "movimentacoes.chequeNumCheque",
              "movimentacoes.recorrente",
              "movimentacoes.parcelamento",
              "movimentacoes.transferenciaEntreCarteiras",
              "movimentacoes.movimentacaoOposta.categoria.codigo",
              "movimentacoes.movimentacaoOposta.carteira.descricaoMontada",
            ],
          });

          if (response.data["@id"]) {
            context.commit("setCadeia", response.data);
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
app.use(ConfirmationService);

app.directive("tooltip", Tooltip);

app.mount("#app");

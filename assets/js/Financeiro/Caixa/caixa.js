import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import { createStore } from "vuex";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import "primeflex/primeflex.css";
import "primevue/resources/themes/saga-blue/theme.css"; // theme
import "primevue/resources/primevue.min.css"; // core css
import "primeicons/primeicons.css";
import "crosier-vue/src/momentjs.locale.ptbr.js";
import { api } from "crosier-vue";
import moment from "moment";
import axios from "axios";
import Page from "./pages/list";

const app = createApp(Page);

app.use(PrimeVue, primevueOptions);

app.use(ToastService);

// Create a new store instance.
const store = createStore({
  state() {
    return {
      loading: 0,
      data: [],
      filters: {
        carteira: null,
        dtMoviment: null,
      },
      saldoAnterior: 0.0,
      saldoPosterior: 0.0,

      exibeDialogMovimentacao: false,
      tipoMovimentacao: null,
      movimentacao: {},
      movimentacaoErrors: {},

      exibirCampos: {},

      ultimaOperacao: {},
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

    async setFilters(state, filters) {
      state.filters = filters;
    },

    async setData(state, data) {
      state.data = data;
    },

    async setTipoMovimentacao(state, tipoMovimentacao) {
      state.tipoMovimentacao = tipoMovimentacao;
    },

    setNovaMovimentacao(state) {
      const dadosUltimaMovimentacao = JSON.parse(
        localStorage.getItem("dadosUltimaMovimentacaoCaixa") ?? "{}"
      );

      if (dadosUltimaMovimentacao) {
        this.commit("setMovimentacao", dadosUltimaMovimentacao);
      } else {
        state.movimentacao = {};
      }
      state.movimentacao.dtMoviment = state.filters.dtMoviment;
      state.movimentacao.carteira = { "@id": state.filters.carteira };
    },

    setMovimentacao(state, movimentacao) {
      movimentacao.dtMoviment = movimentacao.dtMoviment ? new Date(movimentacao.dtMoviment) : null;
      movimentacao.dtVencto = movimentacao.dtVencto ? new Date(movimentacao.dtVencto) : null;
      movimentacao.dtVenctoEfetiva = movimentacao.dtVenctoEfetiva
        ? new Date(movimentacao.dtVenctoEfetiva)
        : null;
      state.movimentacao = movimentacao;

      state.tipoMovimentacao = null;
      if (movimentacao?.tipoLancto?.id) {
        if (movimentacao.tipoLancto.id === 63) {
          state.tipoMovimentacao = "CARTAO";
        } else if (movimentacao.tipoLancto.id === 61) {
          state.tipoMovimentacao = "ENTRADA_POR_TRANSF";
        } else if (movimentacao.tipoLancto.id === 60) {
          state.tipoMovimentacao = "SANGRIA";
        } else if (movimentacao.tipoLancto.id === 20) {
          if ([151, 251].includes(movimentacao.categoria.codigo)) {
            state.tipoMovimentacao = "AJUSTE_DE_CAIXA";
          } else if (movimentacao.categoria.codigoSuper === 1) {
            state.tipoMovimentacao = "EM_ESPECIE";
          } else {
            state.tipoMovimentacao = "SAIDA";
          }
        }
      }
    },

    setMovimentacaoErrors(state, formErrors) {
      state.movimentacaoErrors = formErrors;
    },
  },

  getters: {
    isLoading: (state) => state.loading > 0,
    getFilters: (state) => state.filters,
    getData: (state) => state.data,
    getSaldoAnterior: (state) => state.saldoAnterior,
    getSaldoPosterior: (state) => state.saldoPosterior,

    getTipoMovimentacao: (state) => state.tipoMovimentacao,
    getFields: (state) => state.movimentacao,
    getFieldsErrors: (state) => state.movimentacaoErrors,
  },

  actions: {
    async doFilter(context) {
      context.commit("setLoading", true);

      context.state.exibirCampos = JSON.parse(localStorage.getItem("exibirCamposCaixa") ?? "{}");

      try {
        const caixaListFiltersLS = JSON.parse(localStorage.getItem("caixaListFilters") ?? null);

        if (caixaListFiltersLS) {
          context.state.filters = caixaListFiltersLS;
        }

        if (!context.state.filters.dtMoviment) {
          context.state.filters.dtMoviment = new Date();
        }

        context.state.filters.dtMoviment = moment(context.state.filters.dtMoviment).format();

        if (!context.state.filters.carteira) {
          const rsCarteiras = await api.get({
            apiResource: "/api/fin/carteira",
            allRows: true,
            order: { codigo: "ASC" },
            filters: { caixa: true, atual: true },
            properties: ["id", "descricaoMontada"],
          });
          context.state.filters.carteira = rsCarteiras.data["hydra:member"][0]["@id"];
        }

        const filtersComDtMovimentAjustada = { ...context.state.filters };
        filtersComDtMovimentAjustada.dtMoviment = moment(
          filtersComDtMovimentAjustada.dtMoviment
        ).format("YYYY-MM-DD");

        filtersComDtMovimentAjustada.notLike = {
          status: "ESTORNADA",
        };

        const response = await api.get({
          apiResource: "/api/fin/movimentacao",
          rows: 999999,
          order: {
            "categoria.codigo": "ASC",
            "modo.codigo": "ASC",
            valorTotal: "ASC",
          },
          filters: filtersComDtMovimentAjustada,
          properties: [
            "id",
            "tipoLancto.id",
            "descricao",
            "status",
            "descricaoMontada",
            "dtVencto",
            "dtVenctoEfetiva",
            "dtUtil",
            "valorTotalFormatted",
            "categoria.descricaoMontada",
            "categoria.codigo",
            "categoria.codigoSuper",
            "carteira.descricaoMontada",
            "modo.codigo",
            "modo.descricaoMontada",
            "updated",
            "sacado",
            "cedente",
            "chequeNumCheque",
            "recorrente",
            "parcelamento",
            "cadeia.id",
            "transferenciaEntreCarteiras",
            "movimentacaoOposta.categoria.codigo",
            "movimentacaoOposta.carteira.descricaoMontada",
          ],
        });

        const movimentacoes = response.data["hydra:member"] ?? [];

        const categoriasGrouped = {};
        movimentacoes.forEach((mov) => {
          if (categoriasGrouped[mov.categoria.codigo] === undefined) {
            categoriasGrouped[mov.categoria.codigo] = {
              categoriaDescricaoMontada: mov.categoria.descricaoMontada,
              categoriaCodigo: mov.categoria.codigo,
              categoriaCodigoSuper: mov.categoria.codigoSuper,
              totalCategoria: 0,
              rtaParaAgrupar: 1,
              movimentacoes: [],
            };
          }
          categoriasGrouped[mov.categoria.codigo].movimentacoes.push(mov);
          categoriasGrouped[mov.categoria.codigo].totalCategoria += mov.valorTotal;
        });

        context.state.data = categoriasGrouped;
        localStorage.setItem("caixaListFilters", JSON.stringify(context.state.filters));

        context.dispatch("loadSaldos");

        context.dispatch("loadUltimaOperacao");
      } catch (e) {
        console.error(e);
      }
      context.commit("setLoading", false);
    },

    async loadSaldos(context) {
      const dtMoviment = context.state.filters.dtMoviment.substr(0, 10);
      const umDiaAntes = `${moment(dtMoviment)
        .subtract(1, "days")
        .format("YYYY-MM-DD")}T00:00:00-03:00`;

      const dtMovimentF = `${moment(dtMoviment).format("YYYY-MM-DD")}T00:00:00-03:00`;

      const rsSaldos = await axios.get(
        `/api/fin/saldo?carteira=${context.state.filters.carteira}&dtSaldo[after]=${umDiaAntes}&dtSaldo[before]=${dtMovimentF}&properties[]=id&properties[]=dtSaldo&properties[]=totalRealizadas&properties[]=totalPendencias&properties[]=totalComPendentes`
      );

      const rsSaldoAnterior = rsSaldos.data["hydra:member"].find((saldo) => {
        const dtSaldo = moment(saldo.dtSaldo.substr(0, 10)).format("YYYY-MM-DD");
        const umDiaAntesF = moment(umDiaAntes).format("YYYY-MM-DD");
        return dtSaldo === umDiaAntesF;
      });
      context.state.saldoAnterior = rsSaldoAnterior?.totalRealizadas;

      const rsSaldoPosterior = rsSaldos.data["hydra:member"].find((saldo) => {
        return (
          moment(saldo.dtSaldo).format("YYYY-MM-DD") === moment(dtMoviment).format("YYYY-MM-DD")
        );
      });

      context.state.saldoPosterior = rsSaldoPosterior?.totalRealizadas;
    },

    // async loadStatus(context) {
    //   const rs = await axios.get(`/api/fin/caixaOperacao/status/${context.state.filters.carteira}`);
    //
    //   context.state.saldoAnterior = rsSaldoAnterior?.totalRealizadas;
    // },

    salvarUltimaMovimentacaoNoLocalStorage(context) {
      localStorage.setItem(
        "dadosUltimaMovimentacaoCaixa",
        JSON.stringify({
          descricao: context.state.movimentacao.descricao,
          tipoLancto: context.state.movimentacao.tipoLancto,
          categoria: context.state.movimentacao.categoria,
          carteira: context.state.movimentacao.carteira,
          carteiraDestino: context.state.movimentacao.carteiraDestino,
          modo: context.state.movimentacao.modo,
          centroCusto: context.state.movimentacao.centroCusto,
          operadoraCartao: context.state.movimentacao.operadoraCartao,
          bandeiraCartao: context.state.movimentacao.bandeiraCartao,
        })
      );
    },

    salvarExibirCampos(context) {
      localStorage.setItem("exibirCamposCaixa", JSON.stringify(context.state.exibirCampos));
    },
  },
});

app.use(store);
app.use(ConfirmationService);

app.directive("tooltip", Tooltip);

app.mount("#app");

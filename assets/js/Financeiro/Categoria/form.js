import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import { createStore } from "vuex";
import { api } from "crosier-vue";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import ConfirmationService from "primevue/confirmationservice";
import Page from "./pages/tree";
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
      categorias: [],
      fields: {},
      fieldsErrors: {},
      exibeDialog: false,
    };
  },
  getters: {
    isLoading: (state) => state.loading > 0,

    getFields: (state) => state.fields,

    getFieldsErrors: (state) => state.fieldsErrors,

    getCategorias: (state) => state.categorias,
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
      state.fields = fields;
    },

    setFieldsErrors(state, formErrors) {
      state.fieldsErrors = formErrors;
    },

    setCategorias(state, categorias) {
      state.categorias = categorias;
    },
  },

  actions: {
    async loadData(context) {
      context.commit("setLoading", true);
      try {
        const response = await api.get({
          apiResource: `/api/fin/categoria/`,
          order: { codigoOrd: "ASC" },
          allRows: true,
        });
        if (response.data["hydra:totalItems"] > 0) {
          const categorias = response.data["hydra:member"];
          const tree = [];

          // eslint-disable-next-line no-inner-declarations
          function findNode(arr, id) {
            for (let i = 0; i < arr.length; i++) {
              if (arr[i].key === id) {
                return arr[i];
              }
              if (arr[i].children && arr[i].children.length > 0) {
                const achou = findNode(arr[i].children, id);
                if (achou) {
                  return achou;
                }
              }
            }
            return false;
          }

          categorias.forEach((categ) => {
            const node = {
              key: categ.id,
              data: {
                id: categ.id,
                descricaoMontada: categ.descricaoMontadaTree,
                descricao: categ.descricao,
                codigo: categ.codigo,
                codigoSuper: categ.codigoSuper,
                codigoM: categ.codigoM,
                mascaraDoFilho: categ.mascaraDoFilho,
                pai: categ?.pai,
                qtdeFilhos: 0,
                children: [],
              },
            };
            let pai = null;
            if (!categ.pai) {
              tree.push(node);
            } else {
              pai = findNode(tree, categ.pai.id);
              if (!pai) {
                console.error(`Não achei pai pro ${node}`);
              } else {
                pai.children = pai.children ?? [];
                pai.children.push(node);
                pai.data.qtdeFilhos++;
              }
            }
          });

          context.commit("setCategorias", tree);
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

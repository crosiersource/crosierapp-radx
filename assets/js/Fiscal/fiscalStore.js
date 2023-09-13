import { api } from "crosier-vue";

export const fiscalStore = {
  namespaced: true,

  state: {
    contribuintes: [],
  },

  getters: {
    getContribuintes: (state) => state.contribuintes,
  },

  mutations: {
    setContribuintes(state, payload) {
      state.contribuintes = payload;
    },
  },

  actions: {
    async loadContribuintes(context) {
      const rs = await api.get({
        apiResource: "/api/fis/nfeUtils/getContribuintes",
      });
      context.commit("setContribuintes", rs?.data?.DATA);
    },
  },
};

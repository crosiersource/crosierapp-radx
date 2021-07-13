import Vuex from "vuex";

export const store = {
  namespaced: true,
  state() {
    return {
      selectedItems: [],
      count: 0,
    };
  },
  actions: {
    async loadSelectedRows(context, localStoragePath) {
      console.log(localStoragePath);
      const loadedSelected = await localStorage.getItem(localStoragePath);

      context.dispatch("updateSelectedRows", JSON.parse(loadedSelected).selection);
    },

    updateSelectedRows(context, selectedSet) {
      const newSelectedSet = selectedSet.map((e) => e.id);
      context.commit("setSelectedItems", newSelectedSet);
      context.commit("setCount", newSelectedSet.length);
    },
  },

  mutations: {
    setSelectedItems(state, newSelectedItems) {
      state.selectedItems = newSelectedItems;
    },
    setCount(state, newSelectedItems) {
      state.count = newSelectedItems.length;
    },
  },
  getters: {
    // eslint-disable-next-line no-unused-vars
    selectedItems(state, getters) {
      return state.selectedItems;
    },
    // eslint-disable-next-line no-unused-vars
    countSelectedItems(state, getters) {
      return state.count;
    },
  },
};

export default new Vuex.Store(store);

<template>
  <div class="container">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Produto</h3>
          </div>
          <div class="d-sm-flex flex-nowrap ml-auto">
            <a
              v-show="this.formUrl"
              type="button"
              class="btn btn-info mr-2"
              :href="this.formUrl"
              title="Novo"
            >
              <i class="fas fa-file" aria-hidden="true"></i>
            </a>
            <a
              v-show="this.listUrl"
              role="button"
              class="btn btn-outline-secondary"
              :href="this.listUrl"
              title="Listar"
            >
              <i class="fas fa-list"></i>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <TabView class="mt-3">
          <TabPanel header="Produto">
            <ProdutoForm />
          </TabPanel>
          <TabPanel :disabled="!this.produto?.id" header="Preço">
            <PrecosForm />
          </TabPanel>
        </TabView>
      </div>
    </div>
  </div>
</template>

<script>
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import { mapGetters } from "vuex";
import ProdutoForm from "./form";
import PrecosForm from "./precos";

export default {
  components: {
    ProdutoForm,
    PrecosForm,
    TabView,
    TabPanel,
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      produto: "getFields",
    }),
  },
};
</script>

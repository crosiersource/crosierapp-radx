<template>
  <ConfirmDialog />
  <Toast group="mainToast" position="bottom-right" class="mb-5" />

  <div class="container">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Produto</h3>
            <h6 v-if="this.produto?.id">{{ this.produto.descricaoMontada }}</h6>
          </div>
          <div class="d-sm-flex flex-nowrap ml-auto">
            <a type="button" class="btn btn-info mr-2" href="form" title="Novo">
              <i class="fas fa-file" aria-hidden="true"></i>
            </a>
            <a
              role="button"
              class="btn btn-outline-secondary"
              href="listComEcommerce"
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
          <TabPanel :disabled="!this.produto?.id" header="E-commerce">
            <EcommerceForm />
          </TabPanel>
        </TabView>
      </div>
    </div>
  </div>
</template>

<script>
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import ConfirmDialog from "primevue/confirmdialog";
import Toast from "primevue/toast";
import { mapGetters } from "vuex";
import ProdutoForm from "./form";
import EcommerceForm from "./form_ecommerce";

export default {
  components: {
    ProdutoForm,
    EcommerceForm,
    TabView,
    TabPanel,
    ConfirmDialog,
    Toast,
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      produto: "getFields",
    }),
  },
};
</script>

<template>
  <CrosierBlock :loading="this.loading" />
  <ConfirmDialog group="confirmDialog_crosierListS" />
  <Toast position="bottom-right" class="mb-5" />

  <div class="container">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Nota Fiscal</h3>
          </div>
          <div class="d-sm-flex flex-nowrap ml-auto">
            <a type="button" class="btn btn-info mr-2" href="form" title="Novo">
              <i class="fas fa-file" aria-hidden="true"></i>
            </a>
            <a
              role="button"
              class="btn btn-outline-secondary"
              :href="this.notaFiscal.nossaEmissao ? 'emitidas/list' : 'recebidas/list'"
              title="Listar"
            >
              <i class="fas fa-list"></i>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <TabView class="mt-3">
          <TabPanel header="Cabeçalho">
            <Cabecalho />
          </TabPanel>
          <TabPanel :disabled="!this.notaFiscal?.id" header="Itens">
            <Itens />
          </TabPanel>
          <TabPanel :disabled="!this.notaFiscal?.id" header="Transporte">
            <Transporte />
          </TabPanel>
          <TabPanel :disabled="!this.notaFiscal?.id" header="Cartas de Correção">
            <CartasCorrecao />
          </TabPanel>
          <TabPanel :disabled="!this.notaFiscal?.id" header="Duplicatas">
            <Duplicatas />
          </TabPanel>
          <TabPanel :disabled="!this.notaFiscal?.id" header="Histórico">
            <Historico />
          </TabPanel>
        </TabView>
      </div>
    </div>
  </div>
</template>

<script>
import { CrosierBlock } from "crosier-vue";
import ConfirmDialog from "primevue/confirmdialog";
import Toast from "primevue/toast";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import { mapGetters } from "vuex";
import Cabecalho from "./cabecalho";
import Itens from "./itens";
import Transporte from "./transporte";
import CartasCorrecao from "./cartasCorrecao";
import Duplicatas from "./duplicatas";
import Historico from "./historico";

export default {
  components: {
    CrosierBlock,
    ConfirmDialog,
    Toast,
    TabView,
    TabPanel,
    Cabecalho,
    Itens,
    Transporte,
    CartasCorrecao,
    Duplicatas,
    Historico,
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      notaFiscal: "getNotaFiscal",
    }),
  },
};
</script>
<style>
.p-dialog-mask.p-component-overlay.p-component-overlay-enter {
  z-index: 9999 !important;
}
</style>

<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <div class="container">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Categorias</h3>
          </div>
          <div class="d-sm-flex flex-nowrap ml-auto">
            <button
              type="button"
              class="ml-1 btn btn-outline-info btn-sm"
              title="Expandir todos"
              @click="this.expandAll"
            >
              <i class="fas fa-expand-arrows-alt"></i>
            </button>
            <button
              type="button"
              class="ml-1 btn btn-outline-info btn-sm"
              title="Recolher todos"
              @click="this.collapseAll"
            >
              <i class="fas fa-compress-arrows-alt"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="card-body">
        <TreeTable
          :value="this.categorias"
          :expandedKeys="this.expandedKeys"
          class="p-treetable-sm"
          :rowHover="true"
        >
          <Column field="descricaoMontada" header="Descrição" :expander="true">
            <template #body="r">
              <span :style="'color: ' + (r.node.data.codigoSuper === 1 ? 'blue' : 'red')">{{
                r.node.data.descricaoMontada
              }}</span>
            </template>
          </Column>
          <Column field="id" header="">
            <template class="text-right" #body="r">
              <div class="d-flex justify-content-end">
                <button
                  v-if="r.node.data.codigo.toString().length <= 9"
                  type="button"
                  class="btn btn-outline-success btn-sm"
                  title="Adicionar filho"
                  @click="this.adicionarFilho(r.node.data)"
                >
                  <i class="fas fa-plus"></i>
                </button>
                <button
                  type="button"
                  class="ml-1 btn btn-outline-primary btn-sm"
                  title="Editar registro"
                  @click="this.editar(r.node.data)"
                >
                  <i class="fas fa-wrench" aria-hidden="true"></i>
                </button>
                <button
                  v-show="r.node.data.qtdeFilhos === 0"
                  type="button"
                  class="btn btn-outline-danger btn-sm ml-1"
                  title="Deletar registro"
                  @click="this.deletar(r.node.data.id)"
                >
                  <i class="fas fa-trash" aria-hidden="true"></i>
                </button>
              </div>
            </template>
          </Column>
        </TreeTable>
      </div>
    </div>
  </div>

  <CategoriaForm @dataSaved="this.load()" />
</template>

<script>
import Toast from "primevue/toast";
import TreeTable from "primevue/treetable";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import { mapGetters, mapMutations } from "vuex";
import { api } from "crosier-vue";
import CategoriaForm from "./formDialog";

export default {
  components: {
    Toast,
    ConfirmDialog,
    TreeTable,
    Column,
    CategoriaForm,
  },

  data() {
    return {
      criarVincularFields: false,
      schemaValidator: {},
      expandedKeys: [],
    };
  },

  async mounted() {
    await this.load();
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors", "setCategorias"]),

    async load() {
      this.setLoading(true);
      await this.$store.dispatch("loadData");
      this.expandAll();
      this.setLoading(false);
    },

    adicionarFilho(data) {
      this.setFields({
        pai: data,
      });
      this.$store.state.exibeDialog = true;
    },

    editar(data) {
      this.setFields({
        id: data.id,
        codigo: data.codigoM,
        descricao: data.descricao,
      });
      this.$store.state.exibeDialog = true;
    },

    expandAll() {
      for (const node of this.categorias) {
        this.expandNode(node);
      }
      this.expandedKeys = { ...this.expandedKeys };
    },

    collapseAll() {
      this.expandedKeys = {};
    },

    expandNode(node) {
      this.expandedKeys[node.key] = true;
      if (node.children && node.children.length) {
        for (const child of node.children) {
          this.expandNode(child);
        }
      }
    },

    deletar(id) {
      this.$confirm.require({
        acceptLabel: "Sim",
        rejectLabel: "Não",
        message: "Confirmar a operação?",
        header: "Atenção!",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          try {
            const rsDelete = await api.delete(`/api/fin/categoria/${id}`);
            if (rsDelete?.status === 204) {
              this.$toast.add({
                group: "mainToast",
                severity: "success",
                summary: "Sucesso",
                detail: "Registro deletado com sucesso",
                life: 5000,
              });
              await this.load();
            } else {
              if (rsDelete?.data["hydra:description"]) {
                console.error(rsDelete?.data["hydra:description"]);
              }
              console.error(rsDelete?.statusText);
              throw new Error("Não foi possível deletar o registro");
            }
          } catch (e) {
            console.error(e);
            this.$toast.add({
              group: "mainToast",
              severity: "error",
              summary: "Erro",
              detail: "Ocorreu um erro ao efetuar a operação",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },
  },

  computed: {
    ...mapGetters({
      fields: "getFields",
      formErrors: "getFieldsErrors",
      categorias: "getCategorias",
    }),
  },
};
</script>

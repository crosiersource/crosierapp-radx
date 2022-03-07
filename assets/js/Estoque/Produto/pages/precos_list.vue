<template>
  <ConfirmDialog />

  <div class="card mt-4">
    <div class="card-body">
      <h5 class="card-title">Preços</h5>

      <DataTable
        class="p-datatable-sm p-datatable-striped"
        :value="this.produto.precos"
        dataKey="id"
        :resizableColumns="true"
        columnResizeMode="fit"
        responsiveLayout="scroll"
        :rowHover="true"
      >
        <Column field="lista.descricao" header="Nome" :sortable="true"></Column>
        <Column field="unidade.label" header="Unidade" :sortable="true"></Column>
        <Column field="margem" header="Margem">
          <template #body="r">
            <div class="text-right">
              {{
                parseFloat(r.data.margem * 100.0 ?? 0).toLocaleString("pt-BR", {
                  style: "decimal",
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2,
                  currency: "BRL",
                })
              }}%
            </div>
          </template>
        </Column>
        <Column field="precoCusto" header="Preço Custo">
          <template #body="r">
            <div class="text-right">
              {{
                parseFloat(r.data.precoCusto ?? 0).toLocaleString("pt-BR", {
                  style: "currency",
                  currency: "BRL",
                })
              }}
            </div>
          </template>
        </Column>
        <Column field="precoPrazo" header="Preço Venda">
          <template #body="r">
            <div class="text-right">
              {{
                parseFloat(r.data.precoPrazo ?? 0).toLocaleString("pt-BR", {
                  style: "currency",
                  currency: "BRL",
                })
              }}
            </div>
          </template>
        </Column>
        <Column field="atual" header="Atual">
          <template #body="r">
            <div class="text-center">
              {{ r.data.atual ? "Sim" : "Não" }}
            </div>
          </template>
        </Column>

        <Column field="updated" header="" :sortable="true">
          <template class="text-right" #body="r">
            <div class="d-flex justify-content-end">
              <a
                role="button"
                class="btn btn-primary btn-sm"
                title="Editar registro"
                @click="this.editar(r.data)"
                ><i class="fas fa-wrench" aria-hidden="true"></i
              ></a>
              <a
                role="button"
                class="btn btn-danger btn-sm ml-1"
                title="Deletar registro"
                @click="this.deletar(r.data.id)"
                ><i class="fas fa-trash" aria-hidden="true"></i
              ></a>
            </div>
            <div class="d-flex justify-content-end mt-1">
              <span
                v-if="r.data.updated"
                class="badge badge-info"
                title="Última alteração do registro"
              >
                {{ new Date(r.data.updated).toLocaleString() }}
              </span>
            </div>
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>

<script>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import { mapGetters, mapMutations } from "vuex";
import api from "crosier-vue/src/services/api";

export default {
  name: "precos_list",
  components: {
    DataTable,
    Column,
    ConfirmDialog,
  },

  methods: {
    ...mapMutations(["setLoading", "setPreco"]),

    editar(preco) {
      this.setPreco({ ...preco });
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
            const deleteUrl = `/api/est/produtoPreco/${id}`;
            const rsDelete = await api.delete(deleteUrl);
            if (!rsDelete) {
              throw new Error("rsDelete n/d");
            }
            if (rsDelete?.status === 204) {
              this.$toast.add({
                group: "mainToast",
                severity: "success",
                summary: "Sucesso",
                detail: "Registro deletado com sucesso",
                life: 5000,
              });
              await this.$store.dispatch("loadData");
              this.setPreco({});
            } else if (rsDelete?.data && rsDelete.data["hydra:description"]) {
              throw new Error(`status !== 204: ${rsDelete?.data["hydra:description"]}`);
            } else if (rsDelete?.statusText) {
              throw new Error(`status !== 204: ${rsDelete?.statusText}`);
            } else {
              throw new Error("Erro ao deletar (erro n/d, status !== 204)");
            }
          } catch (e) {
            console.error(e);
            this.$toast.add({
              group: "mainToast",
              severity: "error",
              summary: "Erro",
              detail: "Ocorreu um erro ao deletar",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },
  },

  computed: {
    ...mapGetters({ produto: "getFields" }),
  },
};
</script>

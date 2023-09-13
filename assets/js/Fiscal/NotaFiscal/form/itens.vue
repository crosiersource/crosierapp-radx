<template>
  <Item v-if="this.notaFiscal.id" />
  <div class="d-flex justify-content-end mb-1" v-if="this.notaFiscal.permiteSalvar">
    <button type="button" class="btn btn-outline-info" @click="novoItem()" title="Novo Item">
      <i class="fas fa-file" aria-hidden="true"></i>
    </button>
  </div>
  <CrosierListS
    withoutCard
    :key="this.$store.state.dtItensKey"
    v-if="this.notaFiscal.id"
    :comFiltragem="false"
    apiResource="/api/fis/notaFiscalItem/"
    ref="dt"
    dtStateName="itensList"
    filtersStoreName="itensFilters"
    :properties="[
      'id',
      'updated',
      'ordem',
      'codigo',
      'descricao',
      'cfop',
      'ncm',
      'unidade',
      'qtdeFormatted',
      'valorUnitFormatted',
      'valorDescontoFormatted',
      'valorTotalFormatted',
    ]"
  >
    <template v-slot:columns>
      <Column field="ordem" header="#" sortable />
      <Column field="codigo" header="Código" sortable />
      <Column field="descricao" header="Descrição" sortable>
        <template #body="r">
          {{ r.data.descricao }} ({{ r.data.unidade }})
          <div class="small">NCM: {{ r.data.ncm }}</div>
        </template>
      </Column>
      <Column field="cfop" header="CFOP" sortable />
      <Column field="qtde" header="Qtde" sortable />
      <Column field="valorUnit" header="Vlr Unit" sortable>
        <template #body="r">
          <div class="text-right">
            {{
              parseFloat(r.data.valorUnit ?? 0).toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL",
              })
            }}
          </div>
        </template>
      </Column>
      <Column field="valorTotal" header="Vlr Total" sortable>
        <template #body="r">
          <div v-if="r.data.valorDesconto" class="text-right text-muted">
            (-)
            {{
              parseFloat(r.data.valorDesconto ?? 0).toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL",
              })
            }}
          </div>
          <div class="text-right">
            {{
              parseFloat(r.data.valorTotal ?? 0).toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL",
              })
            }}
          </div>
        </template>
      </Column>
      <Column field="updated" header="" sortable>
        <template #body="r">
          <div class="d-flex justify-content-end">
            <button
              type="button"
              class="btn btn-primary btn-sm ml-1"
              title="Editar registro"
              @click="this.editarItem(r.data.id)"
            >
              <i class="fas fa-wrench" aria-hidden="true"></i>
            </button>
            <button
              type="button"
              class="btn btn-danger btn-sm ml-1"
              title="Deletar registro"
              v-if="this.notaFiscal.permiteSalvar"
              @click="this.$refs.dt.deletar(r.data.id)"
            >
              <i class="fas fa-trash" aria-hidden="true"></i>
            </button>
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
    </template>
  </CrosierListS>
</template>

<script>
import { mapGetters, mapMutations, mapActions } from "vuex";
import { CrosierListS } from "crosier-vue";
import Column from "primevue/column";
import Item from "./item.vue";

export default {
  components: {
    CrosierListS,
    Column,
    Item,
  },

  methods: {
    ...mapMutations(["setLoading", "setNotaFiscalItem"]),
    ...mapActions(["loadNotaFiscalItem"]),

    novoItem() {
      this.setNotaFiscalItem({});
      this.$store.state.exibirDialogItem = true;
    },

    async editarItem(id) {
      await this.loadNotaFiscalItem(id);
      this.$store.state.exibirDialogItem = true;
    },
  },

  computed: {
    ...mapGetters({
      notaFiscal: "getNotaFiscal",
    }),
  },
};
</script>

<template>
  <div class="card mt-4">
    <div class="card-body">
      <h5 class="card-title">Preços</h5>

      <CrosierListS
        :withoutCard="true"
        :comFiltragem="false"
        :comPaginador="false"
        :comExportCSV="false"
        apiResource="/api/est/produtoPreco/"
        dtStateName="produto_precos_list"
        titulo="Preços"
        :preselecao="true"
        :filtersStoreName="null"
        v-model:selection="this.produto.precos"
      >
        <template v-slot:columns>
          <Column field="lista.descricao" header="Nome" :sortable="true"></Column>
          <Column field="unidade.label" header="Unidade" :sortable="true"></Column>
          <Column field="margem" header="Margem">
            <template #body="r">
              <div class="text-right">
                {{
                  parseFloat(r.data.margem * 100.0 ?? 0).toLocaleString("pt-BR", {
                    style: "decimal",
                    minimumSignificantDigits: 2,
                    maximumSignificantDigits: 2,
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
        </template>
      </CrosierListS>
    </div>
  </div>
</template>

<script>
import Column from "primevue/column";
import { mapGetters } from "vuex";
import { CrosierListS } from "crosier-vue";

export default {
  name: "precos_list",
  components: {
    CrosierListS,
    Column,
  },

  methods: {},

  computed: {
    ...mapGetters({ produto: "getFields" }),
  },
};
</script>

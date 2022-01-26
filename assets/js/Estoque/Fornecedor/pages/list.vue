<template>
  <CrosierListS titulo="Fornecedores" apiResource="/api/est/fornecedor">
    <template v-slot:filter-fields>
      <div class="form-row">
        <CrosierInputText
          label="CPF/CNPJ"
          col="3"
          id="documento"
          v-model="this.filters.documento"
        />

        <CrosierInputText label="Nome" col="5" id="nome" v-model="this.filters.nome" />

        <CrosierDropdownBoolean
          label="Utilizado"
          col="4"
          id="utilizado"
          v-model="this.filters.utilizado"
        />
      </div>
    </template>

    <template v-slot:columns>
      <Column field="id" header="Id" :sortable="true"></Column>

      <Column field="codigo" header="Código" :sortable="true"></Column>

      <Column field="documento" header="CPF/CNPJ" :sortable="true"></Column>

      <Column field="nome" header="Nome" :sortable="true"></Column>

      <Column field="updated" header="" :sortable="true">
        <template class="text-right" #body="r">
          <div class="d-flex justify-content-end">
            <a
              role="button"
              class="btn btn-primary btn-sm"
              title="Editar registro"
              :href="'form?id=' + r.data.id"
              ><i class="fas fa-wrench" aria-hidden="true"></i
            ></a>
            <a
              role="button"
              class="btn btn-danger btn-sm ml-1"
              title="Deletar registro"
              @click="this.$refs.dt.deletar(r.data.id)"
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
    </template>
  </CrosierListS>
</template>

<script>
import { mapGetters, mapMutations } from "vuex";
import { CrosierDropdownBoolean, CrosierInputText, CrosierListS } from "crosier-vue";
import Column from "primevue/column";

export default {
  components: {
    CrosierListS,
    Column,
    CrosierDropdownBoolean,
    CrosierInputText,
  },

  methods: {
    ...mapMutations(["setLoading"]),
  },

  computed: {
    ...mapGetters({ filters: "getFilters" }),
  },
};
</script>

<style>
.dt-sm-bt {
  height: 30px !important;
  width: 30px !important;
}
</style>

<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog />

  <CrosierListS titulo="Bancos" apiResource="/api/fin/banco/" ref="dt">
    <template v-slot:filter-fields>
      <div class="form-row">
        <CrosierInputInt
          label="Código"
          col="3"
          id="codigoBanco"
          v-model="this.filters.codigoBanco"
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

      <Column field="codigoBanco" header="Código" :sortable="true"></Column>

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
import {
  CrosierDropdownBoolean,
  CrosierInputInt,
  CrosierInputText,
  CrosierListS,
} from "crosier-vue";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";

export default {
  components: {
    CrosierListS,
    Column,
    CrosierDropdownBoolean,
    CrosierInputText,
    CrosierInputInt,
    Toast,
    ConfirmDialog,
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

<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <CrosierListS
    titulo="Grupos de Movimentação"
    apiResource="/api/fin/grupo/"
    ref="dt"
    :properties="['id', 'descricao', 'carteiraPagantePadrao', 'ativo', 'updated']"
    filtrosNaSidebar
  >
    <template v-slot:filter-fields>
      <CrosierInputText label="Descrição" id="descricao" v-model="this.filters.descricao" />

      <CrosierDropdownBoolean label="Ativo" id="ativo" v-model="this.filters.ativo" />
    </template>
    <template v-slot:columns>
      <Column field="id" header="Id" sortable>
        <template #body="r">
          {{ ("00000000" + r.data.id).slice(-8) }}
        </template>
      </Column>

      <Column field="descricao" header="Descrição" sortable></Column>

      <Column field="ativo" header="Utilizado" sortable>
        <template #body="r">
          {{ r.data.ativo ? "Sim" : "Não" }}
        </template>
      </Column>

      <Column field="updated" header="" sortable>
        <template #body="r">
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
import { CrosierListS, CrosierInputText, CrosierDropdownBoolean } from "crosier-vue";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";

export default {
  components: {
    CrosierListS,
    Column,
    Toast,
    ConfirmDialog,
    CrosierInputText,
    CrosierDropdownBoolean,
  },

  methods: {
    ...mapMutations(["setLoading"]),
  },

  computed: {
    ...mapGetters({ filters: "getFilters" }),
  },
};
</script>

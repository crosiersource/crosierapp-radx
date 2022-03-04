<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <CrosierListS
    titulo="Regras de Importação"
    subtitulo="Para importações de extratos"
    apiResource="/api/fin/regraImportacaoLinha/"
    ref="dt"
  >
    <template v-slot:columns>
      <Column field="id" header="Id" :sortable="true"></Column>

      <Column field="regraRegexJava" header="Regex" :sortable="true"></Column>

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
import { CrosierListS } from "crosier-vue";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";

export default {
  components: {
    CrosierListS,
    Column,
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

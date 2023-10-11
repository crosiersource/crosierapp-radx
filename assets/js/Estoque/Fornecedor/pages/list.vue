<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <CrosierListS
    filtrosNaSidebar
    titulo="Fornecedores"
    apiResource="/api/est/fornecedor/"
    ref="dt"
    :properties="['id', 'codigo', 'updated', 'documento', 'nome', 'nomeFantasia']"
  >
    <template v-slot:filter-fields>
      <CrosierInputText label="CPF/CNPJ" id="documento" v-model="this.filters.documento" />

      <CrosierInputText label="Nome" id="nome" v-model="this.filters.nome" />

      <CrosierDropdownBoolean label="Utilizado" id="utilizado" v-model="this.filters.utilizado" />
    </template>

    <template v-slot:columns>
      <Column field="id" header="Id" sortable>
        <template #body="r">
          {{ ("00000000" + r.data.id).slice(-8) }}
        </template>
      </Column>

      <Column field="codigo" header="Código" sortable></Column>

      <Column field="documento" header="CPF/CNPJ" sortable>
        <template #body="r">
          {{
            (r.data?.documento ?? "").length === 11
              ? String(r.data?.documento ?? "").replace(
                  /(\d{3})(\d{3})(\d{3})(\d{2})/g,
                  "\$1.\$2.\$3\-\$4"
                )
              : String(r.data?.documento ?? "").replace(
                  /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
                  "$1.$2.$3/$4-$5"
                )
          }}
        </template>
      </Column>

      <Column field="nome" header="Nome/Razão Social" sortable></Column>

      <Column field="nomeFantasia" header="Nome Fantasia" sortable></Column>

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
import { CrosierDropdownBoolean, CrosierInputText, CrosierListS } from "crosier-vue";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";

export default {
  components: {
    CrosierListS,
    Column,
    CrosierDropdownBoolean,
    CrosierInputText,
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

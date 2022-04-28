<template>
  <CrosierListS titulo="Clientes" apiResource="/api/crm/cliente/" ref="dt" filtrosNaSidebar>
    <template v-slot:filter-fields>
      <div class="form-row">
        <CrosierInputText label="CPF/CNPJ" id="documento" v-model="this.filters.documento" />
      </div>
      <div class="form-row">
        <CrosierInputText label="Nome" id="nome" v-model="this.filters.nome" />
      </div>
      <div class="form-row">
        <CrosierDropdownBoolean label="Ativo" col="4" id="ativo" v-model="this.filters.ativo" />
      </div>
    </template>

    <template v-slot:columns>
      <Column field="id" header="Id" :sortable="true">
        <template #body="r">
          {{ ("00000000" + r.data.id).slice(-8) }}
        </template>
      </Column>

      <Column field="documento" header="CPF/CNPJ" :sortable="true">
        <template class="text-right" #body="r">
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

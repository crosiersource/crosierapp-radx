<template>
  <CrosierListS titulo="Carteiras" apiResource="/api/fin/carteira" :formUrl="this.formUrl">
    <template v-slot:filter-fields>
      <div class="form-row">
        <div class="col-md-2">
          <label for="id">ID</label>
          <InputText class="form-control" id="id" type="text" v-model="this.filters.id" />
        </div>
        <div class="col-md-3">
          <label for="codigo">Código</label>
          <InputText class="form-control" id="codigo" type="text" v-model="this.filters.codigo" />
        </div>
        <div class="col-md-7">
          <label for="ativo">Descrição</label>
          <Dropdown
            class="form-control"
            inputId="ativo"
            v-model="this.filters.ativo"
            :options="this.dropdownOptions.statusOptions"
            optionLabel="name"
            optionValue="value"
            placeholder="Selecione o status"
          />
        </div>
      </div>
    </template>

    <template v-slot:columns>
      <Column field="id" header="Id" :sortable="true"></Column>

      <Column field="codigo" header="Código" :sortable="true"> </Column>

      <Column field="descricao" header="Descrição" :sortable="true"> </Column>

      <Column field="updated" header="" :sortable="true">
        <template class="text-right" #body="r">
          <div class="d-flex justify-content-end">
            <a
              role="button"
              class="btn btn-primary btn-sm"
              title="Editar registro"
              :href="this.formUrl + '?id=' + r.data.id"
              ><i class="fas fa-wrench" aria-hidden="true"></i
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
import Dropdown from "primevue/dropdown";
import InputText from "primevue/inputtext";

export default {
  components: {
    CrosierListS,
    Column,
    InputText,
    Dropdown,
  },
  data() {
    return {
      formUrl: "/fin/carteira/form",
      dropdownOptions: {
        statusOptions: [
          { name: "Ativo", value: true },
          { name: "Inativo", value: false },
        ],
      },
    };
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

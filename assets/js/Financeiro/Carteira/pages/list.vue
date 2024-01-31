<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <CrosierListS
    titulo="Carteiras"
    apiResource="/api/fin/carteira/"
    ref="dt"
    filtrosNaSidebar
    :properties="[
      'id',
      'updated',
      'descricao',
      'codigo',
      'operadoraCartao.descricao',
      'dtConsolidado',
      'atual',
      'caixa',
      'caixaStatus',
      'destinoDeSangrias',
    ]"
  >
    <template v-slot:filter-fields>
      <CrosierInputText label="Descrição" id="descricao" v-model="this.filters.descricao" />

      <CrosierDropdownBoolean label="Atual" id="atual" v-model="this.filters.atual" />
    </template>

    <template v-slot:columns>
      <Column field="id" header="Id" sortable>
        <template #body="r">
          {{ ("00000000" + r.data.id).slice(-8) }}
        </template>
      </Column>

      <Column field="codigo" header="Código" sortable></Column>

      <Column field="descricao" header="Descrição" sortable>
        <template #body="r">
          {{ r.data.descricao }}
          <div v-if="r.data.operadoraCartao?.descricao">
            <span class="badge badge-info">
              Operadora de Cartão: {{ r.data.operadoraCartao?.descricao }}
            </span>
          </div>
        </template>
      </Column>

      <Column field="dtConsolidado" header="Consolidada em" sortable>
        <template #body="r">
          <div v-if="r.data.dtConsolidado">
            {{ new Date(r.data.dtConsolidado).toLocaleString().substring(0, 10) }}
          </div>
        </template>
      </Column>

      <Column field="atual" header="Atual" sortable>
        <template #body="r">
          {{ r.data.atual ? "Sim" : "Não" }}
        </template>
      </Column>

      <Column field="caixa" header="Caixa" sortable>
        <template #body="r">
          {{ r.data.caixa ? "Sim" : "Não" }}
          <div v-if="r.data.caixa">
            <span class="badge badge-info">
              {{ r.data.caixaStatus }}
            </span>
          </div>
        </template>
      </Column>

      <Column field="destinoDeSangrias" header="Dest Sangrias" sortable>
        <template #body="r">
          {{ r.data.destinoDeSangrias ? "Sim" : "Não" }}
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
import { CrosierInputText, CrosierListS, CrosierDropdownBoolean } from "crosier-vue";
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
  data() {
    return {
      dropdownOptions: {
        statusOptions: [
          { label: "Ativo", value: true },
          { label: "Inativo", value: false },
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

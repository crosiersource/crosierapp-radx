<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <CrosierListS
    titulo="Produto"
    apiResource="/api/est/produto/"
    filtrosNaSidebar
    ref="dt"
    :formUrl="null"
    :properties="[
      'id',
      'updated',
      'depto.descricaoMontada',
      'grupo.descricaoMontada',
      'subgrupo.descricaoMontada',
      'nome',
      'codigo',
      'ecommerce',
      'dtUltIntegracaoEcommerce',
      'qtdeTotalFormatted',
      'jsonData',
    ]"
  >
    <template v-slot:filter-fields>
      <div class="form-row">
        <CrosierInputText label="Código" id="codigo" v-model="this.filters.codigo" />

        <CrosierInputText label="Nome" id="nome" v-model="this.filters['like[nome]']" />

        <CrosierDropdown
          label="Status"
          id="status"
          v-model="this.filters.status"
          :options="[
            { label: 'Ativo', value: 'ATIVO' },
            { label: 'Inativo', value: 'INATIVO' },
          ]"
        />

        <CrosierDropdownBoolean
          label="Integrado ao e-commerce"
          id="ecommerce"
          v-model="this.filters.ecommerce"
        />
      </div>

      <div class="form-row">
        <CrosierDropdownEntity
          v-model="this.filters.depto"
          entity-uri="/api/est/depto"
          optionLabel="descricaoMontada"
          :orderBy="{ codigo: 'ASC' }"
          label="Depto"
          id="depto"
          @update:modelValue="this.onChangeDepto"
        />
      </div>
      <div class="form-row">
        <CrosierDropdownEntity
          ref="grupo"
          v-if="this.filters?.depto"
          v-model="this.filters.grupo"
          entity-uri="/api/est/grupo"
          optionLabel="descricaoMontada"
          :orderBy="{ codigo: 'ASC' }"
          :filters="{ depto: this.filters.depto }"
          label="Grupo"
          id="grupo"
          @update:modelValue="this.onChangeGrupo"
        />
        <div class="col-md-12" v-else>
          <div class="form-group">
            <label>Grupo</label>
            <Skeleton class="form-control" height="2rem" />
          </div>
        </div>
      </div>

      <div class="form-row">
        <CrosierDropdownEntity
          ref="subgrupo"
          v-if="this.filters?.grupo"
          v-model="this.filters.subgrupo"
          entity-uri="/api/est/subgrupo"
          optionLabel="descricaoMontada"
          :orderBy="{ codigo: 'ASC' }"
          :filters="{ grupo: this.filters.grupo }"
          label="Subgrupo"
          id="subgrupo"
        />
        <div class="col-md-12" v-else>
          <div class="form-group">
            <label>Subgrupo</label>
            <Skeleton class="form-control" height="2rem" />
          </div>
        </div>
      </div>

      <div class="form-row">
        <CrosierInputDecimal
          id="qtdeTotal_min"
          col="6"
          label="Estoque (de)..."
          v-model="this.filters['qtdeTotal[gte]']"
        />
        <CrosierInputDecimal
          id="qtdeTotal_max"
          col="6"
          label="... (até)"
          v-model="this.filters['qtdeTotal[lte]']"
        />
      </div>
    </template>

    <template v-slot:columns>
      <Column field="id" header="Id" sortable>
        <template #body="r">
          {{ ("00000000" + r.data.id).slice(-8) }}
        </template>
      </Column>

      <Column field="codigo" header="Código" sortable></Column>

      <Column field="nome" header="Nome" sortable>
        <template #body="r">
          <div style="max-width: 250px; white-space: break-spaces">{{ r.data.nome }}</div>
        </template>
      </Column>

      <Column field="depto.codigo" header="Depto/Grupo/Subgrupo" sortable>
        <template #body="r">
          {{ r.data.depto.descricaoMontada }} <br />
          {{ r.data.grupo.descricaoMontada }} <br />
          {{ r.data.subgrupo.descricaoMontada }}
        </template>
      </Column>

      <Column field="dtUltIntegracaoEcommerce" header="E-comm" sortable>
        <template #body="r">
          <div>
            {{ r.data.ecommerce ? "Sim" : "Não" }}
          </div>
          <span class="ml-1 badge badge-pill badge-info" v-if="r.data.ecommerce">
            Últ Integr: {{ new Date(r.data.dtUltIntegracaoEcommerce).toLocaleString() }}</span
          >
        </template>
      </Column>

      <Column field="qtdeTotal" header="Estoque" sortable></Column>

      <Column field="id" header="Preço Tabela" style="width: 1% !important">
        <template #body="r">
          {{
            parseFloat(r.data.jsonData.preco_tabela ?? 0).toLocaleString("pt-BR", {
              style: "currency",
              currency: "BRL",
            })
          }}
        </template>
      </Column>

      <Column field="id" header="Preço Site" style="width: 1% !important">
        <template #body="r">
          {{
            parseFloat(r.data.jsonData.preco_site ?? 0).toLocaleString("pt-BR", {
              style: "currency",
              currency: "BRL",
            })
          }}
        </template>
      </Column>

      <Column field="updated" header="" sortable>
        <template #body="r">
          <div class="d-flex justify-content-end">
            <a
              role="button"
              class="btn btn-primary btn-sm"
              title="Editar registro"
              :href="'formComEcommerce?id=' + r.data.id"
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
import {
  CrosierDropdownBoolean,
  CrosierDropdownEntity,
  CrosierDropdown,
  CrosierInputText,
  CrosierInputDecimal,
  CrosierListS,
} from "crosier-vue";
import Column from "primevue/column";
import Skeleton from "primevue/skeleton";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";

export default {
  components: {
    CrosierListS,
    Column,
    CrosierDropdownBoolean,
    CrosierDropdown,
    CrosierInputText,
    CrosierInputDecimal,
    Toast,
    ConfirmDialog,
    CrosierDropdownEntity,
    Skeleton,
  },

  methods: {
    ...mapMutations(["setLoading"]),

    onChangeDepto() {
      this.$nextTick(async () => {
        this.filters.grupo = null;
        if (this.$refs?.grupo) {
          await this.$refs.grupo.load();
        }
      });
    },

    onChangeGrupo() {
      this.$nextTick(async () => {
        if (this.$refs?.subgrupo) {
          await this.$refs.subgrupo.load();
        }
      });
    },
  },

  computed: {
    ...mapGetters({ filters: "getFilters" }),
  },
};
</script>

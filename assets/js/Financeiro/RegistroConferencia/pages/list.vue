<template>
  <Toast position="bottom-right" class="mb-5" />
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <CrosierListS
    :filtrosNaSidebar="true"
    titulo="Registros para Conferências"
    apiResource="/api/fin/registroConferencia/"
    ref="dt"
    :properties="[
      'id',
      'descricao',
      'carteira.descricaoMontada',
      'dtRegistro',
      'valorFormatted',
      'updated',
    ]"
    @beforeFilter="this.beforeFilter"
  >
    <template v-slot:filter-fields>
      <div class="form-row">
        <CrosierInputText label="Descrição" id="descricao" v-model="this.filters.descricao" />
      </div>

      <div class="form-row">
        <CrosierCalendar
          label="Dt do Registro (de)"
          col="6"
          inputClass="crsr-date"
          id="dt"
          v-model="this.filters['dtRegistro[after]']"
        />

        <CrosierCalendar
          label="(até)"
          col="6"
          inputClass="crsr-date"
          id="dt"
          v-model="this.filters['dtRegistro[before]']"
        />
      </div>

      <div class="form-row">
        <CrosierCurrency label="Valor (entre)" col="6" v-model="this.filters['valor[gte]']" />

        <CrosierCurrency label="(e)" col="6" v-model="this.filters['valor[lte]']" />
      </div>

      <div class="form-row">
        <CrosierDropdownEntity
          v-model="this.filters.carteira"
          entity-uri="/api/fin/carteira"
          optionLabel="descricaoMontada"
          :orderBy="{ codigo: 'ASC' }"
          label="Carteira"
          id="carteira"
        />
      </div>
    </template>

    <template v-slot:columns>
      <Column field="id" header="Id" :sortable="true"></Column>

      <Column field="descricao" header="Descrição" :sortable="true"></Column>

      <Column field="carteira.descricaoMontada" header="Carteira" :sortable="true"></Column>

      <Column field="dtRegistro" header="Dt do Registro" :sortable="true">
        <template #body="r">
          {{ new Date(r.data.dtRegistro).toLocaleString().substring(0, 10) }}
        </template>
      </Column>

      <Column field="valor" header="Valor" :sortable="true">
        <template #body="r">
          <div class="text-right">
            {{
              parseFloat(r.data.valor ?? 0).toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL",
              })
            }}
          </div>
        </template>
      </Column>

      <Column field="updated" header="" :sortable="true">
        <template class="text-right" #body="r">
          <div class="d-flex justify-content-end">
            <button
              type="button"
              class="btn btn-sm btn-secondary"
              @click="this.gerarProximo(r.data.id)"
              title="Gerar próximo"
            >
              <i class="fas fa-forward"></i>
            </button>
            <a
              role="button"
              class="btn btn-primary btn-sm ml-1"
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
  CrosierCalendar,
  CrosierCurrency,
  CrosierDropdownEntity,
  CrosierInputText,
  CrosierListS,
} from "crosier-vue";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import moment from "moment";
import axios from "axios";

export default {
  components: {
    CrosierListS,
    Column,
    CrosierInputText,
    Toast,
    ConfirmDialog,
    CrosierDropdownEntity,
    CrosierCalendar,
    CrosierCurrency,
  },

  methods: {
    ...mapMutations(["setLoading"]),

    moment(date) {
      return moment(date);
    },

    beforeFilter() {
      this.filters["dtRegistro[after]"] = this.filters["dtRegistro[after]"]
        ? `${moment(this.filters["dtRegistro[after]"]).format("YYYY-MM-DD")}T00:00:00-03:00`
        : null;
      this.filters["dtRegistro[before]"] = this.filters["dtRegistro[before]"]
        ? `${moment(this.filters["dtRegistro[before]"]).format("YYYY-MM-DD")}T23:59:59-03:00`
        : null;
    },

    async gerarProximo(id) {
      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          try {
            const rs = await axios.get(`/api/fin/registroConferencia/gerarProximo/${id}`, {
              validateStatus(status) {
                return status < 500;
              },
            });
            if (rs?.status === 200) {
              this.$refs.dt.doClearFilters();
              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                detail: "Registro gerado com sucesso",
                life: 5000,
              });
              this.setLoading(false);
              return;
            }
            // else
            throw new Error(rs?.data?.EXCEPTION_MSG || rs?.data?.MSG);
          } catch (e) {
            console.dir(e);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: e?.message || "Ocorreu um erro",
              life: 3000,
            });
          }
          this.setLoading(false);
        },
      });
    },
  },

  computed: {
    ...mapGetters({ filters: "getFilters" }),
  },
};
</script>

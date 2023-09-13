<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <CrosierListS
    filtrosNaSidebar
    titulo="Faturas"
    apiResource="/api/fin/fatura/"
    ref="dt"
    dtStateName="faturaPesquisaList"
    @beforeFilter="this.beforeFilter"
    :zerofillId="8"
    :properties="[
      'id',
      'updated',
      'descricao',
      'dtFatura',
      'dtVencto',
      'valorTotal',
      'sacado',
      'sacadoDocumento',
      'sacadoNome',
      'cedente',
      'cedenteDocumento',
      'cedenteNome',
      'quitada',
      'cancelada',
    ]"
  >
    <template v-slot:filter-fields>
      <CrosierInputInt label="Id" id="id" v-model="this.filters.id" />

      <CrosierInputText label="Descrição" id="descricao" v-model="this.filters.descricao" />

      <CrosierInputCpfCnpj
        label="CPF/CNPJ (Sacado)"
        id="documentoSacado"
        v-model="this.filters.documentoSacado"
      />

      <CrosierInputText label="Sacado" id="sacadoNome" v-model="this.filters.sacadoNome" />

      <CrosierInputCpfCnpj
        label="CPF/CNPJ (Cedente)"
        id="documentoCedente"
        v-model="this.filters.documentoCedente"
      />

      <CrosierInputText label="Cedente" id="cedenteNome" v-model="this.filters.cedenteNome" />

      <CrosierDropdownBoolean v-model="this.filters.quitada" label="Quitada" id="quitada" />

      <CrosierDropdownBoolean v-model="this.filters.cancelada" label="Cancelada" id="cancelada" />

      <div class="form-row">
        <CrosierCalendar
          label="Desde..."
          col="6"
          inputClass="crsr-date"
          id="dt"
          :baseZIndex="10000"
          v-model="this.filters['dtFatura[after]']"
        />

        <CrosierCalendar
          label="até..."
          col="6"
          inputClass="crsr-date"
          id="dt"
          :baseZIndex="10000"
          v-model="this.filters['dtFatura[before]']"
        />
      </div>
    </template>

    <template v-slot:columns>
      <Column field="id" header="Id" sortable>
        <template #body="r">
          {{ ("00000000" + r.data.id).slice(-8) }}
        </template>
      </Column>

      <Column field="dtFatura" header="Dt Fatura" sortable>
        <template #body="r">
          <div class="text-center">
            {{ new Date(r.data.dtFatura).toLocaleString() }}
          </div>
        </template>
      </Column>

      <Column field="descricao" header="Descrição">
        <template #body="r">
          <div>{{ r.data.descricao }}</div>
          <div class="smaller">
            Sacado: <b>{{ r.data.sacado }}</b>
          </div>
          <div class="smaller">
            Cedente: <b>{{ r.data.cedente }}</b>
          </div>
        </template>
      </Column>

      <Column field="valorTotal" header="Valor">
        <template #body="r">
          <div class="text-right">
            {{
              parseFloat(r.data.valorTotal ?? 0).toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL",
              })
            }}
          </div>
        </template>
      </Column>

      <Column field="updated" header="" sortable>
        <template #body="r">
          <div class="d-flex justify-content-end">
            <a
              role="button"
              class="btn btn-primary btn-sm ml-1"
              title="Editar registro"
              :href="'form?id=' + r.data.id"
              ><i class="fas fa-wrench" aria-hidden="true"></i
            ></a>
            <button
              type="button"
              class="btn btn-danger btn-sm ml-1"
              title="Deletar registro"
              @click="this.$refs.dt.deletar(r.data.id)"
            >
              <i class="fas fa-trash" aria-hidden="true"></i>
            </button>
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
  CrosierDropdownBoolean,
  CrosierInputInt,
  CrosierInputText,
  CrosierListS,
} from "crosier-vue";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import moment from "moment";

export default {
  components: {
    CrosierListS,
    Column,
    CrosierInputText,
    CrosierCalendar,
    CrosierInputInt,
    CrosierDropdownBoolean,
    Toast,
    ConfirmDialog,
  },

  data() {
    return {};
  },

  methods: {
    ...mapMutations(["setLoading"]),

    moment(date) {
      return moment(date);
    },

    beforeFilter() {
      this.filters["dtFatura[after]"] = this.filters["dtFatura[after]"]
        ? `${moment(this.filters["dtFatura[after]"]).format("YYYY-MM-DD")}T00:00:00-03:00`
        : null;
      this.filters["dtFatura[before]"] = this.filters["dtFatura[before]"]
        ? `${moment(this.filters["dtFatura[before]"]).format("YYYY-MM-DD")}T23:59:59-03:00`
        : null;

      const diff = moment(this.filters["dtFatura[before]"]).diff(
        moment(this.filters["dtFatura[after]"]),
        "days"
      );
      if (diff > 62) {
        this.filters["dtFatura[after]"] = `${this.moment().format("YYYY-MM")}-01T00:00:00-03:00`;
        this.filters["dtFatura[before]"] = `${this.moment()
          .endOf("month")
          .format("YYYY-MM-DD")}T23:59:59-03:00`;
        this.$toast.add({
          severity: "warn",
          summary: "Atenção",
          detail: "Não é possível pesquisar com período superior a 2 meses",
          life: 5000,
        });
      }

      this.faturasSelecionadas = null;
    },
  },

  computed: {
    ...mapGetters({ filters: "getFilters" }),
  },
};
</script>

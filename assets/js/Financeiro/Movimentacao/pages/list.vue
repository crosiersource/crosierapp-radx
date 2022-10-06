<template>
  <Toast group="mainToast" position="bottom-right" class="mb-5" />
  <ConfirmDialog group="confirmDialog_crosierListS" />

  <CrosierListS
    filtrosNaSidebar
    titulo="Movimentações"
    subtitulo="Pesquisa"
    apiResource="/api/fin/movimentacao/"
    ref="dt"
    dtStateName="movimentacaoPesquisaList"
    :ativarSelecao="true"
    v-model:selection="this.movimentacoesSelecionadas"
    @beforeFilter="this.beforeFilter"
    :zerofillId="8"
    :properties="[
      'id',
      'status',
      'categoria.codigoSuper',
      'categoria.descricaoMontada',
      'carteira.descricaoMontada',
      'modo.descricaoMontada',
      'descricaoMontada',
      'valorTotalFormatted',
      'dtVencto',
      'dtVenctoEfetiva',
      'dtUtil',
      'dtMoviment',
      'updated',
      'userUpdatedId',
      'cadeia.id',
      'sacado',
      'cedente',
      'recorrente',
      'parcelamento',
      'movimentacaoOposta.categoria.codigo',
      'movimentacaoOposta.carteira.descricaoMontada',
      'chequeNumCheque',
      'transferenciaEntreCarteiras',
    ]"
  >
    <template v-slot:headerButtons>
      <button
        type="button"
        class="btn btn-outline-dark ml-1"
        @click="this.imprimir"
        title="Imprimir listagem"
        id="btnImprimir"
        name="btnImprimir"
      >
        <i class="fas fa-print"></i>
      </button>

      <button
        type="button"
        class="btn btn-outline-dark ml-1"
        @click="this.imprimirFicha"
        title="Imprimir fichas de movimentação"
        id="btnImprimirFicha"
        name="btnImprimirFicha"
      >
        <i class="far fa-file-alt"></i>
      </button>
    </template>

    <template v-slot:filter-fields>
      <div class="form-row">
        <CrosierInputInt col="3" label="Id" id="id" v-model="this.filters.id" />

        <CrosierInputText
          col="9"
          label="Descrição"
          id="descricao"
          v-model="this.filters.descricao"
        />
      </div>

      <div class="form-row">
        <CrosierDropdown
          v-model="this.filters.status"
          label="Status"
          id="status"
          :options="[
            { label: 'ABERTA', value: 'ABERTA' },
            { label: 'REALIZADA', value: 'REALIZADA' },
          ]"
        />
      </div>

      <div class="form-row">
        <CrosierCalendar
          label="Desde..."
          col="6"
          inputClass="crsr-date"
          id="dt"
          :baseZIndex="10000"
          v-model="this.filters['dtUtil[after]']"
        />

        <CrosierCalendar
          label="até..."
          col="6"
          inputClass="crsr-date"
          id="dt"
          :baseZIndex="10000"
          v-model="this.filters['dtUtil[before]']"
        />
      </div>
      <div class="form-row">
        <CrosierCurrency
          label="Valor Total entre..."
          col="6"
          id="dt"
          v-model="this.filters['valorTotal[gte]']"
        />

        <CrosierCurrency label="e..." col="6" id="dt" v-model="this.filters['valorTotal[lte]']" />
      </div>

      <div class="form-row">
        <CrosierDropdownEntity
          v-model="this.filters.categoria"
          entity-uri="/api/fin/categoria"
          optionLabel="descricaoMontadaTree"
          :orderBy="{ codigoOrd: 'ASC' }"
          label="Categoria"
          id="categoria"
        />
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

      <div class="form-row">
        <CrosierDropdownEntity
          v-model="this.filters.modo"
          entity-uri="/api/fin/modo"
          optionLabel="descricaoMontada"
          :orderBy="{ codigo: 'ASC' }"
          label="Modo"
          id="modo"
        />
      </div>

      <div class="form-row">
        <CrosierDropdownEntity
          v-model="this.filters.centroCusto"
          entity-uri="/api/fin/centroCusto"
          optionLabel="descricaoMontada"
          :orderBy="{ codigo: 'ASC' }"
          label="Centro de Custo"
          id="centroCusto"
        />
      </div>

      <div class="form-row">
        <CrosierInputText
          v-model="this.filters.chequeNumCheque"
          label="Núm Cheque"
          id="chequeNumCheque"
        />
      </div>
    </template>

    <template v-slot:columns>
      <Column field="carteira.codigo">
        <template #header> Carteira<br />Categoria<br />Modo</template>
        <template class="text-right" #body="r">
          <b>{{ r.data.carteira.descricaoMontada }}</b
          ><br />
          {{ r.data.categoria.descricaoMontada }}<br />
          {{ r.data.modo.descricaoMontada }}
        </template>
      </Column>

      <Column field="descricao" header="Descrição" :sortable="true">
        <template class="text-right" #body="r">
          <div style="max-width: 50em; white-space: pre-wrap">
            <b><div v-html="r.data.descricaoMontada"></div></b>

            <div v-if="r.data.categoria.codigoSuper === 1 && r.data.sacado">
              <small>{{ r.data.sacado }}</small>
            </div>
            <div v-if="r.data.categoria.codigoSuper === 2 && r.data.cedente">
              <small>{{ r.data.cedente }}</small>
            </div>

            <div class="text-right w-100">
              <template v-if="r.data.chequeNumCheque">
                <span class="ml-1 badge badge-pill badge-danger"
                  ><i class="fas fa-money-check-alt"></i> Cheque</span
                >
              </template>

              <template v-if="r.data.recorrente">
                <span class="ml-1 badge badge-pill badge-info"
                  ><i class="fas fa-redo"></i> Recorrente</span
                >
              </template>

              <template v-if="r.data.parcelamento">
                <span class="ml-1 badge badge-pill badge-info"
                  ><i class="fas fa-align-justify"></i> Parcelamento</span
                >
              </template>

              <template v-if="r.data?.cadeia?.id && !r.data.recorrente && !r.data.parcelamento">
                <a
                  class="ml-1 badge badge-pill badge-success"
                  :href="'/v/fin/cadeia/exibirMovimentacoes?id=' + r.data?.cadeia?.id"
                  target="_blank"
                  style="text-decoration: none; color: white"
                  ><i class="fas fa-link"></i> Em cadeia</a
                >
              </template>

              <span
                v-if="
                  r.data.transferenciaEntreCarteiras &&
                  r.data.movimentacaoOposta &&
                  r.data.movimentacaoOposta.categoria
                "
                class="ml-1 badge badge-pill badge-secondary"
              >
                <span v-if="r.data?.movimentacaoOposta?.categoria?.codigo === 199"
                  ><i class="fas fa-sign-out-alt"></i> Para:
                </span>
                <span v-if="r.data?.movimentacaoOposta?.categoria?.codigo === 299"
                  ><i class="fas fa-sign-out-alt"></i> De:
                </span>
                {{ r.data.movimentacaoOposta.carteira.descricaoMontada }}
              </span>
            </div>
          </div>
        </template>
      </Column>

      <Column field="dtUtil" header="Data" :sortable="true">
        <template #body="r">
          <div
            class="text-center"
            :title="'Dt Vencto: ' + new Date(r.data.dtVencto).toLocaleString().substring(0, 10)"
          >
            {{ new Date(r.data.dtUtil).toLocaleString().substring(0, 10) }}
            <div class="clearfix"></div>
            <span
              v-if="r.data.status === 'REALIZADA'"
              :class="
                'text-center badge badge-pill badge-' +
                (r.data.categoria.codigoSuper === 1 ? 'success' : 'danger')
              "
              style="width: 82px"
            >
              <i class="fas fa-check-double" title="Movimentação realizada"></i> Realizada</span
            >

            <span
              v-if="r.data.status === 'ABERTA'"
              class="text-center badge badge-pill badge-info"
              style="width: 82px"
            >
              <i class="fas fa-hourglass-half" title="Movimentação aberta"></i> Aberta</span
            >

            <span
              v-if="r.data.status === 'ESTORNADA'"
              class="text-center badge badge-pill badge-dark"
              style="width: 82px"
            >
              <i class="fas fa-hourglass-half" title="Movimentação estornada"></i> Estornada</span
            >
          </div>
        </template>
      </Column>

      <Column field="valorTotal" header="Valor" :sortable="true">
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

      <Column field="updated" header="" :sortable="true">
        <template class="text-right" #body="r">
          <div class="d-flex justify-content-end">
            <a
              v-if="r.data.status === 'ABERTA'"
              role="button"
              class="btn btn-warning btn-sm"
              :href="'/v/fin/movimentacao/aPagarReceber/form?rPagamento=S&id=' + r.data.id"
              title="Registro de Pagamento"
            >
              <i class="fas fa-dollar-sign"></i
            ></a>

            <a
              role="button"
              class="btn btn-primary btn-sm ml-1"
              title="Editar registro"
              :href="'/v/fin/movimentacao/aPagarReceber/form?id=' + r.data.id"
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
  CrosierCurrency,
  CrosierDropdown,
  CrosierInputInt,
  CrosierInputText,
  CrosierDropdownEntity,
  CrosierListS,
} from "crosier-vue";
import Column from "primevue/column";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import moment from "moment";
import printJS from "print-js";
import axios from "axios";

export default {
  components: {
    CrosierListS,
    Column,
    CrosierInputText,
    CrosierCalendar,
    CrosierInputInt,
    CrosierDropdownEntity,
    CrosierDropdown,
    Toast,
    ConfirmDialog,
    CrosierCurrency,
  },
  
  data() {
    return {
      movimentacoesSelecionadas: null,
    };
  },

  methods: {
    ...mapMutations(["setLoading"]),

    moment(date) {
      return moment(date);
    },

    beforeFilter() {
      this.filters["dtUtil[after]"] = this.filters["dtUtil[after]"]
        ? `${moment(this.filters["dtUtil[after]"]).format("YYYY-MM-DD")}T00:00:00-03:00`
        : null;
      this.filters["dtUtil[before]"] = this.filters["dtUtil[before]"]
        ? `${moment(this.filters["dtUtil[before]"]).format("YYYY-MM-DD")}T23:59:59-03:00`
        : null;

      const diff = moment(this.filters["dtUtil[before]"]).diff(
        moment(this.filters["dtUtil[after]"]),
        "days"
      );
      if (diff > 62) {
        this.filters["dtUtil[after]"] = `${this.moment().format("YYYY-MM")}-01T00:00:00-03:00`;
        this.filters["dtUtil[before]"] = `${this.moment()
          .endOf("month")
          .format("YYYY-MM-DD")}T23:59:59-03:00`;
        this.$toast.add({
          severity: "warn",
          summary: "Atenção",
          detail: "Não é possível pesquisar com período superior a 2 meses",
          life: 5000,
        });
      }

      this.movimentacoesSelecionadas = null;
    },

    async imprimir() {
      this.setLoading(true);
      const pdf = await axios.post("/fin/movimentacao/aPagarReceber/rel", {
        tableData: JSON.stringify(this.tableData),
        filters: JSON.stringify(this.filters),
        somatorios: JSON.stringify(Object.fromEntries(this.somatorios)),
        totalGeral: this.totalGeral,
      });
      printJS({
        printable: pdf.data,
        type: "pdf",
        base64: true,
        targetStyles: "*",
      });
      this.setLoading(false);
    },

    async imprimirFicha() {
      if (!this.movimentacoesSelecionadas || this.movimentacoesSelecionadas.length < 1) {
        this.$toast.add({
          severity: "warn",
          summary: "Atenção",
          detail: "Nenhuma movimentação selecionada",
          life: 5000,
        });
        return;
      }
      this.setLoading(true);
      const pdf = await axios.post("/fin/movimentacao/aPagarReceber/fichaMovimentacao", {
        movsSelecionadas: JSON.stringify(this.movimentacoesSelecionadas),
      });
      printJS({
        printable: pdf.data,
        type: "pdf",
        base64: true,
        targetStyles: "*",
      });
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({ filters: "getFilters" }),
  },
};
</script>

<template>
  <header>
    <table width="100%">
      <tbody>
        <tr>
          <td style="vertical-align: top">
            <img src="" width="200" />
          </td>
          <td style="text-align: right">
            <span style="font-size: x-large; font-weight: bolder">Contas a Pagar</span><br />
            <span style="font-size: small">Per&iacute;odo: {{ periodo }}</span
            ><br />
            <span style="font-size: x-small"
              ><i>Impresso em: {{ moment().format("DD/MM/YYYY") }}</i></span
            >
          </td>
        </tr>
      </tbody>
    </table>
  </header>

  <!-- Wrap the content of your PDF inside a main tag -->
  <main>
    <DataTable
      rowGroupMode="subheader"
      groupRowsBy="dtVenctoEfetiva"
      stateStorage="local"
      class="p-datatable-sm p-datatable-striped"
      :stateKey="this.dataTableStateKey"
      :value="tableData"
      :totalRecords="totalRecords"
      :lazy="true"
      :paginator="false"
      @page="doFilter($event)"
      @sort="doFilter($event)"
      :multiSortMeta="multiSortMeta"
      :removable-sort="true"
      v-model:selection="this.selection"
      @update:selection="this.onUpdateSelection($event)"
      selectionMode="multiple"
      :metaKeySelection="false"
      dataKey="id"
      @rowSelect="this.onRowSelect"
      @rowUnselect="this.onRowUnselect"
      :resizableColumns="true"
      columnResizeMode="fit"
      responsiveLayout="scroll"
      :first="firstRecordIndex"
      ref="dt"
      :rowHover="true"
    >
      <Column field="id">
        <template #header>
          <Checkbox
            :binary="true"
            @change="this.tudoSelecionadoClick()"
            v-model="this.tudoSelecionado"
            onIcon="pi pi-check"
            offIcon="pi pi-times"
          />&nbsp; Id
        </template>
        <template #body="r">
          {{ ("0".repeat(8) + r.data.id).slice(-8) }}
        </template>
      </Column>

      <Column field="carteira.codigo">
        <template #header> Carteira<br />Categoria<br />Modo</template>
        <template class="text-right" #body="r">
          <b>{{ r.data.carteira.descricaoMontada }}</b
          ><br />
          {{ r.data.categoria.descricaoMontada }}<br />
          {{ r.data.modo.descricaoMontada }}
        </template>
      </Column>

      <Column field="descricao" header="Descrição">
        <template class="text-right" #body="r">
          <div style="max-width: 50em; white-space: pre-wrap">
            <b>{{ r.data.descricaoMontada }}</b>

            <div v-if="r.data.categoria.codigoSuper === 1 && r.data.sacado">
              <small>{{ r.data.sacado }}</small>
            </div>
            <div v-if="r.data.categoria.codigoSuper === 2 && r.data.cedente">
              <small>{{ r.data.sacado }}</small>
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

              <template v-if="r.data?.cadeia?.id">
                <a
                  class="ml-1 badge badge-pill badge-success"
                  :href="'/fin/movimentacao/listCadeia/' + r.data?.cadeia?.id"
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

      <!-- Não sei pq se colocar a dtVenctoEfetiva ele não renderiza a coluna -->
      <Column field="dtVencto" header="Dt Vencto">
        <template #body="r">
          <div
            class="text-center"
            :title="'Dt Vencto: ' + new Date(r.data.dtVencto).toLocaleString().substring(0, 10)"
          >
            {{ new Date(r.data.dtVenctoEfetiva).toLocaleString().substring(0, 10) }}
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

            <span v-else class="text-center badge badge-pill badge-info" style="width: 82px">
              '<i class="fas fa-hourglass-half" title="Movimentação abera"></i> Aberta</span
            >
          </div>
        </template>
      </Column>

      <Column field="valor" header="Valor">
        <template #body="r">
          <div
            class="text-right"
            :style="
              'font-weight: bolder; color: ' + (r.data.categoria.codigoSuper === 1 ? 'blue' : 'red')
            "
          >
            {{
              parseFloat(r.data.valor ?? 0).toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL",
              })
            }}
          </div>
        </template>
      </Column>

      <template #groupheader="r">
        <h4>{{ new Date(r.data.dtVenctoEfetiva).toLocaleString().substring(0, 10) }}</h4>
      </template>

      <template #groupfooter="r">
        <td colspan="4">
          <div class="text-right">Total:</div>
        </td>
        <td
          class="text-right"
          :style="
            'font-weight: bolder; color: ' +
            (this.somatorios.get(r.data.dtVenctoEfetiva) >= 0 ? 'blue' : 'red')
          "
        >
          {{
            parseFloat(this.somatorios.get(r.data.dtVenctoEfetiva)).toLocaleString("pt-BR", {
              style: "currency",
              currency: "BRL",
            })
          }}
        </td>
      </template>

      <template #footer v-if="this.somatorios.size > 1">
        <div class="h5 text-right">
          Total Geral:
          <span
            class="text-right"
            :style="'font-weight: bolder; color: ' + (this.totalGeral >= 0 ? 'blue' : 'red')"
          >
            {{
              parseFloat(this.totalGeral).toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL",
              })
            }}
          </span>
        </div>
      </template>
    </DataTable>
  </main>
</template>
<script>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import moment from "moment";
import { api } from "crosier-vue";

export default {
  name: "rel_aPagarReceber",

  components: {
    DataTable,
    Column,
  },

  data() {
    return {
      tableData: null,
      somatorios: new Map(),
      totalGeral: 0,
      filters: null,
      apiResource: "/api/fin/movimentacao",
    };
  },

  async mounted() {
    await this.doFilter();
  },

  methods: {
    moment(date) {
      return moment(date);
    },

    async doFilter() {
      this.filters = JSON.parse(
        new URLSearchParams(window.location.search.substring(1)).get("filters")
      );

      this.filters.status = "ABERTA";

      this.filters["dtVenctoEfetiva[after]"] = this.filters["dtVenctoEfetiva[after]"]
        ? `${moment(this.filters["dtVenctoEfetiva[after]"]).format("YYYY-MM-DD")}T00:00:00-03:00`
        : null;
      this.filters["dtVenctoEfetiva[before]"] = this.filters["dtVenctoEfetiva[before]"]
        ? `${moment(this.filters["dtVenctoEfetiva[before]"]).format("YYYY-MM-DD")}T23:59:59-03:00`
        : null;

      const response = await api.get({
        apiResource: this.apiResource,
        allRows: true,
        order: { dtVenctoEfetiva: "ASC", "categoria.codigoSuper": "ASC", valor: "ASC" },
        filters: this.filters,
        defaultFilters: this.defaultFilters,
        properties: [
          "id",
          "descricao",
          "status",
          "descricaoMontada",
          "dtVencto",
          "dtVenctoEfetiva",
          "valorFormatted",
          "categoria.descricaoMontada",
          "categoria.codigoSuper",
          "carteira.descricaoMontada",
          "modo.descricaoMontada",
          "updated",
          "sacado",
          "cedente",
          "chequeNumCheque",
          "recorrente",
          "parcelamento",
          "cadeia.id",
          "transferenciaEntreCarteiras",
          "movimentacaoOposta.categoria.codigo",
          "movimentacaoOposta.carteira.descricaoMontada",
        ],
      });

      this.totalRecords = response.data["hydra:totalItems"];
      this.tableData = response.data["hydra:member"];

      this.somatorios = new Map();

      this.tableData.forEach((m) => {
        const total = this.somatorios.get(m.dtVenctoEfetiva) || 0;
        const valor = m.categoria.codigoSuper === 1 ? m.valor : -m.valor;
        this.somatorios.set(m.dtVenctoEfetiva, total + valor);
      });

      this.tableData.forEach((m) => {
        this.totalGeral += m.categoria.codigoSuper === 1 ? m.valor : -m.valor;
      });

      this.totalRecords = response.data["hydra:totalItems"];
      this.tableData = response.data["hydra:member"];

      console.log(document.documentElement.outerHTML);
      await new Promise((r) => setTimeout(r, 8000));
      window.print();
    },
  },
};
</script>
<style>
/**
    Set the margins of the page to 0, so the footer and the header
    can be of the full height and width !
 **/

* {
  padding: 0;
  margin: 0;
}

@page {
  margin: 0 0;
}

/** Define now the real margins of every page in the PDF **/
body {
  margin: 3.5cm 1cm 1.5cm;
  max-width: 21cm;
  font-size: small;
  font-family: "Ubuntu", sans-serif;
}

/** Define the header rules **/
header {
  position: fixed;
  max-width: 21cm;
  top: 1cm;
  left: 1cm;
  right: 1cm;
  height: 3cm;
}

/** Define the footer rules **/
footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2cm;
}

table {
  border-spacing: 0;
  border-collapse: separate;
  width: 100%;
  font-size: 11px;
  line-height: 0.35cm;
}

td {
  padding: 1px;
}
</style>

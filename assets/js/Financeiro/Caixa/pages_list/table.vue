<template>
  <div
    class="p-datatable p-component p-datatable-hoverable-rows p-datatable-resizable p-datatable-resizable-fit p-datatable-responsive-scroll p-datatable-sm p-datatable-striped"
  >
    <div class="p-datatable-wrapper" style="overflow: auto">
      <table
        role="table"
        class="p-datatable-table p-datatable-resizable-table p-datatable-resizable-table-fit"
      >
        <thead>
          <th>#</th>
          <th>Categoria</th>
          <th>Descrição</th>
          <th>Dt/Hr</th>
          <th>Valor</th>
          <th>Saldo</th>
        </thead>
        <tbody class="p-datatable-tbody" role="rowgroup">
          <tr>
            <td colspan="6" class="text-right"><strong>R$ 123.456,78</strong></td>
          </tr>
          <tr class="p-selectable-row" tabindex="-1" role="row" aria-selected="false">
            <td role="cell">00010738</td>
            <td role="cell">1.99 - TRANSFERÊNCIA DE CONTA</td>
            <td role="cell">
              <div style="max-width: 50em; white-space: pre-wrap">
                <b>RECEB. PROCEDIMENTO(S) - PAGTO DIRETO AO PROFISSIONAL</b>
                <div><small>052.421.059-41 - CARLOS EDUARDO PAULUK</small></div>
              </div>
            </td>
            <td role="cell" style="width: 1% !important; text-align: center">12/12/2023</td>
            <td role="cell" style="width: 100px !important">
              <div class="text-right" style="font-weight: bolder; color: blue">
                R$&nbsp;1.190,00
              </div>
            </td>
            <td role="cell" style="width: 1% !important">
              <div class="text-right" style="font-weight: bolder; color: blue">R$ 1.190,00</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapMutations } from "vuex";
import moment from "moment";

export default {
  name: "table",

  data() {
    return {
      saldoAnterior: 0,
      saldoFinal: 0,
    };
  },

  methods: {
    ...mapMutations(["setLoading"]),

    moment(date) {
      return moment(date);
    },

    /**
     * Pode pegar o saldo através de um Date ou da string 'ANTERIOR' para o saldo anterior.
     *
     * @param d
     * @returns {*|number|null}
     */
    getSaldo(d) {
      let saldo = null;
      if (d === "ANTERIOR") {
        if (this.tableData && this.tableData[0] && this.tableData[0].dtUtil) {
          saldo = this.saldos.find((e) => {
            return (
              this.moment(e.dtSaldo).format("YYYY-MM-DD") ===
              this.moment(this.tableData[0].dtUtil).subtract(1, "days").format("YYYY-MM-DD")
            );
          });
        }
      } else {
        saldo = this.saldos.find(
          (e) => this.moment(e.dtSaldo).format("YYYY-MM-DD") === this.moment(d).format("YYYY-MM-DD")
        );
      }
      return saldo?.totalRealizadas ?? 0;
    },

    getSaldoFormatted(saldo) {
      const valor = this.getSaldo(saldo);
      return this.getValorFormatted(valor);
    },

    getValorFormatted(valor) {
      return parseFloat(valor).toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL",
      });
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      filters: "getFilters",
    }),

    exibeSaldos() {
      return this.filters?.carteirasIds?.length === 1;
    },
  },
};
</script>

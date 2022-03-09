<template>
  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <div>
          <h4 class="card-title mb-0">Vendas</h4>
          <div class="small text-medium-emphasis">Últimos 12 meses</div>
        </div>
        <div
          class="btn-toolbar d-none d-md-block"
          role="toolbar"
          aria-label="Toolbar with buttons"
        ></div>
      </div>
      <div class="c-chart-wrapper">
        <Chart type="line" :data="lineStylesData" :options="basicOptions" style="height: 250px" />
      </div>
    </div>
    <div class="card-footer" v-if="resultadosGerais">
      <div class="row row-cols-1 row-cols-md-3 text-center">
        <div class="col mb-sm-2 mb-0">
          <div class="text-medium-emphasis">Total</div>
          <div class="fw-semibold h4">
            <Vue3autocounter
              :startAmount="0"
              :endAmount="resultadosGerais.totalGeral"
              :duration="4"
              prefix="R$ "
              separator="."
              decimalSeparator=","
              :decimals="2"
              :autoinit="true"
            />
          </div>
          <div class="progress progress-thin mt-2">
            <div
              class="progress-bar"
              role="progressbar"
              style="width: 40%"
              aria-valuenow="40"
              aria-valuemin="0"
              aria-valuemax="100"
            ></div>
          </div>
        </div>

        <div class="col mb-sm-2 mb-0">
          <div class="text-medium-emphasis">Qtde Vendas</div>
          <div class="fw-semibold h4">
            <Vue3autocounter
              :startAmount="0"
              :endAmount="resultadosGerais.qtdeVendas"
              :duration="4"
              separator="."
              :autoinit="true"
            />
          </div>
          <div class="progress progress-thin mt-2">
            <div
              class="progress-bar bg-success"
              role="progressbar"
              style="width: 40%"
              aria-valuenow="40"
              aria-valuemin="0"
              aria-valuemax="100"
            ></div>
          </div>
        </div>

        <div class="col mb-sm-2 mb-0">
          <div class="text-medium-emphasis">Perguntas/Respostas</div>
          <div class="fw-semibold h4">
            <Vue3autocounter
              :startAmount="0"
              :endAmount="resultadosGerais.qtdePerguntas"
              :duration="3"
              separator="."
              :autoinit="true"
            />
          </div>
          <div class="progress progress-thin mt-2">
            <div
              class="progress-bar bg-warning"
              role="progressbar"
              style="width: 60%"
              aria-valuenow="60"
              aria-valuemin="0"
              aria-valuemax="100"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Chart from "primevue/chart";
import { api } from "crosier-vue";
import { mapMutations } from "vuex";
import Vue3autocounter from "vue3-autocounter";
import moment from "moment";

export default {
  name: "graficoVendas",

  components: {
    Chart,
    Vue3autocounter,
  },

  data() {
    return {
      resultadosGerais: null,
      lineStylesData: {
        labels: [],
        datasets: [
          {
            label: "Totais",
            data: [],
            fill: true,
            borderColor: "#FFA726",
            tension: 0.4,
            backgroundColor: "rgba(255,167,38,0.2)",
          },
        ],
      },
      basicOptions: {
        maintainAspectRatio: false,
        responsive: true,
        hoverMode: "index",
        stacked: false,
        plugins: {
          legend: {
            labels: {
              color: "#495057",
            },
          },
        },
        scales: {
          x: {
            ticks: {
              color: "#495057",
            },
            grid: {
              color: "#ebedef",
            },
          },
          y: {
            ticks: {
              color: "#495057",
            },
            grid: {
              color: "#ebedef",
            },
          },
        },
        animations: {
          tension: {
            duration: 1000,
            easing: "linear",
            from: 1,
            to: 0,
          },
        },
      },
    };
  },

  async mounted() {
    this.setLoading(true);

    const rs = await api.get({
      apiResource: "/api/dashboard/tray-e-ml/totaisDeVendasUltimos12Meses",
    });

    console.log(rs);

    const labels = rs.data.DATA.map((e) => {
      const dtf = `${e.mesano}-01T00:00:00-03:00`;
      console.log(dtf);
      return this.moment(dtf).format("MMM/YY");
    });

    const valores = rs.data.DATA.map((e) => e.valor_total);

    this.lineStylesData.labels = labels;
    this.lineStylesData.datasets[0].data = valores;

    const rsResultadosGerais = await api.get({
      apiResource: "/api/dashboard/totalizacoesGerais",
    });
    this.resultadosGerais = rsResultadosGerais.data.DATA;

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading"]),

    moment(date) {
      moment.locale("pt-br");
      return moment(date);
    },
  },
};
</script>

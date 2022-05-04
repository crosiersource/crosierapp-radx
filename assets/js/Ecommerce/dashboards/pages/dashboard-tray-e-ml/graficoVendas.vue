<template>
  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <div>
          <h4 class="card-title mb-0">Vendas</h4>
        </div>
        <div class="btn-toolbar d-none d-md-block" role="toolbar" aria-label="Toolbar with buttons">
          <CrosierCalendar
            label="Período"
            v-model="this.filters.periodo"
            range
            comBotoesPeriodo
            @update:modelValue="this.mudouPeriodo"
          />
        </div>
      </div>
      <div class="c-chart-wrapper">
        <Chart type="line" :data="lineStylesData" :options="basicOptions" style="height: 250px" />
      </div>
    </div>
    <div class="card-footer" v-if="resultadosGerais" :key="this.key_resultadosGerais">
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
import { api, CrosierCalendar } from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import Vue3autocounter from "vue3-autocounter";
import moment from "moment";

export default {
  name: "graficoVendas",

  components: {
    Chart,
    Vue3autocounter,
    CrosierCalendar,
  },

  data() {
    return {
      resultadosGerais: null,
      key_resultadosGerais: 1,
      lineStylesData: {
        labels: [],
        datasets: null,
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

    if (!this.filters.periodo) {
      this.filters.periodo = [new Date(this.moment().subtract(12, "months")), new Date()];
    }

    const lsFilters = localStorage.getItem("dashboard-tray-e-ml_graficoVendas");
    if (lsFilters) {
      this.setFilters(JSON.parse(lsFilters));
    }

    await this.doFilter();

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFilters"]),

    moment(date) {
      moment.locale("pt-br");
      return moment(date);
    },

    async mudouPeriodo() {
      this.$emit("mudouPeriodo");
      await this.doFilter();
    },

    async doFilter() {
      this.setLoading(true);

      localStorage.setItem("dashboard-tray-e-ml_graficoVendas", JSON.stringify(this.filters));

      const rs = await api.get({
        apiResource: "/api/dashboard/tray-e-ml/totaisDeVendasPorPeriodo",
        filters: this.filters,
      });

      console.log(rs);

      const labels = rs.data.DATA.map((e) => {
        const ehMesano = e.dt.length === 7;
        const dtf = ehMesano ? `${e.dt}-01T00:00:00-03:00` : `${e.dt}T00:00:00-03:00`;
        return this.moment(dtf).format(ehMesano ? "MMM/YY" : "DD/MMM");
      });

      const dsValores = rs.data.DATA.map((e) => e.valor_total);
      const dsSomatorios = rs.data.DATA.map((e) => e.somatorio);
      const dsProjecoes = rs.data.DATA.map((e) => e.projecao);
      const dsMeta = rs.data.DATA.map((e) => e.meta);
      console.log(dsMeta);

      this.lineStylesData.labels = labels;

      const ehMesano = rs.data.DATA[0].dt.length === 7;

      if (ehMesano) {
        this.lineStylesData.datasets = [
          {
            label: "Totais",
            data: [],
            fill: true,
            borderColor: "#FFA726",
            tension: 0.4,
            backgroundColor: "rgba(255,167,38,0.2)",
          },
        ];
        this.lineStylesData.datasets[0].data = dsValores;
      } else {
        this.lineStylesData.datasets = [
          {
            label: "Totais",
            data: [],
            fill: true,
            borderColor: "#FFA726",
            tension: 0.4,
            backgroundColor: "rgba(255,167,38,0.2)",
          },
          {
            label: "Somatório",
            data: [],
            fill: true,
            borderColor: "#00FFFF",
            tension: 0.4,
            backgroundColor: "rgba(0,255,255,0.2)",
          },
          {
            label: "Projeção",
            data: [],
            fill: true,
            borderColor: "#006400",
            tension: 0.4,
            backgroundColor: "rgba(0,100,0,0.2)",
          },
          {
            label: "Meta",
            data: [],
            fill: true,
            borderColor: "#FF0000",
            tension: 0.4,
            backgroundColor: "rgba(255,0,0,0.2)",
          },
        ];
        this.lineStylesData.datasets[0].data = dsValores;
        this.lineStylesData.datasets[1].data = dsSomatorios;
        this.lineStylesData.datasets[2].data = dsProjecoes;
        this.lineStylesData.datasets[3].data = dsMeta;
      }

      const rsResultadosGerais = await api.get({
        apiResource: "/api/dashboard/totalizacoesGerais",
        filters: this.filters,
      });
      this.resultadosGerais = rsResultadosGerais.data.DATA;
      this.key_resultadosGerais++;
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      filters: "getFilters",
    }),
  },
};
</script>

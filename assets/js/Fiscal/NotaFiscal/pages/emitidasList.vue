<template>
  <ConfirmDialog />
  <Toast class="mt-4" />
  <CrosierListS
    titulo="Emitidas"
    apiResource="/api/fis/notaFiscal"
    formUrl="/v/fis/notaFiscal/form"
    @beforeFilter="this.beforeFilter"
    v-model:selection="this.selection"
    filtersStoreName="emitidasFilters"
    ativarSelecao
    ref="dt"
    :properties="[
      'id',
      'updated',
      'chaveAcesso',
      'documentoDestinatario',
      'documentoEmitente',
      'dtEmissao',
      'nsu',
      'numero',
      'serie',
      'resumo',
      'valorTotalFormatted',
      'xNomeEmitente',
      'xNomeDestinatario',
      'cStat',
      'xMotivo',
      'cStatLote',
      'xMotivoLote',
      'naturezaOperacao',
      'possuiXml',
    ]"
  >
    <template v-slot:headerButtons>
      <button type="button" class="btn btn-outline-primary ml-1" @click="this.downloadXMLs">
        <i class="fas fa-file-download"></i> Download XMLs
      </button>
    </template>

    <template v-slot:filter-fields>
      <div class="form-row">
        <CrosierDropdown
          label="Emitente"
          col="3"
          id="emitente"
          :options="this.contribuintes"
          optionLabel="empresa"
          optionValue="cnpj"
          :showClear="false"
          v-model="this.filters.documentoEmitente"
        />

        <CrosierInputInt label="Número" col="1" id="codigo" v-model="this.filters.numero" />

        <CrosierInputInt label="Série" col="1" id="serie" v-model="this.filters.serie" />

        <CrosierInputText
          label="CPF/CNPJ Destinatário"
          col="3"
          id="documentoDestinatario"
          v-model="this.filters.documentoDestinatario"
        />

        <CrosierInputText
          label="Nome Destinatário"
          col="4"
          id="xNomeDestinatario"
          v-model="this.filters.xNomeDestinatario"
        />
      </div>

      <div class="form-row">
        <CrosierInputText
          label="Chave"
          col="3"
          id="chaveAcesso"
          v-model="this.filters.chaveAcesso"
        />

        <CrosierCalendar
          label="Dt Emissão (de)"
          col="3"
          inputClass="crsr-date"
          id="dt"
          v-model="this.filters['dtEmissao[after]']"
        />

        <CrosierCalendar
          label="Dt Emissão (até)"
          col="3"
          inputClass="crsr-date"
          id="dt"
          v-model="this.filters['dtEmissao[before]']"
        />

        <CrosierCurrency label="Valor" col="3" id="valor" v-model="this.filters.valorTotal" />
      </div>

      <div class="form-row">
        <CrosierInputText
          label="Natureza da Operação"
          id="naturezaOperacao"
          v-model="this.filters.naturezaOperacao"
          col="6"
        />

        <CrosierDropdown
          label="Finalidade"
          col="6"
          id="finalidadeNf"
          :options="[
            { label: 'Normal', value: 'NORMAL' },
            { label: 'Complementar', value: 'COMPLEMENTAR' },
            { label: 'Ajuste', value: 'AJUSTE' },
            { label: 'Devolução/Retorno', value: 'DEVOLUCAO' },
          ]"
          v-model="this.filters.finalidadeNf"
        />
      </div>
    </template>

    <template v-slot:columns>
      <Column field="id" header="Número / Série / Chave" sortable>
        <template #body="r">
          {{ new String(r.data.numero ?? "0").padStart(6, "0") }} /
          {{ new String(r.data.serie ?? "0").padStart(3, "0") }}
          <div style="font-size: smaller">{{ r.data.chaveAcesso }}</div>
          <div class="badge badge-pill badge-info" title="Natureza da Operação">
            {{ r.data.naturezaOperacao }}
          </div>
        </template>
      </Column>
      <Column field="id" header="Destinatário" sortable>
        <template #body="r">
          <div class="float-left" v-if="r.data?.documentoDestinatario">
            {{
              r.data.documentoDestinatario.length === 14
                ? r.data.documentoDestinatario.replace(
                    /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g,
                    "$1.$2.$3/$4-$5"
                  )
                : r.data.documentoDestinatario.replace(
                    /(\d{3})(\d{3})(\d{3})(\d{2})/g,
                    "$1.$2.$3-$4"
                  )
            }}
            <br />{{ r.data.xNomeDestinatario }}
          </div>
          <div class="float-right">
            <span :class="'badge badge-pill badge-' + (r.data.resumo ? 'secondary' : 'primary')">
              {{ r.data.resumo ? "Resumo" : "Completa" }}
            </span>
          </div>
        </template>
      </Column>
      <Column field="id" header="Status">
        <template #body="r">
          <div v-if="r.data.cStatLote && r.data.cStatLote != r.data.cStat">
            {{ r.data.cStat + " - " + r.data.xMotivo }}
          </div>
          <div v-if="r.data.cStatLote && r.data.cStatLote != r.data.cStat">
            {{ r.data.cStatLote + " - " + r.data.xMotivoLote }}
          </div>
        </template>
      </Column>
      <Column field="dtEmissao" header="Dt Emissão" sortable>
        <template #body="r">
          <div class="text-center">
            {{ this.moment(r.data.dtEmissao).format("DD/MM/YYYY HH:mm:ss") }}
          </div>
        </template>
      </Column>
      <Column field="valorTotal" header="Valor Total" sortable>
        <template #body="r">
          <div class="text-right">
            {{
              (r.data.valorTotal ?? 0).toLocaleString("pt-BR", {
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
              v-if="r.data.jsonData?.fatura?.fatura_id"
              role="button"
              title="Visualizar Fatura"
              class="btn btn-sm btn-outline-warning mr-1"
              href=""
            >
              <i class="fas fa-money-check-alt"></i
            ></a>

            <a
              v-if="r.data.possuiXml"
              role="button"
              title="Download do XML"
              :href="'/api/fis/notaFiscal/downloadXML/' + r.data.id"
              target="_blank"
              class="ml-1 btn btn-outline-primary btn-sm"
            >
              <i class="fas fa-file-code"></i>
            </a>

            <a
              v-if="r.data.possuiXml && !r.data.resumo"
              role="button"
              title="Ver PDF"
              class="ml-1 btn btn-sm btn-outline-success"
              :href="'/api/fis/notaFiscal/imprimir/' + r.data.id"
              target="_blank"
            >
              <i class="fas fa-file-pdf" aria-hidden="true"></i
            ></a>

            <a
              role="button"
              class="ml-1 btn btn-primary btn-sm"
              title="Editar registro"
              :href="'/v/fis/notaFiscal/form?id=' + r.data.id"
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
import { mapActions, mapGetters, mapMutations } from "vuex";
import {
  CrosierCalendar,
  CrosierCurrency,
  CrosierDropdown,
  CrosierInputInt,
  CrosierInputText,
  CrosierListS,
} from "crosier-vue";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import Toast from "primevue/toast";
import moment from "moment";
import axios from "axios";

export default {
  name: "emitidasList",

  components: {
    CrosierListS,
    Column,
    CrosierInputInt,
    CrosierInputText,
    CrosierCalendar,
    CrosierCurrency,
    ConfirmDialog,
    CrosierDropdown,
    Toast,
  },

  data() {
    return {
      selection: [],
      itensMenuManifestar: [
        {
          label: "Manifestar Confirmação",
          icon: "fas fa-check-double",
          command: () => {
            this.manifestarEmLote("confirmacao");
          },
        },
        {
          label: "Manifestar Desconhecimento",
          icon: "far fa-question-circle",
          command: () => {
            this.manifestarEmLote("desconhecimento");
          },
        },
        {
          label: "Manifestar 'Não Realizada'",
          icon: "fas fa-thumbs-down",
          command: () => {
            this.manifestarEmLote("naoRealizada");
          },
        },
      ],
    };
  },

  async mounted() {
    this.setLoading(true);
    await this.loadData();
    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFilters"]),
    ...mapActions(["loadData"]),

    moment(date) {
      return moment(date);
    },

    async beforeFilter() {
      this.filters["dtEmissao[after]"] = this.filters["dtEmissao[after]"]
        ? `${moment(this.filters["dtEmissao[after]"]).format("YYYY-MM-DD")}T00:00:00-03:00`
        : null;
      this.filters["dtEmissao[before]"] = this.filters["dtEmissao[before]"]
        ? `${moment(this.filters["dtEmissao[before]"]).format("YYYY-MM-DD")}T23:59:59-03:00`
        : null;
    },

    edit(data) {
      return data.resumo ? "nfesFornecedores_formResumo" : "nfesFornecedores_form";
    },

    async downloadXMLs() {
      if (!this.filters["dtEmissao[after]"] || !this.filters["dtEmissao[before]"]) {
        this.$toast.add({
          severity: "warn",
          summary: "Atenção",
          detail: "Para efetuar o download é necessário informar o período",
          life: 5000,
        });
        return;
      }
      this.setLoading(true);
      try {
        this.beforeFilter();

        // now concat all filters to the URL as querystring
        const filtersQuerystring = Object.keys(this.filters)
          .map((key) => `${key}=${this.filters[key]}`)
          .join("&");

        const rs = await axios.get(`/api/fis/notaFiscal/downloadXMLsMesAno?${filtersQuerystring}`, {
          validateStatus(status) {
            return status < 500;
          },
          responseType: "blob",
        });

        const contentType = rs.headers["content-type"];

        if (contentType.includes("application/json")) {
          // A resposta é um JSON
          const reader = new FileReader();
          reader.onload = () => {
            const json = JSON.parse(reader.result);

            if (json?.RESULT === "ERRO") {
              this.$toast.add({
                severity: "error",
                summary: "Erro",
                detail: json?.MSG,
                life: 5000,
              });
              this.setLoading(false);
            }
          };
          reader.readAsText(rs.data);
          return;
        }

        window.open(URL.createObjectURL(rs.data));
        // const url = window.URL.createObjectURL(new Blob([rs.data]));
        // const link = document.createElement("a");
        // link.href = url;
        // link.setAttribute("download", rs.headers.filename); // or any other extension
        // document.body.appendChild(link);
        // link.click();
      } catch (e) {
        console.error(e);
      }
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({
      filters: "getEmitidasFilters",
      contribuintes: "getContribuintes",
    }),
  },
};
</script>

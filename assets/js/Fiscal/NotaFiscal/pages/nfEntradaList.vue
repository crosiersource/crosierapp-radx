<template>
  <ConfirmDialog />
  <Toast class="mt-4" />
  <CrosierListS
    titulo="NFs Entrada"
    apiResource="/api/fis/notaFiscal/"
    :formUrl="this.formUrl"
    @beforeFilter="this.beforeFilter"
    v-model:selection="this.selection"
    ref="dt"
  >
    <template v-slot:headerButtons>
      <button type="button" class="btn btn-warning ml-1" @click="this.obterNotas">
        <i class="fas fa-download"></i> Obter Notas
      </button>

      <button type="button" class="btn btn-success ml-1" @click="this.downloadXMLs">
        <i class="fas fa-file-code"></i> Download XMLs
      </button>

      <button type="button" class="btn btn-info ml-1" @click="this.manifestarEmLote">
        <i class="fas fa-eye"></i> Manifestar Ciência
      </button>
    </template>

    <template v-slot:filter-fields>
      <div class="form-row">
        <CrosierDropdown
          label="Contribuinte"
          col="4"
          id="contribuinte"
          :options="this.contribuintes"
          optionLabel="empresa"
          optionValue="cnpj"
          v-model="this.filters.documentoDestinatario"
        />

        <CrosierInputInt label="Número" col="2" id="codigo" v-model="this.filters.numero" />

        <CrosierInputText
          label="CPF/CNPJ Emitente"
          col="3"
          id="documentoEmitente"
          v-model="this.filters.documentoEmitente"
        />

        <CrosierInputText
          label="Nome Emitente"
          col="3"
          id="xNomeEmitente"
          v-model="this.filters.xNomeEmitente"
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
    </template>

    <template v-slot:columns>
      <Column selectionMode="multiple" headerStyle="width: 5em"></Column>
      <Column field="id" header="Número / Série / Chave" :sortable="true">
        <template #body="r">
          {{ new String(r.data.numero ?? "0").padStart(6, "0") }} /
          {{ new String(r.data.serie ?? "0").padStart(3, "0") }}
          <br />
          <span style="font-size: smaller">{{ r.data.chaveAcesso }}</span>
        </template>
      </Column>
      <Column field="documento" header="Emitente" :sortable="true">
        <template #body="r">
          <div class="float-left">
            {{
              r.data.documentoEmitente.length === 14
                ? r.data.documentoEmitente.replace(
                    /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g,
                    "$1.$2.$3/$4-$5"
                  )
                : r.data.documentoEmitente.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g, "$1.$2.$3-$4")
            }}
            <br />{{ r.data.xNomeEmitente }}
          </div>
          <div class="float-right">
            <span :class="'badge badge-pill badge-' + (r.data.resumo ? 'secondary' : 'primary')">
              {{ r.data.resumo ? "Resumo" : "Completa" }}
            </span>
          </div>
        </template>
      </Column>
      <Column field="dtEmissao" header="Dt Emissão" :sortable="true">
        <template #body="r">
          <div class="text-center">
            {{ this.moment(r.data.dtEmissao).format("DD/MM/YYYY HH:mm:ss") }}
          </div>
        </template>
      </Column>
      <Column field="valorTotal" header="Valor Total" :sortable="true">
        <template #body="r">
          <div class="text-right">
            {{
              r.data.valorTotal.toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL",
              })
            }}
          </div>
        </template>
      </Column>
      <Column field="updated" header="" :sortable="true">
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
              role="button"
              title="Download do XML"
              :href="'/fis/nfesFornecedores/downloadXML/' + r.data.id"
              target="_blank"
              class="ml-1 btn btn-outline-primary btn-sm"
            >
              <i class="fas fa-file-code"></i>
            </a>

            <a
              role="button"
              title="Ver PDF"
              class="ml-1 btn btn-sm btn-outline-success"
              :href="'/fis/emissaonfe/imprimir/' + r.data.id"
              target="_blank"
            >
              <i class="fas fa-file-pdf" aria-hidden="true"></i
            ></a>

            <a
              role="button"
              class="ml-1 btn btn-primary btn-sm"
              title="Editar registro"
              :href="'/fis/nfesFornecedores/form/' + r.data.id"
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
  CrosierCalendar,
  CrosierCurrency,
  CrosierInputInt,
  CrosierInputText,
  CrosierListS,
  CrosierDropdown,
} from "crosier-vue";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import Toast from "primevue/toast";
import moment from "moment";
import axios from "axios";

export default {
  name: "nfEntradaList",

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
      contribuintes: null,
    };
  },

  async mounted() {
    this.setLoading(true);
    const rs = await axios.get("/api/fis/nfeUtils/getContribuintes", {
      headers: {
        "Content-Type": "application/ld+json",
      },
      validateStatus(status) {
        return status < 500;
      },
    });
    console.log(rs);
    if (rs?.data?.RESULT === "OK") {
      this.contribuintes = rs.data.DATA;
    } else {
      console.error(rs?.data?.MSG);
      this.$toast.add({
        severity: "error",
        summary: "Erro",
        detail: rs?.data?.MSG,
        life: 5000,
      });
    }
    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading"]),

    moment(date) {
      return moment(date);
    },

    beforeFilter() {
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

    manifestarEmLote() {
      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);

          const nfsIds = this.selection
            .map((nf) => {
              return nf.id;
            })
            .join(",");

          const rs = await axios.get(
            `/fis/notaFiscal/nfEntrada/manifestarEmLote?nfsIds=${nfsIds}`,
            {
              headers: {
                "Content-Type": "application/ld+json",
              },
              validateStatus(status) {
                return status < 500;
              },
            }
          );
          console.log(rs);
          if (rs?.data?.RESULT === "OK") {
            this.$toast.add({
              severity: "success",
              summary: "Sucesso",
              detail: rs?.data?.MSG,
              life: 5000,
            });
            this.$refs.dt.doClearFilters();
          } else {
            console.error(rs?.data?.MSG);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: rs?.data?.MSG,
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },

    downloadXMLs() {
      const nfsIds = this.selection
        .map((nf) => {
          return nf.id;
        })
        .join(",");

      window.open(`/fis/notaFiscal/nfEntrada/downloadXMLs?nfsIds=${nfsIds}`, "_blank");
    },

    obterNotas() {
      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          console.log("obtendo notas");
          const rs = await axios.get(
            `/fis/distDFe/obterDistDFes?documentoDestinatario=${this.filters.documentoDestinatario}`,
            {
              headers: {
                "Content-Type": "application/ld+json",
              },
              validateStatus(status) {
                return status < 500;
              },
            }
          );
          console.log(rs);
          if (rs?.data?.RESULT === "OK") {
            this.$toast.add({
              severity: "success",
              summary: "Sucesso",
              detail: rs?.data?.MSG,
              life: 5000,
            });
            this.$refs.dt.doClearFilters();
          } else {
            console.error(rs?.data?.MSG);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: rs?.data?.MSG,
              life: 5000,
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

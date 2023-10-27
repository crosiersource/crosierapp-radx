<template>
  <CrosierBlock :loading="this.loading" />
  <ConfirmDialog group="confirmDialog_crosierListS" />
  <Toast position="bottom-right" class="mb-5" />

  <div class="container">
    <div class="card" style="margin-bottom: 50px">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center">
          <div class="mr-1">
            <h3>Nota Fiscal</h3>
          </div>
          <div class="d-sm-flex flex-nowrap ml-auto">
            <a type="button" class="btn btn-info mr-2" href="form" title="Novo">
              <i class="fas fa-file" aria-hidden="true"></i>
            </a>
            <a
              role="button"
              class="btn btn-outline-secondary"
              :href="this.notaFiscal.nossaEmissao ? 'emitidas/list' : 'recebidas/list'"
              title="Listar"
            >
              <i class="fas fa-list"></i>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <TabView class="mt-3">
          <TabPanel header="Cabeçalho">
            <Cabecalho @submitForm="this.submitForm" />
          </TabPanel>
          <TabPanel :disabled="!this.notaFiscal?.id" header="Itens">
            <Itens />
          </TabPanel>
          <TabPanel :disabled="!this.notaFiscal?.id" header="Transporte">
            <Transporte />
          </TabPanel>
          <TabPanel :disabled="!this.notaFiscal?.id" header="Cartas de Correção">
            <CartasCorrecao />
          </TabPanel>
          <TabPanel :disabled="!this.notaFiscal?.id" header="Duplicatas">
            <Duplicatas />
          </TabPanel>
          <TabPanel :disabled="!this.notaFiscal?.id" header="Histórico">
            <Historico />
          </TabPanel>
        </TabView>

        <div class="row mt-3">
          <div class="col text-right">
            <button
              class="btn btn-sm btn-primary"
              style="width: 12rem"
              type="button"
              v-if="this.notaFiscal.permiteSalvar"
              @click="this.submitForm"
            >
              <i class="fas fa-save"></i> Salvar
            </button>

            <button
              type="button"
              style="width: 15rem"
              class="btn btn-sm btn-success ml-1"
              v-if="this.notaFiscal.permiteFaturamento"
              @click="this.faturar()"
            >
              <i class="fas fa-file-invoice"></i> Faturar
            </button>

            <button
              type="button"
              style="width: 15rem"
              class="btn btn-sm btn-outline-success ml-1"
              @click="this.consultarStatus()"
              v-if="this.notaFiscal.infoStatus && this.notaFiscal.infoStatus !== 'SEM STATUS'"
            >
              <i class="fas fa-search"></i> Consultar Status
            </button>

            <button
              type="button"
              class="btn btn-sm btn-danger ml-1"
              v-if="this.notaFiscal.permiteCancelamento"
              @click="this.$store.state.exibirDialogCancelamento = true"
            >
              <i class="fas fa-ban"></i> Cancelar
            </button>

            <a
              role="button"
              style="width: 12rem"
              value="Download do XML"
              class="btn btn-sm btn-outline-warning ml-1"
              :href="'/api/fis/notaFiscal/downloadXML/' + this.notaFiscal.id"
              target="_blank"
              v-if="this.notaFiscal.possuiXml"
            >
              <i class="fas fa-file-code"></i> XML
            </a>

            <a
              role="button"
              style="width: 12rem"
              value="Imprimir PDF"
              class="btn btn-sm btn-outline-primary ml-1"
              :href="'/api/fis/notaFiscal/imprimir/' + this.notaFiscal.id"
              target="_blank"
              v-if="this.notaFiscal.possuiXml"
            >
              <i class="fas fa-print" aria-hidden="true"></i> PDF
            </a>

            <a
              role="button"
              value="Imprimir Cancelamento"
              class="btn btn-sm btn-outline-danger ml-1"
              :href="'/api/fis/notaFiscal/imprimirCancelamento/' + this.notaFiscal.id"
              target="_blank"
              v-if="this.notaFiscal.permiteReimpressaoCancelamento"
            >
              <i class="fas fa-print" aria-hidden="true"></i> Imprimir Cancelamento
            </a>

            <button
              type="button"
              class="btn btn-sm btn-secondary ml-1"
              @click="this.clonar"
              v-if="this.notaFiscal.id && this.notaFiscal.nossaEmissao"
            >
              <i class="fas fa-copy" aria-hidden="true"></i> Clonar
            </button>

            <a
              v-if="this.notaFiscal.vendaId"
              role="button"
              value="Ir para venda"
              class="btn btn-sm btn-outline-success ml-1"
              :href="'/ven/venda/ecommerceForm/' + this.notaFiscal.vendaId"
            >
              <i class="fas fa-shopping-cart"></i> Venda
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { CrosierBlock, submitForm } from "crosier-vue";
import * as yup from "yup";
import ConfirmDialog from "primevue/confirmdialog";
import Toast from "primevue/toast";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import { mapActions, mapGetters, mapMutations } from "vuex";
import axios from "axios";
import Cabecalho from "./cabecalho";
import Itens from "./itens";
import Transporte from "./transporte";
import CartasCorrecao from "./cartasCorrecao";
import Duplicatas from "./duplicatas";
import Historico from "./historico";

export default {
  components: {
    CrosierBlock,
    ConfirmDialog,
    Toast,
    TabView,
    TabPanel,
    Cabecalho,
    Itens,
    Transporte,
    CartasCorrecao,
    Duplicatas,
    Historico,
  },

  data() {
    return {
      schemaValidator: {},
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.loadData();

    this.schemaValidator = yup.object().shape({
      documentoEmitente: yup.string().required().typeError(),
      naturezaOperacao: yup.string().required().typeError(),
      finalidadeNf: yup.string().required().typeError(),
      dtSaiEnt: yup.date().required().typeError(),
      entradaSaida: yup.string().required().typeError(),
      idDest: yup.string().required().typeError(),
      documentoDestinatario: yup.string().required().typeError(),
      xNomeDestinatario: yup.string().required().typeError(),
      logradouroDestinatario: yup.string().required().typeError(),
      numeroDestinatario: yup.string().required().typeError(),
      bairroDestinatario: yup.string().required().typeError(),
      cepDestinatario: yup.string().required().typeError(),
      cidadeDestinatario: yup.string().required().typeError(),
      estadoDestinatario: yup.string().required().typeError(),
      transpModalidadeFrete: yup.string().required().typeError(),
      indicadorFormaPagto: yup.string().required().typeError(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setNotaFiscal", "setNotaFiscalErrors"]),
    ...mapActions(["loadData"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fis/notaFiscal",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "notaFiscal",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          delete formData.cStat;
          delete formData.cStatLote;
          delete formData.numero;
          delete formData.serie;
          delete formData.protocoloAutorizacao;
          delete formData.chaveAcesso;
          delete formData.infoStatus;
          delete formData.cnf;
          delete formData.dadosDuplicatas;
          delete formData.dtManifestDest;
          delete formData.dtProtocoloAutorizacao;
          delete formData.jsonData;
          delete formData.permiteSalvar;
          delete formData.permiteReimpressao;
          delete formData.permiteReimpressaoCancelamento;
          delete formData.permiteCancelamento;
          delete formData.permiteCartaCorrecao;
          delete formData.permiteFaturamento;
          delete formData.msgPermiteSalvar;
          delete formData.msgPermiteReimpressao;
          delete formData.msgPermiteReimpressaoCancelamento;
          delete formData.msgPermiteCancelamento;
          delete formData.msgPermiteCartaCorrecao;
          delete formData.msgPermiteFaturamento;
          delete formData.subtotal;
          delete formData.total;
          delete formData.totalDescontos;
          delete formData.nsu;
          delete formData.nossaEmissao;
          delete formData.manifestDest;
          delete formData.motivoCancelamento;
        },
      });
      this.setLoading(false);
    },

    faturar() {
      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        icon: "pi pi-exclamation-triangle",
        group: "confirmDialog_crosierListS",
        accept: async () => {
          this.setLoading(true);

          try {
            const rs = await axios.post(`/api/fis/notaFiscal/faturar/${this.notaFiscal.id}`);
            await this.loadData();
            if (rs?.status === 200) {
              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                detail: "Faturamento enviado com sucesso! (Verifique o status)",
                life: 5000,
              });
            }
          } catch (e) {
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: e?.response?.data?.EXCEPTION_MSG ?? "Ocorreu um erro ao efetuar a operação",
              life: 5000,
            });
          }

          this.setLoading(false);
        },
      });
    },

    clonar() {
      this.$confirm.require({
        header: "Confirmação",
        message: "Confirmar a operação?",
        icon: "pi pi-exclamation-triangle",
        group: "confirmDialog_crosierListS",
        accept: async () => {
          this.setLoading(true);

          try {
            const rs = await axios.post(`/api/fis/notaFiscal/clonar/${this.notaFiscal.id}`);

            if (rs?.status === 200) {
              const url = new URL(window.location.href);
              url.searchParams.set("id", rs.data.DATA.id);
              window.history.replaceState({}, "", url.toString());

              this.$toast.add({
                severity: "success",
                summary: "Sucesso",
                detail: "Nota Fiscal clonada com sucesso!",
                life: 5000,
              });

              await this.loadData();
            }
          } catch (e) {
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: e?.response?.data?.EXCEPTION_MSG ?? "Ocorreu um erro ao efetuar a operação",
              life: 5000,
            });
          }

          this.setLoading(false);
        },
      });
    },

    async consultarStatus() {
      this.setLoading(true);

      try {
        const rs = await axios.post(`/api/fis/notaFiscal/consultarStatus/${this.notaFiscal.id}`);
        await this.loadData();
        if (rs?.status === 200) {
          this.$toast.add({
            severity: "success",
            summary: "Sucesso",
            detail: "Operação realizada com sucesso!",
            life: 5000,
          });
        }
      } catch (e) {
        console.error(e.message);
        this.$toast.add({
          severity: "error",
          summary: "Erro",
          detail: e.message ?? "Ocorreu um erro ao efetuar a operação",
          life: 5000,
        });
      }

      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      notaFiscal: "getNotaFiscal",
      errors: "getNotaFiscalErrors",
    }),
  },
};
</script>

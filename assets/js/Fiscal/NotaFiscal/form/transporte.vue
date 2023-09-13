<template>
  <CrosierFormS @submitForm="this.submitForm" withoutCard semBotaoSalvar>
    <div class="form-row">
      <CrosierDropdown
        label="Modalidade Frete"
        id="transpModalidadeFrete"
        :options="[
          { label: 'Sem frete', value: 'SEM_FRETE' },
          { label: 'Por conta do emitente', value: 'EMITENTE' },
          { label: 'Por conta do destinatário/remetente', value: 'DESTINATARIO' },
          { label: 'Por conta de terceiros', value: 'TERCEIROS' },
        ]"
        v-model="this.notaFiscal.transpModalidadeFrete"
        :disabled="!this.notaFiscal.permiteSalvar"
      />
    </div>

    <div class="card mb-4" v-if="this.notaFiscal.transpModalidadeFrete !== 'SEM_FRETE'">
      <div class="card-body">
        <h5 class="card-title">Volumes</h5>

        <div class="form-row">
          <CrosierInputInt
            label="Qtde"
            id="transpEspecieVolumes"
            v-model="this.notaFiscal.transpQtdeVolumes"
            col="2"
            :disabled="!this.notaFiscal.permiteSalvar"
          />
          <CrosierInputText
            label="Espécie"
            id="transpEspecieVolumes"
            v-model="this.notaFiscal.transpEspecieVolumes"
            col="3"
            helpText="Caixas, pacotes, etc"
            :disabled="!this.notaFiscal.permiteSalvar"
          />
          <CrosierInputText
            label="Marca"
            id="transpMarcaVolumes"
            v-model="this.notaFiscal.transpMarcaVolumes"
            col="4"
            :disabled="!this.notaFiscal.permiteSalvar"
          />
          <CrosierInputText
            label="Numeração"
            id="transpNumeracaoVolumes"
            v-model="this.notaFiscal.transpNumeracaoVolumes"
            col="3"
            :disabled="!this.notaFiscal.permiteSalvar"
          />
        </div>

        <div class="form-row">
          <CrosierInputDecimal
            label="Peso Bruto"
            id="transpPesoBruto"
            v-model="this.notaFiscal.transpPesoBruto"
            col="3"
            :disabled="!this.notaFiscal.permiteSalvar"
            helpText="(em kg)"
            :decimais="3"
          />

          <CrosierInputDecimal
            label="Peso Líquido"
            id="transpPesoLiquido"
            v-model="this.notaFiscal.transpPesoLiquido"
            col="3"
            :disabled="!this.notaFiscal.permiteSalvar"
            helpText="(em kg)"
            :decimais="3"
          />

          <CrosierCurrency
            label="Valor Total do Frete"
            id="transpValorTotalFrete"
            v-model="this.notaFiscal.transpValorTotalFrete"
            col="6"
            :disabled="!this.notaFiscal.permiteSalvar"
          />
        </div>
      </div>
    </div>

    <div class="card mb-4" v-if="this.notaFiscal.transpModalidadeFrete !== 'SEM_FRETE'">
      <div class="card-body">
        <h5 class="card-title">Transportadora</h5>

        <div class="form-row">
          <CrosierInputCpfCnpj
            col="3"
            label="CPF/CNPJ"
            id="transpDocumento"
            v-model="this.notaFiscal.transpDocumento"
            :disabled="!this.notaFiscal.permiteSalvar"
            appendButton
            appendButtonTitle="Pesquisar (é necessário informar a UF)"
            @appendButtonClicked="this.consultarDestinatario"
          />

          <CrosierInputText
            label="Nome / Razão Social"
            id="transpNome"
            v-model="this.notaFiscal.transpNome"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="6"
          />

          <CrosierInputText
            label="Inscrição Estadual"
            id="transpInscricaoEstadual"
            v-model="this.notaFiscal.transpInscricaoEstadual"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="3"
          />
        </div>

        <div class="form-row">
          <CrosierInputText
            label="Endereço Completo"
            id="transpEndereco"
            v-model="this.notaFiscal.transpEndereco"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="6"
          />

          <CrosierInputText
            label="Cidade"
            id="transpCidade"
            v-model="this.notaFiscal.transpCidade"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="4"
          />

          <CrosierDropdownUf
            label="UF"
            id="transpEstado"
            v-model="this.notaFiscal.transpEstado"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="2"
          />
        </div>
      </div>
    </div>
    <div class="row mt-3" v-if="this.notaFiscal.permiteSalvar">
      <div class="col text-right">
        <button class="btn btn-sm btn-primary" style="width: 12rem" type="submit">
          <i class="fas fa-save"></i> Salvar
        </button>
      </div>
    </div>
  </CrosierFormS>
</template>
<script>
import axios from "axios";
import { mapGetters, mapMutations, mapActions } from "vuex";
import {
  CrosierDropdown,
  CrosierCurrency,
  CrosierDropdownUf,
  CrosierFormS,
  CrosierInputDecimal,
  CrosierInputCpfCnpj,
  CrosierInputInt,
  CrosierInputText,
  submitForm,
} from "crosier-vue";

export default {
  components: {
    CrosierFormS,
    CrosierDropdown,
    CrosierInputText,
    CrosierCurrency,
    CrosierInputInt,
    CrosierInputDecimal,
    CrosierInputCpfCnpj,
    CrosierDropdownUf,
  },

  data() {
    return {
      schemaValidator: {},
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.loadData();

    // this.schemaValidator = yup.object().shape({
    //   documentoEmitente: yup.string().required().typeError(),
    //   naturezaOperacao: yup.string().required().typeError(),
    //   finalidadeNf: yup.string().required().typeError(),
    //   dtSaiEnt: yup.date().required().typeError(),
    //   entradaSaida: yup.string().required().typeError(),
    //   idDest: yup.string().required().typeError(),
    //   documentoDestinatario: yup.string().required().typeError(),
    //   xNomeDestinatario: yup.string().required().typeError(),
    //   logradouroDestinatario: yup.string().required().typeError(),
    //   numeroDestinatario: yup.string().required().typeError(),
    //   bairroDestinatario: yup.string().required().typeError(),
    //   cepDestinatario: yup.string().required().typeError(),
    //   cidadeDestinatario: yup.string().required().typeError(),
    //   estadoDestinatario: yup.string().required().typeError(),
    //   transpModalidadeFrete: yup.string().required().typeError(),
    //   indicadorFormaPagto: yup.string().required().typeError(),
    // });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setNotaFiscal", "setNotaFiscalErrors"]),
    ...mapActions(["loadData"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fis/notaFiscal",
        // schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "notaFiscal",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          const naoRemover = [
            "@id",
            "transpModalidadeFrete",
            "transpQtdeVolumes",
            "transpEspecieVolumes",
            "transpMarcaVolumes",
            "transpNumeracaoVolumes",
            "transpPesoBruto",
            "transpPesoLiquido",
            "transpValorTotalFrete",
            "transpDocumento",
            "transpNome",
            "transpInscricaoEstadual",
            "transpEndereco",
            "transpCidade",
            "transpEstado",
          ];
          Object.keys(formData).forEach((key) => {
            if (!naoRemover.includes(key)) {
              delete formData[key];
            }
          });
        },
      });
      this.setLoading(false);
    },

    async consultarDestinatario() {
      this.setLoading(true);
      // /api/fis/notaFiscal/consultarCNPJ
      const rs = await axios.get(
        `/api/fis/notaFiscal/consultarCNPJ?cnpj=${this.notaFiscal.transpDocumento}&uf=${this.notaFiscal.transpEstado}`
      );
      if (rs?.data?.dados) {
        this.notaFiscal.transpNome = rs.data.dados.razaoSocial[0];
        this.notaFiscal.transpInscricaoEstadual = rs.data.dados.IE[0];
        this.notaFiscal.transpEndereco = rs.data.dados.logradouro[0];
        this.notaFiscal.transpCidade = rs.data.dados.cidade[0];
        this.notaFiscal.transpEstado = rs.data.dados.UF[0];
      } else {
        this.$toast.add({
          severity: "info",
          summary: "Atenção",
          detail: "Nenhum dado encontrado para o CNPJ e UF informados!",
          life: 5000,
        });
      }
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({
      notaFiscal: "getNotaFiscal",
      contribuintes: "fiscalStore/getContribuintes",
      errors: "getNotaFiscalErrors",
    }),

    naoPodePesquisarDestinatario() {
      return !this.notaFiscal.documentoDestinatario || !this.notaFiscal.estadoDestinatario;
    },
  },
};
</script>

<template>
  <Cancelamento />
  <CrosierFormS @submitForm="this.submitForm" withoutCard semBotaoSalvar>
    <div class="form-row">
      <CrosierInputInt label="Id" col="2" id="id" v-model="this.notaFiscal.id" disabled />

      <CrosierDropdown
        v-if="this.notaFiscal.nossaEmissao && !this.notaFiscal.id"
        label="Emitente"
        col="10"
        id="emitente"
        optionLabel="empresa"
        optionValue="cnpj"
        :options="this.contribuintes"
        v-model="this.notaFiscal.documentoEmitente"
        :error="this.errors.documentoEmitente"
        :disabled="!this.notaFiscal.permiteSalvar"
      />

      <CrosierInputText
        v-else
        label="Emitente"
        col="10"
        id="emitente"
        v-model="this.notaFiscal.emitenteCompleto"
        disabled
      />
    </div>

    <div class="form-row">
      <CrosierInputText label="Status" id="status" v-model="this.notaFiscal.infoStatus" disabled />
    </div>

    <div class="form-row">
      <CrosierInputInt
        label="Número"
        id="numero"
        v-model="this.notaFiscal.numero"
        disabled
        col="3"
      />

      <CrosierInputInt label="Série" id="serie" v-model="this.notaFiscal.serie" disabled col="2" />

      <CrosierInputText
        label="Chave"
        id="chaveAcesso"
        v-model="this.notaFiscal.chaveAcesso"
        disabled
        col="4"
      />

      <CrosierInputText
        label="Protocolo Autorização"
        id="protocoloAutorizacao"
        v-model="this.notaFiscal.protocoloAutorizacao"
        disabled
        col="3"
      />
    </div>

    <div class="form-row">
      <CrosierInputText
        label="Natureza da Operação"
        id="naturezaOperacao"
        v-model="this.notaFiscal.naturezaOperacao"
        :error="this.errors.naturezaOperacao"
        :disabled="!this.notaFiscal.permiteSalvar"
        col="6"
      />

      <CrosierCalendar
        col="3"
        label="Dt/Hr Emissão"
        id="dtEmissao"
        v-model="this.notaFiscal.dtEmissao"
        showTime
        showSeconds
        :disabled="!this.notaFiscal.permiteSalvar"
      />

      <CrosierCalendar
        col="3"
        label="Dt/Hr Saída"
        id="dtSaiEnt"
        :error="this.errors.dtSaiEnt"
        v-model="this.notaFiscal.dtSaiEnt"
        showTime
        showSeconds
        :disabled="!this.notaFiscal.permiteSalvar"
      />
    </div>

    <div class="form-row">
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
        v-model="this.notaFiscal.finalidadeNf"
        :error="this.errors.finalidadeNf"
        :disabled="!this.notaFiscal.permiteSalvar"
      />

      <CrosierDropdown
        label="Entrada/Saída"
        col="3"
        id="entradaSaida"
        :options="[
          { label: 'Entrada', value: 'E' },
          { label: 'Saída', value: 'S' },
        ]"
        v-model="this.notaFiscal.entradaSaida"
        :error="this.errors.entradaSaida"
        :disabled="!this.notaFiscal.permiteSalvar"
      />

      <CrosierDropdown
        label="ID Dest"
        col="3"
        id="idDest"
        :options="[
          { value: 1, label: '1=Operação interna' },
          { value: 2, label: '2=Operação interestadual' },
          { value: 3, label: '3=Operação com exterior' },
        ]"
        v-model="this.notaFiscal.idDest"
        :error="this.errors.idDest"
        :disabled="!this.notaFiscal.permiteSalvar"
      />
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">Destinatário</h5>

        <div class="form-row">
          <CrosierInputCpfCnpj
            col="3"
            label="CPF/CNPJ"
            id="documentoDestinatario"
            v-model="this.notaFiscal.documentoDestinatario"
            :error="this.errors.documentoDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            :appendButton="this.notaFiscal.permiteSalvar"
            appendButtonTitle="Pesquisar (é necessário informar a UF)"
            @appendButtonClicked="this.consultarDestinatario"
          />

          <CrosierInputText
            label="Nome / Razão Social"
            id="xNomeDestinatario"
            v-model="this.notaFiscal.xNomeDestinatario"
            :error="this.errors.xNomeDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="6"
          />

          <CrosierInputText
            label="Inscrição Estadual"
            id="inscricaoEstadualDestinatario"
            v-model="this.notaFiscal.inscricaoEstadualDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="3"
          />
        </div>

        <div class="form-row">
          <CrosierInputText
            label="Logradouro"
            id="logradouroDestinatario"
            v-model="this.notaFiscal.logradouroDestinatario"
            :error="this.errors.logradouroDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="4"
          />

          <CrosierInputText
            label="Número"
            id="numeroDestinatario"
            v-model="this.notaFiscal.numeroDestinatario"
            :error="this.errors.numeroDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="2"
          />

          <CrosierInputText
            label="Complemento"
            id="complementoDestinatario"
            v-model="this.notaFiscal.complementoDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="2"
          />

          <CrosierInputText
            label="Bairro"
            id="bairroDestinatario"
            v-model="this.notaFiscal.bairroDestinatario"
            :error="this.errors.bairroDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="2"
          />

          <CrosierInputCep
            label="CEP"
            id="cepDestinatario"
            v-model="this.notaFiscal.cepDestinatario"
            :error="this.errors.cepDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            :comConsulta="this.notaFiscal.permiteSalvar"
            @consultaCep="this.consultaCep"
            col="2"
          />
        </div>

        <div class="form-row">
          <CrosierInputTelefone
            label="Fone"
            id="foneDestinatario"
            v-model="this.notaFiscal.foneDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="3"
          />

          <CrosierInputEmail
            label="E-mail"
            id="emailDestinatario"
            v-model="this.notaFiscal.emailDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="3"
          />

          <CrosierInputText
            label="Cidade"
            id="cidadeDestinatario"
            v-model="this.notaFiscal.cidadeDestinatario"
            :error="this.errors.cidadeDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="4"
          />

          <CrosierDropdownUf
            label="UF"
            id="estadoDestinatario"
            v-model="this.notaFiscal.estadoDestinatario"
            :error="this.errors.estadoDestinatario"
            :disabled="!this.notaFiscal.permiteSalvar"
            col="2"
          />
        </div>
      </div>
    </div>

    <div class="form-row">
      <CrosierDropdown
        label="Modalidade Frete"
        col="3"
        id="transpModalidadeFrete"
        :options="[
          { label: 'Sem frete', value: 'SEM_FRETE' },
          { label: 'Por conta do emitente', value: 'EMITENTE' },
          { label: 'Por conta do destinatário/remetente', value: 'DESTINATARIO' },
          { label: 'Por conta de terceiros', value: 'TERCEIROS' },
        ]"
        v-model="this.notaFiscal.transpModalidadeFrete"
        :error="this.errors.transpModalidadeFrete"
        :disabled="!this.notaFiscal.permiteSalvar"
      />

      <CrosierDropdown
        label="Forma de Pagamento"
        col="3"
        id="indicadorFormaPagto"
        :options="[
          { label: 'A vista', value: 'VISTA' },
          { label: 'A prazo', value: 'PRAZO' },
          { label: 'Outros', value: 'OUTROS' },
        ]"
        v-model="this.notaFiscal.indicadorFormaPagto"
        :disabled="!this.notaFiscal.permiteSalvar"
      />

      <CrosierInputText
        label="Id NF Referenciada"
        id="a03idNfReferenciada"
        col="6"
        v-model="this.notaFiscal.a03idNfReferenciada"
        :disabled="!this.notaFiscal.permiteSalvar"
      />
    </div>

    <div class="form-row">
      <CrosierInputTextarea
        label="Informações Complementares"
        id="infoCompl"
        v-model="this.notaFiscal.infoCompl"
        :disabled="!this.notaFiscal.permiteSalvar"
      />
    </div>

    <button
      type="submit"
      v-show="false"
      v-if="this.notaFiscal.permiteSalvar"
      @click="this.$emit('submitForm')"
    ></button>
  </CrosierFormS>
</template>

<script>
import axios from "axios";
import { mapGetters, mapMutations, mapActions } from "vuex";
import {
  CrosierCalendar,
  CrosierDropdown,
  CrosierDropdownUf,
  CrosierFormS,
  CrosierInputCep,
  CrosierInputCpfCnpj,
  CrosierInputEmail,
  CrosierInputInt,
  CrosierInputTelefone,
  CrosierInputText,
  CrosierInputTextarea,
  SetFocus,
} from "crosier-vue";
import Cancelamento from "./cancelamento.vue";

export default {
  components: {
    CrosierFormS,
    CrosierDropdown,
    CrosierInputText,
    CrosierInputInt,
    CrosierInputCpfCnpj,
    CrosierInputTextarea,
    CrosierInputTelefone,
    CrosierInputEmail,
    CrosierInputCep,
    CrosierDropdownUf,
    CrosierCalendar,
    Cancelamento,
  },

  methods: {
    ...mapMutations(["setLoading", "setNotaFiscal", "setNotaFiscalErrors"]),
    ...mapActions(["loadData"]),

    async consultarDestinatario() {
      this.setLoading(true);
      // /api/fis/notaFiscal/consultarCNPJ
      const rs = await axios.get(
        `/api/fis/notaFiscal/consultarCNPJ?cnpj=${this.notaFiscal.documentoDestinatario}&uf=${this.notaFiscal.estadoDestinatario}`
      );
      if (rs?.data?.dados) {
        this.notaFiscal.xNomeDestinatario = rs.data.dados.razaoSocial[0];
        this.notaFiscal.inscricaoEstadualDestinatario = rs.data.dados.IE[0];
        this.notaFiscal.cepDestinatario = rs.data.dados.CEP[0];
        this.notaFiscal.logradouroDestinatario = rs.data.dados.logradouro[0];
        this.notaFiscal.numeroDestinatario = rs.data.dados.numero[0];
        this.notaFiscal.bairroDestinatario = rs.data.dados.bairro[0];
        this.notaFiscal.cidadeDestinatario = rs.data.dados.cidade[0];
        this.notaFiscal.estadoDestinatario = rs.data.dados.UF[0];
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

    consultaCep(rs) {
      if (rs) {
        this.setNotaFiscal({
          ...this.notaFiscal,
          ...{
            bairroDestinatario: rs?.bairro,
            cepDestinatario: rs?.cep,
            cidadeDestinatario: rs?.localidade,
            estadoDestinatario: rs?.uf,
            logradouroDestinatario: rs?.logradouro,
          },
        });
        SetFocus("numeroDestinatario");
      }
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

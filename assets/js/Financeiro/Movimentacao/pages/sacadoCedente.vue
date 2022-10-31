<template>
  <div class="form-row" v-if="!this.fields.categoria && !this.fields?.fatura?.id">
    <div class="col-md-6">
      <div class="form-group">
        <label>Sacado</label>
        <div class="input-group">
          <Skeleton class="form-control" height="2rem" />
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label>Cedente</label>
        <div class="input-group">
          <Skeleton class="form-control" height="2rem" />
        </div>
      </div>
    </div>
  </div>

  <div
    class="form-row"
    v-if="
      this.fields.categoria && this.fields.categoria.codigoSuper === 1 && !this.fields?.fatura?.id
    "
  >
    <!-- Em um RECEBIMENTO, o sacado é um terceiro pagando para uma das filiais (cedente) -->
    <CrosierDropdown
      col="5"
      v-model="this.cedente"
      :options="this.filiais"
      :optionValue="null"
      @update:modelValue="this.updateDropdownCedente"
      label="Cedente"
      id="dd_cedente"
      helpText="Quem recebe o valor"
    />

    <CrosierInputCpfCnpj
      col="2"
      label="Sacado (CPF/CNPJ)"
      v-model="this.fields.sacadoDocumento"
      @blur="this.pesquisarSacadoOuCedentePorDocumento('sacado')"
      helpText="Quem paga o valor"
      ref="sacadoDocumento"
    />

    <CrosierAutoComplete
      label="..."
      id="ac_sacado"
      col="5"
      v-model="this.sacado"
      :values="this.sacadosOuCedentes"
      @complete="this.pesquisarSacadoOuCedenteAc($event, 'sacado')"
      @itemSelect="this.onChangeSacado"
      field="id"
    >
      <template #item="r"> {{ r.item.text }}</template>
    </CrosierAutoComplete>
  </div>

  <div
    class="form-row"
    v-if="
      this.fields.categoria && this.fields.categoria.codigoSuper === 2 && !this.fields?.fatura?.id
    "
  >
    <!-- Em um PAGAMENTO, o sacado é uma das filiais pagando para um terceiro (cedente) -->

    <CrosierInputCpfCnpj
      col="2"
      label="Cedente (CPF/CNPJ)"
      v-model="this.fields.cedenteDocumento"
      @blur="this.pesquisarSacadoOuCedentePorDocumento('cedente')"
      helpText="Quem recebe o valor"
      ref="cedenteDocumento"
    />

    <CrosierAutoComplete
      label="..."
      id="ac_cedente"
      col="5"
      v-model="this.cedente"
      :values="this.sacadosOuCedentes"
      @complete="this.pesquisarSacadoOuCedenteAc($event, 'cedente')"
      @itemSelect="this.onChangeCedente"
      field="id"
    >
      <template #item="r"> {{ r.item.text }}</template>
    </CrosierAutoComplete>

    <CrosierDropdown
      col="5"
      v-model="this.sacado"
      @update:modelValue="this.updateDropdownSacado"
      :options="this.filiais"
      :optionValue="null"
      label="Sacado"
      id="sacado"
      helpText="Quem paga o valor"
    />
  </div>
</template>

<script>
import Skeleton from "primevue/skeleton";
import { CrosierAutoComplete, CrosierInputCpfCnpj, CrosierDropdown } from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import axios from "axios";
import moment from "moment";

export default {
  components: {
    CrosierDropdown,
    CrosierAutoComplete,
    CrosierInputCpfCnpj,
    Skeleton,
  },

  data() {
    return {
      sacado: null,
      cedente: null,
      sacadosOuCedentes: null,
      filiais: null,

      // campos para marcar se deve fazer a pesquisa novamente ou não
      sacadoDocumentoLocal: null,
      cedenteDocumentoLocal: null,
    };
  },

  async mounted() {
    await this.loadFiliais();
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    moment(date) {
      return moment(date);
    },

    async loadFiliais() {
      this.setLoading(true);
      try {
        const rs = await axios.get("/api/fin/movimentacao/filiais/", {
          headers: {
            "Content-Type": "application/ld+json",
          },
          validateStatus(status) {
            return status < 500;
          },
        });
        if (rs?.data?.RESULT === "OK") {
          this.filiais = rs.data.DATA;
        } else {
          console.error(rs?.data?.MSG);
          this.$toast.add({
            severity: "error",
            summary: "Erro",
            detail: rs?.data?.MSG,
            life: 5000,
          });
        }
      } catch (e) {
        console.error(e);
      }
      this.setLoading(false);
    },

    async pesquisarSacadoOuCedenteAc(event, campo) {
      this.setLoading(true);
      try {
        const response = await axios.get(
          `/api/fin/movimentacao/findSacadoOuCedente/?term=${event.query}`
        );

        if (response.status === 200) {
          this.sacadosOuCedentes = response.data.DATA;
          if (this.sacadosOuCedentes.length === 0) {
            this.fields[`${campo}Nome`] = this[campo];
          }
        }
      } catch (err) {
        console.error(err);
      }
      this.setLoading(false);
    },

    async pesquisarSacadoOuCedentePorDocumento(campo) {
      this.setLoading(true);
      if (
        !this[`${campo}DocumentoLocal`] ||
        (this[`${campo}DocumentoLocal`] &&
          this[`${campo}DocumentoLocal`].replace(/\D/g, "") !==
            this.fields[`${campo}Documento`].replace(/\D/g, ""))
      ) {
        try {
          const documento = this.fields[`${campo}Documento`];
          if (documento) {
            const response = await axios.get(
              `/api/fin/movimentacao/findSacadoOuCedente/?term=${documento.replace(/\D/g, "")}`
            );

            if (response.status === 200 && response.data?.DATA[0]?.documento) {
              this.fields[`${campo}Documento`] = this.formataCpfCnpj(
                response.data.DATA[0].documento
              );
              this.fields[`${campo}Nome`] = response.data.DATA[0].nome;
            }
            this[`${campo}DocumentoLocal`] = documento;
          }
        } catch (err) {
          console.error(err);
        }
      }
      this.setLoading(false);
    },

    onChangeSacado() {
      this.$nextTick(() => {
        this.fields.sacadoDocumento = this.formataCpfCnpj(this.sacado.documento);
        this.fields.sacadoNome = this.sacado.nome;
        this.sacado = null;
      });
    },

    onChangeCedente() {
      this.$nextTick(() => {
        this.fields.cedenteDocumento = this.formataCpfCnpj(this.cedente.documento);
        this.fields.cedenteNome = this.cedente.nome;
        this.cedente = null;
      });
    },

    updateDropdownCedente() {
      this.fields.cedenteDocumento = this.cedente?.cnpj;
      this.fields.cedenteNome = this.cedente?.nome_fantasia;
    },

    updateDropdownSacado() {
      this.fields.sacadoDocumento = this.sacado?.cnpj;
      this.fields.sacadoNome = this.sacado?.nome_fantasia;
    },

    formataCpfCnpj(v) {
      if (v.length === 14) {
        return v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
      }
      if (v.length === 11) {
        return v.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
      }
      return v;
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", fieldsErrors: "getFieldsErrors" }),
  },

  watch: {
    // eslint-disable-next-line func-names
    "$store.state.fields.sacadoDocumento": function (n) {
      if (n && !this.sacadoDocumentoLocal) {
        this.sacadoDocumentoLocal = n;
      }
      if (this.fields.categoria && this.fields.categoria.codigoSuper === 2) {
        // campos filiais para sacado
        if (n && this.filiais) {
          this.sacado = this.filiais.find(
            (e) => e.cnpj.replace(/\D/g, "") === this.fields.sacadoDocumento.replace(/\D/g, "")
          );
        }
      }
    },

    // eslint-disable-next-line func-names
    "$store.state.fields.sacadoNome": function (n) {
      if (this.fields.categoria && this.fields.categoria.codigoSuper === 1) {
        // campo autocomplete para sacado
        if (n && !this.sacado) {
          this.sacado = this.fields.sacadoNome;
        }
      }
    },

    // eslint-disable-next-line func-names
    "$store.state.fields.cedenteDocumento": function (n) {
      if (n && !this.cedenteDocumentoLocal) {
        this.cedenteDocumentoLocal = n;
      }
      if (this.fields.categoria && this.fields.categoria.codigoSuper === 1) {
        if (n && this.filiais) {
          this.cedente = this.filiais.find(
            (e) => e.cnpj.replace(/\D/g, "") === this.fields.cedenteDocumento.replace(/\D/g, "")
          );
        }
      }
    },

    // eslint-disable-next-line func-names
    "$store.state.fields.cedenteNome": function (n) {
      if (this.fields.categoria && this.fields.categoria.codigoSuper === 2) {
        // campo autocomplete para cedente
        if (n && !this.cedente) {
          this.cedente = this.fields.cedenteNome;
        }
      }
    },

    // eslint-disable-next-line func-names
    "$store.state.fields.categoria": function (n, v) {
      if (v && n?.id !== v?.id) {
        this.fields.sacadoDocumento = null;
        this.fields.sacadoNome = null;
        this.fields.cedenteDocumento = null;
        this.fields.cedenteNome = null;
        this.cedente = null;
        this.cedenteDocumentoLocal = null;
        this.sacado = null;
        this.sacadoDocumentoLocal = null;
      }
    },
  },
};
</script>

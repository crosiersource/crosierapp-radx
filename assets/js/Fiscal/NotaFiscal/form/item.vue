<template>
  <Dialog
    position="top"
    style="margin-top: 80px"
    v-model:visible="this.$store.state.exibirDialogItem"
    :style="{ width: '55vw' }"
    modal
    ref="dialog"
  >
    <template #header>
      <div class="w-100">
        <div class="row">
          <div class="col-6">
            <h3>Item</h3>
          </div>
        </div>
      </div>
    </template>

    <CrosierFormS @submitForm="this.submitForm" withoutCard semBotaoSalvar>
      <div class="form-row">
        <CrosierInputText
          col="3"
          id="codigo"
          v-model="this.item.codigo"
          :error="this.errors.codigo"
          label="Código"
          :disabled="!this.notaFiscal.permiteSalvar"
        />

        <CrosierInputText
          col="9"
          id="descricao"
          v-model="this.item.descricao"
          :error="this.errors.descricao"
          label="Descrição"
          :disabled="!this.notaFiscal.permiteSalvar"
        />
      </div>

      <div class="form-row">
        <CrosierInputDecimal
          col="2"
          id="qtde"
          v-model="this.item.qtde"
          :error="this.errors.qtde"
          label="Qtde"
          :disabled="!this.notaFiscal.permiteSalvar"
        />

        <CrosierCurrency
          col="2"
          id="valorUnit"
          v-model="this.item.valorUnit"
          :error="this.errors.valorUnit"
          label="Valor Unit"
          :disabled="!this.notaFiscal.permiteSalvar"
        />

        <CrosierCurrency
          col="2"
          id="valorUnit"
          v-model="this.item.valorDesconto"
          label="Desconto"
          :disabled="!this.notaFiscal.permiteSalvar"
        />

        <CrosierCurrency
          col="3"
          id="subtotal"
          v-model="this.item.subtotal"
          label="Subtotal"
          disabled
        />

        <CrosierCurrency
          col="3"
          id="total"
          v-model="this.valorTotal"
          :error="this.errors.valorTotal"
          label="Valor Total"
          disabled
        />
      </div>

      <div class="form-row">
        <CrosierInputText
          col="4"
          id="unidade"
          v-model="this.item.unidade"
          :error="this.errors.unidade"
          label="Unidade"
          :disabled="!this.notaFiscal.permiteSalvar"
        />

        <CrosierInputText
          col="4"
          id="cfop"
          v-model="this.item.cfop"
          :error="this.errors.cfop"
          label="CFOP"
          :disabled="!this.notaFiscal.permiteSalvar"
        />

        <CrosierInputText
          col="4"
          id="cfop"
          v-model="this.item.ncm"
          :error="this.errors.ncm"
          label="NCM"
          :disabled="!this.notaFiscal.permiteSalvar"
        />
      </div>

      <div class="form-row">
        <CrosierInputText
          col="4"
          id="cest"
          v-model="this.item.cest"
          label="CEST"
          :disabled="!this.notaFiscal.permiteSalvar"
          helpText="Código Especificador da Substituição Tributária"
        />

        <CrosierInputText
          col="4"
          id="cst"
          v-model="this.item.cst"
          :error="this.errors.cst"
          label="CST"
          :disabled="!this.notaFiscal.permiteSalvar"
          helpText="Código da Situação Tributária"
        />

        <CrosierInputText
          col="4"
          id="csosn"
          v-model="this.item.csosn"
          :error="this.errors.csosn"
          label="CSOSN"
          :disabled="!this.notaFiscal.permiteSalvar"
          helpText="Código de Situação da Operação - Simples Nacional"
        />
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">ICMS</h5>

          <div class="form-row">
            <CrosierCurrency
              col="3"
              id="icmsValorBc"
              v-model="this.item.icmsValorBc"
              label="Valor - Base Cálculo"
              :disabled="!this.notaFiscal.permiteSalvar"
            />

            <CrosierPercent
              col="2"
              id="icmsAliquota"
              v-model="this.item.icmsAliquota"
              label="Alíquota"
              :disabled="!this.notaFiscal.permiteSalvar"
            />

            <CrosierDropdown
              col="4"
              id="icmsModBC"
              v-model="this.item.icmsModBC"
              :options="[
                { label: '0 - Margem Valor Agregado (%)', value: 0 },
                { label: '1 - Pauta (Valor)', value: 1 },
                { label: '2 - Preço Tabelado Máx. (valor)', value: 2 },
                { label: '3 - Valor da operação', value: 3 },
              ]"
              label="Modalidade BC"
              :disabled="!this.notaFiscal.permiteSalvar"
              helpText="Modalidade de determinação da BC do ICMS"
            />

            <CrosierCurrency
              col="3"
              id="icmsValor"
              v-model="this.item.icmsValor"
              label="Valor"
              :disabled="!this.notaFiscal.permiteSalvar"
            />
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">PIS</h5>

          <div class="form-row">
            <CrosierCurrency
              col="4"
              id="pisValorBc"
              v-model="this.item.pisValorBc"
              label="Valor - Base Cálculo"
              :disabled="!this.notaFiscal.permiteSalvar"
            />

            <CrosierPercent
              col="4"
              id="pisAliquota"
              v-model="this.item.pisAliquota"
              label="Alíquota"
              :disabled="!this.notaFiscal.permiteSalvar"
            />

            <CrosierCurrency
              col="4"
              id="pisValor"
              v-model="this.item.pisValor"
              label="Valor"
              :disabled="!this.notaFiscal.permiteSalvar"
            />
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">COFINS</h5>

          <div class="form-row">
            <CrosierCurrency
              col="4"
              id="cofinsValorBc"
              v-model="this.item.cofinsValorBc"
              label="Valor - Base Cálculo"
              :disabled="!this.notaFiscal.permiteSalvar"
            />

            <CrosierPercent
              col="4"
              id="cofinsAliquota"
              v-model="this.item.cofinsAliquota"
              label="Alíquota"
              :disabled="!this.notaFiscal.permiteSalvar"
            />

            <CrosierCurrency
              col="4"
              id="cofinsValor"
              v-model="this.item.cofinsValor"
              label="Valor"
              :disabled="!this.notaFiscal.permiteSalvar"
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
  </Dialog>
</template>

<script>
import Dialog from "primevue/dialog";
import { mapActions, mapGetters, mapMutations } from "vuex";
import {
  submitForm,
  CrosierFormS,
  CrosierInputText,
  CrosierCurrency,
  CrosierInputDecimal,
  CrosierDropdown,
  CrosierPercent,
} from "crosier-vue";

export default {
  name: "item",

  components: {
    Dialog,
    CrosierFormS,
    CrosierInputDecimal,
    CrosierInputText,
    CrosierCurrency,
    CrosierDropdown,
    CrosierPercent,
  },

  async mounted() {},

  methods: {
    ...mapMutations(["setLoading", "setNotaFiscalItem", "setNotaFiscalItemErrors"]),

    ...mapActions(["loadData"]),

    async submitForm() {
      this.setLoading(true);

      const rs = await submitForm({
        apiResource: "/api/fis/notaFiscalItem",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "notaFiscalItem",
        $toast: this.$toast,
        setUrlId: false,
        fnBeforeSave: (formData) => {
          formData.notaFiscal = this.notaFiscal["@id"];
          delete formData.ncmExistente;
        },
      });

      if ([200, 201].includes(rs?.status)) {
        this.loadData();
        this.$store.state.dtItensKey++;
        this.$store.state.exibirDialogItem = false;
      }

      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({
      loading: "isLoading",
      notaFiscal: "getNotaFiscal",
      item: "getNotaFiscalItem",
      errors: "getNotaFiscalItemErrors",
    }),

    valorTotal() {
      return this.item.qtde * this.item.valorUnit;
    },
  },
};
</script>

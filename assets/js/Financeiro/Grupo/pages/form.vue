<template>
  <Toast position="bottom-right" class="mb-5" />

  <CrosierFormS @submitForm="this.submitForm" titulo="Grupo de Movimentação">
    <div class="form-row">
      <CrosierInputInt label="Id" col="3" id="id" v-model="this.fields.id" disabled />

      <CrosierInputText
        label="Descrição"
        col="9"
        id="descricao"
        v-model="this.fields.descricao"
        :error="this.formErrors.descricao"
      />
    </div>

    <div class="form-row">
      <CrosierInputInt
        label="Dia Vencto"
        col="4"
        id="diaVencto"
        v-model="this.fields.diaVencto"
        :error="this.formErrors.diaVencto"
        :min="1"
        :max="31"
      />

      <CrosierInputInt
        label="Dia Início"
        col="4"
        id="diaInicioAprox"
        v-model="this.fields.diaInicioAprox"
        :error="this.formErrors.diaInicioAprox"
        :min="1"
        :max="31"
        helpText="Informe o dia posterior ao dia de fechamento da fatura"
      />

      <CrosierDropdownBoolean label="Ativo" col="4" id="ativo" v-model="this.fields.ativo" />
    </div>

    <div class="form-row">
      <CrosierDropdownEntity
        col="5"
        v-model="this.fields.carteiraPagantePadrao"
        :error="this.formErrors.carteiraPagantePadrao"
        :filters="{ atual: true, concreta: true }"
        entity-uri="/api/fin/carteira"
        optionLabel="descricaoMontada"
        :optionValue="null"
        :orderBy="{ codigo: 'ASC' }"
        label="Carteira Pagante Padrão"
        id="carteiraPagantePadrao"
      />

      <CrosierDropdownEntity
        col="7"
        v-model="this.fields.categoriaPadrao"
        entity-uri="/api/fin/categoria"
        optionLabel="descricaoMontadaTree"
        :optionValue="null"
        :orderBy="{ codigoOrd: 'ASC' }"
        label="Categoria Padrão"
        id="categoriaPadrao"
      />
    </div>

    <DataTable
      sortField="dtVencto"
      :sortOrder="-1"
      class="p-datatable-sm p-datatable-striped"
      :value="this.fields.itens"
      :paginator="false"
      resizableColumns
      columnResizeMode="fit"
      responsiveLayout="scroll"
      ref="dt"
      rowHover
    >
      <Column field="id" header="Id">
        <template #body="r">
          {{ ("00000000" + r.data.id).slice(-8) }}
        </template>
      </Column>

      <Column field="descricao" header="Descrição"></Column>

      <Column field="valorLanctos" header="Total" sortable>
        <template #body="r">
          <div class="text-right">
            {{
              parseFloat(r.data.valorLanctos ?? 0).toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL",
              })
            }}
          </div>
        </template>
      </Column>
    </DataTable>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import * as yup from "yup";
import {
  CrosierFormS,
  submitForm,
  CrosierDropdownBoolean,
  CrosierInputText,
  CrosierInputInt,
  CrosierDropdownEntity,
  SetFocus,
} from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdownBoolean,
    CrosierInputText,
    CrosierInputInt,
    CrosierDropdownEntity,
    DataTable,
    Column,
  },

  data() {
    return {
      criarVincularFields: false,
      schemaValidator: {},
    };
  },

  async mounted() {
    this.setLoading(true);

    await this.$store.dispatch("loadData");
    this.schemaValidator = yup.object().shape({
      descricao: yup.string().required().typeError(),
      diaVencto: yup.number().required().typeError(),
      diaInicioAprox: yup.number().required().typeError(),
      ativo: yup.boolean().required().typeError(),
      carteiraPagantePadrao: yup.mixed().required().typeError(),
    });

    SetFocus("descricao", 40);

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/grupo",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        fnBeforeSave: (formData) => {
          if (formData?.itens) {
            delete formData.itens;
          }
          formData.categoriaPadrao =
            formData.categoriaPadrao && "@id" in formData.categoriaPadrao
              ? formData.categoriaPadrao["@id"]
              : null;
          formData.carteiraPagantePadrao =
            formData.carteiraPagantePadrao && "@id" in formData.carteiraPagantePadrao
              ? formData.carteiraPagantePadrao["@id"]
              : null;
        },
      });
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),
  },
};
</script>

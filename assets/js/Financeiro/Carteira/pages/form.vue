<template>
  <Toast class="mt-5" />
  <CrosierFormS listUrl="/fin/carteira/list" @submitForm="this.submitForm" titulo="Carteiras">
    <div class="form-row">
      <div class="col-md-2">
        <label for="id">ID</label>
        <InputText class="form-control" id="id" type="text" v-model="this.fields.id" disabled />
      </div>
      <div class="col-md-7">
        <label for="name">Nome do recurso</label>
        <InputText
          :class="'form-control ' + (this.formErrors['nome'] ? 'is-invalid' : '')"
          id="nome"
          type="text"
          v-model="this.fields.nome"
        />
        <div class="invalid-feedback">
          {{ this.formErrors["nome"] }}
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label for="status">Status</label>
          <Dropdown
            :class="'form-control ' + (this.formErrors['ativo'] ? 'is-invalid' : '')"
            id="status"
            inputId="status"
            v-model="this.fields.ativo"
            :options="[
              { name: 'Ativo', value: true },
              { name: 'Inativo', value: false },
            ]"
            optionLabel="name"
            optionValue="value"
            placeholder="Selecione o ativo"
          />
          <div class="invalid-feedback">
            {{ this.formErrors["ativo"] }}
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-4">
      <div class="col-md-8">
        <div class="form-group">
          <label for="cor">Cor de identificação</label>
          <br />
          <ColorPicker id="cor" v-model="this.fields.cor" />
        </div>
      </div>
      <div class="col-md-4" v-show="this.fields.id">
        <div class="form-group" v-show="this.fields?.fields?.descricaoMontada">
          <label for="fields">Fields</label>
          <InputText
            v-if="this.fields?.fields?.descricaoMontada"
            class="form-control"
            id="fields"
            type="text"
            v-model="this.fields.fields.descricaoMontada"
            disabled="disabled"
          />
        </div>
        <div v-if="!this.fields?.fields?.descricaoMontada" class="text-right">
          <Checkbox class="mt-4" v-model="this.criarVincularFields" :binary="true" />
          <span> Criar e Vincular Fields</span>
        </div>
      </div>
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import InputText from "primevue/inputtext";
import ColorPicker from "primevue/colorpicker";
import Dropdown from "primevue/dropdown";
import Checkbox from "primevue/checkbox";
import * as yup from "yup";
import { CrosierFormS, submitForm } from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    Toast,
    ColorPicker,
    CrosierFormS,
    Checkbox,
    InputText,
    Dropdown,
  },

  data() {
    return {
      criarVincularFields: false,
      schemaValidator: {},
      validDate: new Date(),
    };
  },

  async mounted() {
    this.setLoading(true);

    this.$store.dispatch("loadData");
    this.schemaValidator = yup.object().shape({
      nome: yup
        .string()
        .required("Nome é um campo obrigatório.")
        .typeError("Digite um nome válido."),
      ativo: yup
        .boolean()
        .required("Status é um campo obrigatório.")
        .typeError("Selecione uma opção."),
    });
    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/fin/carteira",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
      });
      this.setLoading(false);
    },
  },
  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),
  },
};
</script>

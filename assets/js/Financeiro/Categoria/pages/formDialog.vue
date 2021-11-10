<template>
  <Toast position="bottom-right" class="mt-5" />
  <Dialog
    :breakpoints="{ '960px': '75vw', '640px': '100vw' }"
    :style="{ width: '50vw' }"
    header="Categoria"
    v-model:visible="this.$store.state.exibeDialog"
    :modal="true"
  >
    <CrosierFormS @submitForm="this.submitForm" titulo="Categoria" :withoutCard="true">
      <div class="form-row">
        <CrosierInputText
          :disabled="true"
          v-if="this.fields.pai"
          label="Pai"
          col="12"
          id="pai"
          v-model="this.fields.pai.descricaoMontada"
        />

        <CrosierInputText
          :disabled="true"
          v-if="!this.fields.pai"
          label="Código"
          col="4"
          id="codigo"
          v-model="this.fields.codigo"
        />

        <div class="col-md-4" v-if="this.fields.pai">
          <div class="form-group">
            <label :for="this.id">Código</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">{{ this.fields.pai.codigoM }}.</span>
              </div>
              <InputMask
                class="form-control"
                v-model="this.fields.codigo"
                :mask="this.fields.pai.mascaraDoFilho"
              />
              <div class="invalid-feedbackk blink" v-show="this.fieldsErrors?.codigo">
                {{ this.fieldsErrors?.codigo }}
              </div>
            </div>
          </div>
        </div>

        <CrosierInputText
          label="Descrição"
          col="8"
          id="descricao"
          v-model="this.fields.descricao"
        />
      </div>
    </CrosierFormS>
  </Dialog>
</template>

<script>
import Toast from "primevue/toast";
import Dialog from "primevue/dialog";
import InputMask from "primevue/inputmask";
import * as yup from "yup";
import { CrosierFormS, CrosierInputText, submitForm } from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierInputText,
    Dialog,
    InputMask,
  },

  emits: ["dataSaved"],

  data() {
    return {
      schemaValidator: {},
    };
  },

  async mounted() {
    this.setLoading(true);

    this.schemaValidator = yup.object().shape({
      codigo: yup.number().required().typeError(),
      descricao: yup.string().required().typeError(),
    });

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    async submitForm() {
      this.setLoading(true);
      if (
        await submitForm({
          apiResource: "/api/fin/categoria",
          schemaValidator: this.schemaValidator,
          $store: this.$store,
          formDataStateName: "fields",
          $toast: this.$toast,
          fnBeforeSave: (formData) => {
            if (formData.id) {
              formData["@id"] = `/api/fin/categoria/${formData.id}`;
            }
            if (formData.pai) {
              formData.codigo = `${formData.pai.codigo}${formData.codigo}`;
              formData.pai = `/api/fin/categoria/${formData.pai.id}`;
            }
            formData.codigo = formData.codigo.replace(/\D/g, "");
          },
        })
      ) {
        this.$store.state.exibeDialog = false;
        this.$emit("dataSaved");
      }
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),
  },
};
</script>

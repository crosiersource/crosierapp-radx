<template>
  <div class="card-header">
    <div class="d-flex flex-wrap align-items-center">
      <div class="mr-1">
        <h3>{{ this.titulo }}</h3>
      </div>
    </div>
  </div>
  <div class="card-body">
    <slot name="lista"></slot>
    <hr />
    <slot name="form"></slot>
    <ProgressBar
      mode="indeterminate"
      :style="'height: .5em; margin-bottom: 10px; display: ' + (desabilitado ? '' : 'none')"
    />
    <form @submit.prevent="this.$emit('handleSubmitFormChild')">
      <fieldset :disabled="desabilitado">
        <slot></slot>
        <div class="row mt-3">
          <div class="col text-right">
            <Button label="Salvar" type="submit" icon="fas fa-save" />
          </div>
        </div>
      </fieldset>
    </form>
  </div>
</template>

<script>
import Button from "primevue/button";
import ProgressBar from "primevue/progressbar";
import { postEntityData } from "@/services/ApiPostService";
import { putEntityData } from "@/services/ApiPutService";
import { fetchTableData } from "@/services/ApiDataFetchService";

export default {
  name: "CrosierForm",
  components: { Button, ProgressBar },
  props: {
    titulo: {
      type: String,
      required: true,
    },
    storeNameEntity: {
      type: String,
      required: true,
    },
    apiResource: {
      type: String,
      required: true,
    },
    apiResourceFather: {
      type: String,
      required: true,
    },
    schemaValidator: {
      type: Object,
      required: true,
    },
    storeNameErrors: {
      type: String,
      required: true,
    },
    columns: [],
    setValues: [],
  },
  data() {
    return {
      formErrors: {},
      formErrorsChilds: {},
      desabilitado: false,
      entityId: null,
    };
  },
  async mounted() {
    this.desabilitado = true;
    // verify if id is set.
    // and if found the id then set the register in the store
    // the stored fields are reflected in the form
    // else if not found then redirected to empty form
    const uri = window.location.search.substring(1);
    const params = new URLSearchParams(uri);
    this.entityId = params.get("id");
    if (this.entityId) {
      try {
        const response = await fetchTableData({
          apiResource: `${this.apiResource}/${this.entityId}`,
        });
        if (response.data.id) {
          this.$store.commit("setFormChilds", {
            child: this.storeNameEntity,
            newFormChilds: response.data,
          });
        } else {
          throw new Error("Id não encontrado.");
        }
      } catch (err) {
        console.log(err);
      }
    }
    this.desabilitado = false;
  },
  methods: {
    async submitForm() {
      this.desabilitado = true;
      // local const values receive stored fields that are prefetched or that is typed by users
      // formErrors are setted empty to clean.
      // call yup validator, and if is valid than make an api call (post or put, depedding of id is setted or not)
      // if response ok than store the values of response and redirect to edit form with id setted in the url
      // if yup validation fails then set the errors on store (that is auto reflected in the form)
      const values = this.$store.state.formChilds[this.storeNameEntity];
      this.formErrors = [];

      this.$store.commit("setFormErrorsChilds", {
        child: this.storeNameErrors,
        newFormErrorsChilds: this.formErrorsChilds[this.storeNameErrors],
      });

      try {
        const validated = await this.schemaValidator.validate(values, {
          abortEarly: false,
        });

        let response;
        if (values.id) {
          response = await putEntityData(
            `${this.apiResource}/${values.id}`,
            JSON.stringify(validated)
          );
        } else {
          response = await postEntityData(this.apiResource, JSON.stringify(validated));
        }
        if ([200, 201].includes(response.status)) {
          this.$store.commit("setFormFields", response.data);

          const newResponse = await fetchTableData({
            apiResource: `${this.apiResourceFather}`,
          });

          if (newResponse.data.id) {
            this.$store.commit("setFormFields", newResponse.data);
          } else {
            throw new Error("Id não encontrado.");
          }

          this.$store.commit("setFormChild", {
            child: this.storeNameEntity,
            newFormChild: {},
          });

          this.showSuccess("Salvo com sucesso!");
        }
      } catch (err) {
        this.formErrorsChilds = {};

        err.inner?.forEach((element) => {
          this.formErrorsChilds[element.path] = element.message ?? "Valor inválido";
        });

        this.$store.commit("setFormErrorsChilds", {
          child: this.storeNameErrors,
          newFormErrorsChilds: this.formErrorsChilds,
        });

        this.showError("Não foi possível salvar!");
      }
      this.desabilitado = false;
    },
    handleEdit(child, data) {
      this.$store.state.formChilds[child] = data;
    },
    redirectForm(id = "") {
      window.location.href = `form${id ? `?id=${id}` : ""}`;
    },
    redirectList() {
      window.location.href = this.listUrl;
    },
    showSuccess(message) {
      this.$toast.add({
        severity: "success",
        summary: "Mensagem de sucesso",
        detail: message,
        life: 3000,
      });
    },
    showError(message) {
      this.$toast.add({
        severity: "error",
        summary: "Mensagem de erro",
        detail: message,
        life: 3000,
      });
    },
  },
};
</script>

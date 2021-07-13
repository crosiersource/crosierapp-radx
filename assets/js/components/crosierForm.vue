<template>
  <CrosierBlock :loading="this.desabilitado || this.parentLoad" />
  <div v-if="this.withoutCard">
    <form @submit.prevent="this.submitForm">
      <fieldset :disabled="desabilitado || parentLoad">
        <slot></slot>
        <slot name="formChilds"></slot>
        <div class="row mt-3" v-if="!this.withoutSaveButton">
          <div class="col text-right">
            <Button
              style="width: 12rem"
              label="Salvar"
              type="submit"
              icon="fas fa-save"
              v-if="!this.disabledSubmit"
            />
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <div v-else>
    <div class="container">
      <div class="card" style="margin-bottom: 50px">
        <div class="card-header">
          <div class="d-flex flex-wrap align-items-center">
            <div class="mr-1">
              <h3>{{ titulo }}</h3>
              <h6 v-if="subtitulo">{{ this.subtitulo }}</h6>
            </div>
            <div class="d-sm-flex flex-nowrap ml-auto">
              <a
                v-show="this.formUrl"
                type="button"
                class="btn btn-info mr-2"
                :href="this.formUrl"
                title="Novo"
              >
                <i class="fas fa-file" aria-hidden="true"></i>
              </a>

              <a
                v-show="this.listUrl"
                role="button"
                class="btn btn-outline-secondary"
                :href="this.listUrl"
                title="Listar"
              >
                <i class="fas fa-list"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <ProgressBar
            mode="indeterminate"
            :style="
              'height: .5em; margin-bottom: 10px; display: ' +
              (desabilitado || parentLoad ? '' : 'none')
            "
          />
          <form @submit.prevent="this.submitForm">
            <fieldset :disabled="desabilitado || parentLoad">
              <slot></slot>
              <slot name="formChilds"></slot>
              <div class="row mt-3" v-if="!this.withoutSaveButton">
                <div class="col text-right">
                  <Button
                    style="width: 12rem"
                    label="Salvar"
                    type="submit"
                    icon="fas fa-save"
                    v-if="!this.disabledSubmit"
                  />
                </div>
              </div>
            </fieldset>
          </form>
        </div>
      </div>
    </div>
  </div>

  <Toast class="mt-5" />
</template>

<script>
import Button from "primevue/button";
import Toast from "primevue/toast";
import { postEntityData } from "@/services/ApiPostService";
import { putEntityData } from "@/services/ApiPutService";
import { fetchTableData } from "@/services/ApiDataFetchService";
import ProgressBar from "primevue/progressbar";
import CrosierBlock from "./crosierBlock";

export default {
  name: "CrosierForm",
  components: {
    CrosierBlock,
    Button,
    Toast,
    ProgressBar,
  },
  props: {
    titulo: {
      type: String,
      required: false,
    },
    subtitulo: {
      type: String,
      required: false,
    },
    listUrl: {
      type: String,
      required: false,
    },
    formUrl: {
      type: String,
      required: false,
    },
    apiResource: {
      type: String,
      required: true,
    },
    schemaValidator: {
      type: Object,
      required: false,
      default: null,
    },
    withoutCard: {
      type: Boolean,
      required: false,
      default: false,
    },
    withoutSaveButton: {
      type: Boolean,
      required: false,
      default: false,
    },
    notLoadOnMount: {
      type: Boolean,
      required: false,
      default: false,
    },
    getIdFromUrl: {
      type: Boolean,
      required: false,
      default: true,
    },
    entityId: {
      type: Number,
      required: false,
      default: null,
    },
    notSetUrlId: {
      type: Boolean,
      required: false,
      default: false,
    },
    disabledSubmit: {
      type: Boolean,
      required: false,
      default: false,
    },
    storeName: {
      type: String,
      required: false,
      default: "formFields",
    },
    hasDependents: {
      type: Boolean,
      required: false,
      default: false,
    },
    parentLoad: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      desabilitado: false,
    };
  },
  async mounted() {
    // Já inicializa o objeto dos erros com os atributos certos que o objeto contém
    this.$store.commit(this.commitFormErrors, {});

    this.desabilitado = true;

    if (!this.notLoadOnMount) {
      const uri = window.location.search.substring(1);
      const params = new URLSearchParams(uri);
      let id = null;
      if (this.getIdFromUrl) {
        id = params.get("id");
      } else {
        id = this.entityId;
      }
      if (id) {
        try {
          const response = await fetchTableData({
            apiResource: `${this.apiResource}/${id}}`,
          });
          if (response.data["@id"]) {
            const afterGet = this.$emit("afterGet", response.data);
            const data = afterGet || response.data;
            this.$store.commit(this.commitFormFields, data);
            if (this.hasDependents) {
              // se this.hasDependents === true, é necessário criar uma action chamada hasDependents
              await this.$store.dispatch("hasDependents", data);
            }
          } else {
            throw new Error("Id não encontrado.");
          }
        } catch (err) {
          console.log(err);
        }
      }
    }

    this.desabilitado = false;
  },
  computed: {
    formFields() {
      return this.$store.getters[this.storeName];
    },
    commitFormFields() {
      return `set${this.storeName.charAt(0).toUpperCase()}${this.storeName.slice(1)}`;
    },
    commitFormErrors() {
      return `set${this.storeName.charAt(0).toUpperCase()}${this.storeName.slice(1)}Errors`;
    },
  },
  methods: {
    async submitForm() {
      this.desabilitado = true;

      // tenta fazer a validação dos campos do yup,
      // caso algum não passe dispara um erro que é tratado no catch.
      try {
        this.$store.commit(this.commitFormErrors, []);

        let validated;
        if (this.schemaValidator) {
          validated = await this.schemaValidator.validate(this.formFields, {
            abortEarly: false,
          });
        } else {
          validated = this.formFields;
        }
        // verifica se o @id do formulário esta setado, se sim então a requisição é
        // put(atualização), senão:
        // post(criação).
        let response;
        const beforeSaved = this.$emit("beforeSave", validated);
        validated = beforeSaved || validated;
        if (this.formFields["@id"]) {
          response = await putEntityData(this.formFields["@id"], JSON.stringify(validated));
        } else {
          response = await postEntityData(this.apiResource, JSON.stringify(validated));
        }

        // caso o retorno da api seja de sucesso
        if ([200, 201].includes(response.status)) {
          // armazena os novos dados no store correspondente.

          const afterGet = this.$emit("afterGet", response.data);
          const data = afterGet || response.data;
          this.$store.commit(this.commitFormFields, data);

          if (this.hasDependents) {
            // se this.hasDependents === true, é necessário criar uma action chamada hasDependents
            await this.$store.dispatch("hasDependents", response.data);
          }

          // verifica se é necessário atualizar o id da url
          // só é necessário caso o formulário seja de apenas uma entidade
          // ou o formulário seja da entidade principal.
          if (!this.notSetUrlId) {
            window.history.pushState("form", "id", `?id=${response.data.id}`);
          }

          // emite a mensagem de sucesso.
          this.showSuccess("Registro salvo com sucesso!");

          // emite o evento de data saved e caso seja capturado pelo componente que montou o
          // crosierForm, esse pode atualizar os dados de acordo com o que foi retornado sem
          // precisar de uma nova requisição.
          this.$emit("dataSaved", response.data);
        }
      } catch (err) {
        console.log(err);
        console.log(err.inner);
        // em caso de não passar na validação do yup
        // percorremos os campos com erros do yup para obter a mensagem do erro,
        // caso exista, senão usamos a mensagem padrão.
        const errors = [];
        err.inner?.forEach((element) => {
          errors[element.path] = element.message ?? "Valor inválido";
        });

        this.$store.commit(this.commitFormErrors, errors);

        // emitimos a mensagem de erro.
        this.showError("Ocorreu um erro ao salvar!");
      }
      this.desabilitado = false;
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

<template>
  <CrosierBlock :loading="this.loading" />
  <div v-if="this.withoutCard">
    <form @submit.prevent="this.$emit('submitForm')">
      <fieldset :disabled="this.loading">
        <slot></slot>
        <div class="row mt-3" v-if="!this.semBotaoSalvar">
          <div class="col text-right">
            <button
              class="btn btn-sm btn-primary"
              style="width: 12rem"
              type="submit"
              v-if="!this.disabledSubmit"
            >
              <i class="fas fa-save"></i> Salvar
            </button>
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
            <div class="d-sm-flex flex-nowrap ml-auto"></div>
            <slot name="divCima"></slot>
            <div>
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

              <slot name="btns"></slot>
            </div>
          </div>
        </div>
        <div class="card-body">
          <form @submit.prevent="this.$emit('submitForm')">
            <fieldset :disabled="this.loading">
              <slot></slot>
              <div class="row mt-3" v-if="!this.semBotaoSalvar">
                <div class="col text-right">
                  <button
                    class="btn btn-sm btn-primary"
                    style="width: 12rem"
                    type="submit"
                    icon="fas fa-save"
                    v-if="!this.disabledSubmit"
                  >
                    <i class="fas fa-save"></i> Salvar
                  </button>
                </div>
              </div>
            </fieldset>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// import CrosierBlock from "./crosierBlock";
import { CrosierBlock } from "crosier-vue";

export default {
  name: "CrosierFormS",

  components: {
    CrosierBlock,
  },

  emits: ["submitForm"],

  props: {
    titulo: {
      type: String,
    },
    subtitulo: {
      type: String,
    },
    listUrl: {
      type: String,
      required: false,
      default: "list",
    },
    formUrl: {
      type: String,
      required: false,
      default: "form",
    },
    withoutCard: {
      type: Boolean,
      default: false,
    },
    semBotaoSalvar: {
      type: Boolean,
      default: false,
    },
    disabledSubmit: {
      type: Boolean,
      default: false,
    },
    parentLoad: {
      type: Boolean,
      default: false,
    },
  },

  computed: {
    loading() {
      return this.$store.getters.isLoading || this.parentLoad;
    },
  },
};
</script>

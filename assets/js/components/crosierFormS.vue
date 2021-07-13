<template>
  <CrosierBlock :loading="this.loading" />
  <div v-if="this.withoutCard">
    <form @submit.prevent="this.$emit('submitForm')">
      <fieldset :disabled="this.loading">
        <slot></slot>
        <slot name="formChilds"></slot>
        <div class="row mt-3" v-if="!this.semBotaoSalvar">
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
            :style="'height: .5em; margin-bottom: 10px; display: ' + (this.loading ? '' : 'none')"
          />
          <form @submit.prevent="this.$emit('submitForm')">
            <fieldset :disabled="this.loading">
              <slot></slot>
              <slot name="formChilds"></slot>
              <div class="row mt-3" v-if="!this.semBotaoSalvar">
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
import ProgressBar from "primevue/progressbar";
import CrosierBlock from "./crosierBlock";

export default {
  name: "CrosierFormS",
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
    withoutCard: {
      type: Boolean,
      required: false,
      default: false,
    },
    semBotaoSalvar: {
      type: Boolean,
      required: false,
      default: false,
    },
    disabledSubmit: {
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
  computed: {
    loading() {
      return this.$store.state.loading || this.parentLoad;
    },
  },
};
</script>

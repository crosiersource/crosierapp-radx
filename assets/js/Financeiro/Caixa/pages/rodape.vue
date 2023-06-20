<template>
  <div class="d-flex justify-content-between">
    <div>
      <button
        class="btn btn-outline-secondary ml-1"
        type="button"
        @click="this.$store.state.tipoMovimentacao = null"
      >
        <i class="fas fa-file"></i> Selecionar Tipo
      </button>
      <ToggleButton
        v-model="this.$store.state.exibirCampos.sacadoCedente"
        @click="this.salvarExibirCampos"
        onLabel="Esconder campos Sacado/Cedente"
        offLabel="Exibir campos Sacado/Cedente"
        offIcon="pi pi-check"
        onIcon="pi pi-times"
        class="ml-1"
      />
      <ToggleButton
        v-model="this.$store.state.exibirCampos.obs"
        @click="this.salvarExibirCampos"
        onLabel="Esconder campo Obs"
        offLabel="Exibir campo Obs"
        offIcon="pi pi-check"
        onIcon="pi pi-times"
        class="ml-1"
      />
    </div>

    <button class="btn btn-sm btn-primary" style="width: 12rem" type="submit">
      <i class="fas fa-save"></i> Salvar
    </button>
  </div>
</template>
<script>
import { mapActions } from "vuex";
import ToggleButton from "primevue/togglebutton";

export default {
  components: {
    ToggleButton,
  },
  methods: {
    ...mapActions(["salvarExibirCampos"]),
  },
};
</script>

<template>
  <ConfirmDialog />
  <Toast position="bottom-right" class="mt-5" />
  <button type="button" class="btn btn-danger m-5" @click="this.clearLocalStorage">
    <i class="fas fa-trash"></i> Limpar o Local Storage
  </button>
</template>

<script>
import ConfirmDialog from "primevue/confirmdialog";
import Toast from "primevue/toast";

export default {
  name: "cleanLocalStorage",
  components: {
    Toast,
    ConfirmDialog,
  },

  methods: {
    clearLocalStorage() {
      this.$confirm.require({
        acceptLabel: "Sim",
        rejectLabel: "Não",
        message: "Confirmar?",
        header: "Atenção!",
        icon: "pi pi-exclamation-triangle",
        accept: () => {
          localStorage.clear();
        },
      });
    },
  },
};
</script>

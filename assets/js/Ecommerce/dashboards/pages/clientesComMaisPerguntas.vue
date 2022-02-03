<template>
  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <div>
          <h4 class="card-title mb-0">Perguntas/Respostas</h4>
          <div class="small text-medium-emphasis">Últimos 7 dias</div>

          <ProgressSpinner />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ProgressSpinner from "primevue/progressspinner";

export default {
  name: "perguntasRespostas",

  components: { ProgressSpinner },

  data() {
    return {};
  },
};
</script>

<template>
  <div v-for="erro in erros" :key="erro" class="alert alert-danger" role="danger">
    <i class="fas fa-exclamation"></i> {{ erro }}.
  </div>
</template>
<script>
export default {
  name: "list_recorrente_erros",

  props: {
    erros: {
      type: Array,
      required: true,
    },
  },
};
</script>

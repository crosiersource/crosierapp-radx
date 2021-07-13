<template>
  <Calendar v-model="content" @input="handleInput" dateFormat="dd/mm/yy" />
</template>

<script>
import Calendar from "primevue/calendar";

export default {
  name: "CrosierCalendar",
  components: {
    Calendar,
  },
  props: {
    value: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      content: new Date(this.value),
    };
  },
  mounted() {
    if (this.valor) this.parsedData = new Date(this.valor);
  },
  methods: {
    handleInput(e) {
      console.log(e);
      this.$emit("input", this.content);
    },
  },
};
</script>

<style scoped></style>

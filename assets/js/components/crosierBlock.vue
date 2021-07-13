<template>
  <ProgressBar
    mode="indeterminate"
    :style="'height: .5em; margin-bottom: 10px; display: ' + (loading ? '' : 'none')"
  />
  <div v-show="loading" class="block" id="crosierBlockDiv"></div>
</template>

<script>
import ProgressBar from "primevue/progressbar";

export default {
  components: {
    ProgressBar,
  },
  props: {
    loading: {
      type: Boolean,
      required: true,
      default: true,
    },
  },
};
</script>

<style lang="scss" scoped>
.block {
  z-index: 999999;
  height: 100%;
  width: 100%;
  position: fixed;
  top: 0;
  left: 0;
  background: #333;
  opacity: 0.1;
}
</style>

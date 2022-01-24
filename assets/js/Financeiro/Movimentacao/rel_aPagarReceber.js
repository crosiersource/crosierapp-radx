import { createApp } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import primevueOptions from "crosier-vue/src/primevue.config.js";
import Page from "./pages/rel_aPagarReceber";
import "primeflex/primeflex.css";
import "primevue/resources/themes/saga-blue/theme.css"; // theme
import "primevue/resources/primevue.min.css"; // core css
import "primeicons/primeicons.css";

const app = createApp(Page);

app.use(PrimeVue, primevueOptions);

app.use(ToastService);

app.use(ConfirmationService);

app.directive("tooltip", Tooltip);

app.mount("#app");

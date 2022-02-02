<template>
  <div v-for="(mlConfig, i) in this.clienteConfig.jsonData.mercadolivre">
    <div class="card mt-3">
      <div class="card-body">
        <h5 class="card-title">Mercado Livre</h5>
        <h6 class="card-subtitle mb-2 text-muted">Configurações</h6>
        <div class="form-row mt-3">
          <CrosierInputText id="descricao" label="Descrição" v-model="mlConfig['descricao']" />
        </div>
        <div class="form-row mt-3">
          <div class="col-md-3">
            <div class="form-group">
              <label for="mercadolivre_token_tg">Token TG</label>
              <InputText
                class="form-control notuppercase"
                id="mercadolivre_token_tg"
                type="text"
                v-model="mlConfig['token_tg']"
              />
              <a
                :href="`https://auth.mercadolivre.com.br/authorization?response_type=code&client_id=1976314946902083&state=${this.clienteConfig.UUID}&redirect_uri=https://radx.crosier.conectamaisvc.com.br/api/ecommerce/clienteConfig/autorizarNoMercadoLivre?i=${i}`"
              >
                <small class="form-text">Autorizar</small>
              </a>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="mercadolivre_access_token">Access Token</label>
              <InputText
                class="form-control notuppercase"
                id="mercadolivre_access_token"
                type="text"
                v-model="mlConfig['access_token']"
              />
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="mercadolivre_refresh_token">Refresh Token</label>
              <InputText
                class="form-control notuppercase"
                id="mercadolivre_refresh_token"
                type="text"
                v-model="mlConfig['refresh_token']"
              />
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="mercadolivre_expira_em">Dt Expiração</label>
              <InputText
                class="form-control notuppercase"
                disabled="disabled"
                id="mercadolivre_expira_em"
                type="text"
                v-model="this.clienteConfig.mercadolivreExpiraEm"
              />
            </div>
          </div>
          <div class="col-md-2">
            <button
              type="button"
              v-if="mlConfig['token_tg'] && !mlConfig['refresh_token'] && !mlConfig['access_token']"
              class="btn btn-block btn-sm btn-danger mt-4"
              @click="this.reautorizarNoMercadoLivre"
            >
              <i class="fas fa-link"></i>
              Autorizar
            </button>

            <button
              type="button"
              v-if="mlConfig['token_tg'] && mlConfig['refresh_token'] && mlConfig['access_token']"
              class="btn btn-block btn-sm btn-warning mt-4"
              @click="this.renewAccessTokenMercadoLivre"
            >
              <i class="fas fa-sync-alt"></i>
              Refresh Token
            </button>
          </div>
        </div>
        <div class="form-row mt-3">
          <div class="col-md-3">
            <div class="form-group">
              <label for="mercadolivre_questions_offset">Questions Offset</label>
              <InputText
                class="form-control"
                id="mercadolivre_questions_offset"
                type="text"
                v-model="mlConfig.questions_offset"
              />
            </div>
          </div>
        </div>

        <div class="form-row mt-3">
          <div class="col-md-12 text-right">
            <button type="button" class="btn btn-sm btn-outline-danger" @click="this.remover(i)">
              <i class="far fa-trash-alt"></i> Remover
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="form-row mt-3">
    <div class="col-md-12 text-right">
      <button
        type="button"
        class="btn btn-sm btn-outline-primary"
        title="Adicionar mais uma configuração do Mercado Livre"
        @click="this.adicionar"
      >
        <i class="fas fa-plus"></i> Adicionar
      </button>
    </div>
  </div>
</template>

<script>
import InputText from "primevue/inputtext";
import axios from "axios";
import { CrosierInputText } from "crosier-vue";
import { mapGetters, mapMutations } from "vuex";

export default {
  components: {
    InputText,
    CrosierInputText,
  },

  data() {
    return {
      schemaValidator: {},
      validDate: new Date(),
    };
  },

  async mounted() {
    this.setLoading(true);
    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setNewClienteConfig", "setClienteConfig"]),

    async reautorizarNoMercadoLivre() {
      this.$confirm.require({
        message: "Confirmar operação?",
        header: "Confirmação",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          this.submitForm();
          const rs = await axios.get(
            // eslint-disable-next-line max-len
            `/api/ecommerce/clienteConfig/reautorizarNoMercadoLivre/${this.clienteConfig.id}?token_tg=${this.clienteConfig.jsonData.mercadolivre.token_tg}`,
            {
              validateStatus(status) {
                return status < 500;
              },
            }
          );
          if (rs?.data?.RESULT === "OK") {
            window.location = `form?id=${this.clienteConfig.id}`;
          } else {
            this.$toast.add({
              severity: "e rror",
              summary: "Erro",
              detail: "Erro ao autorizar no Mercado Livre",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },

    async renewAccessTokenMercadoLivre() {
      this.$confirm.require({
        message: "Confirmar operação?",
        header: "Confirmação",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          const rs = await axios.get(
            // eslint-disable-next-line max-len
            `/api/ecommerce/clienteConfig/renewAccessTokenMercadoLivre/${this.clienteConfig.id}`,
            {
              validateStatus(status) {
                return status < 500;
              },
            }
          );
          if (rs?.data?.RESULT === "OK") {
            window.location = `form?id=${this.clienteConfig.id}`;
          } else {
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: "Erro ao renovar access token no Mercado Livre",
              life: 5000,
            });
          }
          this.setLoading(false);
        },
      });
    },

    adicionar() {
      this.clienteConfig.jsonData.mercadolivre.push({
        descricao: null,
        token_tg: null,
        access_token: null,
      });
    },

    remover(i) {
      this.clienteConfig.jsonData.mercadolivre.splice(i, 1);
    },
  },
  computed: {
    ...mapGetters({
      clienteConfig: "getClienteConfig",
      clientes: "getClientes",
      formErrors: "getClienteConfigErrors",
    }),
  },
};
</script>

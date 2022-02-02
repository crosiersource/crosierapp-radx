<template>
  <Toast class="mt-5" />
  <ConfirmDialog />
  <CrosierListS
    titulo="Perguntas e Respostas"
    subtitulo="Mercado Livre"
    apiResource="/api/ecommerce/mercadoLivrePergunta/"
    :defaultSort="{ dtPergunta: 'DESC' }"
    ref="list"
    @beforeFilter="this.beforeFilter"
  >
    <template v-slot:filter-fields>
      <div class="form-row">
        <div class="col-md-8">
          <div class="form-group">
            <label for="cliente">Cliente</label>
            <Dropdown
              class="form-control"
              id="cliente"
              inputId="cliente"
              v-model="this.filters['mercadoLivreItem.clienteConfig.cliente']"
              :options="this.clientes"
              optionValue="@id"
              optionLabel="nome"
              :showClear="true"
              placeholder="--"
            />
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="status">Status</label>
            <InputText class="form-control" id="status" type="text" v-model="this.filters.status" />
          </div>
        </div>
      </div>
      <div class="form-row">
        <div class="col-3">
          <div class="form-group">
            <label for="dtPerguntaIni">Dt Pergunta (entre)</label>
            <Calendar
              id="dtPerguntaIni"
              :showIcon="true"
              :showOnFocus="false"
              class="form-control"
              inputClass="crsr-date"
              v-model="this.filters['dtPergunta[after]']"
              dateFormat="dd/mm/yy"
              ref="emissao"
            />
          </div>
        </div>
        <div class="col-3">
          <div class="form-group">
            <label for="dtPerguntaFim">e</label>
            <Calendar
              id="dtPerguntaFim"
              :showIcon="true"
              :showOnFocus="false"
              class="form-control"
              inputClass="crsr-date"
              :showSeconds="true"
              v-model="this.filters['dtPergunta[before]']"
              dateFormat="dd/mm/yy"
              ref="emissao"
            />
          </div>
        </div>
      </div>
    </template>
    <template v-slot:columns>
      <Column field="id" header="Id" :sortable="true" />
      <Column field="mercadoLivreItem.clienteConfig.cliente.nome" header="Loja" :sortable="true" />
      <Column field="dtPergunta" header="Dt Pergunta" :sortable="true">
        <template class="text-right" #body="r">
          {{ this.moment(r.data.dtPergunta).format("DD/MM/YYYY HH:mm") }}
        </template>
      </Column>
      <Column field="jsonData" header="Pergunta">
        <template class="text-right" #body="r">
          <div style="max-width: 350px; white-space: pre-line">
            <b>P: {{ r.data.jsonData.r.text }}</b>
            <hr />
            R: {{ r.data.jsonData.r?.answer?.text }}
          </div>
        </template>
      </Column>
      <Column field="status" header="Status" :sortable="true"></Column>
      <Column field="produto" header="Produto" :sortable="true">
        <template class="text-right" #body="r">
          <div style="max-width: 250px; white-space: pre-line">
            <a :href="r.data.mercadoLivreItem.jsonData?.r?.permalink" target="_blank">
              {{ r.data.mercadoLivreItem.descricao }}
              ({{
                r.data.mercadoLivreItem.precoVenda.toLocaleString("pt-BR", {
                  style: "currency",
                  currency: "BRL",
                })
              }})
            </a>
          </div>
        </template>
      </Column>
      <Column field="updated" header="" :sortable="true">
        <template class="text-right" #body="r">
          <div class="row d-flex justify-content-end mr-2 ml-2">
            <button
              type="button"
              class="btn btn-sm btn-outline-warning"
              @click="this.atualizarPergunta(r.data.id)"
            >
              <i class="fas fa-sync"></i> Atualizar
            </button>
            <button
              type="button"
              class="btn btn-sm btn-outline-info"
              @click="this.abrirDialogResp(r.data)"
              v-if="r.data.status === 'UNANSWERED'"
            >
              <i class="far fa-comment-dots"></i> Responder
            </button>
          </div>
          <div class="mt-1 d-flex justify-content-end mr-2">
            <span v-if="r.data.updated" class="badge badge-info">
              {{ new Date(r.data.updated).toLocaleString() }}
            </span>
          </div>
        </template>
      </Column>
    </template>
  </CrosierListS>
  <Dialog
    header="Resposta"
    v-model:visible="this.exibeDialogResp"
    :modal="true"
    :breakpoints="{ '960px': '75vw', '640px': '100vw' }"
    :style="{ width: '50vw' }"
  >
    <div class="form-row">
      <div class="col-12">
        <div class="form-group">
          {{ this.mercadoLivrePergunta.jsonData.r.text }}
        </div>
      </div>
    </div>
    <div class="form-row">
      <div class="col-12">
        <Textarea v-model="this.resposta" style="width: 100%; height: 150px" />
      </div>
    </div>

    <template #footer>
      <Button
        label="Cancelar"
        icon="pi pi-times"
        class="p-button-text"
        @click="this.exibeDialogResp = false"
      />
      <Button label="Responder" icon="pi pi-check" autofocus @click="this.responder()" />
    </template>
  </Dialog>
</template>

<script>
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Toast from "primevue/toast";
import Calendar from "primevue/calendar";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import ConfirmDialog from "primevue/confirmdialog";
import Textarea from "primevue/textarea";
import Dropdown from "primevue/dropdown";
import moment from "moment";
import { mapGetters, mapMutations } from "vuex";
import axios from "axios";
import { api, CrosierListS } from "crosier-vue";

export default {
  components: {
    Button,
    CrosierListS,
    Calendar,
    Column,
    Dropdown,
    InputText,
    Toast,
    Textarea,
    Dialog,
    ConfirmDialog,
  },

  data() {
    return {
      exibeDialogResp: false,
      mercadoLivrePergunta: {},
      resposta: null,
      tableData: [],
      columns: [],
    };
  },

  async mounted() {
    this.setLoading(true);
    this.$store.dispatch("loadClientes");
    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading"]),

    async abrirDialogResp(mercadoLivrePergunta) {
      const rs = await this.atualizarPergunta(mercadoLivrePergunta.id);
      if (rs?.data?.RESULT === "OK") {
        try {
          const response = await api.get({
            apiResource: `/api/ecommerce/mercadoLivrePergunta/${mercadoLivrePergunta.id}`,
          });
          if (response.data.status === "ANSWERED") {
            this.$toast.add({
              severity: "warn",
              summary: "Atenção",
              detail: "Esta pergunta já foi respondida",
              life: 5000,
            });
            return;
          }
          // else {
          console.error("Não encontrado");
          // }
        } catch (err) {
          console.error(err);
        }
      }
      this.mercadoLivrePergunta = mercadoLivrePergunta;
      this.exibeDialogResp = true;
    },

    beforeFilter() {
      if (this.filters["dtPergunta[after]"]) {
        this.filters["dtPergunta[after]"].setHours(0, 0, 0, 0);
        this.filters["dtPergunta[after]"] = moment(this.filters["dtPergunta[after]"]).format();
      }
      if (this.filters["dtPergunta[before]"]) {
        this.filters["dtPergunta[before]"].setHours(23, 59, 59, 999);
        this.filters["dtPergunta[before]"] = moment(this.filters["dtPergunta[before]"]).format();
      }
    },

    async responder() {
      this.$confirm.require({
        message: "Confirmar operação?",
        header: "Confirmação",
        icon: "pi pi-exclamation-triangle",
        accept: async () => {
          this.setLoading(true);
          const rs = await axios.get(
            // eslint-disable-next-line max-len
            `/api/ecommerce/mercadoLivrePergunta/responder/${this.mercadoLivrePergunta.id}?resposta=${this.resposta}`,
            {
              validateStatus(status) {
                return status < 500;
              },
            }
          );
          if (rs?.data?.RESULT === "OK") {
            window.location = "/ecommerce/mercadoLivrePergunta/list";
          } else {
            console.error(rs);
            this.$toast.add({
              severity: "error",
              summary: "Erro",
              detail: "Erro ao responder no Mercado Livre",
              life: 5000,
            });
          }
          this.exibeDialogResp = false;
          this.setLoading(false);
        },
      });
    },

    async atualizarPergunta(id) {
      this.setLoading(true);
      try {
        const rs = await axios.get(
          // eslint-disable-next-line max-len
          `/api/ecommerce/mercadoLivrePergunta/atualizarPergunta/${id}`,
          {
            validateStatus(status) {
              return status < 500;
            },
          }
        );
        this.$refs.list.doFilter();
        this.$toast.add({
          severity: "success",
          summary: "Sucesso",
          detail: "Pergunta/resposta atualizada com sucesso",
          life: 5000,
        });
        this.setLoading(false);
        return rs;
      } catch (e) {
        console.error(e);
        this.$toast.add({
          severity: "error",
          summary: "Erro",
          detail: "Erro ao atualizar a pergunta",
          life: 5000,
        });
      }
      this.setLoading(false);
      return null;
    },

    moment(date) {
      return moment(date);
    },
  },

  computed: {
    ...mapGetters({ filters: "getFilters", clientes: "getClientes" }),
  },
};
</script>

<style>
.dt-sm-bt {
  height: 30px !important;
  width: 30px !important;
}

.form-control {
  padding: 0 !important;
  border: none !important;
}

.form-control-text {
  padding-left: 10px !important;
}

.p-inputtext {
  border: 1px solid #ced4da !important;
}
</style>

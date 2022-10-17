<template>
  <Toast position="bottom-right" class="mt-5" />
  <CrosierFormS @submitForm="this.submitForm" titulo="Migração de Produtos">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">De</h5>

        <div class="form-row">
          <CrosierDropdownEntity
            col="4"
            v-model="this.fields.deptoDe"
            :error="this.formErrors.deptoDe"
            entity-uri="/api/est/depto"
            optionLabel="descricaoMontada"
            :optionValue="null"
            :orderBy="{ codigo: 'ASC' }"
            label="Depto"
            id="deptoDe"
            @update:modelValue="this.onChangeDepto('grupoDe')"
          />

          <CrosierDropdownEntity
            col="4"
            ref="grupoDe"
            v-if="this.fields?.deptoDe?.id"
            v-model="this.fields.grupoDe"
            :error="this.formErrors.grupoDe"
            entity-uri="/api/est/grupo"
            optionLabel="descricaoMontada"
            :optionValue="null"
            :orderBy="{ codigo: 'ASC' }"
            :filters="{ depto: this.fields.deptoDe['@id'] }"
            label="Grupo"
            id="grupo"
            @update:modelValue="this.onChangeGrupo"
          />
          <div class="col-md-4" v-else>
            <div class="form-group">
              <label>Grupo</label>
              <Skeleton class="form-control" height="2rem" />
            </div>
          </div>

          <CrosierDropdownEntity
            col="4"
            ref="subgrupoDe"
            v-if="this.fields?.grupoDe?.id"
            v-model="this.fields.subgrupoDe"
            :error="this.formErrors.subgrupoDe"
            entity-uri="/api/est/subgrupo"
            optionLabel="descricaoMontada"
            :optionValue="null"
            :orderBy="{ codigo: 'ASC' }"
            :filters="{ grupo: this.fields.grupoDe['@id'] }"
            label="Subgrupo"
            id="subgrupo"
          />
          <div class="col-md-4" v-else>
            <div class="form-group">
              <label>Subgrupo</label>
              <Skeleton class="form-control" height="2rem" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-body">
        <h5 class="card-title">Para</h5>

        <div class="form-row">
          <CrosierDropdownEntity
            col="4"
            v-model="this.fields.deptoPara"
            :error="this.formErrors.deptoPara"
            entity-uri="/api/est/depto"
            optionLabel="descricaoMontada"
            :optionValue="null"
            :orderBy="{ codigo: 'ASC' }"
            label="Depto"
            id="deptoPara"
            @update:modelValue="this.onChangeDepto('grupoPara')"
          />

          <CrosierDropdownEntity
            col="4"
            ref="grupoPara"
            v-if="this.fields?.deptoPara?.id"
            v-model="this.fields.grupoPara"
            :error="this.formErrors.grupoPara"
            entity-uri="/api/est/grupo"
            optionLabel="descricaoMontada"
            :optionValue="null"
            :orderBy="{ codigo: 'ASC' }"
            :filters="{ depto: this.fields.deptoPara['@id'] }"
            label="Grupo"
            id="grupoPara"
            @update:modelValue="this.onChangeGrupo('subgrupoPara')"
          />
          <div class="col-md-4" v-else>
            <div class="form-group">
              <label>Grupo</label>
              <Skeleton class="form-control" height="2rem" />
            </div>
          </div>

          <CrosierDropdownEntity
            col="4"
            ref="subgrupoPara"
            v-if="this.fields?.grupoPara?.id"
            v-model="this.fields.subgrupoPara"
            :error="this.formErrors.subgrupoPara"
            entity-uri="/api/est/subgrupo"
            optionLabel="descricaoMontada"
            :optionValue="null"
            :orderBy="{ codigo: 'ASC' }"
            :filters="{ grupo: this.fields.grupoPara['@id'] }"
            label="Subgrupo"
            id="subgrupoPara"
          />
          <div class="col-md-4" v-else>
            <div class="form-group">
              <label>Subgrupo</label>
              <Skeleton class="form-control" height="2rem" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </CrosierFormS>
</template>

<script>
import Toast from "primevue/toast";
import * as yup from "yup";
import { mapGetters, mapMutations } from "vuex";
import { CrosierFormS, submitForm, CrosierDropdownEntity, SetFocus } from "crosier-vue";
import Skeleton from "primevue/skeleton";

export default {
  components: {
    Toast,
    CrosierFormS,
    CrosierDropdownEntity,
    Skeleton,
  },

  data() {
    return {
      criarVincularFields: false,
      schemaValidator: {},
    };
  },

  async mounted() {
    this.setLoading(true);

    this.schemaValidator = yup.object().shape({
      deptoDe: yup.mixed().required().typeError(),
      grupoDe: yup.mixed().required().typeError(),
      subgrupoDe: yup.mixed().required().typeError(),

      deptoPara: yup.mixed().required().typeError(),
      grupoPara: yup.mixed().required().typeError(),
      subgrupoPara: yup.mixed().required().typeError(),
    });

    SetFocus("label", 100);

    this.setLoading(false);
  },

  methods: {
    ...mapMutations(["setLoading", "setFields", "setFieldsErrors"]),

    onChangeDepto(grupo) {
      this.$nextTick(async () => {
        this.fields[grupo] = null;
        this.setLoading(true);
        if (this.$refs[grupo]) {
          await this.$refs[grupo].load();
        }
        this.setLoading(false);
      });
    },

    onChangeGrupo(subgrupo) {
      this.$nextTick(async () => {
        this.fields[subgrupo] = null;
        this.setLoading(true);
        if (this.$refs[subgrupo]) {
          await this.$refs[subgrupo].load();
        }
        this.setLoading(false);
      });
    },

    async submitForm() {
      this.setLoading(true);
      await submitForm({
        apiResource: "/api/est/deptoGrupoSubgrupo/migrar",
        schemaValidator: this.schemaValidator,
        $store: this.$store,
        formDataStateName: "fields",
        $toast: this.$toast,
        // fnBeforeSave: (formData) => {
        //
        // },
      });
      this.setLoading(false);
    },
  },

  computed: {
    ...mapGetters({ fields: "getFields", formErrors: "getFieldsErrors" }),
  },
};
</script>

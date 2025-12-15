<template>
  <q-page class="q-pa-sm bg-grey-2">
    <q-card flat bordered>
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6 text-weight-bold">
            Virus del Papiloma Humano (PCR)
          </div>
          <div class="text-caption text-grey-7">
            Detección de HPV de alto riesgo por PCR en tiempo real
          </div>
        </div>
        <q-space />
        <q-btn flat icon="arrow_back" label="Volver" @click="$router.back()" />
        <q-btn color="primary" icon="save" label="Guardar" @click="save" :loading="loading" />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-markup-table dense bordered>
          <thead>
          <tr>
            <th>Determinación</th>
            <th>Resultado</th>
            <th>Valor de referencia</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td>HPV alto riesgo</td>
            <td>
              <q-select v-model="form.hpv_alto_riesgo"
                        :options="opciones" dense outlined />
            </td>
            <td>NO DETECTADO</td>
          </tr>
          <tr>
            <td>HPV 16</td>
            <td><q-select v-model="form.hpv_16" :options="opciones" dense outlined /></td>
            <td>NO DETECTADO</td>
          </tr>
          <tr>
            <td>HPV 18</td>
            <td><q-select v-model="form.hpv_18" :options="opciones" dense outlined /></td>
            <td>NO DETECTADO</td>
          </tr>
          <tr>
            <td>HPV 45</td>
            <td><q-select v-model="form.hpv_45" :options="opciones" dense outlined /></td>
            <td>NO DETECTADO</td>
          </tr>
          </tbody>
        </q-markup-table>

        <div class="q-mt-md">
          <div class="text-subtitle2 q-mb-xs">Observaciones</div>
          <q-input v-model="form.observaciones" type="textarea" outlined autogrow />
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: 'PapilomaHumanoPage',
  data () {
    return {
      loading: false,
      opciones: ['NO DETECTADO', 'DETECTADO'],
      form: {
        hpv_alto_riesgo: 'NO DETECTADO',
        hpv_16: 'NO DETECTADO',
        hpv_18: 'NO DETECTADO',
        hpv_45: 'NO DETECTADO',
        observaciones: ''
      }
    }
  },
  mounted () {
    this.load()
  },
  methods: {
    async load () {
      this.loading = true
      const { data } = await this.$axios.get(
        `/papiloma-humano/solicitud/${this.$route.params.id}`
      )
      Object.assign(this.form, data.papiloma)
      this.loading = false
    },
    async save () {
      this.loading = true
      await this.$axios.post(
        `/papiloma-humano/solicitud/${this.$route.params.id}`,
        this.form
      )
      this.$alert.success('Resultado guardado correctamente')
      this.loading = false
    }
  }
}
</script>

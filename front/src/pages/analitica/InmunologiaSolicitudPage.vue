<template>
  <q-page class="q-pa-sm bg-grey-2">
    <q-card flat bordered>
      <!-- HEADER -->
      <q-card-section class="row items-center q-pa-sm">
        <div>
          <div class="text-h6 text-weight-bold">Inmunología · Analítica</div>
          <div class="text-caption text-grey-7">
            Ingresa los resultados para cada prestación seleccionada en la solicitud.
          </div>
        </div>
        <q-space />
        <q-btn flat icon="arrow_back" label="Volver" no-caps @click="$router.back()" />
        <q-btn
          class="q-ml-sm"
          color="primary"
          icon="save"
          label="Guardar resultados"
          no-caps
          :loading="saving"
          :disable="!prestaciones.length"
          @click="guardar"
        />
      </q-card-section>

      <q-separator />

      <!-- INFO SOLICITUD -->
      <q-card-section class="q-pa-sm bg-grey-1">
        <div class="row q-col-gutter-sm text-caption">
          <div class="col-auto">
            <span class="text-grey-6">Paciente: </span>
            <strong>{{ solicitud?.paciente_nombre }}</strong>
          </div>
          <div class="col-auto">
            <span class="text-grey-6">Edad: </span>
            <strong>{{ solicitud?.paciente_edad }}</strong>
          </div>
          <div class="col-auto">
            <span class="text-grey-6">Género: </span>
            <strong>{{ solicitud?.paciente_genero }}</strong>
          </div>
          <div class="col-auto">
            <span class="text-grey-6">Médico: </span>
            <strong>{{ solicitud?.doctor_nombre || 'N/A' }}</strong>
          </div>
          <div class="col-auto">
            <q-chip square color="primary" text-color="white" dense>
              N° {{ solicitud?.codigo }}
            </q-chip>
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <!-- CUERPO -->
      <q-card-section class="q-pa-sm">
        <div v-if="loading" class="text-center q-pa-lg text-grey-7">
          <q-spinner size="40px" color="primary" />
          <div class="q-mt-sm">Cargando...</div>
        </div>

        <div v-else-if="!prestaciones.length" class="text-center q-pa-lg text-grey-6">
          <q-icon name="info" size="40px" />
          <div class="q-mt-sm">No hay prestaciones de inmunología con rangos vinculados en esta solicitud.</div>
          <div class="text-caption q-mt-xs">
            Vincule rangos a las prestaciones en la sección <strong>Servicios → Vincular rangos</strong>.
          </div>
        </div>

        <div v-else>
          <!-- Una sección por prestación -->
          <div
            v-for="prest in prestaciones"
            :key="prest.servicio_id"
            class="q-mb-md"
          >
            <div class="row items-center q-mb-xs">
              <q-icon name="biotech" color="primary" class="q-mr-xs" />
              <span class="text-subtitle2 text-weight-bold">{{ prest.nombre }}</span>
              <q-chip v-if="prest.metodo" dense class="q-ml-sm" color="grey-3">
                {{ prest.metodo }}
              </q-chip>
              <q-chip v-if="prest.subarea" dense class="q-ml-xs" color="blue-1" text-color="primary">
                {{ prest.subarea }}
              </q-chip>
            </div>

            <div v-if="!prest.rangos.length" class="text-caption text-grey-6 q-mb-sm q-pl-md">
              Esta prestación no tiene rangos vinculados.
            </div>

            <q-table
              v-else
              dense flat bordered
              :rows="prest.rangos"
              :columns="columnsResultados"
              row-key="id"
              :rows-per-page-options="[0]"
              class="q-mb-sm"
              hide-bottom
            >
              <template #body-cell-valor="props">
                <q-td :props="props">
                  <q-input
                    v-model="valores[props.row.id]"
                    dense outlined
                    :placeholder="props.row.unidad || ''"
                    style="min-width: 130px"
                  />
                </q-td>
              </template>

              <template #body-cell-interpretacion="props">
                <q-td :props="props">
                  <div style="max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                       :title="props.row.interpretacion">
                    {{ props.row.interpretacion }}
                  </div>
                </q-td>
              </template>

              <template #body-cell-estado="props">
                <q-td :props="props">
                  <q-chip
                    v-if="calcEstado(props.row)"
                    dense
                    :color="calcEstado(props.row).color"
                    :text-color="calcEstado(props.row).textColor"
                  >
                    {{ calcEstado(props.row).label }}
                  </q-chip>
                </q-td>
              </template>
            </q-table>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: 'InmunologiaSolicitudPage',

  data () {
    return {
      loading: false,
      saving: false,
      solicitud: null,
      prestaciones: [],
      valores: {},   // { area_rango_id: valor_string }

      columnsResultados: [
        { name: 'rango_nombre', label: 'Analito / Condición', field: 'rango_nombre', align: 'left' },
        { name: 'metodo', label: 'Método', field: 'metodo', align: 'left' },
        { name: 'valor', label: 'Valor del paciente', align: 'center' },
        { name: 'unidad', label: 'Unidad', field: 'unidad', align: 'left' },
        { name: 'interpretacion', label: 'Rango de referencia', align: 'left' },
        { name: 'estado', label: 'Estado', align: 'center' }
      ]
    }
  },

  mounted () {
    this.load()
  },

  methods: {
    async load () {
      this.loading = true
      try {
        const { data } = await this.$axios.get(
          `/inmunologia-analitica/solicitud/${this.$route.params.id}`
        )
        this.solicitud = data.solicitud
        this.prestaciones = data.prestaciones.filter(p => p.rangos.length > 0)

        // Inicializar valores con los resultados existentes
        this.valores = {}
        this.prestaciones.forEach(prest => {
          prest.rangos.forEach(rango => {
            this.valores[rango.id] = rango.resultado?.valor_final ?? ''
          })
        })
      } catch (e) {
        console.error(e)
        this.$q.notify({ type: 'negative', message: 'No se pudo cargar la analítica de inmunología' })
      } finally {
        this.loading = false
      }
    },

    async guardar () {
      this.saving = true
      try {
        const resultados = Object.entries(this.valores)
          .filter(([, val]) => val !== null && val !== undefined)
          .map(([rangoId, val]) => ({
            area_rango_id: parseInt(rangoId),
            valor_final: String(val ?? '')
          }))

        await this.$axios.post(
          `/inmunologia-analitica/solicitud/${this.$route.params.id}/resultados`,
          { resultados }
        )
        this.$q.notify({ type: 'positive', message: 'Resultados guardados correctamente' })
        await this.load()
      } catch (e) {
        console.error(e)
        this.$q.notify({ type: 'negative', message: 'Error al guardar resultados' })
      } finally {
        this.saving = false
      }
    },

    calcEstado (rango) {
      const val = parseFloat(this.valores[rango.id])
      if (isNaN(val)) return null

      if (rango.rango_minimo !== null && rango.rango_maximo !== null) {
        if (val < rango.rango_minimo) return { label: 'Bajo', color: 'orange-2', textColor: 'orange-9' }
        if (val > rango.rango_maximo) return { label: 'Alto', color: 'red-2', textColor: 'red-9' }
        return { label: 'Normal', color: 'green-2', textColor: 'green-9' }
      }
      if (rango.rango_maximo !== null && val > rango.rango_maximo) {
        return { label: 'Alto', color: 'red-2', textColor: 'red-9' }
      }
      if (rango.rango_minimo !== null && val < rango.rango_minimo) {
        return { label: 'Bajo', color: 'orange-2', textColor: 'orange-9' }
      }
      return null
    }
  }
}
</script>

<template>
  <q-page class="q-pa-sm">
    <!-- Cabecera: semana y resumen -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="q-pa-sm">
        <div class="row items-center q-col-gutter-sm">
          <div class="col">
            <div class="text-subtitle1 text-weight-bold">Mis laboratorios</div>
            <div class="text-caption text-grey-7" v-if="doctores.length">
              {{ doctores.map(d => d.nombre).join(', ') }}
            </div>
          </div>
          <div class="col-auto">
            <q-btn dense flat round icon="chevron_left" @click="cambiarMes(-1)" :disable="loading">
              <q-tooltip>Mes anterior</q-tooltip>
            </q-btn>
            <q-btn dense flat no-caps label="Este mes" @click="irMesActual" :disable="loading" class="q-px-sm"/>
            <q-btn dense flat round icon="chevron_right" @click="cambiarMes(1)" :disable="loading">
              <q-tooltip>Mes siguiente</q-tooltip>
            </q-btn>
            <q-btn dense flat round icon="refresh" @click="buscar" :loading="loading">
              <q-tooltip>Actualizar</q-tooltip>
            </q-btn>
          </div>
        </div>

        <div class="row q-col-gutter-xs q-mt-xs">
          <div class="col-6 col-sm-3">
            <q-input v-model="desde" type="date" dense outlined label="Desde" @update:model-value="buscar">
              <template v-slot:prepend><q-icon name="event"/></template>
            </q-input>
          </div>
          <div class="col-6 col-sm-3">
            <q-input v-model="hasta" type="date" dense outlined label="Hasta" @update:model-value="buscar">
              <template v-slot:prepend><q-icon name="event"/></template>
            </q-input>
          </div>
          <div class="col-12 col-sm-6">
            <q-input
              v-model="filtro" dense outlined clearable debounce="500"
              placeholder="Buscar paciente, CI, código o sala..."
              @update:model-value="buscar"
            >
              <template v-slot:prepend><q-icon name="search"/></template>
            </q-input>
          </div>
          <div class="col-12">
            <q-btn-toggle
              v-model="verSolo"
              spread no-caps dense unelevated
              toggle-color="primary" color="grey-3" text-color="grey-8"
              :options="[
                { label: 'Todos', value: 'todos' },
                { label: 'Listos', value: 'listos' },
                { label: 'En proceso', value: 'proceso' }
              ]"
              @update:model-value="buscar"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <div v-if="loading" class="text-center q-pa-lg">
      <q-spinner color="primary" size="34px"/>
    </div>

    <div v-else-if="mensaje" class="q-pa-md text-center text-grey-7">
      <q-icon name="info" size="28px" class="q-mb-sm"/>
      <div>{{ mensaje }}</div>
    </div>

    <div v-else-if="solicitudes.length === 0" class="q-pa-md text-center text-grey-7">
      <q-icon name="inbox" size="28px" class="q-mb-sm"/>
      <div>No hay laboratorios en este rango de fechas.</div>
    </div>

    <!-- Listado en tarjetas (pensado para celular) -->
    <div v-else class="column q-gutter-sm">
      <q-card
        v-for="sol in solicitudes"
        :key="sol.id"
        flat bordered
        class="cursor-pointer"
        @click="abrirDetalle(sol)"
      >
        <q-card-section class="q-pa-sm">
          <div class="row items-start no-wrap">
            <div class="col">
              <div class="text-weight-bold ellipsis">{{ sol.paciente_nombre }}</div>
              <div class="text-caption text-grey-7">
                CI {{ sol.paciente_ci || '—' }} · {{ sol.paciente_edad }} años · {{ sol.paciente_genero }}
              </div>
              <div class="text-caption text-grey-7">
                Cód. {{ sol.codigo }} · {{ formatoFecha(sol.fecha_creacion) }}
              </div>
              <div class="text-caption text-grey-7" v-if="sol.sala">
                {{ sol.sala }}<span v-if="sol.cama"> · Cama {{ sol.cama }}</span>
              </div>
            </div>
            <div class="col-auto text-right">
              <q-chip
                dense size="12px" text-color="white"
                :color="sol.todo_realizado ? 'positive' : 'orange-8'"
                :icon="sol.todo_realizado ? 'task_alt' : 'hourglass_top'"
              >
                {{ sol.servicios_realizados }}/{{ sol.total_servicios }}
              </q-chip>
              <div class="q-mt-xs">
                <q-icon v-if="sol.doctor_aceptado_at" name="verified" color="teal" size="18px">
                  <q-tooltip>Aceptado el {{ formatoFecha(sol.doctor_aceptado_at) }}</q-tooltip>
                </q-icon>
                <q-icon v-else-if="sol.doctor_visto_at" name="visibility" color="grey-6" size="18px">
                  <q-tooltip>Visto el {{ formatoFecha(sol.doctor_visto_at) }}</q-tooltip>
                </q-icon>
                <q-icon v-else name="fiber_new" color="primary" size="18px">
                  <q-tooltip>Sin abrir</q-tooltip>
                </q-icon>
              </div>
            </div>
          </div>
          <div class="q-mt-xs">
            <q-chip
              v-for="serv in sol.servicios.slice(0, 3)"
              :key="serv.id"
              dense size="11px"
              :color="serv.realizado === 'REALIZADO' ? 'green-1' : 'grey-3'"
              :text-color="serv.realizado === 'REALIZADO' ? 'green-9' : 'grey-8'"
              class="q-mr-xs q-mb-xs"
            >
              {{ serv.nombre }}
            </q-chip>
            <q-chip v-if="sol.servicios.length > 3" dense size="11px" color="grey-3" text-color="grey-8">
              +{{ sol.servicios.length - 3 }}
            </q-chip>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Paginación -->
    <div v-if="!loading && !mensaje && total > 0" class="row items-center justify-between q-mt-sm q-px-xs">
      <div class="col-auto text-caption text-grey-7">
        Mostrando <b>{{ paginacionInfo.desde }}-{{ paginacionInfo.hasta }}</b> de <b>{{ total }}</b>
      </div>
      <div class="col-auto row items-center q-gutter-sm">
        <q-select
          v-model="porPagina"
          :options="opcionesPorPagina"
          emit-value map-options
          dense outlined options-dense
          style="width:100px"
          label="Filas"
          @update:model-value="cambiarPorPagina"
        />
        <q-pagination
          v-if="porPagina !== 0"
          v-model="page"
          :max="lastPage"
          :max-pages="$q.screen.lt.sm ? 4 : 7"
          boundary-links direction-links
          icon-first="first_page" icon-last="last_page"
          icon-prev="chevron_left" icon-next="chevron_right"
          size="sm" color="primary"
          @update:model-value="cambiarPagina"
        />
      </div>
    </div>

    <!-- Detalle: pantalla completa, con el PDF embebido -->
    <q-dialog v-model="dialogDetalle" maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card v-if="detalle" class="column no-wrap detalle-card">
        <q-toolbar dense class="bg-primary text-white col-auto">
          <q-toolbar-title class="text-body2 text-weight-bold ellipsis">
            {{ detalle.paciente_nombre }}
          </q-toolbar-title>
          <q-icon v-if="detalle.doctor_aceptado_at" name="verified" size="18px" class="q-mr-xs">
            <q-tooltip>Aceptado: {{ formatoFecha(detalle.doctor_aceptado_at) }}</q-tooltip>
          </q-icon>
          <q-btn flat round dense icon="close" v-close-popup/>
        </q-toolbar>

        <!-- datos y prestaciones: una sola línea, el detalle se despliega -->
        <q-expansion-item dense dense-toggle class="col-auto bg-grey-1 datos-solicitud">
          <template v-slot:header>
            <q-item-section>
              <div class="datos-linea ellipsis">
                {{ detalle.codigo }} · {{ formatoFecha(detalle.fecha_creacion) }} ·
                CI {{ detalle.paciente_ci || '—' }} · {{ detalle.paciente_edad }}a ·
                {{ detalle.paciente_genero }}
                <span v-if="detalle.sala"> · {{ detalle.sala }}</span>
              </div>
            </q-item-section>
            <q-item-section side>
              <q-chip
                dense size="10px" text-color="white" class="q-my-none"
                :color="detalle.todo_realizado ? 'positive' : 'orange-8'"
              >
                {{ detalle.servicios_realizados }}/{{ detalle.total_servicios }}
              </q-chip>
            </q-item-section>
          </template>

          <div class="q-px-sm q-pb-sm">
            <div class="datos-linea">
              Reg. {{ detalle.nro_registro || '—' }}
              <span v-if="detalle.cama"> · Cama {{ detalle.cama }}</span>
              · Solicitado por {{ detalle.doctor_nombre }}
            </div>
            <div class="datos-linea" v-if="detalle.doctor_visto_at">
              Visto: {{ formatoFecha(detalle.doctor_visto_at) }}
              <span v-if="detalle.doctor_aceptado_at"> · Aceptado: {{ formatoFecha(detalle.doctor_aceptado_at) }}</span>
            </div>
            <q-banner v-if="detalle.muestra_rechazada === 'Si'" dense class="bg-red-1 text-red-9 q-mt-xs">
              <template v-slot:avatar><q-icon name="report" color="negative"/></template>
              Muestra rechazada. {{ detalle.motivo_rechazo }}
            </q-banner>
            <div class="q-mt-xs">
              <q-chip
                v-for="serv in detalle.servicios"
                :key="serv.id"
                dense size="10px"
                :color="serv.realizado === 'REALIZADO' ? 'green-1' : 'grey-3'"
                :text-color="serv.realizado === 'REALIZADO' ? 'green-9' : 'grey-8'"
                :icon="serv.realizado === 'REALIZADO' ? 'check' : 'schedule'"
                class="q-mr-xs q-mb-xs"
              >
                {{ serv.nombre }}
              </q-chip>
            </div>
          </div>
        </q-expansion-item>

        <q-separator class="col-auto"/>

        <template v-if="detalle.pdfs.length">
          <q-tabs
            v-if="detalle.pdfs.length > 1"
            v-model="pdfTab" dense inline-label no-caps
            class="text-primary col-auto" active-color="primary" indicator-color="primary"
          >
            <q-tab v-for="pdf in detalle.pdfs" :key="pdf.url" :name="pdf.url" :label="pdf.label"/>
          </q-tabs>
          <div class="col pdf-visor">
            <iframe :src="pdfTab" title="Resultado de laboratorio" @load="pdfCargando = false"/>
            <q-inner-loading :showing="pdfCargando" color="primary" label="Cargando resultado..."/>
          </div>
        </template>
        <div v-else class="col column flex-center text-grey-7 q-pa-md">
          <q-icon name="hourglass_empty" size="28px" class="q-mb-sm"/>
          <div class="text-center">Todavía no hay resultados publicados para esta solicitud.</div>
        </div>

        <q-separator class="col-auto"/>

        <q-card-actions class="q-pa-xs col-auto" align="right">
          <q-btn
            v-if="detalle.pdfs.length"
            flat dense no-caps color="primary" icon="open_in_new" label="Abrir PDF"
            @click="abrirEnPestania"
          />
          <q-btn
            dense no-caps color="teal" icon="verified" class="q-px-sm"
            :label="detalle.doctor_aceptado_at ? 'Aceptado' : 'Acepto el resultado'"
            :disable="!!detalle.doctor_aceptado_at || !detalle.pdfs.length"
            :loading="aceptando"
            @click="aceptar"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'MisLaboratorios',
  data () {
    return {
      desde: moment().startOf('month').format('YYYY-MM-DD'),
      hasta: moment().endOf('month').format('YYYY-MM-DD'),
      solicitudes: [],
      doctores: [],
      mensaje: '',
      loading: false,
      filtro: '',
      verSolo: 'todos',
      page: 1,
      porPagina: 10,
      total: 0,
      lastPage: 1,
      opcionesPorPagina: [
        { label: '10', value: 10 },
        { label: '25', value: 25 },
        { label: '50', value: 50 },
        { label: 'Todos', value: 0 }
      ],
      dialogDetalle: false,
      detalle: null,
      pdfTab: '',
      pdfCargando: false,
      aceptando: false,
    }
  },
  computed: {
    paginacionInfo () {
      if (!this.total) return { desde: 0, hasta: 0 }
      if (this.porPagina === 0) return { desde: 1, hasta: this.total }
      const desde = (this.page - 1) * this.porPagina + 1
      return { desde, hasta: Math.min(this.page * this.porPagina, this.total) }
    }
  },
  watch: {
    // cada cambio de laboratorio recarga el iframe: se avisa hasta que termine
    pdfTab (url) {
      this.pdfCargando = !!url
    }
  },
  mounted () {
    this.cargar()
  },
  methods: {
    formatoFecha (valor) {
      return valor ? moment(valor).format('DD/MM/YYYY HH:mm') : ''
    },
    cambiarMes (delta) {
      this.desde = moment(this.desde).add(delta, 'month').startOf('month').format('YYYY-MM-DD')
      this.hasta = moment(this.desde).endOf('month').format('YYYY-MM-DD')
      this.buscar()
    },
    irMesActual () {
      this.desde = moment().startOf('month').format('YYYY-MM-DD')
      this.hasta = moment().endOf('month').format('YYYY-MM-DD')
      this.buscar()
    },
    /** cualquier cambio de filtro vuelve a la primera página */
    buscar () {
      this.page = 1
      this.cargar()
    },
    cambiarPagina (page) {
      this.page = page
      this.cargar()
    },
    cambiarPorPagina (valor) {
      this.porPagina = valor
      this.page = 1
      this.cargar()
    },
    async cargar () {
      this.loading = true
      try {
        const { data } = await this.$axios.get('mis-laboratorios', {
          params: {
            desde: this.desde,
            hasta: this.hasta,
            filtro: this.filtro || '',
            estado: this.verSolo,
            page: this.page,
            per_page: this.porPagina,
          }
        })
        this.solicitudes = data.solicitudes || []
        this.doctores = data.doctores || []
        this.mensaje = data.mensaje || ''
        this.total = data.total || 0
        this.lastPage = data.last_page || 1
        this.page = data.page || this.page
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error al cargar los laboratorios')
      } finally {
        this.loading = false
      }
    },
    async abrirDetalle (sol) {
      this.detalle = sol
      this.pdfTab = sol.pdfs.length ? sol.pdfs[0].url : ''
      this.dialogDetalle = true

      if (sol.doctor_visto_at) return
      try {
        const { data } = await this.$axios.post(`mis-laboratorios/${sol.id}/visto`)
        sol.doctor_visto_at = data.doctor_visto_at
        sol.doctor_aceptado_at = data.doctor_aceptado_at
      } catch (e) {
        // el registro de "visto" no debe interrumpir la lectura del resultado
      }
    },
    abrirEnPestania () {
      if (this.pdfTab) window.open(this.pdfTab, '_blank')
    },
    async aceptar () {
      this.aceptando = true
      try {
        const { data } = await this.$axios.post(`mis-laboratorios/${this.detalle.id}/aceptar`)
        this.detalle.doctor_visto_at = data.doctor_visto_at
        this.detalle.doctor_aceptado_at = data.doctor_aceptado_at
        this.$alert.success('Resultado aceptado')
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo registrar la aceptación')
      } finally {
        this.aceptando = false
      }
    }
  }
}
</script>

<style scoped>
.detalle-card {
  height: 100%;
}

/* el visor toma todo el alto que sobra del diálogo */
.pdf-visor {
  min-height: 0;
  position: relative;
}

.pdf-visor iframe {
  width: 100%;
  height: 100%;
  border: 0;
  display: block;
}

.datos-linea {
  font-size: 11px;
  line-height: 1.25;
  color: #555;
}

.datos-solicitud :deep(.q-item) {
  min-height: 30px;
  padding: 2px 8px;
}
</style>

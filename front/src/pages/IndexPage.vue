<template>
  <q-page class="q-pa-md bg-grey-2">
    <!-- HEADER -->
    <div class="row items-center q-mb-md">

      <div class="col-12 col-md-6">
        <div class="text-h5 text-weight-bold">Dashboard de Solicitudes</div>
        <div class="text-caption text-grey-7">
          Visión por áreas, servicios y preanalítica
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="row items-center q-gutter-sm justify-end">
          <div class="col-12 col-sm-4">
            <q-input
              v-model="filters.date_from"
              type="date"
              dense outlined
              label="Desde"
            />
          </div>
          <div class="col-12 col-sm-4">
            <q-input
              v-model="filters.date_to"
              type="date"
              dense outlined
              label="Hasta"
            />
          </div>
          <div class="col-auto">
            <q-btn
              color="primary"
              icon="refresh"
              label="Actualizar"
              no-caps
              :loading="loading"
              @click="fetchDashboard"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- KPIs ENFOCADOS EN ÁREAS / SERVICIOS -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="q-pa-md">
          <div class="row items-center no-wrap">
            <div class="col">
              <div class="text-caption text-grey-7">Solicitudes</div>
              <div class="text-h5 text-weight-bold">
                {{ resumen.total_solicitudes || 0 }}
              </div>
            </div>
            <div class="col-auto">
              <q-icon name="receipt_long" size="36px" class="text-primary" />
            </div>
          </div>
          <div class="text-caption text-grey-6 q-mt-sm">
            Total de solicitudes en el rango seleccionado.
          </div>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="q-pa-md">
          <div class="row items-center no-wrap">
            <div class="col">
              <div class="text-caption text-grey-7">Servicios solicitados</div>
              <div class="text-h5 text-weight-bold">
                {{ resumen.total_servicios || 0 }}
              </div>
            </div>
            <div class="col-auto">
              <q-icon name="science" size="36px" class="text-secondary" />
            </div>
          </div>
          <div class="text-caption text-grey-6 q-mt-sm">
            Cantidad total de servicios (exámenes) asociados a las solicitudes.
          </div>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="q-pa-md">
          <div class="row items-center no-wrap">
            <div class="col">
              <div class="text-caption text-grey-7">Servicios / solicitud</div>
              <div class="text-h5 text-weight-bold">
                {{ resumen.promedio_servicios || 0 }}
              </div>
            </div>
            <div class="col-auto">
              <q-icon name="bar_chart" size="36px" class="text-teal" />
            </div>
          </div>
          <div class="text-caption text-grey-6 q-mt-sm">
            Promedio de servicios por cada solicitud de laboratorio.
          </div>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="q-pa-md">
          <div class="row items-center no-wrap">
            <div class="col">
              <div class="text-caption text-grey-7">Muestras preanalíticas</div>
              <div class="text-h5 text-weight-bold">
                {{ resumen.total_muestras_preanaliticas || 0 }}
              </div>
            </div>
            <div class="col-auto">
              <q-icon name="inventory_2" size="36px" class="text-positive" />
            </div>
          </div>
          <div class="text-caption text-grey-6 q-mt-sm">
            Muestras registradas en la etapa preanalítica.
          </div>
        </q-card>
      </div>
    </div>

    <!-- GRÁFICOS FILA 1: ÁREAS Y SERVICIOS -->
    <div class="row q-col-gutter-md q-mb-md">
      <!-- Solicitudes por área (donut) -->
      <div class="col-12 col-md-4">
        <q-card flat bordered class="q-pa-md">
          <div class="text-subtitle1 text-weight-bold q-mb-sm">
            Solicitudes por área
          </div>
          <apexchart
            type="donut"
            height="280"
            :options="chartAreas.options"
            :series="chartAreas.series"
          />
        </q-card>
      </div>

      <!-- Solicitudes vs servicios por área (bar) -->
      <div class="col-12 col-md-4">
        <q-card flat bordered class="q-pa-md">
          <div class="text-subtitle1 text-weight-bold q-mb-sm">
            Solicitudes y servicios por área
          </div>
          <apexchart
            type="bar"
            height="280"
            :options="chartServiciosArea.options"
            :series="chartServiciosArea.series"
          />
        </q-card>
      </div>

      <!-- Top servicios más solicitados -->
      <div class="col-12 col-md-4">
        <q-card flat bordered class="q-pa-md">
          <div class="text-subtitle1 text-weight-bold q-mb-sm">
            Top servicios más solicitados
          </div>
          <apexchart
            type="bar"
            height="280"
            :options="chartTopServicios.options"
            :series="chartTopServicios.series"
          />
        </q-card>
      </div>
    </div>

    <!-- GRÁFICOS FILA 2: TIEMPO + PREANALÍTICA -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-8">
        <q-card flat bordered class="q-pa-md">
          <div class="text-subtitle1 text-weight-bold q-mb-sm">
            Evolución diaria de solicitudes
          </div>
          <apexchart
            type="area"
            height="320"
            :options="chartSerieFechas.options"
            :series="chartSerieFechas.series"
          />
        </q-card>
      </div>

      <div class="col-12 col-md-4">
        <q-card flat bordered class="q-pa-md">
          <div class="text-subtitle1 text-weight-bold q-mb-sm">
            Top tipos de muestra (preanalítica)
          </div>
          <apexchart
            type="bar"
            height="320"
            :options="chartMuestrasTipo.options"
            :series="chartMuestrasTipo.series"
          />
        </q-card>
      </div>
    </div>

    <!-- TABLA DE ÚLTIMAS SOLICITUDES (CON ÁREAS Y Nº SERVICIOS) -->
    <q-card flat bordered>
      <q-card-section class="row items-center">
        <div class="col">
          <div class="text-subtitle1 text-weight-bold">
            Últimas solicitudes
          </div>
          <div class="text-caption text-grey-7">
            Las 20 solicitudes más recientes en el rango filtrado, con sus áreas y cantidad de servicios.
          </div>
        </div>
        <div class="col-auto">
          <q-btn
            flat
            dense
            icon="download"
            no-caps
            label="Exportar CSV"
            @click="exportCsv"
          />
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-none">
        <q-table
          :rows="ultimas"
          :columns="columnsUltimas"
          row-key="id"
          dense
          flat
          :rows-per-page-options="[10, 20, 0]"
        >
          <template v-slot:body-cell-estado="props">
            <q-td :props="props">
              <q-chip
                dense
                :color="colorEstado(props.row.estado)"
                text-color="white"
              >
                {{ props.row.estado || 'SIN ESTADO' }}
              </q-chip>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'SolicitudesDashboardPage',
  data () {
    return {
      loading: false,
      filters: {
        date_from: '',
        date_to: ''
      },
      resumen: {},
      ultimas: [],
      columnsUltimas: [
        { name: 'nro_registro', label: 'Nro Registro', field: 'nro_registro', align: 'left' },
        { name: 'codigo_solicitud', label: 'Código', field: 'codigo_solicitud', align: 'left' },
        { name: 'paciente_nombre', label: 'Paciente', field: 'paciente_nombre', align: 'left' },
        { name: 'doctor_nombre', label: 'Doctor', field: 'doctor_nombre', align: 'left' },
        { name: 'areas', label: 'Áreas', field: row => row.areas || '', align: 'left' },
        { name: 'cant_servicios', label: 'N° servicios', field: row => row.cant_servicios, align: 'right' },
        { name: 'tipo_atencion', label: 'Tipo atención', field: 'tipo_atencion', align: 'left' },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left' },
        { name: 'fecha_solicitud', label: 'Fecha', field: 'fecha_solicitud', align: 'left' },
        { name: 'hora_solicitud', label: 'Hora', field: 'hora_solicitud', align: 'left' }
      ],
      // CHARTS
      chartAreas: {
        series: [],
        options: {
          labels: [],
          legend: { position: 'bottom' },
          dataLabels: { enabled: true }
        }
      },
      chartServiciosArea: {
        series: [],
        options: {
          xaxis: { categories: [] },
          plotOptions: { bar: { horizontal: false, columnWidth: '50%' } },
          dataLabels: { enabled: false },
          legend: { position: 'top' }
        }
      },
      chartTopServicios: {
        series: [{ name: 'Solicitudes', data: [] }],
        options: {
          xaxis: { categories: [] },
          plotOptions: { bar: { horizontal: true, barHeight: '70%' } },
          dataLabels: { enabled: false }
        }
      },
      chartSerieFechas: {
        series: [{ name: 'Solicitudes', data: [] }],
        options: {
          xaxis: { categories: [] },
          dataLabels: { enabled: false },
          stroke: { curve: 'smooth' },
          tooltip: { x: { format: 'yyyy-MM-dd' } }
        }
      },
      chartMuestrasTipo: {
        series: [{ name: 'Muestras', data: [] }],
        options: {
          xaxis: { categories: [] },
          plotOptions: { bar: { horizontal: true } },
          dataLabels: { enabled: false }
        }
      }
    }
  },
  mounted () {
    // Por defecto últimos 30 días
    const today = moment().format('YYYY-MM-DD')
    const from = moment().subtract(30, 'days').format('YYYY-MM-DD')
    this.filters.date_from = from
    this.filters.date_to = today
    this.fetchDashboard()
  },
  methods: {
    async fetchDashboard () {
      this.loading = true
      try {
        const res = await this.$axios.get('reportes/solicitudes-dashboard', {
          params: {
            date_from: this.filters.date_from,
            date_to: this.filters.date_to
          }
        })

        const data = res.data || {}
        this.resumen = data.resumen || {}
        this.ultimas = data.ultimas || []

        // --- Áreas: donut (solo solicitudes por área)
        const porArea = data.por_area || []
        this.chartAreas.series = porArea.map(e => e.solicitudes)
        this.chartAreas.options = {
          ...this.chartAreas.options,
          labels: porArea.map(e => e.area_nombre || 'SIN ÁREA')
        }

        // --- Áreas: bar (solicitudes vs servicios)
        this.chartServiciosArea.series = [
          {
            name: 'Solicitudes',
            data: porArea.map(e => e.solicitudes)
          },
          {
            name: 'Servicios',
            data: porArea.map(e => e.servicios)
          }
        ]
        this.chartServiciosArea.options = {
          ...this.chartServiciosArea.options,
          xaxis: { categories: porArea.map(e => e.area_nombre || 'SIN ÁREA') }
        }

        // --- Top servicios más solicitados
        const topServ = data.top_servicios || []
        this.chartTopServicios.series = [
          {
            name: 'Solicitudes',
            data: topServ.map(e => e.total)
          }
        ]
        this.chartTopServicios.options = {
          ...this.chartTopServicios.options,
          xaxis: { categories: topServ.map(e => e.servicio_nombre || 'SIN NOMBRE') }
        }

        // --- Serie fechas (area)
        const serie = data.serie_fechas || []
        this.chartSerieFechas.series = [
          { name: 'Solicitudes', data: serie.map(e => e.total) }
        ]
        this.chartSerieFechas.options = {
          ...this.chartSerieFechas.options,
          xaxis: { categories: serie.map(e => e.fecha) }
        }

        // --- Top tipos de muestra (preanalítica)
        const tipos = data.por_tipo_muestra || []
        this.chartMuestrasTipo.series = [
          { name: 'Muestras', data: tipos.map(e => e.total) }
        ]
        this.chartMuestrasTipo.options = {
          ...this.chartMuestrasTipo.options,
          xaxis: {
            categories: tipos.map(e =>
              `${e.tipo_muestra} (${e.area_nombre || 'SIN ÁREA'})`
            )
          }
        }
      } catch (e) {
        this.$alert?.error(e.response?.data?.message || 'Error cargando dashboard')
      } finally {
        this.loading = false
      }
    },
    colorEstado (estado) {
      switch (estado) {
        case 'CREADO': return 'grey-7'
        case 'ATENDIENDO': return 'orange'
        case 'ENVIADO_ANALITICA': return 'indigo'
        case 'ANALITICA_ATENDIENDO': return 'blue'
        case 'FINALIZADO': return 'positive'
        default: return 'blue-grey'
      }
    },
    exportCsv () {
      const rows = this.ultimas
      if (!rows.length) {
        this.$alert?.info('No hay datos para exportar')
        return
      }
      const header = this.columnsUltimas.map(c => c.label).join(',')
      const body = rows.map(r => [
        r.nro_registro,
        r.codigo_solicitud,
        `"${(r.paciente_nombre || '').replace(/"/g, '""')}"`,
        `"${(r.doctor_nombre || '').replace(/"/g, '""')}"`,
        `"${(r.areas || '').replace(/"/g, '""')}"`,
        r.cant_servicios ?? 0,
        r.tipo_atencion || '',
        r.estado || '',
        r.fecha_solicitud || '',
        r.hora_solicitud || ''
      ].join(','))
      const csv = [header, ...body].join('\n')

      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = 'solicitudes_dashboard.csv'
      a.click()
      URL.revokeObjectURL(url)
    }
  }
}
</script>

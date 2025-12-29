<template>
  <q-page class="q-pa-md bg-grey-2">

    <!-- HEADER / FILTROS -->
    <div class="row items-center q-mb-md">
      <div class="col-12 col-md-6">
        <div class="text-h5 text-weight-bold">Reporte de Servicios</div>
        <div class="text-caption text-grey-7">
          Servicios más cotizados (total Bs) por rango de fechas, con filtro por usuario.
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="row items-center q-gutter-sm justify-end">
          <div class="col-12 col-sm-4">
            <q-input v-model="filters.date_from" type="date" dense outlined label="Desde" />
          </div>
          <div class="col-12 col-sm-4">
            <q-input v-model="filters.date_to" type="date" dense outlined label="Hasta" />
          </div>
          <div class="col-12 col-sm-4">
            <q-select
              v-model="filters.user_id"
              dense outlined
              label="Usuario (opcional)"
              :options="users.map(u => ({ label: `${u.name} (${u.username})`, value: u.id }))"
              emit-value map-options
              clearable
            />
          </div>
          <div class="col-auto">
            <q-btn color="primary" icon="refresh" label="Actualizar" no-caps
                   :loading="loading" @click="fetchData" />
          </div>
        </div>
      </div>
    </div>

    <!-- KPIs -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="q-pa-md">
          <div class="text-caption text-grey-7">Items (servicios)</div>
          <div class="text-h5 text-weight-bold">{{ rows.length }}</div>
          <div class="text-caption text-grey-6">Servicios distintos en el rango.</div>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="q-pa-md">
          <div class="text-caption text-grey-7">Total Bs</div>
          <div class="text-h5 text-weight-bold">{{ money(totalGeneral) }}</div>
          <div class="text-caption text-grey-6">Suma de todos los servicios.</div>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="q-pa-md">
          <div class="text-caption text-grey-7">Top servicio</div>
          <div class="text-subtitle1 text-weight-bold">
            {{ rows[0]?.servicio_nombre || '—' }}
          </div>
          <div class="text-caption text-grey-6">
            {{ rows[0] ? money(rows[0].total_monto) : '—' }}
          </div>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="q-pa-md">
          <div class="text-caption text-grey-7">Exportar</div>
          <div class="row q-gutter-sm q-mt-sm">
            <q-btn outline color="primary" icon="grid_on" label="Excel" no-caps
                   :disable="loading" @click="downloadExcel" />
            <q-btn outline color="negative" icon="picture_as_pdf" label="PDF" no-caps
                   :disable="loading" @click="openPdf" />
          </div>
        </q-card>
      </div>
    </div>

    <!-- TABLA -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center">
        <div class="col">
          <div class="text-subtitle1 text-weight-bold">Servicios (más cotizado → menos)</div>
          <div class="text-caption text-grey-7">
            Muestra total Bs, cantidad de veces y el área.
          </div>
        </div>
        <div class="col-auto">
          <q-input v-model="filter" dense outlined placeholder="Buscar servicio/área..." style="min-width: 260px">
            <template v-slot:append><q-icon name="search" /></template>
          </q-input>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-none">
        <q-table
          :rows="rows"
          :columns="columns"
          row-key="servicio_id"
          dense flat
          :filter="filter"
          :rows-per-page-options="[10, 20, 50, 0]"
        >
          <template v-slot:body-cell-total_monto="props">
            <q-td :props="props" class="text-right">
              <q-chip dense color="primary" text-color="white">
                {{ money(props.row.total_monto) }}
              </q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-area_nombre="props">
            <q-td :props="props">
              <q-chip dense color="grey-3" text-color="black">
                {{ props.row.area_nombre }}
              </q-chip>
            </q-td>
          </template>

        </q-table>
      </q-card-section>
    </q-card>

    <!-- GRÁFICO -->
    <q-card flat bordered>
      <q-card-section>
        <div class="text-subtitle1 text-weight-bold">Gráfico: Top 20 por Total Bs</div>
        <div class="text-caption text-grey-7">
          (Para no saturar el gráfico, solo se muestra el Top 20.)
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <apexchart
          type="bar"
          height="360"
          :options="chart.options"
          :series="chart.series"
        />
      </q-card-section>
    </q-card>

  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'ReportesServiciosResumenPage',
  data () {
    return {
      loading: false,
      filter: '',
      users: [],
      filters: {
        date_from: '',
        date_to: '',
        user_id: null
      },
      rows: [],
      columns: [
        { name: 'servicio_nombre', label: 'Servicio', field: 'servicio_nombre', align: 'left', sortable: true },
        { name: 'area_nombre', label: 'Área', field: 'area_nombre', align: 'left', sortable: true },
        { name: 'cantidad', label: 'Cantidad', field: 'cantidad', align: 'right', sortable: true },
        { name: 'solicitudes', label: 'Solicitudes', field: 'solicitudes', align: 'right', sortable: true },
        { name: 'precio_promedio', label: 'Precio prom.', field: 'precio_promedio', align: 'right', sortable: true },
        { name: 'total_monto', label: 'Total Bs', field: 'total_monto', align: 'right', sortable: true }
      ],
      chart: {
        series: [{ name: 'Total Bs', data: [] }],
        options: {
          xaxis: { categories: [] },
          plotOptions: { bar: { horizontal: true, barHeight: '70%' } },
          dataLabels: { enabled: false },
          tooltip: {
            y: { formatter: val => `${val} Bs` }
          }
        }
      }
    }
  },
  computed: {
    totalGeneral () {
      return (this.rows || []).reduce((acc, r) => acc + (parseFloat(r.total_monto) || 0), 0)
    }
  },
  async mounted () {
    const today = moment().format('YYYY-MM-DD')
    const from = moment().subtract(30, 'days').format('YYYY-MM-DD')
    this.filters.date_from = from
    this.filters.date_to = today

    await this.loadUsers()
    await this.fetchData()
  },
  methods: {
    money (n) {
      const x = parseFloat(n || 0)
      return x.toFixed(2) + ' Bs'
    },
    async loadUsers () {
      try {
        this.users = await this.$axios.get('users').then(r => r.data)
      } catch (e) {
        this.$alert?.error(e.response?.data?.message || 'No se pudo cargar usuarios')
      }
    },
    async fetchData () {
      this.loading = true
      try {
        const res = await this.$axios.get('reportes/servicios-resumen', {
          params: {
            date_from: this.filters.date_from,
            date_to: this.filters.date_to,
            user_id: this.filters.user_id
          }
        })
        const data = res.data || {}
        this.rows = data.rows || []

        // Chart top 20
        const chart = data.chart || []
        this.chart.series = [{ name: 'Total Bs', data: chart.map(x => x.total) }]
        this.chart.options = {
          ...this.chart.options,
          xaxis: { categories: chart.map(x => x.servicio) }
        }
      } catch (e) {
        this.$alert?.error(e.response?.data?.message || 'Error cargando reporte')
      } finally {
        this.loading = false
      }
    },
    downloadExcel () {
      const params = new URLSearchParams({
        date_from: this.filters.date_from || '',
        date_to: this.filters.date_to || ''
      })
      if (this.filters.user_id) params.set('user_id', this.filters.user_id)

      // Descarga directa
      window.open(`${this.$url}/reportes/servicios-resumen/excel?${params.toString()}`, '_blank')
    },
    openPdf () {
      const params = new URLSearchParams({
        date_from: this.filters.date_from || '',
        date_to: this.filters.date_to || ''
      })
      if (this.filters.user_id) params.set('user_id', this.filters.user_id)

      window.open(`${this.$url}/reportes/servicios-resumen/pdf?${params.toString()}`, '_blank')
    }
  }
}
</script>

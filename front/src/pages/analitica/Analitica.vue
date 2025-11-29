<template>
  <q-page class="q-pa-sm">
    <!-- HEADER / FILTROS -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row items-center q-col-gutter-xs">
        <div class="col-12 col-sm-4">
          <div class="text-h6">Área Analítica</div>
          <div class="text-caption text-grey-7">
            Solicitudes recibidas de Preanalítica (estado ENVIADO_ANALITICA)
          </div>
        </div>

        <div class="col-12 col-sm-4">
          <q-input
            v-model="filter"
            dense
            outlined
            debounce="400"
            label="Buscar (paciente / CI / establecimiento)"
          >
            <template #prepend>
              <q-icon name="search" />
            </template>
            <template #append>
              <q-btn
                flat
                round
                dense
                icon="clear"
                @click="clearFilter"
                v-if="filter"
              />
            </template>
          </q-input>
        </div>

        <div class="col-12 col-sm-4 text-right">
          <q-btn
            color="primary"
            icon="refresh"
            label="Actualizar"
            no-caps
            :loading="loading"
            @click="reloadTable"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- TABLA -->
    <q-card flat bordered>
      <q-table
        ref="tableAnalitica"
        :rows="rows"
        :columns="columns"
        row-key="id"
        dense
        flat
        bordered
        :loading="loading"
        :pagination.sync="pagination"
        :rows-per-page-options="[10, 20, 50]"
        @request="onRequest"
        @rowClick="goToDetalle"
      >
        <template #top>
          <div class="row items-center full-width q-pa-xs">
            <div class="col">
              <div class="text-subtitle1">Prestaciones</div>
              <div class="text-caption text-grey-7">
                Mostrando solicitudes con estado <b>ENVIADO_ANALITICA</b>
              </div>
            </div>
          </div>
        </template>

        <!-- PACIENTE -->
        <template #body-cell-paciente="props">
          <q-td :props="props">
            <div class="text-weight-medium">
              {{ props.row.paciente_nombre || props.row.paciente?.nombre_completo }}
            </div>
            <div class="text-caption text-grey-7">
              CI: {{ props.row.paciente_ci || props.row.paciente?.ci || '-' }}
            </div>
          </q-td>
        </template>

        <!-- ESTABLECIMIENTO -->
        <template #body-cell-establecimiento="props">
          <q-td :props="props">
            <div>{{ props.row.establecimiento_salud || '-' }}</div>
          </q-td>
        </template>

        <!-- TIPO ATENCIÓN -->
        <template #body-cell-tipo_atencion="props">
          <q-td :props="props">
            <q-chip
              dense
              :color="props.row.tipo_atencion === 'SI' ? 'green-6' : 'orange-6'"
              text-color="white"
            >
              {{
                props.row.tipo_atencion === 'SI'
                  ? 'SUS SI'
                  : props.row.tipo_otro || 'SUS NO'
              }}
            </q-chip>
          </q-td>
        </template>

        <!-- ESTADO -->
        <template #body-cell-estado="props">
          <q-td :props="props">
            <q-chip
              dense
              :color="props.row.estado === 'ENVIADO_ANALITICA' ? 'purple-6' : 'grey-6'"
              text-color="white"
              icon="local_shipping"
            >
              {{ props.row.estado }}
            </q-chip>
          </q-td>
        </template>

        <!-- CÓDIGO -->
        <template #body-cell-codigo="props">
          <q-td :props="props">
            <div v-if="props.row.codigo">
              <span class="text-bold">
                {{ props.row.codigo }} - {{ props.row.nro_registro }}
              </span>
            </div>
            <div v-else class="text-negative text-caption">
              Sin código
            </div>
          </q-td>
        </template>

        <!-- # SERVICIOS -->
        <template #body-cell-servicios_count="props">
          <q-td :props="props" class="text-center">
            <q-badge color="primary" :label="props.row.servicios?.length || 0" />
          </q-td>
        </template>

        <!-- LISTA SERVICIOS -->
        <template #body-cell-servicios="props">
          <q-td :props="props">
            <ul class="q-pa-none q-ma-none">
              <li v-for="servicio in props.row.servicios" :key="servicio.id">
                {{ textCapitalize(servicio.nombre) }}
              </li>
            </ul>
          </q-td>
        </template>

        <!-- RESPONSABLES -->
        <template #body-cell-responsables="props">
          <q-td :props="props">
            <div class="text-caption">
              <span class="text-grey-7">Preanalítica:</span>
              <b>{{ props.row.user_preanalitica?.name || 'No asignado' }}</b>
            </div>
            <div class="text-caption q-mt-xs">
              <span class="text-grey-7">Analítica:</span>
              <b>{{ props.row.user_analitica?.name || 'No asignado' }}</b>
            </div>
          </q-td>
        </template>

        <!-- FOOTER -->
        <template #bottom="scope">
          <div
            class="row items-center justify-between full-width q-px-sm q-py-xs"
          >
            <div class="col-12 col-sm-4 text-caption q-mb-xs q-mb-sm-none">
              Mostrando
              <b>{{ firstRowIndex(scope.pagination) }} - {{ lastRowIndex(scope.pagination) }}</b>
              de
              <b>{{ scope.pagination.rowsNumber }}</b>
              Prestaciones
            </div>

            <div class="col-12 col-sm-8">
              <div class="row items-center justify-end q-gutter-sm">
                <div class="col-auto">
                  <q-select
                    v-model="pagination.rowsPerPage"
                    :options="[10, 20, 50]"
                    dense
                    outlined
                    options-dense
                    style="width: 90px"
                    label="Filas"
                    @update:model-value="onChangeRowsPerPage"
                  />
                </div>
                <div class="col-auto">
                  <q-pagination
                    v-model="pagination.page"
                    :max="pagesNumber"
                    max-pages="7"
                    boundary-links
                    direction-links
                    icon-first="first_page"
                    icon-last="last_page"
                    icon-prev="chevron_left"
                    icon-next="chevron_right"
                    size="sm"
                    @update:model-value="onChangePage"
                  />
                </div>
              </div>
            </div>
          </div>
        </template>
      </q-table>
    </q-card>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'AreaAnaliticaListPage',
  data () {
    return {
      rows: [],
      loading: false,
      filter: '',
      pagination: {
        page: 1,
        rowsPerPage: 10,
        rowsNumber: 0,
        sortBy: 'id',
        descending: true
      },
      columns: [
        {
          name: 'fecha_creacion',
          label: 'Fecha Solicitud',
          field: row => row.fecha_creacion,
          sortable: true,
          align: 'left',
          format: val => (val ? moment(val).format('DD/MM/YYYY HH:mm') : '')
        },
        {
          name: 'paciente',
          label: 'Paciente',
          field: row =>
            row.paciente_nombre ||
            (row.paciente && row.paciente.nombre_completo) ||
            '',
          align: 'left'
        },
        {
          name: 'doctor',
          label: 'Médico Solicitante',
          field: row =>
            row.doctor_nombre || (row.doctor && row.doctor.name) || '',
          align: 'left'
        },
        {
          name: 'servicios',
          label: 'Prestaciones',
          field: row =>
            row.servicios ? row.servicios.map(s => s.nombre).join(', ') : '',
          align: 'left'
        },
        {
          name: 'establecimiento',
          label: 'Establecimiento',
          field: 'establecimiento_salud',
          align: 'left'
        },
        {
          name: 'tipo_atencion',
          label: 'Tipo atención',
          field: 'tipo_atencion',
          align: 'left'
        },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left' },
        { name: 'codigo', label: 'Código', field: 'codigo', align: 'left' },
        {
          name: 'servicios_count',
          label: '# Prestaciones',
          field: row => (row.servicios ? row.servicios.length : 0),
          align: 'center'
        },
        {
          name: 'responsables',
          label: 'Responsables',
          field: row => row.user_preanalitica?.name,
          align: 'left'
        }
      ]
    }
  },
  computed: {
    pagesNumber () {
      const { rowsPerPage, rowsNumber } = this.pagination
      if (!rowsPerPage || rowsPerPage <= 0) return 1
      return Math.max(1, Math.ceil(rowsNumber / rowsPerPage))
    }
  },
  mounted () {
    this.reloadTable()
  },
  methods: {
    textCapitalize (text) {
      if (!text) return ''
      return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase()
    },
    firstRowIndex (pag) {
      if (pag.rowsNumber === 0) return 0
      return (pag.page - 1) * pag.rowsPerPage + 1
    },
    lastRowIndex (pag) {
      if (pag.rowsNumber === 0) return 0
      const last = pag.page * pag.rowsPerPage
      return last > pag.rowsNumber ? pag.rowsNumber : last
    },
    clearFilter () {
      this.filter = ''
      this.reloadTable()
    },
    reloadTable () {
      if (this.$refs.tableAnalitica) {
        this.$refs.tableAnalitica.requestServerInteraction()
      } else {
        this.fetchFromServer(this.pagination, this.filter)
      }
    },
    onRequest (props) {
      const { page, rowsPerPage, sortBy, descending } = props.pagination
      this.pagination.page = page
      this.pagination.rowsPerPage = rowsPerPage
      this.pagination.sortBy = sortBy
      this.pagination.descending = descending

      this.fetchFromServer(this.pagination, this.filter)
    },
    fetchFromServer (pagination, filter) {
      this.loading = true
      this.$axios
        .get('solicitudes-area-analitica', {
          params: {
            page: pagination.page,
            per_page: pagination.rowsPerPage,
            filter: filter || ''
          }
        })
        .then(res => {
          this.rows = res.data.data || []
          this.pagination.rowsNumber = res.data.total || 0
        })
        .catch(err => {
          console.error(err)
          this.$alert?.error?.(
            'Error al cargar solicitudes para Área Analítica'
          )
        })
        .finally(() => {
          this.loading = false
        })
    },
    onChangePage (page) {
      this.pagination.page = page
      this.reloadTable()
    },
    onChangeRowsPerPage (val) {
      this.pagination.rowsPerPage = val
      this.pagination.page = 1
      this.reloadTable()
    },
    // NUEVO: ir al detalle
    goToDetalle (evt, row) {
      if (!row || !row.id) return
      this.$router.push({ name: 'analitica-detalle', params: { id: row.id } })
    }
  }
}
</script>

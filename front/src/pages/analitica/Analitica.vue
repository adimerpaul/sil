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
              <q-btn flat round dense icon="clear" @click="clearFilter" v-if="filter" />
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
        @rowClick="openDialogAnalitica"
      >
        <template #top>
          <div class="row items-center full-width q-pa-xs">
            <div class="col">
              <div class="text-subtitle1">Solicitudes</div>
              <div class="text-caption text-grey-7">
                Mostrando solicitudes con estado <b>ENVIADO_ANALITICA</b>
              </div>
            </div>
          </div>
        </template>

        <!-- COLUMNA PACIENTE -->
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

        <!-- COLUMNA ESTABLECIMIENTO -->
        <template #body-cell-establecimiento="props">
          <q-td :props="props">
            <div>{{ props.row.establecimiento_salud || '-' }}</div>
          </q-td>
        </template>

        <!-- COLUMNA TIPO ATENCIÓN -->
        <template #body-cell-tipo_atencion="props">
          <q-td :props="props">
            <q-chip
              dense
              :color="props.row.tipo_atencion === 'SI' ? 'green-6' : 'orange-6'"
              text-color="white"
            >
              {{ props.row.tipo_atencion === 'SI' ? 'SUS SI' : props.row.tipo_otro || 'SUS NO' }}
            </q-chip>
          </q-td>
        </template>

        <!-- COLUMNA ESTADO -->
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

        <!-- COLUMNA CÓDIGO -->
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

        <!-- COLUMNA SERVICIOS -->
        <template #body-cell-servicios_count="props">
          <q-td :props="props" class="text-center">
            <q-badge color="primary" :label="props.row.servicios?.length || 0" />
          </q-td>
        </template>

        <!-- COLUMNA RESPONSABLES -->
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
          <div class="row items-center justify-between full-width q-px-sm q-py-xs">
            <div class="col-12 col-sm-4 text-caption q-mb-xs q-mb-sm-none">
              Mostrando
              <b>{{ firstRowIndex(scope.pagination) }} - {{ lastRowIndex(scope.pagination) }}</b>
              de
              <b>{{ scope.pagination.rowsNumber }}</b> solicitudes
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

    <!-- DIALOGO DETALLE ANALÍTICA -->
    <q-dialog
      v-model="dialogAnalitica"
      persistent
      transition-show="jump-down"
      transition-hide="jump-up"
    >
      <q-card class="q-pa-none" style="max-width: 900px;">
        <!-- HEADER -->
        <q-card-section class="bg-purple-8 text-white">
          <div class="row items-center no-wrap">
            <div className="col">
              <div class="text-subtitle1 flex items-center q-gutter-sm">
                <q-icon name="science" />
                <span>Detalle de Solicitud - Área Analítica</span>
              </div>
              <div class="text-caption q-mt-xs">
                Código muestra:
                <span class="text-bold">
                  {{ solicitud.codigo || 'Sin código' }}
                  {{ solicitud.nro_registro ? (' - ' + solicitud.nro_registro) : '' }}
                </span>
              </div>
            </div>

            <div class="col-auto column items-end q-gutter-xs">
              <q-chip
                dense
                square
                :color="solicitud.estado === 'ENVIADO_ANALITICA' ? 'purple-5' : 'grey-6'"
                text-color="white"
                icon="local_shipping"
              >
                {{ solicitud.estado || 'SIN ESTADO' }}
              </q-chip>

              <q-chip
                dense
                square
                :color="solicitud.tipo_atencion === 'SI' ? 'green-5' : 'orange-5'"
                text-color="white"
                icon="health_and_safety"
              >
                {{ solicitud.tipo_atencion === 'SI'
                ? 'SUS SI'
                : (solicitud.tipo_otro || 'SUS NO') }}
              </q-chip>
            </div>

            <div class="col-auto">
              <q-btn dense flat round icon="close" v-close-popup />
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <!-- CONTENIDO -->
        <q-card-section class="q-pa-none">
          <div class="q-pa-md q-gutter-md">
            <!-- Datos solicitud -->
            <div>
              <div class="text-subtitle2 text-primary q-mb-xs">
                Datos de la solicitud
              </div>
              <q-separator spaced />

              <div class="row q-col-gutter-md">
                <div class="col-12 col-sm-6">
                  <div class="text-caption text-grey-7">Fecha de solicitud</div>
                  <div class="text-body2">
                    {{ solicitud.fecha_creacion || '-' }}
                  </div>

                  <div class="text-caption text-grey-7 q-mt-sm">
                    Fecha recepción preanalítica
                  </div>
                  <div class="text-body2">
                    {{ solicitud.fecha_pre_analitica || '-' }}
                  </div>

                  <div class="text-caption text-grey-7 q-mt-sm">
                    Fecha recepción analítica
                  </div>
                  <div class="text-body2">
                    {{ solicitud.fecha_envio_analitica || '-' }}
                  </div>
                </div>

                <div class="col-12 col-sm-6">
                  <div class="text-caption text-grey-7">Responsable Preanalítica</div>
                  <div class="text-body2">
                    {{ solicitud.user_preanalitica?.name || 'No asignado' }}
                  </div>

                  <div class="text-caption text-grey-7 q-mt-sm">Responsable Analítica</div>
                  <div class="text-body2">
                    {{ solicitud.user_analitica?.name || 'No asignado' }}
                  </div>

                  <div class="text-caption text-grey-7 q-mt-sm">Establecimiento</div>
                  <div class="text-body2">
                    {{ solicitud.establecimiento_salud || '-' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Paciente -->
            <div>
              <div class="text-subtitle2 text-primary q-mb-xs">
                Datos del paciente
              </div>
              <q-separator spaced />

              <div class="row q-col-gutter-md">
                <div class="col-12 col-sm-6">
                  <div class="text-caption text-grey-7">Paciente</div>
                  <div class="text-body1 text-weight-medium">
                    {{
                      solicitud.paciente_nombre
                      || solicitud.paciente?.nombre_completo
                      || '-'
                    }}
                  </div>

                  <div class="text-caption text-grey-7 q-mt-sm">Edad</div>
                  <div class="text-body2">
                    {{ solicitud.paciente_edad || solicitud.paciente?.edad || '-' }} años
                  </div>
                </div>

                <div class="col-12 col-sm-6">
                  <div class="text-caption text-grey-7">CI</div>
                  <div class="text-body2">
                    {{ solicitud.paciente_ci || solicitud.paciente?.ci || '-' }}
                  </div>

                  <div class="text-caption text-grey-7 q-mt-sm">Teléfono</div>
                  <div class="text-body2">
                    {{ solicitud.paciente_telefono || solicitud.paciente?.telefono || '-' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Servicios solicitados -->
            <div>
              <div class="text-subtitle2 text-primary q-mb-xs">
                Servicios solicitados
              </div>
              <q-separator spaced />

              <div v-if="solicitud.servicios && solicitud.servicios.length">
                <q-list bordered separator dense>
                  <q-item
                    v-for="servicio in solicitud.servicios"
                    :key="servicio.id"
                    class="q-py-xs"
                  >
                    <q-item-section avatar>
                      <q-icon name="biotech" />
                    </q-item-section>
                    <q-item-section>
                      <div class="text-body2">
                        {{ textCapitalize(servicio.nombre) }}
                      </div>
                      <div class="text-caption text-grey-7">
                        {{ textCapitalize(servicio.area?.name) }}
                      </div>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
              <div v-else class="text-caption text-grey-7">
                No hay servicios registrados.
              </div>
            </div>

            <!-- Muestras preanalíticas -->
            <div>
              <div class="text-subtitle2 text-primary q-mb-xs">
                Muestras preanalíticas
              </div>
              <q-separator spaced />

              <div
                v-if="solicitud.pre_analitica_muestras && solicitud.pre_analitica_muestras.length"
              >
                <q-list bordered dense>
                  <q-item
                    v-for="m in solicitud.pre_analitica_muestras"
                    :key="m.id"
                    class="q-py-xs"
                  >
                    <q-item-section>
                      <div class="text-body2">
                        {{ m.area_tipo_muestra?.tipo_muestra || m.nombre || 'Muestra' }}
                      </div>
                      <div class="text-caption text-grey-7">
                        Estado:
                        <q-select
                          v-model="m.estado"
                          :options="['Pendiente', 'En proceso', 'Procesado']"
                          dense
                          outlined
                          emit-value
                          map-options
                          style="max-width: 150px; display: inline-block;"
                        />
                      </div>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
              <div v-else class="text-caption text-grey-7">
                No se registraron muestras en preanalítica.
              </div>
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <!-- ACCIONES -->
        <q-card-actions align="right" class="bg-grey-1">
          <q-btn
            flat
            label="Cerrar"
            color="primary"
            icon="close"
            v-close-popup
            no-caps
          />
          <q-btn
            unelevated
            color="primary"
            icon="done_all"
            :loading="savingAnalitica"
            no-caps
            @click="guardarAnalitica"
          >
            Guardar Analítica y Finalizar
          </q-btn>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'AreaAnaliticaPage',
  data () {
    return {
      dialogAnalitica: false,
      rows: [],
      loading: false,
      filter: '',
      savingAnalitica: false,
      solicitud: {},
      pagination: {
        page: 1,
        rowsPerPage: 10,
        rowsNumber: 0,
        sortBy: 'id',
        descending: true
      },
      columns: [
        { name: 'fecha_creacion', label: 'Fecha Solicitud', field: row => row.fecha_creacion, sortable: true, align: 'left',
          format: val => (val ? moment(val).format('DD/MM/YYYY HH:mm') : '') },
        {
          name: 'paciente',
          label: 'Paciente',
          field: row => row.paciente_nombre || (row.paciente && row.paciente.nombre_completo) || '',
          align: 'left'
        },
        { name: 'establecimiento', label: 'Establecimiento', field: row => row.establecimiento_salud, align: 'left' },
        { name: 'tipo_atencion', label: 'Tipo atención', field: 'tipo_atencion', align: 'left' },
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
      this.$axios.get('solicitudes-area-analitica', {
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
          this.$alert && this.$alert.error
            ? this.$alert.error('Error al cargar solicitudes para Área Analítica')
            : null
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
    openDialogAnalitica (evt, row) {
      this.solicitud = JSON.parse(JSON.stringify(row || {}))
      this.dialogAnalitica = true
    },
    guardarAnalitica () {
      if (!this.solicitud || !this.solicitud.id) return
      this.savingAnalitica = true

      this.$axios.post(`solicitudes/${this.solicitud.id}/analitica`, {
        muestras: this.solicitud.pre_analitica_muestras || []
      })
        .then(res => {
          this.$alert && this.$alert.success
            ? this.$alert.success('Analítica guardada y solicitud finalizada')
            : null
          this.dialogAnalitica = false
          this.reloadTable()
        })
        .catch(err => {
          console.error(err)
          const msg = err.response?.data?.message || err.message
          this.$alert && this.$alert.error
            ? this.$alert.error('Error al guardar analítica: ' + msg)
            : null
        })
        .finally(() => {
          this.savingAnalitica = false
        })
    }
  }
}
</script>

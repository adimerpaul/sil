<template>
  <q-page class="q-pa-sm">
    <!-- HEADER / FILTROS -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row items-center q-col-gutter-xs">
        <div class="col-12 col-sm-4">
          <div class="text-h6">Área Preanalítica</div>
          <div class="text-caption text-grey-7">
            Solicitudes pendientes de procesamiento
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
        ref="tablePreanalitica"
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
        @rowClick="openDialogSolicitud"
      >
        <template #top>
          <div class="row items-center full-width q-pa-xs">
            <div class="col">
              <div class="text-subtitle1">Solicitudes</div>
              <div class="text-caption text-grey-7">
                Mostrando solicitudes con estado <b>CREADO</b>
              </div>
            </div>
          </div>
        </template>

        <!-- COLUMNA PACIENTE BONITA -->
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
              :color="props.row.estado === 'CREADO' ? 'blue-6' : 'grey-6'"
              text-color="white"
              icon="pending"
            >
              {{ props.row.estado }}
            </q-chip>
          </q-td>
        </template>

        <!-- COLUMNA CÓDIGO -->
        <template #body-cell-codigo="props">
          <q-td :props="props">
            <div v-if="props.row.codigo">
<!--              <q-chip dense color="deep-purple-6" text-color="white" icon="confirmation_number">-->
              <span class="text-bold">
                {{ props.row.codigo }} -
                {{ props.row.nro_registro }}
                </span>
<!--              </q-chip>-->
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

        <!-- ACCIONES -->
        <template #body-cell-actions="props">
          <q-td :props="props" class="text-right">
            <q-btn
              v-if="props.row.estado === 'CREADO'"
              dense
              no-caps
              outline
              color="deep-purple-6"
              icon="confirmation_number"
              :label="props.row.codigo ? 'Ver código' : 'Generar código'"
              :loading="loadingRowId === props.row.id"
              @click="onGenerarCodigo(props.row)"
            />
          </q-td>
        </template>

        <!-- FOOTER CON PAGINACIÓN BONITA -->
        <template #bottom="scope">
          <div class="row items-center justify-between full-width q-px-sm q-py-xs">
            <!-- Info -->
            <div class="col-12 col-sm-4 text-caption q-mb-xs q-mb-sm-none">
              Mostrando
              <b>{{ firstRowIndex(scope.pagination) }} - {{ lastRowIndex(scope.pagination) }}</b>
              de
              <b>{{ scope.pagination.rowsNumber }}</b> solicitudes
            </div>

            <!-- Controles -->
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
<!--    dialogConsentimiento-->
    <q-dialog v-model="dialogConsentimiento" persistent max-width="600px">
      <q-card>
        <q-card-section class="q-pa-md">
          <div class="text-h6">Detalle de Solicitud</div>
        </q-card-section>
        <q-card-section class="q-pt-none">
          <div>
            <strong>Paciente:</strong> {{ consentimiento.paciente_nombre || consentimiento.paciente?.nombre_completo }}
            <strong>Edad:</strong> {{ consentimiento.edad || consentimiento.paciente?.edad }} años<br>
            <br>
            <strong>Fecha de Solicitud :</strong> {{ consentimiento.fecha_creacion }} <br>
            <strong>Fecha de Atencion Preanalitica:</strong> {{ consentimiento.fecha_atencion }}<br>
            <strong>Tiempo de Atención:</strong>
              <q-chip dense color="blue-6" text-color="white" icon="access_time">
              {{ tiempoAtencion( consentimiento.fecha_creacion ,consentimiento.fecha_atencion) || 'No registrado' }}
              </q-chip>
            <br>
            <strong>Establecimiento:</strong> {{ consentimiento.establecimiento_salud }} <br>
            <strong>Tipo de Atención:</strong>{{ consentimiento.tipo_atencion === 'SI' ? 'SUS SI' : consentimiento.tipo_otro || 'SUS NO' }} <br>

<!--            <strong>Estado:</strong> {{ consentimiento.estado }} <br>-->
            <strong>Código:</strong> {{ consentimiento.codigo || 'Sin código' }} {{consentimiento.nro_registro ? '- ' + consentimiento.nro_registro : ''}} <br>
            <strong>Responsable:</strong>
<!--            {{ // consentimiento.user_preanalitica?.name ? 'No asignado' }} <br>-->
            {{ consentimiento.user_preanalitica?.name || 'No asignado' }} <br>
<!--            <pre>-->
<!--              {{ consentimiento.user_preanalitica.name }}-->
<!--            </pre>-->
            <br>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cerrar" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'AreaPreanaliticaPage',
  data () {
    return {
      dialogConsentimiento: false,
      rows: [],
      loading: false,
      filter: '',
      loadingRowId: null,
      pagination: {
        page: 1,
        rowsPerPage: 10,
        rowsNumber: 0,
        sortBy: 'id',
        descending: true
      },
      columns: [
        { name: 'actions', label: 'Acciones', align: 'right' },
        // {
        //   name: 'fecha_solicitud',
        //   label: 'Fecha',
        //   field: row => row.fecha_solicitud,
        //   format: val => (val ? moment(val).format('DD/MM/YYYY') : ''),
        //   sortable: true,
        //   align: 'left'
        // },
        // {
        //   name: 'hora_solicitud',
        //   label: 'Hora',
        //   field: row => row.hora_solicitud,
        //   align: 'left'
        // },
        // fecha_creacion
        {
          name: 'fecha_creacion',
          label: 'Fecha Solicitud',
          field: row => row.fecha_creacion,
          format: val => (val ? moment(val).format('DD/MM/YYYY HH:mm') : ''),
          sortable: true,
          align: 'left'
        },
        {
          name: 'paciente',
          label: 'Paciente',
          field: row => row.paciente_nombre || (row.paciente && row.paciente.nombre_completo) || '',
          align: 'left'
        },
        {
          name: 'establecimiento',
          label: 'Establecimiento',
          field: row => row.establecimiento_salud,
          align: 'left'
        },
        {
          name: 'tipo_atencion',
          label: 'Tipo atención',
          field: 'tipo_atencion',
          align: 'left'
        },
        {
          name: 'estado',
          label: 'Estado',
          field: 'estado',
          align: 'left'
        },
        {
          name: 'codigo',
          label: 'Código',
          field: 'codigo',
          align: 'left'
        },
        {
          name: 'servicios_count',
          label: '# Serv.',
          field: row => (row.servicios ? row.servicios.length : 0),
          align: 'center'
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
    tiempoAtencion(fechaSolicitud, fechaAtencion) {
      if (!fechaSolicitud || !fechaAtencion) return null;

      const inicio = moment(fechaSolicitud);
      const fin = moment(fechaAtencion);

      const duracion = moment.duration(fin.diff(inicio));

      const dias = duracion.days();
      const horas = duracion.hours();
      const minutos = duracion.minutes();

      let resultado = '';
      if (dias > 0) resultado += `${dias} d `;
      if (horas > 0) resultado += `${horas} h `;
      if (minutos > 0) resultado += `${minutos} m`;

      return resultado.trim();
    },
    openDialogSolicitud(action, row, index) {
      this.dialogConsentimiento = true
      this.consentimiento = row
    },
    // Utils info footer
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
      if (this.$refs.tablePreanalitica) {
        this.$refs.tablePreanalitica.requestServerInteraction()
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
      this.$axios.get('solicitudes-area-preanalitica', {
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
            ? this.$alert.error('Error al cargar solicitudes')
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

    onGenerarCodigo (row) {
      this.loadingRowId = row.id
      this.$axios.post(`solicitudes/${row.id}/generar-codigo`)
        .then(res => {
          this.reloadTable()
          this.$alert && this.$alert.success
            ? this.$alert.success('Código generado correctamente')
            : null
        })
        .catch(err => {
          console.error(err)
          const msg = err.response?.data?.message || err.message
          this.$alert && this.$alert.error
            ? this.$alert.error('Error al generar código: ' + msg)
            : null
        })
        .finally(() => {
          this.loadingRowId = null
        })
    }
  }
}
</script>

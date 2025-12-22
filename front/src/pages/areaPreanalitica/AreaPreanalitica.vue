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
              {{ props.row.tipo_atencion === 'SI' ? 'SUS' : props.row.tipo_otro || 'EXT' }}
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
<!--        servicios-->
        <template #body-cell-servicios="props">
          <q-td :props="props">
<!--            lista de servicio en ul margin 0-->
            <ul class="q-pa-none q-ma-none">
              <li v-for="servicio in props.row.servicios" :key="servicio.id">
                {{ textCapitalize(servicio.nombre) }}
              </li>
            </ul>
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
              @click.stop="onGenerarCodigo(props.row)"
            />
            <span v-else class="text-red text-bold">
              Sin Enviar Muestra
            </span>
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
    <q-dialog
      v-model="dialogConsentimiento"
      persistent
      transition-show="jump-down"
      transition-hide="jump-up"
    >
      <q-card class="q-pa-none" style="max-width: 900px;width: 600px">
        <!-- HEADER -->
        <q-card-section class="bg-indigo-8 text-white">
          <div class="row items-center no-wrap">
            <div class="col">
              <div class="text-subtitle1 flex items-center q-gutter-sm">
                <q-icon name="inventory_2" />
                <span>Detalle de Solicitud</span>
              </div>
              <div class="text-caption q-mt-xs">
                Código muestra:
                <span class="text-bold">
              {{ consentimiento.codigo || 'Sin código' }}
              {{ consentimiento.nro_registro ? (' - ' + consentimiento.nro_registro) : '' }}
            </span>
              </div>
            </div>

            <div class="col-auto column items-end q-gutter-xs">
              <!-- Estado -->
              <q-chip
                dense
                square
                :color="consentimiento.estado === 'CREADO' ? 'blue-5' : 'grey-6'"
                text-color="white"
                icon="pending"
              >
                {{ consentimiento.estado || 'SIN ESTADO' }}
              </q-chip>

              <!-- Tipo de atención -->
              <q-chip
                dense
                square
                :color="consentimiento.tipo_atencion === 'SI' ? 'green-5' : 'orange-5'"
                text-color="white"
                icon="health_and_safety"
              >
                {{ consentimiento.tipo_atencion === 'SI'
                ? 'SUS SI'
                : (consentimiento.tipo_otro || 'SUS NO') }}
              </q-chip>
            </div>

            <div class="col-auto">
              <q-btn
                dense
                flat
                round
                icon="close"
                v-close-popup
              />
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <!-- CONTENIDO SCROLLEABLE -->
        <q-card-section class="q-pa-none">
          <div>
            <div class="q-pa-md q-gutter-md">
              <div>
                <div class="text-subtitle2 text-primary q-mb-xs">
                  Datos de la solicitud
                </div>
                <q-separator spaced />

                <div class="row q-col-gutter-md">
                  <div class="col-12 col-sm-6">
                    <div class="text-caption text-grey-7">Fecha de solicitud</div>
                    <div class="text-body2">
                      {{ consentimiento.fecha_creacion || '-' }}
                    </div>

                    <div class="text-caption text-grey-7 q-mt-sm">
                      Fecha recepción preanalítica
                    </div>
                    <div class="text-body2">
                      {{ consentimiento.fecha_pre_analitica || '-' }}
                    </div>

                    <div class="text-caption text-grey-7 q-mt-sm">
                      Tiempo de atención
                    </div>
                    <div>
                      <q-chip
                        dense
                        color="blue-6"
                        text-color="white"
                        icon="access_time"
                      >
                        {{
                          tiempoAtencion(
                            consentimiento.fecha_creacion,
                            consentimiento.fecha_pre_analitica
                          ) || 'No registrado'
                        }}
                      </q-chip>
                    </div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <div class="text-caption text-grey-7">Responsable de entrega</div>
                    <div class="text-body2">
                      {{ consentimiento.user_preanalitica?.name || 'No asignado' }}
                    </div>

                    <div class="text-caption text-grey-7 q-mt-sm">Establecimiento</div>
                    <div class="text-body2">
                      {{ consentimiento.establecimiento_salud || '-' }}
                    </div>
                    <!--                    sala y cama-->
                    <div class="text-caption text-grey-7 q-mt-sm">Sala / Cama</div>
                    <div class="text-body2">
                      <q-chip
                        v-if="consentimiento.sala || consentimiento.cama"
                        dense
                        color="grey-5"
                        text-color="black"
                        icon="hotel"
                      >
                        {{
                          (consentimiento.sala ? consentimiento.sala + ' / ' : '') +
                          (consentimiento.cama ? consentimiento.cama : '')
                        }}
                      </q-chip>
                      <span v-else class="text-grey-7">-</span>
                      <div class="text-caption text-grey-7 q-mt-sm">Tipo de paciente</div>
                      <div class="text-body2">
                        {{ consentimiento.cama === '' || consentimiento.cama === null ? 'Ambulatorio' : 'Internado' }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- BLOQUE: PACIENTE -->
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
                        consentimiento.paciente_nombre
                        || consentimiento.paciente?.nombre_completo
                        || '-'
                      }}
                    </div>

                    <div class="text-caption text-grey-7">Edad</div>
                    <div class="text-body2">
                      {{ consentimiento.edad || consentimiento.paciente?.edad || '-' }} años
                    </div>
                    <div class="text-caption text-grey-7">Sexo</div>
                    <div class="text-body2">
                      {{ consentimiento.paciente?.genero || '-' }}
                    </div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <div class="text-caption text-grey-7">Código</div>
                    <div class="text-body2">
                      {{ consentimiento.codigo || 'Sin código' }}
                    </div>

                    <div class="text-caption text-grey-7">Nro. de registro</div>
                    <div class="text-body2">
                      {{ consentimiento.nro_registro || 'Sin registro' }}
                    </div>
                    <div class="text-caption text-grey-7">Codigo Muestra</div>
                    <div class="text-body2">
<!--                      {{ consentimiento.codigo + '-' + consentimiento.nro_registro?.slice(0,3) || 'Sin código muestra' }} mejora para que no haya nunn ni undefinifd-->
                      {{ consentimiento.codigo
                        ? (consentimiento.nro_registro
                          ? consentimiento.codigo + '-' + consentimiento.nro_registro.slice(0,3)
                          : consentimiento.codigo + '-000')
                        : 'Sin código muestra'
                      }}
                    </div>
                  </div>
                  <div class="col-12">
                  </div>
                </div>
              </div>

              <!-- BLOQUE: DOCTOR SOLICITANTE -->
              <div>
                <div class="text-subtitle2 text-primary q-mb-xs">
                  Médico solicitante
                </div>
                <q-separator spaced />
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-sm-6">
                    <div class="text-caption text-grey-7">Médico</div>
                    <div class="text-body1 text-weight-medium">
                      {{
                        consentimiento.doctor_nombre
                        || consentimiento.doctor?.name
                        || '-'
                      }}
                    </div>
                  </div>
                  <div class="col-12 col-sm-6">
                    <div class="text-caption text-grey-7">Especialidad</div>
                    <div class="text-body2">
                      {{ consentimiento.doctor?.especialidad || '-' }}
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="text-caption text-grey-7 q-mt-sm">Diagnóstico clínico</div>
                    <div class="text-body2">
                      {{ consentimiento.diagnostico_clinico || '-' }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- BLOQUE: SERVICIOS -->
              <div>
                <div class="text-subtitle2 text-primary q-mb-xs">
                  Servicios solicitados
                </div>
                <q-separator spaced />

                <div v-if="consentimiento.servicios && consentimiento.servicios.length">
                  <q-list bordered separator dense>
                    <q-item
                      v-for="servicio in consentimiento.servicios"
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
              <div>
                <div class="q-ml-md q-mt-xs">
                  <template v-for="(atm, index) in areas_tipo_muestras" :key="atm.id">
                    <div class="text-subtitle2 text-primary q-mb-xs">
                      {{ atm.name }}
                    </div>
                    <q-separator spaced />
                    <div class="q-mb-md">
                      <q-checkbox
                        v-for="tipo_muestra in atm.area_tipo_muestras"
                        :key="tipo_muestra.id"
                        v-model="tipo_muestra.selected"
                        :label="tipo_muestra.tipo_muestra"
                        :true-value="true"
                        :false-value="false"
                      />
                    </div>
                  </template>

<!--                  <pre>{{areas_tipo_muestras}}</pre>-->
<!--                  [-->
<!--                  {-->
<!--                  "id": 3,-->
<!--                  "name": "UROANÁLISIS (Area 4)",-->
<!--                  "area_tipo_muestras": [-->
<!--                  {-->
<!--                  "id": 5,-->
<!--                  "area_id": 3,-->
<!--                  "tipo_muestra": "Orinas",-->
<!--                  "selected": false-->
<!--                  },-->
<!--                  {-->
<!--                  "id": 6,-->
<!--                  "area_id": 3,-->
<!--                  "tipo_muestra": "Heces",-->
<!--                  "selected": false-->
<!--                  },-->
<!--                  {-->
<!--                  "id": 7,-->
<!--                  "area_id": 3,-->
<!--                  "tipo_muestra": "Sangre capilar",-->
<!--                  "selected": false-->
<!--                  },-->
<!--                  {-->
<!--                  "id": 8,-->
<!--                  "area_id": 3,-->
<!--                  "tipo_muestra": "Cutánea",-->
<!--                  "selected": false-->
<!--                  }-->
<!--                  ]-->
<!--                  },-->
<!--                  {-->
<!--                  "id": 4,-->
<!--                  "name": "MICROBIOLOGÍA (Area 5)",-->
<!--                  "area_tipo_muestras": [-->
<!--                  {-->
<!--                  "id": 9,-->
<!--                  "area_id": 4,-->
<!--                  "tipo_muestra": "Orina",-->
<!--                  "selected": false-->
<!--                  },-->
<!--                  {-->
<!--                  "id": 10,-->
<!--                  "area_id": 4,-->
<!--                  "tipo_muestra": "Heces",-->
<!--                  "selected": false-->
<!--                  },-->
<!--                  {-->
<!--                  "id": 11,-->
<!--                  "area_id": 4,-->
<!--                  "tipo_muestra": "Líquidos",-->
<!--                  "selected": false-->
<!--                  },-->
<!--                  {-->
<!--                  "id": 12,-->
<!--                  "area_id": 4,-->
<!--                  "tipo_muestra": "Secreciones",-->
<!--                  "selected": false-->
<!--                  },-->
<!--                  {-->
<!--                  "id": 13,-->
<!--                  "area_id": 4,-->
<!--                  "tipo_muestra": "Otros",-->
<!--                  "selected": false-->
<!--                  }-->
<!--                  ]-->
<!--                  }-->
<!--                  ]-->

                </div>
<!--                <pre>{{areas_tipo_muestras}}</pre>-->
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
<!--          label="Guardar y Enviar a Distribucción"-->
          <q-btn
            unelevated
            color="primary"
            icon="send"
            :loading="savingPre"
            no-caps
            @click="guardarPreAnalitica"
          >
            Guardar y Enviar <br>a Distribución
          </q-btn>
        </q-card-actions>

      </q-card>
    </q-dialog>

  </q-page>
</template>

<script>
import moment from 'moment'
import consentimientos from "pages/consentimientos/Consentimientos.vue";

export default {
  name: 'AreaPreanaliticaPage',
  data () {
    return {
      dialogConsentimiento: false,
      rows: [],
      loading: false,
      filter: '',
      loadingRowId: null,
      savingPre: false,          // 👈 nuevo
      selectedMuestras: [],      // 👈 nuevo: ids de area_tipo_muestras
      consentimiento: null,
      areas_tipo_muestras: [],
      areas_tipo_muestrasAll: [],
      pagination: {
        page: 1,
        rowsPerPage: 10,
        rowsNumber: 0,
        sortBy: 'id',
        descending: true
      },
      columns: [
        { name: 'actions', label: 'Acciones', align: 'right' },
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
        // doctor
        {
          name: 'codigo',
          label: 'Código',
          field: 'codigo',
          align: 'left'
        },
        {
          name: 'doctor',
          label: 'Médico Solicitante',
          field: row => row.doctor_nombre || (row.doctor && row.doctor.name) || '',
          align: 'left'
        },
        {
          name: 'servicios',
          label: 'Prestaciones',
          field: row => row.servicios ? row.servicios.map(s => s.nombre).join(', ') : '',
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
          name: 'servicios_count',
          label: '# Prestaciones',
          field: row => (row.servicios ? row.servicios.length : 0),
          align: 'center'
        },
      //   reponsable user_preanalitica_id
        {
          name: 'responsable',
          label: 'Responsable Preanalítica',
          field: row => row.user_preanalitica ? row.user_preanalitica.name : 'No asignado',
          align: 'left'
        }
      ]
    }
  },
  computed: {
    consentimientos() {
      return consentimientos
    },
    pagesNumber () {
      const { rowsPerPage, rowsNumber } = this.pagination
      if (!rowsPerPage || rowsPerPage <= 0) return 1
      return Math.max(1, Math.ceil(rowsNumber / rowsPerPage))
    }
  },
  mounted () {
    this.reloadTable()
    this.areasTipoMuestrasGet()
  },
  methods: {
    areasTipoMuestrasGet(){
      this.$axios.get('areas-tipo-muestras')
        .then(res => {
          this.areas_tipo_muestrasAll = res.data || []
        })
        .catch(err => {
          console.error(err)
          this.$alert && this.$alert.error
            ? this.$alert.error('Error al cargar tipos de muestras')
            : null
        })
    },
    guardarPreAnalitica () {
      if (!this.consentimiento) return

      if (!this.consentimiento.codigo) {
        this.$alert.error('La solicitud debe tener un código antes de enviarla a Distribución.')
        return
      }

      // debe selecionar al menos un tipo de muestra
      const muestrasSeleccionadas = []
      this.areas_tipo_muestras.forEach(area => {
        area.area_tipo_muestras.forEach(tm => {
          if (tm.selected) {
            muestrasSeleccionadas.push(tm.id)
          }
        })
      })
      if (muestrasSeleccionadas.length === 0) {
        this.$alert.error('Debe seleccionar al menos un tipo de muestra para continuar.')
        return
      }

      this.savingPre = true
      this.$axios.post(`solicitudes/${this.consentimiento.id}/pre-analitica`, {
        area_tipo_muestras: this.areas_tipo_muestras
      })
        .then(res => {
          this.$alert.success('Muestras guardadas y solicitud enviada a Distribución')
          this.dialogConsentimiento = false
          this.reloadTable()
        })
        .catch(err => {
          console.error(err)
          const msg = err.response?.data?.message || err.message
          if (this.$alert && this.$alert.error) {
            this.$alert.error('Error al guardar muestras: ' + msg)
          }
        })
        .finally(() => {
          this.savingPre = false
        })
    },
    textCapitalize(text) {
      if (!text) return ''
      return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase()
    },
    tiempoAtencion(fechaSolicitud, fechaAtencion) {
      if (!fechaSolicitud || !fechaAtencion) return null;

      const inicio = moment(fechaSolicitud);
      const fin = moment(fechaAtencion);

      const duracion = moment.duration(fin.diff(inicio));

      const dias = duracion.days();
      const horas = duracion.hours();
      const minutos = duracion.minutes();
      const segundos = duracion.seconds();

      let resultado = '';
      if (dias > 0) resultado += `${dias} d `;
      if (horas > 0) resultado += `${horas} h `;
      if (minutos > 0) resultado += `${minutos} m`;
      if (dias === 0 && horas === 0 && minutos === 0) {
        resultado += `${segundos} s`;
      }

      return resultado.trim();
    },
    openDialogSolicitud (action, row, index) {
      this.consentimiento = row
      // cargar seleccionadas desde backend (relación area_tipo_muestras de la solicitud)
      // this.selectedMuestras = (row.area_tipo_muestras || []).map(m => m.id)
      // console.log(row)
      this.dialogConsentimiento = true
      const areas = []
      row.servicios.forEach(servicio => {
        // if (servicio.area && !areas.includes(servicio.area.id)) {
        if (servicio.area && !areas.some(a => a.id === servicio.area.id)) {
          areas.push(servicio.area)
        }
      })
      // console.log(areas)
      this.areas_tipo_muestras = [...areas].map(area => {
        return {
          id: area.id,
          name: area.name,
          area_tipo_muestras: this.areas_tipo_muestrasAll.filter(tm => tm.area_id === area.id).map(tm => {
            return {
              ...tm,
              selected: (row.area_tipo_muestras || []).some(rtm => rtm.id === tm.id)
            }
          })
        }
      })
      // area_tipo_muestras false
      // this.areas_tipo_muestras.forEach(area => {
      //   area.area_tipo_muestras.forEach(tm => {
      //     tm.selected = false
      //   })
      // })
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
      this.$q.dialog({
        title: row.codigo ? 'Generar nuevo código' : 'Generar código',
        message: row.codigo
          ? '¿Está seguro de generar un nuevo código para esta solicitud? El código anterior dejará de ser válido.'
          : '¿Está seguro de generar un código para esta solicitud?',
        cancel: true,
        persistent: true
      }).onOk(() => {
        this.generarCodigo(row)
      })
    },
    generarCodigo(row) {
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

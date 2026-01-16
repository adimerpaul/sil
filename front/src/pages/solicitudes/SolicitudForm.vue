<template>
  <q-card style="max-width: 980px; margin: 0 auto;" flat bordered>
      <q-card-section class="row items-center q-pa-sm">
        <div class="text-subtitle1">Nueva solicitud</div>
        <q-space />
        <q-btn icon="arrow_back" flat round dense @click="$router.push({ path: '/solicitudes' })" />
        <q-btn color="primary" label="Guardar" @click="$refs.form.submit()" :loading="loading" />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-sm">
        <q-form @submit="guardar" ref="form">
          <!-- Paciente -->
          <div class="row items-center q-mb-xs">
            <q-icon name="person" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Datos del paciente</div>
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-6 col-sm-3">
              <q-input v-model="solicitud.paciente_ci" label="CI" dense outlined
                       @update:model-value="onChangeCi" debounce="600" >
                <template v-slot:append>
                  <q-spinner
                    color="primary"
                    v-if="loading"
                  />
                </template>
              </q-input>
<!--              <pre>{{solicitud}}</pre>-->
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="solicitud.paciente_nombre" label="Nombre" dense outlined />
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model="solicitud.paciente_telefono" label="Celular" dense outlined />
            </div>

            <div class="col-12">
              <q-input v-model="solicitud.paciente_direccion" label="Dirección" dense outlined />
            </div>

            <div class="col-6 col-sm-4">
              <q-input v-model="solicitud.paciente_fecha_nac" type="date" label="F. nacimiento"
                       dense outlined @update:model-value="onCalculateEdad" />
            </div>

            <div class="col-6 col-sm-4">
              <div class="text-caption text-black">Género</div>
              <q-radio v-model="solicitud.paciente_genero" val="F" label="F" dense />
              <q-radio v-model="solicitud.paciente_genero" val="M" label="M" dense />
              <q-radio v-model="solicitud.paciente_genero" val="OTRO" label="Otro" dense />
            </div>

            <div class="col-12 col-sm-4">
              <q-input v-model.number="solicitud.paciente_edad" type="number" label="Edad" dense outlined />
            </div>
          </div>

          <q-separator class="q-my-sm" />

          <!-- Doctor -->
          <div class="row items-center q-mb-xs">
            <q-icon name="person" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Datos del médico solicitante</div>
          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-12">
              <q-select
                v-model="solicitud.doctor_id"
                :options="doctoresOptions"
                use-input
                option-value="id"
                :option-label="doctor =>
                  doctor.nombre + ' (' + doctor.especialidad + ')' +
                  (doctor.telefono ? ' - ' + doctor.telefono : '') + ' ' +
                  (doctor.establecimiento?.nombre || '')
                "
                @filter="filterDoctores"
                emit-value map-options
                dense outlined clearable
                label="Doctor (opcional)"
                @update:model-value="onSelectDoctor"
              >
<!--                <template agregra boton para agregar nuevo doctor>-->
                <template #after>
                  <q-btn flat dense icon="person_add" color="positive" @click="dialogDoctorNew=true" />
                </template>
              </q-select>
            </div>
          </div>

          <q-separator class="q-my-sm" />

          <!-- Datos solicitud -->
          <div class="row items-center q-mb-xs">
            <q-icon name="assignment" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Datos de la solicitud</div>
          </div>

          <div class="row q-col-gutter-xs items-center">
            <div class="col-6 col-sm-3">
              <q-toggle v-model="solicitud.tipo_atencion" true-value="SI" false-value="NO" dense
                        @update:model-value="onTipoAtencionChange">
                {{ solicitud.tipo_atencion === 'SI' ? 'SUS' : 'EXT' }}
              </q-toggle>
            </div>

            <template v-if="solicitud.tipo_atencion === 'NO'">
              <div class="col-6 col-md-3" >
                <q-input  v-model="solicitud.tipo_otro"
                          label="Especificar tipo de atención" dense outlined />
              </div>
              <div class="col-6 col-md-3" >
                <q-input v-model="solicitud.numero_factura"
                         label="Número de factura" dense outlined />
              </div>
            </template>
            <div class="col-6 col-md-6" v-else>
              <q-select
                v-model="solicitud.establecimiento_salud"
                :options="establecimientos"
                option-label="nombre"
                option-value="nombre"
                emit-value map-options
                label="Establecimiento de salud (SUS)"
                dense outlined clearable
                @update:model-value="onEstablecimientoChange"
              >
                <template #option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <q-item-label>{{ scope.opt.nombre }}</q-item-label>
                      <q-item-label caption>{{ scope.opt.tipo }} • {{ scope.opt.nivel }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>

            <div class="col-12 col-md-6 q-mt-xs">
              <q-select
                v-model="solicitud.diagnostico_select"
                :options="diagnosticos"
                option-label="cie10"
                option-value="cie10"
                dense
                outlined
                clearable
                label="Buscar diagnóstico clínico"
                use-input
                emit-value
                map-options
                input-debounce="300"
                @filter="onFilterDiagnosticos"
              >
                <template #option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <q-item-label>{{ scope.opt.cie10 }}</q-item-label>
                      <q-item-label caption>
                        Especialidad: {{ scope.opt.especialidad }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>

            </div>
            <div class="col-12 col-md-6 q-mt-xs">
              <q-input v-model="solicitud.diagnostico_clinico" type="textarea"
                       label="Diagnóstico clínico otros" dense outlined autogrow />
            </div>

            <div class="col-6">
              <q-input v-model="solicitud.fecha_solicitud" type="date" label="Fecha de solicitud medico"
                       dense outlined />
            </div>

            <div class="col-6">
              <q-select v-model="solicitud.sala"
                        :options="salas"
                        label="Unidad solicitante" dense outlined clearable />
            </div>

            <div class="col-4">
              <q-input v-model="solicitud.cama" label="Sala / Cama" dense outlined />
            </div>
            <!--            diagnostico_select-->
            <!--            <div class="col-8">-->
            <!--            </div>-->
          </div>

          <q-separator class="q-my-sm" />

          <!-- Servicios -->
          <div class="row items-center q-mb-xs">
            <q-icon name="biotech" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Servicios solicitados</div>
            <q-space />
            <q-badge color="primary" outline>{{ totalServiciosSeleccionados }} seleccionados</q-badge>
          </div>

          <q-card flat bordered class="q-mb-xs">
            <q-card-section class="row q-col-gutter-xs">
              <div class="col-12 col-sm-6">
                <q-input v-model="serviciosFilter" dense outlined label="Buscar servicio (nombre / código / subárea)" clearable>
                  <template #append><q-icon name="search" /></template>
                </q-input>
              </div>
              <div class="col-12 col-sm-6">
                <q-select v-model="serviciosAreaId" :options="areas" option-label="name" option-value="id"
                          dense outlined clearable label="Filtrar por área" emit-value map-options>
                  <template #prepend><q-icon name="science" /></template>
                </q-select>
                <!--                <pre>{{serviciosAreaId}}</pre>-->
              </div>
            </q-card-section>

            <q-card-section class=" text-grey-7">
              <div v-if="solicitud.tipo_atencion === 'SI' && currentEstablecimiento">
                Mostrando solo servicios del establecimiento: <b>{{ currentEstablecimiento.nombre }}</b>
              </div>
              <div v-else-if="solicitud.tipo_atencion === 'SI'">
                Seleccione un establecimiento para filtrar los servicios.
              </div>
              <div v-else>
                Mostrando todos los servicios disponibles (atención particular / especificar).
              </div>
              <div v-if="selectedServicios.length === 0" class="q-mt-sm">
                No ha seleccionado ningún servicio aún.
              </div>
              <div v-else>
                Servicios seleccionados:
                <ul>
                  <li v-for="(s, index) in selectedServicios" :key="index">
                    {{ s.area }} - {{ s.servicio }} (Bs. {{ s.precio }})
                  </li>
                </ul>
<!--                mostrar cantidada de sericio selecionados y total-->
                <div class="text-bold">
                  Total de servicios seleccionados: <b>{{ selectedServicios.length }}</b>
                  Monto total: <b>Bs. {{ selectedServicios.reduce((sum, s) => sum + parseFloat(s.precio), 0) }}</b>
                </div>
              </div>
            </q-card-section>
          </q-card>

          <div class="row q-col-gutter-xs">
            <div class="col-12">
              <q-expansion-item
                v-for="area in areas"
                :key="area.id || area.name"
                :label="area.name"
                icon="science"
                expand-separator
                dense
                default-opened
                v-show="filteredServicios(area).length > 0"
              >
                <q-card flat>
                  <q-card-section class="q-pa-xs">
                    <div class="row q-col-gutter-xs">
                      <div v-for="servicio in filteredServicios(area)" :key="servicio.id || servicio.codigo"
                           class="col-12 col-sm-6">
                        <q-checkbox v-model="servicio.seleccionado" :true-value="1" :false-value="0" dense>
                          <div>
                            {{ textCapitalize(servicio.nombre) }}
                            <span class="text-primary">(Bs. {{ servicio.precio }})</span>
                          </div>
                          <div>
                            <small class="text-grey">
                              {{ servicio.codigo ? 'Código: ' + servicio.codigo + ' • ' : '' }}
                              {{ servicio.subarea ? 'Subárea: ' + textCapitalize(servicio.subarea) : '' }}
                            </small>
                          </div>
                        </q-checkbox>
                      </div>
                    </div>
                  </q-card-section>
                </q-card>
              </q-expansion-item>

              <div v-if="areas.length === 0" class="text-center text-grey q-mt-md">
                No hay servicios configurados.
              </div>
            </div>
          </div>

          <div class="text-right q-mt-sm">
            <q-btn flat label="Cancelar" :loading="loading" @click="$router.push({ path: '/solicitudes' })" />
            <q-btn color="primary" label="Guardar" type="submit" class="q-ml-xs" :loading="loading" />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  <q-dialog v-model="dialogDoctorNew">
    <q-card style="min-width: 400px; max-width: 600px;">
      <q-card-section class="row items-center">
        <div class="text-h6">
          Nuevo doctor
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-form @submit.prevent="guardarDoctor">
          <div class="row q-col-gutter-sm">
            <div class="col-12">
              <q-input
                v-model="doctor.nombre"
                label="Nombre"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="doctor.especialidad"
                label="Especialidad"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="doctor.ci"
                label="CI"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="doctor.telefono"
                label="Teléfono"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="doctor.email"
                label="Email"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="doctor.registro"
                label="Registro profesional"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-select
                v-model="doctor.establecimiento_id"
                :options="establecimientos"
                option-label="nombre"
                option-value="id"
                emit-value
                map-options
                label="Establecimiento de salud"
                dense outlined
              />
              <!--                <pre>{{establecimientos}}</pre>-->
            </div>
<!--            <div class="col-12 col-sm-6">-->
<!--              <q-select-->
<!--                v-model="doctor.estado"-->
<!--                :options="['ACTIVO', 'INACTIVO']"-->
<!--                label="Estado"-->
<!--                dense outlined-->
<!--                clearable-->
<!--              />-->
<!--            </div>-->
          </div>

          <div class="text-right q-mt-md">
            <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
            <q-btn
              color="primary"
              label="Guardar"
              type="submit"
              class="q-ml-sm"
              :loading="loading"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
import moment from 'moment'

export default {
  name: 'SolicitudForm',
  props: {
    solicitudProp: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      dialogDoctorNew: false,
      doctor: {
        estado: 'ACTIVO'
      },
      loading: false,
      solicitud: {},
      salas:[
        'CIRUGIA MUJERES',
        'CIRUGIA VARONES',
        'CIRUGIA GENERAL',
        'CONSULTA EXTERNA ANESTESIOLOGIA',
        'CONSULTA EXTERNA CARDIOLOGIA',
        'CONSULTA EXTERNA CIRUGIA GENERAL',
        'CONSULTA EXTERNA CIRUGIA MAXILO FACIAL',
        'CONSULTA EXTERNA DERMATOLOGIA',
        'CONSULTA EXTERNA ENDOCRINOLOGIA',
        'CONSULTA EXTERNA GASTROENTEROLOGIA',
        'CONSULTA EXTERNA GERIATRIA',
        'CONSULTA EXTERNA GINECOLOGIA',
        'CONSULTA EXTERNA HEMATOLOGIA',
        'CONSULTA EXTERNA MEDICINA INTERNA',
        'CONSULTA EXTERNA NEFROLOGIA',
        'CONSULTA EXTERNA NEUMOLOGIA',
        'CONSULTA EXTERNA NEUROCIRUGIA',
        'CONSULTA EXTERNA NEUROLOGIA',
        'CONSULTA EXTERNA NUTRICION',
        'CONSULTA EXTERNA OBSTETRICIA',
        'CONSULTA EXTERNA OFTALMOLOGIA',
        'CONSULTA EXTERNA ONCOLOGIA',
        'CONSULTA EXTERNA OTORRINOLARINGOLOGIA',
        'CONSULTA EXTERNA PEDIATRIA',
        'CONSULTA EXTERNA PSICOLOGIA',
        'CONSULTA EXTERNA PSIQUIATRIA',
        'CONSULTA EXTERNA REUMATOLOGIA',
        'CONSULTA EXTERNA TRAUMATOLOGIA',
        'CONSULTA EXTERNA UROLOGIA',
        'EMERGENCIA EMERGENCIA',
        'HOSPITALIZACION CIRUJIA GENERAL',
        'HOSPITALIZACION GINECOLOGIA',
        'HOSPITALIZACION MEDICINA INTERNA',
        'HOSPITALIZACION NEONATOLOGIA',
        'HOSPITALIZACION OBSTETRICIA',
        'HOSPITALIZACION ONCOLOGIA',
        'HOSPITALIZACION PEDIATRIA',
        'HOSPITALIZACION QUEMADOS',
        'HOSPITALIZACION UNIDAD DE CUIDADOS INTENSIVOS (UCI)',
        'HOSPITALIZACION UNIDAD DE TERAPIA NTENSIVA (UTI)',
        'SOLICITUD EXTERNA'
      ],

      doctoresOptions: [],
      doctoresOptionsAll: [],
      areas: [],
      establecimientos: [],
      searchCi: '',
      serviciosFilter: '',
      serviciosAreaId: null,
      diagnosticos: [],
      diagnosticosAll: [],
    }
  },
  computed: {
    selectedServicios() {
      const selected = []
      this.areas.forEach(area => {
        (area.servicios || []).forEach(servicio => {
          if (servicio.seleccionado) {
            selected.push({
              area: area.name,
              servicio: servicio.nombre,
              precio: servicio.precio
            })
          }
        })
      })
      return selected
    },
    currentEstablecimiento () {
      if (!this.solicitud.establecimiento_salud) return null
      return this.establecimientos.find(e => e.nombre === this.solicitud.establecimiento_salud) || null
    },
    totalServiciosSeleccionados () {
      let total = 0
      this.areas.forEach(a => (a.servicios || []).forEach(s => { if (s.seleccionado) total++ }))
      return total
    }
  },
  mounted () {

    // this.initSolicitud()
    this.loadDoctores()
    this.diagnosticosGet()
    this.$axios.get('establecimientos').then(res => { this.establecimientos = res.data })
    this.$axios.get('areasCreateSolicitud').then(res => {
      this.areas = res.data
      console.log('SolicitudForm mounted with solicitudProp:', this.solicitudProp)
      if (this.solicitudProp) {
        this.solicitud = { ...this.solicitudProp }
        // selecioanr las area que tiene el servicios
        this.areas.forEach(area => {
          (area.servicios || []).forEach(s => {
            const found = this.solicitud.servicios.find(ss => ss.id === s.id)
            if (found) {
              s.seleccionado = 1
            }else {
              s.seleccionado = 0
            }
          })
        })
      } else {
        this.initSolicitud()
        this.resetServiciosSelection()
      }
    })
  },
  methods: {
    guardarDoctor() {
      this.loading = true
      this.$axios.post('doctores', this.doctor)
        .then(res => {
          this.$alert?.success ? this.$alert.success('Doctor guardado correctamente') : console.log('Doctor guardado correctamente')
          this.dialogDoctorNew = false
          this.loadDoctores()
          this.solicitud.doctor_id = res.data.id
          this.onSelectDoctor(res.data.id)
          // reset doctor form
          this.doctor = {
            estado: 'ACTIVO'
          }
        })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error ? this.$alert.error('Error al guardar doctor: ' + msg) : console.error(msg)
        })
        .finally(() => { this.loading = false })
    },
    filterDoctores(val, update) {
      update(() => {
        const text = (val || '').toLowerCase().trim()

        if (!text) {
          this.doctoresOptions = this.doctoresOptionsAll
          return
        }

        this.doctoresOptions = this.doctoresOptionsAll
          .filter(d => {
            const nombre = String(d.nombre || '').toLowerCase()
            const esp = String(d.especialidad || '').toLowerCase()
            const ci = String(d.ci || '').toLowerCase()
            const tel = String(d.telefono || '').toLowerCase()
            return (
              nombre.includes(text) ||
              esp.includes(text) ||
              ci.includes(text) ||
              tel.includes(text)
            )
          })
          .slice(0, 50) // 🔥 limita resultados (performance)
      })
    },
    onFilterDiagnosticos (val, update) {
      update(() => {
        const text = (val || '').toLowerCase().trim()

        if (!text) {
          this.diagnosticos = this.diagnosticosAll
          return
        }

        this.diagnosticos = this.diagnosticosAll
          .filter(d => {
            const cie10 = String(d.cie10 || '').toLowerCase()
            const esp = String(d.especialidad || '').toLowerCase()
            const serv = String(d.servicio || '').toLowerCase()
            return (
              cie10.includes(text) ||
              esp.includes(text) ||
              serv.includes(text)
            )
          })
          .slice(0, 50) // 🔥 limita resultados (performance)
      })
    },
    diagnosticosGet () {
      this.$axios.get('diagnosticos').then(res => {
        this.diagnosticos = res.data
        this.diagnosticosAll = res.data
      })
    },
    initSolicitud () {
      this.solicitud = {
        paciente_id: null,
        doctor_id: null,
        codigo_solicitud: '',
        tipo_atencion: 'SI',
        tipo_otro: '',
        fecha_solicitud: moment().format('YYYY-MM-DD'),
        hora_solicitud: moment().format('HH:mm'),
        establecimiento_salud: 'Hospital General',
        zona_establecimiento: '',
        diagnostico_clinico: '',
        estado: 'CREADO',

        paciente_nombre: '',
        paciente_ci: '',
        paciente_telefono: '',
        paciente_direccion: '',
        paciente_fecha_nac: '',
        paciente_genero: '',
        paciente_edad: null,

        doctor_nombre: '',
        doctor_especialidad: '',
        doctor_ci: '',
        doctor_telefono: '',
        doctor_email: '',
        doctor_registro: ''
      }
      this.searchCi = ''
      this.serviciosFilter = ''
      this.serviciosAreaId = null
    },

    onCalculateEdad () {
      if (!this.solicitud.paciente_fecha_nac) return
      const birthDate = moment(this.solicitud.paciente_fecha_nac, 'YYYY-MM-DD')
      if (!birthDate.isValid()) return
      this.solicitud.paciente_edad = moment().diff(birthDate, 'years')
    },

    textCapitalize (str) {
      if (!str) return ''
      return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
    },

    resetServiciosSelection () {
      this.areas.forEach(area => (area.servicios || []).forEach(s => { s.seleccionado = 0 }))
    },

    loadDoctores () {
      this.$axios.get('doctores').then(res => {
        this.doctoresOptions = res.data
        this.doctoresOptionsAll = res.data
      })
    },

    onChangeCi (val) {
      this.searchCi = val
      this.buscarPacientePorCi()
    },

    buscarPacientePorCi () {
      if (!this.searchCi) return
      this.loading = true
      this.solicitud.paciente_id = null
      this.solicitud.paciente_nombre = ''
      this.solicitud.paciente_telefono = ''
      this.solicitud.paciente_direccion = ''
      this.solicitud.paciente_fecha_nac = ''
      this.solicitud.paciente_genero = ''
      this.solicitud.paciente_edad = null
      this.$axios
        .get(`pacientes/buscar-ci/${this.searchCi}`)
        .then(res => { this.onSelectPaciente(res.data) })
        .catch(() => {
          // paciente no encontrado vaciar los demas campos
        })
        .finally(() => { this.loading = false })
    },

    onSelectPaciente (p) {
      if (!p) return
      this.solicitud.paciente_id = p.id
      this.solicitud.paciente_nombre = p.nombre_completo
      this.solicitud.paciente_ci = p.ci
      this.solicitud.paciente_telefono = p.telefono
      this.solicitud.paciente_direccion = p.direccion
      this.solicitud.paciente_fecha_nac = p.fecha_nac
      this.solicitud.paciente_genero = p.genero
      this.solicitud.paciente_edad = p.edad
    },

    onSelectDoctor (id) {
      const d = this.doctoresOptions.find(x => x.id === id)
      if (!d) return
      this.solicitud.doctor_id = d.id
      this.solicitud.doctor_nombre = d.nombre
      this.solicitud.doctor_especialidad = d.especialidad
      this.solicitud.doctor_ci = d.ci
      this.solicitud.doctor_telefono = d.telefono
      this.solicitud.doctor_email = d.email
      this.solicitud.doctor_registro = d.registro
      if (d.establecimiento?.nombre) this.solicitud.establecimiento_salud = d.establecimiento.nombre
    },

    onTipoAtencionChange () {
      // this.resetServiciosSelection()
      if (this.solicitud.tipo_atencion === 'NO') this.solicitud.establecimiento_salud = ''
      else this.solicitud.tipo_otro = ''
    },

    onEstablecimientoChange () {
      this.resetServiciosSelection()
    },

    filteredServicios (area) {
      let servicios = area.servicios || []

      if (this.serviciosAreaId && area.id !== this.serviciosAreaId) return []

      if (this.solicitud.tipo_atencion === 'SI') {
        const est = this.currentEstablecimiento
        if (est && Array.isArray(est.servicio_ids) && est.servicio_ids.length) {
          const allowed = new Set(est.servicio_ids)
          servicios = servicios.filter(s => allowed.has(s.id))
        }
      }

      const text = (this.serviciosFilter || '').toLowerCase().trim()
      if (!text) return servicios

      return servicios.filter(s => {
        const nombre = String(s.nombre ?? '').toLowerCase()
        const sub = String(s.subarea ?? '').toLowerCase()
        const codigo = String(s.codigo ?? '').toLowerCase()
        return nombre.includes(text) || sub.includes(text) || codigo.includes(text)
      })
    },

    guardar () {
      // armar servicios
      this.solicitud.servicios = []
      this.areas.forEach(area => {
        (area.servicios || []).forEach(servicio => {
          if (servicio.seleccionado) {
            this.solicitud.servicios.push({
              id: servicio.id,
              nombre: servicio.nombre,
              precio: servicio.precio,
              area_id: area.id
            })
          }
        })
      })

      if (this.solicitud.servicios.length === 0) {
        this.$alert?.error ? this.$alert.error('Seleccione al menos un servicio') : alert('Seleccione al menos un servicio')
        return
      }
      if (!this.solicitud.paciente_ci) {
        this.$alert?.error ? this.$alert.error('Coloque la CI del paciente') : alert('Coloque la CI del paciente')
        return
      }


      if (this.solicitud.id) {
        if (this.solicitud.codigo == null){
          this.actulizarSolicitud()
        }else{
          this.$q.dialog({
            title: 'Confirmar',
            message: 'La solicitud ya tiene un código asignado, ¿desea actualizar la información?',
            cancel: true,
            persistent: true
          }).onOk(() => {
            this.actulizarSolicitud()
          })
        }
      }else{
        this.loading = true
        this.$axios
          .post('solicitudes', this.solicitud)
          .then(() => {
            this.$alert?.success ? this.$alert.success('Guardado correctamente') : console.log('Guardado correctamente')
            this.$router.push({ path: '/solicitudes' })
          })
          .catch(e => {
            const msg = e.response?.data?.message || e.message
            this.$alert?.error ? this.$alert.error('Error al guardar: ' + msg) : console.error(msg)
          })
          .finally(() => { this.loading = false })
      }
    },
    actulizarSolicitud () {
      this.loading = true
      this.$axios
        .put(`solicitudes/${this.solicitud.id}`, this.solicitud)
        .then(() => {
          this.$alert.success('Actualizado correctamente')
          this.$router.push({ path: '/solicitudes' })
        })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert.error('Error al actualizar: ' + msg)
        })
        .finally(() => { this.loading = false })
    }
  }
}
</script>

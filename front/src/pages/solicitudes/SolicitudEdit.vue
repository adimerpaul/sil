<template>
  <q-page class="q-pa-sm">
    <q-card style="max-width: 980px; margin: 0 auto;" flat bordered>
      <q-card-section class="row items-center q-pa-sm">
        <div class="text-subtitle1">Editar solicitud #{{ id }}</div>
        <q-space />
        <q-btn icon="arrow_back" flat round dense @click="$router.push({ path: '/solicitudes' })" />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-sm">
        <q-form @submit="guardar">
          <!-- (MISMO FORMULARIO QUE NEW) -->
          <!-- Paciente -->
          <div class="row items-center q-mb-xs">
            <q-icon name="person" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Datos del paciente</div>
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-6 col-sm-3">
              <q-input v-model="solicitud.paciente_ci" label="CI" dense outlined
                       @update:model-value="onChangeCi" debounce="600" />
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
                option-value="id"
                :option-label="doctor =>
                  doctor.nombre + ' (' + doctor.especialidad + ')' +
                  (doctor.telefono ? ' - ' + doctor.telefono : '') + ' ' +
                  (doctor.establecimiento?.nombre || '')
                "
                emit-value map-options
                dense outlined clearable
                label="Doctor (opcional)"
                @update:model-value="onSelectDoctor"
              />
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

            <div class="col-6 col-sm-9">
              <q-input v-if="solicitud.tipo_atencion === 'NO'" v-model="solicitud.tipo_otro"
                       label="Especificar tipo de atención" dense outlined />
              <q-select
                v-else
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

            <div class="col-12 q-mt-xs">
              <q-input v-model="solicitud.diagnostico_clinico" type="textarea"
                       label="Diagnóstico clínico principal" dense outlined autogrow />
            </div>

            <div class="col-6">
              <q-input v-model="solicitud.fecha_solicitud" type="date" label="Fecha de solicitud medico"
                       dense outlined />
            </div>

            <div class="col-6">
              <q-select v-model="solicitud.sala"
                        :options="['CM','CV','CG','CE','PED','UTI','NEO','OTRO']"
                        label="Unidad solicitante" dense outlined clearable />
            </div>

            <div class="col-6">
              <q-input v-model="solicitud.cama" label="Sala / Cama" dense outlined />
            </div>
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
                <q-input v-model="serviciosFilter" dense outlined label="Buscar servicio (nombre / código / subárea)">
                  <template #append><q-icon name="search" /></template>
                </q-input>
              </div>
              <div class="col-12 col-sm-6">
                <q-select v-model="serviciosAreaId" :options="areas" option-label="name" option-value="id"
                          dense outlined clearable label="Filtrar por área">
                  <template #prepend><q-icon name="science" /></template>
                </q-select>
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
                expand-separator dense
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
            </div>
          </div>

          <div class="text-right q-mt-sm">
            <q-btn flat label="Cancelar" :loading="loading" @click="$router.push({ path: '/solicitudes' })" />
            <q-btn color="primary" label="Actualizar" type="submit" class="q-ml-xs" :loading="loading" />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'SolicitudEdit',
  data () {
    return {
      id: null,
      loading: false,
      solicitud: {},
      doctoresOptions: [],
      areas: [],
      establecimientos: [],
      searchCi: '',
      serviciosFilter: '',
      serviciosAreaId: null
    }
  },
  computed: {
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
    this.id = this.$route.params.id

    this.loadDoctores()
    this.$axios.get('establecimientos').then(res => { this.establecimientos = res.data })

    // primero cargo áreas, luego cargo solicitud, luego marco servicios
    this.$axios.get('areas').then(async res => {
      this.areas = res.data
      this.resetServiciosSelection()
      await this.loadSolicitud()
    })
  },
  methods: {
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

    aplicarServiciosSeleccionados (serviciosSeleccionados) {
      const ids = new Set((serviciosSeleccionados || []).map(s => s.id))
      this.resetServiciosSelection()
      this.areas.forEach(area => {
        (area.servicios || []).forEach(s => { if (ids.has(s.id)) s.seleccionado = 1 })
      })
    },

    loadDoctores () {
      this.$axios.get('doctores').then(res => { this.doctoresOptions = res.data })
    },

    async loadSolicitud () {
      this.loading = true
      try {
        const res = await this.$axios.get(`solicitudes/${this.id}`)
        this.solicitud = { ...res.data }
        this.aplicarServiciosSeleccionados(res.data.servicios || [])
      } finally {
        this.loading = false
      }
    },

    onChangeCi (val) {
      this.searchCi = val
      this.buscarPacientePorCi()
    },

    buscarPacientePorCi () {
      if (!this.searchCi) return
      this.loading = true
      this.$axios
        .get(`pacientes/buscar-ci/${this.searchCi}`)
        .then(res => { this.onSelectPaciente(res.data) })
        .catch(() => {})
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
      this.resetServiciosSelection()
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

      this.loading = true
      this.$axios
        .put(`solicitudes/${this.id}`, this.solicitud)
        .then(() => {
          this.$alert?.success ? this.$alert.success('Actualizado correctamente') : console.log('Actualizado correctamente')
          this.$router.push({ path: '/solicitudes' })
        })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error ? this.$alert.error('Error al actualizar: ' + msg) : console.error(msg)
        })
        .finally(() => { this.loading = false })
    }
  }
}
</script>

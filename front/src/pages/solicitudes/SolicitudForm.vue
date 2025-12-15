<template>
  <q-page class="q-pa-sm">
    <q-card style="max-width: 980px; margin: 0 auto;" flat bordered>
      <!-- HEADER -->
      <q-card-section class="row items-center q-pa-sm">
        <div class="text-subtitle1">
          {{ mode === 'edit' ? 'Editar solicitud' : 'Nueva solicitud' }}
        </div>

        <q-space />

        <q-btn
          icon="arrow_back"
          flat
          round
          dense
          @click="$emit('cancel')"
        />

        <q-btn
          class="q-ml-sm"
          color="primary"
          :label="mode === 'edit' ? 'Actualizar' : 'Guardar'"
          :loading="loading"
          @click="$refs.form.submit()"
        />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-sm">
        <q-form @submit.prevent="submit" ref="form">

          <!-- =========================
               PACIENTE
          ========================== -->
          <div class="row items-center q-mb-xs">
            <q-icon name="person" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Datos del paciente</div>
          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-6 col-sm-3">
              <q-input
                v-model="local.paciente_ci"
                label="CI"
                dense
                outlined
                debounce="600"
                @update:model-value="onChangeCi"
              />
            </div>

            <div class="col-12 col-sm-6">
              <q-input v-model="local.paciente_nombre" label="Nombre" dense outlined />
            </div>

            <div class="col-6 col-sm-3">
              <q-input v-model="local.paciente_telefono" label="Celular" dense outlined />
            </div>

            <div class="col-12">
              <q-input v-model="local.paciente_direccion" label="Dirección" dense outlined />
            </div>

            <div class="col-6 col-sm-4">
              <q-input
                v-model="local.paciente_fecha_nac"
                type="date"
                label="F. nacimiento"
                dense
                outlined
                @update:model-value="onCalculateEdad"
              />
            </div>

            <div class="col-6 col-sm-4">
              <div class="text-caption text-black">Género</div>
              <q-radio v-model="local.paciente_genero" val="F" label="F" dense />
              <q-radio v-model="local.paciente_genero" val="M" label="M" dense />
              <q-radio v-model="local.paciente_genero" val="OTRO" label="Otro" dense />
            </div>

            <div class="col-12 col-sm-4">
              <q-input v-model.number="local.paciente_edad" type="number" label="Edad" dense outlined />
            </div>
          </div>

          <q-separator class="q-my-sm" />

          <!-- =========================
               DOCTOR
          ========================== -->
          <div class="row items-center q-mb-xs">
            <q-icon name="person" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Datos del médico solicitante</div>
          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-12">
              <q-select
                v-model="local.doctor_id"
                :options="doctoresOptions"
                option-value="id"
                :option-label="doctor =>
                  doctor.nombre + ' (' + doctor.especialidad + ')' +
                  (doctor.telefono ? ' - ' + doctor.telefono : '') + ' ' +
                  (doctor.establecimiento?.nombre || '')
                "
                emit-value
                map-options
                dense
                outlined
                clearable
                label="Doctor (opcional)"
                @update:model-value="onSelectDoctor"
              />
            </div>
          </div>

          <q-separator class="q-my-sm" />

          <!-- =========================
               DATOS SOLICITUD
          ========================== -->
          <div class="row items-center q-mb-xs">
            <q-icon name="assignment" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Datos de la solicitud</div>
          </div>

          <div class="row q-col-gutter-xs items-center">
            <div class="col-6 col-sm-3">
              <q-toggle
                v-model="local.tipo_atencion"
                true-value="SI"
                false-value="NO"
                dense
                @update:model-value="onTipoAtencionChange"
              >
                {{ local.tipo_atencion === 'SI' ? 'SUS' : 'EXT' }}
              </q-toggle>
            </div>

            <div class="col-6 col-sm-9">
              <q-input
                v-if="local.tipo_atencion === 'NO'"
                v-model="local.tipo_otro"
                label="Especificar tipo de atención"
                dense
                outlined
              />

              <q-select
                v-else
                v-model="local.establecimiento_salud"
                :options="establecimientos"
                option-label="nombre"
                option-value="nombre"
                emit-value
                map-options
                label="Establecimiento de salud (SUS)"
                dense
                outlined
                clearable
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
              <q-input
                v-model="local.diagnostico_clinico"
                type="textarea"
                label="Diagnóstico clínico principal"
                dense
                outlined
                autogrow
              />
            </div>

            <div class="col-6">
              <q-input
                v-model="local.fecha_solicitud"
                type="date"
                label="Fecha de solicitud medico"
                dense
                outlined
              />
            </div>

            <div class="col-6">
              <q-select
                v-model="local.sala"
                :options="['CM','CV','CG','CE','PED','UTI','NEO','OTRO']"
                label="Unidad solicitante"
                dense
                outlined
                clearable
              />
            </div>

            <div class="col-6">
              <q-input v-model="local.cama" label="Sala / Cama" dense outlined />
            </div>
          </div>

          <q-separator class="q-my-sm" />

          <!-- =========================
               SERVICIOS
          ========================== -->
          <div class="row items-center q-mb-xs">
            <q-icon name="biotech" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Servicios solicitados</div>
            <q-space />
            <q-badge color="primary" outline>{{ totalServiciosSeleccionados }} seleccionados</q-badge>
          </div>

          <q-card flat bordered class="q-mb-xs">
            <q-card-section class="row q-col-gutter-xs">
              <div class="col-12 col-sm-6">
                <q-input
                  v-model="serviciosFilter"
                  dense
                  outlined
                  label="Buscar servicio (nombre / código / subárea)"
                  clearable
                >
                  <template #append><q-icon name="search" /></template>
                </q-input>
              </div>

              <div class="col-12 col-sm-6">
                <q-select
                  v-model="serviciosAreaId"
                  :options="areas"
                  option-label="name"
                  option-value="id"
                  dense
                  outlined
                  clearable
                  label="Filtrar por área"
                  emit-value
                  map-options
                >
                  <template #prepend><q-icon name="science" /></template>
                </q-select>
              </div>
            </q-card-section>

            <q-card-section class="text-caption text-grey-7">
              <div v-if="local.tipo_atencion === 'SI' && currentEstablecimiento">
                Mostrando solo servicios del establecimiento: <b>{{ currentEstablecimiento.nombre }}</b>
              </div>
              <div v-else-if="local.tipo_atencion === 'SI'">
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
                      <div
                        v-for="servicio in filteredServicios(area)"
                        :key="servicio.id || servicio.codigo"
                        class="col-12 col-sm-6"
                      >
                        <q-checkbox
                          v-model="servicio.seleccionado"
                          :true-value="1"
                          :false-value="0"
                          dense
                        >
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
            <q-btn flat label="Cancelar" :loading="loading" @click="$emit('cancel')" />
            <q-btn
              color="primary"
              :label="mode === 'edit' ? 'Actualizar' : 'Guardar'"
              type="submit"
              class="q-ml-xs"
              :loading="loading"
            />
          </div>

        </q-form>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'SolicitudForm',
  props: {
    value: { type: Object, required: true }, // v-model
    loading: { type: Boolean, default: false },
    mode: { type: String, default: 'create' }, // create | edit
    areas: { type: Array, default: () => [] },
    establecimientos: { type: Array, default: () => [] },
    doctoresOptions: { type: Array, default: () => [] }
  },
  data () {
    return {
      local: JSON.parse(JSON.stringify(this.value || {})),
      searchCi: '',
      serviciosFilter: '',
      serviciosAreaId: null
    }
  },
  computed: {
    currentEstablecimiento () {
      if (!this.local.establecimiento_salud) return null
      return this.establecimientos.find(e => e.nombre === this.local.establecimiento_salud) || null
    },
    totalServiciosSeleccionados () {
      let total = 0
      ;(this.areas || []).forEach(a => (a.servicios || []).forEach(s => { if (s.seleccionado) total++ }))
      return total
    },
    selectedServicios () {
      const selected = []
      ;(this.areas || []).forEach(area => {
        ;(area.servicios || []).forEach(s => {
          if (s.seleccionado) {
            selected.push({ area: area.name, servicio: s.nombre, precio: s.precio })
          }
        })
      })
      return selected
    }
  },
  watch: {
    value: {
      deep: true,
      handler (v) {
        // si cambia desde afuera (Edit cuando carga), refresca
        this.local = JSON.parse(JSON.stringify(v || {}))
      }
    },
    local: {
      deep: true,
      handler (v) {
        // emite a padre
        this.$emit('input', v)
      }
    }
  },
  methods: {
    submit () {
      // armar servicios al enviar (en local)
      this.local.servicios = []
      ;(this.areas || []).forEach(area => {
        ;(area.servicios || []).forEach(servicio => {
          if (servicio.seleccionado) {
            this.local.servicios.push({
              id: servicio.id,
              nombre: servicio.nombre,
              precio: servicio.precio,
              area_id: area.id
            })
          }
        })
      })

      if (!this.local.paciente_ci) {
        this.$alert?.error ? this.$alert.error('Coloque la CI del paciente') : alert('Coloque la CI del paciente')
        return
      }

      if (!this.local.servicios || this.local.servicios.length === 0) {
        this.$alert?.error ? this.$alert.error('Seleccione al menos un servicio') : alert('Seleccione al menos un servicio')
        return
      }

      this.$emit('submit')
    },

    onCalculateEdad () {
      if (!this.local.paciente_fecha_nac) return
      const birthDate = moment(this.local.paciente_fecha_nac, 'YYYY-MM-DD')
      if (!birthDate.isValid()) return
      this.local.paciente_edad = moment().diff(birthDate, 'years')
    },

    textCapitalize (str) {
      if (!str) return ''
      return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
    },

    resetServiciosSelection () {
      ;(this.areas || []).forEach(area => (area.servicios || []).forEach(s => { s.seleccionado = 0 }))
    },

    onChangeCi (val) {
      this.searchCi = val
      this.$emit('buscar-ci', this.searchCi)
    },

    onSelectDoctor (id) {
      // para que el padre rellene datos
      this.$emit('select-doctor', id)
    },

    onTipoAtencionChange () {
      this.resetServiciosSelection()
      if (this.local.tipo_atencion === 'NO') this.local.establecimiento_salud = ''
      else this.local.tipo_otro = ''
    },

    onEstablecimientoChange () {
      this.resetServiciosSelection()
    },

    filteredServicios (area) {
      let servicios = area.servicios || []

      if (this.serviciosAreaId && area.id !== this.serviciosAreaId) return []

      if (this.local.tipo_atencion === 'SI') {
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
    }
  }
}
</script>

<template>
  <q-page class="q-pa-sm bg-grey-2">
    <!-- BREADCRUMB / VOLVER -->
    <div class="row items-center q-mb-sm">
      <div class="col">
        <q-breadcrumbs>
          <template v-slot:separator>
            <q-icon size="1.2em" name="arrow_forward" />
          </template>
          <q-breadcrumbs-el icon="science" label="Área Analítica" to="/analitica" />
          <q-breadcrumbs-el
            :label="solicitud ? ('Solicitud #' + solicitud.id) : 'Detalle'"
          />
        </q-breadcrumbs>
      </div>
      <div class="col-auto">
        <q-btn
          flat
          dense
          icon="arrow_back"
          label="Volver"
          no-caps
          @click="$router.back()"
        />
      </div>
    </div>

    <!-- ENCABEZADO TIPO PLANILLA -->
    <q-card flat bordered class="q-mb-sm">
      <q-card flat bordered class="q-pa-sm bg-grey-1">
        <q-card-actions align="right">
          <q-btn-dropdown
            flat
            color="primary"
            icon="print"
            label="Opciones de impresión"
            no-caps
            v-close-popup
          >
            <q-list dense>
              <q-item clickable @click="imprimir">
                <q-item-section avatar>
                  <q-icon name="print" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Imprimir Analítica</q-item-label>
                </q-item-section>
              </q-item>
              <q-item clickable @click="mandarDoctor">
                <q-item-section avatar>
                  <q-icon name="send" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Mandar informe al doctor</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
          <q-btn
            flat
            color="primary"
            icon="close"
            label="Cancelar"
            no-caps
            @click="$router.back()"
          />
          <q-btn
            unelevated
            color="primary"
            icon="done_all"
            :loading="saving"
            no-caps
            @click="guardarAnalitica"
          >
            Guardar Analítica y Finalizar
          </q-btn>
        </q-card-actions>
      </q-card>
      <q-card-section class="q-pa-sm">
        <div class="row q-col-gutter-sm">
          <div class="col-12 text-center">
            <div class="text-subtitle1 text-weight-bold">
              ANALÍTICA HEMATOLÓGICA
            </div>
            <div class="text-caption text-grey-7">
              (Diseño estilo planilla, similar al formato Word)
            </div>
          </div>
        </div>

        <q-separator class="q-my-sm" />

        <div class="row q-col-gutter-sm text-caption">
          <div class="col-12 col-sm-4">
            <div><b>Fecha / Hora recepción:</b></div>
            <div>{{ solicitud?.fecha_envio_analitica || '-' }}</div>

            <div class="q-mt-xs"><b>N° SUS / EXT:</b></div>
            <div>{{ solicitud?.codigo || '-' }}</div>

            <div class="q-mt-xs"><b>Código paciente:</b></div>
            <div>{{ solicitud?.paciente?.id || '-' }}</div>
          </div>

          <div class="col-12 col-sm-4">
            <div><b>Nombre paciente:</b></div>
            <div>
              {{
                solicitud?.paciente_nombre ||
                solicitud?.paciente?.nombre_completo ||
                '-'
              }}
            </div>

            <div class="q-mt-xs"><b>Edad:</b></div>
            <div>
              {{
                solicitud?.paciente_edad ||
                solicitud?.paciente?.edad ||
                '-'
              }} años
            </div>

            <div class="q-mt-xs"><b>Sexo:</b></div>
            <div>
              {{
                solicitud?.paciente_genero ||
                solicitud?.paciente?.genero ||
                '-'
              }}
            </div>
          </div>

          <div class="col-12 col-sm-4">
            <div><b>Médico solicitante:</b></div>
            <div>
              {{ solicitud?.doctor_nombre || solicitud?.doctor?.name || '-' }}
            </div>

            <div class="q-mt-xs"><b>Diagnóstico clínico:</b></div>
            <div>{{ solicitud?.diagnostico_clinico || '-' }}</div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- TIPOS DE MUESTRA DESDE PRE-ANALÍTICA -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="q-pa-sm">
        <div
          v-if="tiposMuestra.length"
          class="q-mt-none"
        >
          <div class="text-subtitle2 q-mb-xs">
            Tipos de muestra enviados desde Pre-analítica
          </div>
          <q-markup-table dense bordered flat class="full-width">
            <thead>
            <tr>
              <th class="text-left">Área</th>
              <th class="text-left">Tipo de muestra</th>
              <th class="text-center">Estado</th>
            </tr>
            </thead>
            <tbody>
            <tr
              v-for="m in tiposMuestra"
              :key="m.id"
            >
              <td class="text-left text-caption">
                {{ m.area_tipo_muestra?.area?.name || '-' }}
              </td>
              <td class="text-left text-caption">
                {{ m.area_tipo_muestra?.tipo_muestra || m.nombre || '-' }}
              </td>
              <td class="text-center text-caption">
                <q-badge
                  v-if="m.selected"
                  color="green"
                  label="Seleccionada"
                  outline
                  dense
                />
                <q-badge
                  v-else
                  color="grey"
                  label="Pendiente"
                  outline
                  dense
                />
              </td>
            </tr>
            </tbody>
          </q-markup-table>
        </div>
      </q-card-section>
    </q-card>

    <!-- LISTA DE ÁREAS, SERVICIOS Y RANGOS -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="q-pa-sm">
        <div class="text-subtitle2 q-mb-xs">
          Resultados por área y rango
        </div>
        <div class="text-caption text-grey-7">
          Cada área muestra sus parámetros (rangos de referencia). Escribe el
          valor obtenido para el paciente.
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-sm">
        <q-skeleton
          v-if="loading || !solicitud"
          type="rect"
          height="120px"
          class="q-mb-sm"
        />

        <div v-else>
          <div
            v-for="area in areasConRangos"
            :key="area.id"
            class="q-mb-md"
          >
            <q-card flat bordered>
              <q-card-section class="bg-grey-2 q-pa-sm row items-center">
                <div class="col">
                  <div class="text-subtitle2">
                    Área: {{ area.name }}
                  </div>
                  <div class="text-caption text-grey-7">
                    <b>Servicios vinculados:</b>
                    {{ area.servicios.map(s => s.nombre).join(', ') || '—' }}
                  </div>
                  <div
                    v-if="esHematologia(area)"
                    class="text-caption text-grey-8 q-mt-xs"
                  >
                  </div>
                </div>
                <div class="col-auto">
                  <q-chip
                    dense
                    outline
                    color="primary"
                    icon="biotech"
                    :label="(area.rangos || []).length + ' parámetros'"
                  />
                </div>
              </q-card-section>

              <q-separator />

              <q-card-section class="q-pa-sm">
                <!-- ÁREA 1: HEMATOLOGÍA - Sangre entera -->
                <q-form v-if="area.id === 1 && areaExtras[area.id]">
                  <div class="row q-col-gutter-sm text-caption q-mb-md">
                    <div class="col-12 col-sm-3">
                      <div class="text-weight-medium q-mb-xs">Sangre entera</div>
                      <q-option-group
                        v-model="areaExtras[area.id].aceptada"
                        type="radio"
                        :options="[
                          { label: 'Aceptada', value: 'ACEPTADA' },
                          { label: 'Rechazada', value: 'RECHAZADA' }
                        ]"
                        dense
                      />
                    </div>
                    <div class="col-12 col-sm-3">
                      <div class="text-weight-medium q-mb-xs">Presencia de coágulo</div>
                      <q-option-group
                        v-model="areaExtras[area.id].coagulo"
                        type="radio"
                        :options="[
                          { label: 'Sí', value: 'SI' },
                          { label: 'No', value: 'NO' }
                        ]"
                        dense
                      />
                    </div>
                    <div class="col-12 col-sm-3">
                      <div class="text-weight-medium q-mb-xs">Volumen adecuado</div>
                      <q-option-group
                        v-model="areaExtras[area.id].volumen"
                        type="radio"
                        :options="[
                          { label: 'Sí', value: 'SI' },
                          { label: 'No', value: 'NO' }
                        ]"
                        dense
                      />
                    </div>
                    <div class="col-12 col-sm-3 q-mt-sm">
                      <div class="text-weight-medium q-mb-xs">Identificación</div>
                      <q-option-group
                        v-model="areaExtras[area.id].identificacion"
                        type="radio"
                        :options="[
                          { label: 'Adecuada', value: 'ADECUADA' },
                          { label: 'Inadecuada', value: 'INADECUADA' }
                        ]"
                        dense
                      />
                    </div>
                    <div class="col-12 col-sm-3 q-mt-sm">
                      <div class="text-weight-medium q-mb-xs">Equipo</div>
                      <q-select
                        v-model="areaExtras[area.id].equipo"
                        :options="['Mindray C3510','Mindray 5000']"
                        dense
                        outlined
                        emit-value
                        map-options
                        placeholder="Seleccione equipo"
                      />
                    </div>
                  </div>
                </q-form>

                <!-- ÁREA 2: QUÍMICA SANGUÍNEA - Suero -->
                <q-form v-if="area.id === 2 && areaExtras[area.id]">
                  <div class="row q-col-gutter-sm text-caption q-mb-md">
                    <div class="col-12 col-sm-3">
                      <div class="text-weight-medium q-mb-xs">Suero</div>
                      <q-option-group
                        v-model="areaExtras[area.id].aceptada"
                        type="radio"
                        :options="[
                          { label: 'Aceptada', value: 'ACEPTADA' },
                          { label: 'Rechazada', value: 'RECHAZADA' }
                        ]"
                        dense
                      />
                    </div>
                    <div class="col-12 col-sm-3">
                      <div class="text-weight-medium q-mb-xs">Muestra hemolizada</div>
                      <q-option-group
                        v-model="areaExtras[area.id].hemolizada"
                        type="radio"
                        :options="[
                          { label: 'Sí', value: 'SI' },
                          { label: 'No', value: 'NO' }
                        ]"
                        dense
                      />
                    </div>
                    <div class="col-12 col-sm-3">
                      <div class="text-weight-medium q-mb-xs">Volumen insuficiente</div>
                      <q-option-group
                        v-model="areaExtras[area.id].volumen_insuficiente"
                        type="radio"
                        :options="[
                          { label: 'Sí', value: 'SI' },
                          { label: 'No', value: 'NO' }
                        ]"
                        dense
                      />
                    </div>
                    <div class="col-12 col-sm-3 q-mt-sm">
                      <div class="text-weight-medium q-mb-xs">Identificación inadecuada</div>
                      <q-option-group
                        v-model="areaExtras[area.id].identificacion"
                        type="radio"
                        :options="[
                          { label: 'Adecuada', value: 'ADECUADA' },
                          { label: 'Inadecuada', value: 'INADECUADA' }
                        ]"
                        dense
                      />
                    </div>
                    <div class="col-12 col-sm-3 q-mt-sm">
                      <div class="text-weight-medium q-mb-xs">Equipo</div>
                      <q-select
                        v-model="areaExtras[area.id].equipo"
                        :options="['Mindray 240 –STAT FAX 4500-RADIOMETER','Mindray']"
                        dense
                        outlined
                        emit-value
                        map-options
                        placeholder="Seleccione equipo"
                      />
                    </div>
                  </div>
                </q-form>

                <!-- ÁREA 3: SALA / CAMA / TIPO DE PACIENTE -->
                <q-form v-if="area.id === 3 && areaExtras[area.id]">
                  <div class="row q-col-gutter-sm text-caption q-mb-md">
                    <div class="col-12 col-sm-3">
                      <div class="text-weight-medium q-mb-xs">Sala</div>
                      <q-input
                        v-model="areaExtras[area.id].sala"
                        dense
                        outlined
                      />
                    </div>
                    <div class="col-12 col-sm-3">
                      <div class="text-weight-medium q-mb-xs">Cama</div>
                      <q-input
                        v-model="areaExtras[area.id].cama"
                        dense
                        outlined
                      />
                    </div>
                    <div class="col-12 col-sm-3 q-mt-sm">
                      <div class="text-weight-medium q-mb-xs">Paciente ambulatorio</div>
                      <q-option-group
                        v-model="areaExtras[area.id].paciente_ambulatorio"
                        type="radio"
                        :options="[
                          { label: 'Sí', value: 'SI' },
                          { label: 'No', value: 'NO' }
                        ]"
                        dense
                      />
                    </div>
                    <div class="col-12 col-sm-3 q-mt-sm">
                      <div class="text-weight-medium q-mb-xs">Paciente interno</div>
                      <q-option-group
                        v-model="areaExtras[area.id].paciente_interno"
                        type="radio"
                        :options="[
                          { label: 'Sí', value: 'SI' },
                          { label: 'No', value: 'NO' }
                        ]"
                        dense
                      />
                    </div>
                  </div>
                </q-form>

                <!-- TABLA DE PARÁMETROS -->
                <q-markup-table dense bordered flat class="full-width">
                  <thead>
                  <tr>
                    <th class="text-left">Parámetro</th>
                    <th class="text-center">Resultado</th>
                    <th class="text-center">Unidad</th>
                    <th class="text-center">Rango de referencia</th>
                    <th class="text-left">Interpretación</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr
                    v-for="r in area.rangos"
                    :key="r.id"
                  >
                    <td class="text-left text-caption">
                      {{ r.rango_nombre }}
                    </td>

                    <!-- CELDA RESULTADO -->
                    <td class="text-center">
                      <!-- HEMATOLOGÍA: dos columnas AUTO / MANUAL -->
                      <div
                        v-if="esHematologia(area)"
                        class="row q-col-gutter-xs"
                        style="max-width: 260px; margin: 0 auto;"
                      >
                        <div class="col-6">
                          <q-input
                            v-model="resultados[area.id][r.id].valor_automatizado"
                            dense
                            outlined
                            label="Auto"
                          >
                            <template v-slot:prepend>
                              <q-icon
                                v-if="getEstadoRango(area.id, r, 'valor_automatizado') !== null"
                                :name="getEstadoRango(area.id, r, 'valor_automatizado') === 'ok' ? 'check_circle' : 'highlight_off'"
                                :color="getEstadoRango(area.id, r, 'valor_automatizado') === 'ok' ? 'blue-6' : 'red'"
                                size="16px"
                              />
                            </template>
                          </q-input>
                        </div>
                        <div class="col-6">
                          <q-input
                            v-model="resultados[area.id][r.id].valor_manual"
                            dense
                            outlined
                            label="Manual"
                          >
                            <template v-slot:prepend>
                              <q-icon
                                v-if="getEstadoRango(area.id, r, 'valor_manual') !== null"
                                :name="getEstadoRango(area.id, r, 'valor_manual') === 'ok' ? 'check_circle' : 'highlight_off'"
                                :color="getEstadoRango(area.id, r, 'valor_manual') === 'ok' ? 'blue-6' : 'red'"
                                size="16px"
                              />
                            </template>
                          </q-input>
                        </div>
                      </div>

                      <!-- OTRAS ÁREAS: solo un valor -->
                      <div v-else style="max-width: 140px; margin: 0 auto;">
                        <q-input
                          v-model="resultados[area.id][r.id].valor"
                          dense
                          outlined
                        >
                          <template v-slot:prepend>
                            <q-icon
                              v-if="getEstadoRango(area.id, r) !== null"
                              :name="getEstadoRango(area.id, r) === 'ok' ? 'check_circle' : 'highlight_off'"
                              :color="getEstadoRango(area.id, r) === 'ok' ? 'blue-6' : 'red'"
                              size="16px"
                            />
                          </template>
                        </q-input>
                      </div>
                    </td>

                    <td class="text-center text-caption">
                      {{ r.unidad || '' }}
                    </td>
                    <td class="text-center text-caption">
                      {{ formatRango(r) }}
                    </td>
                    <td class="text-left text-caption">
                      {{ r.interpretacion || '' }}
                    </td>
                  </tr>
                  </tbody>
                </q-markup-table>
              </q-card-section>
            </q-card>
          </div>

          <div
            v-if="!areasConRangos.length"
            class="text-caption text-grey-7 text-center q-mt-md"
          >
            La solicitud no tiene áreas con rangos configurados.
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: 'AnaliticaDetallePage',
  data () {
    return {
      loading: false,
      saving: false,
      solicitud: null,
      resultados: {},      // { [area_id]: { [rango_id]: { valor, valor_automatizado, valor_manual } } }
      areaExtras: {}       // { [area_id]: { campo: valor } } (sangre entera, suero, sala/cama, etc.)
    }
  },
  computed: {
    areasConRangos () {
      if (!this.solicitud || !this.solicitud.servicios) return []

      const map = {}
      this.solicitud.servicios.forEach(s => {
        const area = s.area
        if (!area) return
        if (!map[area.id]) {
          map[area.id] = {
            id: area.id,
            name: area.name,
            rangos: area.rangos || [],
            servicios: []
          }
        }
        map[area.id].servicios.push({
          id: s.id,
          nombre: s.nombre
        })
      })

      return Object.values(map)
    },
    tiposMuestra () {
      return (this.solicitud && this.solicitud.pre_analitica_muestras)
        ? this.solicitud.pre_analitica_muestras
        : []
    }
  },
  mounted () {
    this.cargarSolicitud()
  },
  methods: {
    mandarDoctor () {
      if (!this.solicitud) return

      const apiBase = this.$axios.defaults.baseURL || ''
      const backBase = apiBase.replace(/\/api\/?$/, '')

      const codigo = this.solicitud.nro_registro
      if (!codigo) {
        this.$alert?.error?.('La solicitud no tiene nro_registro generado.')
        return
      }

      const urlReporte = `${backBase}/public/reportes/${codigo}`

      let phone = this.solicitud.doctor_telefono || ''
      phone = phone.replace(/\D/g, '')

      if (!phone) {
        this.$alert?.error?.('El médico no tiene teléfono registrado.')
        return
      }

      const mensaje = `Dr(a). ${this.solicitud.doctor_nombre}, le envío los resultados de laboratorio del paciente ${this.solicitud.paciente_nombre}. Puede verlos aquí: ${urlReporte}`

      const text = encodeURIComponent(mensaje)

      const urlWhatsapp = `https://wa.me/591${phone}?text=${text}`

      window.open(urlWhatsapp, '_blank')
    },

    imprimir () {
      if (!this.solicitud || !this.solicitud.id) return

      const apiBase = this.$axios.defaults.baseURL || ''
      const backBase = apiBase.replace(/\/api\/?$/, '')

      const url = `${backBase}/api/solicitudes/${this.solicitud.id}/analitica-pdf`
      window.open(url, '_blank')
    },

    esHematologia (area) {
      if (!area) return false
      if (area.id === 1) return true
      const name = String(area.name || '').toUpperCase()
      return name.includes('HEMATO')
    },

    getValorResultado (areaId, rangoId, field = 'valor') {
      const area = this.resultados[areaId]
      if (!area || !area[rangoId]) return ''
      return area[rangoId][field]
    },

    parseValorNumerico (valor) {
      if (valor === null || valor === undefined) return null
      if (typeof valor === 'number') return isNaN(valor) ? null : valor

      const texto = String(valor).replace(',', '.').trim()
      if (!texto) return null

      const num = Number(texto)
      return isNaN(num) ? null : num
    },

    getEstadoRango (areaId, rango, field = 'valor') {
      const bruto = this.getValorResultado(areaId, rango.id, field)
      const valor = this.parseValorNumerico(bruto)
      if (valor === null) return null

      const min = rango.rango_minimo
      const max = rango.rango_maximo

      if (min != null && valor < min) return 'out'
      if (max != null && valor > max) return 'out'
      return 'ok'
    },

    formatRango (r) {
      const min = r.rango_minimo
      const max = r.rango_maximo
      if (min == null && max == null) return ''
      if (min != null && max != null) return `${min} - ${max}`
      if (min != null) return `≥ ${min}`
      return `≤ ${max}`
    },

    inicializarResultados () {
      const res = {}
      this.areasConRangos.forEach(area => {
        if (!res[area.id]) res[area.id] = {}
        ;(area.rangos || []).forEach(r => {
          res[area.id][r.id] = {
            valor: '',
            valor_automatizado: '',
            valor_manual: ''
          }
        })
      })
      this.resultados = res
    },

    aplicarResultadosDesdeBackend () {
      if (!this.solicitud || !this.solicitud.resultados) return

      this.solicitud.resultados.forEach(row => {
        const areaId = row.area_id
        const rangoId = row.area_rango_id
        if (!this.resultados[areaId] || !this.resultados[areaId][rangoId]) {
          return
        }
        this.resultados[areaId][rangoId] = {
          valor: row.valor_final ?? '',
          valor_automatizado: row.valor_automatizado ?? '',
          valor_manual: row.valor_manual ?? ''
        }
      })
    },

    inicializarAreaExtras () {
      const extras = { ...this.areaExtras }

      this.areasConRangos.forEach(area => {
        const id = area.id
        if (!extras[id]) extras[id] = {}

        if (id === 1) {
          extras[id] = {
            aceptada: extras[id].aceptada ?? null,
            coagulo: extras[id].coagulo ?? null,
            volumen: extras[id].volumen ?? null,
            identificacion: extras[id].identificacion ?? null,
            equipo: extras[id].equipo ?? null
          }
        } else if (id === 2) {
          extras[id] = {
            aceptada: extras[id].aceptada ?? null,
            hemolizada: extras[id].hemolizada ?? null,
            volumen_insuficiente: extras[id].volumen_insuficiente ?? null,
            identificacion: extras[id].identificacion ?? null,
            equipo: extras[id].equipo ?? null
          }
        } else if (id === 3) {
          extras[id] = {
            sala: extras[id].sala ?? '',
            cama: extras[id].cama ?? '',
            paciente_ambulatorio: extras[id].paciente_ambulatorio ?? null,
            paciente_interno: extras[id].paciente_interno ?? null
          }
        }
      })

      this.areaExtras = extras
    },

    aplicarExtrasDesdeBackend () {
      if (!this.solicitud || !this.solicitud.propiedades) return

      const extras = { ...this.areaExtras }

      this.solicitud.propiedades.forEach(p => {
        const areaId = p.area_id
        if (!extras[areaId]) extras[areaId] = {}
        extras[areaId][p.campo] = p.valor
      })

      this.areaExtras = extras
    },

    cargarSolicitud () {
      const id = this.$route.params.id
      if (!id) return

      this.loading = true
      this.$axios
        .get(`solicitudes-area-analitica/${id}`)
        .then(res => {
          this.solicitud = res.data
          this.inicializarResultados()
          this.aplicarResultadosDesdeBackend()
          this.inicializarAreaExtras()
          this.aplicarExtrasDesdeBackend()
        })
        .catch(err => {
          console.error(err)
          const msg = err.response?.data?.message || err.message
          this.$alert?.error?.('Error al cargar solicitud: ' + msg)
        })
        .finally(() => {
          this.loading = false
        })
    },

    guardarAnalitica () {
      if (!this.solicitud || !this.solicitud.id) return

      this.saving = true
      this.$axios
        .post(`solicitudes/${this.solicitud.id}/analitica`, {
          resultados: this.resultados,
          propiedades_area: this.areaExtras
        })
        .then(() => {
          this.$alert?.success?.(
            'Analítica guardada y solicitud finalizada'
          )
        })
        .catch(err => {
          console.error(err)
          const msg = err.response?.data?.message || err.message
          this.$alert?.error?.('Error al guardar analítica: ' + msg)
        })
        .finally(() => {
          this.saving = false
        })
    }
  }
}
</script>

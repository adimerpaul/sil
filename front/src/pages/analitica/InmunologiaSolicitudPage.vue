<template>
  <q-page class="q-pa-sm bg-grey-2">
    <q-card flat bordered>
      <!-- HEADER -->
      <q-card-section class="row items-center q-pa-sm">
        <div>
          <div class="text-h6 text-weight-bold">Inmunología · Analítica</div>
          <div class="text-caption text-grey-7">Ingresa los resultados para cada prestación.</div>
        </div>
        <q-space />
        <q-btn flat icon="arrow_back" label="Volver" no-caps @click="$router.back()" />
        <q-btn
          class="q-ml-sm" flat color="deep-purple"
          icon="print" label="Imprimir" no-caps
          :disable="!prestaciones.length"
          @click="imprimirPdf"
        />
        <q-btn
          class="q-ml-sm" color="primary"
          icon="save" label="Guardar" no-caps
          :loading="saving"
          :disable="!prestaciones.length"
          @click="guardar"
        />
      </q-card-section>

      <q-separator />

      <!-- INFO SOLICITUD -->
      <q-card-section class="q-pa-xs bg-grey-1">
        <div class="row q-gutter-sm text-caption q-px-sm">
          <span><span class="text-grey-6">Paciente: </span><strong>{{ solicitud?.paciente_nombre }}</strong></span>
          <span><span class="text-grey-6">Edad: </span><strong>{{ solicitud?.paciente_edad }}</strong></span>
          <span><span class="text-grey-6">Género: </span><strong>{{ solicitud?.paciente_genero }}</strong></span>
          <span><span class="text-grey-6">Médico: </span><strong>{{ solicitud?.doctor_nombre || 'N/A' }}</strong></span>
          <q-chip square color="primary" text-color="white" dense>N° {{ solicitud?.codigo }}</q-chip>
        </div>

        <!-- Fecha de recepción de la muestra y comentario: uno solo para toda la analítica -->
        <div v-if="prestaciones.length" class="row q-col-gutter-sm q-px-sm q-mt-xs q-mb-xs">
          <div class="col-12 col-sm-4">
            <q-input
              v-model="fechaRecepcion"
              type="date"
              dense outlined clearable stack-label
              label="Fecha de recepción de la muestra"
            />
          </div>
          <div class="col-12 col-sm-8">
            <q-input
              v-model="comentario"
              type="textarea"
              dense outlined autogrow stack-label
              label="Comentario"
              placeholder="Comentario que se imprimirá en el resultado"
            />
          </div>

          <!-- Método y equipo: uno solo para toda la analítica -->
          <div class="col-12 col-sm-4">
            <q-select
              v-model="metodo"
              :options="metodoOptions"
              dense outlined clearable stack-label
              label="Método"
            />
          </div>
          <div class="col-12 col-sm-8">
            <div class="row items-center no-wrap q-gutter-xs">
              <q-select
                v-model="equipo"
                :options="equipoOptions"
                dense outlined clearable stack-label
                class="col"
                label="Equipo"
              />
              <q-btn
                flat round dense icon="settings" color="grey-7" size="sm"
                @click="abrirCatalogo"
              >
                <q-tooltip>Administrar métodos y equipos</q-tooltip>
              </q-btn>
            </div>
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <!-- CUERPO -->
      <q-card-section class="q-pa-sm">
        <div v-if="loading" class="text-center q-pa-lg text-grey-7">
          <q-spinner size="40px" color="primary" />
          <div class="q-mt-sm">Cargando...</div>
        </div>

        <div v-else-if="!prestaciones.length" class="text-center q-pa-lg text-grey-6">
          <q-icon name="info" size="40px" />
          <div class="q-mt-sm">No hay prestaciones de inmunología con rangos vinculados.</div>
          <div class="text-caption q-mt-xs">
            Vincule rangos en <strong>Servicios → Vincular rangos</strong>.
          </div>
        </div>

        <!-- Prestaciones agrupadas por sub-área -->
        <div v-else>
          <div v-for="grupo in gruposSubarea" :key="grupo.clave || 'sin-subarea'" class="q-mb-md">
            <!-- Cabecera sub-área -->
            <div class="imn-subarea q-mb-sm">
              <q-icon name="category" size="16px" class="q-mr-xs" />
              <span>{{ grupo.subarea || 'Otros' }}</span>
              <q-badge class="q-ml-sm" color="primary" outline>{{ grupo.prestaciones.length }}</q-badge>
            </div>

            <div v-for="prest in grupo.prestaciones" :key="prest.servicio_id" class="q-mb-md">
            <!-- Cabecera prestación -->
            <div class="row items-center q-mb-xs">
              <q-icon name="biotech" color="primary" class="q-mr-xs" />
              <span class="text-subtitle2 text-weight-bold">{{ prest.nombre }}</span>
              <q-chip v-if="prest.metodo" dense class="q-ml-sm" color="grey-3">{{ prest.metodo }}</q-chip>
              <q-space />
              <template v-if="prest.realizado === 'REALIZADO'">
                <q-icon name="check_circle" color="green" size="18px" class="q-mr-xs" />
                <span class="text-caption text-teal-8">{{ prest.realizado_por }}</span>
              </template>
              <template v-else>
                <q-icon name="cancel" color="orange" size="18px" class="q-mr-xs" />
                <span class="text-caption text-orange-8">Pendiente</span>
              </template>
            </div>

            <!-- Tabla dense de rangos -->
            <table v-if="prest.rangos.length" class="imn-table q-mb-xs">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Analito / Condición</th>
                  <th>Método</th>
                  <th style="min-width:140px">Valor del paciente</th>
                  <th>Unidad</th>
                  <th v-if="tieneInterpretacion(prest)" style="width:150px">Interpretación</th>
                  <th style="max-width:220px">Rango de referencia</th>
                  <th style="width:80px">Estado</th>
                  <th style="width:44px" class="tc" title="Se muestra en el PDF de resultados">
                    <q-icon name="print" size="14px" />
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(rango, idx) in prest.rangos"
                  :key="rango.id"
                  :class="esFormula(prest, rango) ? 'fila-calculada' : ''"
                >
                  <td class="tc text-grey-6">{{ idx + 1 }}</td>
                  <td>
                    <div class="row items-center no-wrap">
                      <q-icon
                        v-if="esFormula(prest, rango)"
                        name="functions" color="teal" size="13px"
                        class="q-mr-xs"
                      >
                        <q-tooltip>Valor calculado automáticamente</q-tooltip>
                      </q-icon>
                      <span>{{ rango.rango_nombre }}</span>
                    </div>
                    <div v-if="rango.perfil" class="text-caption text-purple-7" style="font-size:10px">
                      {{ rango.perfil }}
                    </div>
                  </td>
                  <td class="text-grey-7">{{ rango.metodo || '—' }}</td>
                  <td>
                    <!-- Dato de lista: se selecciona, no se escribe -->
                    <q-select
                      v-if="rango.opciones && rango.opciones.length"
                      v-model="valores[rango.id]"
                      :options="rango.opciones"
                      dense outlined options-dense clearable
                      class="imn-select"
                      @update:model-value="actualizarPrestacion(prest, rango.id)"
                    />
                    <input
                      v-else
                      v-model="valores[rango.id]"
                      class="imn-input"
                      :class="{ 'imn-input--calculado': esFormula(prest, rango) }"
                      :placeholder="rango.unidad || ''"
                      @input="actualizarPrestacion(prest, rango.id)"
                    />
                  </td>
                  <td class="text-grey-6" style="font-size:11px">{{ rango.unidad || '—' }}</td>
                  <td v-if="tieneInterpretacion(prest)">
                    <!-- Interpretación: solo en los rangos marcados en "Vincular rangos" -->
                    <q-select
                      v-if="rango.con_interpretacion"
                      v-model="interpretaciones[rango.id]"
                      :options="opcionesInterpretacion"
                      dense outlined options-dense clearable
                      class="imn-select"
                      @update:model-value="actualizarPrestacion(prest, rango.id)"
                    />
                    <span v-else class="text-grey-5" style="font-size:11px">—</span>
                  </td>
                  <td>
                    <!-- Sub-rangos numéricos -->
                    <template v-if="subRangos(rango).length">
                      <div
                        v-for="(sr, si) in subRangos(rango)"
                        :key="si"
                        style="font-size:11px; white-space:nowrap"
                      >
                        <span v-if="sr.descripcion" class="text-grey-7">
                          {{ sr.descripcion }}{{ sr.minimo !== null || sr.maximo !== null ? ': ' : '' }}
                        </span>
                        <span>
                          {{ sr.minimo !== null ? sr.minimo : '' }}
                          {{ sr.minimo !== null && sr.maximo !== null ? ' – ' : '' }}
                          {{ sr.maximo !== null ? sr.maximo : '' }}
                        </span>
                      </div>
                    </template>
                    <!-- Texto libre de referencia -->
                    <div
                      v-if="rango.interpretacion"
                      class="text-grey-6 ellipsis"
                      style="max-width:216px; font-size:10px"
                      :title="rango.interpretacion"
                    >
                      {{ rango.interpretacion }}
                    </div>
                    <span v-if="!subRangos(rango).length && !rango.interpretacion" class="text-grey-5" style="font-size:11px">—</span>
                  </td>
                  <td class="tc">
                    <span v-if="esFormula(prest, rango)" class="text-teal-7" style="font-size:10px">calc.</span>
                    <div v-for="(e, ei) in estadoRango(rango)" :key="ei">
                      <span class="imn-badge" :class="`imn-badge--${e.tipo}`">
                        {{ e.label }}
                      </span>
                    </div>
                  </td>
                  <td class="tc">
                    <q-checkbox
                      v-model="visibles[rango.id]"
                      dense size="xs" color="primary"
                      @update:model-value="marcarPrestacionModificada(prest)"
                    >
                      <q-tooltip>
                        {{ visibles[rango.id] ? 'Se imprime en el PDF' : 'Oculto en el PDF' }}
                      </q-tooltip>
                    </q-checkbox>
                  </td>
                </tr>
              </tbody>
            </table>

            <div v-else class="text-caption text-grey-6 q-pl-md q-mb-sm">
              Sin rangos vinculados.
            </div>
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- ADMINISTRACIÓN DEL CATÁLOGO MÉTODO / EQUIPO -->
    <q-dialog v-model="catalogoDialog">
      <q-card style="min-width:520px;max-width:95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1 text-weight-bold">Métodos y equipos de Inmunología</div>
          <q-space />
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <div class="row q-col-gutter-sm items-center">
            <div class="col-12 col-sm-4">
              <q-select
                v-model="catalogoForm.tipo"
                :options="['METODO', 'EQUIPO']"
                dense outlined label="Tipo"
              />
            </div>
            <div class="col-12 col-sm-5">
              <q-input
                v-model="catalogoForm.nombre"
                dense outlined
                :label="catalogoForm.tipo === 'EQUIPO' ? 'Nombre del equipo' : 'Nombre del método'"
                @keyup.enter="guardarCatalogo"
              />
            </div>
            <div class="col-12 col-sm-3 text-right">
              <q-btn
                dense no-caps color="primary"
                :icon="catalogoForm.id ? 'save' : 'add'"
                :label="catalogoForm.id ? 'Actualizar' : 'Agregar'"
                :loading="catalogoSaving"
                :disable="!catalogoForm.nombre"
                @click="guardarCatalogo"
              />
            </div>
          </div>
          <div v-if="catalogoForm.id" class="text-right q-mt-xs">
            <q-btn flat dense no-caps size="sm" label="Cancelar edición" @click="resetCatalogoForm" />
          </div>
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-none">
          <q-table
            :rows="catalogo"
            :columns="catalogoColumns"
            row-key="id"
            flat dense
            :loading="catalogoLoading"
            :pagination="{ rowsPerPage: 10 }"
            no-data-label="Sin métodos ni equipos registrados"
          >
            <template #body-cell-tipo="props">
              <q-td :props="props">
                <q-chip dense square :color="props.row.tipo === 'EQUIPO' ? 'teal-2' : 'indigo-2'">
                  {{ props.row.tipo }}
                </q-chip>
              </q-td>
            </template>

            <template #body-cell-acciones="props">
              <q-td :props="props" class="text-right">
                <q-btn flat round dense size="sm" icon="edit" color="primary" @click="editarCatalogo(props.row)" />
                <q-btn flat round dense size="sm" icon="delete" color="negative" @click="eliminarCatalogo(props.row)" />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'InmunologiaSolicitudPage',

  data () {
    return {
      loading: false,
      saving: false,
      solicitud: null,
      prestaciones: [],
      valores: {},
      visibles: {},               // ids que se imprimen en el PDF de esta solicitud
      interpretaciones: {},       // interpretación elegida por rango (Reactivo, Positivo, ...)
      opcionesInterpretacion: [
        'Reactivo',
        'No reactivo',
        'Positivo',
        'Negativo',
        'Indeterminado',
        'Alto positivo',
        'Débil positivo'
      ],
      fechaRecepcion: null,       // recepción de la muestra, para toda la analítica (YYYY-MM-DD)
      comentario: '',             // comentario que se imprime en el resultado
      metodo: null,               // método usado, para toda la analítica
      equipo: null,               // equipo usado, para toda la analítica
      catalogo: [],               // catálogo método/equipo de inmunología
      catalogoDialog: false,
      catalogoLoading: false,
      catalogoSaving: false,
      catalogoForm: { id: null, tipo: 'METODO', nombre: '' },
      catalogoColumns: [
        { name: 'tipo', label: 'Tipo', field: 'tipo', align: 'left', sortable: true },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left', sortable: true },
        { name: 'acciones', label: '', field: 'acciones', align: 'right' }
      ],
      manualOverrides: new Set(), // ids de campos calculados editados manualmente
      prestacionesModificadas: new Set()
    }
  },

  computed: {
    // El backend ya devuelve las prestaciones ordenadas por subárea; aquí solo
    // se agrupan respetando ese orden para titular cada bloque.
    gruposSubarea () {
      // en la BD conviven variantes ("HORMONAS" / "Hormonas"), se comparan normalizadas
      const clave = s => (s || '').trim().replace(/\s+/g, ' ').toUpperCase()
      const grupos = []
      this.prestaciones.forEach(prest => {
        const ultimo = grupos[grupos.length - 1]
        if (ultimo && ultimo.clave === clave(prest.subarea)) {
          ultimo.prestaciones.push(prest)
        } else {
          grupos.push({
            clave: clave(prest.subarea),
            subarea: (prest.subarea || '').trim(),
            prestaciones: [prest]
          })
        }
      })
      return grupos
    },

    // Métodos y equipos son listas independientes del mismo catálogo
    metodoOptions () {
      return this.catalogo.filter(c => c.tipo === 'METODO').map(c => c.nombre)
    },

    equipoOptions () {
      return this.catalogo.filter(c => c.tipo === 'EQUIPO').map(c => c.nombre)
    }
  },

  mounted () {
    this.load()
    this.loadCatalogo()
  },

  methods: {
    async load () {
      this.loading = true
      try {
        const { data } = await this.$axios.get(`/inmunologia-analitica/solicitud/${this.$route.params.id}`)
        this.solicitud = data.solicitud
        this.prestaciones = data.prestaciones.filter(p => p.rangos.length > 0)

        this.fechaRecepcion = data.solicitud?.inmunologia_fecha_recepcion || null
        this.comentario = data.solicitud?.inmunologia_comentario || ''
        this.metodo = data.solicitud?.inmunologia_metodo || null
        this.equipo = data.solicitud?.inmunologia_equipo || null

        this.valores = {}
        this.visibles = {}
        this.interpretaciones = {}
        this.manualOverrides = new Set()
        this.prestacionesModificadas = new Set()
        this.prestaciones.forEach(prest => {
          prest.rangos.forEach(rango => {
            const valorGuardado = rango.resultado?.valor_final ?? ''
            this.valores[rango.id] = valorGuardado
            this.visibles[rango.id] = rango.visible !== false
            this.interpretaciones[rango.id] = rango.resultado?.interpretacion || null
            // Si es campo de fórmula y tiene valor guardado → fue editado manualmente
            if (valorGuardado !== '' && valorGuardado !== null && this.esFormula(prest, rango)) {
              this.manualOverrides.add(rango.id)
            }
          })
          // Solo calcular fórmulas que no tienen valor manual guardado
          this.recalcularFormulas(prest)
        })
      } catch (e) {
        console.error(e)
        this.$q.notify({ type: 'negative', message: 'No se pudo cargar la analítica' })
      } finally {
        this.loading = false
      }
    },

    // Construye mapa nombre_variable → area_rango_id para una prestación
    mapaVariables (prest) {
      const map = {}
      prest.rangos.forEach(r => {
        if (r.nombre_variable) map[r.nombre_variable] = r.id
      })
      return map
    },

    // Devuelve true si este rango es el resultado de una fórmula
    esFormula (prest, rango) {
      if (!rango.nombre_variable) return false
      return (prest.formulas || []).some(f => f.nombre_variable === rango.nombre_variable)
    },

    // Igual pero por id (para uso interno sin el objeto rango completo)
    esFormulaId (prest, rangoId) {
      const rango = prest.rangos.find(r => r.id === rangoId)
      return rango ? this.esFormula(prest, rango) : false
    },

    marcarPrestacionModificada (prest) {
      this.prestacionesModificadas.add(prest.servicio_id)
    },

    // La columna de interpretación solo aparece si algún rango la tiene marcada
    tieneInterpretacion (prest) {
      return (prest.rangos || []).some(r => r.con_interpretacion)
    },

    actualizarPrestacion (prest, rangoId) {
      this.marcarPrestacionModificada(prest)
      this.recalcularFormulas(prest, rangoId)
    },

    // Recalcula todos los campos de fórmula de una prestación
    recalcularFormulas (prest, editandoId = null) {
      if (!(prest.formulas || []).length) return
      const varMap = this.mapaVariables(prest)

      // Si el usuario editó directamente un campo calculado, marcarlo como override manual
      if (editandoId !== null && this.esFormulaId(prest, editandoId)) {
        this.manualOverrides.add(editandoId)
      }

      prest.formulas.forEach(f => {
        const resultId = varMap[f.nombre_variable]
        if (!resultId) return
        // No sobreescribir campos con override manual
        if (this.manualOverrides.has(resultId)) return

        let expr = f.formula

        // Solo reemplazar variables que aparecen en esta fórmula
        let todosPresentes = true
        for (const [varName, rangoId] of Object.entries(varMap)) {
          if (varName === f.nombre_variable) continue
          const regex = new RegExp('\\b' + varName + '\\b', 'g')
          if (!regex.test(f.formula)) continue   // variable no usada en esta fórmula → ignorar
          const val = parseFloat(this.valores[rangoId])
          if (isNaN(val)) {
            todosPresentes = false
            break
          }
          expr = expr.replace(new RegExp('\\b' + varName + '\\b', 'g'), val)
        }

        if (!todosPresentes) {
          this.valores[resultId] = ''
          return
        }

        try {
          // eslint-disable-next-line no-new-func
          const resultado = new Function('return (' + expr + ')')()
          if (!isNaN(resultado) && isFinite(resultado)) {
            this.valores[resultId] = parseFloat(resultado.toFixed(4)).toString()
          }
        } catch {
          this.valores[resultId] = ''
        }
      })
    },

    subRangos (rango) {
      const defs = [
        { d: 'rango_descripcion', min: 'rango_minimo', max: 'rango_maximo' },
        { d: 'rango_2_descripcion', min: 'rango_2_minimo', max: 'rango_2_maximo' },
        { d: 'rango_3_descripcion', min: 'rango_3_minimo', max: 'rango_3_maximo' },
        { d: 'rango_4_descripcion', min: 'rango_4_minimo', max: 'rango_4_maximo' },
        { d: 'rango_5_descripcion', min: 'rango_5_minimo', max: 'rango_5_maximo' }
      ]
      return defs
        .map(def => ({
          descripcion: rango[def.d] || '',
          minimo: rango[def.min] ?? null,
          maximo: rango[def.max] ?? null
        }))
        .filter(sr => sr.descripcion || sr.minimo !== null || sr.maximo !== null)
    },

    estadoRango (rango) {
      const val = parseFloat(this.valores[rango.id])
      if (isNaN(val)) return []

      const srs = this.subRangos(rango)
      // Solo entrar en modo descripción si al menos uno tiene descripción Y límites
      const srConDesc = srs.filter(sr => sr.descripcion && (sr.minimo !== null || sr.maximo !== null))

      if (srConDesc.length) {
        const coinciden = srConDesc.filter(sr => {
          const sobreMin = sr.minimo === null || val >= sr.minimo
          const bajoMax = sr.maximo === null || val <= sr.maximo
          return sobreMin && bajoMax
        }).map(sr => ({ label: sr.descripcion, tipo: 'normal' }))
        return coinciden.length ? coinciden : [{ label: 'Fuera de rango', tipo: 'alto' }]
      }

      // Sin descripciones → comportamiento clásico Normal/Alto/Bajo
      if (rango.rango_minimo !== null && rango.rango_maximo !== null) {
        if (val < rango.rango_minimo) return [{ label: 'Bajo', tipo: 'bajo' }]
        if (val > rango.rango_maximo) return [{ label: 'Alto', tipo: 'alto' }]
        return [{ label: 'Normal', tipo: 'normal' }]
      }
      if (rango.rango_maximo !== null && val > rango.rango_maximo) return [{ label: 'Alto', tipo: 'alto' }]
      if (rango.rango_minimo !== null && val < rango.rango_minimo) return [{ label: 'Bajo', tipo: 'bajo' }]
      return []
    },

    async guardar () {
      this.saving = true
      try {
        const serviciosModificados = [...this.prestacionesModificadas]
        const resultados = this.prestaciones
          .filter(prest => this.prestacionesModificadas.has(prest.servicio_id))
          .flatMap(prest => prest.rangos.map(rango => ({
            area_rango_id: rango.id,
            valor_final: String(this.valores[rango.id] ?? ''),
            visible: this.visibles[rango.id] !== false,
            interpretacion: rango.con_interpretacion
              ? (this.interpretaciones[rango.id] || null)
              : null
          })))

        await this.$axios.post(
          `/inmunologia-analitica/solicitud/${this.$route.params.id}/resultados`,
          {
            resultados,
            servicios_modificados: serviciosModificados,
            fecha_recepcion: this.fechaRecepcion || null,
            comentario: this.comentario || null,
            metodo: this.metodo || null,
            equipo: this.equipo || null
          }
        )
        this.$q.notify({ type: 'positive', message: 'Resultados guardados correctamente' })
        await this.load()
      } catch (e) {
        console.error(e)
        this.$q.notify({ type: 'negative', message: 'Error al guardar resultados' })
      } finally {
        this.saving = false
      }
    },

    // ── Catálogo método / equipo de inmunología ─────────────────────────
    async loadCatalogo () {
      this.catalogoLoading = true
      try {
        const { data } = await this.$axios.get('/metodo-equipo-inmunologia')
        this.catalogo = data
      } catch (e) {
        console.error(e)
        this.$q.notify({ type: 'negative', message: 'Error al cargar métodos y equipos' })
      } finally {
        this.catalogoLoading = false
      }
    },

    abrirCatalogo () {
      this.resetCatalogoForm()
      this.catalogoDialog = true
      this.loadCatalogo()
    },

    resetCatalogoForm () {
      this.catalogoForm = { id: null, tipo: this.catalogoForm?.tipo || 'METODO', nombre: '' }
    },

    editarCatalogo (row) {
      this.catalogoForm = { id: row.id, tipo: row.tipo, nombre: row.nombre }
    },

    async guardarCatalogo () {
      if (!this.catalogoForm.nombre) return
      this.catalogoSaving = true
      try {
        const payload = {
          tipo: this.catalogoForm.tipo,
          nombre: this.catalogoForm.nombre
        }
        if (this.catalogoForm.id) {
          await this.$axios.put(`/metodo-equipo-inmunologia/${this.catalogoForm.id}`, payload)
        } else {
          await this.$axios.post('/metodo-equipo-inmunologia', payload)
        }
        this.$q.notify({ type: 'positive', message: 'Registro guardado' })
        this.resetCatalogoForm()
        await this.loadCatalogo()
      } catch (e) {
        console.error(e)
        this.$q.notify({ type: 'negative', message: 'Error al guardar el registro' })
      } finally {
        this.catalogoSaving = false
      }
    },

    eliminarCatalogo (row) {
      this.$q.dialog({
        title: 'Eliminar',
        message: `¿Eliminar el ${row.tipo === 'EQUIPO' ? 'equipo' : 'método'} "${row.nombre}"?`,
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/metodo-equipo-inmunologia/${row.id}`)
          this.$q.notify({ type: 'positive', message: 'Registro eliminado' })
          if (this.catalogoForm.id === row.id) this.resetCatalogoForm()
          await this.loadCatalogo()
        } catch (e) {
          console.error(e)
          this.$q.notify({ type: 'negative', message: 'Error al eliminar el registro' })
        }
      })
    },

    imprimirPdf () {
      const codigo = this.solicitud?.inmunologia_analitica_codigo
      if (!codigo) {
        this.$q.notify({ type: 'warning', message: 'Guarda los resultados primero' })
        return
      }
      window.open(`${this.$axios.defaults.baseURL}/inmunologia-analitica/resultado/${codigo}/pdf`, '_blank')
    }
  }
}
</script>

<style scoped>
.imn-subarea {
  display: flex;
  align-items: center;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: #1f2937;
  background: #eef2f7;
  border-left: 4px solid #1976d2;
  border-radius: 4px;
  padding: 5px 8px;
}
.imn-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}
.imn-table th {
  background: #ede9fe;
  color: #4c1d95;
  padding: 4px 6px;
  text-align: left;
  font-weight: 600;
  border: 1px solid #ddd6fe;
  white-space: nowrap;
}
.imn-table td {
  padding: 3px 6px;
  border: 1px solid #e5e7eb;
  vertical-align: middle;
}
.imn-table tr:hover td { background: #faf5ff; }
.fila-calculada td { background: #f0fdfa !important; }
.fila-calculada:hover td { background: #ccfbf1 !important; }
.tc { text-align: center; }

.imn-input {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  padding: 3px 7px;
  font-size: 12px;
  outline: none;
  background: #fff;
  box-sizing: border-box;
}
.imn-input:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 2px #ede9fe;
}
.imn-select :deep(.q-field__control) {
  min-height: 26px;
  height: 26px;
  font-size: 12px;
}
.imn-select :deep(.q-field__native),
.imn-select :deep(.q-field__marginal) {
  min-height: 26px;
  padding: 0;
}
.imn-input--calculado {
  background: #f0fdfa;
  border-color: #99f6e4;
  color: #0d9488;
  cursor: default;
}

.imn-badge {
  display: inline-block;
  padding: 1px 6px;
  border-radius: 10px;
  font-size: 10px;
  font-weight: 600;
}
.imn-badge--normal { background: #dcfce7; color: #166534; }
.imn-badge--bajo   { background: #fef3c7; color: #92400e; }
.imn-badge--alto   { background: #fee2e2; color: #991b1b; }
</style>

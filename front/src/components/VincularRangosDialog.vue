<template>
  <q-dialog
    :model-value="modelValue"
    maximized
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <q-card class="column no-wrap">
      <q-card-section class="row items-center q-pa-sm bg-deep-purple text-white">
        <q-icon name="bar_chart" class="q-mr-sm" />
        <div class="text-subtitle1">Vincular rangos de referencia</div>
        <q-space />
        <q-btn
          icon="close"
          flat round dense
          color="white"
          @click="$emit('update:modelValue', false)"
        />
      </q-card-section>

      <q-separator />

      <q-card-section class="col q-pa-sm" style="overflow-y: auto">
        <div class="text-body2 text-weight-medium q-mb-xs">
          {{ servicio?.nombre || '' }}
        </div>
        <div class="text-caption text-grey-7 q-mb-md">
          Busca y selecciona rangos para agregar. Arrastra las filas para cambiar el orden.
        </div>

        <!-- Combobox para buscar y agregar rangos -->
        <q-select
          v-model="rangoParaAgregar"
          :options="opcionesFiltradas"
          option-value="id"
          :option-label="opt => (opt.rango_nombre || opt.analito || '') + (opt.perfil ? ` (${opt.perfil})` : '')"
          label="Buscar y agregar rango..."
          dense outlined
          use-input
          input-debounce="150"
          clearable
          class="q-mb-md"
          style="max-width: 560px"
          @filter="onFiltrar"
          @update:model-value="onAgregar"
        >
          <template #option="scope">
            <q-item v-bind="scope.itemProps">
              <q-item-section>
                <q-item-label>{{ scope.opt.rango_nombre || scope.opt.analito }}</q-item-label>
                <q-item-label caption>
                  <span v-if="scope.opt.perfil" class="text-purple">{{ scope.opt.perfil }}</span>
                  <span v-if="scope.opt.metodo"> · {{ scope.opt.metodo }}</span>
                  <span v-if="scope.opt.unidad"> · {{ scope.opt.unidad }}</span>
                </q-item-label>
              </q-item-section>
            </q-item>
          </template>
          <template #no-option>
            <q-item>
              <q-item-section class="text-grey">Sin resultados o ya agregados</q-item-section>
            </q-item>
          </template>
        </q-select>

        <!-- Tabla con drag-and-drop -->
        <div v-if="lista.length" class="text-caption text-grey-7 q-mb-xs">
          {{ lista.length }} rango{{ lista.length !== 1 ? 's' : '' }} seleccionado{{ lista.length !== 1 ? 's' : '' }}
        </div>

        <q-markup-table v-if="lista.length" dense flat bordered separator="cell">
          <thead>
            <tr class="bg-grey-2">
              <th style="width: 36px"></th>
              <th style="width: 36px" class="text-center text-grey-7">#</th>
              <th class="text-left">Perfil</th>
              <th class="text-left">Rango / Analito</th>
              <th class="text-left">Método</th>
              <th class="text-left">Referencia</th>
              <th class="text-left" style="min-width: 190px">Nombre de variable</th>
              <th style="width: 44px"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, idx) in lista"
              :key="item.id"
              draggable="true"
              :class="{ 'bg-blue-1': dragOverIndex === idx }"
              style="cursor: grab"
              @dragstart="onDragStart(idx)"
              @dragover.prevent="onDragOver(idx)"
              @dragleave="dragOverIndex = null"
              @drop.prevent="onDrop"
              @dragend="dragOverIndex = null"
            >
              <td class="text-center">
                <q-icon name="drag_indicator" color="grey-5" size="sm" />
              </td>
              <td class="text-center text-grey-7 text-caption">{{ idx + 1 }}</td>
              <td class="text-caption">{{ item.perfil || '—' }}</td>
              <td class="text-caption text-weight-medium">{{ item.rango_nombre }}</td>
              <td class="text-caption">{{ item.metodo || '—' }}</td>
              <td class="text-caption" style="max-width: 220px">
                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px">
                  {{ item.interpretacion || '—' }}
                </div>
              </td>
              <td>
                <q-input
                  v-model="item.nombre_variable"
                  dense outlined
                  style="min-width: 170px"
                  placeholder="ej. glucosa_valor"
                  @click.stop
                />
              </td>
              <td class="text-center">
                <q-btn
                  dense flat round
                  icon="remove_circle_outline"
                  color="negative"
                  size="sm"
                  @click="quitarRango(idx)"
                />
              </td>
            </tr>
          </tbody>
        </q-markup-table>

        <div v-else class="q-pa-md text-caption text-grey-6 text-center bg-grey-1 rounded-borders">
          Aún no hay rangos seleccionados. Usa el selector de arriba para agregar.
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="row justify-end q-pa-sm">
        <q-btn
          flat
          label="Cancelar"
          :loading="loading"
          class="q-mr-sm"
          @click="$emit('update:modelValue', false)"
        />
        <q-btn
          color="deep-purple"
          icon="save"
          label="Guardar vinculación"
          :loading="loading"
          @click="guardar"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: 'VincularRangosDialog',

  props: {
    modelValue: Boolean,
    servicio: { type: Object, default: null },
    rangos: { type: Array, default: () => [] },
    vinculosIniciales: { type: Array, default: () => [] },
    loading: Boolean
  },

  emits: ['update:modelValue', 'save'],

  data () {
    return {
      lista: [],
      rangoParaAgregar: null,
      opcionesFiltradas: [],
      dragFromIndex: null,
      dragOverIndex: null
    }
  },

  watch: {
    modelValue (v) {
      if (v) this.inicializar()
    }
  },

  methods: {
    inicializar () {
      this.lista = [...this.vinculosIniciales]
        .sort((a, b) => (a.pivot?.orden ?? 0) - (b.pivot?.orden ?? 0))
        .map((r, idx) => ({
          id: r.id,
          rango_nombre: r.rango_nombre || r.analito || '',
          perfil: r.perfil || '',
          metodo: r.metodo || '',
          interpretacion: r.interpretacion || '',
          unidad: r.unidad || '',
          nombre_variable: r.pivot?.nombre_variable || '',
          orden: r.pivot?.orden ?? idx + 1
        }))
      this.rangoParaAgregar = null
      this.dragFromIndex = null
      this.dragOverIndex = null
      this.opcionesFiltradas = this.disponibles()
    },

    disponibles () {
      const usados = new Set(this.lista.map(r => r.id))
      return this.rangos.filter(r => !usados.has(r.id))
    },

    onFiltrar (val, update) {
      const term = (val || '').toLowerCase()
      update(() => {
        const disp = this.disponibles()
        this.opcionesFiltradas = term
          ? disp.filter(r =>
              (r.rango_nombre || '').toLowerCase().includes(term) ||
              (r.analito || '').toLowerCase().includes(term) ||
              (r.perfil || '').toLowerCase().includes(term) ||
              (r.metodo || '').toLowerCase().includes(term)
            )
          : disp
      })
    },

    onAgregar (rango) {
      if (!rango) return
      const nombre = rango.rango_nombre || rango.analito || ''
      this.lista.push({
        id: rango.id,
        rango_nombre: nombre,
        perfil: rango.perfil || '',
        metodo: rango.metodo || '',
        interpretacion: rango.interpretacion || '',
        unidad: rango.unidad || '',
        nombre_variable: this.toVariable(nombre),
        orden: this.lista.length + 1
      })
      this.$nextTick(() => { this.rangoParaAgregar = null })
      this.opcionesFiltradas = this.disponibles()
    },

    quitarRango (idx) {
      this.lista.splice(idx, 1)
      this.opcionesFiltradas = this.disponibles()
    },

    toVariable (nombre) {
      return (nombre || '')
        .toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '')
    },

    onDragStart (idx) {
      this.dragFromIndex = idx
    },

    onDragOver (idx) {
      this.dragOverIndex = idx
    },

    onDrop () {
      const from = this.dragFromIndex
      const to = this.dragOverIndex
      if (from === null || to === null || from === to) {
        this.dragFromIndex = null
        this.dragOverIndex = null
        return
      }
      const item = this.lista.splice(from, 1)[0]
      this.lista.splice(to, 0, item)
      this.dragFromIndex = null
      this.dragOverIndex = null
    },

    guardar () {
      const payload = this.lista.map((item, idx) => ({
        area_rango_id: item.id,
        nombre_variable: item.nombre_variable || null,
        orden: idx + 1
      }))
      this.$emit('save', payload)
    }
  }
}
</script>

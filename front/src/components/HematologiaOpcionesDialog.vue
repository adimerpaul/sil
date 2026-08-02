<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <q-card style="min-width: 420px; max-width: 90vw">
      <q-card-section class="row items-center q-pa-sm bg-deep-purple text-white">
        <q-icon name="settings" class="q-mr-sm" />
        <div class="text-subtitle1">{{ titulo }}</div>
        <q-space />
        <q-btn icon="close" flat round dense color="white" @click="$emit('update:modelValue', false)" />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-sm">
        <div class="row items-center no-wrap q-gutter-sm q-mb-sm">
          <q-input
            v-model="nuevo"
            dense
            outlined
            class="col"
            label="Nuevo nombre"
            :disable="saving"
            @keyup.enter="agregar"
          />
          <q-btn
            color="deep-purple"
            icon="add"
            label="Agregar"
            no-caps
            dense
            :disable="!nuevo || !nuevo.trim() || saving"
            @click="agregar"
          />
        </div>

        <q-markup-table dense flat bordered square>
          <thead>
          <tr>
            <th class="text-left">Nombre</th>
            <th class="text-center" style="width: 80px">Activo</th>
            <th class="text-center" style="width: 60px"></th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="opcion in lista" :key="opcion.id">
            <td>
              <q-input
                v-model="opcion.nombre"
                dense
                borderless
                :disable="saving"
                @blur="renombrar(opcion)"
                @keyup.enter="renombrar(opcion)"
              />
            </td>
            <td class="text-center">
              <q-toggle
                v-model="opcion.activo"
                dense
                :disable="saving"
                @update:model-value="guardar(opcion, { activo: opcion.activo })"
              />
            </td>
            <td class="text-center">
              <q-btn
                flat
                round
                dense
                color="negative"
                icon="delete"
                size="sm"
                :disable="saving"
                @click="eliminar(opcion)"
              />
            </td>
          </tr>
          <tr v-if="!loading && !lista.length">
            <td colspan="3" class="text-center text-grey-7">Sin opciones registradas</td>
          </tr>
          </tbody>
        </q-markup-table>

        <div class="text-caption text-grey-7 q-mt-xs">
          Las opciones desactivadas dejan de aparecer en la captura, pero no se borran de los resultados ya guardados.
        </div>

        <q-inner-loading :showing="loading">
          <q-spinner size="32px" />
        </q-inner-loading>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: 'HematologiaOpcionesDialog',

  props: {
    modelValue: Boolean,
    seccion: { type: String, required: true },
    tipo: { type: String, required: true }
  },

  emits: ['update:modelValue', 'saved'],

  data () {
    return {
      loading: false,
      saving: false,
      lista: [],
      nuevo: ''
    }
  },

  computed: {
    titulo () {
      const tipo = this.tipo === 'METODO' ? 'Métodos' : 'Equipos'
      const seccion = this.seccion === 'HEMOGRAMA' ? 'Hemograma' : 'Coagulograma'
      return `${tipo} de ${seccion}`
    }
  },

  watch: {
    modelValue (abierto) {
      if (abierto) this.load()
    }
  },

  methods: {
    load () {
      this.loading = true
      this.$axios.get('hematologia-opciones')
        .then(res => {
          this.lista = (res.data.opciones || [])
            .filter(o => o.seccion === this.seccion && o.tipo === this.tipo)
            .map(o => ({ ...o, activo: Boolean(o.activo), nombreOriginal: o.nombre }))
        })
        .catch(e => this.$alert.error('Error al cargar opciones: ' + (e.response?.data?.message || e.message)))
        .finally(() => { this.loading = false })
    },

    agregar () {
      const nombre = (this.nuevo || '').trim()
      if (!nombre) return

      this.saving = true
      this.$axios.post('hematologia-opciones', {
        seccion: this.seccion,
        tipo: this.tipo,
        nombre
      })
        .then(() => {
          this.nuevo = ''
          this.load()
          this.$emit('saved')
        })
        .catch(e => this.$alert.error('Error al agregar: ' + (e.response?.data?.message || e.message)))
        .finally(() => { this.saving = false })
    },

    renombrar (opcion) {
      const nombre = (opcion.nombre || '').trim()
      if (!nombre || nombre === opcion.nombreOriginal) {
        opcion.nombre = opcion.nombreOriginal
        return
      }
      this.guardar(opcion, { nombre })
    },

    guardar (opcion, payload) {
      this.saving = true
      this.$axios.put(`hematologia-opciones/${opcion.id}`, payload)
        .then(res => {
          opcion.nombre = res.data.nombre
          opcion.nombreOriginal = res.data.nombre
          opcion.activo = Boolean(res.data.activo)
          this.$emit('saved')
        })
        .catch(e => {
          this.$alert.error('Error al guardar: ' + (e.response?.data?.message || e.message))
          this.load()
        })
        .finally(() => { this.saving = false })
    },

    eliminar (opcion) {
      this.$q.dialog({
        title: 'Eliminar opción',
        message: `¿Eliminar "${opcion.nombre}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => {
        this.saving = true
        this.$axios.delete(`hematologia-opciones/${opcion.id}`)
          .then(() => {
            this.load()
            this.$emit('saved')
          })
          .catch(e => this.$alert.error('Error al eliminar: ' + (e.response?.data?.message || e.message)))
          .finally(() => { this.saving = false })
      })
    }
  }
}
</script>

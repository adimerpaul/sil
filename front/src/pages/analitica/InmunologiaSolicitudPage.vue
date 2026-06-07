<template>
  <q-page class="q-pa-sm bg-grey-2">
    <div :class="['calc-drawer', showCalculadora ? 'open' : '']">
      <CalculadoraLab @close="showCalculadora = false" />
    </div>
    <q-card flat bordered>
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6 text-weight-bold">Inmunología · Formularios</div>
          <div class="text-caption text-grey-7">
            Selecciona formularios del área, edítalos por solicitud y imprímelos.
          </div>
        </div>
        <q-space />
        <q-btn flat icon="arrow_back" label="Volver" @click="$router.back()" />
        <q-btn
          class="q-ml-sm"
          outline
          color="primary"
          icon="print"
          label="Imprimir todo"
          no-caps
          :disable="!seleccionados.length"
          @click="printAll"
        />
<!--        btn calculadora-->
        <q-btn
          class="q-ml-sm"
          outline
          color="primary"
          icon="calculate"
          label="Calculadora"
          no-caps
          @click="showCalculadora = true"
        />
      </q-card-section>

      <q-separator />

      <!-- Datos solicitud -->
<!--      <q-card-section class="q-pa-sm">-->
<!--        <div class="row q-col-gutter-sm text-caption">-->
<!--          <div class="col-12 col-md-4">-->
<!--            <div class="text-grey-7">Paciente</div>-->
<!--            <div class="text-body2 text-weight-medium">{{ solicitud?.paciente_nombre }}</div>-->
<!--            <div class="text-grey-7 q-mt-xs">-->
<!--              Edad: <b>{{ solicitud?.paciente_edad }}</b> • Género: <b>{{ solicitud?.paciente_genero }}</b>-->
<!--            </div>-->
<!--          </div>-->

<!--          <div class="col-12 col-md-4">-->
<!--            <div class="text-grey-7">Médico solicitante</div>-->
<!--            <div class="text-body2 text-weight-medium">{{ solicitud?.doctor_nombre || 'N/A' }}</div>-->
<!--            <div class="text-grey-7 q-mt-xs">Fecha solicitud: <b>{{ solicitud?.fecha_solicitud }}</b></div>-->
<!--          </div>-->

<!--          <div class="col-12 col-md-4">-->
<!--            <div class="text-grey-7">Solicitud</div>-->
<!--            <div class="row items-center q-col-gutter-xs q-mt-xs">-->
<!--              <div class="col-auto">-->
<!--                <q-chip square color="primary" text-color="white" dense>N° {{ solicitud?.codigo }}</q-chip>-->
<!--              </div>-->
<!--              <div class="col-auto">-->
<!--                <q-chip square outline color="primary" dense>{{ solicitud?.estado }}</q-chip>-->
<!--              </div>-->
<!--            </div>-->
<!--          </div>-->
<!--        </div>-->
<!--      </q-card-section>-->
      <InfoServicio :header="solicitud" />
      <q-separator />

      <!-- Layout 3 columnas -->
      <q-card-section class="q-pa-sm">
        <div class="row q-col-gutter-sm">

          <!-- IZQUIERDA: Seleccionados -->
          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section class="row items-center q-pb-none">
                <div class="text-subtitle2 text-weight-bold">En la solicitud</div>
                <q-space />
                <q-btn flat round dense icon="refresh" :loading="loading" @click="load" />
              </q-card-section>
              <q-card-section class="q-pt-sm">
                <q-input v-model="filterSel" dense outlined debounce="300" label="Buscar" />
                <div class="q-mt-sm">
                  <q-list bordered separator>
                    <q-item
                      v-for="r in filteredSeleccionados"
                      :key="r.id"
                      clickable
                      :active="selected?.id === r.id"
                      active-class="bg-blue-1"
                      @click="selectRow(r)"
                    >
                      <q-item-section>
                        <q-item-label class="text-weight-medium">{{ r.nombre }}</q-item-label>
                        <q-item-label caption>ID: {{ r.id }}</q-item-label>
                      </q-item-section>

                      <q-item-section side>
                        <q-btn flat round dense icon="print" color="primary" @click.stop="printOne(r)" />
                        <q-btn flat round dense icon="delete" color="negative" @click.stop="remove(r)" />
                      </q-item-section>
                    </q-item>

                    <q-item v-if="!filteredSeleccionados.length">
                      <q-item-section class="text-grey-7">
                        No hay formularios agregados.
                      </q-item-section>
                    </q-item>
                  </q-list>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <!-- CENTRO: Editor -->
          <div class="col-12 col-md-6">
            <q-card flat bordered>
              <q-card-section class="row items-center">
                <div>
                  <div class="text-subtitle2 text-weight-bold">Editor</div>
                  <div class="text-caption text-grey-7">
                    Edita el HTML SOLO para esta solicitud (no altera el formulario base).
                  </div>
                </div>
                <q-space />
                <q-btn
                  color="primary"
                  icon="save"
                  label="Guardar"
                  no-caps
                  :disable="!selected"
                  :loading="saving"
                  @click="save"
                />
              </q-card-section>

              <q-separator />

              <q-card-section>
                <q-input
                  v-model="edit.nombre"
                  dense outlined
                  label="Título / Nombre"
                  :disable="!selected"
                />
                <div class="q-mt-sm">
                  <q-editor
                    v-model="edit.html"
                    :disable="!selected"
                    :toolbar="toolbar"
                    min-height="260px"
                    placeholder="Escribe o pega el HTML aquí..."
                  />
                </div>

                <q-separator class="q-my-md" />

                <div class="text-subtitle2 q-mb-xs">Vista previa</div>
                <q-card flat bordered class="bg-grey-1">
                  <q-card-section>
                    <div v-if="selected" v-html="edit.html"></div>
                    <div v-else class="text-grey-7">Selecciona un formulario a la izquierda.</div>
                  </q-card-section>
                </q-card>
              </q-card-section>
            </q-card>
          </div>

          <!-- DERECHA: Disponibles -->
          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section class="row items-center q-pb-none">
                <div class="text-subtitle2 text-weight-bold">Disponibles</div>
              </q-card-section>

              <q-card-section class="q-pt-sm">
                <q-input v-model="filterDisp" dense outlined debounce="300" label="Buscar" />
                <div class="q-mt-sm">
                  <q-list bordered separator>
                    <q-item v-for="f in filteredDisponibles" :key="f.id" clickable @click="add(f)">
                      <q-item-section>
                        <q-item-label class="text-weight-medium">{{ f.nombre }}</q-item-label>
                        <q-item-label caption>ID: {{ f.id }}</q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <q-btn flat round dense icon="add" color="positive" />
                      </q-item-section>
                    </q-item>

                    <q-item v-if="!filteredDisponibles.length">
                      <q-item-section class="text-grey-7">
                        No hay formularios disponibles.
                      </q-item-section>
                    </q-item>
                  </q-list>
                </div>
              </q-card-section>
            </q-card>
          </div>

        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import InfoServicio from "components/InfoServicio.vue";
import CalculadoraLab from "components/CalculadoraLab.vue";

export default {
  name: 'InmunologiaSolicitudPage',
  components: {CalculadoraLab, InfoServicio},
  data () {
    return {
      showCalculadora: false,
      loading: false,
      saving: false,

      areaId: 0, // ✅ pon aquí el ID del área INMUNOLOGÍA o pásalo por route/query
      solicitud: null,

      disponibles: [],
      seleccionados: [],
      selected: null,

      edit: { nombre: '', html: '' },

      filterSel: '',
      filterDisp: '',

      toolbar: [
        ['undo', 'redo'],
        ['bold', 'italic', 'underline', 'strike'],
        ['quote', 'unordered', 'ordered'],
        ['link'],
        ['viewsource'],
        ['fullscreen']
      ]
    }
  },

  computed: {
    filteredSeleccionados () {
      const t = (this.filterSel || '').toLowerCase()
      return this.seleccionados.filter(x => (x.nombre || '').toLowerCase().includes(t))
    },
    filteredDisponibles () {
      const t = (this.filterDisp || '').toLowerCase()
      const selectedIds = new Set(this.seleccionados.map(s => s.formulario_id))
      return this.disponibles
        .filter(x => !selectedIds.has(x.id))
        .filter(x => (x.nombre || '').toLowerCase().includes(t))
    }
  },

  mounted () {
    // ✅ Opción 1: fijo
    this.areaId = 5

    // ✅ Opción 2: por query ?area_id=5
    // this.areaId = parseInt(this.$route.query.area_id || '0')
    //
    // if (!this.areaId) {
    //   this.$q.notify({ type: 'negative', message: 'Falta area_id (Inmunología). Usa ?area_id=XX' })
    //   return
    // }

    this.load()
  },

  methods: {
    async load () {
      this.loading = true
      try {
        const { data } = await this.$axios.get(`/inmunologia/solicitud/${this.$route.params.id}`, {
          params: { area_id: this.areaId }
        })
        this.solicitud = data.solicitud
        this.disponibles = data.disponibles || []
        this.seleccionados = data.seleccionados || []

        // si no hay seleccionado actual, selecciona el primero
        if (!this.selected && this.seleccionados.length) {
          this.selectRow(this.seleccionados[0])
        }
      } catch (e) {
        console.error(e)
        this.$q.notify({ type: 'negative', message: 'No se pudo cargar Inmunología' })
      } finally {
        this.loading = false
      }
    },

    selectRow (row) {
      this.selected = row
      this.edit = {
        nombre: row.nombre || '',
        html: row.html || ''
      }
    },

    async add (formulario) {
      try {
        const { data } = await this.$axios.post(`/inmunologia/solicitud/${this.$route.params.id}/add`, {
          formulario_id: formulario.id,
          area_id: this.areaId
        })
        // recarga lista seleccionados (simple y seguro)
        await this.load()
        // selecciona el recién agregado si existe
        const found = this.seleccionados.find(x => x.id === data.id)
        if (found) this.selectRow(found)
        this.$q.notify({ type: 'positive', message: 'Agregado a la solicitud' })
      } catch (e) {
        console.error(e)
        this.$q.notify({ type: 'negative', message: 'No se pudo agregar' })
      }
    },

    async remove (row) {
      this.$q.dialog({
        title: 'Quitar formulario',
        message: `¿Quitar "${row.nombre}" de esta solicitud?`,
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/inmunologia/solicitude-formulario/${row.id}`)
          if (this.selected?.id === row.id) {
            this.selected = null
            this.edit = { nombre: '', html: '' }
          }
          await this.load()
          this.$q.notify({ type: 'positive', message: 'Quitado' })
        } catch (e) {
          console.error(e)
          this.$q.notify({ type: 'negative', message: 'No se pudo quitar' })
        }
      })
    },

    async save () {
      if (!this.selected) return
      this.saving = true
      try {
        await this.$axios.put(`/inmunologia/solicitude-formulario/${this.selected.id}`, {
          nombre: this.edit.nombre,
          html: this.edit.html
        })

        // actualiza en memoria
        const idx = this.seleccionados.findIndex(x => x.id === this.selected.id)
        if (idx >= 0) {
          this.seleccionados[idx].nombre = this.edit.nombre
          this.seleccionados[idx].html = this.edit.html
          this.selected = this.seleccionados[idx]
        }

        this.$q.notify({ type: 'positive', message: 'Guardado' })
        this.printOne(this.selected)
      } catch (e) {
        console.error(e)
        this.$q.notify({ type: 'negative', message: 'No se pudo guardar' })
      } finally {
        this.saving = false
      }
    },

    printOne (row) {
      const url = `${this.$axios.defaults.baseURL}/inmunologia/solicitude-formulario/${row.id}/pdf`
      window.open(url, '_blank')
    },

    printAll () {
      const url = `${this.$axios.defaults.baseURL}/inmunologia/solicitud/${this.$route.params.id}/pdf-all?area_id=${this.areaId}`
      window.open(url, '_blank')
    }
  }
}
</script>
<style scoped>
.calc-backdrop{
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.35);
  z-index: 3000;
}

.calc-drawer{
  position: fixed;
  top: 0;
  right: 0;
  width: 380px;
  max-width: 90vw;
  height: 100vh;
  background: #fff;
  z-index: 3100;
  transform: translateX(100%);
  transition: transform .25s ease;
  box-shadow: -10px 0 30px rgba(0,0,0,.2);
}

.calc-drawer.open{
  transform: translateX(0);
}

.calc-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding: 10px 12px;
  border-bottom: 1px solid #e0e0e0;
}
</style>

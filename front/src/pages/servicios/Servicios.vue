<template>
  <q-page class="q-pa-sm bg-grey-2">
    <div class="row q-col-gutter-sm">
      <!-- IZQUIERDA: ÁREAS -->
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section class="row items-center q-pa-sm">
            <div>
              <div class="text-subtitle2">Áreas del laboratorio</div>
              <div class="text-caption text-grey-7">
                Seleccione un área para configurar
              </div>
            </div>
            <q-space />
            <q-btn
              color="primary"
              icon="add"
              dense
              round
              size="sm"
              @click="nuevaArea"
            />
          </q-card-section>

          <q-separator />

          <q-card-section class="q-pa-xs">
            <q-input
              dense
              outlined
              v-model="searchArea"
              label="Buscar área"
              class="q-mb-xs"
            >
              <template #append><q-icon name="search" /></template>
            </q-input>

            <q-list dense bordered class="rounded-borders">
              <q-item
                v-for="a in filteredAreas"
                :key="a.id"
                clickable
                :active="a.id === selectedAreaId"
                active-class="bg-primary text-white"
                @click="selectArea(a)"
              >
                <q-item-section>
                  <q-item-label>{{ a.name }}</q-item-label>
                  <q-item-label caption v-if="a.descripcion">
                    {{ a.descripcion }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-btn
                    flat
                    dense
                    round
                    icon="edit"
                    @click.stop="editarArea(a)"
                  />
                </q-item-section>
              </q-item>

              <q-item v-if="!areas.length" dense>
                <q-item-section>
                  <q-item-label caption>No hay áreas registradas</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <!-- DERECHA: CONFIGURACIÓN POR TABS -->
      <div class="col-12 col-md-9">
        <q-card flat bordered>
          <!-- HEADER + TABS -->
          <q-card-section class="row items-center q-pa-sm">
            <div class="col-12 col-md-6">
              <div class="row items-center">
                <div class="text-subtitle2">
                  Configuración de área
                </div>
              </div>
              <div class="q-mt-xs">
                <q-chip
                  v-if="selectedArea"
                  color="primary"
                  text-color="white"
                  icon="science"
                  dense
                >
                  {{ selectedArea.name }}
                </q-chip>
                <span v-else class="text-caption text-grey-7">
                  Seleccione un área para comenzar
                </span>
              </div>
              <div class="text-caption text-grey-7 q-mt-xs">
                Gestione las prestaciones, rangos de referencia y tipos de muestra
                asociados a cada área.
              </div>
            </div>

            <div class="col-12 col-md-6">
              <q-tabs
                v-model="tab"
                dense
                align="right"
                active-color="primary"
                indicator-color="primary"
                class="q-mt-sm q-mt-md-none"
              >
                <q-tab name="servicios" icon="list_alt" label="Prestaciones" />
                <q-tab name="rangos" icon="bar_chart" label="Rangos" />
                <q-tab name="muestras" icon="biotech" label="Tipos de muestra" />
              </q-tabs>
            </div>
          </q-card-section>

          <q-separator />

          <!-- PANELS -->
          <q-tab-panels v-model="tab" animated keep-alive>
            <!-- TAB: SERVICIOS -->
            <q-tab-panel name="servicios" class="q-pa-none">
              <q-card-section class="row items-center q-pa-sm">
                <div class="text-subtitle2">
                  Prestaciones
                  <span v-if="selectedArea">– {{ selectedArea.name }}</span>
                </div>
                <q-space />
                <q-input
                  dense
                  outlined
                  v-model="searchServicio"
                  label="Buscar prestaciones"
                  class="q-mr-sm"
                  style="max-width: 260px"
                >
                  <template #append><q-icon name="search" /></template>
                </q-input>
                <q-btn
                  color="positive"
                  icon="add_circle_outline"
                  label="Nuevo servicio"
                  no-caps
                  dense
                  :disable="!selectedAreaId"
                  :loading="loading"
                  @click="nuevoServicio"
                />
              </q-card-section>

              <q-separator />

              <q-card-section class="q-pa-none">
                <q-table
                  dense
                  flat
                  bordered
                  class="q-pa-xs"
                  :rows="filteredServicios"
                  :columns="columnsServicios"
                  row-key="id"
                  :rows-per-page-options="[0]"
                  no-data-label="No hay servicios registrados para esta área"
                >
                  <template #body-cell-actions="props">
                    <q-td :props="props">
                      <q-btn-dropdown
                        dense
                        flat
                        dropdown-icon="more_vert"
                        no-icon-animation
                      >
                        <q-list dense style="min-width: 170px">
                          <q-item clickable v-close-popup @click="editarServicio(props.row)">
                            <q-item-section avatar><q-icon name="edit" /></q-item-section>
                            <q-item-section>Editar</q-item-section>
                          </q-item>
                          <q-item clickable v-close-popup @click="abrirVincularServicio(props.row)">
                            <q-item-section avatar><q-icon name="link" /></q-item-section>
                            <q-item-section>Vincular</q-item-section>
                          </q-item>
                          <q-item clickable v-close-popup @click="eliminarServicio(props.row)">
                            <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                            <q-item-section class="text-negative">Eliminar</q-item-section>
                          </q-item>
                        </q-list>
                      </q-btn-dropdown>
                    </q-td>
                  </template>

                  <template #body-cell-nombre="props">
                    <q-td :props="props">
                      <div
                        style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                      >
                        {{ props.row.nombre }}
                      </div>
                      <div
                        class="text-caption text-grey"
                        v-html="props.row.descripcion || ''"
                      />
                    </q-td>
                  </template>

                  <template #body-cell-tipos_muestra="props">
                    <q-td :props="props">
                      <div v-if="props.row.tipos_muestra && props.row.tipos_muestra.length" class="q-gutter-xs">
                        <q-chip
                          v-for="tipo in props.row.tipos_muestra"
                          :key="tipo.id"
                          dense
                          color="blue-1"
                          text-color="primary"
                        >
                          {{ tipo.tipo_muestra }}
                        </q-chip>
                      </div>
                      <span v-else class="text-caption text-grey-7">Sin vincular</span>
                    </q-td>
                  </template>
                </q-table>
              </q-card-section>
            </q-tab-panel>

            <!-- TAB: RANGOS -->
            <q-tab-panel name="rangos" class="q-pa-none">
              <q-card-section class="row items-center q-pa-sm">
                <div class="text-subtitle2">
                  Rangos de referencia
                  <span v-if="selectedArea">– {{ selectedArea.name }}</span>
                </div>
                <q-space />
                <q-input
                  dense
                  outlined
                  v-model="searchRango"
                  label="Buscar rango"
                  class="q-mr-sm"
                  style="max-width: 260px"
                >
                  <template #append><q-icon name="search" /></template>
                </q-input>
                <q-btn
                  color="primary"
                  icon="add"
                  label="Nuevo rango"
                  no-caps
                  dense
                  :disable="!selectedAreaId"
                  :loading="loading"
                  @click="nuevoRango"
                />
              </q-card-section>

              <q-separator />

              <q-card-section class="q-pa-none">
                <q-table
                  dense
                  flat
                  bordered
                  class="q-pa-xs"
                  :rows="filteredRangos"
                  :columns="columnsRangos"
                  row-key="id"
                  :rows-per-page-options="[0]"
                  no-data-label="No hay rangos registrados para esta área"
                >
                  <template #body-cell-actions="props">
                    <q-td :props="props">
                      <q-btn
                        dense
                        flat
                        round
                        icon="edit"
                        @click="editarRango(props.row)"
                      />
                      <q-btn
                        dense
                        flat
                        round
                        icon="delete"
                        color="negative"
                        @click="eliminarRango(props.row.id)"
                      />
                    </q-td>
                  </template>

                  <template #body-cell-interpretacion="props">
                    <q-td :props="props">
                      <div
                        style="max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                        :title="props.row.interpretacion"
                      >
                        {{ props.row.interpretacion }}
                      </div>
                    </q-td>
                  </template>
                </q-table>
              </q-card-section>
            </q-tab-panel>

            <!-- TAB: TIPOS DE MUESTRA -->
            <q-tab-panel name="muestras" class="q-pa-none">
              <q-card-section class="row items-center q-pa-sm">
                <div class="text-subtitle2">
                  Tipos de muestra
                  <span v-if="selectedArea">– {{ selectedArea.name }}</span>
                </div>
                <q-space />
                <q-input
                  dense
                  outlined
                  v-model="searchTipoMuestra"
                  label="Buscar tipo de muestra"
                  class="q-mr-sm"
                  style="max-width: 260px"
                >
                  <template #append><q-icon name="search" /></template>
                </q-input>
                <q-btn
                  color="primary"
                  icon="add"
                  label="Nuevo tipo de muestra"
                  no-caps
                  dense
                  :disable="!selectedAreaId"
                  :loading="loading"
                  @click="nuevoTipoMuestra"
                />
              </q-card-section>

              <q-separator />

              <q-card-section class="q-pa-none">
                <q-table
                  dense
                  flat
                  bordered
                  class="q-pa-xs"
                  :rows="filteredAreaTipoMuestras"
                  :columns="columnsTipoMuestra"
                  row-key="id"
                  :rows-per-page-options="[0]"
                  no-data-label="No hay tipos de muestra registrados para esta área"
                >
                  <template #body-cell-actions="props">
                    <q-td :props="props">
                      <q-btn
                        dense
                        flat
                        round
                        icon="edit"
                        @click="editarTipoMuestra(props.row)"
                      />
                      <q-btn
                        dense
                        flat
                        round
                        icon="delete"
                        color="negative"
                        @click="eliminarTipoMuestra(props.row.id)"
                      />
                    </q-td>
                  </template>
                </q-table>
              </q-card-section>
            </q-tab-panel>
          </q-tab-panels>
        </q-card>
      </div>
    </div>

    <!-- DIALOG ÁREA -->
    <q-dialog v-model="dialogArea">
      <q-card style="min-width: 300px; max-width: 420px;">
        <q-card-section class="row items-center q-pa-sm">
          <div class="text-subtitle1">
            {{ editandoArea ? 'Editar área' : 'Nueva área' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-sm">
          <q-form @submit="guardarArea">
            <q-input
              v-model="areaForm.name"
              label="Nombre"
              dense
              outlined
              class="q-mb-xs"
            />
            <q-input
              v-model="areaForm.descripcion"
              label="Descripción"
              dense
              outlined
              class="q-mb-xs"
            />
            <q-select
              v-model="areaForm.estado"
              :options="['ACTIVO', 'INACTIVO']"
              label="Estado"
              dense
              outlined
            />

            <div class="text-right q-mt-sm">
              <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
              <q-btn
                color="primary"
                label="Guardar"
                type="submit"
                :loading="loading"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- DIALOG SERVICIO -->
    <q-dialog v-model="dialogServicio">
      <q-card style="min-width: 400px; max-width: 600px;">
        <q-card-section class="row items-center q-pa-sm">
          <div class="text-subtitle1">
            {{ editandoServicio ? 'Editar servicio' : 'Nuevo servicio' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-sm">
          <q-form @submit="guardarServicio">
            <div class="row q-col-gutter-xs">
              <div class="col-4">
                <q-input
                  v-model.number="servicioForm.codigo"
                  type="number"
                  label="Código"
                  dense
                  outlined
                />
              </div>
              <div class="col-8">
                <q-input
                  v-model="servicioForm.nombre"
                  label="Nombre"
                  dense
                  outlined
                />
              </div>
              <div class="col-4">
                <q-input
                  v-model="servicioForm.metodo"
                  label="Método"
                  dense
                  outlined
                />
              </div>
              <div class="col-4">
                <q-input
                  v-model="servicioForm.subarea"
                  label="Subárea"
                  dense
                  outlined
                />
              </div>
              <div class="col-4">
                <q-input
                  v-model.number="servicioForm.precio"
                  type="number"
                  step="0.01"
                  label="Precio"
                  dense
                  outlined
                />
              </div>
              <div class="col-4">
                <q-select
                  v-model="servicioForm.estado"
                  :options="['ACTIVO', 'INACTIVO']"
                  label="Estado"
                  dense
                  outlined
                />
              </div>
            </div>

            <div class="text-right q-mt-sm">
              <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
              <q-btn
                color="primary"
                label="Guardar"
                type="submit"
                :loading="loading"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- DIALOG RANGO -->
    <q-dialog v-model="dialogRango">
      <q-card style="min-width: 420px; max-width: 640px;">
        <q-card-section class="row items-center q-pa-sm">
          <div class="text-subtitle1">
            {{ editandoRango ? 'Editar rango' : 'Nuevo rango' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-sm">
          <q-form @submit="guardarRango">
            <div class="row q-col-gutter-xs">
              <div class="col-12">
                <q-input
                  v-model="rangoForm.rango_nombre"
                  label="Nombre del rango (ej. Hemoglobina)"
                  dense
                  outlined
                  autofocus
                />
              </div>

              <div class="col-4">
                <q-input
                  v-model.number="rangoForm.rango_minimo"
                  type="number"
                  step="0.01"
                  label="Mínimo"
                  dense
                  outlined
                />
              </div>
              <div class="col-4">
                <q-input
                  v-model.number="rangoForm.rango_maximo"
                  type="number"
                  step="0.01"
                  label="Máximo"
                  dense
                  outlined
                />
              </div>
              <div class="col-4">
                <q-input
                  v-model="rangoForm.unidad"
                  label="Unidad"
                  dense
                  outlined
                />
              </div>

              <div class="col-12">
                <q-input
                  v-model="rangoForm.interpretacion"
                  type="textarea"
                  autogrow
                  label="Interpretación / texto"
                  dense
                  outlined
                />
              </div>
            </div>

            <div class="text-right q-mt-sm">
              <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
              <q-btn
                color="primary"
                label="Guardar"
                type="submit"
                :loading="loading"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- DIALOG TIPO DE MUESTRA -->
    <q-dialog v-model="dialogTipoMuestra">
      <q-card style="min-width: 360px; max-width: 520px;">
        <q-card-section class="row items-center q-pa-sm">
          <div class="text-subtitle1">
            {{ editandoTipoMuestra ? 'Editar tipo de muestra' : 'Nuevo tipo de muestra' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-sm">
          <q-form @submit="guardarTipoMuestra">
            <q-input
              v-model="tipoMuestraForm.tipo_muestra"
              label="Descripción del tipo de muestra"
              dense
              outlined
              autofocus
            />

            <div class="text-right q-mt-sm">
              <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
              <q-btn
                color="primary"
                label="Guardar"
                type="submit"
                :loading="loading"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogVincularServicio">
      <q-card style="min-width: 420px; max-width: 620px;">
        <q-card-section class="row items-center q-pa-sm">
          <div class="text-subtitle1">
            Vincular tipos de muestra
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-sm">
          <div class="text-body2 text-weight-medium q-mb-xs">
            {{ servicioVincular?.nombre || '' }}
          </div>
          <div class="text-caption text-grey-7 q-mb-md">
            Seleccione los tipos de muestra que corresponden a esta prestación.
          </div>

          <div v-if="!areaTipoMuestras.length" class="text-caption text-grey-7">
            No hay tipos de muestra registrados para esta área.
          </div>

          <q-list v-else bordered separator>
            <q-item
              v-for="tipo in areaTipoMuestras"
              :key="tipo.id"
              tag="label"
              clickable
            >
              <q-item-section avatar>
                <q-checkbox
                  v-model="servicioTiposSeleccionados"
                  :val="tipo.id"
                />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ tipo.tipo_muestra }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>

          <div class="text-right q-mt-sm">
            <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
            <q-btn
              color="primary"
              label="Guardar"
              :loading="loading"
              @click="guardarVinculoServicio"
            />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'ServiciosConfigPage',
  data () {
    return {
      loading: false,

      // TAB actual
      tab: 'servicios',

      // ÁREAS
      areas: [],
      selectedAreaId: null,
      selectedArea: null,
      searchArea: '',
      dialogArea: false,
      editandoArea: false,
      areaForm: {
        id: null,
        name: '',
        descripcion: '',
        estado: 'ACTIVO'
      },

      // SERVICIOS
      servicios: [],
      searchServicio: '',
      dialogServicio: false,
      editandoServicio: false,
      dialogVincularServicio: false,
      servicioVincular: null,
      servicioTiposSeleccionados: [],
      servicioForm: {
        id: null,
        area_id: null,
        codigo: null,
        nombre: '',
        metodo: '',
        subarea: '',
        precio: 0,
        estado: 'ACTIVO'
      },

      columnsServicios: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'codigo', label: 'Código', field: 'codigo', align: 'left' },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left' },
        { name: 'metodo', label: 'Método', field: 'metodo', align: 'left' },
        { name: 'subarea', label: 'Subárea', field: 'subarea', align: 'left' },
        {
          name: 'precio',
          label: 'Precio',
          field: 'precio',
          align: 'right',
          format: v => `Bs. ${Number(v || 0).toFixed(2)}`
        },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left' },
        {
          name: 'tipos_muestra',
          label: 'Tipos de muestra',
          field: row => row.tipos_muestra || [],
          align: 'left'
        }
      ],

      // RANGOS
      rangos: [],
      searchRango: '',
      dialogRango: false,
      editandoRango: false,
      rangoForm: {
        id: null,
        area_id: null,
        rango_nombre: '',
        rango_minimo: null,
        rango_maximo: null,
        unidad: '',
        interpretacion: ''
      },

      columnsRangos: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        {
          name: 'rango_nombre',
          label: 'Nombre',
          field: 'rango_nombre',
          align: 'left'
        },
        {
          name: 'rango_minimo',
          label: 'Mínimo',
          field: 'rango_minimo',
          align: 'right'
        },
        {
          name: 'rango_maximo',
          label: 'Máximo',
          field: 'rango_maximo',
          align: 'right'
        },
        { name: 'unidad', label: 'Unidad', field: 'unidad', align: 'left' },
        {
          name: 'interpretacion',
          label: 'Interpretación',
          field: 'interpretacion',
          align: 'left'
        }
      ],

      // TIPOS DE MUESTRA
      areaTipoMuestras: [],
      searchTipoMuestra: '',
      dialogTipoMuestra: false,
      editandoTipoMuestra: false,
      tipoMuestraForm: {
        id: null,
        area_id: null,
        tipo_muestra: ''
      },

      columnsTipoMuestra: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        {
          name: 'tipo_muestra',
          label: 'Tipo de muestra',
          field: 'tipo_muestra',
          align: 'left'
        }
      ],

    }
  },
  computed: {
    filteredAreas () {
      const t = (this.searchArea || '').toLowerCase()
      if (!t) return this.areas
      return this.areas.filter(a =>
        a.name.toLowerCase().includes(t) ||
        (a.descripcion || '').toLowerCase().includes(t)
      )
    },
    filteredServicios () {
      const t = (this.searchServicio || '').toLowerCase()
      let list = this.servicios
      if (t) {
        list = list.filter(s =>
          (s.nombre || '').toLowerCase().includes(t) ||
          String(s.codigo || '').includes(t)
        )
      }
      return list
    },
    filteredRangos () {
      const t = (this.searchRango || '').toLowerCase()
      let list = this.rangos
      if (t) {
        list = list.filter(r =>
          (r.rango_nombre || '').toLowerCase().includes(t) ||
          (r.unidad || '').toLowerCase().includes(t) ||
          (r.interpretacion || '').toLowerCase().includes(t)
        )
      }
      return list
    },
    filteredAreaTipoMuestras () {
      const t = (this.searchTipoMuestra || '').toLowerCase()
      let list = this.areaTipoMuestras
      if (t) {
        list = list.filter(m =>
          (m.tipo_muestra || '').toLowerCase().includes(t)
        )
      }
      return list
    }
  },
  mounted () {
    this.loadAreas()
  },
  methods: {
    // --- ÁREAS ---
    loadAreas () {
      this.loading = true
      this.$axios.get('areas')
        .then(res => {
          this.areas = res.data
          if (!this.selectedAreaId && this.areas.length) {
            this.selectArea(this.areas[0])
          }
        })
        .finally(() => { this.loading = false })
    },
    selectArea (area) {
      this.selectedAreaId = area.id
      this.selectedArea = area
      this.loadServicios()
      this.loadRangos()
      this.loadAreaTipoMuestras()
    },
    nuevaArea () {
      this.areaForm = {
        id: null,
        name: '',
        descripcion: '',
        estado: 'ACTIVO'
      }
      this.editandoArea = false
      this.dialogArea = true
    },
    editarArea (area) {
      this.areaForm = { ...area }
      this.editandoArea = true
      this.dialogArea = true
    },
    guardarArea () {
      this.loading = true
      const req = this.editandoArea
        ? this.$axios.put(`areas/${this.areaForm.id}`, this.areaForm)
        : this.$axios.post('areas', this.areaForm)

      req.then(() => {
        this.$alert?.success?.('Área guardada')
        this.dialogArea = false
        this.loadAreas()
      })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error?.('Error: ' + msg)
        })
        .finally(() => { this.loading = false })
    },

    // --- SERVICIOS ---
    loadServicios () {
      if (!this.selectedAreaId) return
      this.loading = true
      this.$axios.get('servicios', { params: { area_id: this.selectedAreaId } })
        .then(res => {
          this.servicios = res.data
        })
        .finally(() => { this.loading = false })
    },
    nuevoServicio () {
      this.servicioForm = {
        id: null,
        area_id: this.selectedAreaId,
        codigo: null,
        nombre: '',
        metodo: '',
        subarea: '',
        precio: 0,
        estado: 'ACTIVO'
      }
      this.editandoServicio = false
      this.dialogServicio = true
    },
    editarServicio (row) {
      this.servicioForm = {
        ...row,
        subarea: row.subarea || ''
      }
      this.editandoServicio = true
      this.dialogServicio = true
    },
    guardarServicio () {
      this.loading = true
      const payload = { ...this.servicioForm, area_id: this.selectedAreaId }

      const req = this.editandoServicio
        ? this.$axios.put(`servicios/${payload.id}`, payload)
        : this.$axios.post('servicios', payload)

      req.then(() => {
        this.$alert?.success?.('Servicio guardado')
        this.dialogServicio = false
        this.loadServicios()
      })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error?.('Error: ' + msg)
        })
        .finally(() => { this.loading = false })
    },
    abrirVincularServicio (row) {
      this.servicioVincular = row
      this.servicioTiposSeleccionados = (row.tipos_muestra || []).map(tipo => tipo.id)
      this.dialogVincularServicio = true
    },
    guardarVinculoServicio () {
      if (!this.servicioVincular?.id) return

      this.loading = true
      this.$axios.post(`servicios/${this.servicioVincular.id}/tipos-muestra`, {
        area_tipo_muestra_ids: this.servicioTiposSeleccionados
      })
        .then(() => {
          this.$alert?.success?.('Tipos de muestra vinculados')
          this.dialogVincularServicio = false
          this.loadServicios()
        })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error?.('Error: ' + msg)
        })
        .finally(() => { this.loading = false })
    },
    eliminarServicio (row) {
      const confirmar = this.$alert?.dialog
        ? () => this.$alert.dialog('¿Eliminar servicio?').onOk(this._deleteServicio.bind(this, row.id))
        : () => {
          if (confirm('¿Eliminar servicio?')) {
            this._deleteServicio(row.id)
          }
        }

      confirmar()
    },
    _deleteServicio (id) {
      this.$axios.delete(`servicios/${id}`)
        .then(() => {
          this.$alert?.success?.('Servicio eliminado')
          this.loadServicios()
        })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error?.('Error: ' + msg)
        })
    },

    // --- RANGOS ---
    loadRangos () {
      if (!this.selectedAreaId) {
        this.rangos = []
        return
      }
      this.loading = true
      this.$axios.get('area-rangos', { params: { area_id: this.selectedAreaId } })
        .then(res => {
          this.rangos = res.data
        })
        .finally(() => { this.loading = false })
    },
    nuevoRango () {
      this.rangoForm = {
        id: null,
        area_id: this.selectedAreaId,
        rango_nombre: '',
        rango_minimo: null,
        rango_maximo: null,
        unidad: '',
        interpretacion: ''
      }
      this.editandoRango = false
      this.dialogRango = true
    },
    editarRango (row) {
      this.rangoForm = { ...row }
      this.editandoRango = true
      this.dialogRango = true
    },
    guardarRango () {
      this.loading = true
      const payload = {
        ...this.rangoForm,
        area_id: this.selectedAreaId
      }

      const req = this.editandoRango
        ? this.$axios.put(`area-rangos/${payload.id}`, payload)
        : this.$axios.post('area-rangos', payload)

      req.then(() => {
        this.$alert?.success?.('Rango guardado')
        this.dialogRango = false
        this.loadRangos()
      })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error?.('Error: ' + msg)
        })
        .finally(() => { this.loading = false })
    },
    eliminarRango (id) {
      const confirmar = this.$alert?.dialog
        ? () => this.$alert.dialog('¿Eliminar rango?').onOk(this._deleteRango.bind(this, id))
        : () => {
          if (confirm('¿Eliminar rango?')) {
            this._deleteRango(id)
          }
        }

      confirmar()
    },
    _deleteRango (id) {
      this.$axios.delete(`area-rangos/${id}`)
        .then(() => {
          this.$alert?.success?.('Rango eliminado')
          this.loadRangos()
        })
    },

    // --- TIPOS DE MUESTRA ---
    loadAreaTipoMuestras () {
      if (!this.selectedAreaId) {
        this.areaTipoMuestras = []
        return
      }
      this.loading = true
      this.$axios.get('area-tipo-muestras', { params: { area_id: this.selectedAreaId } })
        .then(res => {
          this.areaTipoMuestras = res.data
        })
        .finally(() => { this.loading = false })
    },
    nuevoTipoMuestra () {
      this.tipoMuestraForm = {
        id: null,
        area_id: this.selectedAreaId,
        tipo_muestra: ''
      }
      this.editandoTipoMuestra = false
      this.dialogTipoMuestra = true
    },
    editarTipoMuestra (row) {
      this.tipoMuestraForm = { ...row }
      this.editandoTipoMuestra = true
      this.dialogTipoMuestra = true
    },
    guardarTipoMuestra () {
      this.loading = true
      const payload = {
        ...this.tipoMuestraForm,
        area_id: this.selectedAreaId
      }

      const req = this.editandoTipoMuestra
        ? this.$axios.put(`area-tipo-muestras/${payload.id}`, payload)
        : this.$axios.post('area-tipo-muestras', payload)

      req.then(() => {
        this.$alert?.success?.('Tipo de muestra guardado')
        this.dialogTipoMuestra = false
        this.loadAreaTipoMuestras()
      })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error?.('Error: ' + msg)
        })
        .finally(() => { this.loading = false })
    },
    eliminarTipoMuestra (id) {
      const confirmar = this.$alert?.dialog
        ? () => this.$alert.dialog('¿Eliminar tipo de muestra?').onOk(this._deleteTipoMuestra.bind(this, id))
        : () => {
          if (confirm('¿Eliminar tipo de muestra?')) {
            this._deleteTipoMuestra(id)
          }
        }

      confirmar()
    },
    _deleteTipoMuestra (id) {
      this.$axios.delete(`area-tipo-muestras/${id}`)
        .then(() => {
          this.$alert?.success?.('Tipo de muestra eliminado')
          this.loadAreaTipoMuestras()
        })
    }
  }
}
</script>

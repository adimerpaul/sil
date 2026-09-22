<template>
  <q-page class="ru-page q-pa-sm">

    <!-- LOADING PDF / EXCEL -->
    <q-dialog :model-value="loadingPdf || loadingExcel" persistent>
      <q-card flat class="ru-card q-pa-md text-center" style="min-width:220px">
        <q-spinner-dots :color="loadingPdf ? 'negative' : 'positive'" size="36px" />
        <div class="text-body2 text-weight-bold q-mt-xs">Generando {{ loadingPdf ? 'PDF' : 'Excel' }}...</div>
      </q-card>
    </q-dialog>

    <!-- HEADER -->
    <div class="ru-header row items-center no-wrap q-mb-sm">
      <q-avatar size="34px" class="ru-header-icon" icon="domain" />
      <div class="q-ml-sm col">
        <div class="ru-title">Reporte por Unidad</div>
        <div class="ru-subtitle">Material despachado por producto</div>
      </div>
      <div class="row no-wrap q-gutter-x-xs">
        <q-btn
          unelevated dense no-caps class="ru-btn-export" icon="grid_on" label="Excel"
          :disable="loading || loadingPdf || rows.length === 0" @click="downloadExcel"
        />
        <q-btn
          unelevated dense no-caps class="ru-btn-export" icon="picture_as_pdf" label="PDF"
          :disable="loading || loadingExcel || rows.length === 0" @click="openPdf"
        />
      </div>
    </div>

    <!-- KPIs -->
    <div class="row q-col-gutter-xs q-mb-sm">
      <div v-for="k in kpis" :key="k.label" class="col-4">
        <div class="ru-kpi row items-center no-wrap">
          <div class="ru-kpi-icon" :class="k.cls"><q-icon :name="k.icon" size="18px" /></div>
          <div class="q-ml-sm ellipsis">
            <div class="ru-kpi-label">{{ k.label }}</div>
            <div class="ru-kpi-value">{{ k.value }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- FILTROS -->
    <div class="ru-card q-pa-sm q-mb-sm">
      <div class="row q-col-gutter-xs items-center">
        <div class="col-6 col-sm-3 col-md-auto">
          <q-input v-model="filters.date_from" type="date" dense outlined label="Desde" stack-label class="ru-field" />
        </div>
        <div class="col-6 col-sm-3 col-md-auto">
          <q-input v-model="filters.date_to" type="date" dense outlined label="Hasta" stack-label class="ru-field" />
        </div>
        <div class="col-12 col-sm-6 col-md">
          <q-select
            v-model="filters.unidad_id"
            :options="unidadOptionsFiltradas"
            dense outlined clearable options-dense class="ru-field"
            label="Unidad solicitante"
            emit-value map-options
            use-input input-debounce="200"
            @filter="filterUnidades"
          >
            <template v-slot:prepend><q-icon name="business" size="xs" /></template>
          </q-select>
        </div>
        <div class="col-12 col-sm-6 col-md">
          <q-select
            v-model="filters.personal_recepcion"
            :options="personaOptionsFiltradas"
            dense outlined clearable options-dense class="ru-field"
            label="Persona que retiró"
            emit-value map-options
            use-input input-debounce="200"
            @filter="filterPersonas"
          >
            <template v-slot:prepend><q-icon name="person" size="xs" /></template>
          </q-select>
        </div>
        <div class="col-12 col-sm-6 col-md">
          <q-select
            v-model="filters.almacen_item_ids"
            :options="materialOptionsFiltradas"
            dense outlined clearable options-dense class="ru-field"
            multiple
            label="Material"
            emit-value map-options
            use-input input-debounce="200"
            :display-value="materialDisplay"
            @filter="filterMateriales"
          >
            <template v-slot:prepend><q-icon name="inventory_2" size="xs" /></template>
            <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
              <q-item v-bind="itemProps" dense>
                <q-item-section side>
                  <q-checkbox dense size="xs" :model-value="selected" @update:model-value="toggleOption(opt)" />
                </q-item-section>
                <q-item-section>{{ opt.label }}</q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>
        <div class="col-12 col-sm-6 col-md-auto row no-wrap q-gutter-x-xs">
          <q-btn unelevated dense color="green-8" icon="search" label="Buscar" no-caps class="col ru-btn" :loading="loading" @click="fetchData" />
          <q-btn flat dense round color="grey-7" icon="restart_alt" @click="clearFilters">
            <q-tooltip>Limpiar filtros</q-tooltip>
          </q-btn>
        </div>
      </div>
    </div>

    <!-- TABLA -->
    <div class="ru-card">
      <q-table
        :rows="rows"
        :columns="columns"
        row-key="almacen_item_id"
        dense flat
        class="ru-table"
        :filter="tableFilter"
        :rows-per-page-options="[25, 50, 100, 0]"
        :pagination="{ rowsPerPage: 50 }"
        :loading="loading"
      >
        <template v-slot:top>
          <div class="row items-center full-width">
            <span class="text-body2 text-weight-bold text-grey-9">Material despachado</span>
            <q-badge rounded color="green-1" text-color="green-9" :label="rows.length" class="q-ml-sm" />
            <q-space />
            <q-input v-model="tableFilter" dense outlined placeholder="Buscar en tabla..." class="ru-field" style="width:200px" clearable>
              <template v-slot:prepend><q-icon name="search" size="xs" /></template>
            </q-input>
          </div>
        </template>

        <!-- Imagen -->
        <template v-slot:body-cell-imagen="props">
          <q-td :props="props" style="width:36px;">
            <div class="ru-thumb">
              <img :src="imgUrl(props.row.imagen)" @error="onImgError" />
            </div>
          </q-td>
        </template>

        <!-- Nombre -->
        <template v-slot:body-cell-item_nombre="props">
          <q-td :props="props" class="text-weight-medium text-grey-9">{{ props.row.item_nombre }}</q-td>
        </template>

        <!-- Unidad de medida -->
        <template v-slot:body-cell-unidad_medida="props">
          <q-td :props="props"><span class="ru-tag">{{ props.row.unidad_medida }}</span></q-td>
        </template>

        <!-- Cantidad -->
        <template v-slot:body-cell-cantidad_total="props">
          <q-td :props="props" class="text-weight-bold text-grey-9">{{ props.row.cantidad_total }}</q-td>
        </template>

        <!-- Precio promedio -->
        <template v-slot:body-cell-precio_promedio="props">
          <q-td :props="props" class="text-grey-7">{{ money(props.row.precio_promedio) }}</q-td>
        </template>

        <!-- Total Bs -->
        <template v-slot:body-cell-total_monto="props">
          <q-td :props="props" class="text-weight-bold text-green-8">{{ money(props.row.total_monto) }}</q-td>
        </template>

        <!-- Personas -->
        <template v-slot:body-cell-personas_recepcion="props">
          <q-td :props="props" class="ru-personas text-grey-7">
            <template v-if="props.row.personas_recepcion">
              {{ props.row.personas_recepcion }}
              <q-tooltip max-width="400px">{{ props.row.personas_recepcion }}</q-tooltip>
            </template>
            <span v-else class="text-grey-4">—</span>
          </q-td>
        </template>

        <template v-slot:no-data>
          <div class="full-width column flex-center q-pa-lg text-grey-5">
            <q-icon name="inventory_2" size="32px" />
            <span class="text-caption q-mt-xs">Sin resultados. Selecciona los filtros y presiona Buscar.</span>
          </div>
        </template>
      </q-table>
    </div>

  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'ReporteUnidadPage',

  data () {
    return {
      loading: false,
      loadingPdf: false,
      loadingExcel: false,
      tableFilter: '',

      filters: {
        date_from: '',
        date_to: '',
        unidad_id: null,
        personal_recepcion: null,
        almacen_item_ids: []
      },

      rows: [],
      summary: { total_items: 0, total_cantidad: 0, total_monto: 0 },

      unidades: [],
      unidadOptionsFiltradas: [],
      personas: [],
      personaOptionsFiltradas: [],
      materiales: [],
      materialOptionsFiltradas: [],

      columns: [
        { name: 'imagen',         label: '',               field: 'imagen',         align: 'center' },
        { name: 'item_nombre',    label: 'Producto',       field: 'item_nombre',    align: 'left',  sortable: true },
        { name: 'unidad_medida',  label: 'Unidad',         field: 'unidad_medida',  align: 'center', sortable: true },
        { name: 'cantidad_total', label: 'Cantidad',       field: 'cantidad_total', align: 'right', sortable: true },
        { name: 'precio_promedio', label: 'P. Unit. Bs',  field: 'precio_promedio', align: 'right', sortable: true },
        { name: 'total_monto',       label: 'Total Bs',            field: 'total_monto',       align: 'right', sortable: true },
        { name: 'personas_recepcion', label: 'Personas que retiraron', field: 'personas_recepcion', align: 'left',  sortable: true }
      ]
    }
  },

  computed: {
    unidadOptions () {
      return this.unidades.map(u => ({ label: u.nombre, value: u.id }))
    },
    materialOptions () {
      return this.materiales.map(m => ({ label: m.nombre, value: m.id }))
    },
    materialDisplay () {
      const ids = this.filters.almacen_item_ids || []
      if (!ids.length) return ''
      if (ids.length === 1) return this.materiales.find(m => m.id === ids[0])?.nombre || '1 material'
      return `${ids.length} materiales`
    },
    kpis () {
      return [
        { label: 'Productos', value: this.summary.total_items, icon: 'category', cls: 'k-blue' },
        { label: 'Cantidad', value: this.summary.total_cantidad, icon: 'inventory', cls: 'k-amber' },
        { label: 'Total', value: this.money(this.summary.total_monto), icon: 'payments', cls: 'k-green' }
      ]
    }
  },

  async mounted () {
    const hoy = moment().format('YYYY-MM-DD')
    this.filters.date_from = moment().startOf('month').format('YYYY-MM-DD')
    this.filters.date_to   = hoy

    await Promise.all([this.loadUnidades(), this.loadPersonas(), this.loadMateriales()])
  },

  methods: {
    money (n) {
      return parseFloat(n || 0).toFixed(2) + ' Bs'
    },

    imgUrl (imagen) {
      if (!imagen) return `${this.$url}/../images/productos/default.png`
      return `${this.$url}/../images/productos/${imagen}`
    },

    onImgError (e) {
      e.target.src = `${this.$url}/../images/productos/default.png`
    },

    async loadUnidades () {
      try {
        const res = await this.$axios.get('reportes/almacen-unidad/unidades')
        this.unidades = res.data || []
        this.unidadOptionsFiltradas = this.unidadOptions
      } catch {}
    },

    async loadPersonas () {
      try {
        const res = await this.$axios.get('reportes/almacen-unidad/personas')
        this.personas = res.data || []
        this.personaOptionsFiltradas = this.personas.map(p => ({ label: p, value: p }))
      } catch {}
    },

    async loadMateriales () {
      try {
        const res = await this.$axios.get('reportes/almacen-unidad/materiales')
        this.materiales = res.data || []
        this.materialOptionsFiltradas = this.materialOptions
      } catch {}
    },

    filterMateriales (val, update) {
      const opts = this.materialOptions
      if (!val) { update(() => { this.materialOptionsFiltradas = opts }); return }
      const needle = val.toLowerCase()
      update(() => { this.materialOptionsFiltradas = opts.filter(o => (o.label || '').toLowerCase().includes(needle)) })
    },

    filterPersonas (val, update) {
      const opts = this.personas.map(p => ({ label: p, value: p }))
      if (!val) { update(() => { this.personaOptionsFiltradas = opts }); return }
      const needle = val.toLowerCase()
      update(() => { this.personaOptionsFiltradas = opts.filter(o => (o.label || '').toLowerCase().includes(needle)) })
    },

    filterUnidades (val, update) {
      const opts = this.unidadOptions
      if (!val) { update(() => { this.unidadOptionsFiltradas = opts }); return }
      const needle = val.toLowerCase()
      update(() => { this.unidadOptionsFiltradas = opts.filter(o => (o.label || '').toLowerCase().includes(needle)) })
    },

    async fetchData () {
      this.loading = true
      try {
        const res  = await this.$axios.get('reportes/almacen-unidad', { params: this.buildParams() })
        this.rows    = res.data?.rows    || []
        this.summary = res.data?.summary || { total_items: 0, total_cantidad: 0, total_monto: 0 }
      } catch (e) {
        this.$q?.notify({ type: 'negative', message: e.response?.data?.message || 'Error cargando el reporte' })
      } finally {
        this.loading = false
      }
    },

    clearFilters () {
      this.filters = {
        date_from:   moment().startOf('month').format('YYYY-MM-DD'),
        date_to:     moment().format('YYYY-MM-DD'),
        unidad_id: null,
        personal_recepcion: null,
        almacen_item_ids: []
      }
    },

    buildParams () {
      const p = {}
      if (this.filters.date_from)   p.date_from   = this.filters.date_from
      if (this.filters.date_to)     p.date_to     = this.filters.date_to
      if (this.filters.unidad_id)          p.unidad_id          = this.filters.unidad_id
      if (this.filters.personal_recepcion) p.personal_recepcion = this.filters.personal_recepcion
      if (this.filters.almacen_item_ids?.length) p.almacen_item_ids = this.filters.almacen_item_ids
      return p
    },

    async downloadExcel () {
      this.loadingExcel = true
      try {
        const res = await this.$axios.get('reportes/almacen-unidad/excel', {
          params: this.buildParams(),
          responseType: 'blob',
          timeout: 120000
        })
        const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        const url  = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href     = url
        link.download = `reporte_unidad_${this.filters.date_from}_${this.filters.date_to}.xlsx`
        link.click()
        window.URL.revokeObjectURL(url)
      } catch {
        this.$q?.notify({ type: 'negative', message: 'Error generando Excel' })
      } finally {
        this.loadingExcel = false
      }
    },

    async openPdf () {
      this.loadingPdf = true
      try {
        const res = await this.$axios.get('reportes/almacen-unidad/pdf', {
          params: this.buildParams(),
          responseType: 'blob',
          timeout: 180000
        })
        const blob = new Blob([res.data], { type: 'application/pdf' })
        window.open(window.URL.createObjectURL(blob), '_blank')
      } catch {
        this.$q?.notify({ type: 'negative', message: 'Error generando PDF' })
      } finally {
        this.loadingPdf = false
      }
    }
  }
}
</script>

<style scoped>
.ru-page {
  background: #f4f6f8;
}

.ru-header {
  background: linear-gradient(120deg, #1b5e20 0%, #2e7d32 55%, #43a047 100%);
  border-radius: 12px;
  padding: 8px 12px;
  color: #fff;
  box-shadow: 0 4px 14px rgba(27, 94, 32, .18);
}
.ru-header-icon {
  background: rgba(255, 255, 255, .18);
  color: #fff;
}
.ru-title {
  font-size: 16px;
  font-weight: 700;
  line-height: 1.2;
}
.ru-subtitle {
  font-size: 11px;
  opacity: .8;
}
.ru-btn-export {
  background: rgba(255, 255, 255, .16);
  color: #fff;
  border-radius: 8px;
  padding: 0 10px;
  font-size: 12px;
}
.ru-btn-export:hover {
  background: rgba(255, 255, 255, .28);
}

.ru-card {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e6e9ed;
  box-shadow: 0 1px 3px rgba(16, 24, 40, .05);
  overflow: hidden;
}

.ru-kpi {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e6e9ed;
  padding: 6px 10px;
  box-shadow: 0 1px 3px rgba(16, 24, 40, .05);
}
.ru-kpi-icon {
  width: 32px;
  height: 32px;
  min-width: 32px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.ru-kpi-icon.k-green  { background: #e8f5e9; color: #2e7d32; }
.ru-kpi-icon.k-blue   { background: #e3f2fd; color: #1565c0; }
.ru-kpi-icon.k-amber  { background: #fff8e1; color: #ef6c00; }
.ru-kpi-label {
  font-size: 11px;
  color: #6b7280;
  line-height: 1.1;
}
.ru-kpi-value {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  line-height: 1.3;
}

.ru-field :deep(.q-field__control) {
  border-radius: 8px;
}
.ru-btn {
  border-radius: 8px;
}

.ru-thumb {
  width: 26px;
  height: 26px;
  border-radius: 6px;
  background: #f4f6f8;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}
.ru-thumb img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.ru-tag {
  font-size: 11px;
  padding: 1px 8px;
  border-radius: 10px;
  background: #f1f3f5;
  color: #4b5563;
}

.ru-personas {
  max-width: 280px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 12px;
}

.ru-table :deep(.q-table__top) {
  padding: 6px 10px;
}
.ru-table :deep(thead tr th) {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .03em;
  color: #6b7280;
  background: #fafbfc;
  position: sticky;
  top: 0;
  z-index: 1;
}
.ru-table :deep(tbody td) {
  height: 30px;
  font-size: 12.5px;
  padding-top: 2px;
  padding-bottom: 2px;
}
.ru-table :deep(tbody tr:nth-child(even)) {
  background: #fafbfc;
}
.ru-table :deep(tbody tr:hover) {
  background: #f1f8e9;
}
</style>

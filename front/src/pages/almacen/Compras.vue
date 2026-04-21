<template>
  <q-page class="q-pa-sm bg-grey-2">
    <div class="row q-col-gutter-sm q-mb-sm">
      <div class="col-12 col-md-4">
        <q-card flat class="summary summary--green">
          <q-card-section>
            <div class="text-caption">Compras Totales</div>
            <div class="text-h5">{{ money(summary.total_compras) }} Bs</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
        <q-card flat class="summary summary--red">
          <q-card-section>
            <div class="text-caption">Compras Anuladas</div>
            <div class="text-h5">{{ money(summary.total_anuladas) }} Bs</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
        <q-card flat class="summary summary--blue">
          <q-card-section>
            <div class="text-caption">Total Compras</div>
            <div class="text-h5">{{ summary.cantidad }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-card flat bordered>
      <q-card-section class="row q-col-gutter-sm items-center q-pa-sm">
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.date_from" dense outlined type="date" label="Fecha inicio" />
        </div>
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.date_to" dense outlined type="date" label="Fecha fin" />
        </div>
        <div class="col-12 col-sm-2">
          <q-select v-model="filters.estado" dense outlined clearable emit-value map-options label="Estado" :options="estadoOptions" />
        </div>
        <div class="col-12 col-sm">
          <q-input v-model="filters.q" dense outlined clearable label="Buscar proveedor/factura" />
        </div>
        <div class="col-auto">
          <q-btn color="primary" icon="search" label="Buscar" no-caps :loading="loading" @click="fetchRows" />
        </div>
        <div class="col-auto">
          <q-btn color="positive" icon="add_circle" label="Compra nueva" no-caps to="/almacen/compras/nueva" />
        </div>
      </q-card-section>

      <q-table
        v-model:pagination="pagination"
        dense
        flat
        bordered
        row-key="id"
        :rows="rows"
        :columns="columns"
        :loading="loading"
        :rows-per-page-options="[10, 15, 25, 50]"
        @request="onRequest"
      >
        <template #body-cell-actions="props">
          <q-td :props="props">
            <q-btn-dropdown dense color="primary" label="Opciones" no-caps size="sm">
              <q-list dense>
                <q-item clickable v-close-popup @click="openDetail(props.row)">
                  <q-item-section avatar><q-icon name="visibility" /></q-item-section>
                  <q-item-section>Ver detalle</q-item-section>
                </q-item>
                <q-item clickable v-close-popup :disable="props.row.estado === 'ANULADO'" @click="anular(props.row)">
                  <q-item-section avatar><q-icon name="cancel" color="negative" /></q-item-section>
                  <q-item-section class="text-negative">Anular</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </q-td>
        </template>
        <template #body-cell-total="props">
          <q-td :props="props">{{ money(props.row.total) }} Bs</q-td>
        </template>
        <template #body-cell-estado="props">
          <q-td :props="props">
            <q-badge :color="props.row.estado === 'ACTIVO' ? 'green' : 'red'">{{ props.row.estado }}</q-badge>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <q-dialog v-model="detailDialog" maximized>
      <q-card>
        <q-card-section class="row items-center q-py-sm">
          <div class="text-subtitle1 text-weight-bold">Detalle compra #{{ selected?.id }}</div>
          <q-space />
          <q-btn flat round dense icon="close" @click="detailDialog = false" />
        </q-card-section>
        <q-separator />
        <q-card-section v-if="selected">
          <div class="row q-col-gutter-sm q-mb-sm">
            <div class="col-12 col-md-3"><b>Proveedor:</b> {{ selected.proveedor?.nombre || selected.nombre || '-' }}</div>
            <div class="col-12 col-md-2"><b>Tipo:</b> {{ selected.tipo_registro }}</div>
            <div class="col-12 col-md-2"><b>Motivo:</b> {{ selected.motivo_registro }}</div>
            <div class="col-12 col-md-2"><b>Factura:</b> {{ selected.nro_factura || '-' }}</div>
            <div class="col-12 col-md-3"><b>Total:</b> {{ money(selected.total) }} Bs</div>
          </div>
          <q-table dense flat bordered :rows="selected.detalles || []" :columns="detailColumns" row-key="id" :rows-per-page-options="[20, 50, 0]" />
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'ComprasPage',
  data () {
    return {
      loading: false,
      rows: [],
      summary: { total_compras: 0, total_anuladas: 0, cantidad: 0 },
      selected: null,
      detailDialog: false,
      filters: {
        date_from: moment().format('YYYY-MM-DD'),
        date_to: moment().format('YYYY-MM-DD'),
        estado: null,
        q: '',
      },
      pagination: { page: 1, rowsPerPage: 15, rowsNumber: 0 },
      estadoOptions: [
        { label: 'Activo', value: 'ACTIVO' },
        { label: 'Anulado', value: 'ANULADO' },
      ],
      columns: [
        { name: 'actions', label: 'Acciones', field: 'id', align: 'left' },
        { name: 'id', label: 'ID', field: 'id', align: 'left' },
        { name: 'fecha_hora', label: 'Fecha', field: 'fecha_hora', align: 'left' },
        { name: 'tipo_registro', label: 'Tipo', field: 'tipo_registro', align: 'left' },
        { name: 'motivo_registro', label: 'Motivo', field: 'motivo_registro', align: 'left' },
        { name: 'proveedor', label: 'Proveedor', field: row => row.proveedor?.nombre || row.nombre || '-', align: 'left' },
        { name: 'user', label: 'Usuario', field: row => row.user?.name || '-', align: 'left' },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left' },
        { name: 'total', label: 'Total', field: 'total', align: 'right' },
        { name: 'tipo_pago', label: 'Pago', field: 'tipo_pago', align: 'left' },
      ],
      detailColumns: [
        { name: 'nombre', label: 'Producto', field: 'nombre', align: 'left' },
        { name: 'cantidad', label: 'Cantidad', field: 'cantidad', align: 'right' },
        { name: 'precio', label: 'Precio', field: 'precio', align: 'right' },
        { name: 'total', label: 'Total', field: 'total', align: 'right' },
        { name: 'lote', label: 'Lote', field: 'lote', align: 'left' },
        { name: 'fecha_vencimiento', label: 'Vence', field: 'fecha_vencimiento', align: 'left' },
      ],
    }
  },
  mounted () {
    this.fetchRows()
  },
  methods: {
    async fetchRows () {
      this.loading = true
      try {
        const res = await this.$axios.get('compras', {
          params: {
            page: this.pagination.page,
            rowsPerPage: this.pagination.rowsPerPage,
            ...this.filters,
          },
        })
        this.rows = res.data.data || []
        this.pagination.rowsNumber = res.data.total || 0
        this.summary = res.data.summary || this.summary
      } finally {
        this.loading = false
      }
    },
    onRequest (props) {
      this.pagination = props.pagination
      this.fetchRows()
    },
    async openDetail (row) {
      const res = await this.$axios.get(`compras/${row.id}`)
      this.selected = res.data
      this.detailDialog = true
    },
    anular (row) {
      this.$alert.dialog(`Desea anular la compra #${row.id}?`).onOk(async () => {
        await this.$axios.delete(`compras/${row.id}`)
        this.$alert.success('Compra anulada')
        await this.fetchRows()
      })
    },
    money (value) {
      return Number(value || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
  },
}
</script>

<style scoped>
.summary {
  color: white;
  min-height: 74px;
}

.summary--green { background: #43a047; }
.summary--red { background: #c90022; }
.summary--blue { background: #3949ab; }
</style>

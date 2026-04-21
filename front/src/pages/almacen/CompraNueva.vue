<template>
  <q-page class="q-pa-sm bg-grey-2">
    <div class="row items-center q-mb-sm">
      <div>
        <div class="text-h6 text-weight-bold">Compras nuevas</div>
        <div class="text-caption text-grey-7">Registro de entradas de almacén</div>
      </div>
      <q-space />
      <q-btn flat icon="arrow_back" label="Volver" no-caps to="/almacen/compras" />
    </div>

    <div class="row q-col-gutter-sm">
      <!-- Carrusel de Productos -->
      <div class="col-12 col-md-5">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <q-input v-model="productFilter" dense outlined clearable debounce="350" label="Buscar producto" @update:model-value="fetchProducts">
              <template #prepend><q-icon name="search" /></template>
            </q-input>
          </q-card-section>
          <q-separator />
          <q-card-section class="product-grid q-pa-sm">
            <div v-for="item in products" :key="item.id" class="product-card" @click="addItem(item)">
              <q-img :src="itemImageUrl(item)" class="product-image" fit="cover" />
              <div class="product-info">
                <div class="product-name">{{ item.nombre }}</div>
                <div class="product-qty">{{ item.unidad_medida || '-' }}</div>
                <div class="product-price">{{ money(item.precio_unitario) }} Bs</div>
              </div>
            </div>
          </q-card-section>
          <q-card-section class="row justify-center q-pa-xs">
            <q-pagination v-model="productPagination.page" :max="productPages" max-pages="6" size="sm" @update:model-value="fetchProducts" />
          </q-card-section>
        </q-card>
      </div>

  <!-- Formulario y Tabla -->
      <div class="col-12 col-md-7">
        <q-card flat bordered>
          <!-- Header: Motivo, Fecha, Pago -->
          <q-card-section class="q-pa-xs">
            <div class="row q-col-gutter-xs">
              <div class="col-12 col-sm-4">
                <q-select v-model="form.motivo_registro" dense outlined emit-value map-options label="Motivo" :options="motivoOptions" />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model="form.fecha_hora" dense outlined type="datetime-local" label="Fecha y hora" />
              </div>
              <div class="col-12 col-sm-4">
                <q-select v-model="form.tipo_pago" dense outlined emit-value map-options label="Pago" :options="pagoOptions" />
              </div>
            </div>
          </q-card-section>

          <!-- Proveedor y Factura -->
          <q-card-section class="q-pa-xs">
            <div class="row q-col-gutter-xs items-end">
              <div class="col-12 col-sm-8">
                <q-select 
                  v-model="form.proveedor_id" 
                  dense outlined 
                  emit-value 
                  map-options 
                  use-input
                  clearable
                  label="Proveedor" 
                  :options="proveedorOptions" 
                  @update:model-value="onProveedorChange"
                >
                  <template #append>
                    <q-btn flat dense icon="add" color="primary" size="sm" @click="showProveedorDialog = true" title="Agregar proveedor" />
                  </template>
                </q-select>
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model="form.nro_factura" dense outlined label="Nro factura" />
              </div>
            </div>
          </q-card-section>

          <q-separator />

          <!-- Tabla de Productos -->
          <div class="table-container q-pa-sm">
            <table class="items-table">
              <thead>
                <tr>
                  <th style="width: 28%">Producto</th>
                  <th style="width: 8%">Cant.</th>
                  <th style="width: 8%">P. Unit.</th>
                  <th style="width: 10%">Total</th>
                  <th style="width: 10%">Lote</th>
                  <th style="width: 12%">Vencimiento</th>
                  <th style="width: 6%">Días</th>
                  <th style="width: 6%">Act.</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in selectedItems" :key="item.producto_id">
                  <td class="producto-cell" :title="item.nombre">{{ item.nombre }}</td>
                  <td><input v-model.number="item.cantidad" type="number" min="0" class="input-inline" @change="recalculate(item)" /></td>
                  <td><input v-model.number="item.precio" type="number" step="0.01" class="input-inline" @change="recalculate(item)" /></td>
                  <td><input v-model.number="item.total" type="number" step="0.01" class="input-inline" @change="recalculatePrice(item)" /></td>
                  <td><input v-model="item.lote" type="text" class="input-inline" /></td>
                  <td><input v-model="item.fecha_vencimiento" type="date" class="input-inline" @change="updateDaysLeft(item)" /></td>
                  <td class="dias-cell" :class="getDaysClass(item.dias_restantes)">{{ item.dias_restantes ?? '-' }}</td>
                  <td class="text-center"><q-btn flat dense round icon="delete" color="negative" size="sm" @click="removeItem(item)" /></td>
                </tr>
                <tr v-if="selectedItems.length === 0">
                  <td colspan="8" class="text-center text-grey-7">0 de 0 productos</td>
                </tr>
                <tr v-else>
                  <td colspan="8" class="text-center text-grey-7">0 de {{ selectedItems.length }} productos</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Total y Botón -->
          <q-card-section class="row items-center q-pa-sm bg-blue-1">
            <div class="text-subtitle2 text-weight-bold">Total: <span class="text-primary text-h6">{{ money(total) }} Bs</span></div>
            <q-space />
            <q-btn color="primary" icon="add_circle" label="Registrar compra" no-caps :loading="saving" :disable="selectedItems.length === 0" @click="confirmSave" />
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Diálogo: Agregar Proveedor -->
    <q-dialog v-model="showProveedorDialog" @hide="newProveedor = {}">
      <q-card style="width: 500px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Agregar nuevo proveedor</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="showProveedorDialog = false" />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-gutter-md">
          <q-input v-model="newProveedor.nombre" outlined label="Nombre *" />
          <q-input v-model="newProveedor.carnet" outlined label="Carnet / NIT" />
          <q-input v-model="newProveedor.razon_social" outlined label="Razón social" />
          <q-input v-model="newProveedor.telefono" outlined label="Teléfono" />
          <q-input v-model="newProveedor.email" outlined label="Email" type="email" />
        </q-card-section>

        <q-separator />

        <q-card-section class="row q-gutter-sm">
          <q-space />
          <q-btn label="Cancelar" flat @click="showProveedorDialog = false" />
          <q-btn label="Crear proveedor" color="primary" :loading="savingProveedor" @click="saveProveedor" />
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Diálogo: Confirmación -->
    <q-dialog v-model="showConfirmDialog" @hide="confirmData = null">
      <q-card style="width: 650px; max-width: 90vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Confirmar registro de compra</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="showConfirmDialog = false" />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-gutter-lg">
          <div class="row q-col-gutter-lg">
            <div class="col-6">
              <div class="text-caption text-grey-7">Fecha y hora</div>
              <div class="text-subtitle2 text-weight-bold">{{ formatDateTime(confirmData?.fecha_hora) }}</div>
            </div>
            <div class="col-6">
              <div class="text-caption text-grey-7">Proveedor</div>
              <div class="text-subtitle2 text-weight-bold">{{ confirmData?.proveedor_nombre || 'Sin proveedor' }}</div>
            </div>
            <div class="col-6">
              <div class="text-caption text-grey-7">Motivo</div>
              <div class="text-subtitle2 text-weight-bold">{{ confirmData?.motivo_registro }}</div>
            </div>
            <div class="col-6">
              <div class="text-caption text-grey-7">Tipo de pago</div>
              <div class="text-subtitle2 text-weight-bold">{{ confirmData?.tipo_pago || 'Ninguno' }}</div>
            </div>
          </div>

          <q-separator />

          <div>
            <div class="text-caption text-grey-7 q-mb-md">Productos ({{ selectedItems.length }})</div>
            <div class="confirm-items">
              <div v-for="(item, idx) in selectedItems" :key="idx" class="confirm-item">
                <span>{{ item.nombre }}</span>
                <span class="text-grey">{{ item.cantidad }}x @ {{ money(item.precio) }} = <strong>{{ money(item.total) }}</strong></span>
              </div>
            </div>
          </div>

          <q-separator />

          <div class="row items-center justify-between">
            <div class="text-subtitle1 text-weight-bold">Total a registrar</div>
            <div class="text-h6 text-primary">{{ money(total) }} Bs</div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-section class="row q-gutter-md q-pt-md">
          <q-space />
          <q-btn label="Cancelar" flat @click="showConfirmDialog = false" />
          <q-btn label="Confirmar registro" color="primary" :loading="saving" @click="save" />
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'CompraNuevaPage',
  data () {
    return {
      loadingProducts: false,
      saving: false,
      savingProveedor: false,
      products: [],
      proveedores: [],
      proveedorOptions: [],
      selectedItems: [],
      productFilter: '',
      productPagination: { page: 1, rowsPerPage: 30, rowsNumber: 0 },
      showProveedorDialog: false,
      showConfirmDialog: false,
      newProveedor: {},
      confirmData: null,
      form: {
        proveedor_id: null,
        fecha_hora: moment().format('YYYY-MM-DDTHH:mm'),
        tipo_registro: 'ENTRADA',
        motivo_registro: 'COMPRA',
        tipo_pago: '',
        nro_factura: '',
        carnet: '',
        nombre: '',
      },
      entradaMotivos: ['COMPRA', 'DONACION', 'TRANSFERENCIA', 'JUSTO'],
      pagoOptions: [
        { label: 'Ninguno', value: '' },
        { label: 'Efectivo', value: 'EFECTIVO' },
        { label: 'Crédito', value: 'CREDITO' },
        { label: 'Transferencia', value: 'TRANSFERENCIA' },
        { label: 'QR', value: 'QR' },
      ],
    }
  },
  computed: {
    total () {
      return this.selectedItems.reduce((sum, item) => sum + Number(item.total || 0), 0)
    },
    productPages () {
      return Math.max(1, Math.ceil(this.productPagination.rowsNumber / this.productPagination.rowsPerPage))
    },
    motivoOptions () {
      return this.entradaMotivos.map(value => ({ label: value, value }))
    },
  },
  mounted () {
    this.fetchProducts()
    this.fetchProveedores()
  },
  methods: {
    async fetchProducts () {
      const res = await this.$axios.get('almacen-items', {
        params: {
          page: this.productPagination.page,
          rowsPerPage: this.productPagination.rowsPerPage,
          q: this.productFilter,
        },
      })
      this.products = res.data.data || []
      this.productPagination.rowsNumber = res.data.total || 0
    },
    async fetchProveedores () {
      const res = await this.$axios.get('proveedores')
      this.proveedores = res.data || []
      this.updateProveedorOptions()
    },
    updateProveedorOptions () {
      this.proveedorOptions = this.proveedores.map(p => ({ label: p.nombre, value: p.id }))
    },
    onProveedorChange () {
      const proveedor = this.proveedores.find(p => p.id === this.form.proveedor_id)
      if (proveedor) {
        this.form.carnet = proveedor?.carnet || proveedor?.nit || ''
        this.form.nombre = proveedor?.nombre || ''
      }
    },
    async saveProveedor () {
      if (!this.newProveedor.nombre?.trim()) {
        this.$alert.warning('El nombre es requerido')
        return
      }
      this.savingProveedor = true
      try {
        const res = await this.$axios.post('proveedores', this.newProveedor)
        this.proveedores.push(res.data)
        this.updateProveedorOptions()
        this.form.proveedor_id = res.data.id
        this.onProveedorChange()
        this.$alert.success('Proveedor creado')
        this.showProveedorDialog = false
        this.newProveedor = {}
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error al crear proveedor')
      } finally {
        this.savingProveedor = false
      }
    },
    addItem (product) {
      const current = this.selectedItems.find(item => item.producto_id === product.id)
      if (current) {
        current.cantidad += 1
        this.recalculate(current)
        return
      }
      const item = {
        producto_id: product.id,
        imagen: product.imagen,
        nombre: product.nombre,
        unidad_medida: product.unidad_medida,
        cantidad: 1,
        precio: Number(product.precio_unitario || 0),
        total: Number(product.precio_unitario || 0),
        factor: 1.25,
        precio_venta: Number(product.precio_unitario || 0) * 1.25,
        lote: '',
        fecha_vencimiento: '',
        dias_restantes: null,
      }
      this.selectedItems.push(item)
    },
    recalculate (item) {
      item.total = Number(item.cantidad || 0) * Number(item.precio || 0)
      item.precio_venta = Number(item.precio || 0) * Number(item.factor || 1)
    },
    recalculatePrice (item) {
      const cantidad = Number(item.cantidad || 1)
      if (cantidad > 0) {
        item.precio = Number(item.total || 0) / cantidad
      }
    },
    updateDaysLeft (item) {
      if (!item.fecha_vencimiento) {
        item.dias_restantes = null
        return
      }
      const today = moment().startOf('day')
      const vencimiento = moment(item.fecha_vencimiento).startOf('day')
      item.dias_restantes = vencimiento.diff(today, 'days')
    },
    getDaysClass (dias) {
      if (dias === null) return ''
      if (dias < 0) return 'dias-vencido'
      if (dias === 0) return 'dias-hoy'
      if (dias <= 7) return 'dias-proximo'
      return 'dias-ok'
    },
    removeItem (item) {
      this.selectedItems = this.selectedItems.filter(row => row.producto_id !== item.producto_id)
    },
    confirmSave () {
      const proveedor = this.proveedores.find(p => p.id === this.form.proveedor_id)
      this.confirmData = {
        fecha_hora: this.form.fecha_hora,
        proveedor_nombre: proveedor?.nombre,
        motivo_registro: this.form.motivo_registro,
        tipo_pago: this.form.tipo_pago,
      }
      this.showConfirmDialog = true
    },
    async save () {
      this.saving = true
      try {
        await this.$axios.post('compras', {
          ...this.form,
          fecha_hora: this.form.fecha_hora ? this.form.fecha_hora.replace('T', ' ') : null,
          items: this.selectedItems,
        })
        this.$alert.success('Compra registrada correctamente')
        this.$router.push('/almacen/compras')
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo registrar')
      } finally {
        this.saving = false
        this.showConfirmDialog = false
      }
    },
    itemImageUrl (row) {
      return `${this.$url}/../images/productos/${row?.imagen || 'default.png'}`
    },
    money (value) {
      return Number(value || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    formatDateTime (datetime) {
      if (!datetime) return '-'
      return moment(datetime).format('DD/MM/YYYY HH:mm')
    },
  },
}
</script>

<style scoped>
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
  gap: 6px;
  max-height: calc(100vh - 230px);
  overflow: auto;
}

.product-card {
  position: relative;
  min-height: 130px;
  border: 1px solid #d8e0e8;
  cursor: pointer;
  overflow: hidden;
  background: #fff;
  transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.product-image {
  height: 70px;
  background: #f5f5f5;
}

.product-info {
  padding: 6px;
  display: flex;
  flex-direction: column;
  gap: 3px;
  height: 60px;
  justify-content: space-between;
}

.product-name {
  font-size: 11px;
  font-weight: 600;
  line-height: 1.1;
  color: #333;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.product-qty {
  font-size: 10px;
  color: #999;
}

.product-price {
  font-size: 12px;
  font-weight: 700;
  color: #1976d2;
}

/* Tabla de productos */
.table-container {
  overflow-x: auto;
  background: white;
  border-radius: 3px;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}

.items-table thead {
  background: #f5f5f5;
  border-bottom: 2px solid #ddd;
  position: sticky;
  top: 0;
}

.items-table th {
  padding: 6px 4px;
  text-align: left;
  font-weight: 600;
  color: #333;
  white-space: nowrap;
  font-size: 11px;
}

.items-table td {
  padding: 4px 2px;
  border-bottom: 1px solid #eee;
  vertical-align: middle;
}

.items-table tbody tr:hover {
  background: #f9f9f9;
}

.producto-cell {
  max-width: 180px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}

/* Columna Días */
.dias-cell {
  text-align: center;
  font-weight: 600;
  font-size: 11px;
  padding: 2px 4px;
  border-radius: 2px;
}

.dias-ok {
  color: #4caf50;
}

.dias-proximo {
  color: #ff9800;
  background: #fff3e0;
}

.dias-hoy {
  color: #f44336;
  background: #ffebee;
}

.dias-vencido {
  color: #c62828;
  background: #ffcdd2;
}

/* Inputs normales en tabla */
.input-inline {
  width: 100%;
  padding: 2px 3px;
  font-size: 11px;
  border: 1px solid #ddd;
  border-radius: 2px;
  text-align: center;
  font-family: inherit;
}

.input-inline:focus {
  outline: none;
  border-color: #1976d2;
  background: #f0f8ff;
}

.input-inline[type="text"] {
  text-align: left;
}

.bg-blue-1 {
  background: #e3f2fd;
  border-radius: 3px;
}

.confirm-items {
  border: 1px solid #eee;
  border-radius: 3px;
  background: #fafafa;
  padding: 8px;
  max-height: 200px;
  overflow-y: auto;
}

.confirm-item {
  display: flex;
  justify-content: space-between;
  padding: 6px 4px;
  border-bottom: 1px solid #eee;
  font-size: 13px;
}

.confirm-item:last-child {
  border-bottom: none;
}
</style>

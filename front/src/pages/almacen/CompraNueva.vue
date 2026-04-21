<template>
  <q-page class="q-pa-sm bg-grey-2">
    <div class="row items-center q-mb-sm">
      <div>
        <div class="text-h6 text-weight-bold">Compras nuevas</div>
        <div class="text-caption text-grey-7">Registro de entradas y salidas de almacen</div>
      </div>
      <q-space />
      <q-btn flat icon="arrow_back" label="Volver" no-caps to="/almacen/compras" />
    </div>

    <div class="row q-col-gutter-sm">
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
              <div class="product-name">{{ item.nombre }}</div>
              <div class="product-foot">
                <span>{{ item.unidad_medida || '-' }}</span>
                <b>{{ money(item.precio_unitario) }} Bs</b>
              </div>
            </div>
          </q-card-section>
          <q-card-section class="row justify-center q-pa-xs">
            <q-pagination v-model="productPagination.page" :max="productPages" max-pages="6" size="sm" @update:model-value="fetchProducts" />
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-7">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <div class="row q-col-gutter-sm">
              <div class="col-12 col-sm-4">
                <q-select v-model="form.tipo_registro" dense outlined emit-value map-options label="Registro" :options="tipoOptions" @update:model-value="syncMotivo" />
              </div>
              <div class="col-12 col-sm-4">
                <q-select v-model="form.motivo_registro" dense outlined emit-value map-options label="Motivo" :options="motivoOptions" />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model="form.fecha_hora" dense outlined type="datetime-local" label="Fecha y hora" />
              </div>
              <div class="col-12 col-sm-6">
                <q-select v-model="form.proveedor_id" dense outlined clearable use-input input-debounce="0" emit-value map-options label="Proveedor" :options="proveedorOptions" @filter="filterProveedores" @update:model-value="onProveedorChange" />
              </div>
              <div class="col-12 col-sm-3">
                <q-input v-model="form.nro_factura" dense outlined label="Nro factura" />
              </div>
              <div class="col-12 col-sm-3">
                <q-select v-model="form.tipo_pago" dense outlined emit-value map-options label="Pago" :options="pagoOptions" />
              </div>
            </div>
          </q-card-section>

          <q-table dense flat bordered title="Productos seleccionados" :rows="selectedItems" :columns="columns" row-key="producto_id" :rows-per-page-options="[0]">
            <template #body-cell-imagen="props">
              <q-td :props="props"><q-avatar rounded size="34px"><q-img :src="itemImageUrl(props.row)" /></q-avatar></q-td>
            </template>
            <template #body-cell-cantidad="props">
              <q-td :props="props"><q-input v-model.number="props.row.cantidad" dense outlined type="number" min="1" style="width: 78px" @update:model-value="recalculate(props.row)" /></q-td>
            </template>
            <template #body-cell-precio="props">
              <q-td :props="props"><q-input v-model.number="props.row.precio" dense outlined type="number" step="0.01" style="width: 96px" @update:model-value="recalculate(props.row)" /></q-td>
            </template>
            <template #body-cell-factor="props">
              <q-td :props="props"><q-input v-model.number="props.row.factor" dense outlined type="number" step="0.01" style="width: 82px" @update:model-value="recalculate(props.row)" /></q-td>
            </template>
            <template #body-cell-lote="props">
              <q-td :props="props"><q-input v-model="props.row.lote" dense outlined style="width: 110px" /></q-td>
            </template>
            <template #body-cell-fecha_vencimiento="props">
              <q-td :props="props"><q-input v-model="props.row.fecha_vencimiento" dense outlined type="date" style="width: 145px" /></q-td>
            </template>
            <template #body-cell-total="props">
              <q-td :props="props">{{ money(props.row.total) }}</q-td>
            </template>
            <template #body-cell-actions="props">
              <q-td :props="props"><q-btn flat round dense color="negative" icon="delete" @click="removeItem(props.row)" /></q-td>
            </template>
          </q-table>

          <q-card-section class="row items-center q-pa-sm">
            <div class="text-subtitle1 text-weight-bold">Total: {{ money(total) }} Bs</div>
            <q-space />
            <q-btn color="primary" icon="add_circle" label="Registrar compra" no-caps :loading="saving" :disable="selectedItems.length === 0" @click="save" />
          </q-card-section>
        </q-card>
      </div>
    </div>
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
      products: [],
      proveedores: [],
      proveedorOptions: [],
      selectedItems: [],
      productFilter: '',
      productPagination: { page: 1, rowsPerPage: 30, rowsNumber: 0 },
      form: {
        proveedor_id: null,
        fecha_hora: moment().format('YYYY-MM-DDTHH:mm'),
        tipo_registro: 'ENTRADA',
        motivo_registro: 'COMPRA',
        tipo_pago: 'EFECTIVO',
        nro_factura: '',
        carnet: '',
        nombre: '',
      },
      tipoOptions: [
        { label: 'Entrada', value: 'ENTRADA' },
        { label: 'Salida', value: 'SALIDA' },
      ],
      entradaMotivos: ['COMPRA', 'DONACION', 'TRANSFERENCIA', 'AJUSTE POSITIVO'],
      salidaMotivos: ['CONSUMO', 'TRANSFERENCIA', 'BAJA', 'AJUSTE NEGATIVO'],
      pagoOptions: [
        { label: 'Efectivo', value: 'EFECTIVO' },
        { label: 'Credito', value: 'CREDITO' },
        { label: 'Transferencia', value: 'TRANSFERENCIA' },
      ],
      columns: [
        { name: 'imagen', label: '', field: 'imagen', align: 'left' },
        { name: 'nombre', label: 'Producto', field: 'nombre', align: 'left' },
        { name: 'cantidad', label: 'Cantidad', field: 'cantidad', align: 'right' },
        { name: 'precio', label: 'Precio unitario', field: 'precio', align: 'right' },
        { name: 'total', label: 'Total', field: 'total', align: 'right' },
        { name: 'factor', label: 'Factor', field: 'factor', align: 'right' },
        { name: 'lote', label: 'Lote', field: 'lote', align: 'left' },
        { name: 'fecha_vencimiento', label: 'Fecha vencimiento', field: 'fecha_vencimiento', align: 'left' },
        { name: 'actions', label: '', field: 'id', align: 'right' },
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
      const list = this.form.tipo_registro === 'SALIDA' ? this.salidaMotivos : this.entradaMotivos
      return list.map(value => ({ label: value, value }))
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
      this.proveedorOptions = this.proveedores.map(p => ({ label: p.nombre, value: p.id }))
    },
    filterProveedores (value, update) {
      update(() => {
        const text = (value || '').toLowerCase()
        this.proveedorOptions = this.proveedores
          .filter(p => !text || p.nombre.toLowerCase().includes(text))
          .map(p => ({ label: p.nombre, value: p.id }))
      })
    },
    onProveedorChange (id) {
      const proveedor = this.proveedores.find(p => p.id === id)
      this.form.carnet = proveedor?.carnet || proveedor?.nit || ''
      this.form.nombre = proveedor?.nombre || ''
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
        cantidad: 1,
        precio: Number(product.precio_unitario || 0),
        total: Number(product.precio_unitario || 0),
        factor: 1.25,
        precio_venta: Number(product.precio_unitario || 0) * 1.25,
        lote: '',
        fecha_vencimiento: null,
      }
      this.selectedItems.push(item)
    },
    recalculate (item) {
      item.total = Number(item.cantidad || 0) * Number(item.precio || 0)
      item.precio_venta = Number(item.precio || 0) * Number(item.factor || 1)
    },
    removeItem (item) {
      this.selectedItems = this.selectedItems.filter(row => row.producto_id !== item.producto_id)
    },
    syncMotivo () {
      this.form.motivo_registro = this.form.tipo_registro === 'SALIDA' ? 'CONSUMO' : 'COMPRA'
    },
    async save () {
      this.saving = true
      try {
        await this.$axios.post('compras', {
          ...this.form,
          fecha_hora: this.form.fecha_hora ? this.form.fecha_hora.replace('T', ' ') : null,
          items: this.selectedItems,
        })
        this.$alert.success('Registro guardado')
        this.$router.push('/almacen/compras')
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo registrar')
      } finally {
        this.saving = false
      }
    },
    itemImageUrl (row) {
      return `${this.$url}/../images/productos/${row?.imagen || 'default.png'}`
    },
    money (value) {
      return Number(value || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
  },
}
</script>

<style scoped>
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
  gap: 6px;
  max-height: calc(100vh - 230px);
  overflow: auto;
}

.product-card {
  position: relative;
  min-height: 122px;
  border: 1px solid #d8e0e8;
  cursor: pointer;
  overflow: hidden;
  background: #fff;
}

.product-image {
  height: 84px;
}

.product-name {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 22px;
  padding: 2px 4px;
  background: rgba(38, 50, 56, .76);
  color: #fff;
  font-size: 12px;
  line-height: 1.05;
}

.product-foot {
  display: flex;
  justify-content: space-between;
  padding: 2px 4px;
  font-size: 12px;
}
</style>

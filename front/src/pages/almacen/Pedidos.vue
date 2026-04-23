<template>
  <q-page class="q-pa-sm bg-grey-2">
    <div class="row items-center q-mb-sm">
      <div>
        <div class="text-h6 text-weight-bold">Pedidos</div>
        <div class="text-caption text-grey-7">Listado de pedidos registrados en almacén</div>
      </div>
      <q-space />
      <q-btn
        v-if="canCreate"
        unelevated
        color="primary"
        icon="add_circle"
        label="Pedido nuevo"
        no-caps
        to="/pedidos/nuevo"
      />
    </div>

    <div class="row q-col-gutter-sm q-mb-sm">
      <div class="col-12 col-sm-3">
        <q-card flat bordered class="summary-card summary-blue">
          <q-card-section class="row items-center no-wrap">
            <q-avatar size="48px" color="blue-7" text-color="white" icon="pending_actions" />
            <div class="q-ml-md">
              <div class="text-caption text-grey-8">Pendientes</div>
              <div class="text-h6 text-weight-bold text-blue-9">{{ summaryData.total_pendientes }}</div>
            </div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-3">
        <q-card flat bordered class="summary-card summary-green">
          <q-card-section class="row items-center no-wrap">
            <q-avatar size="48px" color="green-6" text-color="white" icon="task_alt" />
            <div class="q-ml-md">
              <div class="text-caption text-grey-8">Aceptados</div>
              <div class="text-h6 text-weight-bold text-green-9">{{ summaryData.total_aceptados }}</div>
            </div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-3">
        <q-card flat bordered class="summary-card summary-red">
          <q-card-section class="row items-center no-wrap">
            <q-avatar size="48px" color="red-6" text-color="white" icon="cancel" />
            <div class="q-ml-md">
              <div class="text-caption text-grey-8">Rechazados</div>
              <div class="text-h6 text-weight-bold text-red-9">{{ summaryData.total_rechazados }}</div>
            </div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-3">
        <q-card flat bordered class="summary-card summary-slate">
          <q-card-section class="row items-center no-wrap">
            <q-avatar size="48px" color="grey-8" text-color="white" icon="shopping_bag" />
            <div class="q-ml-md">
              <div class="text-caption text-grey-8">Cantidad de pedidos</div>
              <div class="text-h6 text-weight-bold text-grey-10">{{ summaryData.cantidad }}</div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row q-col-gutter-sm items-center q-pa-sm">
        <div class="col-12 col-sm-3">
          <q-input
            v-model="filters.date_from"
            dense
            outlined
            type="date"
            label="Fecha inicio"
          >
            <template #prepend><q-icon name="event" /></template>
          </q-input>
        </div>
        <div class="col-12 col-sm-3">
          <q-input
            v-model="filters.date_to"
            dense
            outlined
            type="date"
            label="Fecha fin"
          >
            <template #prepend><q-icon name="event" /></template>
          </q-input>
        </div>
        <div class="col-12 col-sm-2">
          <q-select
            v-model="filters.estado"
            :options="estadoOptions"
            dense
            outlined
            clearable
            emit-value
            map-options
            label="Estado"
          />
        </div>
        <div class="col-12 col-sm">
          <q-input
            v-model="filters.q"
            dense
            outlined
            clearable
            debounce="350"
            label="Buscar usuario o ID"
            @update:model-value="applyFilters"
          >
            <template #prepend><q-icon name="search" /></template>
          </q-input>
        </div>
        <div class="col-auto">
          <q-btn
            unelevated
            color="primary"
            icon="search"
            label="Buscar"
            no-caps
            :loading="store.loading"
            @click="applyFilters"
          />
        </div>
        <div class="col-auto">
          <q-btn
            flat
            color="grey-8"
            icon="refresh"
            label="Limpiar"
            no-caps
            @click="clearFilters"
          />
        </div>
      </q-card-section>

      <q-separator />

      <q-table
        v-model:pagination="store.pagination"
        flat
        row-key="id"
        :rows="store.pedidos"
        :columns="columns"
        :loading="store.loading"
        :rows-per-page-options="[10, 15, 25, 50]"
        @request="onRequest"
      >
        <template #body-cell-id="props">
          <q-td :props="props">
            <q-badge color="grey-3" text-color="grey-9" class="text-weight-medium">
              #{{ props.row.id }}
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-nombre_usuario="props">
          <q-td :props="props">
            <div class="row items-center no-wrap">
              <q-avatar size="32px" color="primary" text-color="white" icon="person" class="q-mr-sm" />
              <div class="text-weight-medium">{{ props.row.nombre_usuario || '-' }}</div>
            </div>
          </q-td>
        </template>

        <template #body-cell-fecha_hora="props">
          <q-td :props="props">
            <div class="text-weight-medium">{{ formatDate(props.row.fecha_hora) }}</div>
            <div class="text-caption text-grey-7">{{ formatTime(props.row.fecha_hora) }}</div>
          </q-td>
        </template>

        <template #body-cell-estado="props">
          <q-td :props="props">
            <q-badge :color="estadoColor(props.row.estado)" class="q-pa-xs text-weight-bold">
              {{ props.row.estado }}
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-modificado="props">
          <q-td :props="props">
            <q-chip
              dense
              square
              :color="props.row.modificado ? 'amber-2' : 'grey-3'"
              :text-color="props.row.modificado ? 'amber-10' : 'grey-8'"
            >
              {{ props.row.modificado ? 'Sí' : 'No' }}
            </q-chip>
          </q-td>
        </template>

        <template #body-cell-total="props">
          <q-td :props="props" class="text-right">
            <span class="text-weight-bold text-primary">{{ money(props.row.total) }} Bs</span>
          </q-td>
        </template>

        <template #body-cell-acciones="props">
          <q-td :props="props">
            <q-btn-dropdown
              unelevated
              color="primary"
              text-color="white"
              no-caps
              size="sm"
              label="Opciones"
              icon="settings"
            >
              <q-list dense>
                <q-item clickable v-close-popup @click="viewPedido(props.row)">
                  <q-item-section avatar><q-icon name="visibility" color="primary" /></q-item-section>
                  <q-item-section>Ver detalle</q-item-section>
                </q-item>
                <q-item
                  v-if="canPrint"
                  clickable
                  v-close-popup
                  :disable="printingId === props.row.id"
                  @click="printPedido(props.row.id)"
                >
                  <q-item-section avatar>
                    <q-spinner v-if="printingId === props.row.id" color="teal" size="20px" />
                    <q-icon v-else name="print" color="teal" />
                  </q-item-section>
                  <q-item-section>Imprimir</q-item-section>
                </q-item>
                <q-item
                  v-if="canEdit"
                  clickable
                  v-close-popup
                  :disable="props.row.estado !== 'PENDIENTE'"
                  @click="editPedido(props.row)"
                >
                  <q-item-section avatar><q-icon name="edit" color="amber-9" /></q-item-section>
                  <q-item-section>Editar estado</q-item-section>
                </q-item>
                <q-separator v-if="canDelete" />
                <q-item
                  v-if="canDelete"
                  clickable
                  v-close-popup
                  :disable="props.row.estado !== 'PENDIENTE'"
                  @click="deletePedido(props.row.id)"
                >
                  <q-item-section avatar><q-icon name="cancel" color="negative" /></q-item-section>
                  <q-item-section class="text-negative">Anular</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <q-dialog v-model="showDetailDialog">
      <q-card class="detail-dialog">
        <div class="detail-header">
          <div class="row items-center no-wrap">
            <q-icon name="receipt_long" size="28px" class="q-mr-sm" />
            <div>
              <div class="text-h6 text-weight-bold">Detalle de pedido #{{ selectedPedido?.id }}</div>
              <div class="text-caption text-grey-3">{{ formatDateTime(selectedPedido?.fecha_hora) }}</div>
            </div>
            <q-space />
            <q-badge
              v-if="selectedPedido"
              :color="estadoColor(selectedPedido.estado)"
              class="q-pa-sm text-weight-bold q-mr-sm"
            >
              {{ selectedPedido.estado }}
            </q-badge>
            <q-btn flat round dense icon="close" color="white" @click="showDetailDialog = false" />
          </div>
        </div>

        <q-card-section v-if="selectedPedido" class="q-pa-md">
          <div class="meta-grid">
            <div class="meta-item">
              <q-icon name="badge" size="20px" class="meta-icon" />
              <div class="meta-content">
                <div class="meta-label">ID</div>
                <div class="meta-value">#{{ selectedPedido.id }}</div>
              </div>
            </div>
            <div class="meta-item">
              <q-icon name="person" size="20px" class="meta-icon" />
              <div class="meta-content">
                <div class="meta-label">Usuario</div>
                <div class="meta-value">{{ selectedPedido.nombre_usuario || selectedPedido.user?.name || '-' }}</div>
              </div>
            </div>
            <div class="meta-item">
              <q-icon name="event" size="20px" class="meta-icon" />
              <div class="meta-content">
                <div class="meta-label">Fecha y hora</div>
                <div class="meta-value">{{ formatDateTime(selectedPedido.fecha_hora) }}</div>
              </div>
            </div>
            <div class="meta-item">
              <q-icon name="edit_note" size="20px" class="meta-icon" />
              <div class="meta-content">
                <div class="meta-label">Modificado</div>
                <div class="meta-value">{{ selectedPedido.modificado ? 'Sí' : 'No' }}</div>
              </div>
            </div>
            <div class="meta-item">
              <q-icon name="payments" size="20px" class="meta-icon" />
              <div class="meta-content">
                <div class="meta-label">Total</div>
                <div class="meta-value">{{ money(selectedPedido.total) }} Bs</div>
              </div>
            </div>
          </div>

        </q-card-section>

        <q-separator />

        <q-card-section v-if="selectedPedido" class="q-pa-md">

          <div class="row items-center q-mb-sm">
            <q-icon name="inventory_2" size="18px" color="primary" class="q-mr-xs" />
            <div class="text-subtitle2 text-weight-bold">Productos</div>
            <q-space />
            <q-chip dense color="primary" text-color="white" :label="`${(selectedPedido.detalles || []).length} items`" />
          </div>

          <div class="detail-items">
            <div
              v-for="det in selectedPedido.detalles || []"
              :key="det.id"
              class="detail-item"
            >
              <q-img
                :src="itemImageUrl(det)"
                class="detail-item-img"
                fit="cover"
                no-spinner
              />
              <div class="detail-item-info">
                <div class="detail-item-name">{{ det.producto?.nombre || '-' }}</div>
                <div class="detail-item-meta">
                  <span>{{ det.cantidad }} x {{ money(det.precio_unitario) }} Bs</span>
                </div>
              </div>
              <div class="detail-item-total">{{ money(det.subtotal) }} Bs</div>
            </div>
            <div v-if="(selectedPedido.detalles || []).length === 0" class="text-center text-grey-7 q-pa-md">
              Sin productos
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-section v-if="selectedPedido" class="q-pa-md detail-summary">
          <div class="summary-row">
            <span class="summary-label">Subtotal</span>
            <span class="summary-value">{{ money(selectedPedido.total) }} Bs</span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Items</span>
            <span class="summary-value">{{ (selectedPedido.detalles || []).length }}</span>
          </div>
          <q-separator class="q-my-sm" />
          <div class="summary-row total-row">
            <span class="total-label">Total</span>
            <span class="total-value">{{ money(selectedPedido.total) }} Bs</span>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-actions class="q-pa-md">
          <q-space />
          <q-btn flat no-caps color="grey-8" label="Cerrar" @click="showDetailDialog = false" />
          <q-btn
            v-if="selectedPedido && canPrint"
            unelevated
            no-caps
            color="teal"
            icon="print"
            label="Imprimir"
            :loading="printingId === selectedPedido.id"
            @click="printPedido(selectedPedido.id)"
          />
          <q-btn
            v-if="selectedPedido && canEdit"
            unelevated
            no-caps
            color="primary"
            icon="edit"
            label="Editar estado"
            :disable="selectedPedido.estado !== 'PENDIENTE'"
            @click="editFromDetail(selectedPedido)"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="showEditDialog" position="right">
      <q-card style="min-width: 420px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Editar estado del pedido</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="showEditDialog = false" />
        </q-card-section>
        <q-separator />
        <q-card-section class="q-gutter-md">
          <div class="text-caption text-grey-7">Pedido #{{ editingPedido?.id }}</div>
          <q-select
            v-model="editingPedido.estado"
            :options="estadoOptions"
            label="Estado"
            outlined
            dense
            emit-value
            map-options
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" no-caps @click="showEditDialog = false" />
          <q-btn color="primary" label="Guardar" no-caps :loading="store.loading" @click="saveEdit" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { computed, getCurrentInstance, onMounted, ref } from 'vue'
import { useQuasar } from 'quasar'
import { usePedidosStore } from 'stores/pedidos.js'
import moment from 'moment'

const { proxy } = getCurrentInstance()
const $q = useQuasar()
const store = usePedidosStore()

const showDetailDialog = ref(false)
const showEditDialog = ref(false)
const selectedPedido = ref(null)
const editingPedido = ref(null)
const printingId = ref(null)

const filters = ref({
  date_from: moment().format('YYYY-MM-DD'),
  date_to: moment().format('YYYY-MM-DD'),
  estado: null,
  q: ''
})

const estadoOptions = [
  { label: 'Pendiente', value: 'PENDIENTE' },
  { label: 'Aceptado', value: 'ACEPTADO' },
  { label: 'Rechazado', value: 'RECHAZADO' }
]

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left' },
  { name: 'nombre_usuario', label: 'Usuario', field: 'nombre_usuario', align: 'left' },
  { name: 'fecha_hora', label: 'Fecha', field: 'fecha_hora', align: 'left' },
  { name: 'estado', label: 'Estado', field: 'estado', align: 'left' },
  { name: 'modificado', label: 'Modificado', field: 'modificado', align: 'left' },
  { name: 'total', label: 'Total', field: 'total', align: 'right' },
  { name: 'acciones', label: 'Acciones', field: 'acciones', align: 'center' }
]

const userPermissions = computed(() => proxy.$store.permissions || [])
const canCreate = computed(() => userPermissions.value.includes('Crear Pedidos'))
const canEdit = computed(() => userPermissions.value.includes('Editar Pedidos'))
const canDelete = computed(() => userPermissions.value.includes('Anular Pedidos'))
const canPrint = computed(() => userPermissions.value.includes('Imprimir Pedidos'))

const summaryData = computed(() => ({
  total_pendientes: store.summary.total_pendientes ?? store.pedidos.filter(p => p.estado === 'PENDIENTE').length,
  total_aceptados: store.summary.total_aceptados ?? store.pedidos.filter(p => p.estado === 'ACEPTADO').length,
  total_rechazados: store.summary.total_rechazados ?? store.pedidos.filter(p => p.estado === 'RECHAZADO').length,
  cantidad: store.summary.cantidad ?? store.pedidos.length
}))

onMounted(async () => {
  await store.fetchPedidos(filters.value)
})

async function applyFilters () {
  store.pagination.page = 1
  await store.fetchPedidos(filters.value)
}

function clearFilters () {
  filters.value = {
    date_from: moment().format('YYYY-MM-DD'),
    date_to: moment().format('YYYY-MM-DD'),
    estado: null,
    q: ''
  }
  applyFilters()
}

async function onRequest (props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination
  store.pagination = {
    ...store.pagination,
    page,
    rowsPerPage,
    sortBy,
    descending
  }
  await store.fetchPedidos(filters.value)
}

async function viewPedido (pedido) {
  selectedPedido.value = await store.fetchPedido(pedido.id)
  showDetailDialog.value = true
}

function editPedido (pedido) {
  editingPedido.value = { ...pedido }
  showEditDialog.value = true
}

function editFromDetail (pedido) {
  showDetailDialog.value = false
  editPedido(pedido)
}

async function saveEdit () {
  await store.updatePedido(editingPedido.value.id, { estado: editingPedido.value.estado })
  showEditDialog.value = false
  await applyFilters()
  $q.notify({
    color: 'positive',
    message: 'Pedido actualizado',
    position: 'top'
  })
}

function deletePedido (id) {
  $q.dialog({
    title: 'Confirmar',
    message: '¿Deseas anular este pedido?',
    cancel: true
  }).onOk(async () => {
    await store.deletePedido(id)
    await applyFilters()
    $q.notify({
      color: 'positive',
      message: 'Pedido anulado',
      position: 'top'
    })
  })
}

async function printPedido (id) {
  printingId.value = id
  try {
    const res = await proxy.$axios.get(`pedidos/${id}/pdf`, { responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    window.open(url, '_blank')
    window.setTimeout(() => window.URL.revokeObjectURL(url), 60000)
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'No se pudo generar la impresión',
      position: 'top'
    })
  } finally {
    printingId.value = null
  }
}

function estadoColor (estado) {
  if (estado === 'ACEPTADO') return 'green'
  if (estado === 'RECHAZADO') return 'red'
  return 'blue'
}

function money (value) {
  return new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(Number(value || 0))
}

function formatDate (value) {
  if (!value) return '-'
  return moment(value).format('DD/MM/YYYY')
}

function formatTime (value) {
  if (!value) return ''
  return moment(value).format('HH:mm')
}

function formatDateTime (value) {
  if (!value) return '-'
  return moment(value).format('DD/MM/YYYY HH:mm')
}

function itemImageUrl (det) {
  const imagen = det?.producto?.imagen || det?.imagen || 'default.png'
  return `${proxy.$url}/../images/productos/${imagen}`
}
</script>

<style scoped>
.summary-card {
  border-radius: 10px;
}

.summary-blue { border-left: 4px solid #1976d2; }
.summary-green { border-left: 4px solid #43a047; }
.summary-red { border-left: 4px solid #c90022; }
.summary-slate { border-left: 4px solid #455a64; }

.detail-dialog {
  width: 760px;
  max-width: 94vw;
  border-radius: 10px;
  overflow: hidden;
}

.detail-header {
  background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
  color: #fff;
  padding: 16px 18px;
}

.meta-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
}

.meta-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 12px;
  background: #f7f9fc;
  border: 1px solid #e5eaf2;
  border-radius: 8px;
}

.meta-icon {
  color: #1976d2;
  margin-top: 2px;
}

.meta-content {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.meta-label {
  font-size: 11px;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  font-weight: 600;
}

.meta-value {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  text-transform: capitalize;
  word-break: break-word;
}

.detail-items {
  border: 1px solid #e5eaf2;
  border-radius: 8px;
  background: #fff;
  padding: 4px;
  max-height: 320px;
  overflow-y: auto;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 10px;
  border-bottom: 1px solid #f0f2f5;
}

.detail-item:last-child {
  border-bottom: none;
}

.detail-item-img {
  flex: 0 0 auto;
  width: 56px;
  height: 56px;
  border-radius: 6px;
  border: 1px solid #e5eaf2;
  background: #f5f5f5;
}

.detail-item-info {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.detail-item-name {
  font-size: 13px;
  font-weight: 600;
  color: #1f2937;
  text-transform: capitalize;
  line-height: 1.2;
  word-break: break-word;
}

.detail-item-meta {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.detail-item-total {
  font-size: 14px;
  font-weight: 700;
  color: #1976d2;
  white-space: nowrap;
}

.detail-summary {
  background: #f7f9fc;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 0;
}

.summary-label {
  font-size: 13px;
  color: #4b5563;
}

.summary-value {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
}

.total-row {
  padding: 6px 12px;
  background: #e3f2fd;
  border-radius: 8px;
}

.total-label {
  font-size: 15px;
  font-weight: 700;
  color: #0d47a1;
}

.total-value {
  font-size: 22px;
  font-weight: 800;
  color: #1976d2;
}
</style>

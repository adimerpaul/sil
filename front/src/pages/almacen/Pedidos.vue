<template>
  <q-page class="q-pa-md">
    <!-- Summary Cards -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-xs-12 col-sm-6 col-md-3">
        <q-card class="bg-info text-white">
          <q-card-section>
            <div class="text-h6">{{ summaryData.total_pendientes }}</div>
            <div class="text-caption">Pendientes</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="text-h6">{{ summaryData.total_aceptados }}</div>
            <div class="text-caption">Aceptados</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-3">
        <q-card class="bg-negative text-white">
          <q-card-section>
            <div class="text-h6">{{ summaryData.total_rechazados }}</div>
            <div class="text-caption">Rechazados</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-3">
        <q-card class="bg-secondary text-white">
          <q-card-section>
            <div class="text-h6">{{ summaryData.cantidad }}</div>
            <div class="text-caption">Total</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Filter Section -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-xs-12 col-sm-6 col-md-3">
            <q-input
              v-model="filters.date_from"
              type="date"
              label="Desde"
              filled
              dense
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3">
            <q-input
              v-model="filters.date_to"
              type="date"
              label="Hasta"
              filled
              dense
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3">
            <q-select
              v-model="filters.estado"
              :options="estadoOptions"
              label="Estado"
              filled
              dense
              clearable
              emit-value
              map-options
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3">
            <q-input
              v-model="filters.q"
              label="Buscar"
              filled
              dense
              debounce="500"
              @update:model-value="applyFilters"
            />
          </div>
        </div>
      </q-card-section>
      <q-card-actions>
        <q-btn
          color="primary"
          label="Filtrar"
          @click="applyFilters"
        />
        <q-btn
          color="secondary"
          label="Limpiar"
          @click="clearFilters"
          flat
        />
        <q-space />
        <q-btn
          color="positive"
          label="Nuevo Pedido"
          icon="add"
          @click="showNewDialog = true"
        />
      </q-card-actions>
    </q-card>

    <!-- Table -->
    <q-card>
      <q-linear-progress
        v-if="store.loading"
        indeterminate
        color="primary"
      />
      <q-table
        :rows="store.pedidos"
        :columns="columns"
        row-key="id"
        :pagination.sync="store.pagination"
        @request="onRequest"
        :loading="store.loading"
        flat
        bordered
      >
        <template #body-cell-acciones="props">
          <q-td :props="props">
            <q-btn
              color="primary"
              icon="visibility"
              size="sm"
              flat
              @click="viewPedido(props.row)"
            />
            <q-btn
              v-if="canEdit"
              color="info"
              icon="edit"
              size="sm"
              flat
              @click="editPedido(props.row)"
            />
            <q-btn
              color="negative"
              icon="delete"
              size="sm"
              flat
              @click="deletePedido(props.row.id)"
            />
            <q-btn
              color="secondary"
              icon="print"
              size="sm"
              flat
              @click="printPedido(props.row.id)"
            />
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- View Detail Dialog -->
    <q-dialog v-model="showDetailDialog" position="right">
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Detalle del Pedido</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="showDetailDialog = false" />
        </q-card-section>
        <q-separator />
        <q-card-section class="scroll" style="height: 400px">
          <div v-if="selectedPedido">
            <q-item>
              <q-item-section>
                <q-item-label caption>ID</q-item-label>
                <q-item-label>{{ selectedPedido.id }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label caption>Usuario</q-item-label>
                <q-item-label>{{ selectedPedido.nombre_usuario }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label caption>Fecha</q-item-label>
                <q-item-label>{{ selectedPedido.fecha_hora }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label caption>Estado</q-item-label>
                <q-item-label>{{ selectedPedido.estado }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label caption>Modificado</q-item-label>
                <q-item-label>{{ selectedPedido.modificado ? 'Sí' : 'No' }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label caption>Total</q-item-label>
                <q-item-label class="text-h6 text-weight-bold">
                  {{ formatCurrency(selectedPedido.total) }}
                </q-item-label>
              </q-item-section>
            </q-item>
            <q-separator class="q-my-md" />
            <div class="text-subtitle2 q-mb-md">Detalles:</div>
            <q-table
              v-if="selectedPedido.detalles"
              :rows="selectedPedido.detalles"
              :columns="detailColumns"
              row-key="id"
              flat
              dense
            />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Edit Dialog -->
    <q-dialog v-model="showEditDialog" position="right">
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Editar Pedido</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="showEditDialog = false" />
        </q-card-section>
        <q-separator />
        <q-card-section class="scroll" style="height: 400px">
          <div v-if="editingPedido">
            <q-select
              v-model="editingPedido.estado"
              :options="['PENDIENTE', 'ACEPTADO', 'RECHAZADO']"
              label="Estado"
              filled
              dense
              emit-value
              map-options
            />
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" @click="showEditDialog = false" />
          <q-btn
            color="primary"
            label="Guardar"
            @click="saveEdit"
            :loading="store.loading"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- New Pedido Dialog -->
    <q-dialog v-model="showNewDialog">
      <q-card style="min-width: 600px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Nuevo Pedido</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="showNewDialog = false" />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <FormularioPedido @saved="pedidoGuardado" />
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePedidosStore } from 'stores/pedidos.js'
import { useQuasar } from 'quasar'
import FormularioPedido from 'components/FormularioPedido.vue'

const $q = useQuasar()
const store = usePedidosStore()

const showDetailDialog = ref(false)
const showEditDialog = ref(false)
const showNewDialog = ref(false)
const selectedPedido = ref(null)
const editingPedido = ref(null)

const filters = ref({
  date_from: '',
  date_to: '',
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
  { name: 'total', label: 'Total', field: 'total', align: 'left' },
  { name: 'acciones', label: 'Acciones', field: 'acciones', align: 'center' }
]

const detailColumns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left' },
  { name: 'cantidad', label: 'Cantidad', field: 'cantidad', align: 'left' },
  { name: 'precio_unitario', label: 'Precio', field: 'precio_unitario', align: 'left' },
  { name: 'subtotal', label: 'Subtotal', field: 'subtotal', align: 'left' }
]

const summaryData = computed(() => ({
  total_pendientes: store.pedidos.filter(p => p.estado === 'PENDIENTE').length,
  total_aceptados: store.pedidos.filter(p => p.estado === 'ACEPTADO').length,
  total_rechazados: store.pedidos.filter(p => p.estado === 'RECHAZADO').length,
  cantidad: store.pedidos.length
}))

const canEdit = computed(() => {
  return ['admin', 'jefe-almacen'].some(role =>
    JSON.parse(localStorage.getItem('user') || '{}').roles?.includes?.(role)
  )
})

onMounted(() => {
  store.fetchPedidos(filters.value)
})

const applyFilters = async () => {
  store.pagination.page = 1
  await store.fetchPedidos(filters.value)
}

const clearFilters = () => {
  filters.value = {
    date_from: '',
    date_to: '',
    estado: null,
    q: ''
  }
  applyFilters()
}

const onRequest = async (props) => {
  const { page, rowsPerPage } = props.pagination
  store.pagination.page = page
  store.pagination.rowsPerPage = rowsPerPage
  await store.fetchPedidos(filters.value)
}

const viewPedido = async (pedido) => {
  selectedPedido.value = pedido
  showDetailDialog.value = true
}

const editPedido = (pedido) => {
  editingPedido.value = { ...pedido }
  showEditDialog.value = true
}

const saveEdit = async () => {
  await store.updatePedido(editingPedido.value.id, { estado: editingPedido.value.estado })
  showEditDialog.value = false
  await applyFilters()
  $q.notify({
    color: 'positive',
    message: 'Pedido actualizado',
    position: 'top'
  })
}

const deletePedido = (id) => {
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

const printPedido = async (id) => {
  $q.notify({
    color: 'info',
    message: 'Generando impresión...',
    position: 'top'
  })
}

const pedidoGuardado = async () => {
  showNewDialog.value = false
  await applyFilters()
  $q.notify({
    color: 'positive',
    message: 'Pedido creado correctamente',
    position: 'top'
  })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR'
  }).format(value)
}
</script>

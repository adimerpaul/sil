<template>
  <q-page class="q-pa-sm">
    <!-- FILTROS -->
    <q-card flat bordered>
      <q-card-section class="row items-center q-col-gutter-xs">
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.from" type="date" dense outlined label="Desde" />
        </div>
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.to" type="date" dense outlined label="Hasta" />
        </div>
        <div class="col-12 col-sm-3">
          <q-select
            v-model="filters.estado"
            :options="['', 'CREADO', 'ATENDIENDO', 'FINALIZADO']"
            dense outlined
            label="Estado"
          />
        </div>
      </q-card-section>

      <q-card-section class="row items-center q-col-gutter-xs">
        <div class="col-12 col-sm-6">
          <q-input dense outlined v-model="filter" label="Buscar">
            <template #append><q-icon name="search" /></template>
          </q-input>
        </div>
        <div class="col-12 col-sm-6 text-right">
          <q-btn
            color="primary"
            icon="search"
            label="Filtrar"
            no-caps
            class="q-mr-xs"
            :loading="loading"
            @click="getSolicitudes"
          />
          <q-btn
            color="positive"
            icon="add_circle_outline"
            label="Nueva"
            no-caps
            :loading="loading"
            @click="nuevo"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- TABLA -->
    <q-table
      class="q-mt-sm"
      :rows="rows"
      :columns="columns"
      row-key="id"
      dense flat bordered
      :rows-per-page-options="[0]"
      :filter="filter"
      title="Solicitudes"
    >
      <template #body-cell-actions="props">
        <q-td :props="props">
          <q-btn-dropdown color="primary" label="Opciones" dense size="10px" no-caps>
            <q-list>
              <q-item clickable v-close-popup @click="editar(props.row)">
                <q-item-section avatar><q-icon name="edit" /></q-item-section>
                <q-item-section>Editar</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="eliminar(props.row.id)">
                <q-item-section avatar><q-icon name="delete" /></q-item-section>
                <q-item-section>Eliminar</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </q-td>
      </template>
    </q-table>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'SolicitudesPage',
  data () {
    return {
      rows: [],
      columns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'id', label: 'ID', field: 'id', align: 'left' },
        { name: 'fecha_solicitud', label: 'Fecha', field: row => row.fecha_solicitud, format: v => v || '' },
        { name: 'paciente', label: 'Paciente', field: row => row.paciente?.nombre_completo || row.paciente_nombre || '' },
        { name: 'doctor', label: 'Doctor', field: row => row.doctor?.nombre || row.doctor_nombre || '' },
        { name: 'tipo_atencion', label: 'Tipo atención', field: 'tipo_atencion' },
        { name: 'estado', label: 'Estado', field: 'estado' }
      ],
      filter: '',
      loading: false,
      filters: {
        from: moment().format('YYYY-MM-DD'),
        to: moment().format('YYYY-MM-DD'),
        tipo_atencion: '',
        estado: ''
      }
    }
  },
  mounted () {
    this.getSolicitudes()
  },
  methods: {
    getSolicitudes () {
      this.loading = true
      this.$axios
        .get('solicitudes', { params: this.filters })
        .then(res => { this.rows = res.data })
        .finally(() => { this.loading = false })
    },

    // ✅ AHORA NAVEGA A RUTA
    nuevo () {
      this.$router.push({ name: 'solicitudes-new' })
    },

    // ✅ AHORA NAVEGA A RUTA
    editar (row) {
      this.$router.push({ name: 'solicitudes-edit', params: { id: row.id } })
    },

    eliminar (id) {
      if (this.$alert && this.$alert.dialog) {
        this.$alert.dialog('¿Eliminar solicitud?').onOk(() => {
          this.$axios.delete(`solicitudes/${id}`).then(() => {
            this.$alert.success('Eliminado')
            this.getSolicitudes()
          })
        })
      } else {
        if (confirm('¿Eliminar solicitud?')) {
          this.$axios.delete(`solicitudes/${id}`).then(() => {
            this.getSolicitudes()
          })
        }
      }
    }
  }
}
</script>

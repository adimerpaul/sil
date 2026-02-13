<template>
  <q-page class="q-pa-sm">
    <q-card flat bordered>
      <q-card-section class="row items-center q-col-gutter-xs">
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.from" type="date" dense outlined label="Desde" />
        </div>
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.to" type="date" dense outlined label="Hasta" />
        </div>
        <div class="col-12 col-sm-3" v-if="$store.user?.role === 'Administrador'">
          <q-select
            v-model="filters.area_id"
            :options="areas"
            option-label="title"
            option-value="id"
            emit-value
            map-options
            clearable
            dense
            outlined
            label="Area"
          />
        </div>
      </q-card-section>

      <q-card-section class="row items-center q-col-gutter-xs">
        <div class="col-12 col-sm-6">
          <q-input dense outlined v-model="filters.search" label="Buscar paciente / codigo / doctor" @keyup.enter="fetchRows">
            <template #append><q-icon name="search" /></template>
          </q-input>
        </div>
        <div class="col-12 col-sm-6 text-right">
          <q-btn color="primary" icon="search" label="Filtrar" no-caps :loading="loading" @click="fetchRows" class="q-mr-xs" />
          <q-btn-dropdown color="grey-8" icon="description" label="Reportes" no-caps :loading="loadingReport">
            <q-list dense>
              <q-item clickable v-close-popup @click="openReporte('dia')">
                <q-item-section>Reporte por día</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="openReporte('area')">
                <q-item-section>Reporte por área</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="openReporte('pendientes')">
                <q-item-section>Pendientes</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="openReporte('activos')">
                <q-item-section>Activos</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="openReporte('recogidos')">
                <q-item-section>Recogidos</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </div>
      </q-card-section>
    </q-card>

    <q-table
      class="q-mt-sm"
      :rows="rows"
      :columns="columns"
      row-key="id"
      dense
      flat
      bordered
      v-model:pagination="pagination"
      :loading="loading"
      @request="onRequest"
      :rows-per-page-options="[10, 20, 50]"
      title="Recogidos por solicitud y area"
    >
      <template #body-cell-codigo="props">
        <q-td :props="props">
          {{ `${props.row.codigo || ''}${props.row.nro_registro || ''}` }}
        </q-td>
      </template>

      <template #body-cell-servicios="props">
        <q-td :props="props">
          <div class="column q-gutter-xs">
            <div
              v-for="ga in groupedAreas(props.row.servicio_solicitudes)"
              :key="`${props.row.id}-${ga.area_id}`"
              class="row items-center q-gutter-sm"
            >
              <q-checkbox
                :model-value="isSelectedArea(props.row.id, ga.area_id)"
                @update:model-value="toggleAreaSelection(props.row.id, ga.area_id, $event)"
                dense
              />

              <q-badge :color="areaStatusColor(ga)" text-color="white" class="q-pa-xs">
                <q-icon :name="areaStatusIcon(ga)" class="q-mr-xs" />
                {{ ga.area_nombre }} ({{ ga.recogidos }}/{{ ga.total_servicios }})
              </q-badge>

              <q-btn
                size="sm"
                dense
                color="grey-8"
                label="Recoger area"
                no-caps
                @click="openDialogByAreas(props.row, [ga])"
              />
            </div>

            <div class="row">
              <q-btn
                size="sm"
                dense
                color="grey"
                icon="done_all"
                class="text-black"
                label="Seleccionar todos"
                no-caps
                @click="openDialogSelected(props.row)"
              />
            </div>
          </div>
        </q-td>
      </template>
    </q-table>

    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 560px; max-width: 760px;">
        <q-card-section class="row items-center">
          <div class="text-h6">Recogido por area</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />

        <q-card-section>
          <div class="text-body2 q-mb-sm">
            <b>Paciente:</b> {{ selectedSolicitud?.paciente_nombre || '-' }}<br>
            <b>Areas:</b>
            <span v-if="selectedDialogAreas.length">{{ selectedDialogAreas.map(x => x.area_nombre).join(', ') }}</span>
            <span v-else>-</span>
          </div>

          <q-form @submit.prevent="saveRecogidoAreas">
            <div class="row q-col-gutter-sm">
              <div class="col-12">
                <q-toggle
                  v-model="form.fue_recogido"
                  :true-value="true"
                  :false-value="false"
                  label="Fue recogido"
                />
              </div>

              <div class="col-12 col-sm-6" v-if="form.fue_recogido">
                <q-input v-model="form.recogido_por_personal" dense outlined label="Recogido por personal" />
              </div>
              <div class="col-12 col-sm-6" v-if="form.fue_recogido">
<!--                <q-input v-model="form.grado_parentesco" dense outlined label="Grado de parentesco" />-->
                <q-select v-model="form.grado_parentesco" :options="['Padre', 'Madre', 'Hno/a', 'Abuelo/a', 'Tio/a', 'Primo/a', 'Otro','Personal']" dense outlined label="Grado de parentesco" />
              </div>
              <div class="col-12 col-sm-6" v-if="form.fue_recogido">
                <q-input v-model="form.telefono_recogido" dense outlined label="Telefono" />
              </div>
<!--              ci_recogido-->
              <div class="col-12 col-sm-6" v-if="form.fue_recogido">
                <q-input v-model="form.ci_recogido" dense outlined label="C.I. Recogido por" />
              </div>
<!--              <div class="col-12 col-sm-6" v-if="form.fue_recogido">-->
<!--                <q-input v-model="form.recogido_en_dia" type="date" dense outlined label="En el dia" />-->
<!--              </div>-->
            </div>

            <div class="text-right q-mt-md">
              <q-btn flat label="Cancelar" v-close-popup />
              <q-btn color="primary" label="Guardar" class="q-ml-sm" type="submit" :loading="saving" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'RecogidosPage',
  data () {
    return {
      loading: false,
      loadingReport: false,
      saving: false,
      rows: [],
      areas: [],
      selectedAreasBySolicitud: {},
      columns: [
        { name: 'id', label: 'ID', field: 'id', align: 'left' },
        { name: 'codigo', label: 'Codigo', field: 'codigo', align: 'left' },
        { name: 'fecha', label: 'Fecha', field: row => row.fecha_solicitud || row.fecha_creacion || '', align: 'left' },
        { name: 'paciente', label: 'Paciente', field: row => row.paciente_nombre || '', align: 'left' },
        { name: 'doctor', label: 'Doctor', field: row => row.doctor?.nombre || row.doctor_nombre || '', align: 'left' },
        { name: 'servicios', label: 'Areas del servicio', field: 'servicio_solicitudes', align: 'left' },
      ],
      pagination: {
        sortBy: 'id',
        descending: true,
        page: 1,
        rowsPerPage: 10,
        rowsNumber: 0,
      },
      filters: {
        search: '',
        from: moment().format('YYYY-MM-DD'),
        to: moment().format('YYYY-MM-DD'),
        area_id: null,
      },
      dialog: false,
      selectedSolicitud: null,
      selectedDialogAreas: [],
      form: {
        fue_recogido: false,
        recogido_por_personal: '',
        grado_parentesco: '',
        telefono_recogido: '',
        ci_recogido: '',
        recogido_en_dia: moment().format('YYYY-MM-DD'),
      },
    }
  },
  mounted () {
    this.fetchAreas()
    this.fetchRows()
  },
  methods: {
    fetchAreas () {
      if (this.$store.user?.role !== 'Administrador') return
      this.$axios.get('areas')
        .then(res => { this.areas = res.data || [] })
    },
    params () {
      return {
        ...this.filters,
        page: this.pagination.page,
        per_page: this.pagination.rowsPerPage,
      }
    },
    fetchRows () {
      this.loading = true
      this.$axios.get('recogidos', { params: this.params() })
        .then(res => {
          this.rows = res.data.rows || []
          this.pagination.rowsNumber = res.data.pagination?.rows_number || 0
          this.pagination.page = res.data.pagination?.page || 1
          this.pagination.rowsPerPage = res.data.pagination?.per_page || this.pagination.rowsPerPage
        })
        .finally(() => { this.loading = false })
    },
    onRequest (props) {
      this.pagination = props.pagination
      this.fetchRows()
    },
    async openReporte (tipo) {
      this.loadingReport = true
      try {
        const params = {
          tipo,
          from: this.filters.from || null,
          to: this.filters.to || null,
          date: this.filters.from || moment().format('YYYY-MM-DD'),
          search: this.filters.search || null,
          area_id: this.filters.area_id || null,
        }
        const res = await this.$axios.get('reportes/recogidos/pdf', {
          params,
          responseType: 'blob',
        })

        const blob = new Blob([res.data], { type: 'application/pdf' })
        const url = window.URL.createObjectURL(blob)
        window.open(url, '_blank')
        setTimeout(() => window.URL.revokeObjectURL(url), 20000)
      } catch (err) {
        this.$alert.error(err.response?.data?.message || 'No se pudo generar el reporte')
      } finally {
        this.loadingReport = false
      }
    },
    groupedAreas (servicios = []) {
      const map = {}
      servicios.forEach(ss => {
        const areaId = ss.area_id || ss.area?.id || ss.servicio?.area?.id
        if (!areaId) return

        if (!map[areaId]) {
          map[areaId] = {
            area_id: areaId,
            area_nombre: ss.area?.title || ss.area?.name || ss.servicio?.area?.title || ss.servicio?.area?.name || `Area ${areaId}`,
            total_servicios: 0,
            recogidos: 0,
            realizados: 0,
            all_recogido: false,
            all_realizado: false,
          }
        }

        map[areaId].total_servicios += 1
        if (ss.fue_recogido) map[areaId].recogidos += 1
        if ((ss.realizado || '').toUpperCase() === 'REALIZADO') map[areaId].realizados += 1
      })

      return Object.values(map).map(x => ({
        ...x,
        all_recogido: x.total_servicios > 0 && x.total_servicios === x.recogidos,
        all_realizado: x.total_servicios > 0 && x.total_servicios === x.realizados,
      }))
    },
    areaStatusColor (ga) {
      if (ga.all_recogido) return 'blue-9'
      if (ga.all_realizado) return 'green'
      return 'orange-7'
    },
    areaStatusIcon (ga) {
      if (ga.all_recogido) return 'check_circle'
      if (ga.all_realizado) return 'task_alt'
      return 'schedule'
    },
    areaKey (solicitudeId, areaId) {
      return `${solicitudeId}-${areaId}`
    },
    isSelectedArea (solicitudeId, areaId) {
      return !!this.selectedAreasBySolicitud[this.areaKey(solicitudeId, areaId)]
    },
    toggleAreaSelection (solicitudeId, areaId, checked) {
      const key = this.areaKey(solicitudeId, areaId)
      if (checked) this.selectedAreasBySolicitud[key] = true
      else delete this.selectedAreasBySolicitud[key]
      this.selectedAreasBySolicitud = { ...this.selectedAreasBySolicitud }
    },
    selectedAreasCount (solicitudeId) {
      const prefix = `${solicitudeId}-`
      return Object.keys(this.selectedAreasBySolicitud).filter(k => k.startsWith(prefix)).length
    },
    openDialogByAreas (solicitud, areasList) {
      this.selectedSolicitud = solicitud
      this.selectedDialogAreas = areasList

      const first = areasList[0]
      const firstService = (solicitud.servicio_solicitudes || []).find(x => (x.area_id || x.area?.id) === first.area_id)

      this.form = {
        fue_recogido: !!firstService?.fue_recogido,
        recogido_por_personal: firstService?.recogido_por_personal || '',
        grado_parentesco: firstService?.grado_parentesco || '',
        telefono_recogido: firstService?.telefono_recogido || '',
        ci_recogido: firstService?.ci_recogido || '',
        recogido_en_dia: firstService?.recogido_en_dia || moment().format('YYYY-MM-DD'),
      }

      this.dialog = true
    },
    openDialogSelected (solicitud) {
      const grouped = this.groupedAreas(solicitud.servicio_solicitudes)
      if (!grouped.length) return

      grouped.forEach(a => {
        const key = this.areaKey(solicitud.id, a.area_id)
        this.selectedAreasBySolicitud[key] = true
      })
      this.selectedAreasBySolicitud = { ...this.selectedAreasBySolicitud }

      this.openDialogByAreas(solicitud, grouped)
    },
    async saveRecogidoAreas () {
      if (!this.selectedSolicitud?.id || !this.selectedDialogAreas.length) return
      this.saving = true

      try {
        for (const area of this.selectedDialogAreas) {
          await this.$axios.post('recogidos/recoger-area', {
            solicitude_id: this.selectedSolicitud.id,
            area_id: area.area_id,
            ...this.form,
          })
        }

        const row = this.rows.find(r => r.id === this.selectedSolicitud.id)
        if (row) {
          row.servicio_solicitudes = row.servicio_solicitudes.map(ss => {
            const hit = this.selectedDialogAreas.some(a => a.area_id === (ss.area_id || ss.area?.id || ss.servicio?.area?.id))
            if (!hit) return ss
            return {
              ...ss,
              fue_recogido: this.form.fue_recogido,
              recogido_por_personal: this.form.fue_recogido ? this.form.recogido_por_personal : null,
              grado_parentesco: this.form.fue_recogido ? this.form.grado_parentesco : null,
              telefono_recogido: this.form.fue_recogido ? this.form.telefono_recogido : null,
              ci_recogido: this.form.fue_recogido ? this.form.ci_recogido : null,
              recogido_en_dia: this.form.fue_recogido ? this.form.recogido_en_dia : null,
            }
          })
        }

        this.dialog = false
        this.$alert.success('Recogido actualizado para toda el area')
      } catch (err) {
        this.$alert.error(err.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.saving = false
      }
    },
  },
}
</script>

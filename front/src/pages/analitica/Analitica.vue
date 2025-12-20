<template>
  <q-page class="q-pa-sm">
    <!-- HEADER / FILTROS -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row items-center q-col-gutter-xs">
        <div class="col-12 col-sm-3">
          <div class="text-subtitle2">{{$store.user.area?.name}}</div>
<!--          <div class="text-caption text-grey-7">-->
<!--            Solicitudes recibidas de Preanalítica (estado ENVIADO_ANALITICA)-->
<!--          </div>-->
        </div>

        <div class="col-12 col-sm-4">
          <q-input
            v-model="filter"
            dense
            outlined
            debounce="400"
            label="Buscar (paciente / CI / establecimiento)"
          >
            <template #prepend>
              <q-icon name="search" />
            </template>
            <template #append>
              <q-btn
                flat
                round
                dense
                icon="clear"
                @click="clearFilter"
                v-if="filter"
              />
            </template>
          </q-input>
        </div>
<!--        inout fecha-->
        <div class="col-12 col-sm-3">
          <q-input
            v-model="fecha"
            type="date"
            dense
            outlined
            label="Fecha de Solicitud"
          >
            <template #prepend>
              <q-icon name="event" />
            </template>
          </q-input>
        </div>

        <div class="col-12 col-sm-2 text-right">
          <q-btn
            color="primary"
            icon="search"
            label="Buscar"
            no-caps
            :loading="loading"
            @click="analiticaGet()"
          />
        </div>
        <div class="col-12">
          <q-markup-table dense wrap-cells flat bordered>
            <thead>
            <tr class="bg-primary text-white" >
              <th>Opciones</th>
              <th>Id</th>
              <th>Paciente</th>
              <th>CI</th>
              <th>Establecimiento</th>
              <th>Fecha Solicitud</th>
              <th>Estado</th>
              <th>Servicios</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="solicitud in solicitudes" :key="solicitud.id" style="cursor: pointer;">
              <td>
                <q-btn-dropdown dense color="primary" no-caps label="Opciones" size="10px">
                  <q-list>
                    <!-- HEMATOLOGÍA -->
                    <q-item clickable @click="selectHematologia(solicitud)" v-close-popup dense>
                      <q-item-section avatar><q-icon name="bloodtype" /></q-item-section>
                      <q-item-section>Hematología</q-item-section>
                    </q-item>

                    <q-item clickable @click="printHematologia(solicitud)" v-close-popup dense v-if="solicitud.hematologia?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Hematología</q-item-section>
                    </q-item>

                    <q-separator spaced />

                    <q-item clickable @click="$router.push({ name: 'analitica-quimica-sanguinia', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="science" /></q-item-section>
                      <q-item-section>Química Sanguínea</q-item-section>
                    </q-item>
                    <q-item clickable @click="printQuimica(solicitud)" v-close-popup dense v-if="solicitud.quimica_sanguinea?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Química Sanguínea</q-item-section>
                    </q-item>
                    <q-separator spaced />

                    <q-item clickable @click="$router.push({ name: 'analitica-uroanalisis', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="water_drop" /></q-item-section>
                      <q-item-section>
                        Uroanálisis
<!--                        <pre>{{solicitud.uroanalisis.code}}</pre>-->
                      </q-item-section>
                    </q-item>

                    <q-item clickable @click="printUroanalisis(solicitud)" v-close-popup dense v-if="solicitud.uroanalisis?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Uroanálisis</q-item-section>
                    </q-item>

                    <q-separator spaced />

                    <q-item clickable @click="$router.push({ name: 'analitica-parasitologia', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="bug_report" /></q-item-section>
                      <q-item-section>Parasitología</q-item-section>
                    </q-item>

                    <q-item clickable @click="printParasitologia(solicitud)" v-close-popup dense v-if="solicitud.parasitologia?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Parasitología</q-item-section>
                    </q-item>
<!--                    {-->
<!--                    path: '/analitica/papiloma-humano/:id',-->
<!--                    name: 'analitica-papiloma-humano',-->
<!--                    component: () => import('pages/analitica/PapilomaHumano.vue'),-->
<!--                    meta: {requiresAuth: true, perm: 'Analitica'}-->
<!--                    },-->
                    <q-separator spaced />

                    <q-item clickable @click="$router.push({ name: 'analitica-papiloma-humano', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="health_and_safety" /></q-item-section>
                      <q-item-section>Papiloma Humano</q-item-section>
                    </q-item>
                    <q-item clickable @click="printPapilomaHumano(solicitud)" v-close-popup dense v-if="solicitud.papilomaHumano?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Papiloma Humano</q-item-section>
                    </q-item>
<!--                    {-->
<!--                    path: '/analitica/panel-respiratorio/:id',-->
<!--                    name: 'analitica-panel-respiratorio',-->
<!--                    component: () => import('pages/analitica/PanelRespiratorio.vue'),-->
<!--                    meta: {requiresAuth: true, perm: 'Analitica'}-->
<!--                    },-->
                    <q-separator spaced />

                    <q-item clickable @click="$router.push({ name: 'analitica-panel-respiratorio', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="air" /></q-item-section>
                      <q-item-section>Panel Respiratorio</q-item-section>
                    </q-item>
                    <q-item clickable @click="printPanelRespiratorio(solicitud)" v-close-popup dense v-if="solicitud.panelRespiratorio?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Panel Respiratorio</q-item-section>
                    </q-item>
<!--                    {-->
<!--                    path: '/analitica/panel-sexual/:id',-->
<!--                    name: 'analitica-panel-sexual',-->
<!--                    component: () => import('pages/analitica/PanelSexual.vue'),-->
<!--                    meta: {requiresAuth: true, perm: 'Analitica'}-->
<!--                    },-->
                    <q-separator spaced />

                    <q-item clickable @click="$router.push({ name: 'analitica-panel-sexual', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="favorite" /></q-item-section>
                      <q-item-section>Panel Sexual</q-item-section>
                    </q-item>
                    <q-item clickable @click="printPanelSexual(solicitud)" v-close-popup dense v-if="solicitud.panelSexual?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Panel Sexual</q-item-section>
                    </q-item>
<!--                    {-->
<!--                    path: '/analitica/cultivo-antibiograma/:id',-->
<!--                    name: 'analitica-cultivo-antibiograma',-->
<!--                    component: () => import('pages/analitica/CultivoAntibiograma.vue'),-->
<!--                    meta: {requiresAuth: true, perm: 'Analitica'}-->
<!--                    },-->
                    <q-separator spaced />

                    <q-item clickable @click="$router.push({ name: 'analitica-cultivo-antibiograma', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="healing" /></q-item-section>
                      <q-item-section>Cultivo y Antibiograma</q-item-section>
                    </q-item>
                    <q-item clickable @click="printCultivoAntibiograma(solicitud)" v-close-popup dense v-if="solicitud.cultivoAntibiograma?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Cultivo y Antibiograma</q-item-section>
                    </q-item>
<!--                    {-->
<!--                    path: '/analitica/inmunologia/:id',-->
<!--                    name: 'analitica-inmunologia',-->
<!--                    component: () => import('pages/analitica/InmunologiaSolicitudPage.vue'),-->
<!--                    meta: {requiresAuth: true, perm: 'Analitica'}-->
<!--                    },-->
                    <q-separator spaced />

                    <q-item clickable @click="$router.push({ name: 'analitica-inmunologia', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="shield" /></q-item-section>
                      <q-item-section>Inmunología</q-item-section>
                    </q-item>
                    <q-item clickable @click="printInmunologia(solicitud)" v-close-popup dense v-if="solicitud.inmunologia?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Inmunología</q-item-section>
                    </q-item>
                  </q-list>
                </q-btn-dropdown>
              </td>
              <td>{{ solicitud.id }}</td>
              <td>{{ solicitud.paciente_nombre }}</td>
              <td>{{ solicitud.paciente_ci }}</td>
              <td>{{ solicitud.establecimiento_salud }}</td>
              <td>{{ solicitud.fecha_envio_analitica }}</td>
              <td>
                <q-chip v-if="solicitud.estado === 'FINALIZADO'" color="green" text-color="white" dense>
                  Finalizado
                </q-chip>
                <q-chip v-else-if="solicitud.estado === 'ENVIADO_ANALITICA'" color="red" text-color="white" dense>
                  Recibido
                </q-chip>
              </td>
              <td>
                <ul style="padding-left: 1em; margin: 0;">
                  <li v-for="servicio in solicitud.servicios" :key="servicio.id">
                    {{ servicio.nombre }} - {{ servicio.precio }}
                  </li>
                </ul>
              </td>
            </tr>
            </tbody>
          </q-markup-table>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'AreaAnaliticaListPage',
  data () {
    return {
      fecha: moment().format('YYYY-MM-DD'),
      solicitudes: [],
      loading: false,
      filter: '',
    }
  },
  computed: {
  },
  mounted () {
    this.analiticaGet()
    if (!this.$store.socketAnalitica) {
      this.$store.socketAnalitica = true
      this.$socket.on('silSolicitud', msg => {
        this.$alert.info('Nueva solicitud de analítica recibido.')
        this.analiticaGet()
      })
    }
  },
  methods: {
    printQuimica (solicitud) {
      const url = `${this.$axios.defaults.baseURL}/quimica-sanguinea/solicitud/${solicitud.quimica_sanguinea?.code}/pdf`
      window.open(url, '_blank')
    },
    printHematologia(solicitud) {
      // $query = Solicitude::with([
      //   'paciente', 'doctor', 'servicios.area.rangos', 'resultados',
      //   'hematologia',
      //   'quimicaSanguinea',
      //   'uroanalisis',
      //   'parasitologia',
      //   'papilomaHumano',
      //   'panelRespiratorio',
      //   'panelSexual',
      //   'cultivoAntibiograma',
      // ])
      const url = `${this.$axios.defaults.baseURL}/hematologia/solicitud/${solicitud.hematologia?.code}/pdf`
      window.open(url, '_blank')
    },
    printUroanalisis (solicitud) {
      const url = `${this.$axios.defaults.baseURL}/uroanalisis/solicitud/${solicitud.uroanalisis?.code}/pdf`
      window.open(url, '_blank')
    },
    printParasitologia(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/parasitologia/solicitud/${solicitud.parasitologia?.code}/pdf`
      window.open(url, '_blank')
    },
    printPapilomaHumano(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/papiloma-humano/solicitud/${solicitud.papilomaHumano?.code}/pdf`
      window.open(url, '_blank')
    },
    printPanelRespiratorio(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/panel-respiratorio/solicitud/${solicitud.panelRespiratorio?.code}/pdf`
      window.open(url, '_blank')
    },
    printPanelSexual(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/panel-sexual/solicitud/${solicitud.panelSexual?.code}/pdf`
      window.open(url, '_blank')
    },
    printCultivoAntibiograma(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/cultivo-antibiograma/solicitud/${solicitud.cultivoAntibiograma?.code}/pdf`
      window.open(url, '_blank')
    },
    printInmunologia(solicitud) {
      // const url = `${this.$axios.defaults.baseURL}/inmunologia/solicitud/${solicitud.id}/pdf`
      // window.open(url, '_blank')
      // http://localhost:8000/api/inmunologia/solicitud/3/pdf-all?area_id=5
      const url = `${this.$axios.defaults.baseURL}/inmunologia/solicitud/${solicitud.id}/pdf-all?area_id=5`
      window.open(url, '_blank')
    },
    selectHematologia(solicitud) {
      this.$router.push({ name: 'analitica-hematologia', params: { id: solicitud.id } })
    },
    selectRow(solicitud) {
      this.$router.push({ name: 'analitica-detalle', params: { id: solicitud.id } })
    },
    clearFilter () {
      this.filter = ''
      this.analiticaGet()
    },
    async analiticaGet () {
      this.loading = true
      try {
        const params = {
          filter: this.filter,
          fecha: this.fecha,
        }
        const response = await this.$axios.get('/solicitudesAnalitica', { params })
        this.solicitudes = response.data
      } catch (error) {
        this.$alert.error('Error al cargar las solicitudes de analítica.')
      } finally {
        this.loading = false
      }
    },
  }
}
</script>

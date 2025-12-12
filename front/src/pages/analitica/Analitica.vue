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
<!--                btn dropdown-->
                <q-btn-dropdown
                  dense
                  color="primary"
                  no-caps
                  label="Opciones"
                  size="10px"
                >
                  <q-list>
<!--                    HEMATOLOGÍA-->
                    <q-item clickable @click="selectHematologia(solicitud)" v-close-popup>
                      <q-item-section avatar>
                        <q-icon name="bloodtype" />
                      </q-item-section>
                      <q-item-section>
                        Hematología
                      </q-item-section>
                    </q-item>
                    <q-item clickable @click="printHematologia(solicitud)" v-close-popup>
                      <q-item-section avatar>
                        <q-icon name="print" />
                      </q-item-section>
                      <q-item-section>
                        Imprimir Hematología
                      </q-item-section>
                    </q-item>
                    <q-item clickable @click="$router.push({ name: 'analitica-quimica-sanguinia', params: { id: solicitud.id } })" v-close-popup>
                      <q-item-section avatar>
                        <q-icon name="science" />
                      </q-item-section>
                      <q-item-section>
                        Química Sanguínea
                      </q-item-section>
                    </q-item>
<!--                    {-->
<!--                    path: '/analitica/uroanalisis/:id',-->
<!--                    name: 'analitica-uroanalisis',-->
<!--                    component: () => import('pages/analitica/Uroanalisis.vue'),-->
<!--                    meta: { requiresAuth: true, perm: 'Analitica' }-->
<!--                    },-->
                    <q-item clickable @click="$router.push({ name: 'analitica-uroanalisis', params: { id: solicitud.id } })" v-close-popup>
                      <q-item-section avatar>
                        <q-icon name="water_drop" />
                      </q-item-section>
                      <q-item-section>
                        Uroanálisis
                      </q-item-section>
                    </q-item>
                  </q-list>
                  <q-item clickable @click="$router.push({ name: 'analitica-parasitologia', params: { id: solicitud.id } })" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="bug_report" />
                    </q-item-section>
                    <q-item-section>
                      Parasitología
                    </q-item-section>
                  </q-item>
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
    printHematologia(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/hematologia/solicitud/${solicitud.id}/pdf`
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

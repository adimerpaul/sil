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
            <tr class="bg-primary text-white">
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
            <tr v-for="solicitud in solicitudes" :key="solicitud.id">
              <td>{{ solicitud.id }}</td>
              <td>{{ solicitud.paciente_nombre }}</td>
              <td>{{ solicitud.paciente_ci }}</td>
              <td>{{ solicitud.establecimiento_salud }}</td>
              <td>{{ solicitud.fecha_envio_analitica }}</td>
              <td>{{ solicitud.estado }}</td>
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
<!--      <pre>{{solicitudes}}</pre>-->
<!--      [-->
<!--      {-->
<!--      "id": 5,-->
<!--      "paciente_id": 3,-->
<!--      "doctor_id": null,-->
<!--      "codigo_solicitud": null,-->
<!--      "tipo_atencion": "SI",-->
<!--      "tipo_otro": null,-->
<!--      "fecha_solicitud": "2025-12-09",-->
<!--      "hora_solicitud": "02:47:00",-->
<!--      "establecimiento_salud": "Hospital General",-->
<!--      "zona_establecimiento": null,-->
<!--      "diagnostico_clinico": null,-->
<!--      "estado": "ENVIADO_ANALITICA",-->
<!--      "codigo": 5,-->
<!--      "nro_registro": "GER100789",-->
<!--      "fecha_creacion": "2025-12-09 02:48:08",-->
<!--      "fecha_pre_analitica": "2025-12-09 02:48:23",-->
<!--      "fecha_envio_analitica": "2025-12-09 02:48:27",-->
<!--      "fecha_aceptacion_analitica": null,-->
<!--      "fecha_finalizacion": null,-->
<!--      "sala": null,-->
<!--      "cama": null,-->
<!--      "paciente_nombre": "Giovana Evelyn Ramirez",-->
<!--      "paciente_ci": "67890",-->
<!--      "paciente_telefono": "567890",-->
<!--      "paciente_direccion": "calle x",-->
<!--      "paciente_fecha_nac": "1989-07-10",-->
<!--      "paciente_genero": "F",-->
<!--      "paciente_edad": 36,-->
<!--      "doctor_nombre": null,-->
<!--      "doctor_especialidad": null,-->
<!--      "doctor_ci": null,-->
<!--      "doctor_telefono": null,-->
<!--      "doctor_email": null,-->
<!--      "doctor_registro": null,-->
<!--      "establecimiento_id": null,-->
<!--      "user_id": 2,-->
<!--      "user_preanalitica_id": 2,-->
<!--      "user_analitica_id": null,-->
<!--      "muestra_sangre_entera": null,-->
<!--      "muestra_coagulo": null,-->
<!--      "muestra_volumen": null,-->
<!--      "muestra_identificacion": null,-->
<!--      "muestra_equipo": null,-->
<!--      "paciente": {-->
<!--      "id": 3,-->
<!--      "fecha_recepcion": null,-->
<!--      "hora_recepcion": null,-->
<!--      "nombre_completo": "Giovana Evelyn Ramirez",-->
<!--      "fecha_nac": "1989-07-10",-->
<!--      "genero": "F",-->
<!--      "edad": 36,-->
<!--      "ci": "67890",-->
<!--      "telefono": "567890",-->
<!--      "direccion": "calle x",-->
<!--      "discapacidad": 0,-->
<!--      "discapacidad_cual": null,-->
<!--      "discapacidad_otro": null,-->
<!--      "embarazo": 0,-->
<!--      "fum": null,-->
<!--      "sem_gest": null-->
<!--      },-->
<!--      "doctor": null,-->
<!--      "servicios": [-->
<!--      {-->
<!--      "id": 84,-->
<!--      "area_id": 5,-->
<!--      "codigo": 86,-->
<!--      "nombre": "CHAGAS IgG (EN SUERO)",-->
<!--      "descripcion": null,-->
<!--      "metodo": "ELISA",-->
<!--      "subarea": "Infecciosos",-->
<!--      "precio": "70.00",-->
<!--      "estado": "ACTIVO",-->
<!--      "pivot": {-->
<!--      "solicitude_id": 5,-->
<!--      "servicio_id": 84,-->
<!--      "precio": "70.00",-->
<!--      "area_id": 5,-->
<!--      "nombre": "CHAGAS IgG (EN SUERO)",-->
<!--      "created_at": "2025-12-09T06:48:08.000000Z",-->
<!--      "updated_at": "2025-12-09T06:48:08.000000Z"-->
<!--      },-->
<!--      "area": {-->
<!--      "id": 5,-->
<!--      "name": "INMUNOLOGÍA / INFECCIOSOS (Area 6)",-->
<!--      "descripcion": "INMUNOLOGÍA / INFECCIOSOS (Area 6)",-->
<!--      "estado": "ACTIVO",-->
<!--      "rangos": []-->
<!--      }-->
<!--      }-->
<!--      ],-->
<!--      "resultados": []-->
<!--      }-->
<!--      ]-->
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
  },
  methods: {
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

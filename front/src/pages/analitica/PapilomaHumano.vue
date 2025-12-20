<template>
  <q-page class="q-pa-sm bg-grey-2">
    <q-card flat bordered>
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6 text-weight-bold">
            Virus del Papiloma Humano (PCR)
          </div>
          <div class="text-caption text-grey-7">
            Detección de HPV de alto riesgo por PCR en tiempo real
          </div>
        </div>
        <q-space />
        <q-btn flat icon="arrow_back" label="Volver" @click="$router.back()" />
        <q-btn color="primary" icon="save" label="Guardar" @click="save" :loading="loading" />
        <q-btn
          class="q-ml-sm"
          outline
          color="primary"
          icon="print"
          label="Imprimir"
          no-caps
          :disable="!solicitud"
          @click="printPdf"
        />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-sm">
<!--        <pre>{{solicitud}}</pre>-->
<!--        {-->
<!--        "id": 3,-->
<!--        "paciente_id": 2,-->
<!--        "doctor_id": null,-->
<!--        "codigo_solicitud": null,-->
<!--        "tipo_atencion": "SI",-->
<!--        "tipo_otro": null,-->
<!--        "fecha_solicitud": "2025-12-15",-->
<!--        "hora_solicitud": "03:07:00",-->
<!--        "establecimiento_salud": "Hospital General",-->
<!--        "zona_establecimiento": null,-->
<!--        "diagnostico_clinico": null,-->
<!--        "diagnostico_select": null,-->
<!--        "estado": "ENVIADO_ANALITICA",-->
<!--        "codigo": 3,-->
<!--        "nro_registro": "ACA020489",-->
<!--        "fecha_creacion": "2025-12-15 03:08:07",-->
<!--        "fecha_pre_analitica": "2025-12-15 03:08:14",-->
<!--        "fecha_envio_analitica": "2025-12-15 03:08:20",-->
<!--        "fecha_aceptacion_analitica": null,-->
<!--        "fecha_finalizacion": null,-->
<!--        "sala": null,-->
<!--        "cama": null,-->
<!--        "paciente_nombre": "Adimer Paul Chambi Ajata",-->
<!--        "paciente_ci": "7336199",-->
<!--        "paciente_telefono": "69603027",-->
<!--        "paciente_direccion": "Av. Siempre Viva 742",-->
<!--        "paciente_fecha_nac": "1989-04-02",-->
<!--        "paciente_genero": "M",-->
<!--        "paciente_edad": 36,-->
<!--        "doctor_nombre": null,-->
<!--        "doctor_especialidad": null,-->
<!--        "doctor_ci": null,-->
<!--        "doctor_telefono": null,-->
<!--        "doctor_email": null,-->
<!--        "doctor_registro": null,-->
<!--        "establecimiento_id": null,-->
<!--        "user_id": 1,-->
<!--        "user_preanalitica_id": 1,-->
<!--        "user_analitica_id": null,-->
<!--        "muestra_sangre_entera": null,-->
<!--        "muestra_coagulo": null,-->
<!--        "muestra_volumen": null,-->
<!--        "muestra_identificacion": null,-->
<!--        "muestra_equipo": null,-->
<!--        "paciente": {-->
<!--        "id": 2,-->
<!--        "fecha_recepcion": "2025-12-13",-->
<!--        "hora_recepcion": "06:38:39",-->
<!--        "nombre_completo": "Adimer Paul Chambi Ajata",-->
<!--        "fecha_nac": "1989-04-02",-->
<!--        "genero": "M",-->
<!--        "edad": 36,-->
<!--        "ci": "7336199",-->
<!--        "telefono": "69603027",-->
<!--        "direccion": "Av. Siempre Viva 742",-->
<!--        "discapacidad": 0,-->
<!--        "discapacidad_cual": null,-->
<!--        "discapacidad_otro": null,-->
<!--        "embarazo": 0,-->
<!--        "fum": null,-->
<!--        "sem_gest": null-->
<!--        },-->
<!--        "doctor": null-->
<!--        }-->
<!--        <div class="row q-col-gutter-sm text-caption">-->
<!--          <div class="col-12 col-md-4">-->
<!--            <div class="text-grey-7">Paciente</div>-->
<!--            <div class="text-body2 text-weight-medium">-->
<!--              {{ pacienteNombre }}-->
<!--            </div>-->
<!--            <div class="text-grey-7 q-mt-xs">-->
<!--              Edad: <b>{{ pacienteEdad }}</b> • Género:-->
<!--              <b>{{ pacienteGenero }}</b>-->
<!--            </div>-->
<!--          </div>-->

<!--          <div class="col-12 col-md-4">-->
<!--            <div class="text-grey-7">Médico solicitante</div>-->
<!--            <div class="text-body2 text-weight-medium">-->
<!--              {{ doctorNombre }}-->
<!--            </div>-->
<!--            <div class="text-grey-7 q-mt-xs">-->
<!--              Fecha solicitud:-->
<!--              <b>{{ solicitudFecha }}</b>-->
<!--            </div>-->
<!--          </div>-->

<!--          <div class="col-12 col-md-4">-->
<!--            <div class="text-grey-7">Solicitud</div>-->
<!--            <div class="row items-center q-col-gutter-xs q-mt-xs">-->
<!--              <div class="col-auto">-->
<!--                <q-chip square color="primary" text-color="white" dense>-->
<!--                  N° {{ solicitudCodigo }}-->
<!--                </q-chip>-->
<!--              </div>-->
<!--              <div class="col-auto">-->
<!--                <q-chip square outline color="primary" class="badge-estado" dense>-->
<!--                  {{ solicitudEstado }}-->
<!--                </q-chip>-->
<!--              </div>-->
<!--            </div>-->
<!--          </div>-->
<!--        </div>-->
        <div class="row q-col-gutter-sm text-caption">
          <div class="col-12 col-md-4">
            <div class="text-grey-7">Paciente</div>
            <div class="text-body2 text-weight-medium">
              {{ solicitud?.paciente_nombre }}
            </div>
            <div class="text-grey-7 q-mt-xs">
              Edad: <b>{{ solicitud?.paciente_edad }}</b> • Género:
              <b>{{ solicitud?.paciente_genero }}</b>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="text-grey-7">Médico solicitante</div>
            <div class="text-body2 text-weight-medium">
              {{ solicitud?.doctor_nombre || 'N/A' }}
            </div>
            <div class="text-grey-7 q-mt-xs">
              Fecha solicitud:
              <b>{{ solicitud?.fecha_solicitud }}</b>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="text-grey-7">Solicitud</div>
            <div class="row items-center q-col-gutter-xs q-mt-xs">
              <div class="col-auto">
                <q-chip square color="primary" text-color="white" dense>
                  N° {{ solicitud?.codigo }}
                </q-chip>
              </div>
              <div class="col-auto">
                <q-chip square outline color="primary" class="badge-estado" dense>
                  {{ solicitud?.estado }}
                </q-chip>
              </div>
            </div>
          </div>
        </div>
      </q-card-section>

      <q-card-section>
        <q-markup-table dense bordered>
          <thead>
          <tr>
            <th>Determinación</th>
            <th>Resultado</th>
            <th>Valor de referencia</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td>HPV alto riesgo</td>
            <td>
              <q-select v-model="form.hpv_alto_riesgo"
                        :options="opciones" dense outlined />
            </td>
            <td>NO DETECTADO</td>
          </tr>
          <tr>
            <td>HPV 16</td>
            <td><q-select v-model="form.hpv_16" :options="opciones" dense outlined /></td>
            <td>NO DETECTADO</td>
          </tr>
          <tr>
            <td>HPV 18</td>
            <td><q-select v-model="form.hpv_18" :options="opciones" dense outlined /></td>
            <td>NO DETECTADO</td>
          </tr>
          <tr>
            <td>HPV 45</td>
            <td><q-select v-model="form.hpv_45" :options="opciones" dense outlined /></td>
            <td>NO DETECTADO</td>
          </tr>
          </tbody>
        </q-markup-table>

        <div class="q-mt-md">
          <div class="text-subtitle2 q-mb-xs">Observaciones</div>
          <q-input v-model="form.observaciones" type="textarea" outlined autogrow />
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: 'PapilomaHumanoPage',
  data () {
    return {
      loading: false,
      opciones: ['NO DETECTADO', 'DETECTADO'],
      solicitud: null,
      form: {
        hpv_alto_riesgo: 'NO DETECTADO',
        hpv_16: 'NO DETECTADO',
        hpv_18: 'NO DETECTADO',
        hpv_45: 'NO DETECTADO',
        observaciones: ''
      }
    }
  },
  mounted () {
    this.load()
  },
  methods: {
    async load () {
      this.loading = true
      const { data } = await this.$axios.get(
        `/papiloma-humano/solicitud/${this.$route.params.id}`
      )
      // Object.assign(this.form, data.papiloma)
      this.form = data.papiloma
      this.solicitud = data.solicitud

      this.loading = false
    },
    async save () {
      this.loading = true
      await this.$axios.post(
        `/papiloma-humano/solicitud/${this.$route.params.id}`,
        this.form
      )
      this.$alert.success('Resultado guardado correctamente')
      this.loading = false
    },
    printPdf() {
      const code = this.form.code
      const url = `${this.$axios.defaults.baseURL}/papiloma-humano/solicitud/${code}/pdf`
      window.open(url, '_blank')
    }
  }
}
</script>

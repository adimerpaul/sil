<template>
  <q-page class="q-pa-sm bg-grey-2">
    <!-- ENCABEZADO -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row items-center q-col-gutter-sm">
        <div class="col">
          <div class="text-h6 text-weight-bold">Uroanálisis</div>
          <div class="text-caption text-grey-7">
            Examen general de orina: examen físico, microscópico y químico.
          </div>
        </div>

        <div class="col-auto">
          <q-btn
            flat
            icon="refresh"
            label="Refrescar"
            no-caps
            class="q-mr-sm"
            :disable="loading"
            @click="load"
          />
          <q-btn
            flat
            icon="arrow_back"
            label="Volver"
            no-caps
            class="q-mr-xs"
            @click="$router.back()"
          />
          <q-btn
            color="primary"
            icon="save"
            label="Guardar"
            no-caps
            :loading="loading"
            @click="onSubmit"
          />
        </div>
      </q-card-section>

      <q-separator />

      <!-- DATOS DE SOLICITUD / PACIENTE -->
      <q-card-section v-if="header" class="q-pa-sm">
        <div class="row q-col-gutter-sm text-caption">
          <div class="col-12 col-md-4">
            <div class="text-grey-7">Paciente</div>
            <div class="text-body2 text-weight-medium">
              {{ pacienteNombre }}
            </div>
            <div class="text-grey-7 q-mt-xs">
              Edad: <b>{{ pacienteEdad }}</b>
              • Género: <b>{{ pacienteGenero }}</b>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="text-grey-7">Médico solicitante</div>
            <div class="text-body2 text-weight-medium">
              {{ doctorNombre }}
            </div>
            <div class="text-grey-7 q-mt-xs">
              Fecha solicitud: <b>{{ solicitudFecha }}</b>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="text-grey-7">Solicitud</div>
            <div class="row items-center q-col-gutter-xs q-mt-xs">
              <div class="col-auto">
                <q-chip square color="primary" text-color="white" dense>
                  N° {{ solicitudCodigo }}
                </q-chip>
              </div>
              <div class="col-auto">
                <q-chip
                  square
                  outline
                  color="primary"
                  class="badge-estado"
                  dense
                >
                  {{ solicitudEstado }}
                </q-chip>
              </div>
            </div>
          </div>
        </div>
      </q-card-section>

      <q-inner-loading :showing="loading && !formLoaded">
        <q-spinner size="42px" />
      </q-inner-loading>
    </q-card>

    <!-- FORMULARIO PRINCIPAL -->
    <q-card flat bordered>
      <q-card-section class="q-pa-sm">
        <q-form @submit.prevent="onSubmit">
          <!-- MATERIAL / MÉTODO -->
          <div class="row q-col-gutter-sm q-mb-md">
            <div class="col-12 col-sm-6">
              <div class="section-title q-mb-xs">Material de ensayo</div>
              <q-input
                v-model="form.material_ensayo"
                dense
                outlined
                class="bg-white"
                placeholder="ORINA"
              />
            </div>
            <div class="col-12 col-sm-6">
              <div class="section-title q-mb-xs">Método</div>
              <q-input
                v-model="form.metodo"
                dense
                outlined
                class="bg-white"
                placeholder="Manual / Microscópico / Tira reactiva"
              />
            </div>
          </div>

          <div class="row q-col-gutter-md">
            <!-- COLUMNA IZQUIERDA: FÍSICO + MICROSCÓPICO + OTROS -->
            <div class="col-12 col-md-7">
              <!-- EXAMEN FÍSICO -->
              <div class="section-title q-mb-xs">
                Examen físico
              </div>
              <q-markup-table
                dense
                flat
                bordered
                square
                class="bg-white q-mb-md"
              >
                <thead>
                <tr>
                  <th class="text-left">Examen</th>
                  <th class="text-left">Res.</th>
                  <th class="text-left">Unidades</th>
                  <th class="text-left">Rango</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td>Cantidad</td>
                  <td>
                    <q-input
                      v-model.number="form.cantidad"
                      dense
                      outlined
                      type="number"
                      step="0.1"
                      input-class="text-right"
                    />
                  </td>
                  <td>ml</td>
                  <td>*</td>
                </tr>
                <tr>
                  <td>Color</td>
                  <td>
                    <q-select
                      v-model="form.color"
                      :options="colorOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                  <td>*</td>
                  <td>Amarillo</td>
                </tr>
                <tr>
                  <td>Olor</td>
                  <td>
                    <q-select
                      v-model="form.olor"
                      :options="olorOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                  <td>*</td>
                  <td>Sui-generis</td>
                </tr>
                <tr>
                  <td>Aspecto</td>
                  <td>
                    <q-select
                      v-model="form.aspecto"
                      :options="aspectoOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                  <td>*</td>
                  <td>Límpido</td>
                </tr>
                <tr>
                  <td>Reacción (pH)</td>
                  <td>
                    <q-input
                      v-model="form.reaccion"
                      dense
                      outlined
                      placeholder="pH 6.0 ácido"
                    />
                  </td>
                  <td>*</td>
                  <td>pH 6.0 ácido</td>
                </tr>
                <tr>
                  <td>Densidad</td>
                  <td>
                    <q-input
                      v-model.number="form.densidad"
                      dense
                      outlined
                      type="number"
                      step="0.001"
                      input-class="text-right"
                    />
                  </td>
                  <td>mmHg</td>
                  <td>1.025</td>
                </tr>
                <tr>
                  <td>Espuma</td>
                  <td>
                    <q-select
                      v-model="form.espuma"
                      :options="espumaOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                  <td>*</td>
                  <td>Blanco fugaz</td>
                </tr>
                <tr>
                  <td>Sedimento</td>
                  <td>
                    <q-select
                      v-model="form.sedimento"
                      :options="sedimentoOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                  <td>*</td>
                  <td>Escaso</td>
                </tr>
                </tbody>
              </q-markup-table>

              <!-- EXAMEN MICROSCÓPICO (SEDIMENTO) -->
              <div class="section-title q-mb-xs">
                Examen microscópico (sedimento)
              </div>
              <q-markup-table
                dense
                flat
                bordered
                square
                class="bg-white q-mb-md"
              >
                <thead>
                <tr>
                  <th class="text-left">Examen</th>
                  <th class="text-left">Sedimento</th>
                  <th class="text-left">Unidades</th>
                  <th class="text-left">Rango</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td>Células epiteliales</td>
                  <td>
                    <q-input
                      v-model="form.celulas_epiteliales"
                      dense
                      outlined
                      input-class="text-right"
                      placeholder="0-1"
                    />
                  </td>
                  <td>xcampo/uL</td>
                  <td>0-1</td>
                </tr>
                <tr>
                  <td>Leucocitos</td>
                  <td>
                    <q-input
                      v-model="form.leucocitos"
                      dense
                      outlined
                      input-class="text-right"
                      placeholder="0-1"
                    />
                  </td>
                  <td>xcampo/uL</td>
                  <td>0-1</td>
                </tr>
                <tr>
                  <td>Hematies</td>
                  <td>
                    <q-input
                      v-model="form.hematies"
                      dense
                      outlined
                      input-class="text-right"
                    />
                  </td>
                  <td>xcampo/uL</td>
                  <td>0-1</td>
                </tr>
                <tr>
                  <td>Bacterias</td>
                  <td>
                    <q-input
                      v-model="form.bacterias"
                      dense
                      outlined
                      placeholder="Escaso / ++ / +++"
                    />
                  </td>
                  <td>xcampo/uL</td>
                  <td>Escaso</td>
                </tr>
                <tr>
                  <td>Filamento mucoide</td>
                  <td>
                    <q-input
                      v-model="form.filamento_mucoide"
                      dense
                      outlined
                      placeholder="Escaso / ++ / +++"
                    />
                  </td>
                  <td>xcampo/uL</td>
                  <td>*</td>
                </tr>
                <tr>
                  <td>Cilindros</td>
                  <td>
                    <q-input
                      v-model="form.cilindros"
                      dense
                      outlined
                      placeholder="#"
                    />
                  </td>
                  <td>xcampo/uL</td>
                  <td>#</td>
                </tr>
                <tr>
                  <td>Células</td>
                  <td>
                    <q-input
                      v-model="form.celulas"
                      dense
                      outlined
                      placeholder="#"
                    />
                  </td>
                  <td>xcampo/uL</td>
                  <td>#</td>
                </tr>
                <tr>
                  <td>Cristales</td>
                  <td>
                    <q-input
                      v-model="form.cristales"
                      dense
                      outlined
                      placeholder="# / Fosfato amorfo / etc."
                    />
                  </td>
                  <td>xcampo/uL</td>
                  <td>#</td>
                </tr>
                </tbody>
              </q-markup-table>

              <!-- OTROS EXÁMENES -->
              <div class="section-title q-mb-xs">
                Otros exámenes
              </div>
              <q-markup-table
                dense
                flat
                bordered
                square
                class="bg-white q-mb-md"
              >
                <thead>
                <tr>
                  <th class="text-left">Examen</th>
                  <th class="text-left">Res.</th>
                  <th class="text-left">Unidades</th>
                  <th class="text-left">Rango</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td>Morfología eritrocitaria</td>
                  <td>
                    <q-input
                      v-model="form.morfologia_eritrocitaria"
                      dense
                      outlined
                      placeholder="NORMAL / Alteraciones..."
                    />
                  </td>
                  <td>xcampo/uL</td>
                  <td>*</td>
                </tr>
                </tbody>
              </q-markup-table>
            </div>

            <!-- COLUMNA DERECHA: EXAMEN QUÍMICO -->
            <div class="col-12 col-md-5">
              <div class="section-title q-mb-xs">
                Examen químico
              </div>
              <q-markup-table
                dense
                flat
                bordered
                square
                class="bg-white q-mb-md"
              >
                <thead>
                <tr>
                  <th class="text-left">Examen químico</th>
                  <th class="text-left">Res.</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td>Proteínas</td>
                  <td>
                    <q-select
                      v-model="form.proteinas"
                      :options="proteinasOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                </tr>
                <tr>
                  <td>Glucosa</td>
                  <td>
                    <q-select
                      v-model="form.glucosa"
                      :options="glucosaOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                </tr>
                <tr>
                  <td>Sangre</td>
                  <td>
                    <q-select
                      v-model="form.sangre"
                      :options="sangreOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                </tr>
                <tr>
                  <td>Cetonas</td>
                  <td>
                    <q-select
                      v-model="form.cetonas"
                      :options="cetonasOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                </tr>
                <tr>
                  <td>Bilirrubina</td>
                  <td>
                    <q-select
                      v-model="form.bilirrubina"
                      :options="bilirrubinaOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                </tr>
                <tr>
                  <td>Urobilinógeno</td>
                  <td>
                    <q-select
                      v-model="form.urobilinogeno"
                      :options="urobilinogenoOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                </tr>
                <tr>
                  <td>Nitritos</td>
                  <td>
                    <q-select
                      v-model="form.nitritos"
                      :options="nitritosOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </td>
                </tr>
                </tbody>
              </q-markup-table>

              <!-- OBSERVACIONES GENERALES -->
              <div class="section-title q-mb-xs">
                Observaciones
              </div>
              <q-input
                v-model="form.observaciones"
                type="textarea"
                dense
                outlined
                autogrow
                class="bg-white"
                placeholder="Observaciones clínicas, correlación con cuadro, etc."
              />
            </div>
          </div>

          <!-- BOTONES -->
          <div class="text-right q-mt-md">
            <q-btn
              flat
              label="Cancelar"
              no-caps
              class="q-mr-sm"
              :disable="loading"
              @click="$router.back()"
            />
            <q-btn
              color="primary"
              icon="save"
              label="Guardar"
              type="submit"
              no-caps
              :loading="loading"
            />
          </div>
        </q-form>
      </q-card-section>

      <q-inner-loading :showing="loading && formLoaded">
        <q-spinner size="42px" />
      </q-inner-loading>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: 'UroanalisisPage',

  data () {
    return {
      solicitudId: this.$route.params.id,
      loading: false,
      header: null,
      formLoaded: false,

      // Opciones para selects (puedes ajustarlas a tu gusto)
      colorOptions: ['Amarillo', 'Ámbar', 'Rojo', 'Pardo', 'Incoloro'],
      olorOptions: ['Sui-generis', 'Fétido'],
      aspectoOptions: ['Límpido', 'Turbio', 'Opalescente'],
      espumaOptions: ['Ausente', 'Escasa', 'Moderada', 'Abundante'],
      sedimentoOptions: ['Ausente', 'Escaso', 'Moderado', 'Abundante'],

      proteinasOptions: [
        'NO CONTIENE',
        'TRAZAS',
        'CONTIENE + (30 mg/dl)',
        'CONTIENE ++ (100 mg/dl)',
        'CONTIENE +++ (300 mg/dl)',
        'CONTIENE ++++ (mg/dl)'
      ],
      glucosaOptions: [
        'NO CONTIENE',
        'TRAZAS',
        'CONTIENE + (250 mg/dl)',
        'CONTIENE ++ (500 mg/dl)',
        'CONTIENE +++ (1000 mg/dl)',
        'CONTIENE ++++ (>=2000 mg/dl)'
      ],
      sangreOptions: ['NO CONTIENE', 'TRAZAS', 'POSITIVO +', 'POSITIVO ++', 'POSITIVO +++'],
      cetonasOptions: ['NO CONTIENE', 'TRAZAS', 'POSITIVO +', 'POSITIVO ++', 'POSITIVO +++'],
      bilirrubinaOptions: [
        'NO CONTIENE',
        'CONTIENE + (1 mg/dl)',
        'CONTIENE ++ (2 mg/dl)',
        'CONTIENE +++ (4 mg/dl)'
      ],
      urobilinogenoOptions: [
        'NORMAL (0.2 mg/dl)',
        '1 mg/dl',
        '2 mg/dl',
        '4 mg/dl'
      ],
      nitritosOptions: ['NEGATIVO', 'POSITIVO'],

      form: {
        material_ensayo: 'ORINA',
        metodo: 'MANUAL/MICROSCÓPICO/TIRA REACTIVA',
        cantidad: null,
        color: 'Amarillo',
        olor: 'Sui-generis',
        aspecto: 'Límpido',
        reaccion: 'pH 6.0 ácido',
        densidad: null,
        espuma: 'Blanco fugaz',
        sedimento: 'Escaso',
        celulas_epiteliales: null,
        leucocitos: null,
        hematies: null,
        bacterias: null,
        filamento_mucoide: null,
        cilindros: null,
        celulas: null,
        cristales: null,
        morfologia_eritrocitaria: 'NORMAL',
        proteinas: 'NO CONTIENE',
        glucosa: 'NO CONTIENE',
        sangre: 'NO CONTIENE',
        cetonas: 'NO CONTIENE',
        bilirrubina: 'NO CONTIENE',
        urobilinogeno: 'NORMAL (0.2 mg/dl)',
        nitritos: 'NEGATIVO',
        observaciones: ''
      }
    }
  },

  computed: {
    pacienteNombre () {
      const h = this.header
      if (!h) return '-'
      if (h.paciente && h.paciente.nombre_completo) return h.paciente.nombre_completo
      return h.paciente_nombre || '-'
    },
    pacienteEdad () {
      const h = this.header
      if (!h) return '-'
      if (h.paciente && h.paciente.edad) return h.paciente.edad
      return h.paciente_edad || '-'
    },
    pacienteGenero () {
      const h = this.header
      if (!h) return '-'
      if (h.paciente && h.paciente.genero) return h.paciente.genero
      return h.paciente_genero || '-'
    },
    doctorNombre () {
      const h = this.header
      if (!h) return '-'
      if (h.doctor && h.doctor.nombre) return h.doctor.nombre
      return h.doctor_nombre || '-'
    },
    solicitudFecha () {
      const h = this.header
      if (!h) return '-'
      return h.fecha_solicitud || '-'
    },
    solicitudCodigo () {
      const h = this.header
      if (!h) return '-'
      return h.nro_registro || h.codigo_solicitud || h.id || '-'
    },
    solicitudEstado () {
      const h = this.header
      if (!h) return '-'
      return h.estado || '-'
    }
  },

  mounted () {
    this.load()
  },

  methods: {
    async load () {
      try {
        this.loading = true
        this.formLoaded = false

        const { data } = await this.$axios.get(
          `/uroanalisis/solicitud/${this.solicitudId}`
        )

        this.header = data.solicitud || null
        this.form = Object.assign({}, this.form, data.uroanalisis || {})
        this.formLoaded = true
      } catch (e) {
        const msg = e.response?.data?.message || e.message
        if (this.$alert && this.$alert.error) {
          this.$alert.error('Error al cargar uroanálisis: ' + msg)
        } else {
          console.error(msg)
        }
      } finally {
        this.loading = false
      }
    },

    async save () {
      try {
        this.loading = true
        await this.$axios.post(
          `/uroanalisis/solicitud/${this.solicitudId}`,
          this.form
        )

        if (this.$alert && this.$alert.success) {
          this.$alert.success('Uroanálisis guardado correctamente')
        } else {
          console.log('Uroanálisis guardado correctamente')
        }
      } catch (e) {
        const msg = e.response?.data?.message || e.message
        if (this.$alert && this.$alert.error) {
          this.$alert.error('Error al guardar: ' + msg)
        } else {
          console.error(msg)
        }
      } finally {
        this.loading = false
      }
    },

    onSubmit () {
      this.save()
    }
  }
}
</script>

<style scoped>
.section-title {
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.badge-estado {
  font-size: 0.7rem;
  text-transform: uppercase;
}
.q-markup-table th {
  font-size: 0.75rem;
  background: #f5f5f5;
}
.q-markup-table td {
  font-size: 0.75rem;
}
.bg-white {
  background-color: #ffffff;
}
</style>

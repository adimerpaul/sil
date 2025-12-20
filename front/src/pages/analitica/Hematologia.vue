<template>
  <q-page class="q-pa-sm bg-grey-2">
    <!-- ENCABEZADO -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row items-center q-col-gutter-sm">
        <div class="col">
          <div class="text-h6 text-weight-bold">Hematología</div>
          <div class="text-caption text-grey-7">
            Hemograma, recuento diferencial, coagulograma y grupo sanguíneo
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
            icon="print"
            label="Imprimir"
            no-caps
            class="q-mr-sm"
            :disable="loading || !header"
            @click="printHematologia"
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

      <!-- DATOS SOLICITUD / PACIENTE (SIN COMPUTEDS, SIN FALLBACKS) -->
      <q-card-section v-if="header" class="q-pa-sm">
        <div class="row q-col-gutter-sm text-caption">
          <div class="col-12 col-md-4">
            <div class="text-grey-7">Paciente</div>
            <div class="text-body2 text-weight-medium">
              {{ header?.paciente?.nombre }}
            </div>
            <div class="text-grey-7 q-mt-xs">
              Edad: <b>{{ header?.paciente?.edad }}</b> • Género: <b>{{ header?.paciente?.genero }}</b>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="text-grey-7">Médico solicitante</div>
            <div class="text-body2 text-weight-medium">
              {{ header?.doctor?.nombre }}
            </div>
            <div class="text-grey-7 q-mt-xs">
              Fecha solicitud: <b>{{ header?.fecha_solicitud }}</b>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="text-grey-7">Solicitud</div>
            <div class="row items-center q-col-gutter-xs q-mt-xs">
              <div class="col-auto">
                <q-chip square color="primary" text-color="white" dense>
                  N° {{ header?.nro_registro }}
                </q-chip>
              </div>
              <div class="col-auto">
                <q-chip square outline color="primary" class="badge-estado" dense>
                  {{ header?.estado }}
                </q-chip>
              </div>
            </div>
          </div>

          <!-- LISTA SERVICIOS -->
          <div class="col-12">
            <ul class="q-pl-md q-mt-none">
              <li v-for="(s, index) in (header?.servicios || [])" :key="index">
                {{ s.nombre }}
              </li>
            </ul>
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
          <!-- HEMOGRAMA BÁSICO -->
          <div class="section-title q-mb-xs">Hemograma básico</div>

          <q-markup-table dense flat bordered square class="bg-white q-mb-md">
            <thead>
            <tr>
              <th class="text-left">Analito</th>
              <th class="text-left">Resultado</th>
              <th class="text-left">Rango de referencia</th>
              <th class="text-left">Unidad</th>
            </tr>
            </thead>

            <tbody>
            <tr v-if="canServicios(['HEMOGRAMA COMPLETO+ PLAQUETAS','MORFOLOGÍA DE GLÓBULOS ROJOS'])">
              <td>Glóbulos rojos</td>
              <td>
                <q-input
                  v-model.number="form.globulos_rojos"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                      'text-right',
                      isOutOfRange('Globulos Rojos', form.globulos_rojos) ? 'text-negative text-weight-bold' : ''
                    ]"
                />
              </td>
              <td>{{ rangoTexto('Globulos Rojos') }}</td>
              <td>{{ rangoUnidad('Globulos Rojos') }}</td>
            </tr>

            <tr v-if="canServicios('HEMOGRAMA COMPLETO+ PLAQUETAS')">
              <td>Glóbulos blancos</td>
              <td>
                <q-input
                  v-model.number="form.globulos_blancos"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                      'text-right',
                      isOutOfRange('Globulos Blancos (Leucocitos)', form.globulos_blancos) ? 'text-negative text-weight-bold' : ''
                    ]"
                />
              </td>
              <td>{{ rangoTexto('Globulos Blancos (Leucocitos)') }}</td>
              <td>{{ rangoUnidad('Globulos Blancos (Leucocitos)') }}</td>
            </tr>

            <tr v-if="canServicios(['COAGULOGRAMA (TP,RECUENTO DE PLAQUETAS, APTT)','HEMOGRAMA COMPLETO+ PLAQUETAS','RECUENTO DE PLAQUETAS'])">
              <td>Plaquetas</td>
              <td>
                <q-input
                  v-model.number="form.plaquetas"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                      'text-right',
                      isOutOfRange('Plaquetas', form.plaquetas) ? 'text-negative text-weight-bold' : ''
                    ]"
                />
              </td>
              <td>{{ rangoTexto('Plaquetas') }}</td>
              <td>{{ rangoUnidad('Plaquetas') }}</td>
            </tr>

            <tr v-if="canServicios(['HEMOGRAMA COMPLETO+ PLAQUETAS','HEMATOCRITO Y HEMOGLOBINA'])">
              <td>Hemoglobina</td>
              <td>
                <q-input
                  v-model.number="form.hemoglobina"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                      'text-right',
                      isOutOfRange('Hemoglobina', form.hemoglobina) ? 'text-negative text-weight-bold' : ''
                    ]"
                />
              </td>
              <td>{{ rangoTexto('Hemoglobina') }}</td>
              <td>{{ rangoUnidad('Hemoglobina') }}</td>
            </tr>

            <tr v-if="canServicios(['HEMOGRAMA COMPLETO+ PLAQUETAS','HEMATOCRITO Y HEMOGLOBINA'])">
              <td>Hematocrito</td>
              <td>
                <q-input
                  v-model.number="form.hematocrito"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                      'text-right',
                      isOutOfRange('Hematocrito', form.hematocrito) ? 'text-negative text-weight-bold' : ''
                    ]"
                />
              </td>
              <td>{{ rangoTexto('Hematocrito') }}</td>
              <td>{{ rangoUnidad('Hematocrito') }}</td>
            </tr>

            <tr v-if="canServicios('ÍNDICES HEMATIMÉTRICOS')">
              <td>VCM</td>
              <td>
                <q-input
                  v-model.number="form.vcm"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                      'text-right',
                      isOutOfRange('V.C.M.', form.vcm) ? 'text-negative text-weight-bold' : ''
                    ]"
                />
              </td>
              <td>{{ rangoTexto('V.C.M.') }}</td>
              <td>{{ rangoUnidad('V.C.M.') }}</td>
            </tr>

            <tr v-if="canServicios('ÍNDICES HEMATIMÉTRICOS')">
              <td>HBCM</td>
              <td>
                <q-input
                  v-model.number="form.hbcm"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                      'text-right',
                      isOutOfRange('Hb.C.M.', form.hbcm) ? 'text-negative text-weight-bold' : ''
                    ]"
                />
              </td>
              <td>{{ rangoTexto('Hb.C.M.') }}</td>
              <td>{{ rangoUnidad('Hb.C.M.') }}</td>
            </tr>

            <tr v-if="canServicios('ÍNDICES HEMATIMÉTRICOS')">
              <td>CHCM</td>
              <td>
                <q-input
                  v-model.number="form.chcm"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                      'text-right',
                      isOutOfRange('CHCM', form.chcm) ? 'text-negative text-weight-bold' : ''
                    ]"
                />
              </td>
              <td>{{ rangoTexto('CHCM') }}</td>
              <td>{{ rangoUnidad('CHCM') }}</td>
            </tr>

            <tr>
              <td>Leucocitos totales</td>
              <td>
                <q-input
                  v-model.number="form.leucocitos_totales"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                      'text-right',
                      isOutOfRange('Leucocitos totales', form.leucocitos_totales) ? 'text-negative text-weight-bold' : ''
                    ]"
                />
              </td>
              <td>{{ rangoTexto('Leucocitos totales') }}</td>
              <td>{{ rangoUnidad('Leucocitos totales') }}</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- RECUENTO DIFERENCIAL -->
          <div class="section-title q-mb-xs">Recuento diferencial</div>

          <q-markup-table dense flat bordered square class="bg-white q-mb-md">
            <thead>
            <tr>
              <th class="text-left">Célula</th>
              <th class="text-left">%</th>
              <th class="text-left">Valor absoluto</th>
              <th class="text-left">Rango % ref.</th>
              <th class="text-left">Rango absoluto ref.</th>
            </tr>
            </thead>

            <tbody>
            <tr v-if="canServicios('HEMOGRAMA COMPLETO+ PLAQUETAS')">
              <td>Basófilos</td>
              <td>
                <q-input v-model.number="form.basofilos_porcentaje" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Basofilos', form.basofilos_porcentaje) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>
                <q-input v-model.number="form.basofilos_absoluto" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Basilos (Absoluto)', form.basofilos_absoluto) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>{{ rangoTexto('Basofilos') }}</td>
              <td>{{ rangoTexto('Basilos (Absoluto)') }}</td>
            </tr>

            <tr v-if="canServicios('HEMOGRAMA COMPLETO+ PLAQUETAS')">
              <td>Eosinófilos</td>
              <td>
                <q-input v-model.number="form.eosinofilos_porcentaje" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Eosinofilos', form.eosinofilos_porcentaje) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>
                <q-input v-model.number="form.eosinofilos_absoluto" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Eosinofilos (Absoluto)', form.eosinofilos_absoluto) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>{{ rangoTexto('Eosinofilos') }}</td>
              <td>{{ rangoTexto('Eosinofilos (Absoluto)') }}</td>
            </tr>

            <tr v-if="canServicios('HEMOGRAMA COMPLETO+ PLAQUETAS')">
              <td>Cayados</td>
              <td>
                <q-input v-model.number="form.cayados_porcentaje" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Cayados', form.cayados_porcentaje) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>
                <q-input v-model.number="form.cayados_absoluto" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Cayados (Absoluto)', form.cayados_absoluto) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>{{ rangoTexto('Cayados') }}</td>
              <td>{{ rangoTexto('Cayados (Absoluto)') }}</td>
            </tr>

            <tr v-if="canServicios('HEMOGRAMA COMPLETO+ PLAQUETAS')">
              <td>Segmentados</td>
              <td>
                <q-input v-model.number="form.segmentados_porcentaje" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Segmentados', form.segmentados_porcentaje) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>
                <q-input v-model.number="form.segmentados_absoluto" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Segmentados (Absoluto)', form.segmentados_absoluto) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>{{ rangoTexto('Segmentados') }}</td>
              <td>{{ rangoTexto('Segmentados (Absoluto)') }}</td>
            </tr>

            <tr v-if="canServicios('HEMOGRAMA COMPLETO+ PLAQUETAS')">
              <td>Linfocitos</td>
              <td>
                <q-input v-model.number="form.linfocitos_porcentaje" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Linfocitos', form.linfocitos_porcentaje) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>
                <q-input v-model.number="form.linfocitos_absoluto" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Linfocitos (Absoluto)', form.linfocitos_absoluto) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>{{ rangoTexto('Linfocitos') }}</td>
              <td>{{ rangoTexto('Linfocitos (Absoluto)') }}</td>
            </tr>

            <tr v-if="canServicios('HEMOGRAMA COMPLETO+ PLAQUETAS')">
              <td>Monocitos</td>
              <td>
                <q-input v-model.number="form.monocitos_porcentaje" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Monocitos', form.monocitos_porcentaje) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>
                <q-input v-model.number="form.monocitos_absoluto" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('Monocitos (Absoluto)', form.monocitos_absoluto) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>{{ rangoTexto('Monocitos') }}</td>
              <td>{{ rangoTexto('Monocitos (Absoluto)') }}</td>
            </tr>

            <tr v-if="canServicios('HEMOGRAMA COMPLETO+ PLAQUETAS')">
              <td>Blastos</td>
              <td>
                <q-input v-model.number="form.blastos_porcentaje" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('BLASTOS', form.blastos_porcentaje) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>
                <q-input v-model.number="form.blastos_absoluto" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>{{ rangoTexto('BLASTOS') }}</td>
              <td></td>
            </tr>

            <tr v-if="canServicios('HEMOGRAMA COMPLETO+ PLAQUETAS')">
              <td>Metamielocitos</td>
              <td>
                <q-input v-model.number="form.metamielocitos_porcentaje" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('METAMIELOCITO', form.metamielocitos_porcentaje) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>
                <q-input v-model.number="form.metamielocitos_absoluto" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>{{ rangoTexto('METAMIELOCITO') }}</td>
              <td></td>
            </tr>

            <tr v-if="canServicios('HEMOGRAMA COMPLETO+ PLAQUETAS')">
              <td>Eritroblastos</td>
              <td>
                <q-input v-model.number="form.eritroblastos_porcentaje" dense outlined type="number" step="0.01"
                         :input-class="['text-right', isOutOfRange('ERITROBLASTOS', form.eritroblastos_porcentaje) ? 'text-negative text-weight-bold' : '']"
                />
              </td>
              <td>
                <q-input v-model.number="form.eritroblastos_absoluto" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>{{ rangoTexto('ERITROBLASTOS') }}</td>
              <td></td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- MORFOLOGÍA ERITROCITOS -->
          <div class="section-title q-mb-xs">Morfología de eritrocitos</div>
          <q-input
            v-model="form.morfologia_eritrocitos"
            type="textarea"
            dense
            outlined
            autogrow
            class="bg-white q-mb-md"
            placeholder="Anisocitosis, poiquilocitosis, hipocromía, etc."
          />

          <!-- COAGULOGRAMA -->
          <div class="section-title q-mb-xs">Coagulograma</div>

          <q-markup-table dense flat bordered square class="bg-white q-mb-md">
            <thead>
            <tr>
              <th class="text-left">Prueba</th>
              <th class="text-left">Resultado</th>
              <th class="text-left">Rango de referencia</th>
              <th class="text-left">Unidad</th>
            </tr>
            </thead>

            <tbody>
            <tr v-if="canServicios(['COAGULOGRAMA (TP,RECUENTO DE PLAQUETAS, APTT)','TIEMPO DE PROTROMBINA (TP)'])">
              <td>Tiempo de protrombina (TP)</td>
              <td>
                <q-input v-model.number="form.tiempo_protrombina" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>11 – 15</td>
              <td>seg</td>
            </tr>

            <tr v-if="canServicios(['COAGULOGRAMA (TP,RECUENTO DE PLAQUETAS, APTT)','TIEMPO DE PROTROMBINA (TP)'])">
              <td>Actividad de protrombina</td>
              <td>
                <q-input v-model.number="form.actividad_protrombina" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>70 – 100</td>
              <td>%</td>
            </tr>

            <tr v-if="canServicios(['COAGULOGRAMA (TP,RECUENTO DE PLAQUETAS, APTT)','TIEMPO DE PROTROMBINA (TP)'])">
              <td>INR</td>
              <td>
                <q-input v-model.number="form.inr" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>0.8 – 1.2</td>
              <td>-</td>
            </tr>

            <tr v-if="canServicios(['COAGULOGRAMA (TP,RECUENTO DE PLAQUETAS, APTT)','TIEMPO PARCIAL DE TROMBOPLASTINA ACTIVADA (APTT)'])">
              <td>APTT</td>
              <td>
                <q-input v-model.number="form.aptt" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>24 – 35</td>
              <td>seg</td>
            </tr>

            <tr v-if="canServicios('FIBRINÓGENO')">
              <td>Fibrinógeno</td>
              <td>
                <q-input v-model.number="form.fibrinogeno" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>2.0 – 4.0</td>
              <td>g/L</td>
            </tr>

            <tr v-if="canServicios('ERITROSEDIMENTACIÓN (VSG- VES)')">
              <td>V.E.S</td>
              <td>
                <q-input v-model.number="form.ves" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>&lt; 20</td>
              <td>mm/h</td>
            </tr>

            <tr>
              <td>IPR</td>
              <td>
                <q-input v-model.number="form.ipr" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>-</td>
              <td>%</td>
            </tr>

            <tr>
              <td>IPR 2</td>
              <td>
                <q-input v-model.number="form.ipr2" dense outlined type="number" step="0.01" input-class="text-right" />
              </td>
              <td>-</td>
              <td>%</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- GRUPO SANGUÍNEO -->
          <div class="row q-col-gutter-sm q-mb-md">
            <div class="col-12 col-sm-4" v-if="canServicios('GRUPO SANGUÍNEO Y FACTOR')">
              <div class="section-title q-mb-xs">Grupo sanguíneo</div>
              <q-select
                v-model="form.grupo_sanguineo"
                :options="['O', 'A', 'B', 'AB']"
                dense
                outlined
                clearable
                class="bg-white"
                label="Grupo (ABO)"
              />
            </div>

            <div class="col-12 col-sm-4" v-if="canServicios('GRUPO SANGUÍNEO Y FACTOR')">
              <div class="section-title q-mb-xs">Factor Rh</div>
              <q-select
                v-model="form.factor_rh"
                :options="['Positivo', 'Negativo']"
                dense
                outlined
                clearable
                class="bg-white"
                label="Rh"
              />
            </div>

            <div class="col-12 col-sm-4">
              <div class="section-title q-mb-xs">Método / Equipo</div>
              <q-input v-model="form.metodo" dense outlined class="bg-white q-mb-xs" label="Método (A, M, M/SA, etc.)" />
              <q-select
                v-model="form.equipo"
                :options="['Mindray BC 3000 Plus', 'Mindray BC 5130', 'Otro']"
                dense
                outlined
                clearable
                class="bg-white"
                label="Equipo"
              />
            </div>
          </div>

          <!-- BOTONES -->
          <div class="text-right q-mt-md">
            <q-btn flat label="Cancelar" no-caps class="q-mr-sm" :disable="loading" @click="$router.back()" />
            <q-btn color="primary" icon="save" label="Guardar" type="submit" no-caps :loading="loading" />
          </div>
        </q-form>
      </q-card-section>

      <q-inner-loading :showing="loading && formLoaded">
        <q-spinner size="42px"/>
      </q-inner-loading>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: 'HematologiaPage',
  data () {
    return {
      solicitudId: this.$route.params.id,
      loading: false,
      header: null,
      formLoaded: false,
      rangos: [],
      form: {
        globulos_rojos: null,
        globulos_blancos: null,
        plaquetas: null,
        hemoglobina: null,
        hematocrito: null,
        vcm: null,
        hbcm: null,
        chcm: null,
        leucocitos_totales: null,

        basofilos_porcentaje: null,
        basofilos_absoluto: null,
        eosinofilos_porcentaje: null,
        eosinofilos_absoluto: null,
        cayados_porcentaje: null,
        cayados_absoluto: null,
        segmentados_porcentaje: null,
        segmentados_absoluto: null,
        linfocitos_porcentaje: null,
        linfocitos_absoluto: null,
        monocitos_porcentaje: null,
        monocitos_absoluto: null,
        blastos_porcentaje: null,
        blastos_absoluto: null,
        metamielocitos_porcentaje: null,
        metamielocitos_absoluto: null,
        eritroblastos_porcentaje: null,
        eritroblastos_absoluto: null,

        morfologia_eritrocitos: '',

        tiempo_protrombina: null,
        actividad_protrombina: null,
        inr: null,
        aptt: null,
        fibrinogeno: null,
        ves: null,
        ipr: null,
        ipr2: null,

        grupo_sanguineo: '',
        factor_rh: '',
        metodo: '',
        equipo: ''
      }
    }
  },

  mounted () {
    this.load()
  },

  methods: {
    // ========= servicio match =========
    canServicios (can) {
      const norm = (v) => String(v ?? '').replace(/\s+/g, ' ').trim().toLowerCase()
      if (!this.header || !Array.isArray(this.header.servicios)) return false
      const targets = Array.isArray(can) ? can : [can]
      const wanted = targets.map(norm)
      return this.header.servicios.some(s => wanted.includes(norm(s.nombre)))
    },

    // ========= api =========
    async load () {
      try {
        this.loading = true
        this.formLoaded = false
        const { data } = await this.$axios.get(`/hematologia/solicitud/${this.solicitudId}`)
        this.header = data.solicitud || null
        this.form = Object.assign({}, this.form, data.hematologia || {})
        this.rangos = data.rangos || []
        this.formLoaded = true
      } catch (e) {
        const msg = e.response?.data?.message || e.message
        if (this.$alert && this.$alert.error) this.$alert.error('Error al cargar hematología: ' + msg)
        else console.error(msg)
      } finally {
        this.loading = false
      }
    },

    async save () {
      try {
        this.loading = true
        await this.$axios.post(`/hematologia/solicitud/${this.solicitudId}`, this.form)
        if (this.$alert && this.$alert.success) this.$alert.success('Hematología guardada correctamente')
        else console.log('Hematología guardada correctamente')
      } catch (e) {
        const msg = e.response?.data?.message || e.message
        if (this.$alert && this.$alert.error) this.$alert.error('Error al guardar: ' + msg)
        else console.error(msg)
      } finally {
        this.loading = false
      }
    },

    printHematologia () {
      // const id = this.solicitudId
      const code = this.form?.code || ''
      // console.log('Imprimir hematología:', this.form)
      const url = `${this.$axios.defaults.baseURL}/hematologia/solicitud/${code}/pdf`
      window.open(url, '_blank')
    },

    onSubmit () {
      this.save()
    },

    // ========= rangos =========
    getRango (nombre) {
      if (!this.rangos || !Array.isArray(this.rangos)) return null
      return this.rangos.find(r => (r.rango_nombre || '').toLowerCase() === (nombre || '').toLowerCase()) || null
    },

    rangoTexto (nombre) {
      const r = this.getRango(nombre)
      if (!r) return ''
      if (r.rango_minimo !== null && r.rango_maximo !== null) return `${r.rango_minimo} - ${r.rango_maximo}`
      if (r.interpretacion) return r.interpretacion
      return ''
    },

    rangoUnidad (nombre) {
      const r = this.getRango(nombre)
      return r && r.unidad ? r.unidad : ''
    },

    isOutOfRange (nombre, valor) {
      const r = this.getRango(nombre)
      const num = parseFloat(valor)
      if (!r || isNaN(num)) return false
      if (r.rango_minimo !== null && num < r.rango_minimo) return true
      if (r.rango_maximo !== null && num > r.rango_maximo) return true
      return false
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

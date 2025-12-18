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
          <!--          refresch-->
          <!--          <q-btn-->
          <!--            flat-->
          <!--            icon="refresh"-->
          <!--            label="Refrescar"-->
          <!--            no-caps-->
          <!--            class="q-mr-sm"-->
          <!--            :disable="loading"-->
          <!--            @click="load"-->
          <!--          />-->
          <!--          ntn imprimir-->
          <!--          printHematologia(solicitud) {-->
          <!--          const url = `${this.$axios.defaults.baseURL}/hematologia/solicitud/${solicitud.id}/pdf`-->
          <!--          window.open(url, '_blank')-->
          <!--          },-->
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

      <q-separator/>

      <!-- DATOS DE LA SOLICITUD / PACIENTE -->
      <q-card-section v-if="header" class="q-pa-sm">
        <div class="row q-col-gutter-sm text-caption">
          <div class="col-12 col-md-4">
            <div class="text-grey-7">Paciente</div>
            <div class="text-body2 text-weight-medium">
              {{ pacienteNombre }}
            </div>
            <div class="text-grey-7 q-mt-xs">
              Edad: <b>{{ pacienteEdad }}</b> • Género:
              <b>{{ pacienteGenero }}</b>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="text-grey-7">Médico solicitante</div>
            <div class="text-body2 text-weight-medium">
              {{ doctorNombre }}
            </div>
            <div class="text-grey-7 q-mt-xs">
              Fecha solicitud:
              <b>{{ solicitudFecha }}</b>
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
                <q-chip square outline color="primary" class="badge-estado" dense>
                  {{ solicitudEstado }}
                </q-chip>
              </div>
            </div>
          </div>
        </div>
      </q-card-section>

      <q-inner-loading :showing="loading && !formLoaded">
        <q-spinner size="42px"/>
      </q-inner-loading>
    </q-card>

    <!-- FORMULARIO PRINCIPAL -->
    <q-card flat bordered>
      <q-card-section class="q-pa-sm">
        <q-form @submit.prevent="onSubmit">
          <!-- HEMOGRAMA BÁSICO -->
          <div class="section-title q-mb-xs">
            Hemograma básico
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
              <th class="text-left">Analito</th>
              <th class="text-left">Resultado</th>
              <th class="text-left">Rango de referencia</th>
              <th class="text-left">Unidad</th>
            </tr>
            </thead>
            <tbody>
            <tr>
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
            isOutOfRange('Globulos Rojos', form.globulos_rojos)
              ? 'text-negative text-weight-bold'
              : ''
          ]"
                />
              </td>
              <td>{{ rangoTexto('Globulos Rojos') }}</td>
              <td>{{ rangoUnidad('Globulos Rojos') }}</td>
            </tr>

            <tr>
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
            isOutOfRange('Globulos Blancos (Leucocitos)', form.globulos_blancos)
              ? 'text-negative text-weight-bold'
              : ''
          ]"
                />
              </td>
              <td>{{ rangoTexto('Globulos Blancos (Leucocitos)') }}</td>
              <td>{{ rangoUnidad('Globulos Blancos (Leucocitos)') }}</td>
            </tr>

            <tr>
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
            isOutOfRange('Plaquetas', form.plaquetas)
              ? 'text-negative text-weight-bold'
              : ''
          ]"
                />
              </td>
              <td>{{ rangoTexto('Plaquetas') }}</td>
              <td>{{ rangoUnidad('Plaquetas') }}</td>
            </tr>

            <tr>
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
            isOutOfRange('Hemoglobina', form.hemoglobina)
              ? 'text-negative text-weight-bold'
              : ''
          ]"
                />
              </td>
              <td>{{ rangoTexto('Hemoglobina') }}</td>
              <td>{{ rangoUnidad('Hemoglobina') }}</td>
            </tr>

            <tr>
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
            isOutOfRange('Hematocrito', form.hematocrito)
              ? 'text-negative text-weight-bold'
              : ''
          ]"
                />
              </td>
              <td>{{ rangoTexto('Hematocrito') }}</td>
              <td>{{ rangoUnidad('Hematocrito') }}</td>
            </tr>

            <tr>
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
            isOutOfRange('V.C.M.', form.vcm)
              ? 'text-negative text-weight-bold'
              : ''
          ]"
                />
              </td>
              <td>{{ rangoTexto('V.C.M.') }}</td>
              <td>{{ rangoUnidad('V.C.M.') }}</td>
            </tr>

            <tr>
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
            isOutOfRange('Hb.C.M.', form.hbcm)
              ? 'text-negative text-weight-bold'
              : ''
          ]"
                />
              </td>
              <td>{{ rangoTexto('Hb.C.M.') }}</td>
              <td>{{ rangoUnidad('Hb.C.M.') }}</td>
            </tr>

            <tr>
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
            isOutOfRange('CHCM', form.chcm)
              ? 'text-negative text-weight-bold'
              : ''
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
            isOutOfRange('Leucocitos totales', form.leucocitos_totales)
              ? 'text-negative text-weight-bold'
              : ''
          ]"
                />
              </td>
              <td>{{ rangoTexto('Leucocitos totales') }}</td>
              <td>{{ rangoUnidad('Leucocitos totales') }}</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- RECUENTO DIFERENCIAL -->
          <div class="section-title q-mb-xs">
            Recuento diferencial
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
              <th class="text-left">Célula</th>
              <th class="text-left">%</th>
              <th class="text-left">Valor absoluto</th>
              <th class="text-left">Rango % ref.</th>
              <th class="text-left">Rango absoluto ref.</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td>Basófilos</td>
              <td>
                <q-input
                  v-model.number="form.basofilos_porcentaje"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
            'text-right',
            isOutOfRange('Basofilos', form.basofilos_porcentaje)
              ? 'text-negative text-weight-bold'
              : ''
          ]"
                />
              </td>
              <td>
                <q-input
                  v-model.number="form.basofilos_absoluto"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                    'text-right',
                    isOutOfRange('Basilos (Absoluto)', form.basofilos_absoluto)
                      ? 'text-negative text-weight-bold'
                      : ''
                  ]"
                />
              </td>
              <td>{{ rangoTexto('Basofilos') }}</td>
              <td>{{ rangoTexto('Basilos (Absoluto)') }}</td>
            </tr>
            <tr>
              <td>Eosinófilos</td>
              <td>
                <q-input
                  v-model.number="form.eosinofilos_porcentaje"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
            'text-right',
            isOutOfRange('Eosinofilos', form.eosinofilos_porcentaje)
              ? 'text-negative text-weight-bold'
              : ''
                  ]"
                />
              </td>
              <td>
                <q-input
                  v-model.number="form.eosinofilos_absoluto"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                    'text-right',
                    isOutOfRange('Eosinofilos (Absoluto)', form.eosinofilos_absoluto)
                      ? 'text-negative text-weight-bold'
                      : ''
                  ]"
                />
              </td>
              <td>
                {{ rangoTexto('Eosinofilos') }}
              </td>
              <td>
                {{ rangoTexto('Eosinofilos (Absoluto)') }}
              </td>
            </tr>
            <tr>
              <td>Cayados</td>
              <td>
                <q-input
                  v-model.number="form.cayados_porcentaje"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
            'text-right',
            isOutOfRange('Cayados', form.cayados_porcentaje)
              ? 'text-negative text-weight-bold'
              : ''
                  ]"
                />
              </td>
              <td>
                <q-input
                  v-model.number="form.cayados_absoluto"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                    'text-right',
                    isOutOfRange('Cayados (Absoluto)', form.cayados_absoluto)
                      ? 'text-negative text-weight-bold'
                      : ''
                  ]"
                />
              </td>
              <td>
                {{ rangoTexto('Cayados') }}
              </td>
              <td>
                {{ rangoTexto('Cayados (Absoluto)') }}
              </td>
            </tr>
            <tr>
              <td>Segmentados</td>
              <td>
                <q-input
                  v-model.number="form.segmentados_porcentaje"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
            'text-right',
            isOutOfRange('Segmentados', form.segmentados_porcentaje)
              ? 'text-negative text-weight-bold'
              : ''
                  ]"
                />
              </td>
              <td>
                <q-input
                  v-model.number="form.segmentados_absoluto"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                    'text-right',
                    isOutOfRange('Segmentados (Absoluto)', form.segmentados_absoluto)
                      ? 'text-negative text-weight-bold'
                      : ''
                  ]"
                />
              </td>
              <td>
                {{ rangoTexto('Segmentados') }}
              </td>
              <td>
                {{ rangoTexto('Segmentados (Absoluto)') }}
              </td>
            </tr>
            <tr>
              <td>Linfocitos</td>
              <td>
                <q-input
                  v-model.number="form.linfocitos_porcentaje"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
            'text-right',
            isOutOfRange('Linfocitos', form.linfocitos_porcentaje)
              ? 'text-negative text-weight-bold'
              : ''
                  ]"
                />
              </td>
              <td>
                <q-input
                  v-model.number="form.linfocitos_absoluto"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                    'text-right',
                    isOutOfRange('Linfocitos (Absoluto)', form.linfocitos_absoluto)
                      ? 'text-negative text-weight-bold'
                      : ''
                  ]"
                />
              </td>
              <td>
                {{ rangoTexto('Linfocitos') }}
              </td>
              <td>
                {{ rangoTexto('Linfocitos (Absoluto)') }}
              </td>
            </tr>
            <tr>
              <td>Monocitos</td>
              <td>
                <q-input
                  v-model.number="form.monocitos_porcentaje"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
            'text-right',
            isOutOfRange('Monocitos', form.monocitos_porcentaje)
              ? 'text-negative text-weight-bold'
              : ''
                  ]"
                />
              </td>
              <td>
                <q-input
                  v-model.number="form.monocitos_absoluto"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
                    'text-right',
                    isOutOfRange('Monocitos (Absoluto)', form.monocitos_absoluto)
                      ? 'text-negative text-weight-bold'
                      : ''
                  ]"
                />
              </td>
              <td>
                {{ rangoTexto('Monocitos') }}
              </td>
              <td>
                {{ rangoTexto('Monocitos (Absoluto)') }}
              </td>
            </tr>
            <tr>
              <td>Blastos</td>
              <td>
                <q-input
                  v-model.number="form.blastos_porcentaje"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
            'text-right',
            isOutOfRange('BLASTOS', form.blastos_porcentaje)
              ? 'text-negative text-weight-bold'
              : ''
                  ]"
                />
              </td>
              <td>
                <q-input
                  v-model.number="form.blastos_absoluto"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>
                {{ rangoTexto('BLASTOS') }}
              </td>
            </tr>
            <tr>
              <td>Metamielocitos</td>
              <td>
                <q-input
                  v-model.number="form.metamielocitos_porcentaje"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
            'text-right',
            isOutOfRange('METAMIELOCITO', form.metamielocitos_porcentaje)
              ? 'text-negative text-weight-bold'
              : ''
                  ]"
                />
              </td>
              <td>
                <q-input
                  v-model.number="form.metamielocitos_absoluto"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>
                {{ rangoTexto('METAMIELOCITO') }}
              </td>
            </tr>
            <tr>
              <td>Eritroblastos</td>
              <td>
                <q-input
                  v-model.number="form.eritroblastos_porcentaje"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  :input-class="[
            'text-right',
            isOutOfRange('ERITROBLASTOS', form.eritroblastos_porcentaje)
              ? 'text-negative text-weight-bold'
              : ''
                  ]"
                />
              </td>
              <td>
                <q-input
                  v-model.number="form.eritroblastos_absoluto"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>
                {{ rangoTexto('ERITROBLASTOS') }}
              </td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- MORFOLOGÍA ERITROCITOS -->
          <div class="section-title q-mb-xs">
            Morfología de eritrocitos
          </div>
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
          <div class="section-title q-mb-xs">
            Coagulograma
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
              <th class="text-left">Prueba</th>
              <th class="text-left">Resultado</th>
              <th class="text-left">Rango de referencia</th>
              <th class="text-left">Unidad</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td>Tiempo de protrombina (TP)</td>
              <td>
                <q-input
                  v-model.number="form.tiempo_protrombina"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>11 – 15</td>
              <td>seg</td>
            </tr>
            <tr>
              <td>Actividad de protrombina</td>
              <td>
                <q-input
                  v-model.number="form.actividad_protrombina"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>70 – 100</td>
              <td>%</td>
            </tr>
            <tr>
              <td>INR</td>
              <td>
                <q-input
                  v-model.number="form.inr"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>0.8 – 1.2</td>
              <td>-</td>
            </tr>
            <tr>
              <td>APTT</td>
              <td>
                <q-input
                  v-model.number="form.aptt"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>24 – 35</td>
              <td>seg</td>
            </tr>
            <tr>
              <td>Fibrinógeno</td>
              <td>
                <q-input
                  v-model.number="form.fibrinogeno"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>2.0 – 4.0</td>
              <td>g/L</td>
            </tr>
            <tr>
              <td>V.E.S</td>
              <td>
                <q-input
                  v-model.number="form.ves"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>&lt; 20</td>
              <td>mm/h</td>
            </tr>
            <tr>
              <td>IPR</td>
              <td>
                <q-input
                  v-model.number="form.ipr"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>-</td>
              <td>%</td>
            </tr>
            <tr>
              <td>IPR 2</td>
              <td>
                <q-input
                  v-model.number="form.ipr2"
                  dense
                  outlined
                  type="number"
                  step="0.01"
                  input-class="text-right"
                />
              </td>
              <td>-</td>
              <td>%</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- GRUPO SANGUÍNEO Y MÉTODO -->
          <div class="row q-col-gutter-sm q-mb-md">
            <div class="col-12 col-sm-4">
              <div class="section-title q-mb-xs">
                Grupo sanguíneo
              </div>
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
            <div class="col-12 col-sm-4">
              <div class="section-title q-mb-xs">
                Factor Rh
              </div>
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
              <div class="section-title q-mb-xs">
                Método / Equipo
              </div>
              <q-input
                v-model="form.metodo"
                dense
                outlined
                class="bg-white q-mb-xs"
                label="Método (A, M, M/SA, etc.)"
              />
<!--              <q-input-->
<!--                v-model="form.equipo"-->
<!--                dense-->
<!--                outlined-->
<!--                class="bg-white"-->
<!--                label="Equipo (ej. Mindray BC 3000 Plus)"-->
<!--              /> BC 5130 y BC 3000-->
              <q-select v-model="form.equipo"
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
        <q-spinner size="42px"/>
      </q-inner-loading>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: 'HematologiaPage',
  data() {
    return {
      solicitudId: this.$route.params.id,
      loading: false,
      header: null,
      formLoaded: false,
      rangos: [], // 👈 NUEVO
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
  computed: {
    pacienteNombre() {
      const h = this.header
      if (!h) return '-'
      if (h.paciente && h.paciente.nombre_completo) return h.paciente.nombre_completo
      return h.paciente_nombre || '-'
    },
    pacienteEdad() {
      const h = this.header
      if (!h) return '-'
      if (h.paciente && h.paciente.edad) return h.paciente.edad
      return h.paciente_edad || '-'
    },
    pacienteGenero() {
      const h = this.header
      if (!h) return '-'
      if (h.paciente && h.paciente.genero) return h.paciente.genero
      return h.paciente_genero || '-'
    },
    doctorNombre() {
      const h = this.header
      if (!h) return '-'
      if (h.doctor && h.doctor.nombre) return h.doctor.nombre
      return h.doctor_nombre || '-'
    },
    solicitudFecha() {
      const h = this.header
      if (!h) return '-'
      return h.fecha_solicitud || '-'
    },
    solicitudCodigo() {
      const h = this.header
      if (!h) return '-'
      return h.nro_registro || h.codigo_solicitud || h.id || '-'
    },
    solicitudEstado() {
      const h = this.header
      if (!h) return '-'
      return h.estado || '-'
    }
  },
  mounted() {
    this.load()
  },
  methods: {
    async load() {
      try {
        this.loading = true
        this.formLoaded = false
        const {data} = await this.$axios.get(`/hematologia/solicitud/${this.solicitudId}`)
        this.header = data.solicitud || null
        // Merge para no perder keys por defecto
        this.form = Object.assign({}, this.form, data.hematologia || {})
        this.rangos = data.rangos || [] // 👈 NUEVO
        this.formLoaded = true
      } catch (e) {
        const msg = e.response?.data?.message || e.message
        if (this.$alert && this.$alert.error) {
          this.$alert.error('Error al cargar hematología: ' + msg)
        } else {
          console.error(msg)
        }
      } finally {
        this.loading = false
      }
    },
    async save() {
      try {
        this.loading = true
        await this.$axios.post(`/hematologia/solicitud/${this.solicitudId}`, this.form)
        if (this.$alert && this.$alert.success) {
          this.$alert.success('Hematología guardada correctamente')
        } else {
          console.log('Hematología guardada correctamente')
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
    printHematologia() {
      const id = this.solicitudId
      const url = `${this.$axios.defaults.baseURL}/hematologia/solicitud/${id}/pdf`
      window.open(url, '_blank')
    },
    onSubmit() {
      this.save()
    },
    getRango(nombre) {
      console.log(Array.isArray(this.rangos), this.rangos)
      if (!this.rangos || !Array.isArray(this.rangos)) return null
      return (
        this.rangos.find(
          r => (r.rango_nombre || '').toLowerCase() === (nombre || '').toLowerCase()
        ) || null
      )
    },
    rangoTexto(nombre) {
      const r = this.getRango(nombre)
      if (!r) return ''
      // si tiene rango_minimo y rango_maximo, usamos eso
      if (r.rango_minimo !== null && r.rango_maximo !== null) {
        return `${r.rango_minimo} - ${r.rango_maximo}`
      }
      // si no, usamos interpretacion
      if (r.interpretacion) {
        return r.interpretacion
      }
      return ''
    },
    rangoUnidad(nombre) {
      const r = this.getRango(nombre)
      return r && r.unidad ? r.unidad : ''
    },
    isOutOfRange(nombre, valor) {
      const r = this.getRango(nombre)
      const num = parseFloat(valor)
      if (!r || isNaN(num)) return false

      if (r.rango_minimo !== null && num < r.rango_minimo) {
        return true
      }
      if (r.rango_maximo !== null && num > r.rango_maximo) {
        return true
      }
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

<template>
  <q-page class="q-pa-sm bg-grey-2">
    <!-- ENCABEZADO -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row items-center q-col-gutter-sm">
        <div class="col">
          <div class="text-h6 text-weight-bold">Química sanguínea</div>
          <div class="text-caption text-grey-7">
            Perfil bioquímico, lipídico, hepático, electrolitos y pruebas serológicas básicas.
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
<!--          imprimir-->
          <q-btn
            class="q-ml-sm"
            outline
            color="primary"
            icon="print"
            label="Imprimir"
            no-caps
            :disable="!formLoaded"
            @click="printPdf"
          />
        </div>
      </q-card-section>

      <q-separator />

      <!-- DATOS SOLICITUD / PACIENTE -->
      <q-card-section v-if="header" class="q-pa-sm">
        <div class="row q-col-gutter-sm text-caption">
          <div class="col-12 col-md-4">
            <div class="text-grey-7">Paciente</div>
            <div class="text-body2 text-weight-medium">
              {{ pacienteNombre }}
            </div>
            <div class="text-grey-7 q-mt-xs">
              Edad: <b>{{ pacienteEdad }}</b> • Género: <b>{{ pacienteGenero }}</b>
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
                <q-chip square outline color="primary" class="badge-estado" dense>
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
          <!-- BLOQUE: QUÍMICA SANGUÍNEA BÁSICA -->
          <div class="section-title q-mb-xs">Química sanguínea básica</div>
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
            <tr>
              <td>Ácido Úrico</td>
              <td>
                <q-input
                  v-model.number="form.acido_urico"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Ácido Úrico', form.acido_urico)"
                />
              </td>
              <td>{{ rangoTexto('Ácido Úrico') }}</td>
              <td>{{ rangoUnidad('Ácido Úrico') }}</td>
            </tr>
            <tr>
              <td>Albúmina</td>
              <td>
                <q-input
                  v-model.number="form.albumina"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Albúmina', form.albumina)"
                />
              </td>
              <td>{{ rangoTexto('Albúmina') }}</td>
              <td>{{ rangoUnidad('Albúmina') }}</td>
            </tr>
            <tr>
              <td>Proteínas totales</td>
              <td>
                <q-input
                  v-model.number="form.proteinas_totales"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Proteínas totales', form.proteinas_totales)"
                />
              </td>
              <td>{{ rangoTexto('Proteínas totales') }}</td>
              <td>{{ rangoUnidad('Proteínas totales') }}</td>
            </tr>
            <tr>
              <td>Glucosa</td>
              <td>
                <q-input
                  v-model.number="form.glucosa"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Glucosa', form.glucosa)"
                />
              </td>
              <td>{{ rangoTexto('Glucosa') }}</td>
              <td>{{ rangoUnidad('Glucosa') }}</td>
            </tr>
            <tr>
              <td>Urea</td>
              <td>
                <q-input
                  v-model.number="form.urea"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Urea', form.urea)"
                />
              </td>
              <td>{{ rangoTexto('Urea') }}</td>
              <td>{{ rangoUnidad('Urea') }}</td>
            </tr>
            <tr>
              <td>NUS</td>
              <td>
                <q-input
                  v-model.number="form.nus"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('NUS', form.nus)"
                />
              </td>
              <td>{{ rangoTexto('NUS') }}</td>
              <td>{{ rangoUnidad('NUS') }}</td>
            </tr>
            <tr>
              <td>Creatinina</td>
              <td>
                <q-input
                  v-model.number="form.creatinina"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Creatinina', form.creatinina)"
                />
              </td>
              <td>{{ rangoTexto('Creatinina') }}</td>
              <td>{{ rangoUnidad('Creatinina') }}</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- PERFIL HEPÁTICO -->
          <div class="section-title q-mb-xs">Enzimas hepáticas y bilirrubinas</div>
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
            <tr>
              <td>Bilirrubina Total</td>
              <td>
                <q-input
                  v-model.number="form.bilirrubina_total"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Bilirrubina Total', form.bilirrubina_total)"
                />
              </td>
              <td>{{ rangoTexto('Bilirrubina Total') }}</td>
              <td>{{ rangoUnidad('Bilirrubina Total') }}</td>
            </tr>
            <tr>
              <td>Bilirrubina Directa</td>
              <td>
                <q-input
                  v-model.number="form.bilirrubina_directa"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Bilirrubina Directa', form.bilirrubina_directa)"
                />
              </td>
              <td>{{ rangoTexto('Bilirrubina Directa') }}</td>
              <td>{{ rangoUnidad('Bilirrubina Directa') }}</td>
            </tr>
            <tr>
              <td>Bilirrubina Indirecta</td>
              <td>
                <q-input
                  v-model.number="form.bilirrubina_indirecta"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Bilirrubina Indirecta', form.bilirrubina_indirecta)"
                />
              </td>
              <td>{{ rangoTexto('Bilirrubina Indirecta') }}</td>
              <td>{{ rangoUnidad('Bilirrubina Indirecta') }}</td>
            </tr>

            <tr>
              <td>G.O.T. (TGO)</td>
              <td>
                <q-input
                  v-model.number="form.got"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('G.O.T. (TGO)', form.got)"
                />
              </td>
              <td>{{ rangoTexto('G.O.T. (TGO)') }}</td>
              <td>{{ rangoUnidad('G.O.T. (TGO)') }}</td>
            </tr>
            <tr>
              <td>G.P.T. (TGP)</td>
              <td>
                <q-input
                  v-model.number="form.gpt"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('G.P.T. (TGP)', form.gpt)"
                />
              </td>
              <td>{{ rangoTexto('G.P.T. (TGP)') }}</td>
              <td>{{ rangoUnidad('G.P.T. (TGP)') }}</td>
            </tr>
            <tr>
              <td>Fosfatasa Alcalina</td>
              <td>
                <q-input
                  v-model.number="form.fosfatasa_alcalina"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Fosfatasa Alcalina', form.fosfatasa_alcalina)"
                />
              </td>
              <td>{{ rangoTexto('Fosfatasa Alcalina') }}</td>
              <td>{{ rangoUnidad('Fosfatasa Alcalina') }}</td>
            </tr>
            <tr>
              <td>GGT</td>
              <td>
                <q-input
                  v-model.number="form.ggt"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('GGT', form.ggt)"
                />
              </td>
              <td>{{ rangoTexto('GGT') }}</td>
              <td>{{ rangoUnidad('GGT') }}</td>
            </tr>
            <tr>
              <td>Amilasa</td>
              <td>
                <q-input
                  v-model.number="form.amilasa"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Amilasa', form.amilasa)"
                />
              </td>
              <td>{{ rangoTexto('Amilasa') }}</td>
              <td>{{ rangoUnidad('Amilasa') }}</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- PERFIL LIPÍDICO -->
          <div class="section-title q-mb-xs">Perfil lipídico</div>
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
            <tr>
              <td>Colesterol total</td>
              <td>
                <q-input
                  v-model.number="form.colesterol_total"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Colesterol total', form.colesterol_total)"
                />
              </td>
              <td>{{ rangoTexto('Colesterol total') }}</td>
              <td>{{ rangoUnidad('Colesterol total') }}</td>
            </tr>
            <tr>
              <td>Triglicéridos</td>
              <td>
                <q-input
                  v-model.number="form.trigliceridos"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Triglicéridos', form.trigliceridos)"
                />
              </td>
              <td>{{ rangoTexto('Triglicéridos') }}</td>
              <td>{{ rangoUnidad('Triglicéridos') }}</td>
            </tr>
            <tr>
              <td>HDL Colesterol</td>
              <td>
                <q-input
                  v-model.number="form.hdl_colesterol"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('HDL Colesterol', form.hdl_colesterol)"
                />
              </td>
              <td>{{ rangoTexto('HDL Colesterol') }}</td>
              <td>{{ rangoUnidad('HDL Colesterol') }}</td>
            </tr>
            <tr>
              <td>LDL Colesterol</td>
              <td>
                <q-input
                  v-model.number="form.ldl_colesterol"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('LDL Colesterol', form.ldl_colesterol)"
                />
              </td>
              <td>{{ rangoTexto('LDL Colesterol') }}</td>
              <td>{{ rangoUnidad('LDL Colesterol') }}</td>
            </tr>
            <tr>
              <td>VLDL Colesterol</td>
              <td>
                <q-input
                  v-model.number="form.vldl_colesterol"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('VLDL Colesterol', form.vldl_colesterol)"
                />
              </td>
              <td>{{ rangoTexto('VLDL Colesterol') }}</td>
              <td>{{ rangoUnidad('VLDL Colesterol') }}</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- ELECTROLITOS Y MINERALES -->
          <div class="section-title q-mb-xs">Electrolitos y minerales</div>
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
            <tr>
              <td>Sodio</td>
              <td>
                <q-input
                  v-model.number="form.sodio"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Sodio', form.sodio)"
                />
              </td>
              <td>{{ rangoTexto('Sodio') }}</td>
              <td>{{ rangoUnidad('Sodio') }}</td>
            </tr>
            <tr>
              <td>Potasio</td>
              <td>
                <q-input
                  v-model.number="form.potasio"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Potasio', form.potasio)"
                />
              </td>
              <td>{{ rangoTexto('Potasio') }}</td>
              <td>{{ rangoUnidad('Potasio') }}</td>
            </tr>
            <tr>
              <td>Cloro</td>
              <td>
                <q-input
                  v-model.number="form.cloro"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Cloro', form.cloro)"
                />
              </td>
              <td>{{ rangoTexto('Cloro') }}</td>
              <td>{{ rangoUnidad('Cloro') }}</td>
            </tr>
            <tr>
              <td>Calcio</td>
              <td>
                <q-input
                  v-model.number="form.calcio"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Calcio', form.calcio)"
                />
              </td>
              <td>{{ rangoTexto('Calcio') }}</td>
              <td>{{ rangoUnidad('Calcio') }}</td>
            </tr>
            <tr>
              <td>Fósforo</td>
              <td>
                <q-input
                  v-model.number="form.fosforo"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Fósforo', form.fosforo)"
                />
              </td>
              <td>{{ rangoTexto('Fósforo') }}</td>
              <td>{{ rangoUnidad('Fósforo') }}</td>
            </tr>
            <tr>
              <td>Magnesio</td>
              <td>
                <q-input
                  v-model.number="form.magnesio"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Magnesio', form.magnesio)"
                />
              </td>
              <td>{{ rangoTexto('Magnesio') }}</td>
              <td>{{ rangoUnidad('Magnesio') }}</td>
            </tr>
            <tr>
              <td>LDH</td>
              <td>
                <q-input
                  v-model.number="form.ldh"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('LDH', form.ldh)"
                />
              </td>
              <td>{{ rangoTexto('LDH') }}</td>
              <td>{{ rangoUnidad('LDH') }}</td>
            </tr>
            <tr>
              <td>Hierro sérico</td>
              <td>
                <q-input
                  v-model.number="form.hierro_serico"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Hierro sérico', form.hierro_serico)"
                />
              </td>
              <td>{{ rangoTexto('Hierro sérico') }}</td>
              <td>{{ rangoUnidad('Hierro sérico') }}</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- ORINA 24 HRS -->
          <div class="section-title q-mb-xs">Orina de 24 horas</div>
          <q-markup-table dense flat bordered square class="bg-white q-mb-md">
            <thead>
            <tr>
              <th class="text-left">Parámetro</th>
              <th class="text-left">Resultado</th>
              <th class="text-left">Rango de referencia</th>
              <th class="text-left">Unidad</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td>Creatinuria 24 hrs.</td>
              <td>
                <q-input
                  v-model.number="form.creatinuria_24h"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Creatinuria 24 hrs.', form.creatinuria_24h)"
                />
              </td>
              <td>{{ rangoTexto('Creatinuria 24 hrs.') }}</td>
              <td>{{ rangoUnidad('Creatinuria 24 hrs.') }}</td>
            </tr>
            <tr>
              <td>Proteinuria de 24 hrs.</td>
              <td>
                <q-input
                  v-model.number="form.proteinuria_24h"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Proteinuria de 24 hrs.', form.proteinuria_24h)"
                />
              </td>
              <td>{{ rangoTexto('Proteinuria de 24 hrs.') }}</td>
              <td>{{ rangoUnidad('Proteinuria de 24 hrs.') }}</td>
            </tr>
            <tr>
              <td>Volumen 24 h</td>
              <td>
                <q-input
                  v-model.number="form.volumen_24h"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('VOLUMEN', form.volumen_24h)"
                />
              </td>
              <td>{{ rangoTexto('VOLUMEN') }}</td>
              <td>{{ rangoUnidad('VOLUMEN') }}</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- HEMOGLOBINA GLICOSILADA -->
          <div class="section-title q-mb-xs">Control glucémico</div>
          <q-markup-table dense flat bordered square class="bg-white q-mb-md">
            <thead>
            <tr>
              <th class="text-left">Parámetro</th>
              <th class="text-left">Resultado</th>
              <th class="text-left">Rango de referencia</th>
              <th class="text-left">Unidad</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td>Hb glicosilada</td>
              <td>
                <q-input
                  v-model.number="form.hb_glicosilada"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Hb glicosilada', form.hb_glicosilada)"
                />
              </td>
              <td>{{ rangoTexto('Hb glicosilada') }}</td>
              <td>{{ rangoUnidad('Hb glicosilada') }}</td>
            </tr>
            <tr>
              <td>Hb A1C</td>
              <td>
                <q-input
                  v-model.number="form.hb_a1c"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('Hb A1C', form.hb_a1c)"
                />
              </td>
              <td>{{ rangoTexto('Hb A1C') }}</td>
              <td>{{ rangoUnidad('Hb A1C') }}</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- SEROLÓGICOS -->
          <div class="section-title q-mb-xs">Pruebas serológicas</div>
          <q-markup-table dense flat bordered square class="bg-white q-mb-md">
            <thead>
            <tr>
              <th class="text-left">Prueba</th>
              <th class="text-left">Resultado</th>
              <th class="text-left">Rango / Interpretación</th>
              <th class="text-left">Unidad</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td>ASO</td>
              <td>
                <q-input
                  v-model.number="form.aso"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('ASO', form.aso)"
                />
              </td>
              <td>{{ rangoTexto('ASO') }}</td>
              <td>{{ rangoUnidad('ASO') }}</td>
            </tr>
            <tr>
              <td>FR</td>
              <td>
                <q-input
                  v-model.number="form.fr"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('FR', form.fr)"
                />
              </td>
              <td>{{ rangoTexto('FR') }}</td>
              <td>{{ rangoUnidad('FR') }}</td>
            </tr>
            <tr>
              <td>PCR</td>
              <td>
                <q-input
                  v-model.number="form.pcr"
                  dense outlined type="number" step="0.01"
                  :input-class="inputRangeClass('PCR', form.pcr)"
                />
              </td>
              <td>{{ rangoTexto('PCR') }}</td>
              <td>{{ rangoUnidad('PCR') }}</td>
            </tr>
            <tr>
              <td>Prueba rápida de VIH</td>
              <td>
                <q-input
                  v-model="form.prueba_rapida_vih"
                  dense outlined
                  placeholder="Reactivo / No reactivo"
                />
              </td>
              <td>{{ rangoTexto('Prueba rápida de VIH') }}</td>
              <td>{{ rangoUnidad('Prueba rápida de VIH') }}</td>
            </tr>
            <tr>
              <td>RPR</td>
              <td>
                <q-input
                  v-model="form.rpr"
                  dense outlined
                  placeholder="Reactivo / No reactivo"
                />
              </td>
              <td>{{ rangoTexto('RPR') }}</td>
              <td>{{ rangoUnidad('RPR') }}</td>
            </tr>
            <tr>
              <td>Reacción de Widal</td>
              <td>
                <q-input
                  v-model="form.reaccion_widal"
                  dense outlined
                  placeholder="O-, H-, A-, B-"
                />
              </td>
              <td>{{ rangoTexto('Reacción de Widal') }}</td>
              <td>{{ rangoUnidad('Reacción de Widal') }}</td>
            </tr>
            <tr>
              <td>D.C.E.</td>
              <td>
                <q-input
                  v-model="form.dce"
                  dense outlined
                />
              </td>
              <td>{{ rangoTexto('D.C.E.') }}</td>
              <td>{{ rangoUnidad('D.C.E.') }}</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- OBSERVACIONES, MÉTODO, EQUIPO -->
          <div class="section-title q-mb-xs">Observaciones / Método / Equipo</div>
          <q-input
            v-model="form.observaciones"
            type="textarea"
            dense outlined autogrow
            class="bg-white q-mb-sm"
            placeholder="Observaciones clínicas relevantes…"
          />
          <div class="row q-col-gutter-sm q-mb-md">
            <div class="col-12 col-sm-4">
              <q-input v-model="form.metodo" dense outlined class="bg-white" label="Método" />
            </div>
            <div class="col-12 col-sm-8">
              <q-input v-model="form.equipo" dense outlined class="bg-white" label="Equipo" />
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
  name: 'QuimicaSanguineaPage',
  data () {
    return {
      solicitudId: this.$route.params.id,
      loading: false,
      header: null,
      formLoaded: false,
      rangos: [],
      form: {
        acido_urico: null,
        albumina: null,
        proteinas_totales: null,
        bilirrubina_total: null,
        bilirrubina_directa: null,
        bilirrubina_indirecta: null,
        got: null,
        gpt: null,
        fosfatasa_alcalina: null,
        ggt: null,
        amilasa: null,
        glucosa: null,
        urea: null,
        nus: null,
        creatinina: null,
        trigliceridos: null,
        colesterol_total: null,
        hdl_colesterol: null,
        ldl_colesterol: null,
        vldl_colesterol: null,
        ck_total: null,
        ck_mb: null,
        ferritina: null,
        hierro_serico: null,
        got_cinetico: null,
        gpt_cinetico: null,
        hb_glicosilada: null,
        hb_a1c: null,
        sodio: null,
        potasio: null,
        cloro: null,
        calcio: null,
        fosforo: null,
        magnesio: null,
        ldh: null,
        creatinuria_24h: null,
        proteinuria_24h: null,
        volumen_24h: null,
        aso: null,
        fr: null,
        pcr: null,
        prueba_rapida_vih: '',
        rpr: '',
        reaccion_widal: '',
        dce: '',
        observaciones: '',
        metodo: '',
        equipo: ''
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
        const { data } = await this.$axios.get(`/quimica-sanguinea/solicitud/${this.solicitudId}`)
        this.header = data.solicitud || null
        this.form = Object.assign({}, this.form, data.quimica || {})
        this.rangos = data.rangos || []
        this.formLoaded = true
      } catch (e) {
        const msg = e.response?.data?.message || e.message
        if (this.$alert?.error) {
          this.$alert.error('Error al cargar química sanguínea: ' + msg)
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
        await this.$axios.post(`/quimica-sanguinea/solicitud/${this.solicitudId}`, this.form)
        if (this.$alert?.success) {
          this.$alert.success('Química sanguínea guardada correctamente')
        } else {
          console.log('Química sanguínea guardada correctamente')
        }
      } catch (e) {
        const msg = e.response?.data?.message || e.message
        if (this.$alert?.error) {
          this.$alert.error('Error al guardar: ' + msg)
        } else {
          console.error(msg)
        }
      } finally {
        this.loading = false
      }
    },
    printPdf(){
      const url = this.$axios.defaults.baseURL + `/quimica-sanguinea/solicitud/${this.solicitudId}/pdf`
      window.open(url, '_blank')
    },
    onSubmit () {
      this.save()
    },
    getRango (nombre) {
      if (!Array.isArray(this.rangos)) return null
      return (
        this.rangos.find(
          r => (r.rango_nombre || '').toLowerCase() === (nombre || '').toLowerCase()
        ) || null
      )
    },
    rangoTexto (nombre) {
      const r = this.getRango(nombre)
      if (!r) return ''
      if (r.rango_minimo !== null && r.rango_maximo !== null) {
        return `${r.rango_minimo} - ${r.rango_maximo}`
      }
      if (r.interpretacion) {
        return r.interpretacion
      }
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
    },
    inputRangeClass (nombre, valor) {
      return [
        'text-right',
        this.isOutOfRange(nombre, valor) ? 'text-negative text-weight-bold' : ''
      ]
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

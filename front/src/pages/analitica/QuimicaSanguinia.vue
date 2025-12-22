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
            flat icon="refresh" label="Refrescar" no-caps
            class="q-mr-sm"
            :disable="loading"
            @click="load"
          />
          <q-btn
            flat icon="arrow_back" label="Volver" no-caps
            class="q-mr-xs"
            @click="$router.back()"
          />
          <q-btn
            color="primary" icon="save" label="Guardar" no-caps
            :loading="loading"
            @click="onSubmit"
          />
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

      <!-- DATOS SOLICITUD / PACIENTE (SIN COMPUTEDS) -->
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

    <!-- FORMULARIO -->
    <q-card flat bordered>
      <q-card-section class="q-pa-sm">
        <q-form @submit.prevent="onSubmit">

          <!-- =======================
               QUÍMICA BÁSICA / RENAL
               ======================= -->
          <div
            v-if="hasAnyServicios([
              'ÁCIDO ÚRICO',
              'ALBUMINA',
              'PROTEINAS TOTALES',
              'GLICEMIA',
              'UREA',
              'NITROGENO UREICO SERICO (NUS)',
              'CREATININA SÉRICA',
              'PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)',
              'PROTEINOGRAMA (PROTEÍNAS TOTALES, ALBÚMINA, GLOBULINA)'
            ])"
          >
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
              <tr v-if="canServicios(['ÁCIDO ÚRICO','PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)'])">
                <td>Ácido Úrico</td>
                <td>
                  <q-input v-model.number="form.acido_urico" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Acido Urico', form.acido_urico)" />
                </td>
                <td>{{ rangoTexto('Acido Urico') }}</td>
                <td>{{ rangoUnidad('Acido Urico') }}</td>
              </tr>

              <tr v-if="canServicios(['ALBUMINA','PROTEINOGRAMA (PROTEÍNAS TOTALES, ALBÚMINA, GLOBULINA)'])">
                <td>Albúmina</td>
                <td>
                  <q-input
                    v-model.number="form.albumina" dense outlined type="number" step="0.01"
                    @update:model-value="
                    form.globulina = form.proteinas_totales - form.albumina;
                    form.relacion_ag = form.albumina / form.globulina
"
                           :input-class="inputRangeClass('Albumina', form.albumina)" />
                </td>
                <td>{{ rangoTexto('Albumina') }}</td>
                <td>{{ rangoUnidad('Albumina') }}</td>
              </tr>

              <tr v-if="canServicios(['PROTEINAS TOTALES','PROTEINOGRAMA (PROTEÍNAS TOTALES, ALBÚMINA, GLOBULINA)'])">
                <td>Proteínas totales</td>
                <td>
                  <q-input
                    v-model.number="form.proteinas_totales" dense outlined type="number" step="0.01"
                    @update:model-value="form.globulina = form.proteinas_totales - form.albumina"
                           :input-class="inputRangeClass('Proteinas totales', form.proteinas_totales)" />
                </td>
                <td>{{ rangoTexto('Proteinas totales') }}</td>
                <td>{{ rangoUnidad('Proteinas totales') }}</td>
              </tr>

              <tr v-if="canServicios(['GLICEMIA','PRUEBA DE TOLERANCIA A LA GLUCOSA (3 MEDICIONES) (PTG)','PRUEBA DE TOLERANCIA A LA GLUCOSA (4 MEDICIONES) (PTG)'])">
                <td>Glucosa</td>
                <td>
                  <q-input v-model.number="form.glucosa" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Glucosa', form.glucosa)" />
                </td>
                <td>{{ rangoTexto('Glucosa') }}</td>
                <td>{{ rangoUnidad('Glucosa') }}</td>
              </tr>

              <tr v-if="canServicios(['UREA','PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)'])">
                <td>Urea</td>
                <td>
                  <q-input v-model.number="form.urea" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Urea', form.urea)" />
                </td>
                <td>{{ rangoTexto('Urea') }}</td>
                <td>{{ rangoUnidad('Urea') }}</td>
              </tr>

              <tr v-if="canServicios('NITROGENO UREICO SERICO (NUS)')">
                <td>NUS</td>
                <td>
                  <q-input v-model.number="form.nus" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('NUS', form.nus)" />
                </td>
                <td>{{ rangoTexto('NUS') }}</td>
                <td>{{ rangoUnidad('NUS') }}</td>
              </tr>

              <tr v-if="canServicios(['CREATININA SÉRICA','PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)','CLEARENCE DE CREATININA'])">
                <td>Creatinina</td>
                <td>
                  <q-input v-model.number="form.creatinina" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Creatinina', form.creatinina)" />
                </td>
                <td>{{ rangoTexto('Creatinina') }}</td>
                <td>{{ rangoUnidad('Creatinina') }}</td>
              </tr>
<!--              Globulina = Proteinas_Totales – Albumina-->

              <tr v-if="canServicios('PROTEINOGRAMA (PROTEÍNAS TOTALES, ALBÚMINA, GLOBULINA)')">
                <td>Globulina</td>
                <td>
                  <!--              Relacion A/G= Albumina/Globulina-->
                  <q-input
                    v-model.number="form.globulina" dense outlined type="number" step="0.01"
                    @update:model-value="form.relacion_ag = form.albumina / form.globulina"
                           :input-class="inputRangeClass('Globulina', form.globulina)" />
                </td>
                <td>{{ rangoTexto('Globulina') }}</td>
                <td>{{ rangoUnidad('Globulina') }}</td>
              </tr>
              <tr v-if="canServicios('PROTEINOGRAMA (PROTEÍNAS TOTALES, ALBÚMINA, GLOBULINA)')">
                <td>Relación A/G</td>
                <td>
                  <q-input v-model.number="form.relacion_ag" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Relación A/G', form.relacion_ag)" />
                </td>
                <td>{{ rangoTexto('Relación A/G') }}</td>
                <td>{{ rangoUnidad('Relación A/G') }}</td>
              </tr>
              </tbody>
            </q-markup-table>
          </div>

          <!-- =======================
               PERFIL HEPÁTICO
               ======================= -->
          <div
            v-if="hasAnyServicios([
              'BILIRRUBINAS TOTALES Y FRACCIONADAS',
              'TRANSAMINASAS GOT',
              'TRANSAMINASAS GPT',
              'FOSFATASA ALCALINA',
              'GAMA GLUTAMIL TRANSFERASA (GGT)',
              'AMILASA',
              'PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)'
            ])"
          >
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
              <tr v-if="canServicios(['BILIRRUBINAS TOTALES Y FRACCIONADAS','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)'])">
                <td>Bilirrubina Total</td>
                <td>
<!--                  Bilirrubina Indirecta = Bilirrubina Total - Bilirrubina Directa-->
                  <q-input
                    v-model.number="form.bilirrubina_total" dense outlined type="number" step="0.01"
                    @update:model-value="form.bilirrubina_indirecta = form.bilirrubina_total - form.bilirrubina_directa"
                           :input-class="inputRangeClass('Bilirrubina Total', form.bilirrubina_total)" />
                </td>
                <td>{{ rangoTexto('Bilirrubina Total') }}</td>
                <td>{{ rangoUnidad('Bilirrubina Total') }}</td>
              </tr>

              <tr v-if="canServicios(['BILIRRUBINAS TOTALES Y FRACCIONADAS','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)'])">
                <td>Bilirrubina Directa</td>
                <td>
<!--                  Bilirrubina Indirecta = Bilirrubina Total - Bilirrubina Directa-->
                  <q-input
                    v-model.number="form.bilirrubina_directa" dense outlined type="number" step="0.01"
                    @update:model-value="form.bilirrubina_indirecta = form.bilirrubina_total - form.bilirrubina_directa"
                           :input-class="inputRangeClass('Bilirrubina Directa', form.bilirrubina_directa)" />
                </td>
                <td>{{ rangoTexto('Bilirrubina Directa') }}</td>
                <td>{{ rangoUnidad('Bilirrubina Directa') }}</td>
              </tr>

              <tr v-if="canServicios(['BILIRRUBINAS TOTALES Y FRACCIONADAS','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)'])">
                <td>Bilirrubina Indirecta</td>
                <td>
                  <q-input v-model.number="form.bilirrubina_indirecta" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Bilirrubina Indirecta', form.bilirrubina_indirecta)" />
                </td>
                <td>{{ rangoTexto('Bilirrubina Indirecta') }}</td>
                <td>{{ rangoUnidad('Bilirrubina Indirecta') }}</td>
              </tr>

              <tr v-if="canServicios(['TRANSAMINASAS GOT','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)'])">
                <td>G.O.T. (TGO)</td>
                <td>
                  <q-input v-model.number="form.got" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('G.O.T. (TGO)', form.got)" />
                </td>
                <td>{{ rangoTexto('G.O.T. (TGO)') }}</td>
                <td>{{ rangoUnidad('G.O.T. (TGO)') }}</td>
              </tr>

              <tr v-if="canServicios(['TRANSAMINASAS GPT','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)'])">
                <td>G.P.T. (TGP)</td>
                <td>
                  <q-input v-model.number="form.gpt" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('G.P.T. (TGP)', form.gpt)" />
                </td>
                <td>{{ rangoTexto('G.P.T. (TGP)') }}</td>
                <td>{{ rangoUnidad('G.P.T. (TGP)') }}</td>
              </tr>

              <tr v-if="canServicios(['FOSFATASA ALCALINA','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)'])">
                <td>Fosfatasa Alcalina</td>
                <td>
                  <q-input v-model.number="form.fosfatasa_alcalina" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Fosfatasa Alcalina', form.fosfatasa_alcalina)" />
                </td>
                <td>{{ rangoTexto('Fosfatasa Alcalina') }}</td>
                <td>{{ rangoUnidad('Fosfatasa Alcalina') }}</td>
              </tr>

              <tr v-if="canServicios(['GAMA GLUTAMIL TRANSFERASA (GGT)','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)'])">
                <td>GGT</td>
                <td>
                  <q-input v-model.number="form.ggt" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('GGT', form.ggt)" />
                </td>
                <td>{{ rangoTexto('GGT') }}</td>
                <td>{{ rangoUnidad('GGT') }}</td>
              </tr>

              <tr v-if="canServicios('AMILASA')">
                <td>Amilasa</td>
                <td>
                  <q-input v-model.number="form.amilasa" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Amilasa', form.amilasa)" />
                </td>
                <td>{{ rangoTexto('Amilasa') }}</td>
                <td>{{ rangoUnidad('Amilasa') }}</td>
              </tr>
              </tbody>
            </q-markup-table>
          </div>

          <!-- =======================
               PERFIL LIPÍDICO
               ======================= -->
          <div
            v-if="hasAnyServicios([
              'COLESTEROL',
              'TRIGLICÉRIDOS',
              'HDLc, LDLc, VLDLc',
              'PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)'
            ])"
          >
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
              <tr v-if="canServicios(['COLESTEROL','PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)'])">
                <td>Colesterol total</td>
                <td>
                  <q-input v-model.number="form.colesterol_total" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Colesterol total', form.colesterol_total)" />
                </td>
                <td>{{ rangoTexto('Colesterol total') }}</td>
                <td>{{ rangoUnidad('Colesterol total') }}</td>
              </tr>

              <tr v-if="canServicios(['TRIGLICÉRIDOS','PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)'])">
                <td>Triglicéridos</td>
                <td>
                  <q-input v-model.number="form.trigliceridos" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Triglicéridos', form.trigliceridos)" />
                </td>
                <td>{{ rangoTexto('Triglicéridos') }}</td>
                <td>{{ rangoUnidad('Triglicéridos') }}</td>
              </tr>

              <tr v-if="canServicios(['HDLc, LDLc, VLDLc','PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)'])">
                <td>HDL</td>
                <td>
                  <q-input v-model.number="form.hdl_colesterol" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('HDL Colesterol', form.hdl_colesterol)" />
                </td>
                <td>{{ rangoTexto('HDL Colesterol') }}</td>
                <td>{{ rangoUnidad('HDL Colesterol') }}</td>
              </tr>

              <tr v-if="canServicios(['HDLc, LDLc, VLDLc','PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)'])">
                <td>LDL</td>
                <td>
                  <q-input v-model.number="form.ldl_colesterol" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('LDL Colesterol', form.ldl_colesterol)" />
                </td>
                <td>{{ rangoTexto('LDL Colesterol') }}</td>
                <td>{{ rangoUnidad('LDL Colesterol') }}</td>
              </tr>

              <tr v-if="canServicios(['HDLc, LDLc, VLDLc','PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)'])">
                <td>VLDL</td>
                <td>
                  <q-input v-model.number="form.vldl_colesterol" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('VLDL Colesterol', form.vldl_colesterol)" />
                </td>
                <td>{{ rangoTexto('VLDL Colesterol') }}</td>
                <td>{{ rangoUnidad('VLDL Colesterol') }}</td>
              </tr>
              </tbody>
            </q-markup-table>
          </div>

          <!-- =======================
               ELECTROLITOS / IONOGRAMA
               ======================= -->
          <div
            v-if="hasAnyServicios([
              'ELECTROLITOS (SODIO, POTASIO, CLORO) (NA,K,CL)',
              'IONOGRAMA (NA,K,CL,CA,Mg,P)',
              'CALCIO',
              'FÓSFORO',
              'MAGNESIO'
            ])"
          >
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
              <tr v-if="canServicios(['ELECTROLITOS (SODIO, POTASIO, CLORO) (NA,K,CL)','IONOGRAMA (NA,K,CL,CA,Mg,P)'])">
                <td>Sodio</td>
                <td>
                  <q-input v-model.number="form.sodio" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Sodio', form.sodio)" />
                </td>
                <td>{{ rangoTexto('Sodio') }}</td>
                <td>{{ rangoUnidad('Sodio') }}</td>
              </tr>

              <tr v-if="canServicios(['ELECTROLITOS (SODIO, POTASIO, CLORO) (NA,K,CL)','IONOGRAMA (NA,K,CL,CA,Mg,P)'])">
                <td>Potasio</td>
                <td>
                  <q-input v-model.number="form.potasio" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Potasio', form.potasio)" />
                </td>
                <td>{{ rangoTexto('Potasio') }}</td>
                <td>{{ rangoUnidad('Potasio') }}</td>
              </tr>

              <tr v-if="canServicios(['ELECTROLITOS (SODIO, POTASIO, CLORO) (NA,K,CL)','IONOGRAMA (NA,K,CL,CA,Mg,P)'])">
                <td>Cloro</td>
                <td>
                  <q-input v-model.number="form.cloro" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Cloro', form.cloro)" />
                </td>
                <td>{{ rangoTexto('Cloro') }}</td>
                <td>{{ rangoUnidad('Cloro') }}</td>
              </tr>

              <tr v-if="canServicios(['CALCIO','IONOGRAMA (NA,K,CL,CA,Mg,P)'])">
                <td>Calcio</td>
                <td>
                  <q-input v-model.number="form.calcio" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Calcio', form.calcio)" />
                </td>
                <td>{{ rangoTexto('Calcio') }}</td>
                <td>{{ rangoUnidad('Calcio') }}</td>
              </tr>

              <tr v-if="canServicios(['FÓSFORO','IONOGRAMA (NA,K,CL,CA,Mg,P)'])">
                <td>Fósforo</td>
                <td>
                  <q-input v-model.number="form.fosforo" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Fósforo', form.fosforo)" />
                </td>
                <td>{{ rangoTexto('Fósforo') }}</td>
                <td>{{ rangoUnidad('Fósforo') }}</td>
              </tr>

              <tr v-if="canServicios(['MAGNESIO','IONOGRAMA (NA,K,CL,CA,Mg,P)'])">
                <td>Magnesio</td>
                <td>
                  <q-input v-model.number="form.magnesio" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Magnesio', form.magnesio)" />
                </td>
                <td>{{ rangoTexto('Magnesio') }}</td>
                <td>{{ rangoUnidad('Magnesio') }}</td>
              </tr>
              </tbody>
            </q-markup-table>
          </div>

          <!-- =======================
               ORINA 24 HRS
               ======================= -->
          <div
            v-if="hasAnyServicios([
              'CREATININA EN ORINA (CREATINURIA)',
              'PROTEINURIA 24 HRS'
            ])"
          >
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
              <tr v-if="canServicios('CREATININA EN ORINA (CREATINURIA)')">
                <td>Creatinuria 24 hrs.</td>
                <td>
                  <q-input v-model.number="form.creatinuria_24h" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Creatinuria 24 hrs.', form.creatinuria_24h)" />
                </td>
                <td>{{ rangoTexto('Creatinuria 24 hrs.') }}</td>
                <td>{{ rangoUnidad('Creatinuria 24 hrs.') }}</td>
              </tr>

              <tr v-if="canServicios('PROTEINURIA 24 HRS')">
                <td>Proteinuria de 24 hrs.</td>
                <td>
                  <q-input v-model.number="form.proteinuria_24h" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Proteinuria de 24 hrs.', form.proteinuria_24h)" />
                </td>
                <td>{{ rangoTexto('Proteinuria de 24 hrs.') }}</td>
                <td>{{ rangoUnidad('Proteinuria de 24 hrs.') }}</td>
              </tr>

              <tr v-if="canServicios(['PROTEINURIA 24 HRS','CREATININA EN ORINA (CREATINURIA)'])">
                <td>Volumen 24 h</td>
                <td>
                  <q-input v-model.number="form.volumen_24h" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('VOLUMEN', form.volumen_24h)" />
                </td>
                <td>{{ rangoTexto('VOLUMEN') }}</td>
                <td>{{ rangoUnidad('VOLUMEN') }}</td>
              </tr>
              </tbody>
            </q-markup-table>
          </div>

          <!-- =======================
               CONTROL GLUCÉMICO
               ======================= -->
          <div
            v-if="hasAnyServicios([
              'HEMOGLOBINA GLICOSILADA A1c'
            ])"
          >
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
              <tr v-if="canServicios('HEMOGLOBINA GLICOSILADA A1c')">
                <td>Hb A1C</td>
                <td>
                  <q-input v-model.number="form.hb_a1c" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('Hb A1C', form.hb_a1c)" />
                </td>
                <td>{{ rangoTexto('Hb A1C') }}</td>
                <td>{{ rangoUnidad('Hb A1C') }}</td>
              </tr>
              </tbody>
            </q-markup-table>
          </div>

          <!-- =======================
               SEROLÓGICOS / RÁPIDAS
               ======================= -->
          <div
            v-if="hasAnyServicios([
              'ASTO O ASO',
              'FACTOR REUMATOIDEO (FR)',
              'PCR CUALITATIVO (PROTEÍNA C REACTIVA)',
              'PRUEBA RAPIDA PARA VIH',
              'PRUEBA RAPIDA PARA SIFILIS',
              'PRUEBA RAPIDA PARA CHAGAS',
              'PRUEBA RAPIDA PARA HEPATITIS B',
              'PRUEBA RAPIDA PARA HEPATITIS C',
              'PRUEBA RAPIDA PARA TROPONINA',
              'REACCIÓN DE WIDAL',
              'RPR- VDRL',
              'TEST DE EMBARAZO EN SUERO (GONADOTROFINA CORIÓNICA HUMANA CUALITATIVO)'
            ])"
          >
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
              <tr v-if="canServicios('ASTO O ASO')">
                <td>ASO</td>
                <td>
                  <q-input v-model.number="form.aso" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('ASO', form.aso)" />
                </td>
                <td>{{ rangoTexto('ASO') }}</td>
                <td>{{ rangoUnidad('ASO') }}</td>
              </tr>

              <tr v-if="canServicios('FACTOR REUMATOIDEO (FR)')">
                <td>FR</td>
                <td>
                  <q-input v-model.number="form.fr" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('FR', form.fr)" />
                </td>
                <td>{{ rangoTexto('FR') }}</td>
                <td>{{ rangoUnidad('FR') }}</td>
              </tr>

              <tr v-if="canServicios('PCR CUALITATIVO (PROTEÍNA C REACTIVA)')">
                <td>PCR</td>
                <td>
                  <q-input v-model.number="form.pcr" dense outlined type="number" step="0.01"
                           :input-class="inputRangeClass('PCR', form.pcr)" />
                </td>
                <td>{{ rangoTexto('PCR') }}</td>
                <td>{{ rangoUnidad('PCR') }}</td>
              </tr>

              <tr v-if="canServicios('PRUEBA RAPIDA PARA VIH')">
                <td>Prueba rápida VIH</td>
                <td><q-input v-model="form.prueba_rapida_vih" dense outlined placeholder="Reactivo / No reactivo" /></td>
                <td>{{ rangoTexto('Prueba rápida VIH') }}</td>
                <td>{{ rangoUnidad('Prueba rápida VIH') }}</td>
              </tr>

              <tr v-if="canServicios('RPR- VDRL')">
                <td>RPR / VDRL</td>
                <td><q-input v-model="form.rpr" dense outlined placeholder="Reactivo / No reactivo" /></td>
                <td>{{ rangoTexto('RPR / VDRL') }}</td>
                <td>{{ rangoUnidad('RPR / VDRL') }}</td>
              </tr>

              <tr v-if="canServicios('REACCIÓN DE WIDAL')">
                <td>Reacción de Widal</td>
                <td><q-input v-model="form.reaccion_widal" dense outlined placeholder="O-, H-, A-, B-" /></td>
                <td>{{ rangoTexto('Reacción de Widal') }}</td>
                <td>{{ rangoUnidad('Reacción de Widal') }}</td>
              </tr>
              </tbody>
            </q-markup-table>
          </div>

          <!-- =======================
               OBSERVACIONES / MÉTODO / EQUIPO
               ======================= -->
          <div
            v-if="hasAnyServicios([
              'PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)',
              'PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)',
              'PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)',
              'IONOGRAMA (NA,K,CL,CA,Mg,P)',
              'ELECTROLITOS (SODIO, POTASIO, CLORO) (NA,K,CL)',
              'ÁCIDO ÚRICO','ALBUMINA','PROTEINAS TOTALES','GLICEMIA','UREA','NITROGENO UREICO SERICO (NUS)','CREATININA SÉRICA',
              'BILIRRUBINAS TOTALES Y FRACCIONADAS','TRANSAMINASAS GOT','TRANSAMINASAS GPT','FOSFATASA ALCALINA','GAMA GLUTAMIL TRANSFERASA (GGT)','AMILASA',
              'COLESTEROL','TRIGLICÉRIDOS','HDLc, LDLc, VLDLc','HEMOGLOBINA GLICOSILADA A1c',
              'ASTO O ASO','FACTOR REUMATOIDEO (FR)','PCR CUALITATIVO (PROTEÍNA C REACTIVA)','PRUEBA RAPIDA PARA VIH','RPR- VDRL','REACCIÓN DE WIDAL'
            ])"
          >
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
              <div class="col-12 col-md-4">
                <div class="section-title q-mb-xs">Muestra rechazada</div>

                <q-toggle v-model="form.muestra_rechazada" label="¿Muestra rechazada?" true-value="Si" false-value="No"
                          @update:model-value="form.muestra_observacion = ''"
                />
              </div>
              <div class="col-12 col-md-4" v-if="form.muestra_rechazada === 'Si'">
                <div class="section-title q-mb-xs">Observación</div>
                <q-input
                  v-model="form.muestra_observacion"
                  type="textarea"
                  dense
                  outlined
                  label="Observación de la muestra"
                />
              </div>
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
        hb_a1c: null,
        sodio: null,
        potasio: null,
        cloro: null,
        calcio: null,
        fosforo: null,
        magnesio: null,
        creatinuria_24h: null,
        proteinuria_24h: null,
        volumen_24h: null,
        aso: null,
        fr: null,
        pcr: null,
        prueba_rapida_vih: '',
        rpr: '',
        reaccion_widal: '',
        observaciones: '',
        metodo: '',
        equipo: ''
      }
    }
  },

  mounted () {
    this.load()
  },

  methods: {
    // ========= servicio match (IGUAL QUE HEMATOLOGÍA) =========
    canServicios (can) {
      const norm = (v) => String(v ?? '').replace(/\s+/g, ' ').trim().toLowerCase()
      if (!this.header || !Array.isArray(this.header.servicios)) return false
      const targets = Array.isArray(can) ? can : [can]
      const wanted = targets.map(norm)
      return this.header.servicios.some(s => wanted.includes(norm(s.nombre)))
    },

    // muestra sección si existe al menos 1 servicio
    hasAnyServicios (list) {
      const arr = Array.isArray(list) ? list : [list]
      return arr.some(x => this.canServicios(x))
    },

    // ========= api =========
    async load () {
      try {
        this.loading = true
        this.formLoaded = false
        const { data } = await this.$axios.get(`/quimica-sanguinea/solicitud/${this.solicitudId}`)
        this.header = data.solicitud || null
        this.form = Object.assign({}, this.form, data.quimica || {})
        const muestra_rechazada = data.solicitud?.muestra_rechazada || 'No'
        const muestra_observacion = data.solicitud?.muestra_observacion || ''
        this.form.muestra_rechazada = muestra_rechazada
        this.form.muestra_observacion = muestra_observacion
        this.rangos = data.rangos || []
        this.formLoaded = true
      } catch (e) {
        const msg = e.response?.data?.message || e.message
        if (this.$alert?.error) this.$alert.error('Error al cargar química sanguínea: ' + msg)
        else console.error(msg)
      } finally {
        this.loading = false
      }
    },

    async save () {
      try {
        this.loading = true
        await this.$axios.post(`/quimica-sanguinea/solicitud/${this.solicitudId}`, this.form)
        if (this.$alert?.success) this.$alert.success('Química sanguínea guardada correctamente')
        else console.log('Química sanguínea guardada correctamente')
        this.$router.back()
      } catch (e) {
        const msg = e.response?.data?.message || e.message
        if (this.$alert?.error) this.$alert.error('Error al guardar: ' + msg)
        else console.error(msg)
      } finally {
        this.loading = false
      }
    },

    printPdf () {
      let code = this.form.code || 'N/A'
      const url = this.$axios.defaults.baseURL + `/quimica-sanguinea/solicitud/${code}/pdf`
      window.open(url, '_blank')
    },

    onSubmit () {
      this.save()
    },

    // ========= rangos =========
    getRango (nombre) {
      if (!Array.isArray(this.rangos)) return null
      return this.rangos.find(
        r => (r.rango_nombre || '').toLowerCase() === (nombre || '').toLowerCase()
      ) || null
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
    },

    inputRangeClass (nombre, valor) {
      return ['text-right', this.isOutOfRange(nombre, valor) ? 'text-negative text-weight-bold' : '']
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

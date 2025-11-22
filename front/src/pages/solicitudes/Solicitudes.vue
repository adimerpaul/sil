<template>
  <q-page class="q-pa-sm">
    <!-- FILTROS -->
    <q-card flat bordered>
      <q-card-section class="row items-center q-col-gutter-xs">
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.from" type="date" dense outlined label="Desde" />
        </div>
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.to" type="date" dense outlined label="Hasta" />
        </div>
        <div class="col-12 col-sm-3">
          <q-select
            v-model="filters.estado"
            :options="['', 'CREADO', 'ATENDIENDO', 'FINALIZADO']"
            dense outlined
            label="Estado"
          />
        </div>
      </q-card-section>

      <q-card-section class="row items-center q-col-gutter-xs">
        <div class="col-12 col-sm-6">
          <q-input dense outlined v-model="filter" label="Buscar">
            <template #append><q-icon name="search" /></template>
          </q-input>
        </div>
        <div class="col-12 col-sm-6 text-right">
          <q-btn
            color="primary"
            icon="search"
            label="Filtrar"
            no-caps
            class="q-mr-xs"
            :loading="loading"
            @click="getSolicitudes"
          />
          <q-btn
            color="positive"
            icon="add_circle_outline"
            label="Nueva"
            no-caps
            :loading="loading"
            @click="nuevo"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- TABLA -->
    <q-table
      class="q-mt-sm"
      :rows="rows"
      :columns="columns"
      row-key="id"
      dense flat bordered
      :rows-per-page-options="[0]"
      :filter="filter"
      title="Solicitudes"
    >
      <template #body-cell-actions="props">
        <q-td :props="props">
          <q-btn-dropdown color="primary" label="Opciones" dense size="10px" no-caps>
            <q-list>
              <q-item clickable v-close-popup @click="editar(props.row)">
                <q-item-section avatar><q-icon name="edit" /></q-item-section>
                <q-item-section>Editar</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="eliminar(props.row.id)">
                <q-item-section avatar><q-icon name="delete" /></q-item-section>
                <q-item-section>Eliminar</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </q-td>
      </template>
    </q-table>

    <!-- DIÁLOGO -->
    <q-dialog v-model="dialog">
      <q-card style="max-width: 780px;">
        <q-card-section class="row items-center q-pa-sm">
          <div class="text-subtitle1">
            {{ editando ? 'Editar solicitud' : 'Nueva solicitud' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-sm">
          <q-form @submit="guardar">
            <!-- Paciente -->
            <div class="row items-center q-mb-xs">
              <q-icon name="person" size="18px" class="q-mr-xs" />
              <div class="text-subtitle2">Datos del paciente</div>
            </div>
            <div class="row q-col-gutter-xs">
<!--              <div class="col-12 col-sm-4">-->
<!--                <q-input-->
<!--                  v-model="searchCi"-->
<!--                  label="CI (buscar)"-->
<!--                  dense outlined-->
<!--                >-->
<!--                  <template #append>-->
<!--                    <q-btn flat dense icon="search" @click="buscarPacientePorCi" />-->
<!--                  </template>-->
<!--                </q-input>-->
<!--              </div>-->
<!--              <div class="col-12 col-sm-8">-->
<!--                <q-select-->
<!--                  v-model="solicitud.paciente_id"-->
<!--                  :options="pacientesOptions"-->
<!--                  option-value="id"-->
<!--                  option-label="nombre_completo"-->
<!--                  emit-value-->
<!--                  map-options-->
<!--                  dense-->
<!--                  outlined-->
<!--                  clearable-->
<!--                  label="Paciente (opcional)"-->
<!--                  @update:model-value="onSelectPaciente"-->
<!--                />-->
<!--              </div>-->
              <div class="col-6 col-sm-3">
                <q-input v-model="solicitud.paciente_ci" label="CI" dense outlined  @update:model-value="onChangeCi" debounce="300" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="solicitud.paciente_nombre" label="Nombre" dense outlined />
              </div>
              <div class="col-6 col-sm-3">
                <q-input v-model="solicitud.paciente_telefono" label="Teléfono" dense outlined />
              </div>

              <div class="col-12">
                <q-input v-model="solicitud.paciente_direccion" label="Dirección" dense outlined />
              </div>

              <div class="col-6 col-sm-4">
                <q-input
                  v-model="solicitud.paciente_fecha_nac"
                  type="date"
                  label="F. nacimiento"
                  dense outlined
                />
              </div>
              <div class="col-6 col-sm-4">
                <q-select
                  v-model="solicitud.paciente_genero"
                  :options="['F', 'M', 'OTRO']"
                  label="Género"
                  dense
                  outlined
                  clearable
                />
              </div>
              <div class="col-12 col-sm-4">
                <q-input
                  v-model.number="solicitud.paciente_edad"
                  type="number"
                  label="Edad"
                  dense outlined
                />
              </div>
            </div>

            <q-separator class="q-my-sm" />

            <!-- Doctor -->
            <div class="row items-center q-mb-xs">
              <q-icon name="person" size="18px" class="q-mr-xs" />
              <div class="text-subtitle2">Datos del médico solicitante</div>
            </div>
            <div class="row q-col-gutter-xs">
              <div class="col-12 col-sm-12">
                <q-select
                  v-model="solicitud.doctor_id"
                  :options="doctoresOptions"
                  option-value="id"
                  :option-label="doctor => doctor.nombre + ' (' + doctor.especialidad + ')' + (doctor.telefono ? ' - ' + doctor.telefono : '')"
                  emit-value
                  map-options
                  dense
                  outlined
                  clearable
                  label="Doctor (opcional)"
                  @update:model-value="onSelectDoctor"
                />
              </div>
<!--              <div class="col-12 col-sm-6">-->
<!--                <q-input-->
<!--                  v-model="solicitud.doctor_nombre"-->
<!--                  label="Nombre del doctor"-->
<!--                  dense outlined-->
<!--                />-->
<!--              </div>-->

<!--              <div class="col-12 col-sm-4">-->
<!--                <q-input-->
<!--                  v-model="solicitud.doctor_especialidad"-->
<!--                  label="Especialidad"-->
<!--                  dense outlined-->
<!--                />-->
<!--              </div>-->
<!--              <div class="col-6 col-sm-4">-->
<!--                <q-input-->
<!--                  v-model="solicitud.doctor_ci"-->
<!--                  label="CI doctor"-->
<!--                  dense outlined-->
<!--                />-->
<!--              </div>-->
<!--              <div class="col-6 col-sm-4">-->
<!--                <q-input-->
<!--                  v-model="solicitud.doctor_registro"-->
<!--                  label="Registro prof."-->
<!--                  dense outlined-->
<!--                />-->
<!--              </div>-->

<!--              <div class="col-6 col-sm-6">-->
<!--                <q-input-->
<!--                  v-model="solicitud.doctor_telefono"-->
<!--                  label="Teléfono doctor"-->
<!--                  dense outlined-->
<!--                />-->
<!--              </div>-->
<!--              <div class="col-6 col-sm-6">-->
<!--                <q-input-->
<!--                  v-model="solicitud.doctor_email"-->
<!--                  label="Email doctor"-->
<!--                  dense outlined-->
<!--                />-->
<!--              </div>-->
            </div>

            <q-separator class="q-my-sm" />

            <!-- Datos de la solicitud -->
            <div class="row items-center q-mb-xs">
              <q-icon name="assignment" size="18px" class="q-mr-xs" />
              <div class="text-subtitle2">Datos de la solicitud</div>
            </div>
            <div class="row q-col-gutter-xs">
<!--              <div class="col-6 col-sm-3">-->
<!--                <q-input-->
<!--                  v-model="solicitud.codigo_solicitud"-->
<!--                  label="Código"-->
<!--                  dense outlined-->
<!--                />-->
<!--              </div>-->
              <div class="col-6 col-sm-2">
                <q-toggle
                  v-model="solicitud.tipo_atencion"
                  true-value="SI"
                  false-value="NO"
                  dense
                >
                  {{ solicitud.tipo_atencion === 'SI' ? 'SUS SI' : 'SUS NO' }}
                </q-toggle>
              </div>
              <div class="col-6 col-sm-4">
                <q-input
                  v-if="solicitud.tipo_atencion === 'NO'"
                  v-model="solicitud.tipo_otro"
                  label="Especificar"
                  dense outlined
                />
<!--                select establecmeintos-->
                <q-select
                  v-else
                  v-model="solicitud.establecimiento_salud"
                  :options="establecimientos"
                  option-label="nombre"
                  option-value="nombre"
                  emit-value
                  map-options
                  label="Establecimiento de salud"
                  dense outlined
                />
<!--                <pre>{{ solicitud.establecimiento_salud }}</pre>-->
              </div>

<!--              <div class="col-12 col-sm-6">-->
<!--                <q-input-->
<!--                  v-model="solicitud.establecimiento_salud"-->
<!--                  label="Establecimiento de salud"-->
<!--                  dense outlined-->
<!--                />-->
<!--              </div>-->
<!--              <div class="col-12 col-sm-6">-->
<!--                <q-input-->
<!--                  v-model="solicitud.zona_establecimiento"-->
<!--                  label="Zona establecimiento"-->
<!--                  dense outlined-->
<!--                />-->
<!--              </div>-->

              <div class="col-12 col-md-6">
                <q-input
                  v-model="solicitud.diagnostico_clinico"
                  type="textarea"
                  label="Diagnóstico clínico principal"
                  dense outlined autogrow
                />
              </div>

<!--              <div class="col-12 col-sm-4">-->
<!--                <q-select-->
<!--                  v-model="solicitud.estado"-->
<!--                  :options="['CREADO', 'ATENDIENDO', 'FINALIZADO']"-->
<!--                  label="Estado"-->
<!--                  dense outlined-->
<!--                />-->
<!--              </div>-->
            </div>

            <q-separator class="q-my-sm" />

            <!-- Servicios -->
            <div class="row items-center q-mb-xs">
              <q-icon name="biotech" size="18px" class="q-mr-xs" />
              <div class="text-subtitle2">Servicios solicitados</div>
            </div>
            <div class="row q-col-gutter-xs">
              <div class="col-12">
                <div v-for="area in areas" :key="area.name" class="q-mb-sm">
                  <div class="text-bold q-mb-xs">{{ area.name }}</div>
                  <div class="row q-col-gutter-xs">
                    <div
                      v-for="servicio in area.servicios"
                      :key="servicio.codigo"
                      class="col-12 col-sm-6"
                    >
                      <q-checkbox
                        v-model="servicio.seleccionado"
                        :true-value="1"
                        :false-value="0"
                        :label="`${servicio.nombre} (Bs. ${servicio.precio})`"
                        dense
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="text-right q-mt-sm">
              <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
              <q-btn
                color="primary"
                label="Guardar"
                type="submit"
                class="q-ml-xs"
                :loading="loading"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment';

export default {
  name: 'SolicitudesPage',
  data () {
    return {
      rows: [],
      columns: [
        {name: 'actions', label: 'Acciones', align: 'center'},
        {name: 'id', label: 'ID', field: 'id', align: 'left'},
        {
          name: 'fecha_solicitud',
          label: 'Fecha',
          field: row => row.fecha_solicitud,
          format: v => v || ''
        },
        {
          name: 'paciente',
          label: 'Paciente',
          field: row => row.paciente?.nombre_completo || row.paciente_nombre || ''
        },
        {
          name: 'doctor',
          label: 'Doctor',
          field: row => row.doctor?.nombre || row.doctor_nombre || ''
        },
        {name: 'tipo_atencion', label: 'Tipo atención', field: 'tipo_atencion'},
        {name: 'estado', label: 'Estado', field: 'estado'}
      ],
      filter: '',
      dialog: false,
      editando: false,
      loading: false,

      solicitud: {},
      filters: {
        from: moment().format('YYYY-MM-DD'),
        to: moment().format('YYYY-MM-DD'),
        tipo_atencion: '',
        estado: ''
      },

      pacientesOptions: [],
      doctoresOptions: [],
      searchCi: '',
      areas: [
        {
          "name": "HEMATOLOGIA",
          "servicios": [
            { "codigo": 1, "nombre": "COAGULOGRAMA (TP, RECUENTO DE PLAQUETAS, APTT)", "metodo": "M/SA", "precio": 55 },
            { "codigo": 2, "nombre": "ERITROSEDIMENTACIÓN (VSG-VES)", "metodo": "M", "precio": 15 },
            { "codigo": 3, "nombre": "FIBRINÓGENO", "metodo": "M", "precio": 15 },
            { "codigo": 4, "nombre": "FROTIS SANGUÍNEO / LEUCOGRAMA", "metodo": "M", "precio": 15 },
            { "codigo": 5, "nombre": "GRUPO SANGUÍNEO Y FACTOR", "metodo": "M", "precio": 15 },
            { "codigo": 6, "nombre": "HEMOGRAMA COMPLETO + PLAQUETAS", "metodo": "A", "precio": 30 },
            { "codigo": 7, "nombre": "HEMATOCRITO Y HEMOGLOBINA", "metodo": "A", "precio": 15 },
            { "codigo": 8, "nombre": "ÍNDICES HEMATIMÉTRICOS", "metodo": "A", "precio": 20 },
            { "codigo": 9, "nombre": "MORFOLOGÍA DE GLÓBULOS ROJOS", "metodo": "M", "precio": 15 },
            { "codigo": 10, "nombre": "RECUENTO DE PLAQUETAS", "metodo": "M/A", "precio": 15 },
            { "codigo": 11, "nombre": "RECUENTO DE RETICULOCITOS", "metodo": "M", "precio": 15 },
            { "codigo": 12, "nombre": "TIEMPO DE PROTROMBINA (TP)", "metodo": "M/SA", "precio": 20 },
            { "codigo": 13, "nombre": "TIEMPO PARCIAL DE TROMBOPLASTINA ACTIVADA (APTT)", "metodo": "M/SA", "precio": 20 }
          ]
        },
        {
          "name": "QUIMICA SANGUINEA",
          "servicios": [
            { "codigo": 14, "nombre": "ÁCIDO ÚRICO", "metodo": "M/A", "precio": 20 },
            { "codigo": 15, "nombre": "ALBÚMINA", "metodo": "M/A", "precio": 20 },
            { "codigo": 16, "nombre": "AMILASA", "metodo": "M/A", "precio": 20 },
            { "codigo": 17, "nombre": "BILIRRUBINAS TOTALES Y FRACCIONADAS", "metodo": "M/A", "precio": 20 },
            { "codigo": 18, "nombre": "CALCIO", "metodo": "M", "precio": 30 },
            { "codigo": 19, "nombre": "CITOQUÍMICO LÍQUIDO CEFALORRAQUÍDEO Y OTROS LÍQUIDOS", "metodo": "M/A", "precio": 60 },
            { "codigo": 20, "nombre": "CK TOTAL", "metodo": "M", "precio": 30 },
            { "codigo": 21, "nombre": "CK MB", "metodo": "M", "precio": 30 },
            { "codigo": 22, "nombre": "CLEARENCE DE CREATININA", "metodo": "M", "precio": 35 },
            { "codigo": 23, "nombre": "COLESTEROL", "metodo": "M/A", "precio": 20 },
            { "codigo": 24, "nombre": "CREATININA EN ORINA (CREATINURIA)", "metodo": "M/A", "precio": 20 },
            { "codigo": 25, "nombre": "CREATININA SÉRICA", "metodo": "M/A", "precio": 20 },
            { "codigo": 26, "nombre": "ELECTROLITOS (SODIO, POTASIO, CLORO) (NA,K,CL)", "metodo": "M/A", "precio": 90 },
            { "codigo": 27, "nombre": "FOSFATASA ALCALINA", "metodo": "M/A", "precio": 20 },
            { "codigo": 28, "nombre": "FÓSFORO", "metodo": "M/A", "precio": 20 },
            { "codigo": 29, "nombre": "GAMA GLUTAMIL TRANSFERASA (GGT)", "metodo": "M/A", "precio": 30 },
            { "codigo": 30, "nombre": "GASOMETRÍA ARTERIAL O VENOSA", "metodo": "M", "precio": 30 },
            { "codigo": 31, "nombre": "GLICEMIA", "metodo": "A", "precio": 20 },
            { "codigo": 32, "nombre": "HDLc, LDLc, VLDLc", "metodo": "M/A", "precio": 20 },
            { "codigo": 33, "nombre": "HEMOGLOBINA GLICOSILADA A1c", "metodo": "SA", "precio": 90 },
            { "codigo": 34, "nombre": "HIERRO", "metodo": "M", "precio": 20 },
            { "codigo": 35, "nombre": "IONOGRAMA (NA,K,CL,CA,Mg,P)", "metodo": "M", "precio": 80 },
            { "codigo": 36, "nombre": "LACTATO DESHIDROGENASA (LDH)", "metodo": "M", "precio": 30 },
            { "codigo": 37, "nombre": "LIPASA", "metodo": "M", "precio": 20 },
            { "codigo": 38, "nombre": "MAGNESIO", "metodo": "M", "precio": 40 },
            { "codigo": 39, "nombre": "NITRÓGENO URÉICO SÉRICO (NUS)", "metodo": "M/A", "precio": 30 },
            { "codigo": 40, "nombre": "PROTEÍNAS TOTALES", "metodo": "M/A", "precio": 20 },
            { "codigo": 41, "nombre": "PROTEINOGRAMA (PROTEÍNAS TOTALES, ALBÚMINA, GLOBULINA)", "metodo": "M/A", "precio": 20 },
            { "codigo": 42, "nombre": "PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)", "metodo": "M/A", "precio": 40 },
            { "codigo": 43, "nombre": "PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICÉRIDOS, HDLc, LDLc, VLDLc)", "metodo": "M/A", "precio": 80 }
          ]
        },
        {
          "name": "INMUNOSEROLOGIA",
          "servicios": [
            { "codigo": 52, "nombre": "ASTO O ASO", "metodo": "M", "precio": 30 },
            { "codigo": 53, "nombre": "FACTOR REUMATOIDEO (FR)", "metodo": "M", "precio": 30 },
            { "codigo": 54, "nombre": "PCR CUALITATIVO (PROTEÍNA C REACTIVA)", "metodo": "M", "precio": 30 },
            { "codigo": 55, "nombre": "PRUEBA RÁPIDA PARA HEPATITIS B", "metodo": "M", "precio": 60 },
            { "codigo": 56, "nombre": "PRUEBA RÁPIDA PARA HEPATITIS C", "metodo": "M", "precio": 60 },
            { "codigo": 57, "nombre": "PRUEBA RÁPIDA PARA CHAGAS", "metodo": "M", "precio": 60 },
            { "codigo": 58, "nombre": "PRUEBA RÁPIDA PARA VIH", "metodo": "M", "precio": 40 },
            { "codigo": 59, "nombre": "PRUEBA RÁPIDA PARA SÍFILIS", "metodo": "M", "precio": 40 },
            { "codigo": 60, "nombre": "PRUEBA RÁPIDA PARA TROPONINA", "metodo": "M", "precio": 40 },
            { "codigo": 61, "nombre": "REACCIÓN DE WIDAL", "metodo": "M", "precio": 30 },
            { "codigo": 62, "nombre": "PR- VDRL", "metodo": "M", "precio": 30 },
            { "codigo": 63, "nombre": "TEST DE EMBARAZO EN SUERO (GONADOTROFINA CORIÓNICA HUMANA CUALITATIVO)", "metodo": "M", "precio": 25 }
          ]
        },
        {
          "name": "UROANÁLISIS",
          "servicios": [
            { "codigo": 65, "nombre": "EXAMEN GENERAL DE ORINA", "metodo": "M", "precio": 20 },
            { "codigo": 66, "nombre": "MORFOLOGÍA DE ERITROCITOS", "metodo": "M", "precio": 10 },
            { "codigo": 67, "nombre": "TEST DE CRISTALIZACIÓN", "metodo": "M", "precio": 10 }
          ]
        },
        {
          "name": "PARASITOLOGÍA Y ESTUDIOS EN HECES FECALES",
          "servicios": [
            { "codigo": 68, "nombre": "AMEBAS EN FRESCO", "metodo": "MANUAL", "precio": 15 },
            { "codigo": 69, "nombre": "BENEDICT + pH O PRUEBA DE TOLERANCIA A LA LACTOSA EN HECES", "metodo": "MANUAL", "precio": 20 },
            { "codigo": 70, "nombre": "COPROPARASITOLÓGICO SIMPLE", "metodo": "MANUAL", "precio": 15 },
            { "codigo": 71, "nombre": "COPROPARASITOLÓGICO SERIADO", "metodo": "MANUAL", "precio": 25 },
            { "codigo": 72, "nombre": "EXAMEN DIRECTO PARA LEISHMANIA", "metodo": "MANUAL", "precio": 20 },
            { "codigo": 73, "nombre": "GOTA GRUESA PARA MALARIA", "metodo": "MANUAL", "precio": 20 },
            { "codigo": 74, "nombre": "MICROMÉTODO PARA CHAGAS", "metodo": "MANUAL", "precio": 15 },
            { "codigo": 75, "nombre": "MOCO FECAL", "metodo": "MANUAL", "precio": 15 },
            { "codigo": 76, "nombre": "PRUEBA RÁPIDA PARA ROTAVIRUS", "metodo": "MANUAL", "precio": 35 },
            { "codigo": 77, "nombre": "SANGRE OCULTA EN HECES", "metodo": "MANUAL", "precio": 15 },
            { "codigo": 78, "nombre": "TÉCNICA DE GRAHAM", "metodo": "MANUAL", "precio": 15 }
          ]
        },
        {
          "name": "BACTERIOLOGIA",
          "servicios": [
            { "codigo": 79, "nombre": "CULTIVO Y ANTIBIOGRAMA PARA GÉRMENES COMUNES", "metodo": "MANUAL", "precio": 60 },
            { "codigo": 80, "nombre": "CULTIVO MICOLÓGICO Y FUNGIGRAMA", "metodo": "MANUAL", "precio": 120 },
            { "codigo": 81, "nombre": "EXAMEN EN FRESCO", "metodo": "MANUAL", "precio": 15 },
            { "codigo": 82, "nombre": "FROTIS TINCIÓN GRAM", "metodo": "MANUAL", "precio": 15 },
            { "codigo": 83, "nombre": "HEMOCULTIVO SIMPLE (UNA TOMA)", "metodo": "MANUAL", "precio": 200 },
            { "codigo": 84, "nombre": "HEMOCULTIVO SERIADO (3 TOMAS)", "metodo": "MANUAL", "precio": 700 },
            { "codigo": 85, "nombre": "RETROCULTIVO", "metodo": "MANUAL", "precio": 70 }
          ]
        },
        {
          "name": "INMUNOLOGIA / INFECCIOSOS",
          "servicios": [
            { "codigo": 86, "nombre": "CHAGAS IgG (EN SUERO)", "metodo": "ELISA", "precio": 70 },
            { "codigo": 87, "nombre": "CHAGAS (EN SUERO)", "metodo": "HAI Y ELISA", "precio": 110 },
            { "codigo": 88, "nombre": "CHAGAS IgM (EN SUERO)", "metodo": "HAI", "precio": 40 },
            { "codigo": 89, "nombre": "CHLAMYDIA (IgG e IgM) (EN SUERO)", "metodo": "ELISA", "precio": 170 },
            { "codigo": 90, "nombre": "CISTICERCOCIS (IgG) (EN SUERO)", "metodo": "ELISA", "precio": 170 },
            { "codigo": 91, "nombre": "CITOMEGALOVIRUS (IgG e IgM) (EN SUERO)", "metodo": "ELISA", "precio": 170 },
            { "codigo": 92, "nombre": "EPSTEIN BARR (IgG e IgM) (EN SUERO) (MONONUCLEOSIS)", "metodo": "ELISA", "precio": 220 },
            { "codigo": 93, "nombre": "HELICOBACTER PYLORI (IgG e IgM) (EN SUERO)", "metodo": "ELISA", "precio": 120 },
            { "codigo": 94, "nombre": "HELICOBACTER PYLORI Ag (EN HECES)", "metodo": "ELISA", "precio": 120 },
            { "codigo": 95, "nombre": "HEPATITIS A (IgM) (EN SUERO)", "metodo": "ELISA", "precio": 60 },
            { "codigo": 96, "nombre": "HEPATITIS B (HBsAg) (EN SUERO)", "metodo": "ELISA", "precio": 60 },
            { "codigo": 97, "nombre": "HEPATITIS B anti-CORE (EN SUERO)", "metodo": "ELISA", "precio": 60 },
            { "codigo": 98, "nombre": "HEPATITIS C (IgG) (EN SUERO)", "metodo": "ELISA", "precio": 60 },
            { "codigo": 99, "nombre": "HERPES (IgM) (EN SUERO)", "metodo": "ELISA", "precio": 120 },
            { "codigo": 100, "nombre": "HERPES (IgG) (EN SUERO)", "metodo": "ELISA", "precio": 120 },
            { "codigo": 101, "nombre": "HIDATIDOSIS (IgG) (EN SUERO) (EQUINOCOCCUS)", "metodo": "ELISA", "precio": 100 },
            { "codigo": 102, "nombre": "PROCALCITONINA (EN SUERO) (BIOMARCADOR DE SEPSIS)", "metodo": "ELISA", "precio": 120 },
            { "codigo": 103, "nombre": "RUBEOLA (IgM) (EN SUERO)", "metodo": "ELISA", "precio": 90 },
            { "codigo": 104, "nombre": "TOXOPLASMOSIS (IgM e IgG) (EN SUERO)", "metodo": "ELISA", "precio": 120 },
            {
              "codigo": 105,
              "nombre": "TORCH (TOXO (IgM e IgG), CHAGAS (HAI Y ELISA), RUBEOLA (IgM), CITOMEGALOVIRUS (IgG e IgM))",
              "metodo": "ELISA Y HAI",
              "precio": 410
            }
          ]
        },
        {
          "name": "HORMONAS",
          "servicios": [
            { "codigo": 106, "nombre": "ACTH (PLASMA CON EDTA)", "metodo": "ELISA Y CLIA", "precio": 200 },
            { "codigo": 107, "nombre": "CORTISOL (2 TOMAS: 8:00 Y 16:00)", "metodo": "ELISA Y CLIA", "precio": 120 },
            { "codigo": 108, "nombre": "ESTRADIOL (ESTRÓGENOS)", "metodo": "ELISA Y CLIA", "precio": 100 },
            { "codigo": 109, "nombre": "FSH (HORMONA FOLÍCULO ESTIMULANTE)", "metodo": "ELISA Y CLIA", "precio": 100 },
            { "codigo": 110, "nombre": "HGC CUANTIFICACIÓN HORMONA GONADOTROFINA CORIÓNICA FRACCIÓN BETA", "metodo": "ELISA Y CLIA", "precio": 60 },
            { "codigo": 111, "nombre": "HGH - HORMONA DEL CRECIMIENTO (1 TOMA SIN ESTIMULACIÓN)", "metodo": "ELISA Y CLIA", "precio": 100 },
            { "codigo": 112, "nombre": "LH (HORMONA LUTENIZANTE)", "metodo": "ELISA Y CLIA", "precio": 100 },
            { "codigo": 113, "nombre": "PROGESTERONA", "metodo": "ELISA Y CLIA", "precio": 100 },
            { "codigo": 114, "nombre": "PROLACTINA", "metodo": "ELISA Y CLIA", "precio": 100 },
            { "codigo": 115, "nombre": "PARATHORMONA", "metodo": "ELISA Y CLIA", "precio": 100 },
            { "codigo": 116, "nombre": "TSH", "metodo": "ELISA Y CLIA", "precio": 100 },
            { "codigo": 117, "nombre": "TSH NEONATAL (TAMIZAJE)", "metodo": "ELISA Y CLIA", "precio": 90 },
            { "codigo": 118, "nombre": "T3 TOTAL", "metodo": "ELISA Y CLIA", "precio": 60 },
            { "codigo": 119, "nombre": "T4 TOTAL", "metodo": "ELISA Y CLIA", "precio": 60 },
            { "codigo": 120, "nombre": "T4 LIBRE", "metodo": "ELISA Y CLIA", "precio": 60 },
            { "codigo": 121, "nombre": "TESTOSTERONA TOTAL", "metodo": "ELISA Y CLIA", "precio": 100 },
            { "codigo": 122, "nombre": "TESTOSTERONA LIBRE", "metodo": "ELISA Y CLIA", "precio": 100 },
            { "codigo": 123, "nombre": "TIROGLOBULINA", "metodo": "ELISA Y CLIA", "precio": 90 }
          ]
        },
        {
          "name": "MARCADORES DE AUTOINMUNIDAD",
          "servicios": [
            { "codigo": 124, "nombre": "ANA (ANTICUERPOS ANTINUCLEARES)", "metodo": "ELISA", "precio": 90 },
            { "codigo": 125, "nombre": "ANTI-DNA", "metodo": "ELISA", "precio": 90 },
            { "codigo": 126, "nombre": "ANTI-TPO (ANTI-PEROXIDASA)", "metodo": "ELISA Y CLIA", "precio": 90 },
            { "codigo": 127, "nombre": "ANCA-P", "metodo": "ELISA", "precio": 90 },
            { "codigo": 128, "nombre": "ANCA-C", "metodo": "ELISA", "precio": 90 },
            { "codigo": 129, "nombre": "ANTI-TG (ANTI-TIROGLOBULINA)", "metodo": "ELISA Y CLIA", "precio": 60 },
            { "codigo": 130, "nombre": "ANTI-CCP", "metodo": "ELISA", "precio": 90 },
            { "codigo": 131, "nombre": "PERIL ENA", "metodo": "ELISA", "precio": 200 }
          ]
        },
        {
          "name": "MARCADORES TUMORALES",
          "servicios": [
            { "codigo": 132, "nombre": "ALFA FETOPROTEÍNA", "metodo": "ELISA Y CLIA", "precio": 60 },
            { "codigo": 133, "nombre": "ANTI BETA-2 MICROGLOBULINA", "metodo": "ELISA", "precio": 90 },
            { "codigo": 134, "nombre": "CA 125", "metodo": "ELISA Y CLIA", "precio": 60 },
            { "codigo": 135, "nombre": "CA 15-3", "metodo": "ELISA Y CLIA", "precio": 60 },
            { "codigo": 136, "nombre": "CA 19-9", "metodo": "ELISA Y CLIA", "precio": 60 },
            { "codigo": 137, "nombre": "CA 2-50", "metodo": "CLIA", "precio": 70 },
            { "codigo": 138, "nombre": "CA 72-4", "metodo": "CLIA", "precio": 70 },
            { "codigo": 139, "nombre": "CEA", "metodo": "ELISA Y CLIA", "precio": 70 },
            { "codigo": 140, "nombre": "PSA LIBRE", "metodo": "ELISA Y CLIA", "precio": 60 },
            { "codigo": 141, "nombre": "PSA TOTAL", "metodo": "ELISA Y CLIA", "precio": 60 }
          ]
        },
        {
          "name": "INMUNOGLOBULINAS Y FACTORES DE COMPLEMENTO",
          "servicios": [
            { "codigo": 142, "nombre": "C3", "metodo": "NEFELOMETRÍA", "precio": 60 }
          ]
        }
      ],
      establecimientos: []
    }
  },
  mounted () {
    this.getSolicitudes();
    this.loadPacientes();
    this.loadDoctores();
    this.$axios.get('establecimientos').then(res => {
      this.establecimientos = res.data;
    });
  },
  methods: {
    onChangeCi(val) {
      this.searchCi = val;
      // axios post
      this.buscarPacientePorCi();
    },
    getSolicitudes () {
      this.loading = true;
      this.$axios.get('solicitudes', { params: this.filters })
        .then(res => {
          this.rows = res.data;
        })
        .finally(() => {
          this.loading = false;
        });
    },
    loadPacientes () {
      this.$axios.get('pacientes').then(res => {
        this.pacientesOptions = res.data;
      });
    },
    loadDoctores () {
      this.$axios.get('doctores').then(res => {
        this.doctoresOptions = res.data;
      });
    },
    nuevo () {
      this.solicitud = {
        paciente_id: null,
        doctor_id: null,

        codigo_solicitud: '',
        tipo_atencion: 'SI',
        tipo_otro: '',
        fecha_solicitud: moment().format('YYYY-MM-DD'),
        hora_solicitud: moment().format('HH:mm'),
        establecimiento_salud: '',
        zona_establecimiento: '',
        diagnostico_clinico: '',
        estado: 'CREADO',

        paciente_nombre: '',
        paciente_ci: '',
        paciente_telefono: '',
        paciente_direccion: '',
        paciente_fecha_nac: '',
        paciente_genero: '',
        paciente_edad: null,

        doctor_nombre: '',
        doctor_especialidad: '',
        doctor_ci: '',
        doctor_telefono: '',
        doctor_email: '',
        doctor_registro: ''
      };
      this.searchCi = '';
      this.editando = false;
      this.dialog = true;

      this.areas.forEach(area => {
        area.servicios.forEach(servicio => {
          servicio.seleccionado = 0;
        });
      });
    },
    editar (row) {
      this.solicitud = { ...row, paciente_id: row.paciente_id, doctor_id: row.doctor_id };
      this.editando = true;
      this.dialog = true;
    },
    guardar () {
      this.loading = true;
      const req = this.editando
        ? this.$axios.put(`solicitudes/${this.solicitud.id}`, this.solicitud)
        : this.$axios.post('solicitudes', this.solicitud);

      req.then(() => {
        this.$alert && this.$alert.success
          ? this.$alert.success('Guardado correctamente')
          : console.log('Guardado correctamente');
        this.dialog = false;
        this.getSolicitudes();
      })
        .catch(e => {
          const msg = e.response?.data?.message || e.message;
          this.$alert && this.$alert.error
            ? this.$alert.error('Error al guardar: ' + msg)
            : console.error(msg);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    eliminar (id) {
      if (this.$alert && this.$alert.dialog) {
        this.$alert.dialog('¿Eliminar solicitud?').onOk(() => {
          this.$axios.delete(`solicitudes/${id}`).then(() => {
            this.$alert.success('Eliminado');
            this.getSolicitudes();
          });
        });
      } else {
        if (confirm('¿Eliminar solicitud?')) {
          this.$axios.delete(`solicitudes/${id}`).then(() => {
            this.getSolicitudes();
          });
        }
      }
    },
    buscarPacientePorCi () {
      if (!this.searchCi) return;
      this.loading = true;
      this.$axios.get(`pacientes/buscar-ci/${this.searchCi}`)
        .then(res => {
          this.onSelectPaciente(res.data.id);
        })
        .catch(() => {
          // this.$alert && this.$alert.error
          //   ? this.$alert.error('Paciente no encontrado')
          //   : alert('Paciente no encontrado');
        })
        .finally(() => {
          this.loading = false;
        });
    },
    onSelectPaciente (id) {
      const p = this.pacientesOptions.find(x => x.id === id);
      if (!p) return;
      this.solicitud.paciente_id        = p.id;
      this.solicitud.paciente_nombre    = p.nombre_completo;
      this.solicitud.paciente_ci        = p.ci;
      this.solicitud.paciente_telefono  = p.telefono;
      this.solicitud.paciente_direccion = p.direccion;
      this.solicitud.paciente_fecha_nac = p.fecha_nac;
      this.solicitud.paciente_genero    = p.genero;
      this.solicitud.paciente_edad      = p.edad;
    },
    onSelectDoctor (id) {
      const d = this.doctoresOptions.find(x => x.id === id);
      if (!d) return;
      this.solicitud.doctor_id           = d.id;
      this.solicitud.doctor_nombre       = d.nombre;
      this.solicitud.doctor_especialidad = d.especialidad;
      this.solicitud.doctor_ci           = d.ci;
      this.solicitud.doctor_telefono     = d.telefono;
      this.solicitud.doctor_email        = d.email;
      this.solicitud.doctor_registro     = d.registro;
    }
  }
};
</script>

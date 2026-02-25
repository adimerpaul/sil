<template>
  <q-card style="max-width: 980px; margin: 0 auto;" flat bordered>
      <q-card-section class="row items-center q-pa-sm">
        <div class="text-subtitle1">
          Nueva solicitud
<!--          si hay fecah de creacion nesecito la mostrar-->
          <template v-if="solicitud.id">
            <q-chip size="sm" color="grey-4" text-color="black" class="q-ml-sm">
              Creación: {{ solicitud.fecha_creacion }} {{ solicitud.hora_creacion }}
            </q-chip>
          </template>
        </div>
        <q-space />
        <q-btn icon="arrow_back" flat round dense @click="$router.push({ path: '/solicitudes' })" />
        <q-btn color="primary" label="Guardar" @click="$refs.form.submit()" :loading="loading" />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-sm">
        <q-form @submit="guardar" ref="form">
          <!-- Paciente -->
          <div class="row items-center q-mb-xs">
            <q-icon name="person" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">
              Datos del paciente
              <q-btn flat dense icon="child_care" color="primary" class="q-ml-sm"
                     @click="rnnnGet('RN')"
                     label="RN" />
              <q-btn flat dense icon="face" color="primary" class="q-ml-xs"
                     @click="rnnnGet('NN')"
                     label="NN" />
            </div>
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-6 col-sm-3">
              <q-input v-model="solicitud.paciente_ci" label="CI" dense outlined clearable
                       @update:model-value="onChangeCi" debounce="600" >
                <template v-slot:append>
                  <q-spinner
                    color="primary"
                    v-if="loading"
                  />
                </template>
              </q-input>
<!--              <pre>{{solicitud}}</pre>-->
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="solicitud.paciente_nombre"
                label="Nombre"
                dense
                outlined
                clearable
                @update:model-value="val => setUpperSolicitudField('paciente_nombre', val)"
              >
                <template v-slot:append>
                  <q-btn flat dense icon="search" @click="clickDialogPaciente" />
                </template>
              </q-input>
            </div>
            <div class="col-6 col-sm-3">
              <q-input
                v-model="solicitud.paciente_telefono"
                label="Celular"
                dense
                outlined
                clearable
                @update:model-value="val => setUpperSolicitudField('paciente_telefono', val)"
              />
            </div>

<!--            <div class="col-12 col-md-6">-->
<!--              <q-input v-model="solicitud.paciente_direccion" label="Dirección" dense outlined clearable/>-->
<!--            </div>-->
            <div class="col-12 col-md-6">
              <div class="text-caption text-black">FUM (Fecha última menstruación)</div>
              <q-chip size="12px" color="primary" text-color="white">FUM: {{ solicitud.paciente_fum || 'N/A' }}</q-chip>
<!--              <pre>{{solicitud.paciente.fum}}</pre>-->
            </div>

            <div class="col-6 col-sm-4">
              <q-input v-model="solicitud.paciente_fecha_nac" type="date" label="F. nacimiento"
                       dense outlined @update:model-value="onCalculateEdad" />
            </div>

            <div class="col-6 col-sm-4">
              <div class="text-caption text-black">Género</div>
              <q-radio v-model="solicitud.paciente_genero" val="F" label="F" dense />
              <q-radio v-model="solicitud.paciente_genero" val="M" label="M" dense />
              <q-radio v-model="solicitud.paciente_genero" val="OTRO" label="Otro" dense />
            </div>

            <div class="col-12 col-sm-2">
              <q-input v-model.number="solicitud.paciente_edad" type="number" label="Edad" dense outlined />
            </div>
            <div class="col-12 col-sm-2">
              <span class="text-bold">Edad adm: </span> <br>
              {{edadadmY}}
            </div>

            <div class="col-12 col-sm-4">
              <q-toggle
                v-model="solicitud.paciente_discapacidad"
                :true-value="1"
                :false-value="0"
                label="Discapacidad"
                dense
              />
            </div>
            <div class="col-12 col-sm-4" v-if="solicitud.paciente_discapacidad">
              <q-input
                v-model="solicitud.paciente_discapacidad_cual"
                label="Discapacidad (cual)"
                dense
                outlined
                clearable
                @update:model-value="val => setUpperSolicitudField('paciente_discapacidad_cual', val)"
              />
            </div>
            <div class="col-12 col-sm-4" v-if="solicitud.paciente_discapacidad">
              <q-input
                v-model="solicitud.paciente_discapacidad_otro"
                label="Discapacidad (otro)"
                dense
                outlined
                clearable
                @update:model-value="val => setUpperSolicitudField('paciente_discapacidad_otro', val)"
              />
            </div>

            <div class="col-12 col-sm-4">
              <q-toggle
                v-model="solicitud.paciente_embarazo"
                :true-value="1"
                :false-value="0"
                label="Embarazo"
                dense
              />
            </div>
            <div class="col-12 col-sm-4" v-if="solicitud.paciente_embarazo">
              <q-input
                v-model="solicitud.paciente_fum"
                type="date"
                label="FUM"
                dense
                @update:model-value="
                solicitud.paciente_sem_gest = solicitud.paciente_fum
                  ? Math.floor(moment().diff(moment(solicitud.paciente_fum, 'YYYY-MM-DD'), 'weeks', true))
                  : null"
                outlined
              />
            </div>
            <div class="col-12 col-sm-4" v-if="solicitud.paciente_embarazo">
              <q-input
                v-model.number="solicitud.paciente_sem_gest"
                type="number"
                label="Semanas gestación"
                dense
                outlined
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
            <div class="col-12">
              <q-select
                v-model="solicitud.doctor_id"
                :options="doctoresOptions"
                use-input
                option-value="id"
                :option-label="doctor =>
                  doctor.nombre + ' (' + doctor.especialidad + ')' +
                  (doctor.telefono ? ' - ' + doctor.telefono : '') + ' ' +
                  (doctor.establecimiento?.nombre || '')
                "
                @filter="filterDoctores"
                emit-value map-options
                dense outlined clearable
                label="Doctor (opcional)"
                @update:model-value="onSelectDoctor"
              >
<!--                <template agregra boton para agregar nuevo doctor>-->
                <template #after>
                  <q-btn flat dense icon="person_add" color="positive" @click="dialogDoctorNew=true" />
                </template>
              </q-select>
            </div>
          </div>

          <q-separator class="q-my-sm" />

          <!-- Datos solicitud -->
          <div class="row items-center q-mb-xs">
            <q-icon name="assignment" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Datos de la solicitud</div>
          </div>

          <div class="row q-col-gutter-xs items-center">
            <div class="col-6 col-sm-3">
              <q-toggle v-model="solicitud.tipo_atencion" true-value="SI" false-value="NO" dense
                        @update:model-value="onTipoAtencionChange">
                {{ solicitud.tipo_atencion === 'SI' ? 'SUS' : 'EXT' }}
              </q-toggle>
            </div>

            <template v-if="solicitud.tipo_atencion === 'NO'">
              <div class="col-6 col-md-3" >
<!--                <q-input  v-model="solicitud.tipo_otro"-->
<!--                          label="Especificar tipo de atención" dense outlined />-->
                <q-select
                  v-model="solicitud.establecimiento_salud"
                  :options="establecimientosPrivados"
                  option-label="nombre"
                  use-input
                  @filter="(val, update) => {
                  update(() => {
                    const text = (val || '').toLowerCase().trim()

                    if (!text) {
                      this.establecimientosPrivados = this.establecimientosPrivadosAll
                      return
                    }

                    this.establecimientosPrivados = this.establecimientosPrivadosAll
                      .filter(e => {
                        const nombre = String(e.nombre || '').toLowerCase()
                        const tipo = String(e.tipo || '').toLowerCase()
                        const nivel = String(e.nivel || '').toLowerCase()
                        return (
                          nombre.includes(text) ||
                          tipo.includes(text) ||
                          nivel.includes(text)
                        )
                      })
                      .slice(0, 50) // 🔥 limita resultados (performance)
                  })
                }"
                  option-value="nombre"
                  emit-value map-options
                  label="Establecimiento de salud (SUS)"
                  dense outlined clearable
                  @update:model-value="onEstablecimientoChange"
                >
                  <template #after>
                    <q-btn
                      flat
                      dense
                      icon="add_business"
                      color="positive"
                      @click="openDialogEstablecimiento('PRIVADO')"
                    />
                  </template>
                  <template #option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>{{ scope.opt.nombre }}</q-item-label>
                        <q-item-label caption>{{ scope.opt.tipo }} • {{ scope.opt.nivel }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
              </div>
              <div class="col-6 col-md-3" >
                <q-input v-model="solicitud.numero_factura"
                         label="Número de factura" dense outlined />
              </div>
            </template>
            <div class="col-6 col-md-6" v-else>
              <q-select
                v-model="solicitud.establecimiento_salud"
                :options="establecimientosPublicos"
                option-label="nombre"
                use-input
                @filter="(val, update) => {
                  update(() => {
                    const text = (val || '').toLowerCase().trim()

                    if (!text) {
                      this.establecimientosPublicos = this.establecimientosPublicosAll
                      return
                    }

                    this.establecimientosPublicos = this.establecimientosPublicosAll
                      .filter(e => {
                        const nombre = String(e.nombre || '').toLowerCase()
                        const tipo = String(e.tipo || '').toLowerCase()
                        const nivel = String(e.nivel || '').toLowerCase()
                        return (
                          nombre.includes(text) ||
                          tipo.includes(text) ||
                          nivel.includes(text)
                        )
                      })
                      .slice(0, 50) // 🔥 limita resultados (performance)
                  })
                }"
                option-value="nombre"
                emit-value map-options
                label="Establecimiento de salud (SUS)"
                dense outlined clearable
                @update:model-value="onEstablecimientoChange"
              >
                <template #after>
                  <q-btn
                    flat
                    dense
                    icon="add_business"
                    color="positive"
                    @click="openDialogEstablecimiento('PUBLICO')"
                  />
                </template>
                <template #option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <q-item-label>{{ scope.opt.nombre }}</q-item-label>
                      <q-item-label caption>{{ scope.opt.tipo }} • {{ scope.opt.nivel }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
<!--              <pre>{{establecimientosPublicos}}</pre>-->
            </div>

            <div class="col-12 col-md-6 q-mt-xs">
              <q-select
                v-model="solicitud.diagnostico_select"
                :options="diagnosticos"
                option-label="cie10"
                option-value="cie10"
                dense
                outlined
                clearable
                label="Buscar diagnóstico clínico"
                use-input
                emit-value
                map-options
                input-debounce="300"
                @filter="onFilterDiagnosticos"
              >
                <template #option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <q-item-label>{{ scope.opt.cie10 }}</q-item-label>
                      <q-item-label caption>
                        Especialidad: {{ scope.opt.especialidad }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>

            </div>
            <div class="col-12 col-md-6 q-mt-xs">
              <q-input v-model="solicitud.diagnostico_clinico" type="textarea"
                       label="Diagnóstico clínico otros" dense outlined autogrow />
            </div>

            <div class="col-6">
              <q-input v-model="solicitud.fecha_solicitud" type="date" label="Fecha de solicitud medico"
                       dense outlined />
            </div>

            <div class="col-6">
              <q-select v-model="solicitud.sala"
                        use-input
                        @filter="(val, update) => {
                          update(() => {
                            const text = (val || '').toLowerCase().trim()

                            if (!text) {
                              this.salas = this.salasAll
                              return
                            }

                            this.salas = this.salasAll
                              .filter(s => s.toLowerCase().includes(text))
                              .slice(0, 50) // 🔥 limita resultados (performance)
                          })
                        }"
                        :options="salas"
                        label="Unidad solicitante" dense outlined clearable />
            </div>

            <div class="col-4">
              <q-input v-model="solicitud.cama" label="Sala / Cama" dense outlined />
            </div>
            <!--            diagnostico_select-->
            <!--            <div class="col-8">-->
            <!--            </div>-->
          </div>

          <q-separator class="q-my-sm" />

          <!-- Servicios -->
          <div class="row items-center q-mb-xs">
            <q-icon name="biotech" size="18px" class="q-mr-xs" />
            <div class="text-subtitle2">Servicios solicitados</div>
            <q-space />
            <q-badge color="primary" outline>{{ totalServiciosSeleccionados }} seleccionados</q-badge>
          </div>

          <q-card flat bordered class="q-mb-xs">
<!--            <q-card-section class="row q-col-gutter-xs">-->
<!--              <div class="col-12 col-sm-6">-->
<!--                <q-input v-model="serviciosFilter" dense outlined label="Buscar servicio (nombre / código / subárea)" clearable>-->
<!--                  <template #append><q-icon name="search" /></template>-->
<!--                </q-input>-->
<!--              </div>-->
<!--              <div class="col-12 col-sm-6">-->
<!--                <q-select v-model="serviciosAreaId" :options="areas" option-label="name" option-value="id"-->
<!--                          dense outlined clearable label="Filtrar por área" emit-value map-options>-->
<!--                  <template #prepend><q-icon name="science" /></template>-->
<!--                </q-select>-->
<!--                &lt;!&ndash;                <pre>{{serviciosAreaId}}</pre>&ndash;&gt;-->
<!--              </div>-->
<!--            </q-card-section>-->

            <q-card-section class=" text-grey-7">
              <div v-if="solicitud.tipo_atencion === 'SI' && currentEstablecimiento">
                Mostrando solo servicios del establecimiento: <b>{{ currentEstablecimiento.nombre }}</b>
              </div>
              <div v-else-if="solicitud.tipo_atencion === 'SI'">
                Seleccione un establecimiento para filtrar los servicios.
              </div>
              <div v-else>
                Mostrando todos los servicios disponibles (atención particular / especificar).
              </div>
              <div v-if="selectedServicios.length === 0" class="q-mt-sm">
                No ha seleccionado ningún servicio aún.
              </div>
              <div v-else>
                Servicios seleccionados:
                <ul>
                  <li v-for="(s, index) in selectedServicios" :key="index">
                    {{ s.area }} - {{ s.servicio }} (Bs. {{ s.precio }})
                  </li>
                </ul>
<!--                mostrar cantidada de sericio selecionados y total-->
                <div class="text-bold">
                  Total de servicios seleccionados: <b>{{ selectedServicios.length }}</b>
                  Monto total: <b>Bs. {{ selectedServicios.reduce((sum, s) => sum + parseFloat(s.precio), 0) }}</b>
                </div>
              </div>
            </q-card-section>
          </q-card>
<!--          <div class="row"  mostrar solo al crar no al editar-->
          <div class="row q-ma-md" v-if="!solicitud.id" style="border: 1px solid #3666ce; padding: 10px; border-radius: 4px;">
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.bilirrubinas_totales" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([17])"
                          class="text-subtitle2"
              >
                Bilirrubinas totales y fracciones
              </q-checkbox>
            </div>
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.inmunoglobulinas" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([144, 145, 146])"
                          class="text-subtitle2"
              >
                Inmunoglobulinas IgG, IgM, IgA
              </q-checkbox>
            </div>
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.coproparasitologico_simple" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([70])"
                          class="text-subtitle2"
              >
                Coproparasitológico simple
              </q-checkbox>
            </div>
<!--            moco fecal-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.moco_fecal" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([75])"
                          class="text-subtitle2"
              >
                Moco fecal
              </q-checkbox>
            </div>
<!--            Coproparasitológico seriado-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.coproparasitologico_seriado" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([71])"
                          class="text-subtitle2"
              >
                Coproparasitológico seriado
              </q-checkbox>
            </div>
<!--            Nitrógeno ureico sérico y urea-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.nitrogeno_ureico" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([39, 51])"
                          class="text-subtitle2"
              >
                Nitrógeno ureico sérico y urea
              </q-checkbox>
            </div>
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.creatinina_orina" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([24])"
                          class="text-subtitle2"
              >
                Creatinina en orina
              </q-checkbox>
            </div>
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.proteinac" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([54])"
                          class="text-subtitle2"
              >
                Proteína C Reactiva (PCR)
              </q-checkbox>
            </div>
<!--            ☐ Creatinina sérica-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.creatinina_serica" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([28])"
                          class="text-subtitle2"
              >
                Creatinina sérica
              </q-checkbox>
            </div>
<!--            ☐ Proteinuria de 24 horas-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.proteinuria_24h" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([45])"
                          class="text-subtitle2"
              >
                Proteinuria de 24 horas
              </q-checkbox>
            </div>
<!--            ☐ Cultivo p/ gérmenes comunes y antibiograma-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.cultivo_germenes" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([79])"
                          class="text-subtitle2"
              >
                Cultivo p/ gérmenes comunes y antibiograma
              </q-checkbox>
            </div>
<!--            ☐ Prueba rápida para sífilis-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.prueba_sifilis" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([59])"
                          class="text-subtitle2"
              >
                Prueba rápida para sífilis
              </q-checkbox>
            </div>
<!--            ☐ Examen general de orina-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.examen_orina" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([65])"
                          class="text-subtitle2"
              >
                Examen general de orina
              </q-checkbox>
            </div>
<!--            ☐ Tiempo de coagulación y tiempo de sangría-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.tiempo_coagulacion" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([13])"
                          class="text-subtitle2"
              >
                Tiempo de coagulación y tiempo de sangría
              </q-checkbox>
            </div>
<!--            ☐ Electrolitos (sodio, potasio, cloro)-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.electrolitos" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([26])"
                          class="text-subtitle2"
              >
                Electrolitos (sodio, potasio, cloro)
              </q-checkbox>
            </div>
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.tiempo_protrombina" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([12])"
                          class="text-subtitle2"
              >
                Tiempo de protrombina / TPP
              </q-checkbox>
            </div>
<!--            ☐ Factor reumatoideo-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.factor_reumatoideo" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([53])"
                          class="text-subtitle2"
              >
                Factor reumatoideo
              </q-checkbox>
            </div>
<!--            ☐ Transaminasas TGO – TGP-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.transaminasas" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([49,48])"
                          class="text-subtitle2"
              >
                Transaminasas TGO – TGP
              </q-checkbox>
            </div>
<!--            ☐ Fosfatasa alcalina y ácida-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.fosfatasa" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([27])"
                          class="text-subtitle2"
              >
                Fosfatasa alcalina y ácida
              </q-checkbox>
            </div>
<!--            ☐ Test de embarazo en sangre (HCG)-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.test_embarazo" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([63])"
                          class="text-subtitle2"
              >
                Test de embarazo en sangre (HCG)
              </q-checkbox>
            </div>
<!--            ☐ Frotis tinción Gram-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.frotis_gram" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([82])"
                          class="text-subtitle2"
              >
                Frotis tinción Gram
              </q-checkbox>
            </div>
<!--            ☐ Reactantes de fase aguda (VES, Fibrinógeno, PCR)-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.reactantes_fase_aguda" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([2, 3, 54])"
                          class="text-subtitle2"
              >
                Reactantes de fase aguda (VES, Fibrinógeno, PCR)
              </q-checkbox>
            </div>
<!--            ☐ Grupo sanguíneo y factor Rh-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.grupo_sanguineo" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([5])"
                          class="text-subtitle2"
              >
                Grupo sanguíneo y factor Rh
              </q-checkbox>
            </div>
<!--            Reacción Widal-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.reaccion_widal" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([61])"
                          class="text-subtitle2"
              >
                Reacción Widal
              </q-checkbox>
            </div>
<!--            Glicemia-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.glicemia" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([31])"
                          class="text-subtitle2"
              >
                Glicemia
              </q-checkbox>
            </div>
<!--            ☐ RPR para sífilis – VDRL-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.rpr_sifilis" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([59])"
                          class="text-subtitle2"
              >
                RPR para sífilis – VDRL
              </q-checkbox>
            </div>
<!--            ☐ Gasometría arterial o venosa-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.gasometria" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([30])"
                          class="text-subtitle2"
              >
                Gasometría arterial o venosa
              </q-checkbox>
            </div>
<!--&lt;!&ndash;           Hemoglobina y hematocrito-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.hemoglobina" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([7])"
                          class="text-subtitle2"
              >
                Hemoglobina y hematocrito
              </q-checkbox>
            </div>
<!--            Hemograma completo-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.hemograma_completo" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([6])"
                          class="text-subtitle2"
              >
                Hemograma completo
              </q-checkbox>
            </div>
<!--            VIH-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.vih" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([58])"
                          class="text-subtitle2"
              >
                VIH
              </q-checkbox>
            </div>
<!--            Ihonograma-->
            <div class="col-12 col-md-6">
              <q-checkbox v-model="extras.ihonograma" :true-value="1" :false-value="null" dense
                @update:model-value="selecinarCodigo([35])"
                          class="text-subtitle2"
              >
                Ionograma
              </q-checkbox>
            </div>

          </div>
          <q-card-section class="row q-col-gutter-xs">
            <div class="col-12 col-sm-6">
              <q-input v-model="serviciosFilter" dense outlined label="Buscar servicio (nombre / código / subárea)" clearable>
                <template #append><q-icon name="search" /></template>
              </q-input>
            </div>
            <div class="col-12 col-sm-6">
              <q-select v-model="serviciosAreaId" :options="areas" option-label="name" option-value="id"
                        dense outlined clearable label="Filtrar por área" emit-value map-options>
                <template #prepend><q-icon name="science" /></template>
              </q-select>
              <!--                <pre>{{serviciosAreaId}}</pre>-->
            </div>
          </q-card-section>

          <div class="row q-col-gutter-xs">
            <div class="col-12">
              <q-expansion-item
                v-for="area in areas"
                :key="area.id || area.name"
                :label="area.name"
                icon="science"
                expand-separator
                dense
                default-opened
                v-show="filteredServicios(area).length > 0"
              >
                <q-card flat>
                  <q-card-section class="q-pa-xs">
                    <div class="row q-col-gutter-xs">
                      <div v-for="servicio in filteredServicios(area)" :key="servicio.id || servicio.codigo"
                           class="col-12 col-sm-6">
<!--                        <pre>{{servicio}}</pre>-->
                        <q-checkbox v-model="servicio.seleccionado" :true-value="1" :false-value="0" dense>
                          <div>
                            {{ textCapitalize(servicio.nombre) }}
                            <span class="text-primary">(Bs. {{ servicio.precio }})</span>
                          </div>
                          <div>
                            <small class="text-grey">
                              {{ servicio.codigo ? 'Código: ' + servicio.codigo + ' • ' : '' }}
                              {{ servicio.subarea ? 'Subárea: ' + textCapitalize(servicio.subarea) : '' }}
                            </small>
                          </div>
                        </q-checkbox>
                      </div>
                    </div>
                  </q-card-section>
                </q-card>
              </q-expansion-item>

              <div v-if="areas.length === 0" class="text-center text-grey q-mt-md">
                No hay servicios configurados.
              </div>
            </div>
          </div>

          <div class="text-right q-mt-sm">
            <q-btn flat label="Cancelar" :loading="loading" @click="$router.push({ path: '/solicitudes' })" />
            <q-btn color="primary" label="Guardar" type="submit" class="q-ml-xs" :loading="loading" />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  <q-dialog v-model="consentimientoDialog" persistent>
    <q-card style="min-width: 720px; max-width: 920px;">
      <q-card-section class="row items-center">
        <div class="text-h6">Consentimiento de la solicitud</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="row q-col-gutter-sm q-mb-sm">
          <div class="col-12 col-sm-6">
            <b>Paciente:</b> {{ consentimiento.nombre_completo || '-' }}
<!--            icon btn copiar -->
            <q-btn flat dense icon="content_copy" color="primary" @click="
             consentimiento.declarante_nombre = consentimiento.nombre_completo;
            " />
          </div>
          <div class="col-12 col-sm-3"><b>CI:</b> {{ consentimiento.ci || '-' }}</div>
          <div class="col-12 col-sm-3"><b>Solicitud:</b> #{{ solicitudCreadaId || '-' }}</div>
        </div>

        <q-form @submit.prevent="guardarConsentimientoNuevaSolicitud">
          <div class="row q-col-gutter-sm">
            <div class="col-12 col-sm-3">
              <q-input v-model="consentimiento.fecha_recepcion" type="date" dense outlined label="Fecha recepción" />
            </div>
            <div class="col-12 col-sm-3">
              <q-input v-model="consentimiento.hora_recepcion" type="time" dense outlined label="Hora recepción" />
            </div>
            <div class="col-12 col-sm-3">
              <q-input v-model="consentimiento.fecha_solicitud" type="date" dense outlined label="Fecha solicitud médico" />
            </div>
            <div class="col-12 col-sm-3">
              <q-input v-model="consentimiento.fecha_consentimiento" type="date" dense outlined label="Fecha consentimiento" />
            </div>

            <div class="col-12 col-sm-4">
              <q-toggle
                v-model="consentimiento.medicamento"
                :true-value="1"
                :false-value="0"
                label="Medicamento"
              />
            </div>
            <div class="col-12 col-sm-8" v-if="consentimiento.medicamento">
              <q-input v-model="consentimiento.tratamiento" dense outlined label="Tratamiento" />
            </div>

            <div class="col-12 col-sm-4">
              <q-select
                v-model="consentimiento.condicion"
                :options="['BASAL', 'AYUNO PROL', 'POST PRANDIAL', 'ETAPA_GESTACION']"
                dense outlined
                label="Condición"
                clearable
              />
            </div>
            <div class="col-12 col-sm-4" v-if="consentimiento.condicion === 'ETAPA_GESTACION'">
              <q-input v-model="consentimiento.etapa_gestacion" dense outlined label="Etapa gestación" />
            </div>
            <div class="col-12 col-sm-4">
              <q-select
                v-model="consentimiento.tipo"
                :options="['ACEPTA', 'RECHAZA']"
                dense outlined
                label="Tipo consentimiento"
                clearable
              />
            </div>

            <div class="col-12 col-sm-6">
              <q-input v-model="consentimiento.declarante_nombre" dense outlined label="Yo declarante" />
            </div>
            <div class="col-12 col-sm-6">
              <q-select
                v-model="consentimiento.declarante_condicion"
                :options="['Padre', 'Madre', 'Abuelo/a', 'Hijo/a', 'Propio', 'Otros']"
                dense outlined
                label="En condición de"
                clearable
              />
            </div>
            <div class="col-12" v-if="consentimiento.declarante_condicion === 'Otros'">
              <q-input v-model="consentimiento.declarante_condicion_otro" dense outlined label="Otra condición" />
            </div>
          </div>

          <div class="text-right q-mt-md">
            <q-btn flat label="Omitir" @click="omitirConsentimiento" :disable="consentimientoLoading" />
            <q-btn color="primary" label="Guardar consentimiento" class="q-ml-sm" type="submit" :loading="consentimientoLoading" />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-dialog>
  <q-dialog v-model="dialogDoctorNew">
    <q-card style="min-width: 400px; max-width: 600px;">
      <q-card-section class="row items-center">
        <div class="text-h6">
          Nuevo doctor
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-form @submit.prevent="guardarDoctor">
          <div class="row q-col-gutter-sm">
            <div class="col-12">
              <q-input
                v-model="doctor.nombre"
                label="Nombre"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="doctor.especialidad"
                label="Especialidad"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="doctor.ci"
                label="CI"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="doctor.telefono"
                label="Teléfono"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="doctor.email"
                label="Email"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="doctor.registro"
                label="Registro profesional"
                dense outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-select
                v-model="doctor.establecimiento_id"
                :options="establecimientos"
                option-label="nombre"
                option-value="id"
                emit-value
                map-options
                label="Establecimiento de salud"
                dense outlined
              />
              <!--                <pre>{{establecimientos}}</pre>-->
            </div>
<!--            <div class="col-12 col-sm-6">-->
<!--              <q-select-->
<!--                v-model="doctor.estado"-->
<!--                :options="['ACTIVO', 'INACTIVO']"-->
<!--                label="Estado"-->
<!--                dense outlined-->
<!--                clearable-->
<!--              />-->
<!--            </div>-->
          </div>

          <div class="text-right q-mt-md">
            <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
            <q-btn
              color="primary"
              label="Guardar"
              type="submit"
              class="q-ml-sm"
              :loading="loading"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-dialog>
  <q-dialog v-model="dialogEstablecimientoNew">
    <q-card style="min-width: 420px; max-width: 620px;">
      <q-card-section class="row items-center">
        <div class="text-h6">Nuevo establecimiento</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-separator />
      <q-card-section>
        <q-form @submit.prevent="guardarEstablecimientoRapido">
          <div class="row q-col-gutter-sm">
            <div class="col-12">
              <q-input v-model="establecimientoNew.nombre" label="Nombre" dense outlined />
            </div>
            <div class="col-12 col-sm-6">
              <q-select
                v-model="establecimientoNew.tipo"
                :options="['PUBLICO', 'PRIVADO']"
                label="Tipo"
                dense
                outlined
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-select
                v-model="establecimientoNew.nivel"
                :options="['NIVEL I', 'NIVEL II', 'NIVEL III']"
                label="Nivel"
                dense
                outlined
              />
            </div>
            <div class="col-12">
              <q-input v-model="establecimientoNew.direccion" label="Dirección" dense outlined />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="establecimientoNew.telefono_contacto" label="Teléfono" dense outlined />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="establecimientoNew.inicial" label="Inicial" dense outlined />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="establecimientoNew.responsable_laboratorio" label="Responsable" dense outlined />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="establecimientoNew.telefono_responsable" label="Tel. responsable" dense outlined />
            </div>
          </div>

          <div class="text-right q-mt-md">
            <q-btn flat label="Cancelar" v-close-popup :disable="savingEstablecimiento" />
            <q-btn color="primary" label="Guardar" type="submit" class="q-ml-sm" :loading="savingEstablecimiento" />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-dialog>
  <q-dialog v-model="dialogPaciente" persistent max-width="600px">
    <q-card style="min-width: 400px; max-width: 90vh;">
      <q-card-section class="row items-center q-pa-md">
        <div class="text-subtitle2">
          Buscar Paciente
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup @click="dialogPaciente = false" />
      </q-card-section>
      <q-card-section class="q-pt-none">
        <!-- Aquí puedes agregar el contenido para buscar y seleccionar un paciente -->
        <div class="">
<!--          pacienteNameSearch-->
          <q-input
            v-model="pacienteNameSearch"
            label="Buscar por nombre"
            dense
            outlined
            debounce="300"
            clearable
            @update:model-value="buscarpacientes"
          >
            <template #append>
              <q-icon name="search" />
            </template>
          </q-input>
          <div class="q-mt-sm">
<!--            <pre>{{pacientes}}</pre>-->
<!--            [-->
<!--            {-->
<!--            "id": 2,-->
<!--            "fecha_recepcion": "2025-12-15",-->
<!--            "hora_recepcion": "05:43:59",-->
<!--            "nombre_completo": "Adimer Paul Chambi Ajata",-->
<!--            "fecha_nac": "1989-04-02",-->
<!--            "genero": "M",-->
<!--            "edad": 36,-->
<!--            "ci": "7336199",-->
<!--            "telefono": "69603027",-->
<!--            "direccion": "Av. Siempre Viva 742",-->
<!--            "discapacidad": 0,-->
<!--            "discapacidad_cual": null,-->
<!--            "discapacidad_otro": null,-->
<!--            "embarazo": 0,-->
<!--            "fum": null,-->
<!--            "sem_gest": null-->
<!--            }-->
<!--            ]-->
            <q-list bordered separator>
              <q-item v-for="paciente in pacientes" :key="paciente.id" clickable @click="onSelectPaciente(paciente); dialogPaciente = false">
                <q-item-section>
                  <q-item-label>{{ paciente.nombre_completo }}</q-item-label>
                  <q-item-label caption>CI: {{ paciente.ci }} • Tel: {{ paciente.telefono || 'N/A' }}
                    • F. Nac: {{ paciente.fecha_nac || 'N/A' }}
                  </q-item-label>
                </q-item-section>
              </q-item>
              <q-item v-if="pacientes.length === 0">
                <q-item-section>
                  <q-item-label>No se encontraron pacientes.</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
import moment from 'moment'
import salasJson from 'src/data/salas.json'

export default {
  name: 'SolicitudForm',
  props: {
    solicitudProp: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      extras: {},
      moment: moment,
      dialogDoctorNew: false,
      dialogEstablecimientoNew: false,
      savingEstablecimiento: false,
      doctor: {
        estado: 'ACTIVO'
      },
      establecimientoNew: {
        nombre: '',
        tipo: 'PUBLICO',
        nivel: 'NIVEL I',
        direccion: '',
        telefono_contacto: '',
        inicial: '',
        responsable_laboratorio: '',
        telefono_responsable: '',
        estado: 'ACTIVO'
      },
      loading: false,
      solicitud: {},
      salas : [...Object.values(salasJson)],
      salasAll: [...Object.values(salasJson)],
      doctoresOptions: [],
      doctoresOptionsAll: [],
      areas: [],
      establecimientos: [],
      establecimientosPublicos: [],
      establecimientosPublicosAll: [],
      establecimientosPrivados: [],
      establecimientosPrivadosAll: [],
      establecimientosAll: [],
      searchCi: '',
      serviciosFilter: '',
      serviciosAreaId: null,
      diagnosticos: [],
      diagnosticosAll: [],
      dialogPaciente: false,
      pacienteNameSearch: '',
      pacientes: [],
      consentimientoDialog: false,
      consentimientoLoading: false,
      solicitudCreadaId: null,
      consentimiento: {
        id: null,
        solicitude_id: null,
        paciente_id: null,
        nombre_completo: '',
        ci: '',
        fecha_recepcion: '',
        hora_recepcion: '',
        fecha_solicitud: '',
        fecha_consentimiento: '',
        medicamento: 0,
        tratamiento: '',
        condicion: '',
        etapa_gestacion: '',
        tipo: '',
        declarante_nombre: '',
        declarante_condicion: '',
        declarante_condicion_otro: ''
      }
    }
  },
  computed: {
    edadadmY () {
      if (!this.solicitud.paciente_fecha_nac) return ''

      const nacimiento = moment(this.solicitud.paciente_fecha_nac, 'YYYY-MM-DD')
      if (!nacimiento.isValid()) return ''

      const hoy = moment()

      const years = hoy.diff(nacimiento, 'years')
      nacimiento.add(years, 'years')

      const months = hoy.diff(nacimiento, 'months')
      nacimiento.add(months, 'months')

      const days = hoy.diff(nacimiento, 'days')

      return `${days}d ${months}m ${years}A`
    },
    selectedServicios() {
      const selected = []
      this.areas.forEach(area => {
        (area.servicios || []).forEach(servicio => {
          if (servicio.seleccionado) {
            selected.push({
              area: area.name,
              servicio: servicio.nombre,
              precio: servicio.precio
            })
          }
        })
      })
      return selected
    },
    currentEstablecimiento () {
      if (!this.solicitud.establecimiento_salud) return null
      return this.establecimientos.find(e => e.nombre === this.solicitud.establecimiento_salud) || null
    },
    totalServiciosSeleccionados () {
      let total = 0
      this.areas.forEach(a => (a.servicios || []).forEach(s => { if (s.seleccionado) total++ }))
      return total
    }
  },
  mounted () {

    // this.initSolicitud()
    this.loadDoctores()
    this.diagnosticosGet()
    this.$axios.get('establecimientos').then(res => {
      this.establecimientos = res.data;
      this.establecimientosAll = res.data;
      // establecimientosPublicos
      this.establecimientosPublicos = res.data.filter(e => e.tipo === 'PUBLICO')
      this.establecimientosPublicosAll = res.data.filter(e => e.tipo === 'PUBLICO')
      this.establecimientosPrivados = res.data.filter(e => e.tipo === 'PRIVADO')
      this.establecimientosPrivadosAll = res.data.filter(e => e.tipo === 'PRIVADO')
    })
    this.$axios.get('areasCreateSolicitud').then(res => {
      this.areas = res.data
      console.log('SolicitudForm mounted with solicitudProp:', this.solicitudProp)
      if (this.solicitudProp) {
        this.solicitud = { ...this.solicitudProp }
        // selecioanr las area que tiene el servicios
        this.areas.forEach(area => {
          (area.servicios || []).forEach(s => {
            const found = this.solicitud.servicios.find(ss => ss.id === s.id)
            if (found) {
              s.seleccionado = 1
            }else {
              s.seleccionado = 0
            }
          })
        })
      } else {
        this.initSolicitud()
        this.resetServiciosSelection()
      }
    })
  },
  methods: {
    toUpperText (value) {
      if (value === null || value === undefined) return value
      return String(value).toLocaleUpperCase('es')
    },
    setUpperSolicitudField (field, value) {
      this.solicitud[field] = this.toUpperText(value)
    },
    normalizePacienteUpperFields () {
      this.solicitud.paciente_nombre = this.toUpperText(this.solicitud.paciente_nombre || '')
      this.solicitud.paciente_direccion = this.toUpperText(this.solicitud.paciente_direccion || '')
      this.solicitud.paciente_telefono = this.toUpperText(this.solicitud.paciente_telefono || '')
      this.solicitud.paciente_genero = this.toUpperText(this.solicitud.paciente_genero || '')
      this.solicitud.paciente_discapacidad_cual = this.toUpperText(this.solicitud.paciente_discapacidad_cual || '')
      this.solicitud.paciente_discapacidad_otro = this.toUpperText(this.solicitud.paciente_discapacidad_otro || '')
    },
    selecinarCodigo(codigos){
      // this.areas.forEach(area => {
      //   (area.servicios || []).forEach(s => {
      //     if(s.codigo === codigo){
      //       // s.seleccionado = 1 si esta mascao desmaracar
      //       s.seleccionado = s.seleccionado === 1 ? 0 : 1
      //     }
      //   })
      // })
      for (const codigo of codigos) {
        this.areas.forEach(area => {
          (area.servicios || []).forEach(s => {
            if (s.codigo === codigo) {
              s.seleccionado = s.seleccionado === 1 ? 0 : 1
            }
          })
        })
      }
    },
    consentimientoBaseDesdeSolicitud (s) {
      return {
        id: null,
        solicitude_id: s?.id || null,
        paciente_id: s?.paciente_id || null,
        nombre_completo: s?.paciente_nombre || '',
        ci: s?.paciente_ci || '',
        fecha_recepcion: moment().format('YYYY-MM-DD'),
        hora_recepcion: moment().format('HH:mm'),
        fecha_solicitud: s?.fecha_solicitud || '',
        fecha_consentimiento: moment().format('YYYY-MM-DD'),
        medicamento: 0,
        tratamiento: '',
        condicion: '',
        etapa_gestacion: '',
        tipo: '',
        declarante_nombre: '',
        declarante_condicion: '',
        declarante_condicion_otro: ''
      }
    },
    abrirConsentimientoNuevaSolicitud (solicitudCreada) {
      this.solicitudCreadaId = solicitudCreada?.id || null
      this.consentimiento = this.consentimientoBaseDesdeSolicitud(solicitudCreada)
      this.consentimientoLoading = true
      this.consentimientoDialog = true
      this.$axios
        .get(`solicitudes/${this.solicitudCreadaId}/consentimiento`)
        .then(res => {
          this.consentimiento = { ...this.consentimiento, ...res.data }
          // si no tiene el nombre
          if (!this.consentimiento.nombre_completo) {
            this.consentimiento.nombre_completo = this.solicitud.paciente_nombre || ''
          }
        })
        .finally(() => { this.consentimientoLoading = false })
    },
    omitirConsentimiento () {
      this.consentimientoDialog = false
      this.$router.push({ path: '/solicitudes' })
    },
    guardarConsentimientoNuevaSolicitud () {
      if (!this.solicitudCreadaId) return
      this.consentimientoLoading = true
      this.$axios
        .post(`solicitudes/${this.solicitudCreadaId}/consentimiento`, this.consentimiento)
        .then(() => {
          this.$alert?.success ? this.$alert.success('Solicitud y consentimiento guardados correctamente') : null
          this.consentimientoDialog = false
          // imprimrir
          // http://localhost:8000/api/solicitudes/472/consentimiento/print
          const url = `${this.$axios.defaults.baseURL}/solicitudes/${this.solicitudCreadaId}/consentimiento/print`
          window.open(url, '_blank')
          this.$router.push({ path: '/solicitudes' })
        })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error ? this.$alert.error('Error al guardar consentimiento: ' + msg) : null
        })
        .finally(() => { this.consentimientoLoading = false })
    },
    rnnnGet(tipo){
      this.loading = true
      this.$axios.get(`pacientesnn-rn/`, {
        params: {
          tipo: tipo
        }
      }).then(res => {
        this.solicitud.paciente_nombre = res.data
      }).finally(() => {
        this.loading = false
      })
    },
    buscarpacientes(){
      this.$axios.get('pacientes', {
        params: {
          page: 1,
          per_page: 10,
          search: this.pacienteNameSearch
        }
      }).then(res => {
        this.pacientes = res.data.data
      })
    },
    clickDialogPaciente(){
      this.dialogPaciente = true
    },
    openDialogEstablecimiento (tipo) {
      this.establecimientoNew = {
        nombre: this.solicitud.establecimiento_salud || '',
        tipo: tipo || (this.solicitud.tipo_atencion === 'SI' ? 'PUBLICO' : 'PRIVADO'),
        nivel: 'NIVEL I',
        direccion: '',
        telefono_contacto: '',
        inicial: '',
        responsable_laboratorio: '',
        telefono_responsable: '',
        estado: 'ACTIVO'
      }
      this.dialogEstablecimientoNew = true
    },
    guardarEstablecimientoRapido () {
      if (!this.establecimientoNew.nombre || !this.establecimientoNew.nombre.trim()) {
        this.$alert?.error ? this.$alert.error('Ingrese el nombre del establecimiento') : null
        return
      }

      this.savingEstablecimiento = true
      this.$axios.post('establecimientos', this.establecimientoNew)
        .then(res => {
          const est = res.data
          this.establecimientos.unshift(est)
          this.establecimientosAll.unshift(est)
          if (est.tipo === 'PUBLICO') {
            this.establecimientosPublicos.unshift(est)
            this.establecimientosPublicosAll.unshift(est)
          } else {
            this.establecimientosPrivados.unshift(est)
            this.establecimientosPrivadosAll.unshift(est)
          }
          this.solicitud.establecimiento_salud = est.nombre
          this.onEstablecimientoChange()
          this.dialogEstablecimientoNew = false
          this.$alert?.success ? this.$alert.success('Establecimiento agregado') : null
        })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error ? this.$alert.error('Error al guardar establecimiento: ' + msg) : null
        })
        .finally(() => {
          this.savingEstablecimiento = false
        })
    },
    guardarDoctor() {
      this.loading = true
      this.$axios.post('doctores', this.doctor)
        .then(res => {
          this.$alert?.success ? this.$alert.success('Doctor guardado correctamente') : console.log('Doctor guardado correctamente')
          this.dialogDoctorNew = false
          this.loadDoctores()
          this.solicitud.doctor_id = res.data.id
          this.onSelectDoctor(res.data.id)
          // reset doctor form
          this.doctor = {
            estado: 'ACTIVO'
          }
        })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert?.error ? this.$alert.error('Error al guardar doctor: ' + msg) : console.error(msg)
        })
        .finally(() => { this.loading = false })
    },
    filterDoctores(val, update) {
      update(() => {
        const text = (val || '').toLowerCase().trim()

        if (!text) {
          this.doctoresOptions = this.doctoresOptionsAll
          return
        }

        this.doctoresOptions = this.doctoresOptionsAll
          .filter(d => {
            const nombre = String(d.nombre || '').toLowerCase()
            const esp = String(d.especialidad || '').toLowerCase()
            const ci = String(d.ci || '').toLowerCase()
            const tel = String(d.telefono || '').toLowerCase()
            return (
              nombre.includes(text) ||
              esp.includes(text) ||
              ci.includes(text) ||
              tel.includes(text)
            )
          })
          .slice(0, 50) // 🔥 limita resultados (performance)
      })
    },
    onFilterDiagnosticos (val, update) {
      update(() => {
        const text = (val || '').toLowerCase().trim()

        if (!text) {
          this.diagnosticos = this.diagnosticosAll
          return
        }

        this.diagnosticos = this.diagnosticosAll
          .filter(d => {
            const cie10 = String(d.cie10 || '').toLowerCase()
            const esp = String(d.especialidad || '').toLowerCase()
            const serv = String(d.servicio || '').toLowerCase()
            return (
              cie10.includes(text) ||
              esp.includes(text) ||
              serv.includes(text)
            )
          })
          .slice(0, 50) // 🔥 limita resultados (performance)
      })
    },
    diagnosticosGet () {
      this.$axios.get('diagnosticos').then(res => {
        this.diagnosticos = res.data
        this.diagnosticosAll = res.data
      })
    },
    initSolicitud () {
      this.solicitud = {
        paciente_id: null,
        doctor_id: null,
        codigo_solicitud: '',
        tipo_atencion: 'SI',
        tipo_otro: '',
        fecha_solicitud: moment().format('YYYY-MM-DD'),
        hora_solicitud: moment().format('HH:mm'),
        establecimiento_salud: 'Hospital General',
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
        paciente_discapacidad: 0,
        paciente_discapacidad_cual: '',
        paciente_discapacidad_otro: '',
        paciente_embarazo: 0,
        paciente_fum: '',
        paciente_sem_gest: null,

        doctor_nombre: '',
        doctor_especialidad: '',
        doctor_ci: '',
        doctor_telefono: '',
        doctor_email: '',
        doctor_registro: ''
      }
      this.searchCi = ''
      this.serviciosFilter = ''
      this.serviciosAreaId = null
    },

    onCalculateEdad () {
      if (!this.solicitud.paciente_fecha_nac) return
      const birthDate = moment(this.solicitud.paciente_fecha_nac, 'YYYY-MM-DD')
      if (!birthDate.isValid()) return
      this.solicitud.paciente_edad = moment().diff(birthDate, 'years')
    },

    textCapitalize (str) {
      if (!str) return ''
      return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
    },

    resetServiciosSelection () {
      this.areas.forEach(area => (area.servicios || []).forEach(s => { s.seleccionado = 0 }))
    },

    loadDoctores () {
      this.$axios.get('doctores').then(res => {
        this.doctoresOptions = res.data
        this.doctoresOptionsAll = res.data
      })
    },

    onChangeCi (val) {
      this.searchCi = val
      const name = (this.solicitud.paciente_nombre || '').toUpperCase()
      if (name.includes('RN')) return
      this.buscarPacientePorCi()
    },

    buscarPacientePorCi () {
      if (!this.searchCi) return
      this.loading = true
      this.solicitud.paciente_id = null
      this.solicitud.paciente_nombre = ''
      this.solicitud.paciente_telefono = ''
      this.solicitud.paciente_direccion = ''
      this.solicitud.paciente_fecha_nac = ''
      this.solicitud.paciente_genero = ''
      this.solicitud.paciente_edad = null
      this.solicitud.paciente_discapacidad = 0
      this.solicitud.paciente_discapacidad_cual = ''
      this.solicitud.paciente_discapacidad_otro = ''
      this.solicitud.paciente_embarazo = 0
      this.solicitud.paciente_fum = ''
      this.solicitud.paciente_sem_gest = null
      this.$axios
        .get(`pacientes/buscar-ci/${this.searchCi}`)
        .then(res => { this.onSelectPaciente(res.data) })
        .catch(() => {
          // paciente no encontrado vaciar los demas campos
        })
        .finally(() => { this.loading = false })
    },

    onSelectPaciente (p) {
      if (!p) return
      this.solicitud.paciente_id = p.id
      this.solicitud.paciente_nombre = p.nombre_completo
      this.solicitud.paciente_ci = p.ci
      this.solicitud.paciente_telefono = p.telefono
      this.solicitud.paciente_direccion = p.direccion
      this.solicitud.paciente_fecha_nac = p.fecha_nac
      this.solicitud.paciente_genero = p.genero
      this.solicitud.paciente_edad = p.edad
      this.solicitud.paciente_discapacidad = p.discapacidad ?? 0
      this.solicitud.paciente_discapacidad_cual = p.discapacidad_cual || ''
      this.solicitud.paciente_discapacidad_otro = p.discapacidad_otro || ''
      this.solicitud.paciente_embarazo = p.embarazo ?? 0
      this.solicitud.paciente_fum = p.fum || ''
      this.solicitud.paciente_sem_gest = p.sem_gest
    },

    onSelectDoctor (id) {
      const d = this.doctoresOptions.find(x => x.id === id)
      if (!d) return
      this.solicitud.doctor_id = d.id
      this.solicitud.doctor_nombre = d.nombre
      this.solicitud.doctor_especialidad = d.especialidad
      this.solicitud.doctor_ci = d.ci
      this.solicitud.doctor_telefono = d.telefono
      this.solicitud.doctor_email = d.email
      this.solicitud.doctor_registro = d.registro
      if (d.establecimiento?.nombre) this.solicitud.establecimiento_salud = d.establecimiento.nombre
    },

    onTipoAtencionChange () {
      // this.resetServiciosSelection()
      if (this.solicitud.tipo_atencion === 'NO') this.solicitud.establecimiento_salud = ''
      else this.solicitud.tipo_otro = ''
    },

    onEstablecimientoChange () {
      this.resetServiciosSelection()
    },

    filteredServicios (area) {
      let servicios = area.servicios || []

      if (this.serviciosAreaId && area.id !== this.serviciosAreaId) return []

      if (this.solicitud.tipo_atencion === 'SI') {
        const est = this.currentEstablecimiento
        if (est && Array.isArray(est.servicio_ids) && est.servicio_ids.length) {
          const allowed = new Set(est.servicio_ids)
          servicios = servicios.filter(s => allowed.has(s.id))
        }
      }

      const text = (this.serviciosFilter || '').toLowerCase().trim()
      if (!text) return servicios

      return servicios.filter(s => {
        const nombre = String(s.nombre ?? '').toLowerCase()
        const sub = String(s.subarea ?? '').toLowerCase()
        const codigo = String(s.codigo ?? '').toLowerCase()
        return nombre.includes(text) || sub.includes(text) || codigo.includes(text)
      })
    },

    guardar () {
      this.normalizePacienteUpperFields()
      // armar servicios
      this.solicitud.servicios = []
      this.areas.forEach(area => {
        (area.servicios || []).forEach(servicio => {
          if (servicio.seleccionado) {
            this.solicitud.servicios.push({
              id: servicio.id,
              nombre: servicio.nombre,
              precio: servicio.precio,
              area_id: area.id
            })
          }
        })
      })

      if (this.solicitud.servicios.length === 0) {
        this.$alert?.error ? this.$alert.error('Seleccione al menos un servicio') : alert('Seleccione al menos un servicio')
        return
      }
      // if (!this.solicitud.paciente_ci) {
      //   this.$alert?.error ? this.$alert.error('Coloque la CI del paciente') : alert('Coloque la CI del paciente')
      //   return
      // }


      if (this.solicitud.id) {
        if (this.solicitud.codigo == null){
          this.actulizarSolicitud()
        }else{
          this.$q.dialog({
            title: 'Confirmar',
            message: 'La solicitud ya tiene un código asignado, ¿desea actualizar la información?',
            cancel: true,
            persistent: true
          }).onOk(() => {
            this.actulizarSolicitud()
          })
        }
      }else{
        this.loading = true
        this.$axios
          .post('solicitudes', this.solicitud)
          .then((res) => {
            const solicitudCreada = res.data || {}
            this.$q.dialog({
              size: '400px',
              title: 'Solicitud guardada',
              message: '<span class="text-primary" style="font-size: 20px">¿Desea llenar el consentimiento ahora?</span>' +
                '<br><small>Puede llenarlo después desde la sección de solicitudes</small>',
              html: true,
              cancel: true,
              ok: {
                label: 'Sí, llenar ahora',
                color: 'primary'
              },
              cancel: {
                label: 'No'
              },
              persistent: true
            }).onOk(() => {
              this.abrirConsentimientoNuevaSolicitud(solicitudCreada)
            }).onCancel(() => {
              this.$alert?.success ? this.$alert.success('Solicitud guardada correctamente') : null
              this.$router.push({ path: '/solicitudes' })
            })
          })
          .catch(e => {
            const msg = e.response?.data?.message || e.message
            this.$alert?.error ? this.$alert.error('Error al guardar: ' + msg) : console.error(msg)
          })
          .finally(() => { this.loading = false })
      }
    },
    actulizarSolicitud () {
      this.normalizePacienteUpperFields()
      this.loading = true
      this.$axios
        .put(`solicitudes/${this.solicitud.id}`, this.solicitud)
        .then(() => {
          this.$alert.success('Actualizado correctamente')
          this.$router.push({ path: '/solicitudes' })
        })
        .catch(e => {
          const msg = e.response?.data?.message || e.message
          this.$alert.error('Error al actualizar: ' + msg)
        })
        .finally(() => { this.loading = false })
    }
  }
}
</script>

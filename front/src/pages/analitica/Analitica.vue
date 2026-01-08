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
                <q-btn-dropdown dense color="primary" no-caps label="Opciones" size="10px">
                  <q-list>
                    <!-- HEMATOLOGÍA -->
                    <q-item clickable @click="selectHematologia(solicitud)" v-close-popup dense>
                      <q-item-section avatar><q-icon name="bloodtype" /></q-item-section>
                      <q-item-section>Hematología</q-item-section>
                    </q-item>

                    <q-item clickable @click="printHematologia(solicitud)" v-close-popup dense v-if="solicitud.hematologia?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Hematología</q-item-section>
                    </q-item>
                    <!--                    enviar whatsapp dcotor y paciente-->
                    <q-item clickable @click="enviarWhatsApp(solicitud,'HematologiaDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.hematologia?.code">
                      <q-item-section avatar>
                        <q-icon name="fa-brands fa-whatsapp" />
                      </q-item-section>
                      <q-item-section>
                        WhatsApp Doctor({{solicitud.doctor_telefono}})
                        <!--                        <pre>{{solicitud.doctor_telefono}}</pre>-->
                      </q-item-section>
                    </q-item>
                    <q-item clickable @click="enviarWhatsApp(solicitud,'HematologiaPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.hematologia?.code">
                      <q-item-section avatar>
                        <q-icon name="fa-brands fa-whatsapp" />
                      </q-item-section>
                      <q-item-section>
                        WhatsApp Paciente({{solicitud.paciente_telefono}})
                        <!--                        <pre>{{solicitud.paciente_telefono}}</pre>-->
                      </q-item-section>
                    </q-item>

                    <q-separator spaced />

<!--                    &lt;!&ndash; QUÍMICA SANGUÍNEA &ndash;&gt;-->
<!--                    <q-item clickable @click="$router.push({ name: 'analitica-quimica-sanguinia', params: { id: solicitud.id } })" v-close-popup dense>-->
<!--                      <q-item-section avatar><q-icon name="science" /></q-item-section>-->
<!--                      <q-item-section>Química Sanguínea</q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-item clickable @click="printQuimica(solicitud)" v-close-popup dense v-if="solicitud.quimica_sanguinea?.code">-->
<!--                      <q-item-section avatar><q-icon name="print" /></q-item-section>-->
<!--                      <q-item-section>Imprimir Química Sanguínea</q-item-section>-->
<!--                    </q-item>-->
<!--&lt;!&ndash;                    imprimi curva de tolerancia&ndash;&gt;-->
<!--                    <q-item clickable @click="printQuimicaTolerancia(solicitud)" v-close-popup dense v-if="solicitud.quimica_sanguinea?.code">-->
<!--                      <q-item-section avatar><q-icon name="show_chart" /></q-item-section>-->
<!--                      <q-item-section>Curva de Tolerancia</q-item-section>-->
<!--                    </q-item>-->
<!--&lt;!&ndash;                    imprecion de cito quimico&ndash;&gt;-->
<!--                    <q-item clickable @click="printCitoQuimico(solicitud)" v-close-popup dense v-if="solicitud.quimica_sanguinea?.code">-->
<!--                      <q-item-section avatar><q-icon name="biotech" /></q-item-section>-->
<!--                      <q-item-section>Cito Químico</q-item-section>-->
<!--                    </q-item>-->


                    <!-- QUÍMICA SANGUÍNEA -->
                    <q-item clickable @click="$router.push({ name: 'analitica-quimica-sanguinia', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="science" /></q-item-section>
                      <q-item-section>Química Sanguínea</q-item-section>
                    </q-item>

                    <!-- SUBMENÚ: IMPRESIÓN -->
                    <q-item dense v-if="solicitud.quimica_sanguinea?.code" class="cursor-pointer">
                      <q-item-section avatar>
                        <q-icon name="print" />
                      </q-item-section>

                      <q-item-section>
                        Impresión
                      </q-item-section>

                      <q-item-section side>
                        <q-icon name="chevron_right" />
                      </q-item-section>

                      <q-menu anchor="top end" self="top start">
                        <q-list dense style="min-width: 220px">

                          <q-item clickable v-close-popup dense @click="printQuimica(solicitud)">
                            <q-item-section avatar><q-icon name="science" /></q-item-section>
                            <q-item-section>Imprimir Química</q-item-section>
                          </q-item>

                          <q-item clickable v-close-popup dense @click="printQuimicaTolerancia(solicitud)">
                            <q-item-section avatar><q-icon name="show_chart" /></q-item-section>
                            <q-item-section>Curva de Tolerancia</q-item-section>
                          </q-item>

                          <q-item clickable v-close-popup dense @click="printCitoQuimico(solicitud)">
                            <q-item-section avatar><q-icon name="biotech" /></q-item-section>
                            <q-item-section>Citoquímico</q-item-section>
                          </q-item>

                          <!-- FUTURO: aquí vas agregando más items de impresión -->
                          <!--
                          <q-item clickable v-close-popup dense @click="printOtroFormulario(solicitud)">
                            <q-item-section avatar><q-icon name="description" /></q-item-section>
                            <q-item-section>Otro Formulario</q-item-section>
                          </q-item>
                          -->

                        </q-list>
                      </q-menu>
                    </q-item>
                    <!-- WhatsApp Química -->
                    <q-item clickable @click="enviarWhatsApp(solicitud,'QuimicaDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.quimica_sanguinea?.code">
                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                      <q-item-section>WhatsApp Doctor({{solicitud.doctor_telefono}})</q-item-section>
                    </q-item>
                    <q-item clickable @click="enviarWhatsApp(solicitud,'QuimicaPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.quimica_sanguinea?.code">
                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                      <q-item-section>WhatsApp Paciente({{solicitud.paciente_telefono}})</q-item-section>
                    </q-item>

                    <q-separator spaced />

                    <!-- UROANÁLISIS -->
                    <q-item clickable @click="$router.push({ name: 'analitica-uroanalisis', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="water_drop" /></q-item-section>
                      <q-item-section>
                        Uroanálisis
                        <!--                        <pre>{{solicitud.uroanalisis.code}}</pre>-->
                      </q-item-section>
                    </q-item>

                    <q-item clickable @click="printUroanalisis(solicitud)" v-close-popup dense v-if="solicitud.uroanalisis?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Uroanálisis</q-item-section>
                    </q-item>

                    <!-- WhatsApp Uroanálisis -->
                    <q-item clickable @click="enviarWhatsApp(solicitud,'UroanalisisDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.uroanalisis?.code">
                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                      <q-item-section>WhatsApp Doctor({{solicitud.doctor_telefono}})</q-item-section>
                    </q-item>
                    <q-item clickable @click="enviarWhatsApp(solicitud,'UroanalisisPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.uroanalisis?.code">
                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                      <q-item-section>WhatsApp Paciente({{solicitud.paciente_telefono}})</q-item-section>
                    </q-item>

                    <q-separator spaced />

                    <!-- PARASITOLOGÍA -->
                    <q-item clickable @click="$router.push({ name: 'analitica-parasitologia', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="bug_report" /></q-item-section>
                      <q-item-section>Parasitología</q-item-section>
                    </q-item>

                    <q-item clickable @click="printParasitologia(solicitud)" v-close-popup dense v-if="solicitud.parasitologia?.code">
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Parasitología</q-item-section>
                    </q-item>

                    <!-- WhatsApp Parasitología -->
                    <q-item clickable @click="enviarWhatsApp(solicitud,'ParasitologiaDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.parasitologia?.code">
                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                      <q-item-section>WhatsApp Doctor({{solicitud.doctor_telefono}})</q-item-section>
                    </q-item>
                    <q-item clickable @click="enviarWhatsApp(solicitud,'ParasitologiaPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.parasitologia?.code">
                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                      <q-item-section>WhatsApp Paciente({{solicitud.paciente_telefono}})</q-item-section>
                    </q-item>

                    <q-separator spaced />

                    <!-- PAPILOMA HUMANO -->
<!--                    <q-item clickable @click="$router.push({ name: 'analitica-papiloma-humano', params: { id: solicitud.id } })" v-close-popup dense>-->
<!--                      <q-item-section avatar><q-icon name="health_and_safety" /></q-item-section>-->
<!--                      <q-item-section>-->
<!--                        Papiloma Humano-->
<!--                        &lt;!&ndash;                        <pre>{{solicitud}}</pre>&ndash;&gt;-->
<!--                      </q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-item clickable @click="printPapilomaHumano(solicitud)" v-close-popup dense v-if="solicitud.papiloma_humano?.code">-->
<!--                      <q-item-section avatar><q-icon name="print" /></q-item-section>-->
<!--                      <q-item-section>Imprimir Papiloma Humano</q-item-section>-->
<!--                    </q-item>-->
<!--                    &lt;!&ndash; WhatsApp Papiloma Humano &ndash;&gt;-->
<!--                    <q-item clickable @click="enviarWhatsApp(solicitud,'PapilomaDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.papiloma_humano?.code">-->
<!--                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>-->
<!--                      <q-item-section>WhatsApp Doctor({{solicitud.doctor_telefono}})</q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-item clickable @click="enviarWhatsApp(solicitud,'PapilomaPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.papiloma_humano?.code">-->
<!--                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>-->
<!--                      <q-item-section>WhatsApp Paciente({{solicitud.paciente_telefono}})</q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-separator spaced />-->
<!--                    &lt;!&ndash; PANEL RESPIRATORIO &ndash;&gt;-->
<!--                    <q-item clickable @click="$router.push({ name: 'analitica-panel-respiratorio', params: { id: solicitud.id } })" v-close-popup dense>-->
<!--                      <q-item-section avatar><q-icon name="air" /></q-item-section>-->
<!--                      <q-item-section>Panel Respiratorio</q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-item clickable @click="printPanelRespiratorio(solicitud)" v-close-popup dense v-if="solicitud.panel_respiratorio?.code">-->
<!--                      <q-item-section avatar><q-icon name="print" /></q-item-section>-->
<!--                      <q-item-section>Imprimir Panel Respiratorio</q-item-section>-->
<!--                    </q-item>-->
<!--                    &lt;!&ndash; WhatsApp Panel Respiratorio &ndash;&gt;-->
<!--                    <q-item clickable @click="enviarWhatsApp(solicitud,'PanelRespiratorioDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.panel_respiratorio?.code">-->
<!--                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>-->
<!--                      <q-item-section>WhatsApp Doctor({{solicitud.doctor_telefono}})</q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-item clickable @click="enviarWhatsApp(solicitud,'PanelRespiratorioPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.panel_respiratorio?.code">-->
<!--                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>-->
<!--                      <q-item-section>WhatsApp Paciente({{solicitud.paciente_telefono}})</q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-separator spaced />-->
<!--                    &lt;!&ndash; PANEL SEXUAL &ndash;&gt;-->
<!--                    <q-item clickable @click="$router.push({ name: 'analitica-panel-sexual', params: { id: solicitud.id } })" v-close-popup dense>-->
<!--                      <q-item-section avatar><q-icon name="favorite" /></q-item-section>-->
<!--                      <q-item-section>Panel Sexual</q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-item clickable @click="printPanelSexual(solicitud)" v-close-popup dense v-if="solicitud.panel_sexual?.code">-->
<!--                      <q-item-section avatar><q-icon name="print" /></q-item-section>-->
<!--                      <q-item-section>Imprimir Panel Sexual</q-item-section>-->
<!--                    </q-item>-->

<!--                    en biologia molecular debe ir los 3 papilomas en su menu-->
                    <q-item dense class="cursor-pointer">
                      <q-item-section avatar>
                        <q-icon name="print" />
                      </q-item-section>

                      <q-item-section>
                        Biologia molecular
                      </q-item-section>

                      <q-item-section side>
                        <q-icon name="chevron_right" />
                      </q-item-section>

                      <q-menu anchor="top end" self="top start">
                        <q-list dense style="min-width: 220px">

                          <q-item clickable v-close-popup dense @click="$router.push({ name: 'analitica-papiloma-humano', params: { id: solicitud.id } })" >
                            <q-item-section  avatar><q-icon name="health_and_safety" /></q-item-section>
                            <q-item-section>Papiloma Humano</q-item-section>
                          </q-item>
                          <q-item clickable v-close-popup dense @click="printPapilomaHumano(solicitud)" v-if="solicitud.papiloma_humano?.code">
                            <q-item-section avatar><q-icon name="print" /></q-item-section>
                            <q-item-section>Imprimir Papiloma Humano</q-item-section>
                          </q-item>
                          <q-item clickable @click="enviarWhatsApp(solicitud,'PapilomaDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.papiloma_humano?.code">
                            <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                            <q-item-section>WhatsApp Doctor({{solicitud.doctor_telefono}})</q-item-section>
                          </q-item>
                          <q-item clickable @click="enviarWhatsApp(solicitud,'PapilomaPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.papiloma_humano?.code">
                            <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                            <q-item-section>WhatsApp Paciente({{solicitud.paciente_telefono}})</q-item-section>
                          </q-item>
                          <q-separator spaced />
                          <q-item clickable v-close-popup dense @click="$router.push({ name: 'analitica-panel-respiratorio', params: { id: solicitud.id } })" >
                            <q-item-section  avatar><q-icon name="air" /></q-item-section>
                            <q-item-section>Panel Respiratorio</q-item-section>
                          </q-item>
                          <q-item clickable v-close-popup dense @click="printPanelRespiratorio(solicitud)" v-if="solicitud.panel_respiratorio?.code">
                            <q-item-section avatar><q-icon name="print" /></q-item-section>
                            <q-item-section>Imprimir Panel Respiratorio</q-item-section>
                          </q-item>
                          <q-item clickable @click="enviarWhatsApp(solicitud,'PanelRespiratorioDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.panel_respiratorio?.code">
                            <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                            <q-item-section>WhatsApp Doctor({{solicitud.doctor_telefono}})</q-item-section>
                          </q-item>
                          <q-item clickable @click="enviarWhatsApp(solicitud,'PanelRespiratorioPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.panel_respiratorio?.code">
                            <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                            <q-item-section>WhatsApp Paciente({{solicitud.paciente_telefono}})</q-item-section>
                          </q-item>
                          <q-separator spaced />
                          <q-item clickable v-close-popup dense @click="$router.push({ name: 'analitica-panel-sexual', params: { id: solicitud.id } })" >
                            <q-item-section  avatar><q-icon name="favorite" /></q-item-section>
                            <q-item-section>Panel Sexual</q-item-section>
                          </q-item>
                          <q-item clickable v-close-popup dense @click="printPanelSexual(solicitud)" v-if="solicitud.panel_sexual?.code">
                            <q-item-section avatar><q-icon name="print" /></q-item-section>
                            <q-item-section>Imprimir Panel Sexual</q-item-section>
                          </q-item>
                          <q-item clickable @click="enviarWhatsApp(solicitud,'PanelSexualDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.panel_sexual?.code">
                            <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                            <q-item-section>WhatsApp Doctor({{solicitud.doctor_telefono}})</q-item-section>
                          </q-item>
                          <q-item clickable @click="enviarWhatsApp(solicitud,'PanelSexualPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.panel_sexual?.code">
                            <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                            <q-item-section>WhatsApp Paciente({{solicitud.paciente_telefono}})</q-item-section>
                          </q-item>
                        </q-list>
                      </q-menu>
                    </q-item>
<!--                    <q-item dense v-if="solicitud.quimica_sanguinea?.code" class="cursor-pointer">-->

                    <!-- WhatsApp Panel Sexual -->
                    <q-item clickable @click="enviarWhatsApp(solicitud,'PanelSexualDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.panel_sexual?.code">
                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                      <q-item-section>WhatsApp Doctor({{solicitud.doctor_telefono}})</q-item-section>
                    </q-item>
                    <q-item clickable @click="enviarWhatsApp(solicitud,'PanelSexualPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.panel_sexual?.code">
                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                      <q-item-section>WhatsApp Paciente({{solicitud.paciente_telefono}})</q-item-section>
                    </q-item>

                    <q-separator spaced />

                    <!-- INMUNOLOGÍA -->
                    <q-item clickable @click="$router.push({ name: 'analitica-inmunologia', params: { id: solicitud.id } })" v-close-popup dense>
                      <q-item-section avatar><q-icon name="shield" /></q-item-section>
                      <q-item-section>
                        Inmunología
                        <!--                        <pre>{{solicitud}}</pre>-->
                      </q-item-section>
                    </q-item>
                    <q-item clickable @click="printInmunologia(solicitud)" v-close-popup dense>
                      <q-item-section avatar><q-icon name="print" /></q-item-section>
                      <q-item-section>Imprimir Inmunología</q-item-section>
                    </q-item>

                    <!-- WhatsApp Inmunología (solo si hay "code"; si tu backend no usa code, ajusta aquí) -->
                    <q-item clickable @click="enviarWhatsApp(solicitud,'InmunologiaDoctor')" v-close-popup dense v-if="solicitud.doctor_telefono && solicitud.inmunologia?.code">
                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                      <q-item-section>WhatsApp Doctor({{solicitud.doctor_telefono}})</q-item-section>
                    </q-item>
                    <q-item clickable @click="enviarWhatsApp(solicitud,'InmunologiaPaciente')" v-close-popup dense v-if="solicitud.paciente_telefono && solicitud.inmunologia?.code">
                      <q-item-section avatar><q-icon name="fa-brands fa-whatsapp" /></q-item-section>
                      <q-item-section>WhatsApp Paciente({{solicitud.paciente_telefono}})</q-item-section>
                    </q-item>

                  </q-list>
                </q-btn-dropdown>
              </td>
              <td>{{ solicitud.id }}</td>
              <td>{{ solicitud.paciente_nombre }}</td>
              <td>{{ solicitud.paciente_ci }}</td>
              <td>{{ solicitud.establecimiento_salud }}</td>
              <td>{{ solicitud.fecha_envio_analitica }}</td>
              <td>
                <q-chip v-if="solicitud.estado === 'ANALIZADO'" color="green" text-color="white" dense>
                  Finalizado
                </q-chip>
                <q-chip v-else-if="solicitud.estado === 'ENVIADO_ANALITICA'" color="red" text-color="white" dense>
                  Recibido
                </q-chip>
<!--                MUESTRA RECHAZADA-->
                <q-chip v-else-if="solicitud.estado === 'MUESTRA RECHAZADA'" color="orange" text-color="white" dense>
                  Muestra Rechazada
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
    // if (!this.$store.socketAnalitica) {
    //   this.$store.socketAnalitica = true
    //   this.$socket.on('silSolicitud', msg => {
    //     this.$alert.info('Nueva solicitud de analítica recibido.')
    //     this.analiticaGet()
    //   })
    // }
  },
  methods: {
    printQuimica (solicitud) {
      const url = `${this.$axios.defaults.baseURL}/quimica-sanguinea/solicitud/${solicitud.quimica_sanguinea?.code}/pdf`
      window.open(url, '_blank')
    },
    printQuimicaTolerancia(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/quimica-sanguinea/solicitud/${solicitud.quimica_sanguinea?.code}/pdf-tolerancia`
      window.open(url, '_blank')
    },

    printCitoQuimico(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/quimica-sanguinea/solicitud/${solicitud.quimica_sanguinea?.code}/pdf-cito-quimico`
      window.open(url, '_blank')
    },

    enviarWhatsApp(solicitud, tipo) {
      let mensajeWhatssApp = ''
      let telefono = ''
      let linkPdf = ''

      // ===== Links por tipo (mismo estilo que Hematología) =====
      if (tipo === 'HematologiaDoctor' || tipo === 'HematologiaPaciente') {
        linkPdf = `${this.$axios.defaults.baseURL}/hematologia/solicitud/${solicitud.hematologia?.code}/pdf`
      } else if (tipo === 'QuimicaDoctor' || tipo === 'QuimicaPaciente') {
        linkPdf = `${this.$axios.defaults.baseURL}/quimica-sanguinea/solicitud/${solicitud.quimica_sanguinea?.code}/pdf`
      } else if (tipo === 'UroanalisisDoctor' || tipo === 'UroanalisisPaciente') {
        linkPdf = `${this.$axios.defaults.baseURL}/uroanalisis/solicitud/${solicitud.uroanalisis?.code}/pdf`
      } else if (tipo === 'ParasitologiaDoctor' || tipo === 'ParasitologiaPaciente') {
        linkPdf = `${this.$axios.defaults.baseURL}/parasitologia/solicitud/${solicitud.parasitologia?.code}/pdf`
      } else if (tipo === 'PapilomaDoctor' || tipo === 'PapilomaPaciente') {
        linkPdf = `${this.$axios.defaults.baseURL}/papiloma-humano/solicitud/${solicitud.papiloma_humano?.code}/pdf`
      } else if (tipo === 'PanelRespiratorioDoctor' || tipo === 'PanelRespiratorioPaciente') {
        linkPdf = `${this.$axios.defaults.baseURL}/panel-respiratorio/solicitud/${solicitud.panel_respiratorio?.code}/pdf`
      } else if (tipo === 'PanelSexualDoctor' || tipo === 'PanelSexualPaciente') {
        linkPdf = `${this.$axios.defaults.baseURL}/panel-sexual/solicitud/${solicitud.panel_sexual?.code}/pdf`
      } else if (tipo === 'InmunologiaDoctor' || tipo === 'InmunologiaPaciente') {
        // Nota: aquí asumo que Inmunología tiene code (como los demás). Si no, quítalo y usa tu pdf-all.
        linkPdf = `${this.$axios.defaults.baseURL}/inmunologia/solicitud/${solicitud.inmunologia?.code}/pdf`
      }

      // ===== Mensajes Doctor/Paciente =====
      if (tipo === 'HematologiaDoctor') {
        mensajeWhatssApp = `Estimado Dr. ${solicitud.doctor_nombre}, le informamos que los resultados de Hematología para el paciente ${solicitud.paciente_nombre} (CI: ${solicitud.paciente_ci}) ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.doctor_telefono
      } else if (tipo === 'HematologiaPaciente') {
        mensajeWhatssApp = `Estimado/a ${solicitud.paciente_nombre}, le informamos que sus resultados de Hematología ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.paciente_telefono
      } else if (tipo === 'QuimicaDoctor') {
        mensajeWhatssApp = `Estimado Dr. ${solicitud.doctor_nombre}, le informamos que los resultados de Química Sanguínea para el paciente ${solicitud.paciente_nombre} (CI: ${solicitud.paciente_ci}) ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.doctor_telefono
      } else if (tipo === 'QuimicaPaciente') {
        mensajeWhatssApp = `Estimado/a ${solicitud.paciente_nombre}, le informamos que sus resultados de Química Sanguínea ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.paciente_telefono
      } else if (tipo === 'UroanalisisDoctor') {
        mensajeWhatssApp = `Estimado Dr. ${solicitud.doctor_nombre}, le informamos que los resultados de Uroanálisis para el paciente ${solicitud.paciente_nombre} (CI: ${solicitud.paciente_ci}) ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.doctor_telefono
      } else if (tipo === 'UroanalisisPaciente') {
        mensajeWhatssApp = `Estimado/a ${solicitud.paciente_nombre}, le informamos que sus resultados de Uroanálisis ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.paciente_telefono
      } else if (tipo === 'ParasitologiaDoctor') {
        mensajeWhatssApp = `Estimado Dr. ${solicitud.doctor_nombre}, le informamos que los resultados de Parasitología para el paciente ${solicitud.paciente_nombre} (CI: ${solicitud.paciente_ci}) ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.doctor_telefono
      } else if (tipo === 'ParasitologiaPaciente') {
        mensajeWhatssApp = `Estimado/a ${solicitud.paciente_nombre}, le informamos que sus resultados de Parasitología ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.paciente_telefono
      } else if (tipo === 'PapilomaDoctor') {
        mensajeWhatssApp = `Estimado Dr. ${solicitud.doctor_nombre}, le informamos que los resultados de Papiloma Humano para el paciente ${solicitud.paciente_nombre} (CI: ${solicitud.paciente_ci}) ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.doctor_telefono
      } else if (tipo === 'PapilomaPaciente') {
        mensajeWhatssApp = `Estimado/a ${solicitud.paciente_nombre}, le informamos que sus resultados de Papiloma Humano ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.paciente_telefono
      } else if (tipo === 'PanelRespiratorioDoctor') {
        mensajeWhatssApp = `Estimado Dr. ${solicitud.doctor_nombre}, le informamos que los resultados de Panel Respiratorio para el paciente ${solicitud.paciente_nombre} (CI: ${solicitud.paciente_ci}) ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.doctor_telefono
      } else if (tipo === 'PanelRespiratorioPaciente') {
        mensajeWhatssApp = `Estimado/a ${solicitud.paciente_nombre}, le informamos que sus resultados de Panel Respiratorio ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.paciente_telefono
      } else if (tipo === 'PanelSexualDoctor') {
        mensajeWhatssApp = `Estimado Dr. ${solicitud.doctor_nombre}, le informamos que los resultados de Panel Sexual para el paciente ${solicitud.paciente_nombre} (CI: ${solicitud.paciente_ci}) ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.doctor_telefono
      } else if (tipo === 'PanelSexualPaciente') {
        mensajeWhatssApp = `Estimado/a ${solicitud.paciente_nombre}, le informamos que sus resultados de Panel Sexual ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.paciente_telefono
      } else if (tipo === 'InmunologiaDoctor') {
        mensajeWhatssApp = `Estimado Dr. ${solicitud.doctor_nombre}, le informamos que los resultados de Inmunología para el paciente ${solicitud.paciente_nombre} (CI: ${solicitud.paciente_ci}) ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.doctor_telefono
      } else if (tipo === 'InmunologiaPaciente') {
        mensajeWhatssApp = `Estimado/a ${solicitud.paciente_nombre}, le informamos que sus resultados de Inmunología ya están disponibles. Puede acceder a los resultados en el siguiente enlace: ${linkPdf}`
        telefono = solicitud.paciente_telefono
      }

      const urlWhatsApp = `https://api.whatsapp.com/send?phone=${telefono}&text=${encodeURIComponent(mensajeWhatssApp)}`
      window.open(urlWhatsApp, '_blank')
    },

    printHematologia(solicitud) {
      // $query = Solicitude::with([
      //   'paciente', 'doctor', 'servicios.area.rangos', 'resultados',
      //   'hematologia',
      //   'quimicaSanguinea',
      //   'uroanalisis',
      //   'parasitologia',
      //   'papilomaHumano',
      //   'panelRespiratorio',
      //   'panelSexual',
      //   'cultivoAntibiograma',
      // ])
      const url = `${this.$axios.defaults.baseURL}/hematologia/solicitud/${solicitud.hematologia?.code}/pdf`
      window.open(url, '_blank')
    },
    printUroanalisis (solicitud) {
      const url = `${this.$axios.defaults.baseURL}/uroanalisis/solicitud/${solicitud.uroanalisis?.code}/pdf`
      window.open(url, '_blank')
    },
    printParasitologia(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/parasitologia/solicitud/${solicitud.parasitologia?.code}/pdf`
      window.open(url, '_blank')
    },
    printPapilomaHumano(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/papiloma-humano/solicitud/${solicitud.papiloma_humano?.code}/pdf`
      window.open(url, '_blank')
    },
    printPanelRespiratorio(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/panel-respiratorio/solicitud/${solicitud.panel_respiratorio?.code}/pdf`
      window.open(url, '_blank')
    },
    printPanelSexual(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/panel-sexual/solicitud/${solicitud.panel_sexual?.code}/pdf`
      window.open(url, '_blank')
    },
    printCultivoAntibiograma(solicitud) {
      const url = `${this.$axios.defaults.baseURL}/cultivo-antibiograma/solicitud/${solicitud.cultivoAntibiograma?.code}/pdf`
      window.open(url, '_blank')
    },
    printInmunologia(solicitud) {
      // const url = `${this.$axios.defaults.baseURL}/inmunologia/solicitud/${solicitud.id}/pdf`
      // window.open(url, '_blank')
      // http://localhost:8000/api/inmunologia/solicitud/3/pdf-all?area_id=5
      const url = `${this.$axios.defaults.baseURL}/inmunologia/solicitud/${solicitud.id}/pdf-all?area_id=5`
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

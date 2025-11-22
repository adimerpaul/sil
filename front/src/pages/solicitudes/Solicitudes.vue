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
              <div class="col-6 col-sm-3">
                <q-input
                  v-model="solicitud.paciente_ci"
                  label="CI"
                  dense outlined
                  @update:model-value="onChangeCi"
                  debounce="300"
                />
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
            </div>

            <q-separator class="q-my-sm" />

            <!-- Datos de la solicitud -->
            <div class="row items-center q-mb-xs">
              <q-icon name="assignment" size="18px" class="q-mr-xs" />
              <div class="text-subtitle2">Datos de la solicitud</div>
            </div>
            <div class="row q-col-gutter-xs">
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
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="solicitud.diagnostico_clinico"
                  type="textarea"
                  label="Diagnóstico clínico principal"
                  dense outlined autogrow
                />
              </div>
            </div>

            <q-separator class="q-my-sm" />

            <!-- Servicios -->
            <div class="row items-center q-mb-xs">
              <q-icon name="biotech" size="18px" class="q-mr-xs" />
              <div class="text-subtitle2">Servicios solicitados</div>
            </div>

            <!-- FILTROS DE SERVICIOS -->
            <div class="row q-col-gutter-xs q-mb-xs">
              <div class="col-12 col-sm-6">
                <q-input
                  v-model="serviciosFilter"
                  dense outlined
                  label="Buscar servicio (nombre / código / subárea)"
                >
                  <template #append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-sm-6">
                <q-select
                  v-model="serviciosAreaId"
                  :options="areas"
                  option-label="name"
                  option-value="id"
                  dense outlined clearable
                  label="Filtrar por área"
                />
              </div>
            </div>

            <div class="row q-col-gutter-xs">
              <div class="col-12">
                <q-expansion-item
                  v-for="area in areas"
                  :key="area.id || area.name"
                  :label="area.name"
                  icon="science"
                  expand-separator
                  dense
                  v-show="filteredServicios(area).length > 0"
                >
                  <q-card flat>
                    <q-card-section class="q-pa-xs">
                      <div class="row q-col-gutter-xs">
                        <div
                          v-for="servicio in filteredServicios(area)"
                          :key="servicio.id || servicio.codigo"
                          class="col-12 col-sm-6"
                        >
                          <q-checkbox
                            v-model="servicio.seleccionado"
                            :true-value="1"
                            :false-value="0"
                            dense
                          >
                            <div>
                              {{ textCapitalize(servicio.nombre) }} (Bs. {{ servicio.precio }})
                            </div>
                            <div>
                              <small class="text-grey">
                                {{ servicio.subarea ? 'Subárea: ' + textCapitalize(servicio.subarea) : '' }}
                              </small>
                            </div>
                          </q-checkbox>
                        </div>
                      </div>
                    </q-card-section>
                  </q-card>
                </q-expansion-item>
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
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'id', label: 'ID', field: 'id', align: 'left' },
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
        { name: 'tipo_atencion', label: 'Tipo atención', field: 'tipo_atencion' },
        { name: 'estado', label: 'Estado', field: 'estado' }
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
      areas: [],
      establecimientos: [],

      // NUEVOS FILTROS DE SERVICIOS
      serviciosFilter: '',
      serviciosAreaId: null
    }
  },
  mounted () {
    this.getSolicitudes();
    // this.loadPacientes();
    this.loadDoctores();
    this.$axios.get('establecimientos').then(res => {
      this.establecimientos = res.data;
    });
    this.$axios.get('areas').then(res => {
      this.areas = res.data;
    });
  },
  methods: {
    textCapitalize (str) {
      if (!str) return '';
      return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    },
    onChangeCi (val) {
      this.searchCi = val;
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

      // reset selección de servicios
      this.areas.forEach(area => {
        area.servicios.forEach(servicio => {
          servicio.seleccionado = 0;
        });
      });

      // reset filtros de servicios
      this.serviciosFilter = '';
      this.serviciosAreaId = null;
    },
    editar (row) {
      this.solicitud = { ...row, paciente_id: row.paciente_id, doctor_id: row.doctor_id };
      this.editando = true;
      this.dialog = true;
    },
    guardar () {
      this.loading = true;
      this.solicitud.servicios = [];
      this.areas.forEach(area => {
        area.servicios.forEach(servicio => {
          if (servicio.seleccionado) {
            this.solicitud.servicios.push({
              id: servicio.id,
              nombre: servicio.nombre,
              precio: servicio.precio
            });
          }
        });
      });

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
          // Paciente no encontrado: silencio
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
    },

    // 🔎 Filtro de servicios por texto y área
    filteredServicios (area) {
      let servicios = area.servicios || [];

      // filtrar por área seleccionada
      if (this.serviciosAreaId && area.id !== this.serviciosAreaId) {
        return [];
      }

      const text = (this.serviciosFilter || '').toLowerCase().trim();
      if (!text) {
        return servicios;
      }

      return servicios.filter(serv => {
        const nombre = String(serv.nombre ?? '').toLowerCase();
        const sub    = String(serv.subarea ?? '').toLowerCase();
        const codigo = String(serv.codigo ?? '').toLowerCase(); // 👈 aquí el cambio clave

        return (
          nombre.includes(text) ||
          sub.includes(text) ||
          codigo.includes(text)
        );
      });
    }
  }
};
</script>

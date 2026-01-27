<template>
  <q-page class="q-pa-md">
    <q-table
      ref="tablePacientes"
      :rows="rows"
      :columns="columns"
      title="Pacientes"
      dense flat bordered
      row-key="id"
      :filter="filter"
      :pagination.sync="pagination"
      :rows-per-page-options="[10, 20, 50]"
      :loading="loading"
      @request="onRequest"
    >
      <!-- TOP -->
      <template #top-right>
        <q-btn
          color="positive"
          icon="add_circle_outline"
          label="Nuevo"
          @click="nuevo"
          no-caps
          class="q-mr-sm"
          :loading="loading"
        />
        <q-btn
          color="primary"
          icon="refresh"
          label="Actualizar"
          @click="getPacientes"
          no-caps
          class="q-mr-sm"
          :loading="loading"
        />
        <q-input dense outlined v-model="filter" label="Buscar" debounce="500">
          <template #append><q-icon name="search" /></template>
        </q-input>
      </template>

      <!-- ACCIONES -->
      <template #body-cell-actions="props">
        <q-td :props="props">
          <q-btn-dropdown label="Opciones" color="primary" dense size="10px" no-caps>
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

      <!-- BOTTOM CON PAGINACIÓN BONITA -->
      <template #bottom="scope">
        <div class="row items-center justify-between q-pa-sm full-width">
          <!-- Info -->
          <div class="col-12 col-sm-4 text-caption q-mb-xs q-mb-sm-none">
            <div>
              Mostrando
              <b>
                {{ firstRowIndex(scope.pagination) }}
                -
                {{ lastRowIndex(scope.pagination) }}
              </b>
              de
              <b>{{ scope.pagination.rowsNumber }}</b>
              pacientes
            </div>
          </div>

          <!-- Controles -->
          <div class="col-12 col-sm-8">
            <div class="row items-center justify-end q-gutter-sm">

              <!-- selector filas por página -->
              <div class="col-auto">
                <q-select
                  v-model="scope.pagination.rowsPerPage"
                  :options="[10, 20, 50]"
                  dense outlined
                  options-dense
                  style="width: 90px"
                  label="Filas"
                  @update:model-value="val => onChangeRowsPerPage(val, scope)"
                />
              </div>

              <!-- paginación -->
              <div class="col-auto">
                <q-pagination
                  v-model="scope.pagination.page"
                  :max="pagesNumber"
                  max-pages="7"
                  boundary-links
                  direction-links
                  icon-first="first_page"
                  icon-last="last_page"
                  icon-prev="chevron_left"
                  icon-next="chevron_right"
                  size="sm"
                  @update:model-value="val => onChangePage(val, scope)"
                />
              </div>
            </div>
          </div>
        </div>
      </template>
    </q-table>

    <!-- Diálogo -->
    <q-dialog v-model="dialog">
      <q-card style="min-width: 400px;">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ editando ? 'Editar Paciente' : 'Nuevo Paciente' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="guardar">
            <q-input v-model="paciente.nombre_completo" label="Nombre completo" dense outlined required />
            <q-input v-model="paciente.ci" label="CI" dense outlined />
            <q-input v-model="paciente.telefono" label="Teléfono/Celular" dense outlined />
            <q-input v-model="paciente.direccion" label="Dirección" dense outlined />
            <q-input
              v-model="paciente.fecha_nac"
              type="date"
              label="Fecha de nacimiento"
              dense outlined
              @update:model-value="calculateEdad"
              clearable
            />
            <q-select
              v-model="paciente.genero"
              label="Género"
              :options="['F','M','OTRO']"
              dense
              outlined
            />
            <div class="q-pa-xs q-mt-sm" style="border: 1px solid #ccc; border-radius: 4px;">
              <span>Edad calculada: </span>
              <span class="text-h6">
                {{ edadCaculado }}
              </span>
            </div>
            <div class="q-mt-sm">
              <q-toggle
                v-model="paciente.discapacidad"
                label="Discapacidad"
                :true-value="1"
                :false-value="0"
              />
            </div>
            <q-select
              v-if="paciente.discapacidad"
              v-model="paciente.discapacidad_cual"
              label="¿Cuál?"
              :options="discapacidades"
              dense
              outlined
            />
            <q-input
              v-if="paciente.discapacidad_cual === 'Otros'"
              v-model="paciente.discapacidad_otro"
              label="Especifique"
              dense
              outlined
            />
            <div class="q-mt-sm">
              <q-toggle
                v-model="paciente.embarazo"
                label="Embarazo"
                :true-value="1"
                :false-value="0"
              />
            </div>
            <q-input
              v-if="paciente.embarazo"
              v-model="paciente.fum"
              type="date"
              label="FUM"
              dense
              outlined
              @update:model-value="calculateFum"
            />
            <q-input
              v-if="paciente.embarazo"
              v-model="paciente.sem_gest"
              type="number"
              label="Semanas gestación"
              dense
              outlined
            />
            <div class="text-right q-mt-md">
              <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
              <q-btn color="primary" label="Guardar" type="submit" class="q-ml-sm" :loading="loading" />
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
  name: 'PacientesPage',
  data () {
    return {
      rows: [],
      filter: '',
      dialog: false,
      editando: false,
      loading: false,
      paciente: {},
      pagination: {
        page: 1,
        rowsPerPage: 20,
        rowsNumber: 0,
        sortBy: 'id',
        descending: true
      },
      columns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'id', label: 'Código paciente', field: 'id', align: 'left' },
        { name: 'nombre_completo', label: 'Nombre', field: 'nombre_completo' },
        { name: 'ci', label: 'CI', field: 'ci' },
        { name: 'telefono', label: 'Teléfono', field: 'telefono' },
        { name: 'genero', label: 'Género', field: 'genero' },
        {
          name: 'edad',
          label: 'Edad',
          field: (row) => {
            if (row.fecha_nac) {
              const birthDate = moment(row.fecha_nac);
              const today = moment();
              const years = today.diff(birthDate, 'years');
              birthDate.add(years, 'years');
              const months = today.diff(birthDate, 'months');
              birthDate.add(months, 'months');
              const days = today.diff(birthDate, 'days');
              return `${years}a ${months}m ${days}d`;
            }
            return '';
          }
        }
      ],
      discapacidades: [
        'Visual',
        'Auditiva',
        'Física-Motora',
        'Intelectual',
        'Psicosocial',
        'Otros'
      ]
    };
  },
  computed: {
    edadCaculado () {
      if (this.paciente.fecha_nac) {
        const birthDate = moment(this.paciente.fecha_nac);
        const today = moment();
        const years = today.diff(birthDate, 'years');
        birthDate.add(years, 'years');
        const months = today.diff(birthDate, 'months');
        birthDate.add(months, 'months');
        const days = today.diff(birthDate, 'days');
        return `${years} años ${months} meses ${days} dias`;
      }
      return '';
    },
    // número total de páginas
    pagesNumber () {
      const { rowsPerPage, rowsNumber } = this.pagination;
      if (!rowsPerPage || rowsPerPage <= 0) return 1;
      return Math.max(1, Math.ceil(rowsNumber / rowsPerPage));
    }
  },
  mounted () {
    this.getPacientes();
  },
  methods: {

    // índices para el texto "Mostrando X - Y de Z"
    firstRowIndex (pag) {
      if (pag.rowsNumber === 0) return 0;
      return (pag.page - 1) * pag.rowsPerPage + 1;
    },
    lastRowIndex (pag) {
      if (pag.rowsNumber === 0) return 0;
      const last = pag.page * pag.rowsPerPage;
      return last > pag.rowsNumber ? pag.rowsNumber : last;
    },

    calculateFum () {
      if (this.paciente.fum) {
        const fumDate = new Date(this.paciente.fum);
        const today = new Date();
        const diffTime = Math.abs(today - fumDate);
        const diffWeeks = Math.floor(diffTime / (1000 * 60 * 60 * 24 * 7));
        this.paciente.sem_gest = diffWeeks;
      } else {
        this.paciente.sem_gest = null;
      }
    },
    calculateEdad () {
      if (this.paciente.fecha_nac) {
        const birthDate = new Date(this.paciente.fecha_nac);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
          age--;
        }
        this.paciente.edad = age;
      } else {
        this.paciente.edad = null;
      }
    },

    // botón Actualizar / primera carga
    getPacientes () {
      if (this.$refs.tablePacientes) {
        this.$refs.tablePacientes.requestServerInteraction();
      } else {
        this.requestPacientes(this.pagination, this.filter);
      }
    },

    // handler QTable server-side
    onRequest (props) {
      const { page, rowsPerPage, sortBy, descending } = props.pagination;
      const filter = props.filter;

      // sincronizo mi objeto local con el del QTable
      this.pagination.page = page;
      this.pagination.rowsPerPage = rowsPerPage;
      this.pagination.sortBy = sortBy;
      this.pagination.descending = descending;

      this.requestPacientes(this.pagination, filter);
    },

    // llamada real al backend
    requestPacientes (pagination, filter) {
      this.loading = true;

      this.$axios.get('pacientes', {
        params: {
          page: pagination.page,
          per_page: pagination.rowsPerPage,
          search: filter || ''
        }
      })
        .then(res => {
          this.rows = res.data.data;
          // total de registros para todas las páginas
          this.pagination.rowsNumber = res.data.total;
        })
        .finally(() => {
          this.loading = false;
        });
    },

    // cuando cambia la página desde QPagination
    onChangePage (page, scope) {
      // scope.pagination ya tiene page por el v-model
      this.pagination.page = page;           // mantengo mi copia en sync
      if (this.$refs.tablePacientes) {
        this.$refs.tablePacientes.requestServerInteraction(); // dispara @request con la nueva page
      }
    },

    // cuando cambia las filas por página
    onChangeRowsPerPage (val, scope) {
      scope.pagination.page = 1;             // reset en el QTable
      this.pagination.rowsPerPage = val;
      this.pagination.page = 1;
      if (this.$refs.tablePacientes) {
        this.$refs.tablePacientes.requestServerInteraction();
      }
    },
    nuevo () {
      this.paciente = {
        nombre_completo: '',
        ci: '',
        telefono: '',
        direccion: '',
        fecha_nac: '',
        genero: '',
        edad: null,
        discapacidad: 0,
        discapacidad_cual: '',
        embarazo: 0,
        fum: '',
        sem_gest: null,
        discapacidad_otro: ''
      };
      this.editando = false;
      this.dialog = true;
    },

    editar (row) {
      this.paciente = { ...row };
      this.editando = true;
      this.dialog = true;
    },

    guardar () {
      this.loading = true;
      const req = this.editando
        ? this.$axios.put(`pacientes/${this.paciente.id}`, this.paciente)
        : this.$axios.post('pacientes', this.paciente);

      req.then(() => {
        this.$alert && this.$alert.success
          ? this.$alert.success('Guardado correctamente')
          : console.log('Guardado correctamente');
        this.dialog = false;
        this.getPacientes();
      })
        .catch((e) => {
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
      this.$alert.dialog('¿Eliminar paciente?').onOk(() => {
        this.$axios.delete(`pacientes/${id}`).then(() => {
          this.$alert && this.$alert.success
            ? this.$alert.success('Eliminado')
            : console.log('Eliminado');
          this.getPacientes();
        });
      });
    }
  }
};
</script>

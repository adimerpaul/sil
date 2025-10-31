<template>
  <q-page class="q-pa-md">
    <q-table
      :rows="rows"
      :columns="columns"
      title="Pacientes"
      dense flat bordered
      :rows-per-page-options="[0]"
      :filter="filter"
    >
      <template #top-right>
        <q-btn color="positive" icon="add_circle_outline" label="Nuevo" @click="nuevo" no-caps class="q-mr-sm"/>
        <q-btn color="primary" icon="refresh" label="Actualizar" @click="getPacientes" no-caps class="q-mr-sm"/>
        <q-input dense outlined v-model="filter" label="Buscar">
          <template #append><q-icon name="search"/></template>
        </q-input>
      </template>

      <template #body-cell-actions="props">
        <q-td :props="props">
          <q-btn-dropdown label="Opciones" color="primary" dense size="10px" no-caps>
            <q-list>
              <q-item clickable v-close-popup @click="editar(props.row)">
                <q-item-section avatar><q-icon name="edit"/></q-item-section>
                <q-item-section>Editar</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="eliminar(props.row.id)">
                <q-item-section avatar><q-icon name="delete"/></q-item-section>
                <q-item-section>Eliminar</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </q-td>
      </template>
    </q-table>

    <!-- Diálogo -->
    <q-dialog v-model="dialog">
      <q-card style="min-width: 400px;">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ editando ? 'Editar Paciente' : 'Nuevo Paciente' }}</div>
          <q-space/><q-btn icon="close" flat round dense v-close-popup/>
        </q-card-section>

        <q-card-section>
          <q-form @submit="guardar">
            <q-input v-model="paciente.nombre_completo" label="Nombre completo" dense outlined required/>
            <q-input v-model="paciente.ci" label="CI" dense outlined/>
            <q-input v-model="paciente.telefono" label="Teléfono" dense outlined/>
            <q-input v-model="paciente.direccion" label="Dirección" dense outlined/>
            <q-input v-model="paciente.fecha_nac" type="date" label="Fecha de nacimiento" dense outlined/>
            <q-select v-model="paciente.genero" label="Género" :options="['F','M','OTRO']" dense outlined/>
            <q-input v-model="paciente.edad" type="number" label="Edad" dense outlined/>
            <q-toggle v-model="paciente.discapacidad" label="Discapacidad"/>
            <q-input v-if="paciente.discapacidad" v-model="paciente.discapacidad_cual" label="¿Cuál?" dense outlined/>
            <q-toggle v-model="paciente.embarazo" label="Embarazo"/>
            <q-input v-if="paciente.embarazo" v-model="paciente.fum" type="date" label="FUM" dense outlined/>
            <q-input v-if="paciente.embarazo" v-model="paciente.sem_gest" type="number" label="Semanas gestación" dense outlined/>
            <div class="text-right q-mt-md">
              <q-btn flat label="Cancelar" v-close-popup/>
              <q-btn color="primary" label="Guardar" type="submit" class="q-ml-sm"/>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'PacientesPage',
  data() {
    return {
      rows: [],
      filter: '',
      dialog: false,
      editando: false,
      paciente: {},
      columns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'nombre_completo', label: 'Nombre', field: 'nombre_completo' },
        { name: 'ci', label: 'CI', field: 'ci' },
        { name: 'telefono', label: 'Teléfono', field: 'telefono' },
        { name: 'genero', label: 'Género', field: 'genero' },
        { name: 'edad', label: 'Edad', field: 'edad' },
      ],
    };
  },
  mounted() {
    this.getPacientes();
  },
  methods: {
    getPacientes() {
      this.$axios.get('pacientes').then(res => this.rows = res.data);
    },
    nuevo() {
      this.paciente = {};
      this.editando = false;
      this.dialog = true;
    },
    editar(row) {
      this.paciente = { ...row };
      this.editando = true;
      this.dialog = true;
    },
    guardar() {
      const req = this.editando
        ? this.$axios.put(`pacientes/${this.paciente.id}`, this.paciente)
        : this.$axios.post('pacientes', this.paciente);

      req.then(() => {
        this.$alert.success('Guardado correctamente');
        this.dialog = false;
        this.getPacientes();
      });
    },
    eliminar(id) {
      this.$alert.dialog('¿Eliminar paciente?').onOk(() => {
        this.$axios.delete(`pacientes/${id}`).then(() => {
          this.$alert.success('Eliminado');
          this.getPacientes();
        });
      });
    },
  },
};
</script>

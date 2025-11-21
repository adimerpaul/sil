<template>
  <q-page class="q-pa-md">
    <q-card flat bordered>
      <q-card-section class="row items-center q-col-gutter-sm">
        <div class="col-12 col-sm-6">
          <q-input
            dense
            outlined
            v-model="filter"
            label="Buscar doctor"
          >
            <template #append><q-icon name="search" /></template>
          </q-input>
        </div>
        <div class="col-12 col-sm-6 text-right">
          <q-btn
            color="primary"
            icon="add_circle_outline"
            label="Nuevo doctor"
            no-caps
            @click="nuevo"
            :loading="loading"
          />
        </div>
      </q-card-section>
    </q-card>

    <q-table
      class="q-mt-md"
      :rows="rows"
      :columns="columns"
      row-key="id"
      dense flat bordered
      :rows-per-page-options="[0]"
      :filter="filter"
      title="Doctores"
    >
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
    </q-table>

    <!-- Diálogo -->
    <q-dialog v-model="dialog">
      <q-card style="min-width: 400px; max-width: 600px;">
        <q-card-section class="row items-center">
          <div class="text-h6">
            {{ editando ? 'Editar doctor' : 'Nuevo doctor' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section>
          <q-form @submit="guardar">
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
                  v-model="doctor.estado"
                  :options="['ACTIVO', 'INACTIVO']"
                  label="Estado"
                  dense outlined
                  clearable
                />
              </div>
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
  </q-page>
</template>

<script>
export default {
  name: 'DoctoresPage',
  data () {
    return {
      rows: [],
      columns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'id', label: 'ID', field: 'id', align: 'left' },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left' },
        { name: 'especialidad', label: 'Especialidad', field: 'especialidad', align: 'left' },
        { name: 'ci', label: 'CI', field: 'ci', align: 'left' },
        { name: 'telefono', label: 'Teléfono', field: 'telefono', align: 'left' },
        { name: 'email', label: 'Email', field: 'email', align: 'left' },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left' },
      ],
      filter: '',
      dialog: false,
      editando: false,
      loading: false,
      doctor: {},
    };
  },
  mounted () {
    this.getDoctores();
  },
  methods: {
    getDoctores () {
      this.loading = true;
      this.$axios.get('doctores')
        .then(res => {
          this.rows = res.data;
        })
        .finally(() => {
          this.loading = false;
        });
    },
    nuevo () {
      this.doctor = {
        nombre: '',
        especialidad: '',
        ci: '',
        telefono: '',
        email: '',
        registro: '',
        estado: 'ACTIVO',
      };
      this.editando = false;
      this.dialog = true;
    },
    editar (row) {
      this.doctor = { ...row };
      this.editando = true;
      this.dialog = true;
    },
    guardar () {
      this.loading = true;

      const req = this.editando
        ? this.$axios.put(`doctores/${this.doctor.id}`, this.doctor)
        : this.$axios.post('doctores', this.doctor);

      req.then(() => {
        this.$alert && this.$alert.success
          ? this.$alert.success('Guardado correctamente')
          : console.log('Guardado correctamente');

        this.dialog = false;
        this.getDoctores();
      })
        .catch(e => {
          const msg = e.response?.data?.message || e.message;
          this.$alert && this.$alert.error
            ? this.$alert.error('Error al guardar: ' + msg)
            : console.error('Error al guardar: ', msg);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    eliminar (id) {
      if (this.$alert && this.$alert.dialog) {
        this.$alert.dialog('¿Eliminar doctor?').onOk(() => {
          this.$axios.delete(`doctores/${id}`).then(() => {
            this.$alert.success('Eliminado');
            this.getDoctores();
          });
        });
      } else {
        if (confirm('¿Eliminar doctor?')) {
          this.$axios.delete(`doctores/${id}`).then(() => {
            this.getDoctores();
          });
        }
      }
    },
  },
};
</script>

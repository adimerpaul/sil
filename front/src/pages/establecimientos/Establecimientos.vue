<template>
  <q-page class="q-pa-md bg-grey-2">
    <!-- Filtros -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center q-col-gutter-sm">
        <div class="col-12 col-sm-4">
          <q-input
            dense
            outlined
            v-model="filter"
            label="Buscar establecimiento"
            debounce="300"
            @update:model-value="getEstablecimientos"
          >
            <template #append><q-icon name="search" /></template>
          </q-input>
        </div>

        <div class="col-12 col-sm-3">
          <q-select
            v-model="filterTipo"
            :options="tipoOptions"
            dense outlined
            emit-value map-options
            label="Tipo"
            @update:model-value="getEstablecimientos"
            clearable
          />
        </div>

        <div class="col-12 col-sm-3">
          <q-select
            v-model="filterEstado"
            :options="estadoOptions"
            dense outlined
            emit-value map-options
            label="Estado"
            @update:model-value="getEstablecimientos"
            clearable
          />
        </div>

        <div class="col-12 col-sm-2 text-right">
          <q-btn
            color="primary"
            icon="add_circle_outline"
            label="Nuevo"
            no-caps
            @click="nuevo"
            :loading="loading"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Tabla -->
    <q-table
      class="q-mt-md"
      :rows="rows"
      :columns="columns"
      row-key="id"
      dense
      flat
      bordered
      :rows-per-page-options="[0]"
      :loading="loading"
      title="Establecimientos de salud"
    >
      <!-- Columna tipo con chip -->
      <template #body-cell-tipo="props">
        <q-td :props="props">
          <q-chip
            v-if="props.row.tipo"
            dense
            :color="props.row.tipo === 'PUBLICO' ? 'green-6' : 'indigo-5'"
            text-color="white"
            icon="local_hospital"
          >
            {{ props.row.tipo }}
          </q-chip>
        </q-td>
      </template>

      <!-- Columna estado con chip -->
      <template #body-cell-estado="props">
        <q-td :props="props">
          <q-chip
            dense
            :color="props.row.estado === 'ACTIVO' ? 'positive' : 'grey-6'"
            text-color="white"
            :icon="props.row.estado === 'ACTIVO' ? 'check_circle' : 'pause_circle'"
          >
            {{ props.row.estado }}
          </q-chip>
        </q-td>
      </template>

      <!-- Acciones -->
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
      <q-card style="min-width: 480px; max-width: 900px;">
        <q-card-section class="row items-center">
          <div class="text-h6">
            {{ editando ? 'Editar establecimiento' : 'Nuevo establecimiento' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section>
          <q-form @submit.prevent="guardar">
            <div class="row q-col-gutter-md">
              <div class="col-12">
                <q-input
                  v-model="establecimiento.nombre"
                  label="Nombre del establecimiento"
                  dense
                  outlined
                  autofocus
                >
                  <template #prepend>
                    <q-icon name="local_hospital" />
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-sm-6">
                <q-field label="Tipo" borderless stack-label>
                  <div class="row items-center q-gutter-sm">
                    <q-radio
                      v-model="establecimiento.tipo"
                      val="PUBLICO"
                      label="Público"
                      dense
                    />
                    <q-radio
                      v-model="establecimiento.tipo"
                      val="PRIVADO"
                      label="Privado"
                      dense
                    />
                  </div>
                </q-field>
              </div>

              <div class="col-12 col-sm-6">
                <q-select
                  v-model="establecimiento.nivel"
                  :options="nivelOptions"
                  label="Nivel"
                  dense
                  outlined
                  emit-value
                  map-options
                />
              </div>

              <div class="col-12">
                <q-input
                  v-model="establecimiento.direccion"
                  type="textarea"
                  autogrow
                  label="Dirección"
                  dense
                  outlined
                >
                  <template #prepend>
                    <q-icon name="place" />
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-sm-6">
                <q-input
                  v-model="establecimiento.telefono_contacto"
                  label="Teléfono establecimiento"
                  dense
                  outlined
                >
                  <template #prepend>
                    <q-icon name="call" />
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-sm-6">
                <q-input
                  v-model="establecimiento.responsable_laboratorio"
                  label="Responsable de laboratorio"
                  dense
                  outlined
                >
                  <template #prepend>
                    <q-icon name="badge" />
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-sm-6">
                <q-input
                  v-model="establecimiento.telefono_responsable"
                  label="Teléfono responsable"
                  dense
                  outlined
                >
                  <template #prepend>
                    <q-icon name="phone_in_talk" />
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-sm-6">
                <q-select
                  v-model="establecimiento.estado"
                  :options="['ACTIVO', 'INACTIVO']"
                  label="Estado"
                  dense
                  outlined
                  clearable
                />
              </div>

              <!-- NUEVO: selección de servicios -->
              <div class="col-12">
                <q-select
                  v-model="establecimiento.servicio_ids"
                  :options="serviciosOptions"
                  option-value="id"
                  :option-label="servicioLabel"
                  emit-value
                  map-options
                  multiple
                  use-chips
                  dense
                  outlined
                  label="Servicios que ofrece el establecimiento"
                  popup-content-class="q-pa-none"
                >
                  <!-- opción personalizada con código y área -->
                  <template #option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>
                          {{ servicioLabel(scope.opt) }}
                        </q-item-label>
                        <q-item-label caption v-if="scope.opt.area">
                          Área: {{ scope.opt.area.name }}
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
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
  name: 'EstablecimientosPage',
  data () {
    return {
      rows: [],
      columns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'id', label: 'ID', field: 'id', align: 'left' },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left' },
        { name: 'tipo', label: 'Tipo', field: 'tipo', align: 'left' },
        { name: 'nivel', label: 'Nivel', field: 'nivel', align: 'left' },
        { name: 'direccion', label: 'Dirección', field: 'direccion', align: 'left' },
        { name: 'telefono_contacto', label: 'Tel. contacto', field: 'telefono_contacto', align: 'left' },
        { name: 'responsable_laboratorio', label: 'Responsable lab.', field: 'responsable_laboratorio', align: 'left' },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'center' }
      ],
      filter: '',
      filterTipo: null,
      filterEstado: null,
      tipoOptions: [
        { label: 'Público', value: 'PUBLICO' },
        { label: 'Privado', value: 'PRIVADO' }
      ],
      estadoOptions: [
        { label: 'Activo', value: 'ACTIVO' },
        { label: 'Inactivo', value: 'INACTIVO' }
      ],
      nivelOptions: [
        { label: 'Nivel I', value: 'NIVEL I' },
        { label: 'Nivel II', value: 'NIVEL II' },
        { label: 'Nivel III', value: 'NIVEL III' }
      ],
      dialog: false,
      editando: false,
      loading: false,
      establecimiento: {},
      // lista de servicios para el select
      serviciosOptions: []
    };
  },
  mounted () {
    this.getEstablecimientos();
    this.getServicios();
  },
  methods: {
    servicioLabel (serv) {
      if (!serv) return '';
      const codigo = serv.codigo ? serv.codigo + ' - ' : '';
      return codigo + serv.nombre;
    },

    getEstablecimientos () {
      this.loading = true;
      this.$axios.get('establecimientos', {
        params: {
          q: this.filter || null,
          tipo: this.filterTipo || null,
          estado: this.filterEstado || null
        }
      })
        .then(res => {
          this.rows = res.data;
        })
        .finally(() => {
          this.loading = false;
        });
    },

    getServicios () {
      // si quieres solo activos, puedes pasar estado=ACTIVO
      this.$axios.get('servicios', { params: { estado: 'ACTIVO' } })
        .then(res => {
          this.serviciosOptions = res.data;
        });
    },

    nuevo () {
      this.establecimiento = {
        nombre: '',
        tipo: 'PUBLICO',
        nivel: 'NIVEL I',
        direccion: '',
        telefono_contacto: '',
        responsable_laboratorio: '',
        telefono_responsable: '',
        estado: 'ACTIVO',
        servicio_ids: [] // 👈 importante
      };
      this.editando = false;
      this.dialog = true;
    },

    editar (row) {
      this.establecimiento = {
        ...row,
        servicio_ids: (row.servicio_ids || []).slice()
      };
      this.editando = true;
      this.dialog = true;
    },

    guardar () {
      this.loading = true;
      const req = this.editando
        ? this.$axios.put(`establecimientos/${this.establecimiento.id}`, this.establecimiento)
        : this.$axios.post('establecimientos', this.establecimiento);

      req.then(() => {
        this.$alert && this.$alert.success
          ? this.$alert.success('Guardado correctamente')
          : console.log('Guardado correctamente');

        this.dialog = false;
        this.getEstablecimientos();
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
        this.$alert.dialog('¿Eliminar establecimiento?').onOk(() => {
          this.$axios.delete(`establecimientos/${id}`).then(() => {
            this.$alert.success('Eliminado');
            this.getEstablecimientos();
          });
        });
      } else {
        if (confirm('¿Eliminar establecimiento?')) {
          this.$axios.delete(`establecimientos/${id}`).then(() => {
            this.getEstablecimientos();
          });
        }
      }
    }
  }
};
</script>

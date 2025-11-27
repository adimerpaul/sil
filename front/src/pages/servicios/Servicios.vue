<template>
  <q-page class="q-pa-sm bg-grey-2">
    <div class="row q-col-gutter-sm">
      <!-- ÁREAS -->
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section class="row items-center q-pa-sm">
            <div class="text-subtitle2">Áreas</div>
            <q-space />
            <q-btn
              color="primary"
              icon="add"
              dense round
              size="sm"
              @click="nuevaArea"
            />
          </q-card-section>

          <q-separator />

          <q-card-section class="q-pa-xs">
            <q-input
              dense outlined
              v-model="searchArea"
              label="Buscar área"
              class="q-mb-xs"
            >
              <template #append><q-icon name="search" /></template>
            </q-input>

            <q-list dense bordered class="rounded-borders">
              <q-item
                v-for="a in filteredAreas"
                :key="a.id"
                clickable
                :active="a.id === selectedAreaId"
                active-class="bg-primary text-white"
                @click="selectArea(a)"
              >
                <q-item-section>
                  <q-item-label>{{ a.name }}</q-item-label>
<!--                  <q-item-label caption>{{ a.descripcion }}</q-item-label>-->
                </q-item-section>
                <q-item-section side>
                  <q-btn
                    flat dense round
                    icon="edit"
                    @click.stop="editarArea(a)"
                  />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <!-- SERVICIOS -->
      <div class="col-12 col-md-9">
        <q-card flat bordered>
          <q-card-section class="row items-center q-pa-sm">
            <div class="text-subtitle2">
              Prestaciones
              <span v-if="selectedArea">– {{ selectedArea.name }}</span>
            </div>
            <q-space />
            <q-input
              dense outlined
              v-model="searchServicio"
              label="Buscar prestaciones"
              class="q-mr-sm"
              style="max-width: 260px"
            >
              <template #append><q-icon name="search" /></template>
            </q-input>
            <q-btn
              color="positive"
              icon="add_circle_outline"
              label="Nuevo servicio"
              no-caps
              dense
              :disable="!selectedAreaId"
              :loading="loading"
              @click="nuevoServicio"
            />
          </q-card-section>

          <q-separator />

          <q-table
            dense flat bordered
            class="q-pa-xs"
            :rows="filteredServicios"
            :columns="columnsServicios"
            row-key="id"
            :rows-per-page-options="[0]"
          >
            <template #body-cell-actions="props">
              <q-td :props="props">
                <q-btn dense flat round icon="edit" @click="editarServicio(props.row)" />
<!--                <q-btn dense flat round icon="delete" color="negative" @click="eliminarServicio(props.row.id)" />-->
              </q-td>
            </template>
            <template #body-cell-nombre="props">
              <q-td :props="props">
<!--                <div style=" maxiswth line-hem 0.9-->
                <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                  {{ props.row.nombre }}
                </div>
                <div class="text-caption text-grey" v-html="props.row.descripcion || ''">
                </div>
              </q-td>
            </template>
          </q-table>
        </q-card>
      </div>
    </div>

    <!-- DIALOG ÁREA -->
    <q-dialog v-model="dialogArea">
      <q-card style="min-width: 300px; max-width: 420px;">
        <q-card-section class="row items-center q-pa-sm">
          <div class="text-subtitle1">
            {{ editandoArea ? 'Editar área' : 'Nueva área' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-sm">
          <q-form @submit="guardarArea">
            <q-input v-model="areaForm.name" label="Nombre" dense outlined class="q-mb-xs" />
            <q-input v-model="areaForm.descripcion" label="Descripción" dense outlined class="q-mb-xs" />
            <q-select
              v-model="areaForm.estado"
              :options="['ACTIVO','INACTIVO']"
              label="Estado"
              dense outlined
            />

            <div class="text-right q-mt-sm">
              <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
              <q-btn color="primary" label="Guardar" type="submit" :loading="loading" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- DIALOG SERVICIO -->
    <q-dialog v-model="dialogServicio">
      <q-card style="min-width: 400px; max-width: 600px;">
        <q-card-section class="row items-center q-pa-sm">
          <div class="text-subtitle1">
            {{ editandoServicio ? 'Editar servicio' : 'Nuevo servicio' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-sm">
          <q-form @submit="guardarServicio">
            <div class="row q-col-gutter-xs">
              <div class="col-4">
                <q-input v-model.number="servicioForm.codigo" type="number" label="Código" dense outlined />
              </div>
              <div class="col-8">
                <q-input v-model="servicioForm.nombre" label="Nombre" dense outlined />
              </div>
              <div class="col-4">
                <q-input v-model="servicioForm.metodo" label="Método" dense outlined />
              </div>
              <div class="col-4">
                <q-input v-model.number="servicioForm.precio" type="number" step="0.01" label="Precio" dense outlined />
              </div>
              <div class="col-4">
                <q-select
                  v-model="servicioForm.estado"
                  :options="['ACTIVO','INACTIVO']"
                  label="Estado"
                  dense outlined
                />
              </div>
            </div>

            <div class="text-right q-mt-sm">
              <q-btn flat label="Cancelar" v-close-popup :loading="loading" />
              <q-btn color="primary" label="Guardar" type="submit" :loading="loading" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'ServiciosConfigPage',
  data () {
    return {
      loading: false,

      // ÁREAS
      areas: [],
      selectedAreaId: null,
      selectedArea: null,
      searchArea: '',
      dialogArea: false,
      editandoArea: false,
      areaForm: {
        id: null,
        name: '',
        descripcion: '',
        estado: 'ACTIVO'
      },

      // SERVICIOS
      servicios: [],
      searchServicio: '',
      dialogServicio: false,
      editandoServicio: false,
      servicioForm: {
        id: null,
        area_id: null,
        codigo: null,
        nombre: '',
        metodo: '',
        precio: 0,
        estado: 'ACTIVO'
      },

      columnsServicios: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'codigo', label: 'Código', field: 'codigo', align: 'left' },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left' },
        { name: 'metodo', label: 'Método', field: 'metodo', align: 'left' },
        { name: 'subarea', label: 'Subárea', field: 'subarea', align: 'left' },
        {
          name: 'precio',
          label: 'Precio',
          field: 'precio',
          align: 'right',
          format: v => `Bs. ${Number(v || 0).toFixed(2)}`
        },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left' }
      ]
    };
  },
  computed: {
    filteredAreas () {
      const t = (this.searchArea || '').toLowerCase();
      if (!t) return this.areas;
      return this.areas.filter(a =>
        a.name.toLowerCase().includes(t) ||
        (a.descripcion || '').toLowerCase().includes(t)
      );
    },
    filteredServicios () {
      const t = (this.searchServicio || '').toLowerCase();
      let list = this.servicios;
      if (t) {
        list = list.filter(s =>
          (s.nombre || '').toLowerCase().includes(t) ||
          String(s.codigo || '').includes(t)
        );
      }
      return list;
    }
  },
  mounted () {
    this.loadAreas();
  },
  methods: {
    // --- ÁREAS ---
    loadAreas () {
      this.loading = true;
      this.$axios.get('areas')
        .then(res => {
          this.areas = res.data;
          if (!this.selectedAreaId && this.areas.length) {
            this.selectArea(this.areas[0]);
          }
        })
        .finally(() => { this.loading = false; });
    },
    selectArea (area) {
      this.selectedAreaId = area.id;
      this.selectedArea = area;
      this.loadServicios();
    },
    nuevaArea () {
      this.areaForm = {
        id: null,
        name: '',
        descripcion: '',
        estado: 'ACTIVO'
      };
      this.editandoArea = false;
      this.dialogArea = true;
    },
    editarArea (area) {
      this.areaForm = { ...area };
      this.editandoArea = true;
      this.dialogArea = true;
    },
    guardarArea () {
      this.loading = true;
      const req = this.editandoArea
        ? this.$axios.put(`areas/${this.areaForm.id}`, this.areaForm)
        : this.$axios.post('areas', this.areaForm);

      req.then(() => {
        this.$alert?.success?.('Área guardada');
        this.dialogArea = false;
        this.loadAreas();
      })
        .catch(e => {
          const msg = e.response?.data?.message || e.message;
          this.$alert?.error?.('Error: ' + msg);
        })
        .finally(() => { this.loading = false; });
    },

    // --- SERVICIOS ---
    loadServicios () {
      if (!this.selectedAreaId) return;
      this.loading = true;
      this.$axios.get('servicios', { params: { area_id: this.selectedAreaId } })
        .then(res => {
          this.servicios = res.data;
        })
        .finally(() => { this.loading = false; });
    },
    nuevoServicio () {
      this.servicioForm = {
        id: null,
        area_id: this.selectedAreaId,
        codigo: null,
        nombre: '',
        metodo: '',
        precio: 0,
        estado: 'ACTIVO'
      };
      this.editandoServicio = false;
      this.dialogServicio = true;
    },
    editarServicio (row) {
      this.servicioForm = { ...row };
      this.editandoServicio = true;
      this.dialogServicio = true;
    },
    guardarServicio () {
      this.loading = true;
      const payload = { ...this.servicioForm, area_id: this.selectedAreaId };

      const req = this.editandoServicio
        ? this.$axios.put(`servicios/${payload.id}`, payload)
        : this.$axios.post('servicios', payload);

      req.then(() => {
        this.$alert?.success?.('Servicio guardado');
        this.dialogServicio = false;
        this.loadServicios();
      })
        .catch(e => {
          const msg = e.response?.data?.message || e.message;
          this.$alert?.error?.('Error: ' + msg);
        })
        .finally(() => { this.loading = false; });
    },
    eliminarServicio (id) {
      if (this.$alert?.dialog) {
        this.$alert.dialog('¿Eliminar servicio?').onOk(() => {
          this.$axios.delete(`servicios/${id}`).then(() => {
            this.$alert.success('Eliminado');
            this.loadServicios();
          });
        });
      } else if (confirm('¿Eliminar servicio?')) {
        this.$axios.delete(`servicios/${id}`).then(() => {
          this.loadServicios();
        });
      }
    }
  }
};
</script>

<template>
  <q-page class="q-pa-sm bg-grey-2">
    <!-- HEADER -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row items-center q-col-gutter-sm">
        <div class="col-12 col-sm-6">
          <div class="text-h6">Formularios de Laboratorio</div>
          <div class="text-caption text-grey-7">
            Diseña y administra formularios por área (Hematología, Química, etc.)
          </div>
        </div>
        <div class="col-12 col-sm-3">
          <q-input
            v-model="filters.search"
            dense
            outlined
            debounce="400"
            label="Buscar (nombre / área)"
            @update:model-value="fetchFormularios"
          >
            <template #prepend>
              <q-icon name="search" />
            </template>
            <template #append>
              <q-btn
                v-if="filters.search"
                flat dense round icon="close"
                @click="clearSearch"
              />
            </template>
          </q-input>
        </div>
        <div class="col-12 col-sm-3 text-right">
<!--          actulizar-->
          <q-btn
            flat
            dense
            round
            icon="refresh"
            :loading="loading"
            @click="fetchFormularios"
            class="q-mr-sm"
          />
          <q-btn
            color="primary"
            icon="add"
            label="Nuevo formulario"
            no-caps
            @click="openDialogCreate"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- TABLA -->
    <q-card flat bordered>
      <q-card-section class="q-pa-none">
        <q-table
          :rows="rows"
          :columns="columns"
          row-key="id"
          :loading="loading"
          :pagination="pagination"
          @request="onRequest"
          flat
          dense
          wrap-cells
          separator="horizontal"
          class="no-border-radius"
          :rows-per-page-options="[0]"
        >
          <template #loading>
            <q-inner-loading showing>
              <q-spinner />
            </q-inner-loading>
          </template>

          <template #body-cell-area="props">
            <q-td :props="props">
              <q-chip
                size="sm"
                color="indigo-5"
                text-color="white"
                outline
              >
                {{ props.row.area?.name || 'Sin área' }}
              </q-chip>
            </q-td>
          </template>
<!--          template html-->
          <template #body-cell-html="props">
            <q-td :props="props">
              <div style="max-height: 100px; overflow: auto; border: 1px solid #e0e0e0; padding: 8px; border-radius: 4px; background-color: #f9f9f9;">
                <div v-html="props.row.html"></div>
              </div>
            </q-td>
          </template>

          <template #body-cell-actions="props">
            <q-td :props="props" auto-width>
              <q-btn-dropdown label="Opciones" color="primary" dense no-caps size="10px">
                <q-list>
                  <q-item clickable v-ripple @click="openDialogView(props.row)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="visibility" />
                    </q-item-section>
                    <q-item-section>Ver</q-item-section>
                  </q-item>

                  <q-item clickable v-ripple @click="openDialogEdit(props.row)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="edit" />
                    </q-item-section>
                    <q-item-section>Editar</q-item-section>
                  </q-item>

                  <q-item clickable v-ripple @click="confirmDelete(props.row)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="delete" />
                    </q-item-section>
                    <q-item-section>Eliminar</q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
<!--              <q-btn-->
<!--                dense round flat-->
<!--                icon="visibility"-->
<!--                @click="openDialogView(props.row)"-->
<!--              >-->
<!--                <q-tooltip>Ver</q-tooltip>-->
<!--              </q-btn>-->
<!--              <q-btn-->
<!--                dense round flat-->
<!--                icon="edit"-->
<!--                color="primary"-->
<!--                @click="openDialogEdit(props.row)"-->
<!--              >-->
<!--                <q-tooltip>Editar</q-tooltip>-->
<!--              </q-btn>-->
<!--              <q-btn-->
<!--                dense round flat-->
<!--                icon="delete"-->
<!--                color="negative"-->
<!--                @click="confirmDelete(props.row)"-->
<!--              >-->
<!--                <q-tooltip>Eliminar</q-tooltip>-->
<!--              </q-btn>-->
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <!-- DIALOGO CREAR/EDITAR -->
    <q-dialog v-model="dialog.visible" persistent >
      <q-card style="max-width: 1100px; width: 95vw;">
        <q-card-section class="row items-center q-pb-none">
          <div class="col">
            <div class="text-h6">
              {{ dialog.isEdit ? 'Editar formulario' : 'Nuevo formulario' }}
            </div>
            <div class="text-caption text-grey-7">
              Define el nombre, área y contenido HTML del formulario.
            </div>
          </div>
          <div class="col-auto">
            <q-btn icon="close" flat round dense v-close-popup />
          </div>
        </q-card-section>

        <q-separator spaced />

        <q-card-section class="q-pt-none">
          <q-form @submit.prevent="saveFormulario" class="column fit">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-input
                  v-model="form.nombre"
                  label="Nombre del formulario"
                  outlined
                  dense
                  :error="!!errors.nombre"
                  :error-message="errors.nombre"
                  autofocus
                />
              </div>
              <div class="col-12 col-md-6">
<!--                option-value="id"-->
<!--                option-label="nombre"-->
                <q-select
                  v-model="form.area_id"
                  :options="areasOptions"
                  option-label="name"
                  option-value="id"
                  emit-value
                  map-options
                  label="Área"
                  outlined
                  dense
                  :error="!!errors.area_id"
                  :error-message="errors.area_id"
                >
                  <template #prepend>
                    <q-icon name="science" />
                  </template>
                </q-select>
<!--                <pre>{{areasOptions}}</pre>-->
              </div>
            </div>

            <div class="q-mt-md column fit">
              <div class="text-subtitle2 q-mb-xs">
                Contenido (HTML) – Editor WYSIWYG
              </div>
              <div class="q-card q-pa-none bg-grey-1 fit" style="border-radius: 8px;">
                <q-editor
                  v-model="form.html"
                  :toolbar="editorToolbar"
                  :definitions="editorDefinitions"
                  placeholder="Escribe o pega aquí el HTML del formulario..."
                />
              </div>
              <div v-if="errors.html" class="text-negative text-caption q-mt-xs">
                {{ errors.html }}
              </div>
            </div>
          </q-form>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" v-close-popup no-caps />
          <q-btn
            color="primary"
            :label="dialog.isEdit ? 'Guardar cambios' : 'Crear formulario'"
            no-caps
            :loading="saving"
            @click="saveFormulario"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- DIALOGO VER SOLO LECTURA -->
    <q-dialog v-model="dialogView.visible" >
      <q-card style="max-width: 1100px; width: 95vw;">
        <q-card-section class="row items-center q-pb-none">
          <div class="col">
            <div class="text-h6">Vista previa – {{ dialogView.row?.nombre }}</div>
            <div class="text-caption text-grey-7">
              Área: {{ dialogView.row?.area?.nombre || 'Sin área' }}
            </div>
          </div>
          <div class="col-auto">
            <q-btn icon="close" flat round dense v-close-popup />
          </div>
        </q-card-section>

        <q-separator spaced />

        <q-card-section class="q-pt-none" style="">
          <div v-html="dialogView.row?.html"></div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { useQuasar } from 'quasar';

export default {
  name: 'FormulariosPage',
  data () {
    return {
      loading: false,
      saving: false,
      rows: [],
      areasOptions: [],
      filters: {
        search: '',
      },
      pagination: {
        page: 1,
        rowsPerPage: 10,
        rowsNumber: 0,
      },
      columns: [
        { name: 'actions', label: 'Acciones', field: 'id', align: 'right' },
        { name: 'id', label: 'ID', field: 'id', align: 'left', sortable: true },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left', sortable: true },
        {
          name: 'area',
          label: 'Área',
          field: row => row.area?.name,
          align: 'left',
          sortable: false
        },
        // html
        { name: 'html', label: 'Contenido HTML', field: 'html', align: 'left', sortable: false },
        // {
        //   name: 'updated_at',
        //   label: 'Actualizado',
        //   field: 'updated_at',
        //   align: 'left',
        //   sortable: true,
        //   format: val => (val ? val.substring(0, 10) : '')
        // },
      ],
      dialog: {
        visible: false,
        isEdit: false,
      },
      dialogView: {
        visible: false,
        row: null,
      },
      form: {
        id: null,
        nombre: '',
        area_id: null,
        html: '',
      },
      errors: {},
      editorToolbar: [
        ['undo', 'redo'],
        ['bold', 'italic', 'underline', 'strike'],
        ['quote', 'unordered', 'ordered'],
        ['link'],
        ['viewsource'],
        ['fullscreen']
      ],
      editorDefinitions: {
        viewsource: {
          icon: 'code',
          tip: 'Ver código fuente',
          handler: (ctx) => {
            ctx.toggleFullscreen();
          }
        }
      }
    };
  },
  mounted () {
    this.fetchAreas();
    this.fetchFormularios();
  },
  methods: {
    async fetchAreas () {
      try {
        // Ajusta la URL al endpoint simple de áreas que tengas
        const { data } = await this.$axios.get('/areas', { params: { all: 1 } });
        this.areasOptions = data;
      } catch (e) {
        console.error(e);
      }
    },

    async fetchFormularios () {
      this.loading = true;
      try {
        const { page, rowsPerPage } = this.pagination;
        const { data } = await this.$axios.get('/formularios', {
          params: {
            search: this.filters.search || undefined,
            per_page: rowsPerPage,
            page
          }
        });

        this.rows = data.data;
        this.pagination.rowsNumber = data.total;
        this.pagination.page = data.current_page;
        this.pagination.rowsPerPage = data.per_page;
      } catch (e) {
        console.error(e);
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar los formularios'
        });
      } finally {
        this.loading = false;
      }
    },

    onRequest (props) {
      if (props.pagination) {
        this.pagination = props.pagination;
        this.fetchFormularios();
      }
    },

    clearSearch () {
      this.filters.search = '';
      this.fetchFormularios();
    },

    resetForm () {
      this.form = {
        id: null,
        nombre: '',
        area_id: null,
        html: '',
      };
      this.errors = {};
    },

    openDialogCreate () {
      this.resetForm();
      this.dialog.isEdit = false;
      this.dialog.visible = true;
    },

    openDialogEdit (row) {
      this.resetForm();
      this.dialog.isEdit = true;
      this.form = {
        id: row.id,
        nombre: row.nombre,
        area_id: row.area_id || (row.area ? row.area.id : null),
        html: row.html || ''
      };
      this.dialog.visible = true;
    },

    openDialogView (row) {
      this.dialogView.row = row;
      this.dialogView.visible = true;
    },

    confirmDelete (row) {
      this.$q.dialog({
        title: 'Eliminar formulario',
        message: `¿Seguro que deseas eliminar el formulario "${row.nombre}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => {
        this.deleteFormulario(row.id);
      });
    },

    async deleteFormulario (id) {
      try {
        await this.$axios.delete(`/formularios/${id}`);
        this.$q.notify({ type: 'positive', message: 'Formulario eliminado' });
        this.fetchFormularios();
      } catch (e) {
        console.error(e);
        this.$q.notify({ type: 'negative', message: 'No se pudo eliminar el formulario' });
      }
    },

    async saveFormulario () {
      this.saving = true;
      this.errors = {};
      try {
        const payload = {
          nombre: this.form.nombre,
          area_id: this.form.area_id,
          html: this.form.html
        };

        if (this.dialog.isEdit && this.form.id) {
          const { data } = await this.$axios.put(`/formularios/${this.form.id}`, payload);
          this.$q.notify({ type: 'positive', message: 'Formulario actualizado' });
        } else {
          const { data } = await this.$axios.post('/formularios', payload);
          this.$q.notify({ type: 'positive', message: 'Formulario creado' });
        }

        this.dialog.visible = false;
        this.fetchFormularios();
      } catch (e) {
        if (e.response && e.response.status === 422) {
          this.errors = e.response.data.errors || {};
        }
        console.error(e);
        this.$q.notify({ type: 'negative', message: 'Error al guardar el formulario' });
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>

<template>
  <q-page class="q-pa-sm bg-grey-2">
    <q-card flat bordered>
      <q-card-section class="inventory-toolbar">
        <div>
          <div class="text-h6 text-weight-bold">Inventario</div>
          <div class="text-caption text-grey-7">Items del clasificador presupuestario</div>
        </div>
        <q-space />
        <q-btn-dropdown
          color="primary"
          icon="bar_chart"
          label="Reportes"
          no-caps
          dense
          :loading="!!reportLoading"
          :disable="!!reportLoading"
        >
          <q-list dense style="min-width: 190px">
            <q-item clickable v-close-popup :disable="!!reportLoading" @click="printReport(false)">
              <q-item-section avatar>
                <q-spinner v-if="reportLoading === 'all'" color="primary" size="20px" />
                <q-icon v-else name="print" />
              </q-item-section>
              <q-item-section>
                {{ reportLoading === 'all' ? 'Generando todo...' : 'Imprimir todo' }}
              </q-item-section>
            </q-item>
            <q-item clickable v-close-popup :disable="!!reportLoading" @click="printReport(true)">
              <q-item-section avatar>
                <q-spinner v-if="reportLoading === 'existing'" color="primary" size="20px" />
                <q-icon v-else name="inventory" />
              </q-item-section>
              <q-item-section>
                {{ reportLoading === 'existing' ? 'Generando existente...' : 'Imprimir existente' }}
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
        <q-btn color="indigo" icon="account_tree" label="Catalogo" no-caps dense @click="openCatalogManager" />
        <q-btn color="positive" icon="add_circle_outline" label="Nuevo item" no-caps dense @click="openItemDialog()" />
        <q-btn dense flat round icon="refresh" :loading="loading" @click="reloadAll">
          <q-tooltip>Actualizar</q-tooltip>
        </q-btn>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-sm">
        <div class="row q-col-gutter-sm">
          <div class="col-12 col-sm-6 col-md-3">
            <q-select
              v-model="filters.grupo_id"
              :options="grupoOptions"
              dense
              outlined
              clearable
              emit-value
              map-options
              label="Grupo"
              @update:model-value="onGrupoChange"
            />
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <q-select
              v-model="filters.partida_id"
              :options="partidaOptions"
              dense
              outlined
              clearable
              emit-value
              map-options
              label="Partida"
              @update:model-value="onPartidaChange"
            />
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <q-select
              v-model="filters.subpartida_id"
              :options="subpartidaOptions"
              dense
              outlined
              clearable
              emit-value
              map-options
              label="Subpartida"
              @update:model-value="resetAndFetchItems"
            />
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <q-input v-model="filters.q" dense outlined clearable label="Buscar" debounce="450" @update:model-value="resetAndFetchItems">
              <template #prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
        </div>
      </q-card-section>

      <q-card-section class="q-pa-sm q-pt-none">
        <div class="row q-col-gutter-sm">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="summary-card">
              <div class="text-caption text-grey-7">Grupo</div>
              <div class="summary-title">{{ selectedGrupoLabel }}</div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="summary-card">
              <div class="text-caption text-grey-7">Partida</div>
              <div class="summary-title">{{ selectedPartidaLabel }}</div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="summary-card">
              <div class="text-caption text-grey-7">Subpartida</div>
              <div class="summary-title">{{ selectedSubpartidaLabel }}</div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="summary-card summary-card--strong">
              <div class="row items-center no-wrap">
                <div>
                  <div class="text-caption text-grey-7">Items / Existencia</div>
                  <div class="summary-title">{{ summary.items }} items</div>
                </div>
                <q-space />
                <q-badge color="primary" class="text-bold">
                  {{ quantity(summary.cantidad) }}
                </q-badge>
              </div>
            </div>
          </div>
        </div>
      </q-card-section>

      <q-table
        v-model:pagination="pagination"
        dense
        flat
        bordered
        row-key="id"
        class="inventory-table"
        :rows="items"
        :columns="columns"
        :loading="loading"
        :rows-per-page-options="[10, 15, 25, 50]"
        binary-state-sort
        @request="onTableRequest"
      >
        <template #body-cell-imagen="props">
          <q-td :props="props">
            <q-avatar rounded size="42px" class="item-image">
              <q-img :src="itemImageUrl(props.row)" ratio="1" fit="cover" />
            </q-avatar>
          </q-td>
        </template>

        <template #body-cell-actions="props">
          <q-td :props="props">
            <q-btn-dropdown dense color="primary" label="Opciones" no-caps size="sm">
              <q-list dense style="min-width: 150px">
                <q-item clickable v-close-popup @click="openItemDialog(props.row)">
                  <q-item-section avatar><q-icon name="edit" /></q-item-section>
                  <q-item-section>Editar</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="deleteItem(props.row)">
                  <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                  <q-item-section class="text-negative">Eliminar</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </q-td>
        </template>

        <template #body-cell-clasificador="props">
          <q-td :props="props">
            <div class="text-weight-medium">
              {{ props.row.subpartida?.codigo }} - {{ props.row.subpartida?.nombre }}
            </div>
            <div class="text-caption text-grey-7 ellipsis">
              {{ props.row.subpartida?.partida?.codigo }} - {{ props.row.subpartida?.partida?.nombre }}
            </div>
            <div class="text-caption text-grey-6 ellipsis">
              {{ props.row.subpartida?.partida?.grupo?.codigo }} - {{ props.row.subpartida?.partida?.grupo?.nombre }}
            </div>
          </q-td>
        </template>

        <template #body-cell-nombre="props">
          <q-td :props="props">
            <div class="inventory-name">{{ props.row.nombre }}</div>
          </q-td>
        </template>

        <template #body-cell-precio_unitario="props">
          <q-td :props="props">
            <q-badge color="blue-1" text-color="primary">{{ money(props.row.precio_unitario) }}</q-badge>
          </q-td>
        </template>

        <template #body-cell-cantidad="props">
          <q-td :props="props">
            <q-badge
              :color="Number(props.row.cantidad || 0) > 0 ? 'green-1' : 'grey-3'"
              :text-color="Number(props.row.cantidad || 0) > 0 ? 'green-9' : 'grey-7'"
              class="text-bold"
            >
              {{ quantity(props.row.cantidad) }}
            </q-badge>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <q-dialog v-model="catalogManagerDialog" persistent maximized>
      <q-card>
        <q-card-section class="row items-center q-py-sm">
          <div>
            <div class="text-subtitle1 text-weight-bold">Catalogo presupuestario</div>
            <div class="text-caption text-grey-7">Grupos, partidas y subpartidas</div>
          </div>
          <q-space />
          <q-btn dense flat round icon="close" @click="catalogManagerDialog = false" />
        </q-card-section>
        <q-separator />

        <q-card-section class="q-pa-sm">
          <div class="row q-col-gutter-sm">
            <div class="col-12 col-md-3">
              <q-btn-toggle
                v-model="catalogTab"
                spread
                dense
                unelevated
                toggle-color="primary"
                :options="[
                  { label: 'Grupos', value: 'grupos' },
                  { label: 'Partidas', value: 'partidas' },
                  { label: 'Subpartidas', value: 'subpartidas' },
                ]"
              />
            </div>
            <div class="col-12 col-md">
              <q-input v-model="catalogFilter" dense outlined clearable label="Buscar catalogo">
                <template #prepend><q-icon name="search" /></template>
              </q-input>
            </div>
            <div class="col-12 col-md-auto">
              <q-btn color="positive" icon="add" label="Nuevo" no-caps dense @click="openCatalogForm()" />
            </div>
          </div>
        </q-card-section>

        <q-table
          dense
          flat
          bordered
          row-key="id"
          :rows="filteredCatalogRows"
          :columns="catalogColumns"
          :rows-per-page-options="[15, 25, 50, 0]"
        >
          <template #body-cell-actions="props">
            <q-td :props="props">
              <q-btn-dropdown dense color="primary" label="Opciones" no-caps size="sm">
                <q-list dense style="min-width: 150px">
                  <q-item clickable v-close-popup @click="openCatalogForm(props.row)">
                    <q-item-section avatar><q-icon name="edit" /></q-item-section>
                    <q-item-section>Editar</q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="deleteCatalog(props.row)">
                    <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                    <q-item-section class="text-negative">Eliminar</q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>
          <template #body-cell-padre="props">
            <q-td :props="props">{{ parentLabel(props.row) }}</q-td>
          </template>
        </q-table>
      </q-card>
    </q-dialog>

    <q-dialog v-model="catalogFormDialog" persistent>
      <q-card style="width: min(92vw, 460px)">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1 text-weight-bold">
            {{ catalogForm.id ? 'Editar' : 'Nuevo' }} {{ catalogSingular }}
          </div>
          <q-space />
          <q-btn dense flat round icon="close" @click="catalogFormDialog = false" />
        </q-card-section>
        <q-card-section>
          <q-form @submit="saveCatalog">
            <q-select
              v-if="catalogTab === 'partidas'"
              v-model="catalogForm.grupo_id"
              :options="grupoOptions"
              dense
              outlined
              emit-value
              map-options
              label="Grupo"
              :rules="[val => !!val || 'Campo requerido']"
            />
            <q-select
              v-if="catalogTab === 'subpartidas'"
              v-model="catalogForm.partida_id"
              :options="allPartidaOptions"
              dense
              outlined
              emit-value
              map-options
              label="Partida"
              :rules="[val => !!val || 'Campo requerido']"
            />
            <q-input v-model.number="catalogForm.num" dense outlined type="number" label="Num" :rules="[val => val !== null && val !== '' || 'Campo requerido']" />
            <q-input v-model="catalogForm.codigo" dense outlined label="Codigo" :rules="[val => !!val || 'Campo requerido']" />
            <q-input v-model="catalogForm.nombre" dense outlined label="Nombre" :rules="[val => !!val || 'Campo requerido']" />
            <div class="text-right q-mt-sm">
              <q-btn flat color="negative" label="Cancelar" no-caps @click="catalogFormDialog = false" />
              <q-btn color="primary" label="Guardar" no-caps type="submit" :loading="savingCatalog" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="itemDialog" persistent>
      <q-card style="width: min(92vw, 560px)">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1 text-weight-bold">
            {{ itemForm.id ? 'Editar item' : 'Nuevo item' }}
          </div>
          <q-space />
          <q-btn dense flat round icon="close" @click="itemDialog = false" />
        </q-card-section>
        <q-card-section>
          <q-form @submit="saveItem">
            <q-select
              v-model="itemGrupoId"
              :options="itemGrupoOptions"
              dense
              outlined
              clearable
              emit-value
              map-options
              use-input
              input-debounce="0"
              label="Grupo"
              @filter="filterGrupoOptions"
              @update:model-value="onItemGrupoChange"
            />
            <q-select
              v-model="itemPartidaId"
              :options="itemPartidaOptions"
              dense
              outlined
              clearable
              emit-value
              map-options
              use-input
              input-debounce="0"
              label="Partida"
              @filter="filterItemPartidaOptions"
              @update:model-value="onItemPartidaChange"
            />
            <q-select
              v-model="itemForm.subpartida_id"
              :options="itemSubpartidaOptions"
              dense
              outlined
              emit-value
              map-options
              use-input
              input-debounce="0"
              label="Subpartida"
              @filter="filterItemSubpartidaOptions"
              :rules="[val => !!val || 'Campo requerido']"
            />
            <q-input v-model="itemForm.nombre" dense outlined label="Nombre" :rules="[val => !!val || 'Campo requerido']" />
            <q-input v-model="itemForm.unidad_medida" dense outlined label="Unidad de medida" />
            <q-input v-model.number="itemForm.precio_unitario" dense outlined type="number" step="0.01" label="Precio unitario" />
            <div class="row q-col-gutter-sm items-center">
              <div class="col-auto">
                <q-avatar rounded size="74px" class="item-image-preview">
                  <q-img :src="itemPreviewUrl" ratio="1" fit="cover" />
                </q-avatar>
              </div>
              <div class="col">
                <q-file
                  v-model="itemImageFile"
                  dense
                  outlined
                  clearable
                  accept="image/*"
                  label="Imagen producto"
                  @update:model-value="onItemImageChange"
                >
                  <template #prepend>
                    <q-icon name="image" />
                  </template>
                </q-file>
              </div>
            </div>
            <div
              class="item-image-dropzone q-mt-sm"
              :class="{ 'item-image-dropzone--over': draggingItemImage }"
              ref="itemImageDropzone"
              tabindex="0"
              @click="focusItemImageDropzone"
              @dragover.prevent="onItemImageDragOver"
              @dragleave.prevent="onItemImageDragLeave"
              @drop.prevent="onItemImageDrop"
              @paste="onItemImagePaste"
            >
              <input
                ref="itemImageInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onItemImageInputChange"
              >
              <div class="column items-center justify-center full-height">
                <q-icon name="add_photo_alternate" size="34px" color="grey-7" />
                <div class="text-caption text-grey-7 q-mt-xs">
                  Cambiar foto: arrastra o pega (Ctrl+V)
                </div>
                <q-btn
                  class="q-mt-sm"
                  dense
                  outline
                  no-caps
                  color="primary"
                  icon="upload"
                  label="Seleccionar"
                  @click.stop="pickItemImageFile"
                />
              </div>
            </div>
            <div class="text-right q-mt-sm">
              <q-btn flat color="negative" label="Cancelar" no-caps @click="itemDialog = false" />
              <q-btn color="primary" label="Guardar" no-caps type="submit" :loading="savingItem" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'InventarioPage',
  data () {
    return {
      loading: false,
      reportLoading: null,
      savingCatalog: false,
      savingItem: false,
      items: [],
      grupos: [],
      grupoOptionsFiltered: [],
      itemPartidaOptionsFiltered: [],
      itemSubpartidaOptionsFiltered: [],
      itemGrupoId: null,
      itemPartidaId: null,
      itemImageFile: null,
      itemPreviewUrl: '',
      draggingItemImage: false,
      summary: {
        items: 0,
        cantidad: 0,
      },
      catalogManagerDialog: false,
      catalogFormDialog: false,
      itemDialog: false,
      catalogTab: 'grupos',
      catalogFilter: '',
      catalogForm: {},
      itemForm: {},
      filters: {
        grupo_id: null,
        partida_id: null,
        subpartida_id: null,
        q: '',
      },
      pagination: {
        sortBy: 'nombre',
        descending: false,
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0,
      },
      columns: [
        { name: 'actions', label: 'Acciones', field: 'id', align: 'left' },
        { name: 'imagen', label: 'Imagen', field: 'imagen', align: 'left' },
        { name: 'nombre', label: 'Item', field: 'nombre', align: 'left', sortable: true },
        { name: 'unidad_medida', label: 'Unidad', field: 'unidad_medida', align: 'left', sortable: true },
        { name: 'cantidad', label: 'Cantidad', field: 'cantidad', align: 'right', sortable: true },
        { name: 'precio_unitario', label: 'P.U.', field: 'precio_unitario', align: 'right', sortable: true },
        { name: 'clasificador', label: 'Clasificador', field: row => row.subpartida?.codigo || '', align: 'left' },
      ],
    }
  },
  computed: {
    grupoOptions () {
      return this.grupos.map(g => ({ label: `${g.codigo} - ${g.nombre}`, value: g.id }))
    },
    allPartidas () {
      return this.grupos.flatMap(g => (g.partidas || []).map(p => ({ ...p, grupo: g })))
    },
    allSubpartidas () {
      return this.allPartidas.flatMap(p => (p.subpartidas || []).map(s => ({ ...s, partida: p })))
    },
    partidaOptions () {
      return this.allPartidas
        .filter(p => !this.filters.grupo_id || p.grupo_id === this.filters.grupo_id)
        .map(p => ({ label: `${p.codigo} - ${p.nombre}`, value: p.id }))
    },
    allPartidaOptions () {
      return this.allPartidas.map(p => ({ label: `${p.codigo} - ${p.nombre}`, value: p.id }))
    },
    subpartidaOptions () {
      return this.allSubpartidas
        .filter(s => !this.filters.partida_id || s.partida_id === this.filters.partida_id)
        .map(s => ({ label: `${s.codigo} - ${s.nombre}`, value: s.id }))
    },
    allSubpartidaOptions () {
      return this.allSubpartidas.map(s => ({ label: `${s.codigo} - ${s.nombre}`, value: s.id }))
    },
    itemGrupoOptions () {
      return this.grupoOptionsFiltered.length ? this.grupoOptionsFiltered : this.grupoOptions
    },
    itemPartidaBaseOptions () {
      return this.allPartidas
        .filter(p => !this.itemGrupoId || p.grupo_id === this.itemGrupoId)
        .map(p => ({ label: `${p.codigo} - ${p.nombre}`, value: p.id }))
    },
    itemPartidaOptions () {
      return this.itemPartidaOptionsFiltered.length ? this.itemPartidaOptionsFiltered : this.itemPartidaBaseOptions
    },
    itemSubpartidaBaseOptions () {
      return this.allSubpartidas
        .filter(s => !this.itemPartidaId || s.partida_id === this.itemPartidaId)
        .map(s => ({ label: `${s.codigo} - ${s.nombre}`, value: s.id }))
    },
    itemSubpartidaOptions () {
      return this.itemSubpartidaOptionsFiltered.length ? this.itemSubpartidaOptionsFiltered : this.itemSubpartidaBaseOptions
    },
    selectedGrupoLabel () {
      const grupo = this.grupos.find(g => g.id === this.filters.grupo_id)
      return grupo ? `${grupo.codigo} - ${grupo.nombre}` : 'Todos'
    },
    selectedPartidaLabel () {
      const partida = this.allPartidas.find(p => p.id === this.filters.partida_id)
      return partida ? `${partida.codigo} - ${partida.nombre}` : 'Todas'
    },
    selectedSubpartidaLabel () {
      const subpartida = this.allSubpartidas.find(s => s.id === this.filters.subpartida_id)
      return subpartida ? `${subpartida.codigo} - ${subpartida.nombre}` : 'Todas'
    },
    catalogRows () {
      if (this.catalogTab === 'grupos') return this.grupos
      if (this.catalogTab === 'partidas') return this.allPartidas
      return this.allSubpartidas
    },
    filteredCatalogRows () {
      const q = (this.catalogFilter || '').toLowerCase().trim()
      if (!q) return this.catalogRows
      return this.catalogRows.filter(row => {
        return [row.codigo, row.nombre, this.parentLabel(row)]
          .filter(Boolean)
          .some(value => String(value).toLowerCase().includes(q))
      })
    },
    catalogColumns () {
      const columns = [
        { name: 'actions', label: 'Acciones', field: 'id', align: 'left' },
        { name: 'codigo', label: 'Codigo', field: 'codigo', align: 'left', sortable: true },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left', sortable: true },
      ]
      if (this.catalogTab !== 'grupos') columns.push({ name: 'padre', label: 'Padre', field: 'id', align: 'left' })
      return columns
    },
    catalogSingular () {
      if (this.catalogTab === 'grupos') return 'grupo'
      if (this.catalogTab === 'partidas') return 'partida'
      return 'subpartida'
    },
    catalogEndpoint () {
      if (this.catalogTab === 'grupos') return 'grupos'
      if (this.catalogTab === 'partidas') return 'partidas'
      return 'subpartidas'
    },
  },
  mounted () {
    this.reloadAll()
  },
  methods: {
    async reloadAll () {
      await this.fetchCatalog()
      await this.fetchItems()
    },
    async fetchCatalog () {
      const res = await this.$axios.get('grupos', { params: { with_partidas: 1 } })
      this.grupos = res.data || []
    },
    async fetchItems () {
      this.loading = true
      try {
        const res = await this.$axios.get('almacen-items', {
          params: {
            page: this.pagination.page,
            rowsPerPage: this.pagination.rowsPerPage,
            sortBy: this.pagination.sortBy,
            descending: this.pagination.descending,
            ...this.filters,
          },
        })
        this.items = res.data.data || []
        this.pagination.rowsNumber = res.data.total || 0
        this.summary = res.data.summary || { items: this.pagination.rowsNumber, cantidad: 0 }
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo cargar inventario')
      } finally {
        this.loading = false
      }
    },
    onTableRequest (props) {
      this.pagination = props.pagination
      this.fetchItems()
    },
    resetAndFetchItems () {
      this.pagination.page = 1
      this.fetchItems()
    },
    onGrupoChange () {
      this.filters.partida_id = null
      this.filters.subpartida_id = null
      this.resetAndFetchItems()
    },
    onPartidaChange () {
      this.filters.subpartida_id = null
      this.resetAndFetchItems()
    },
    openCatalogManager () {
      this.catalogManagerDialog = true
    },
    openCatalogForm (row = null) {
      this.catalogForm = row
        ? { ...row }
        : { num: null, codigo: '', nombre: '', grupo_id: this.filters.grupo_id, partida_id: this.filters.partida_id }
      this.catalogFormDialog = true
    },
    async saveCatalog () {
      this.savingCatalog = true
      try {
        const payload = {
          num: this.catalogForm.num,
          codigo: this.catalogForm.codigo,
          nombre: this.catalogForm.nombre,
        }
        if (this.catalogTab === 'partidas') payload.grupo_id = this.catalogForm.grupo_id
        if (this.catalogTab === 'subpartidas') payload.partida_id = this.catalogForm.partida_id
        if (this.catalogForm.id) {
          await this.$axios.put(`${this.catalogEndpoint}/${this.catalogForm.id}`, payload)
        } else {
          await this.$axios.post(this.catalogEndpoint, payload)
        }
        this.catalogFormDialog = false
        this.$alert.success('Catalogo actualizado')
        await this.reloadAll()
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.savingCatalog = false
      }
    },
    deleteCatalog (row) {
      this.$alert.dialog(`Desea eliminar ${row.codigo} - ${row.nombre}?`).onOk(async () => {
        try {
          await this.$axios.delete(`${this.catalogEndpoint}/${row.id}`)
          this.$alert.success('Registro eliminado')
          await this.reloadAll()
        } catch (e) {
          this.$alert.error(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    },
    openItemDialog (row = null) {
      const subpartida = row?.subpartida_id
        ? this.allSubpartidas.find(s => s.id === row.subpartida_id)
        : this.allSubpartidas.find(s => s.id === this.filters.subpartida_id)
      const partida = subpartida ? this.allPartidas.find(p => p.id === subpartida.partida_id) : null
      this.itemGrupoId = partida?.grupo_id || this.filters.grupo_id || null
      this.itemPartidaId = subpartida?.partida_id || this.filters.partida_id || null
      this.itemPartidaOptionsFiltered = []
      this.itemSubpartidaOptionsFiltered = []
      this.itemImageFile = null
      this.itemPreviewUrl = this.itemImageUrl(row || {})
      this.itemForm = row
        ? { ...row }
        : {
            subpartida_id: this.filters.subpartida_id,
            nombre: '',
            unidad_medida: '',
            precio_unitario: 0,
          }
      this.itemDialog = true
    },
    async saveItem () {
      this.savingItem = true
      try {
        const payload = this.itemPayload()
        if (this.itemForm.id) {
          if (payload instanceof FormData) {
            await this.$axios.post(`almacen-items/${this.itemForm.id}`, payload)
          } else {
            await this.$axios.put(`almacen-items/${this.itemForm.id}`, payload)
          }
        } else {
          await this.$axios.post('almacen-items', payload)
        }
        this.itemDialog = false
        this.$alert.success('Item guardado')
        await this.fetchItems()
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.savingItem = false
      }
    },
    itemPayload () {
      if (!this.itemImageFile) {
        return {
          subpartida_id: this.itemForm.subpartida_id,
          nombre: this.itemForm.nombre,
          unidad_medida: this.itemForm.unidad_medida,
          precio_unitario: this.itemForm.precio_unitario,
        }
      }

      const form = new FormData()
      form.append('subpartida_id', this.itemForm.subpartida_id || '')
      form.append('nombre', this.itemForm.nombre || '')
      form.append('unidad_medida', this.itemForm.unidad_medida || '')
      form.append('precio_unitario', this.itemForm.precio_unitario || 0)
      form.append('imagen', this.itemImageFile)
      if (this.itemForm.id) form.append('_method', 'PUT')
      return form
    },
    onItemImageChange (file) {
      if (this.itemPreviewUrl && this.itemPreviewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(this.itemPreviewUrl)
      }
      this.itemPreviewUrl = file ? URL.createObjectURL(file) : this.itemImageUrl(this.itemForm)
    },
    pickItemImageFile () {
      this.$refs.itemImageInput?.click()
    },
    focusItemImageDropzone () {
      this.$refs.itemImageDropzone?.focus?.()
    },
    onItemImageInputChange (e) {
      const file = e?.target?.files?.[0]
      if (!file) return
      this.itemImageFile = file
      this.onItemImageChange(file)
      e.target.value = ''
    },
    onItemImageDragOver () {
      this.draggingItemImage = true
    },
    onItemImageDragLeave () {
      this.draggingItemImage = false
    },
    onItemImageDrop (e) {
      this.draggingItemImage = false
      const file = e?.dataTransfer?.files?.[0]
      if (!file) return
      if (!String(file.type || '').startsWith('image/')) {
        this.$alert?.error && this.$alert.error('Solo se permiten imágenes')
        return
      }
      this.itemImageFile = file
      this.onItemImageChange(file)
    },
    async onItemImagePaste (e) {
      try {
        const items = Array.from(e?.clipboardData?.items || [])
        const imageItem = items.find(it => String(it.type || '').startsWith('image/'))
        if (imageItem) {
          const file = imageItem.getAsFile()
          if (!file) return
          this.itemImageFile = file
          this.onItemImageChange(file)
          return
        }

        const text = (e?.clipboardData?.getData('text/plain') || '').trim()
        if (/^https?:\/\//i.test(text)) {
          const res = await fetch(text)
          const blob = await res.blob()
          if (!String(blob.type || '').startsWith('image/')) {
            this.$alert?.error && this.$alert.error('El enlace pegado no es una imagen')
            return
          }
          const ext = (blob.type || 'image/png').split('/')[1] || 'png'
          const file = new File([blob], `imagen.${ext}`, { type: blob.type || 'image/png' })
          this.itemImageFile = file
          this.onItemImageChange(file)
          return
        }

        this.$alert?.error && this.$alert.error('Pega una imagen (copiada) o un enlace directo a imagen')
      } catch (err) {
        console.log(err)
        this.$alert?.error && this.$alert.error('No se pudo pegar la imagen (puede ser por permisos del navegador)')
      }
    },
    itemImageUrl (row) {
      return `${this.$url}/../images/productos/${row?.imagen || 'default.png'}`
    },
    deleteItem (row) {
      this.$alert.dialog(`Desea eliminar ${row.nombre}?`).onOk(async () => {
        try {
          await this.$axios.delete(`almacen-items/${row.id}`)
          this.$alert.success('Item eliminado')
          await this.fetchItems()
        } catch (e) {
          this.$alert.error(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    },
    async printReport (existente) {
      this.reportLoading = existente ? 'existing' : 'all'
      try {
        const res = await this.$axios.get('almacen-items/reporte/pdf', {
          params: {
            existente: existente ? 1 : 0,
            ...this.filters,
          },
          responseType: 'blob',
        })
        const blob = new Blob([res.data], { type: 'application/pdf' })
        const url = window.URL.createObjectURL(blob)
        window.open(url, '_blank')
        window.setTimeout(() => window.URL.revokeObjectURL(url), 60000)
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo generar el reporte')
      } finally {
        this.reportLoading = null
      }
    },
    onItemGrupoChange () {
      this.itemPartidaId = null
      this.itemForm.subpartida_id = null
      this.itemPartidaOptionsFiltered = []
      this.itemSubpartidaOptionsFiltered = []
    },
    onItemPartidaChange () {
      this.itemForm.subpartida_id = null
      this.itemSubpartidaOptionsFiltered = []
    },
    filterGrupoOptions (value, update) {
      update(() => {
        this.grupoOptionsFiltered = this.filterOptions(this.grupoOptions, value)
      })
    },
    filterItemPartidaOptions (value, update) {
      update(() => {
        this.itemPartidaOptionsFiltered = this.filterOptions(this.itemPartidaBaseOptions, value)
      })
    },
    filterItemSubpartidaOptions (value, update) {
      update(() => {
        this.itemSubpartidaOptionsFiltered = this.filterOptions(this.itemSubpartidaBaseOptions, value)
      })
    },
    filterOptions (options, value) {
      const text = (value || '').toLowerCase()
      if (!text) return options
      return options.filter(option => option.label.toLowerCase().includes(text))
    },
    parentLabel (row) {
      if (this.catalogTab === 'partidas') return row.grupo ? `${row.grupo.codigo} - ${row.grupo.nombre}` : ''
      if (this.catalogTab === 'subpartidas') return row.partida ? `${row.partida.codigo} - ${row.partida.nombre}` : ''
      return ''
    },
    money (value) {
      const number = Number(value || 0)
      return number.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    quantity (value) {
      const number = Number(value || 0)
      return number.toLocaleString('es-BO', { maximumFractionDigits: 2 })
    },
  },
}
</script>

<style scoped>
.inventory-toolbar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  flex-wrap: wrap;
}

.inventory-table {
  min-height: 560px;
}

.inventory-name {
  max-width: 520px;
  white-space: normal;
  line-height: 1.2;
}

.item-image,
.item-image-preview {
  border: 1px solid #d9e2ec;
  background: #f5f8fb;
}

.item-image-dropzone {
  height: 92px;
  border: 2px dashed rgba(0, 0, 0, 0.18);
  border-radius: 10px;
  background: white;
  cursor: pointer;
  outline: none;
}

.item-image-dropzone--over {
  border-color: #1976d2;
  background: #e3f2fd;
}
</style>

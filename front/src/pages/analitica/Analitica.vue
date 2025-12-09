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
      </q-card-section>
    </q-card>

    <!-- TABLA -->
    <q-card flat bordered>
<!--      <q-table-->
<!--        ref="tableAnalitica"-->
<!--        :rows="rows"-->
<!--        :columns="columns"-->
<!--        row-key="id"-->
<!--        dense-->
<!--        flat-->
<!--        bordered-->
<!--        :loading="loading"-->
<!--        :pagination.sync="pagination"-->
<!--        :rows-per-page-options="[10, 20, 50]"-->
<!--        @request="onRequest"-->
<!--        @rowClick="goToDetalle"-->
<!--      >-->
<!--        <template #top>-->
<!--          <div class="row items-center full-width q-pa-xs">-->
<!--            <div class="col">-->
<!--              <div class="text-subtitle1">Prestaciones</div>-->
<!--              <div class="text-caption text-grey-7">-->
<!--                Mostrando solicitudes con estado <b>ENVIADO_ANALITICA</b>-->
<!--              </div>-->
<!--            </div>-->
<!--          </div>-->
<!--        </template>-->

<!--        &lt;!&ndash; PACIENTE &ndash;&gt;-->
<!--        <template #body-cell-paciente="props">-->
<!--          <q-td :props="props">-->
<!--            <div class="text-weight-medium">-->
<!--              {{ props.row.paciente_nombre || props.row.paciente?.nombre_completo }}-->
<!--            </div>-->
<!--            <div class="text-caption text-grey-7">-->
<!--              CI: {{ props.row.paciente_ci || props.row.paciente?.ci || '-' }}-->
<!--            </div>-->
<!--          </q-td>-->
<!--        </template>-->

<!--        &lt;!&ndash; ESTABLECIMIENTO &ndash;&gt;-->
<!--        <template #body-cell-establecimiento="props">-->
<!--          <q-td :props="props">-->
<!--            <div>{{ props.row.establecimiento_salud || '-' }}</div>-->
<!--          </q-td>-->
<!--        </template>-->

<!--        &lt;!&ndash; TIPO ATENCIÓN &ndash;&gt;-->
<!--        <template #body-cell-tipo_atencion="props">-->
<!--          <q-td :props="props">-->
<!--            <q-chip-->
<!--              dense-->
<!--              :color="props.row.tipo_atencion === 'SI' ? 'green-6' : 'orange-6'"-->
<!--              text-color="white"-->
<!--            >-->
<!--              {{-->
<!--                props.row.tipo_atencion === 'SI'-->
<!--                  ? 'SUS SI'-->
<!--                  : props.row.tipo_otro || 'SUS NO'-->
<!--              }}-->
<!--            </q-chip>-->
<!--          </q-td>-->
<!--        </template>-->

<!--        &lt;!&ndash; ESTADO &ndash;&gt;-->
<!--        <template #body-cell-estado="props">-->
<!--          <q-td :props="props">-->
<!--            <q-chip-->
<!--              dense-->
<!--              :color="props.row.estado === 'ENVIADO_ANALITICA' ? 'purple-6' : 'grey-6'"-->
<!--              text-color="white"-->
<!--              icon="local_shipping"-->
<!--            >-->
<!--              {{ props.row.estado }}-->
<!--            </q-chip>-->
<!--          </q-td>-->
<!--        </template>-->

<!--        &lt;!&ndash; CÓDIGO &ndash;&gt;-->
<!--        <template #body-cell-codigo="props">-->
<!--          <q-td :props="props">-->
<!--            <div v-if="props.row.codigo">-->
<!--              <span class="text-bold">-->
<!--                {{ props.row.codigo }} - {{ props.row.nro_registro }}-->
<!--              </span>-->
<!--            </div>-->
<!--            <div v-else class="text-negative text-caption">-->
<!--              Sin código-->
<!--            </div>-->
<!--          </q-td>-->
<!--        </template>-->

<!--        &lt;!&ndash; # SERVICIOS &ndash;&gt;-->
<!--        <template #body-cell-servicios_count="props">-->
<!--          <q-td :props="props" class="text-center">-->
<!--            <q-badge color="primary" :label="props.row.servicios?.length || 0" />-->
<!--          </q-td>-->
<!--        </template>-->

<!--        &lt;!&ndash; LISTA SERVICIOS &ndash;&gt;-->
<!--        <template #body-cell-servicios="props">-->
<!--          <q-td :props="props">-->
<!--            <ul class="q-pa-none q-ma-none">-->
<!--              <li v-for="servicio in props.row.servicios" :key="servicio.id">-->
<!--                {{ textCapitalize(servicio.nombre) }}-->
<!--              </li>-->
<!--            </ul>-->
<!--          </q-td>-->
<!--        </template>-->

<!--        &lt;!&ndash; RESPONSABLES &ndash;&gt;-->
<!--        <template #body-cell-responsables="props">-->
<!--          <q-td :props="props">-->
<!--            <div class="text-caption">-->
<!--              <span class="text-grey-7">Preanalítica:</span>-->
<!--              <b>{{ props.row.user_preanalitica?.name || 'No asignado' }}</b>-->
<!--            </div>-->
<!--            <div class="text-caption q-mt-xs">-->
<!--              <span class="text-grey-7">Analítica:</span>-->
<!--              <b>{{ props.row.user_analitica?.name || 'No asignado' }}</b>-->
<!--            </div>-->
<!--          </q-td>-->
<!--        </template>-->

<!--        &lt;!&ndash; FOOTER &ndash;&gt;-->
<!--        <template #bottom="scope">-->
<!--          <div-->
<!--            class="row items-center justify-between full-width q-px-sm q-py-xs"-->
<!--          >-->
<!--            <div class="col-12 col-sm-4 text-caption q-mb-xs q-mb-sm-none">-->
<!--              Mostrando-->
<!--              <b>{{ firstRowIndex(scope.pagination) }} - {{ lastRowIndex(scope.pagination) }}</b>-->
<!--              de-->
<!--              <b>{{ scope.pagination.rowsNumber }}</b>-->
<!--              Prestaciones-->
<!--            </div>-->

<!--            <div class="col-12 col-sm-8">-->
<!--              <div class="row items-center justify-end q-gutter-sm">-->
<!--                <div class="col-auto">-->
<!--                  <q-select-->
<!--                    v-model="pagination.rowsPerPage"-->
<!--                    :options="[10, 20, 50]"-->
<!--                    dense-->
<!--                    outlined-->
<!--                    options-dense-->
<!--                    style="width: 90px"-->
<!--                    label="Filas"-->
<!--                    @update:model-value="onChangeRowsPerPage"-->
<!--                  />-->
<!--                </div>-->
<!--                <div class="col-auto">-->
<!--                  <q-pagination-->
<!--                    v-model="pagination.page"-->
<!--                    :max="pagesNumber"-->
<!--                    max-pages="7"-->
<!--                    boundary-links-->
<!--                    direction-links-->
<!--                    icon-first="first_page"-->
<!--                    icon-last="last_page"-->
<!--                    icon-prev="chevron_left"-->
<!--                    icon-next="chevron_right"-->
<!--                    size="sm"-->
<!--                    @update:model-value="onChangePage"-->
<!--                  />-->
<!--                </div>-->
<!--              </div>-->
<!--            </div>-->
<!--          </div>-->
<!--        </template>-->
<!--      </q-table>-->
      <pre>{{}}</pre>
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
      rows: [],
      loading: false,
      filter: '',
    }
  },
  computed: {
  },
  mounted () {
    this.analiticaGet()
  },
  methods: {
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
        this.rows = response.data
      } catch (error) {
        this.$alert.error('Error al cargar las solicitudes de analítica.')
      } finally {
        this.loading = false
      }
    },
  }
}
</script>

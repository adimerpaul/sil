<template>
  <q-layout view="lHh Lpr lFf">
    <!-- HEADER -->
    <q-header class="bg-white text-black" bordered>
      <q-toolbar>
        <q-btn
          flat
          color="primary"
          :icon="leftDrawerOpen ? 'keyboard_double_arrow_left' : 'keyboard_double_arrow_right'"
          aria-label="Menu"
          @click="toggleLeftDrawer"
          unelevated
          dense
        />
        <div class="row items-center q-gutter-sm">
          <!--          <q-badge color="green-8" text-color="white" class="text-bold">SIL</q-badge>-->
          <div class="text-subtitle1 text-weight-medium" style="line-height: 0.9">
            Dashboard de Gestión <br>
            <q-badge color="warning" text-color="black" v-if="roleText" class="text-bold">
              {{ $store.user.area ? $store.user.area.name : roleText }}
            </q-badge>
          </div>
        </div>

        <q-space />

        <div class="row items-center q-gutter-sm">

          <q-btn-dropdown flat unelevated no-caps dropdown-icon="expand_more">
            <template v-slot:label>
              <div class="row items-center no-wrap q-gutter-sm">
                <q-avatar rounded>
                  <q-img :src="`${$url}/../images/${$store.user.avatar}`" width="40px" height="40px" v-if="$store.user.avatar"/>
                  <q-icon name="person" v-else />
                </q-avatar>
                <div class="text-left" style="line-height: 1">
                  <div class="ellipsis" style="max-width: 130px;">
                    {{ $store.user.username }}
                  </div>
                  <q-chip dense size="10px" :color="$filters.color($store.user.role)" text-color="white">
                    {{ $store.user.role }}
                  </q-chip>
                </div>
              </div>
            </template>

            <q-item clickable v-close-popup>
              <q-item-section>
                <q-item-label class="text-grey-7">
                  Permisos asignados
                </q-item-label>
                <q-item-label caption class="q-mt-xs">
                  <div class="row q-col-gutter-xs" style="min-width: 150px; max-width: 150px;">
                    <q-chip
                      v-for="(p, i) in $store.permissions"
                      :key="i"
                      dense
                      color="grey-3"
                      text-color="black"
                      size="12px"
                      class="q-mr-xs q-mb-xs"
                    >
                      {{ p }}
                    </q-chip>
                    <q-badge v-if="!$store.permissions?.length" color="grey-5" outline>Sin permisos</q-badge>
                  </div>
                </q-item-label>
              </q-item-section>
            </q-item>

            <q-separator />

            <q-item clickable v-ripple @click="logout" v-close-popup>
              <q-item-section avatar>
                <q-icon name="logout" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Salir</q-item-label>
              </q-item-section>
            </q-item>
          </q-btn-dropdown>
        </div>
      </q-toolbar>
    </q-header>

    <!-- DRAWER -->
    <q-drawer
      v-model="leftDrawerOpen"
      bordered
      show-if-above
      :width="200"
      :breakpoint="500"
      class="bg-primary text-white"
    >
      <q-list class="q-pb-none">
        <q-item-label header class="text-center q-pa-none q-pt-md">
          <q-avatar size="64px" class="q-mb-sm bg-white" rounded>
            <q-img src="/logo.png" width="90px" />
          </q-avatar>
          <div class="text-weight-bold text-white">SIL</div>
          <div class="text-caption text-white">Hospital General San Juan de Dios</div>
        </q-item-label>

<!--        <q-separator color="green-8" />-->

        <q-item-label header class="q-px-md text-grey-3 q-mt-sm">
          Módulos del Sistema
        </q-item-label>
        <q-item dense to="/" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Dashboard')">
          <q-item-section avatar>
            <q-icon name="dashboard" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Dashboard</q-item-label>
          </q-item-section>
        </q-item>

        <q-item dense to="/usuarios" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Usuarios')">
          <q-item-section avatar>
            <q-icon name="people" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Usuarios</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/establecimientos" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Establecimientos')">
          <q-item-section avatar>
            <q-icon name="local_hospital" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Establecimientos</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/pacientes" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Pacientes')">
          <q-item-section avatar>
            <q-icon name="folder_shared" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Pacientes</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/doctores" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Doctores')">
          <q-item-section avatar>
            <q-icon name="medical_services" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Doctores</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/servicios" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Servicios')">
          <q-item-section avatar>
            <q-icon name="room_service" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Prestaciones</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/formularios" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Formularios')">
          <q-item-section avatar>
            <q-icon name="description" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Formularios</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/consentimientos" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Consentimientos')">
          <q-item-section avatar>
            <q-icon name="assignment_turned_in" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Consentimientos</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/solicitudes" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Solicitudes')">
          <q-item-section avatar>
            <q-icon name="request_page" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Admision</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/area-preanalitica" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Area preanalitica')">
          <q-item-section avatar>
            <q-icon name="science" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Preanalítica</q-item-label>
          </q-item-section>
        </q-item>
<!--        Preanalitca terminadoas-->
        <q-item dense to="/area-preanalitica-procesadas" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Area preanalitica')">
          <q-item-section avatar>
            <q-icon name="analytics" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Preanalítica estados</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/analitica" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Analitica')">
          <q-item-section avatar>
            <q-icon name="biotech" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">
<!--              si el susatio role es adminsitrador colcaor Analitica-->
<!--              <span v-if="$store.user.role === 'Administrador'">Analítica</span>-->
<!--              <template v-else>-->
<!--                <div v-if="hasPermission('HEMATOLOGÍA')">Hematología</div>-->
<!--                <div v-if="hasPermission('QUÍMICA SANGUÍNEA Y SEROLOGÍA')">Química Sanguínea y Serología</div>-->
<!--                <div v-if="hasPermission('UROANÁLISIS')">Uroanálisis</div>-->
<!--                <div v-if="hasPermission('MICROBIOLOGÍA')">Microbiología</div>-->
<!--                <div v-if="hasPermission('INMUNOLOGÍA / INFECCIOSOS')">Inmunología / Infecciosos</div>-->
<!--                <div v-if="hasPermission('BIOLOGÍA MOLECULAR')">Biología Molecular</div>-->
<!--              </template>-->
<!--              <span v-else>{{$store.user.area?.title}}</span>-->
              Analítica
<!--              <pre>{{$store.user.area}}</pre>-->
            </q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/reportes/servicios-resumen" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="hasPermission('Usuarios')">
          <q-item-section avatar>
            <q-icon name="bar_chart" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Servicios Resumen</q-item-label>
          </q-item-section>
        </q-item>
        <q-expansion-item dense expand-separator icon="insert_chart" label="Reportes" active-class="menu-active" >
          <q-list>
            <q-item :inset-level="0.3" dense to="/reporte/consentimiento" exact clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="assignment" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Reporte de Consentimientos</q-item-label>
              </q-item-section>
            </q-item>
<!--            {-->
<!--            path: '/reporte/solicitudes',-->
<!--            component: () => import('pages/solicitudes/SolicitudesReporte.vue'),-->
<!--            meta: {requiresAuth: true, perm: 'Consentimientos'}-->
<!--            },-->
            <q-item :inset-level="0.3" dense to="/reporte/solicitudes" exact clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="request_page" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Reporte de Solicitudes</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
<!--        {-->
<!--        path: '/formularios',-->
<!--        component: () => import('pages/formularios/Formularios.vue'),-->
<!--        meta: { requiresAuth: true, perm: 'Formularios' }-->
<!--        }-->
<!--        HEMATOLOGÍA (Area 2)-->
<!--        QUÍMICA SANGUÍNEA Y SEROLOGÍA (Area 3)-->
<!--        UROANÁLISIS (Area 4)-->
<!--        MICROBIOLOGÍA (Area 5)-->
<!--        INMUNOLOGÍA / INFECCIOSOS (Area 6)-->
<!--        BIOLOGÍA MOLECULAR (Area 7)-->

        <div class="q-pa-md">
          <div class="text-white-7 text-caption">
            SIL v{{ $version }}
          </div>
          <div class="text-white-7 text-caption">
            © {{ new Date().getFullYear() }} Hospital General San Juan de Dios
          </div>
        </div>

        <q-item clickable class="text-white" @click="logout" v-close-popup>
          <q-item-section avatar>
            <q-icon name="logout" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Salir</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <!-- PAGE -->
    <q-page-container class="bg-grey-2">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { computed, getCurrentInstance, ref } from 'vue'
import { useCounterStore } from 'stores/example-store'

const { proxy } = getCurrentInstance()
const store = useCounterStore()

const leftDrawerOpen = ref(false)

// Helpers de permisos
function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
function hasPermission(perm) {
  return proxy.$store.permissions.includes(perm)
}
function logout () {
  proxy.$alert.dialog('¿Desea salir del sistema?')
    .onOk(() => {
      proxy.$axios.post('/logout')
        .then(() => {
          proxy.$store.isLogged = false
          proxy.$store.user = {}
          proxy.$store.permissions = []
          localStorage.removeItem('tokenSIL')
          proxy.$router.push('/login')
        })
        // .catch(() => proxy.$alert.error('Error al cerrar sesión. Intente nuevamente.'))
        .catch(() => {
          proxy.$store.isLogged = false
          proxy.$store.user = {}
          proxy.$store.permissions = []
          localStorage.removeItem('tokenSIL')
          proxy.$router.push('/login')
        })
    })
}

const roleText = computed(() => {
  const role = proxy.$store.user.role
  if (!role) return ''
  if (role === 'Administrador') return 'Administrador'
  return role
})
</script>

<style scoped>
.menu-item {
  border-radius: 10px;
  margin: 4px 8px;
  padding: 4px 6px;
}
.menu-active {
  background: rgba(255, 255, 255, 0.15);
  color: #fff !important;
  border-radius: 10px;
}
</style>

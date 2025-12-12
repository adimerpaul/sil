const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      { path: '/usuarios', component: () => import('pages/usuarios/Usuarios.vue'), meta: { requiresAuth: true, perm: 'Usuarios' } },
      { path: '/pacientes', component: () => import('pages/pacientes/Pacientes.vue'), meta: { requiresAuth: true, perm: 'Pacientes' } },
      { path: '/consentimientos', component: () => import('pages/consentimientos/Consentimientos.vue'), meta: { requiresAuth: true, perm: 'Consentimientos' } },
      { path: '/doctores', component: () => import('pages/doctors/Doctors.vue'), meta: { requiresAuth: true, perm: 'Doctores' } },
      { path: '/solicitudes', component: () => import('pages/solicitudes/Solicitudes.vue'), meta: { requiresAuth: true, perm: 'Solicitudes' }},
      { path: '/establecimientos', component: () => import('pages/establecimientos/Establecimientos.vue'), meta: { requiresAuth: true, perm: 'Establecimientos' } },
      { path: '/servicios', component: () => import('pages/servicios/Servicios.vue'), meta: { requiresAuth: true, perm: 'Servicios' } },
      { path: '/area-preanalitica', component: () => import('pages/areaPreanalitica/AreaPreanalitica.vue'), meta: { requiresAuth: true, perm: 'Area Preanalitica' } },
      { path: '/analitica', component: () => import('pages/analitica/Analitica.vue'), meta: { requiresAuth: true, perm: 'Analitica' } },
      {
        path: '/analitica/:id',
        name: 'analitica-detalle',
        component: () => import('pages/analitica/AnaliticaDetalle.vue'),
        meta: { requiresAuth: true, perm: 'Analitica' }
      },
      // this.$router.push({ name: 'analitica-hematologia', params: { id: solicitud.id } })
      {
        path: '/analitica/hematologia/:id',
        name: 'analitica-hematologia',
        component: () => import('pages/analitica/Hematologia.vue'),
        meta: { requiresAuth: true, perm: 'Analitica' }
      },
      {
        path: '/analitica/quimica-sanguinia/:id',
        name: 'analitica-quimica-sanguinia',
        component: () => import('pages/analitica/QuimicaSanguinia.vue'),
        meta: { requiresAuth: true, perm: 'Analitica' }
      },
      // uroanalisis
      {
        path: '/analitica/uroanalisis/:id',
        name: 'analitica-uroanalisis',
        component: () => import('pages/analitica/Uroanalisis.vue'),
        meta: { requiresAuth: true, perm: 'Analitica' }
      },
      {
        path: '/analitica/parasitologia/:id',
        name: 'analitica-parasitologia',
        component: () => import('pages/analitica/Parasitologia.vue'),
        meta: { requiresAuth: true, perm: 'Analitica' }
      },
    //   formularios
      {
        path: '/formularios',
        component: () => import('pages/formularios/Formularios.vue'),
        meta: { requiresAuth: true, perm: 'Formularios' }
      }
    ]
  },
  {
    path: '/login',
    component: () => import('layouts/Login.vue')
  },
  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes

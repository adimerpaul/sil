const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      {path: '', component: () => import('pages/IndexPage.vue'), meta: {requiresAuth: true}},
      {
        path: '/usuarios',
        component: () => import('pages/usuarios/Usuarios.vue'),
        meta: {requiresAuth: true, perm: 'Usuarios'}
      },
      {
        path: '/pacientes',
        component: () => import('pages/pacientes/Pacientes.vue'),
        meta: {requiresAuth: true, perm: 'Pacientes'}
      },
      {
        path: '/consentimientos',
        component: () => import('pages/consentimientos/Consentimientos.vue'),
        meta: {requiresAuth: true, perm: 'Consentimientos'}
      },
      {
        path: '/reporte/consentimiento',
        component: () => import('pages/consentimientos/ConsentimientoReporte.vue'),
        meta: {requiresAuth: true, perm: 'Consentimientos'}
      },
      {
        path: '/reporte/solicitudes',
        component: () => import('pages/solicitudes/SolicitudesReporte.vue'),
        meta: {requiresAuth: true, perm: 'Consentimientos'}
      },
      {
        path: '/doctores',
        component: () => import('pages/doctors/Doctors.vue'),
        meta: {requiresAuth: true, perm: 'Doctores'}
      },
      {
        path: '/solicitudes',
        component: () => import('pages/solicitudes/Solicitudes.vue'),
        meta: {requiresAuth: true, perm: 'Solicitudes'}
      },
      {
        path: '/recogidos',
        component: () => import('pages/recogidos/Recogidos.vue'),
        meta: {requiresAuth: true, perm: 'Solicitudes'}
      },

      {
        path: '/solicitudes/new',
        name: 'solicitudes-new',
        component: () => import('pages/solicitudes/SolicitudNew.vue'),
        meta: {requiresAuth: true, perm: 'Solicitudes'}
      },
      {
        path: '/solicitudes/:id/edit',
        name: 'solicitudes-edit',
        component: () => import('pages/solicitudes/SolicitudEdit.vue'),
        meta: {requiresAuth: true, perm: 'Solicitudes'}
      },
      {
        path: '/establecimientos',
        component: () => import('pages/establecimientos/Establecimientos.vue'),
        meta: {requiresAuth: true, perm: 'Establecimientos'}
      },
      {
        path: '/servicios',
        component: () => import('pages/servicios/Servicios.vue'),
        meta: {requiresAuth: true, perm: 'Servicios'}
      },
      {
        path: '/almacen/inventario',
        component: () => import('pages/almacen/Inventario.vue'),
        meta: {requiresAuth: true, perm: 'Módulo inventario'}
      },
      {
        path: '/almacen/compras',
        component: () => import('pages/almacen/Compras.vue'),
        meta: {requiresAuth: true, perm: 'Modulo detalle compras'}
      },
      {
        path: '/almacen/productos-por-vencer',
        component: () => import('pages/almacen/ProductosPorVencer.vue'),
        meta: {requiresAuth: true, perm: 'Módulo inventario'}
      },
      {
        path: '/almacen/productos-vencidos',
        component: () => import('pages/almacen/ProductosVencidos.vue'),
        meta: {requiresAuth: true, perm: 'Módulo inventario'}
      },
      {
        path: '/almacen/proveedores',
        component: () => import('pages/almacen/Proveedores.vue'),
        meta: {requiresAuth: true, perm: 'Módulo inventario'}
      },
      {
        path: '/almacen/compras/nueva',
        component: () => import('pages/almacen/CompraNueva.vue'),
        meta: {requiresAuth: true, perm: 'Modulo compras'}
      },
      {
        path: '/almacen/compras/:id/editar',
        component: () => import('pages/almacen/CompraNueva.vue'),
        props: true,
        meta: {requiresAuth: true, perm: 'Modulo compras'}
      },
      {
        path: '/pedidos',
        component: () => import('pages/almacen/Pedidos.vue'),
        meta: {requiresAuth: true, perm: 'Ver Pedidos'}
      },
      {
        path: '/pedidos/nuevo',
        component: () => import('pages/almacen/PedidosNueva.vue'),
        meta: {requiresAuth: true, perm: 'Crear Pedidos'}
      },
      {
        path: '/area-preanalitica',
        component: () => import('pages/areaPreanalitica/AreaPreanalitica.vue'),
        meta: {requiresAuth: true, perm: 'Area Preanalitica'}
      },
      // area-preanalitica-procesadas
      {
        path: '/area-preanalitica-procesadas',
        component: () => import('pages/areaPreanalitica/AreaPreanaliticaProcesadas.vue'),
        meta: {requiresAuth: true, perm: 'Area Preanalitica' }
      },
      {
        path: '/analitica',
        component: () => import('pages/analitica/Analitica.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      {
        path: '/analitica/:id',
        name: 'analitica-detalle',
        component: () => import('pages/analitica/AnaliticaDetalle.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      // this.$router.push({ name: 'analitica-hematologia', params: { id: solicitud.id } })
      {
        path: '/analitica/hematologia/:id',
        name: 'analitica-hematologia',
        component: () => import('pages/analitica/Hematologia.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      {
        path: '/analitica/quimica-sanguinia/:id',
        name: 'analitica-quimica-sanguinia',
        component: () => import('pages/analitica/QuimicaSanguinia.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      // uroanalisis
      {
        path: '/analitica/uroanalisis/:id',
        name: 'analitica-uroanalisis',
        component: () => import('pages/analitica/Uroanalisis.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      {
        path: '/analitica/parasitologia/:id',
        name: 'analitica-parasitologia',
        component: () => import('pages/analitica/Parasitologia.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      // papiloma humano
      {
        path: '/analitica/papiloma-humano/:id',
        name: 'analitica-papiloma-humano',
        component: () => import('pages/analitica/PapilomaHumano.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      // panel drespiratorio
      {
        path: '/analitica/panel-respiratorio/:id',
        name: 'analitica-panel-respiratorio',
        component: () => import('pages/analitica/PanelRespiratorio.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      // panel sexual
      {
        path: '/analitica/panel-sexual/:id',
        name: 'analitica-panel-sexual',
        component: () => import('pages/analitica/PanelSexual.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      // cultivo antibiograma
      {
        path: '/analitica/cultivo-antibiograma/:id',
        name: 'analitica-cultivo-antibiograma',
        component: () => import('pages/analitica/CultivoAntibiograma.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      // inmulogia
      {
        path: '/analitica/inmunologia/:id',
        name: 'analitica-inmunologia',
        component: () => import('pages/analitica/InmunologiaSolicitudPage.vue'),
        meta: {requiresAuth: true, perm: 'Analitica'}
      },
      //   formularios
      {
        path: '/formularios',
        component: () => import('pages/formularios/Formularios.vue'),
        meta: {requiresAuth: true, perm: 'Formularios'}
      },
      // ReportesServiciosResumen.vue
      {
        path: '/reportes/servicios-resumen',
        component: () => import('pages/reportes/ReportesServiciosResumen.vue'),
        meta: {requiresAuth: true, perm: 'Reportes'}
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

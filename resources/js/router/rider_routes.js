const riderRoutes = [
  {
    path: '/rider',
    component: () => import('@/views/rider/Home.vue'),
    meta: { requiresAuth: true, requiresRider: true },
    children: [
      { path: '', redirect: '/rider/dashboard' },
      { path: 'dashboard', name: 'RiderDashboard', component: () => import('@/views/rider/Dashboard.vue') },
      { path: 'deliveries', name: 'RiderDeliveries', component: () => import('@/views/rider/Deliveries.vue') },
      { path: 'notifications', name: 'RiderNotifications', component: () => import('@/views/Notifications.vue') },
      { path: 'settings', name: 'RiderSettings', component: () => import('@/views/Settings.vue') },
      { path: ':pathMatch(.*)*', redirect: '/rider/dashboard' },
    ],
  },
];

export default riderRoutes;

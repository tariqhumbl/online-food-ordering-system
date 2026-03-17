const adminRoutes = [
  {
    path: '/admin',
    component: () => import('@/views/admin/Home.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard', name: 'AdminDashboard', component: () => import('@/views/admin/Dashboard.vue') },
      { path: 'restaurants', name: 'AdminRestaurants', component: () => import('@/views/admin/Restaurants.vue') },
      { path: 'riders', name: 'AdminRiders', component: () => import('@/views/admin/Riders.vue') },
      { path: 'orders', name: 'AdminOrders', component: () => import('@/views/admin/Orders.vue') },
      { path: 'notifications', name: 'AdminNotifications', component: () => import('@/views/Notifications.vue') },
      { path: 'settings', name: 'AdminSettings', component: () => import('@/views/Settings.vue') },
      { path: ':pathMatch(.*)*', redirect: '/admin/dashboard' },
    ],
  },
];

export default adminRoutes;

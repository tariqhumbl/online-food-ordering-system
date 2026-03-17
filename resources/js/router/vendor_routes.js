const vendorRoutes = [
  {
    path: '/vendor',
    component: () => import('@/views/vendor/Home.vue'),
    meta: { requiresAuth: true, requiresVendor: true },
    children: [
      { path: '', redirect: '/vendor/dashboard' },
      { path: 'dashboard', name: 'VendorDashboard', component: () => import('@/views/vendor/Dashboard.vue') },
      { path: 'restaurant', name: 'VendorRestaurant', component: () => import('@/views/vendor/Restaurant.vue') },
      { path: 'menu', name: 'VendorMenu', component: () => import('@/views/vendor/Menu.vue') },
      { path: 'orders', name: 'VendorOrders', component: () => import('@/views/vendor/Orders.vue') },
      { path: 'notifications', name: 'VendorNotifications', component: () => import('@/views/Notifications.vue') },
      { path: 'settings', name: 'VendorSettings', component: () => import('@/views/Settings.vue') },
      { path: ':pathMatch(.*)*', redirect: '/vendor/dashboard' },
    ],
  },
];

export default vendorRoutes;

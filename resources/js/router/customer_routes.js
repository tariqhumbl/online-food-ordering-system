const customerRoutes = [
  {
    path: '/customer',
    component: () => import('@/views/customer/Home.vue'),
    meta: { requiresAuth: true, requiresCustomer: true },
    children: [
      { path: '', redirect: '/customer/dashboard' },
      { path: 'dashboard', name: 'CustomerDashboard', component: () => import('@/views/customer/Dashboard.vue') },
      { path: 'restaurants', name: 'CustomerRestaurants', component: () => import('@/views/customer/RestaurantList.vue') },
      { path: 'restaurants/:id', name: 'CustomerRestaurantMenu', component: () => import('@/views/customer/RestaurantMenu.vue') },
      { path: 'cart', name: 'Cart', component: () => import('@/views/customer/Cart.vue') },
      { path: 'checkout', name: 'Checkout', component: () => import('@/views/customer/Checkout.vue') },
      { path: 'orders', name: 'CustomerOrders', component: () => import('@/views/customer/Orders.vue') },
      { path: 'orders/:id', name: 'OrderTracking', component: () => import('@/views/customer/OrderTracking.vue') },
      { path: 'notifications', name: 'Notifications', component: () => import('@/views/Notifications.vue') },
      { path: 'settings', name: 'CustomerSettings', component: () => import('@/views/Settings.vue') },
      { path: ':pathMatch(.*)*', redirect: '/customer/dashboard' },
    ],
  },
];

export default customerRoutes;

import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/website/Home.vue';
import adminRoutes from '@/router/admin_routes.js';
import vendorRoutes from '@/router/vendor_routes.js';
import customerRoutes from '@/router/customer_routes.js';
import riderRoutes from '@/router/rider_routes.js';
import Login from '../views/auth/Login.vue';
import Register from '../views/auth/Register.vue';
import ForgotPassword from '../views/auth/ForgotPassword.vue';
import ResetPassword from '../views/auth/ResetPassword.vue';
import ChangePassword from '../views/auth/ChangePassword.vue';
import UpdateProfile from '../views/auth/UpdateProfile.vue';
import { isLoggedIn, getUserRole, getUser } from '@/services/auth_service';

const routes = [
  {
    path: '/',
    component: Home,
  },
  {
    path: '/restaurant/:id',
    name: 'PublicRestaurant',
    component: () => import('@/views/website/RestaurantDetail.vue'),
  },
  {
    path: '/dashboard',
    redirect: () => {
      if (!isLoggedIn()) return '/login';
      const user = getUser();
      const roleId = user?.role_id;
      const pathMap = { 1: '/admin/dashboard', 2: '/vendor/dashboard', 3: '/customer/dashboard', 4: '/rider/dashboard' };
      return pathMap[roleId] || '/customer/dashboard';
    },
  },
  {
    path: '/login',
    component: Login,
  },
  {
    path: '/register',
    component: Register,
  },
  {
    path: '/forgot-request',
    component: ForgotPassword,
  },
  {
    path: '/reset-password',
    component: ResetPassword,
  },
  {
    path: '/change-password',
    component: ChangePassword,
  },
  {
    path: '/update-profile',
    component: UpdateProfile,
  },

  ...adminRoutes,
  ...vendorRoutes,
  ...customerRoutes,
  ...riderRoutes,
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) {
      return { el: to.hash, behavior: 'smooth' };
    }
    return { top: 0, behavior: 'smooth' };
  },
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = isLoggedIn();
  const userRole = getUserRole();

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login');
  } else if (to.meta.requiresAdmin && userRole !== 'admin') {
    next('/login');
  } else if (to.meta.requiresVendor && userRole !== 'vendor') {
    next('/login');
  } else if (to.meta.requiresCustomer && userRole !== 'customer') {
    next('/login');
  } else if (to.meta.requiresRider && userRole !== 'rider') {
    next('/login');
  } else {
    next();
  }
});

export default router;

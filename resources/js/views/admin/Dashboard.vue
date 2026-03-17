<template>
  <div class="pc-content admin-dashboard">
    <!-- Breadcrumb -->
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/admin/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Dashboard</li>
            </ul>
          </div>
          <div class="col-12">
            <div class="page-header-title d-flex align-items-center gap-2">
              <i class="ph-duotone ph-squares-four text-primary" style="font-size: 1.75rem;"></i>
              <div>
                <h2 class="mb-0">Admin Dashboard</h2>
                <p class="text-muted mb-0 small">Overview of your food ordering platform</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stat cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-4 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <h5 class="mb-0 text-muted">Total Restaurants</h5>
              <i class="ph-duotone ph-storefront text-primary opacity-75" style="font-size: 2rem;"></i>
            </div>
            <template v-if="statsLoading">
              <div class="placeholder-glow"><span class="placeholder col-4" style="height: 2rem;"></span></div>
            </template>
            <template v-else>
              <h3 class="f-w-300 mb-1">{{ stats.totalRestaurants }}</h3>
              <p class="text-muted mb-0 small">Active & pending</p>
            </template>
            <div class="progress mt-3" style="height: 6px">
              <div class="progress-bar bg-primary" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <h5 class="mb-0 text-muted">Total Orders</h5>
              <i class="ph-duotone ph-receipt text-info opacity-75" style="font-size: 2rem;"></i>
            </div>
            <template v-if="statsLoading">
              <div class="placeholder-glow"><span class="placeholder col-4" style="height: 2rem;"></span></div>
            </template>
            <template v-else>
              <h3 class="f-w-300 mb-1">{{ stats.totalOrders }}</h3>
              <p class="text-muted mb-0 small">All time</p>
            </template>
            <div class="progress mt-3" style="height: 6px">
              <div class="progress-bar bg-info" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden h-100 bg-brand-color-3">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <h5 class="mb-0 text-white text-opacity-90">Pending Orders</h5>
              <i class="ph-duotone ph-clock-countdown text-white opacity-75" style="font-size: 2rem;"></i>
            </div>
            <template v-if="statsLoading">
              <div class="placeholder-glow"><span class="placeholder bg-white bg-opacity-50 col-4" style="height: 2rem;"></span></div>
            </template>
            <template v-else>
              <h3 class="f-w-300 mb-1 text-white">{{ stats.pendingOrders }}</h3>
              <p class="text-white text-opacity-75 mb-0 small">Awaiting action</p>
            </template>
            <div class="progress mt-3 bg-white bg-opacity-25" style="height: 6px">
              <div class="progress-bar bg-white" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="row mb-4">
      <div class="col-12">
        <h5 class="mb-3">Quick actions</h5>
      </div>
      <div class="col-md-4 col-sm-6 mb-3">
        <router-link to="/admin/restaurants" class="card quick-action-card text-decoration-none text-dark h-100">
          <div class="card-body d-flex align-items-center">
            <div class="flex-shrink-0 me-3 rounded-3 bg-primary bg-opacity-10 p-3">
              <i class="ph-duotone ph-storefront text-primary" style="font-size: 2rem;"></i>
            </div>
            <div>
              <h6 class="mb-0">Restaurants</h6>
              <small class="text-muted">Manage restaurants & approve vendors</small>
            </div>
            <i class="ph-duotone ph-caret-right ms-auto text-muted"></i>
          </div>
        </router-link>
      </div>
      <div class="col-md-4 col-sm-6 mb-3">
        <router-link to="/admin/orders" class="card quick-action-card text-decoration-none text-dark h-100">
          <div class="card-body d-flex align-items-center">
            <div class="flex-shrink-0 me-3 rounded-3 bg-info bg-opacity-10 p-3">
              <i class="ph-duotone ph-list-checks text-info" style="font-size: 2rem;"></i>
            </div>
            <div>
              <h6 class="mb-0">Orders</h6>
              <small class="text-muted">View and manage all orders</small>
            </div>
            <i class="ph-duotone ph-caret-right ms-auto text-muted"></i>
          </div>
        </router-link>
      </div>
      <div class="col-md-4 col-sm-6 mb-3">
        <a href="/" target="_blank" class="card quick-action-card text-decoration-none text-dark h-100">
          <div class="card-body d-flex align-items-center">
            <div class="flex-shrink-0 me-3 rounded-3 bg-success bg-opacity-10 p-3">
              <i class="ph-duotone ph-globe text-success" style="font-size: 2rem;"></i>
            </div>
            <div>
              <h6 class="mb-0">View landing page</h6>
              <small class="text-muted">See public site & restaurants</small>
            </div>
            <i class="ph-duotone ph-caret-right ms-auto text-muted"></i>
          </div>
        </a>
      </div>
    </div>

    <!-- Recent orders -->
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between py-3">
        <h5 class="mb-0">Recent orders</h5>
        <router-link to="/admin/orders" class="btn btn-sm btn-outline-primary">View all</router-link>
      </div>
      <div class="card-body py-2 px-0">
        <div v-if="ordersLoading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted mb-0">Loading orders...</p>
        </div>
        <div v-else-if="ordersError" class="alert alert-warning m-3 mb-0">{{ ordersError }}</div>
        <div v-else-if="!recentOrders.length" class="text-center py-5 text-muted">
          No orders yet.
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover table-borderless table-sm mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>Order</th>
                <th>Restaurant</th>
                <th>Customer</th>
                <th>Status</th>
                <th class="text-end">Total</th>
                <th>Date</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="o in recentOrders" :key="o.id">
                <td><strong>{{ o.order_number }}</strong></td>
                <td>{{ o.restaurant ? o.restaurant.name : '—' }}</td>
                <td>
                  <span>{{ o.user ? o.user.name : '—' }}</span>
                  <br /><small class="text-muted">{{ o.customer_phone || (o.user && o.user.email) || '' }}</small>
                </td>
                <td>
                  <span :class="orderStatusBadgeClass(o.status)">{{ formatStatus(o.status) }}</span>
                </td>
                <td class="text-end">{{ formatMoney(o.total) }}</td>
                <td class="small">{{ formatDate(o.created_at) }}</td>
                <td class="text-end">
                  <router-link :to="'/admin/orders'" class="btn btn-sm btn-outline-primary">View</router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { ref, reactive, onMounted } from 'vue';

const STATUS_LABELS = {
  pending: 'Pending',
  confirmed: 'Confirmed',
  preparing: 'Preparing',
  ready: 'Ready',
  out_for_delivery: 'Out for delivery',
  delivered: 'Delivered',
  cancelled: 'Cancelled',
};

export default {
  name: 'AdminDashboard',
  setup() {
    const statsLoading = ref(true);
    const ordersLoading = ref(true);
    const ordersError = ref('');

    const stats = reactive({
      totalRestaurants: 0,
      totalOrders: 0,
      pendingOrders: 0,
    });

    const recentOrders = ref([]);

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }

    function formatDate(val) {
      if (!val) return '—';
      return new Date(val).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
    }

    function formatStatus(s) {
      return STATUS_LABELS[s] || s;
    }

    function orderStatusBadgeClass(status) {
      const map = {
        pending: 'badge bg-secondary',
        confirmed: 'badge bg-info',
        preparing: 'badge bg-primary',
        ready: 'badge bg-warning text-dark',
        out_for_delivery: 'badge bg-primary',
        delivered: 'badge bg-success',
        cancelled: 'badge bg-danger',
      };
      return map[status] || 'badge bg-light text-dark';
    }

    function loadStats() {
      statsLoading.value = true;
      Promise.all([
        API.admin.restaurants.list({ per_page: 1 }).then((r) => r.data.total ?? 0),
        API.orders.list({ per_page: 1 }).then((r) => r.data.total ?? 0),
        API.orders.list({ status: 'pending', per_page: 1 }).then((r) => r.data.total ?? 0),
      ])
        .then(([restaurants, orders, pending]) => {
          stats.totalRestaurants = restaurants;
          stats.totalOrders = orders;
          stats.pendingOrders = pending;
        })
        .catch(() => {
          stats.totalRestaurants = 0;
          stats.totalOrders = 0;
          stats.pendingOrders = 0;
        })
        .finally(() => (statsLoading.value = false));
    }

    function loadRecentOrders() {
      ordersLoading.value = true;
      ordersError.value = '';
      API.orders
        .list({ per_page: 10 })
        .then((res) => {
          recentOrders.value = res.data.data ?? [];
        })
        .catch(() => {
          ordersError.value = 'Could not load recent orders.';
        })
        .finally(() => (ordersLoading.value = false));
    }

    onMounted(() => {
      loadStats();
      loadRecentOrders();
    });

    return {
      stats,
      statsLoading,
      recentOrders,
      ordersLoading,
      ordersError,
      formatMoney,
      formatDate,
      formatStatus,
      orderStatusBadgeClass,
    };
  },
};
</script>

<style scoped>
.admin-dashboard .quick-action-card {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.admin-dashboard .quick-action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
}
</style>

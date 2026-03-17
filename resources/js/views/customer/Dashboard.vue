<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/customer/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Dashboard</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">Welcome, {{ userName }}</h2>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">Browse restaurants and place orders.</p>

    <!-- Quick action cards -->
    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <router-link to="/customer/restaurants" class="card text-decoration-none text-dark hover-shadow h-100">
          <div class="card-body d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
              <i class="ph-duotone ph-storefront text-primary" style="font-size: 2.5rem;"></i>
            </div>
            <div>
              <h5 class="mb-1">Browse restaurants</h5>
              <small class="text-muted">Find food and view menus</small>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col-md-4 mb-3">
        <router-link to="/customer/cart" class="card text-decoration-none text-dark hover-shadow h-100">
          <div class="card-body d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
              <i class="ph-duotone ph-shopping-cart text-primary" style="font-size: 2.5rem;"></i>
            </div>
            <div>
              <h5 class="mb-1">Cart</h5>
              <small class="text-muted">{{ cartItemCount }} item(s)</small>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col-md-4 mb-3">
        <router-link to="/customer/orders" class="card text-decoration-none text-dark hover-shadow h-100">
          <div class="card-body d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
              <i class="ph-duotone ph-list-checks text-primary" style="font-size: 2.5rem;"></i>
            </div>
            <div>
              <h5 class="mb-1">My orders</h5>
              <small class="text-muted">View order history</small>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
      <div class="col-md-6 col-lg-3 mb-3">
        <div class="card statistics-card-1 overflow-hidden">
          <div class="card-body">
            <h5 class="mb-2">Total orders</h5>
            <h3 class="f-w-300 mb-0">{{ orders.length }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 mb-3">
        <div class="card statistics-card-1 overflow-hidden">
          <div class="card-body">
            <h5 class="mb-2">Cart items</h5>
            <h3 class="f-w-300 mb-0">{{ cartItemCount }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent orders -->
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between py-3">
        <h5 class="mb-0">Recent orders</h5>
        <router-link to="/customer/orders" class="btn btn-sm btn-primary">View all</router-link>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border spinner-border-sm text-primary"></div>
        </div>
        <div v-else-if="!recentOrders.length" class="text-center py-4 text-muted">
          No orders yet.
          <router-link to="/customer/restaurants" class="d-block mt-2">Browse restaurants</router-link>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover table-sm mb-0">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Restaurant</th>
                <th>Status</th>
                <th>Total</th>
                <th>Date</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="o in recentOrders" :key="o.id">
                <td><strong>{{ o.order_number }}</strong></td>
                <td>{{ o.restaurant?.name ?? '—' }}</td>
                <td><span :class="orderStatusBadgeClass(o.status)">{{ formatStatus(o.status) }}</span></td>
                <td>{{ formatMoney(o.total) }}</td>
                <td>{{ formatDate(o.created_at) }}</td>
                <td>
                  <router-link :to="`/customer/orders/${o.id}`" class="btn btn-sm btn-outline-primary">Track</router-link>
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
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';

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
  name: 'CustomerDashboard',
  setup() {
    const store = useStore();
    const loading = ref(false);
    const orders = ref([]);

    const userName = computed(() => store.state.profile?.name ?? 'Customer');
    const cartItemCount = computed(() => store.getters.cartItemCount ?? 0);
    const recentOrders = computed(() => orders.value.slice(0, 10));

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }
    function formatDate(val) {
      if (!val) return '—';
      return new Date(val).toLocaleString();
    }
    function formatStatus(s) {
      return STATUS_LABELS[s] ?? s;
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
      return 'badge ' + (map[status] ?? 'badge bg-light text-dark');
    }

    function load() {
      loading.value = true;
      API.orders
        .list({ per_page: 20 })
        .then((res) => {
          orders.value = res.data?.data ?? [];
        })
        .catch(() => {})
        .finally(() => (loading.value = false));
    }

    onMounted(() => load());

    return {
      userName,
      cartItemCount,
      orders,
      recentOrders,
      loading,
      formatMoney,
      formatDate,
      formatStatus,
      orderStatusBadgeClass,
    };
  },
};
</script>

<style scoped>
.hover-shadow:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}
</style>

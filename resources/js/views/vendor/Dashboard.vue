<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/vendor/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Dashboard</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">Dashboard</h2>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">Manage your restaurant, menu, and orders here.</p>

    <div v-if="!restaurantId" class="alert alert-info">
      <strong>Get started:</strong> Create your restaurant in
      <router-link to="/vendor/restaurant" class="alert-link">My Restaurant</router-link>
      to manage your menu and orders.
    </div>

    <template v-else>
      <!-- Stat cards -->
      <div class="row mb-4">
        <div class="col-md-4 col-sm-6 mb-3">
          <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
              <h5 class="mb-3">Total Orders</h5>
              <h3 class="f-w-300 mb-2">{{ stats.totalOrders }}</h3>
              <p class="text-muted mb-0 small">All time</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
          <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
              <h5 class="mb-3">Pending Orders</h5>
              <h3 class="f-w-300 mb-2">{{ stats.pendingOrders }}</h3>
              <p class="text-muted mb-0 small">Awaiting action</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
          <div class="card statistics-card-1 overflow-hidden bg-brand-color-3">
            <div class="card-body">
              <h5 class="mb-3 text-white">Restaurant</h5>
              <h3 class="f-w-300 mb-2 text-white text-truncate">{{ restaurantName || '—' }}</h3>
              <router-link to="/vendor/restaurant" class="text-white text-opacity-90 small">Edit details</router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="row mb-4">
        <div class="col-12">
          <h5 class="mb-3">Quick actions</h5>
        </div>
        <div class="col-md-4 mb-2">
          <router-link to="/vendor/restaurant" class="card text-decoration-none text-dark hover-shadow">
            <div class="card-body d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <i class="ph-duotone ph-storefront" style="font-size: 2rem;"></i>
              </div>
              <div>
                <h6 class="mb-0">My Restaurant</h6>
                <small class="text-muted">Edit details & settings</small>
              </div>
            </div>
          </router-link>
        </div>
        <div class="col-md-4 mb-2">
          <router-link to="/vendor/menu" class="card text-decoration-none text-dark hover-shadow">
            <div class="card-body d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <i class="ph-duotone ph-cooking-pot" style="font-size: 2rem;"></i>
              </div>
              <div>
                <h6 class="mb-0">Menu</h6>
                <small class="text-muted">Categories & items</small>
              </div>
            </div>
          </router-link>
        </div>
        <div class="col-md-4 mb-2">
          <router-link to="/vendor/orders" class="card text-decoration-none text-dark hover-shadow">
            <div class="card-body d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <i class="ph-duotone ph-list-checks" style="font-size: 2rem;"></i>
              </div>
              <div>
                <h6 class="mb-0">Orders</h6>
                <small class="text-muted">View & manage orders</small>
              </div>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Recent orders -->
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-3">
          <h5 class="mb-0">Recent orders</h5>
          <router-link to="/vendor/orders" class="btn btn-sm btn-primary">View all</router-link>
        </div>
        <div class="card-body">
          <div v-if="loading" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
          </div>
          <div v-else-if="!recentOrders.length" class="text-center py-4 text-muted">
            No orders yet.
          </div>
          <div v-else class="table-responsive">
            <table class="table table-hover table-sm mb-0">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Customer</th>
                  <th>Status</th>
                  <th>Total</th>
                  <th>Date</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="o in recentOrders" :key="o.id">
                  <td><strong>{{ o.order_number }}</strong></td>
                  <td>{{ o.user?.name ?? '—' }}</td>
                  <td class="align-middle">
                    <select
                      class="form-select form-select-sm"
                      :value="o.status"
                      :disabled="statusUpdatingId === o.id"
                      @change="updateOrderStatus(o, $event.target.value)"
                    >
                      <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                  </td>
                  <td>{{ formatMoney(o.total) }}</td>
                  <td>{{ formatDate(o.created_at) }}</td>
                  <td>
                    <router-link :to="'/vendor/orders'" class="btn btn-sm btn-outline-primary">View</router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useToast } from 'vue-toastification';

const STATUS_OPTIONS = [
  { value: 'pending', label: 'Pending' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'preparing', label: 'Preparing' },
  { value: 'ready', label: 'Ready' },
  { value: 'out_for_delivery', label: 'Out for delivery' },
  { value: 'delivered', label: 'Delivered' },
  { value: 'cancelled', label: 'Cancelled' },
];

export default {
  name: 'VendorDashboard',
  setup() {
    const store = useStore();
    const toast = useToast();
    const loading = ref(false);
    const statusUpdatingId = ref(null);
    const orders = ref([]);
    const restaurant = ref(null);

    const statusOptions = STATUS_OPTIONS;
    const recentOrders = computed(() => orders.value.slice(0, 10));

    const restaurantId = computed(() => store.state.profile?.restaurant_id ?? null);
    const restaurantName = computed(() => restaurant.value?.name ?? null);

    const stats = computed(() => {
      const list = orders.value;
      const pending = list.filter((o) => ['pending', 'confirmed', 'preparing', 'ready'].includes(o.status)).length;
      return { totalOrders: list.length, pendingOrders: pending };
    });

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }
    function formatDate(val) {
      if (!val) return '—';
      return new Date(val).toLocaleString();
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
      if (!restaurantId.value) return;
      loading.value = true;
      Promise.all([
        API.orders.list({ per_page: 50 }),
        API.restaurants.get(restaurantId.value),
      ])
        .then(([ordersRes, restRes]) => {
          orders.value = ordersRes.data?.data ?? [];
          restaurant.value = restRes.data;
        })
        .catch(() => {})
        .finally(() => (loading.value = false));
    }

    async function updateOrderStatus(order, newStatus) {
      if (order.status === newStatus) return;
      statusUpdatingId.value = order.id;
      try {
        await API.orders.updateStatus(order.id, newStatus);
        const o = orders.value.find((x) => x.id === order.id);
        if (o) o.status = newStatus;
        toast.success('Order status updated.');
      } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to update status.');
      } finally {
        statusUpdatingId.value = null;
      }
    }
    onMounted(() => load());

    return {
      restaurantId,
      restaurantName,
      stats,
      recentOrders,
      loading,
      statusUpdatingId,
      statusOptions,
      updateOrderStatus,
      formatMoney,
      formatDate,
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

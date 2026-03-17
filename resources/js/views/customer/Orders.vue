<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/customer/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">My Orders</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">My Orders</h2>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">View order history and track status.</p>

    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select v-model="filters.status" class="form-select" @change="loadOrders">
              <option value="">All</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="preparing">Preparing</option>
              <option value="ready">Ready</option>
              <option value="out_for_delivery">Out for delivery</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-outline-secondary" @click="resetFilters">Reset</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Loading orders...</p>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
        <div v-else-if="!orders.length" class="text-center py-5 text-muted">
          No orders yet.
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover">
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
              <tr v-for="o in orders" :key="o.id">
                <td><strong>{{ o.order_number }}</strong></td>
                <td>{{ o.restaurant?.name ?? '—' }}</td>
                <td>
                  <span :class="orderStatusBadgeClass(o.status)">{{ formatStatus(o.status) }}</span>
                </td>
                <td>{{ formatMoney(o.total) }}</td>
                <td>{{ formatDate(o.created_at) }}</td>
                <td>
                  <router-link :to="`/customer/orders/${o.id}`" class="btn btn-sm btn-outline-primary">
                    Track
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="pagination.last_page > 1" class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }}
          </small>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page - 1)">Previous</a>
              </li>
              <li
                v-for="p in pageNumbers"
                :key="p"
                class="page-item"
                :class="{ active: pagination.current_page === p }"
              >
                <a class="page-link" href="#" @click.prevent="goToPage(p)">{{ p }}</a>
              </li>
              <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page + 1)">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { ref, reactive, onMounted, watch } from 'vue';
import { useToast } from 'vue-toastification';

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
  name: 'CustomerOrders',
  setup() {
    const toast = useToast();
    const orders = ref([]);
    const loading = ref(false);
    const error = ref('');
    const filters = reactive({ status: '' });
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      from: 0,
      to: 0,
      total: 0,
      prev_page_url: null,
      next_page_url: null,
    });
    const pageNumbers = ref([]);

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

    function loadOrders() {
      loading.value = true;
      error.value = '';
      const params = { page: pagination.current_page, per_page: 15 };
      if (filters.status) params.status = filters.status;
      API.orders
        .list(params)
        .then((res) => {
          orders.value = res.data.data ?? [];
          Object.assign(pagination, {
            current_page: res.data.current_page,
            last_page: res.data.last_page,
            from: res.data.from,
            to: res.data.to,
            total: res.data.total,
            prev_page_url: res.data.prev_page_url,
            next_page_url: res.data.next_page_url,
          });
        })
        .catch((err) => {
          error.value = err.response?.data?.message ?? 'Failed to load orders.';
          toast.error(error.value);
        })
        .finally(() => (loading.value = false));
    }

    function resetFilters() {
      filters.status = '';
      pagination.current_page = 1;
      loadOrders();
    }

    function goToPage(page) {
      if (page < 1 || page > pagination.last_page) return;
      pagination.current_page = page;
      loadOrders();
    }

    watch(
      () => [pagination.current_page, pagination.last_page],
      () => {
        const cur = pagination.current_page;
        const last = pagination.last_page;
        const start = Math.max(1, cur - 2);
        const end = Math.min(last, cur + 2);
        const pages = [];
        for (let i = start; i <= end; i++) pages.push(i);
        pageNumbers.value = pages;
      },
      { immediate: true }
    );

    onMounted(() => loadOrders());

    return {
      orders,
      loading,
      error,
      filters,
      pagination,
      pageNumbers,
      formatMoney,
      formatDate,
      formatStatus,
      orderStatusBadgeClass,
      loadOrders,
      resetFilters,
      goToPage,
    };
  },
};
</script>

<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/customer/dashboard">Home</router-link></li>
              <li class="breadcrumb-item"><router-link to="/customer/orders">My Orders</router-link></li>
              <li class="breadcrumb-item" aria-current="page">{{ order?.order_number ?? 'Tracking' }}</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">Order {{ order?.order_number }}</h2>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">Loading order...</p>
    </div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-else-if="!order" class="alert alert-warning">Order not found.</div>
    <div v-else class="row">
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">Status</h5>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <span :class="orderStatusBadgeClass(order.status)" class="badge fs-6">{{ formatStatus(order.status) }}</span>
            </div>
            <div class="progress-steps">
              <div
                v-for="(step, i) in statusSteps"
                :key="step.key"
                class="d-flex align-items-center mb-2"
              >
                <div
                  class="rounded-circle d-flex align-items-center justify-content-center me-3 step-dot"
                  :class="stepReached(step.key) ? 'bg-primary text-white' : 'bg-light'"
                  style="width: 2rem; height: 2rem;"
                >
                  <i v-if="stepReached(step.key)" class="ph-duotone ph-check"></i>
                  <span v-else>{{ i + 1 }}</span>
                </div>
                <span :class="stepReached(step.key) ? 'text-primary fw-semibold' : 'text-muted'">{{ step.label }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">Order details</h5>
          </div>
          <div class="card-body">
            <p class="mb-1"><strong>Restaurant:</strong> {{ order.restaurant?.name }}</p>
            <p class="mb-1"><strong>Delivery address:</strong> {{ order.delivery_address }}</p>
            <p class="mb-1"><strong>Phone:</strong> {{ order.customer_phone || '—' }}</p>
            <p class="mb-2"><strong>Placed:</strong> {{ formatDate(order.created_at) }}</p>
            <div v-if="order.notes" class="mb-3">
              <strong>Notes:</strong> {{ order.notes }}
            </div>
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>Item</th>
                  <th class="text-end">Qty</th>
                  <th class="text-end">Price</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in order.items" :key="item.id">
                  <td>{{ item.item_name }}</td>
                  <td class="text-end">{{ item.quantity }}</td>
                  <td class="text-end">{{ formatMoney(item.unit_price) }}</td>
                  <td class="text-end">{{ formatMoney(item.subtotal) }}</td>
                </tr>
              </tbody>
            </table>
            <hr />
            <div class="d-flex justify-content-end gap-3">
              <span>Subtotal: {{ formatMoney(order.subtotal) }}</span>
              <span>Delivery: {{ formatMoney(order.delivery_fee) }}</span>
              <span><strong>Total: {{ formatMoney(order.total) }}</strong></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useToast } from 'vue-toastification';

const STATUS_ORDER = [
  'pending',
  'confirmed',
  'preparing',
  'ready',
  'out_for_delivery',
  'delivered',
];
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
  name: 'OrderTracking',
  setup() {
    const route = useRoute();
    const toast = useToast();
    const order = ref(null);
    const loading = ref(false);
    const error = ref('');

    const statusSteps = [
      { key: 'pending', label: 'Order placed' },
      { key: 'confirmed', label: 'Confirmed' },
      { key: 'preparing', label: 'Preparing' },
      { key: 'ready', label: 'Ready for pickup/delivery' },
      { key: 'out_for_delivery', label: 'Out for delivery' },
      { key: 'delivered', label: 'Delivered' },
    ];

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
    function stepReached(stepKey) {
      const s = order.value?.status;
      if (s === 'cancelled') return stepKey === 'pending';
      const idx = STATUS_ORDER.indexOf(s);
      const stepIdx = STATUS_ORDER.indexOf(stepKey);
      return stepIdx >= 0 && stepIdx <= idx;
    }

    onMounted(() => {
      const id = route.params.id;
      if (!id) return;
      loading.value = true;
      API.orders
        .get(id)
        .then((res) => {
          order.value = res.data;
        })
        .catch((err) => {
          error.value = err.response?.data?.message ?? 'Failed to load order.';
          toast.error(error.value);
        })
        .finally(() => (loading.value = false));
    });

    return {
      order,
      loading,
      error,
      statusSteps,
      formatMoney,
      formatDate,
      formatStatus,
      orderStatusBadgeClass,
      stepReached,
    };
  },
};
</script>

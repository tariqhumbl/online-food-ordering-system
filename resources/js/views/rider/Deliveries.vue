<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/rider/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Deliveries</li>
            </ul>
          </div>
          <div class="col-12">
            <div class="d-flex align-items-center gap-2">
              <i class="ph-duotone ph-package text-primary" style="font-size: 1.75rem;"></i>
              <h2 class="mb-0">Deliveries</h2>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="alert alert-info mb-4 py-3">
      <strong>Your role:</strong> You deliver orders from the restaurant (vendor) to the customer. When a restaurant marks an order as <strong>Ready</strong>, it appears under <strong>Available for delivery</strong>. Accept it to assign yourself, then mark it <strong>Delivered</strong> when done.
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">Loading...</p>
    </div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-else>
      <div class="row">
        <div class="col-lg-6 mb-4">
          <div class="card h-100">
            <div class="card-header bg-light">
              <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="ph-duotone ph-package"></i> My deliveries
              </h5>
              <small class="text-muted d-block mt-1">Orders you have accepted — pick up and deliver to the customer.</small>
            </div>
            <div class="card-body">
              <div v-if="!myDeliveries.length" class="text-muted text-center py-3">
                <i class="ph-duotone ph-package" style="font-size: 2rem; opacity: 0.5;"></i>
                <p class="mb-0 mt-2">No deliveries assigned to you.</p>
              </div>
              <div v-else class="list-group list-group-flush">
                <div
                  v-for="o in myDeliveries"
                  :key="o.id"
                  class="list-group-item d-flex justify-content-between align-items-start"
                >
                  <div>
                    <strong>{{ o.order_number }}</strong>
                    <br />
                    <small class="text-muted">{{ o.restaurant?.name }}</small>
                    <br />
                    <small>{{ o.delivery_address }}</small>
                    <br />
                    <span :class="orderStatusBadgeClass(o.status)">{{ formatStatus(o.status) }}</span>
                  </div>
                  <div>
                    <button
                      v-if="o.status === 'out_for_delivery'"
                      type="button"
                      class="btn btn-sm btn-success"
                      @click="updateStatus(o, 'delivered')"
                    >
                      <i class="ph-duotone ph-check-circle me-1"></i> Mark delivered
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary ms-1"
                      @click="openDetail(o)"
                    >
                      <i class="ph-duotone ph-eye me-1"></i> View
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6 mb-4">
          <div class="card h-100">
            <div class="card-header bg-light">
              <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="ph-duotone ph-clock-countdown"></i> Available for delivery
              </h5>
              <small class="text-muted d-block mt-1">Orders marked <strong>Ready</strong> by the restaurant — accept to assign yourself.</small>
            </div>
            <div class="card-body">
              <div v-if="!availableDeliveries.length" class="text-muted text-center py-3">
                <i class="ph-duotone ph-clock-countdown" style="font-size: 2rem; opacity: 0.5;"></i>
                <p class="mb-0 mt-2">No orders ready for pickup. Restaurants will appear here when they mark orders as Ready.</p>
              </div>
              <div v-else class="list-group list-group-flush">
                <div
                  v-for="o in availableDeliveries"
                  :key="o.id"
                  class="list-group-item d-flex justify-content-between align-items-start"
                >
                  <div>
                    <strong>{{ o.order_number }}</strong>
                    <br />
                    <small class="text-muted">{{ o.restaurant?.name }}</small>
                    <br />
                    <small>{{ o.delivery_address }}</small>
                    <br />
                    <span :class="orderStatusBadgeClass(o.status)">{{ formatStatus(o.status) }}</span>
                  </div>
                  <div>
                    <button
                      type="button"
                      class="btn btn-sm btn-primary"
                      @click="updateStatus(o, 'out_for_delivery')"
                    >
                      <i class="ph-duotone ph-plus-circle me-1"></i> Take delivery
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary ms-1"
                      @click="openDetail(o)"
                    >
                      <i class="ph-duotone ph-eye me-1"></i> View
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="detailModal" ref="detailModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center gap-2">
              <i class="ph-duotone ph-receipt"></i> Order {{ selectedOrder?.order_number }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="selectedOrder">
            <p class="mb-1"><strong>Restaurant:</strong> {{ selectedOrder.restaurant?.name }}</p>
            <p class="mb-1"><strong>Customer:</strong> {{ selectedOrder.user?.name }} — {{ selectedOrder.customer_phone || selectedOrder.user?.email }}</p>
            <p class="mb-2"><strong>Delivery address:</strong> {{ selectedOrder.delivery_address }}</p>
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Item</th>
                  <th class="text-end">Qty</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in selectedOrder.items" :key="item.id">
                  <td>{{ item.item_name }}</td>
                  <td class="text-end">{{ item.quantity }}</td>
                  <td class="text-end">{{ formatMoney(item.subtotal) }}</td>
                </tr>
              </tbody>
            </table>
            <div class="d-flex justify-content-end"><strong>Total: {{ formatMoney(selectedOrder.total) }}</strong></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { Modal } from 'bootstrap';
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
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
  name: 'RiderDeliveries',
  setup() {
    const store = useStore();
    const toast = useToast();
    const orders = ref([]);
    const loading = ref(false);
    const error = ref('');
    const selectedOrder = ref(null);
    const detailModal = ref(null);
    let detailModalBs = null;
    const userId = computed(() => store.state.profile?.id ?? null);

    const myDeliveries = computed(() =>
      orders.value.filter((o) => o.delivery_rider_id === userId.value && o.status !== 'delivered' && o.status !== 'cancelled')
    );
    const availableDeliveries = computed(() =>
      orders.value.filter((o) => !o.delivery_rider_id && o.status === 'ready')
    );

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
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
      API.orders
        .list({ per_page: 50 })
        .then((res) => {
          orders.value = res.data.data ?? [];
        })
        .catch((err) => {
          error.value = err.response?.data?.message ?? 'Failed to load deliveries.';
          toast.error(error.value);
        })
        .finally(() => (loading.value = false));
    }

    function updateStatus(order, status) {
      API.orders
        .updateStatus(order.id, status)
        .then(() => {
          toast.success(status === 'delivered' ? 'Marked as delivered.' : 'Delivery assigned to you.');
          loadOrders();
        })
        .catch((err) => toast.error(err.response?.data?.message ?? 'Failed to update.'));
    }

    function openDetail(order) {
      if (order.items?.length) {
        selectedOrder.value = order;
        detailModalBs = detailModalBs || new Modal(detailModal.value);
        detailModalBs.show();
        return;
      }
      API.orders.get(order.id).then((res) => {
        selectedOrder.value = res.data;
        detailModalBs = detailModalBs || new Modal(detailModal.value);
        detailModalBs.show();
      });
    }

    onMounted(() => loadOrders());

    return {
      orders,
      loading,
      error,
      myDeliveries,
      availableDeliveries,
      selectedOrder,
      detailModal,
      formatMoney,
      formatStatus,
      orderStatusBadgeClass,
      updateStatus,
      openDetail,
    };
  },
};
</script>

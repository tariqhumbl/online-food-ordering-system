<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/vendor/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Orders</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">Orders</h2>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">View and manage incoming orders.</p>

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
                <th>Customer</th>
                <th>Status</th>
                <th>Rider</th>
                <th>Total</th>
                <th>Date</th>
                <th width="180">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="o in orders" :key="o.id">
                <td><strong>{{ o.order_number }}</strong></td>
                <td>
                  {{ o.user?.name ?? '—' }}
                  <br /><small class="text-muted">{{ o.customer_phone || o.user?.email || '' }}</small>
                </td>
                <td>
                  <span :class="orderStatusBadgeClass(o.status)">{{ formatStatus(o.status) }}</span>
                </td>
                <td>
                  <template v-if="getRider(o)">
                    <i class="ph-duotone ph-package text-muted me-1"></i>{{ getRider(o).name }}
                  </template>
                  <span v-else class="text-muted">—</span>
                </td>
                <td>{{ formatMoney(o.total) }}</td>
                <td>{{ formatDate(o.created_at) }}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="openDetail(o)">
                    View
                  </button>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    @click="openStatusModal(o)"
                  >
                    Status
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="pagination.last_page > 1" class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }}</small>
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

    <!-- Detail modal -->
    <div class="modal fade" id="detailModal" ref="detailModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Order {{ selectedOrder?.order_number }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="selectedOrder">
            <!-- Order status track -->
            <div class="order-track mb-4">
              <div class="order-track-title mb-2">Order progress</div>
              <div class="order-track-steps">
                <div
                  v-for="step in statusSteps"
                  :key="step.value"
                  class="order-track-step"
                  :class="{ active: isStepReached(step.value, selectedOrder.status), current: selectedOrder.status === step.value }"
                >
                  <span class="order-track-dot"></span>
                  <span class="order-track-label">{{ step.label }}</span>
                  <span v-if="step.value !== statusSteps[statusSteps.length - 1].value" class="order-track-line"></span>
                </div>
              </div>
            </div>

            <p class="mb-1"><strong>Customer:</strong> {{ selectedOrder.user?.name }} ({{ selectedOrder.user?.email }})</p>
            <p class="mb-1"><strong>Phone:</strong> {{ selectedOrder.customer_phone || '—' }}</p>
            <p class="mb-2"><strong>Delivery address:</strong> {{ selectedOrder.delivery_address }}</p>
            <p class="mb-2"><strong>Notes:</strong> {{ selectedOrder.notes || '—' }}</p>

            <div v-if="getRider(selectedOrder)" class="mb-3 p-3 bg-light rounded">
              <strong class="d-block mb-1">Assigned rider</strong>
              <span><i class="ph-duotone ph-package me-1"></i>{{ getRider(selectedOrder).name }}</span>
              <span v-if="getRider(selectedOrder).email" class="text-muted ms-2 small">({{ getRider(selectedOrder).email }})</span>
            </div>

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
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" @click="openStatusModal(selectedOrder)">Update status</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Status modal -->
    <div class="modal fade" id="statusModal" ref="statusModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Update status</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="orderForStatus">
            <label class="form-label">New status</label>
            <select v-model="newStatus" class="form-select">
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="preparing">Preparing</option>
              <option value="ready">Ready</option>
              <option value="out_for_delivery">Out for delivery</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" :disabled="updating" @click="submitStatus">
              {{ updating ? 'Updating...' : 'Update' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { Modal } from 'bootstrap';
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

const STATUS_ORDER = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered'];
const statusSteps = STATUS_ORDER.map((value) => ({ value, label: STATUS_LABELS[value] }));

export default {
  name: 'VendorOrders',
  setup() {
    const toast = useToast();
    const orders = ref([]);
    const loading = ref(false);
    const error = ref('');
    const updating = ref(false);
    const selectedOrder = ref(null);
    const orderForStatus = ref(null);
    const newStatus = ref('pending');
    const detailModal = ref(null);
    const statusModal = ref(null);
    let detailModalBs = null;
    let statusModalBs = null;
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

    function getRider(order) {
      if (!order) return null;
      return order.delivery_rider ?? order.deliveryRider ?? null;
    }

    function isStepReached(stepValue, currentStatus) {
      if (!currentStatus || currentStatus === 'cancelled') return false;
      const currentIdx = STATUS_ORDER.indexOf(currentStatus);
      const stepIdx = STATUS_ORDER.indexOf(stepValue);
      return stepIdx >= 0 && currentIdx >= stepIdx;
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

    function openStatusModal(order) {
      if (!order) return;
      orderForStatus.value = order;
      newStatus.value = order.status || 'pending';
      detailModalBs?.hide();
      statusModalBs = statusModalBs || new Modal(statusModal.value);
      statusModalBs.show();
    }

    function submitStatus() {
      if (!orderForStatus.value) return;
      updating.value = true;
      API.orders
        .updateStatus(orderForStatus.value.id, newStatus.value)
        .then(() => {
          toast.success('Status updated.');
          statusModalBs.hide();
          orderForStatus.value = null;
          loadOrders();
          selectedOrder.value = null;
        })
        .catch((err) => toast.error(err.response?.data?.message ?? 'Failed to update.'))
        .finally(() => (updating.value = false));
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
      selectedOrder,
      orderForStatus,
      newStatus,
      updating,
      detailModal,
      statusModal,
      statusSteps,
      formatMoney,
      formatDate,
      formatStatus,
      orderStatusBadgeClass,
      getRider,
      isStepReached,
      loadOrders,
      resetFilters,
      goToPage,
      openDetail,
      openStatusModal,
      submitStatus,
    };
  },
};
</script>

<style scoped>
.order-track-title {
  font-weight: 600;
  font-size: 0.9rem;
}
.order-track-steps {
  display: flex;
  flex-wrap: wrap;
  gap: 0;
  align-items: flex-start;
}
.order-track-step {
  display: flex;
  align-items: center;
  position: relative;
}
.order-track-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: var(--pc-sidebar-border, #e9ecef);
  flex-shrink: 0;
  transition: background 0.2s;
}
.order-track-step.active .order-track-dot {
  background: var(--bs-primary, #0d6efd);
}
.order-track-step.current .order-track-dot {
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3);
}
.order-track-label {
  font-size: 0.8rem;
  margin-left: 6px;
  margin-right: 12px;
  color: var(--pc-sidebar-arrow, #6c757d);
  white-space: nowrap;
}
.order-track-step.active .order-track-label {
  color: var(--bs-body-color, #212529);
  font-weight: 500;
}
.order-track-step.current .order-track-label {
  font-weight: 600;
  color: var(--bs-primary, #0d6efd);
}
.order-track-line {
  width: 24px;
  height: 2px;
  background: var(--pc-sidebar-border, #e9ecef);
  margin-right: 4px;
}
.order-track-step.active .order-track-line {
  background: var(--bs-primary, #0d6efd);
}
</style>

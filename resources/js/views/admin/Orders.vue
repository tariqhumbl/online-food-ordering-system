<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-md-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
              <li class="breadcrumb-item" aria-current="page">Orders</li>
            </ul>
          </div>
          <div class="col-md-12">
            <div class="page-header-title">
              <h2 class="mb-0">Orders</h2>
            </div>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">View and manage all food orders.</p>

    <!-- Filters -->
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

    <!-- Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Loading orders...</p>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
        <div v-else-if="!orders.length" class="text-center py-5 text-muted">
          No orders found.
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Restaurant</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Total</th>
                <th>Date</th>
                <th width="120">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="o in orders" :key="o.id">
                <td><strong>{{ o.order_number }}</strong></td>
                <td>{{ o.restaurant ? o.restaurant.name : '—' }}</td>
                <td>
                  {{ o.user ? o.user.name : '—' }}
                  <br /><small class="text-muted">{{ o.customer_phone || (o.user && o.user.email) || '' }}</small>
                </td>
                <td>
                  <span :class="orderStatusBadgeClass(o.status)">{{ formatStatus(o.status) }}</span>
                </td>
                <td>{{ formatMoney(o.total) }}</td>
                <td>{{ formatDate(o.created_at) }}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="openDetailModal(o)">
                    View
                  </button>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    @click="openStatusModal(o)"
                    :title="'Update status'"
                  >
                    Status
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
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
                class="page-item"
                :class="{ active: pagination.current_page === p }"
                v-for="p in pageNumbers"
                :key="p"
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

    <!-- Order detail modal -->
    <div class="modal fade" id="detailModal" ref="detailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Order {{ selectedOrder?.order_number }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="selectedOrder">
            <div class="row mb-3">
              <div class="col-md-6">
                <p class="mb-1"><strong>Restaurant:</strong> {{ selectedOrder.restaurant?.name }}</p>
                <p class="mb-1"><strong>Customer:</strong> {{ selectedOrder.user?.name }} ({{ selectedOrder.user?.email }})</p>
                <p class="mb-1"><strong>Phone:</strong> {{ selectedOrder.customer_phone || '—' }}</p>
              </div>
              <div class="col-md-6">
                <p class="mb-1"><strong>Status:</strong> <span :class="orderStatusBadgeClass(selectedOrder.status)">{{ formatStatus(selectedOrder.status) }}</span></p>
                <p class="mb-1"><strong>Delivery address:</strong> {{ selectedOrder.delivery_address || '—' }}</p>
                <p class="mb-1"><strong>Date:</strong> {{ formatDate(selectedOrder.created_at) }}</p>
              </div>
            </div>
            <div v-if="selectedOrder.notes" class="mb-3">
              <strong>Notes:</strong> {{ selectedOrder.notes }}
            </div>
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Item</th>
                  <th class="text-end">Qty</th>
                  <th class="text-end">Unit price</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in selectedOrder.items" :key="item.id">
                  <td>{{ item.item_name }}</td>
                  <td class="text-end">{{ item.quantity }}</td>
                  <td class="text-end">{{ formatMoney(item.unit_price) }}</td>
                  <td class="text-end">{{ formatMoney(item.subtotal) }}</td>
                </tr>
              </tbody>
            </table>
            <div class="d-flex justify-content-end gap-3 mt-2">
              <span>Subtotal: {{ formatMoney(selectedOrder.subtotal) }}</span>
              <span>Delivery: {{ formatMoney(selectedOrder.delivery_fee) }}</span>
              <span><strong>Total: {{ formatMoney(selectedOrder.total) }}</strong></span>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" @click="openStatusModal(selectedOrder)">Update status</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Update status modal -->
    <div class="modal fade" id="statusModal" ref="statusModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Update order status</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="orderForStatus">
            <p class="mb-2">Order <strong>{{ orderForStatus.order_number }}</strong></p>
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

export default {
  name: 'AdminOrders',
  setup() {
    const toast = useToast();
    const detailModal = ref(null);
    const statusModal = ref(null);
    let detailModalBs = null;
    let statusModalBs = null;

    const orders = ref([]);
    const loading = ref(false);
    const error = ref('');
    const updating = ref(false);
    const selectedOrder = ref(null);
    const orderForStatus = ref(null);
    const newStatus = ref('pending');

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

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }

    function formatDate(val) {
      if (!val) return '—';
      return new Date(val).toLocaleString();
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
      return 'badge ' + (map[status] || 'badge bg-light text-dark');
    }

    function loadOrders() {
      loading.value = true;
      error.value = '';
      const params = { page: pagination.current_page, per_page: 15 };
      if (filters.status) params.status = filters.status;
      API.orders
        .list(params)
        .then((res) => {
          orders.value = res.data.data || [];
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
          error.value = err.response?.data?.message || 'Failed to load orders.';
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

    const pageNumbers = ref([]);
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

    function openDetailModal(order) {
      if (order.id === selectedOrder.value?.id && selectedOrder.value?.items) {
        selectedOrder.value = order;
        detailModalBs = detailModalBs || new Modal(detailModal.value);
        detailModalBs.show();
        return;
      }
      selectedOrder.value = order;
      if (!order.items || order.items.length === 0) {
        API.orders.get(order.id).then((res) => {
          selectedOrder.value = res.data;
          detailModalBs = detailModalBs || new Modal(detailModal.value);
          detailModalBs.show();
        });
      } else {
        detailModalBs = detailModalBs || new Modal(detailModal.value);
        detailModalBs.show();
      }
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
      const orderId = orderForStatus.value.id;
      API.orders
        .updateStatus(orderId, newStatus.value)
        .then(() => {
          toast.success('Order status updated.');
          statusModalBs.hide();
          orderForStatus.value = null;
          if (selectedOrder.value?.id === orderId) {
            selectedOrder.value = null;
          }
          loadOrders();
        })
        .catch((err) => {
          toast.error(err.response?.data?.message || 'Failed to update status.');
        })
        .finally(() => (updating.value = false));
    }

    onMounted(() => {
      loadOrders();
    });

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
      loadOrders,
      resetFilters,
      goToPage,
      openDetailModal,
      openStatusModal,
      submitStatus,
      formatMoney,
      formatDate,
      formatStatus,
      orderStatusBadgeClass,
    };
  },
};
</script>

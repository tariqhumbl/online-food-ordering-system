<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-md-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
              <li class="breadcrumb-item" aria-current="page">Restaurants</li>
            </ul>
          </div>
          <div class="col-md-12">
            <div class="page-header-title d-flex flex-wrap align-items-center justify-content-between gap-2">
              <h2 class="mb-0">Restaurants</h2>
              <button type="button" class="btn btn-primary" @click="openAddModal">
                <i class="ph-duotone ph-plus me-1"></i> Add Restaurant
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">Manage all restaurants in the system.</p>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Search</label>
            <input
              v-model="filters.search"
              type="text"
              class="form-control"
              placeholder="Name, address, email..."
              @input="debouncedLoad"
            />
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select v-model="filters.status" class="form-select" @change="loadRestaurants">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending</option>
            </select>
          </div>
          <div class="col-md-2 d-flex align-items-end">
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
          <p class="mt-2 text-muted">Loading restaurants...</p>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
        <div v-else-if="!restaurants.length" class="text-center py-5 text-muted">
          No restaurants found.
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Delivery fee</th>
                <th>Owner</th>
                <th width="140">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in restaurants" :key="r.id">
                <td>
                  <strong>{{ r.name }}</strong>
                  <br /><small class="text-muted">{{ r.slug }}</small>
                </td>
                <td>{{ r.address || '—' }}</td>
                <td>{{ r.phone || '—' }}</td>
                <td>
                  <span :class="statusBadgeClass(r.status)">{{ r.status }}</span>
                </td>
                <td>{{ r.delivery_fee != null ? formatMoney(r.delivery_fee) : '—' }}</td>
                <td>{{ r.user ? r.user.name : '—' }}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="openEditModal(r)">
                    Edit
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-danger" @click="confirmDelete(r)">
                    Delete
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
              <li class="page-item" :class="{ active: pagination.current_page === p }" v-for="p in pageNumbers" :key="p">
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

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="restaurantModal" ref="restaurantModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit Restaurant' : 'Add Restaurant' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="saveRestaurant">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Name <span class="text-danger">*</span></label>
                  <input v-model="form.name" type="text" class="form-control" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Slug <span class="text-danger">*</span></label>
                  <input v-model="form.slug" type="text" class="form-control" required />
                </div>
                <template v-if="!isEditing">
                  <div class="col-12 mt-2">
                    <hr class="my-2" />
                    <h6 class="text-muted mb-2">Manager account (credentials will be sent by email)</h6>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Manager name <span class="text-danger">*</span></label>
                    <input v-model="form.manager_name" type="text" class="form-control" required />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Manager email <span class="text-danger">*</span></label>
                    <input v-model="form.manager_email" type="email" class="form-control" required placeholder="Login email for manager" />
                  </div>
                </template>
                <div class="col-12">
                  <label class="form-label">Address</label>
                  <input v-model="form.address" type="text" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <input v-model="form.phone" type="text" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input v-model="form.email" type="email" class="form-control" />
                </div>
                <div class="col-12" v-if="isEditing">
                  <label class="form-label">Status</label>
                  <select v-model="form.status" class="form-select">
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Delivery fee</label>
                  <input v-model.number="form.delivery_fee" type="number" step="0.01" min="0" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Est. delivery (minutes)</label>
                  <input v-model.number="form.estimated_delivery_minutes" type="number" min="0" class="form-control" />
                </div>
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea v-model="form.description" class="form-control" rows="2"></textarea>
                </div>
              </div>
              <div v-if="formError" class="alert alert-danger mt-3 mb-0">{{ formError }}</div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete confirm modal -->
    <div class="modal fade" id="deleteModal" ref="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Restaurant</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete <strong>{{ restaurantToDelete?.name }}</strong>? This cannot be undone.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" :disabled="deleting" @click="performDelete">
              {{ deleting ? 'Deleting...' : 'Delete' }}
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

const defaultForm = () => ({
  name: '',
  slug: '',
  address: '',
  phone: '',
  email: '',
  description: '',
  status: 'pending',
  delivery_fee: null,
  estimated_delivery_minutes: null,
  manager_name: '',
  manager_email: '',
});

export default {
  name: 'AdminRestaurants',
  setup() {
    const toast = useToast();
    const restaurantModal = ref(null);
    const deleteModal = ref(null);
    let modalBs = null;
    let deleteModalBs = null;

    const restaurants = ref([]);
    const loading = ref(false);
    const error = ref('');
    const saving = ref(false);
    const deleting = ref(false);
    const formError = ref('');
    const isEditing = ref(false);
    const editingId = ref(null);
    const restaurantToDelete = ref(null);

    const filters = reactive({
      search: '',
      status: '',
    });
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      from: 0,
      to: 0,
      total: 0,
      prev_page_url: null,
      next_page_url: null,
    });
    const form = reactive(defaultForm());

    function slugify(text) {
      return String(text)
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-');
    }

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }

    function statusBadgeClass(status) {
      const map = { active: 'badge bg-success', inactive: 'badge bg-secondary', pending: 'badge bg-warning' };
      return 'badge ' + (map[status] || 'badge bg-light text-dark');
    }

    function loadRestaurants() {
      loading.value = true;
      error.value = '';
      const params = { page: pagination.current_page, per_page: 15 };
      if (filters.search) params.search = filters.search;
      if (filters.status) params.status = filters.status;
      API.admin.restaurants
        .list(params)
        .then((res) => {
          restaurants.value = res.data.data || [];
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
          error.value = err.response?.data?.message || 'Failed to load restaurants.';
          toast.error(error.value);
        })
        .finally(() => (loading.value = false));
    }

    let debounceTimer = null;
    function debouncedLoad() {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        pagination.current_page = 1;
        loadRestaurants();
      }, 300);
    }

    function resetFilters() {
      filters.search = '';
      filters.status = '';
      pagination.current_page = 1;
      loadRestaurants();
    }

    function goToPage(page) {
      if (page < 1 || page > pagination.last_page) return;
      pagination.current_page = page;
      loadRestaurants();
    }

    const pageNumbers = ref([]);
    watch(
      () => [pagination.current_page, pagination.last_page],
      () => {
        const cur = pagination.current_page;
        const last = pagination.last_page;
        const pages = [];
        let start = Math.max(1, cur - 2);
        let end = Math.min(last, cur + 2);
        for (let i = start; i <= end; i++) pages.push(i);
        pageNumbers.value = pages;
      },
      { immediate: true }
    );

    function openAddModal() {
      isEditing.value = false;
      editingId.value = null;
      Object.assign(form, defaultForm());
      formError.value = '';
      modalBs = modalBs || new Modal(restaurantModal.value);
      modalBs.show();
    }

    function openEditModal(r) {
      isEditing.value = true;
      editingId.value = r.id;
      Object.assign(form, {
        name: r.name,
        slug: r.slug,
        address: r.address || '',
        phone: r.phone || '',
        email: r.email || '',
        description: r.description || '',
        status: r.status || 'pending',
        delivery_fee: r.delivery_fee != null ? r.delivery_fee : null,
        estimated_delivery_minutes: r.estimated_delivery_minutes != null ? r.estimated_delivery_minutes : null,
        manager_name: '',
        manager_email: '',
      });
      formError.value = '';
      modalBs = modalBs || new Modal(restaurantModal.value);
      modalBs.show();
    }

    watch(
      () => form.name,
      (name) => {
        if (!isEditing.value && name) form.slug = slugify(name);
      }
    );

    function saveRestaurant() {
      formError.value = '';
      saving.value = true;
      const payload = {
        name: form.name,
        slug: form.slug,
        address: form.address || null,
        phone: form.phone || null,
        email: form.email || null,
        description: form.description || null,
        delivery_fee: form.delivery_fee != null && form.delivery_fee !== '' ? form.delivery_fee : null,
        estimated_delivery_minutes:
          form.estimated_delivery_minutes != null && form.estimated_delivery_minutes !== ''
            ? form.estimated_delivery_minutes
            : null,
      };
      if (isEditing.value) {
        payload.status = form.status;
      } else {
        payload.manager_name = form.manager_name?.trim() || '';
        payload.manager_email = form.manager_email?.trim() || '';
      }

      const promise = isEditing.value
        ? API.restaurants.update(editingId.value, payload)
        : API.admin.restaurants.create(payload);

      promise
        .then(() => {
          toast.success(isEditing.value ? 'Restaurant updated.' : 'Restaurant created. Manager will receive login credentials by email.');
          modalBs.hide();
          loadRestaurants();
        })
        .catch((err) => {
          formError.value = err.response?.data?.message || Object.values(err.response?.data?.errors || {}).flat().join(' ') || 'Failed to save.';
        })
        .finally(() => (saving.value = false));
    }

    function confirmDelete(r) {
      restaurantToDelete.value = r;
      deleteModalBs = deleteModalBs || new Modal(deleteModal.value);
      deleteModalBs.show();
    }

    function performDelete() {
      if (!restaurantToDelete.value) return;
      deleting.value = true;
      API.restaurants
        .delete(restaurantToDelete.value.id)
        .then(() => {
          toast.success('Restaurant deleted.');
          deleteModalBs.hide();
          restaurantToDelete.value = null;
          loadRestaurants();
        })
        .catch((err) => {
          toast.error(err.response?.data?.message || 'Failed to delete.');
        })
        .finally(() => (deleting.value = false));
    }

    onMounted(() => {
      loadRestaurants();
    });

    return {
      restaurants,
      loading,
      error,
      filters,
      pagination,
      pageNumbers,
      form,
      isEditing,
      formError,
      saving,
      restaurantModal,
      deleteModal,
      restaurantToDelete,
      deleting,
      openAddModal,
      openEditModal,
      saveRestaurant,
      resetFilters,
      loadRestaurants,
      goToPage,
      debouncedLoad,
      confirmDelete,
      performDelete,
      formatMoney,
      statusBadgeClass,
    };
  },
};
</script>

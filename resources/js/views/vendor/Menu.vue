<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/vendor/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Menu</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">Menu Management</h2>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">Manage menu categories and items.</p>

    <div v-if="!restaurantId" class="alert alert-warning">
      Create your restaurant first in <router-link to="/vendor/restaurant">My Restaurant</router-link>.
    </div>

    <div v-else-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">Loading menu...</p>
    </div>

    <div v-else>
      <div class="mb-4">
        <button type="button" class="btn btn-primary" @click="openCategoryModal()">
          <i class="ph-duotone ph-plus me-1"></i> Add category
        </button>
      </div>

      <div v-for="cat in categories" :key="cat.id" class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">{{ cat.name }}</h5>
          <div>
            <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="openCategoryModal(cat)">
              Edit
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger me-1" @click="deleteCategory(cat)">
              Delete
            </button>
            <button type="button" class="btn btn-sm btn-success" @click="openItemModal(null, cat)">
              Add item
            </button>
          </div>
        </div>
        <div class="card-body">
          <div v-if="!cat.menu_items?.length" class="text-muted small">No items yet.</div>
          <div v-else class="table-responsive">
            <table class="table table-sm table-hover table-menu-align">
              <thead>
                <tr>
                  <th style="width: 60px;">Image</th>
                  <th>Name</th>
                  <th>Description</th>
                  <th>Price</th>
                  <th>Available</th>
                  <th width="120">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in cat.menu_items" :key="item.id">
                  <td>
                    <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="rounded d-block mx-auto" style="width: 48px; height: 48px; object-fit: cover;" />
                    <span v-else class="text-muted small">—</span>
                  </td>
                  <td>{{ item.name }}</td>
                  <td class="small text-muted">{{ item.description || '—' }}</td>
                  <td>{{ formatMoney(item.price) }}</td>
                  <td>
                    <span :class="item.is_available ? 'badge bg-success' : 'badge bg-secondary'">
                      {{ item.is_available ? 'Yes' : 'No' }}
                    </span>
                  </td>
                  <td>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="openItemModal(item, cat)">
                      Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="deleteItem(item)">
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Category modal -->
    <div class="modal fade" id="categoryModal" ref="categoryModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingCategory ? 'Edit category' : 'Add category' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="saveCategory">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input v-model="categoryForm.name" type="text" class="form-control" required />
              </div>
              <div class="mb-0">
                <label class="form-label">Description</label>
                <textarea v-model="categoryForm.description" class="form-control" rows="2"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Item modal -->
    <div class="modal fade" id="itemModal" ref="itemModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingItem ? 'Edit item' : 'Add item' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="saveItem">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input v-model="itemForm.name" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea v-model="itemForm.description" class="form-control" rows="2"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Price <span class="text-danger">*</span></label>
                <input v-model.number="itemForm.price" type="number" step="0.01" min="0" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Image</label>
                <input
                  type="file"
                  ref="itemImageInput"
                  accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                  class="form-control"
                  @change="onItemImageChange"
                />
                <small class="text-muted">Optional. JPEG, PNG, GIF or WebP, max 2MB. Shows on the landing page.</small>
                <div v-if="itemForm.imagePreview" class="mt-2">
                  <img :src="itemForm.imagePreview" alt="Preview" class="rounded" style="max-height: 80px; max-width: 120px; object-fit: cover;" />
                </div>
                <div v-else-if="editingItem?.image_url" class="mt-2">
                  <span class="small text-muted">Current:</span>
                  <img :src="editingItem.image_url" alt="Current" class="rounded ms-1" style="max-height: 60px; max-width: 100px; object-fit: cover;" />
                </div>
              </div>
              <div class="mb-0">
                <div class="form-check">
                  <input v-model="itemForm.is_available" type="checkbox" class="form-check-input" id="itemAvailable" />
                  <label class="form-check-label" for="itemAvailable">Available</label>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { Modal } from 'bootstrap';
import { ref, reactive, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useToast } from 'vue-toastification';

export default {
  name: 'VendorMenu',
  setup() {
    const store = useStore();
    const toast = useToast();
    const categories = ref([]);
    const loading = ref(false);
    const saving = ref(false);
    const categoryModal = ref(null);
    const itemModal = ref(null);
    let categoryModalBs = null;
    let itemModalBs = null;
    const editingCategory = ref(null);
    const editingItem = ref(null);
    const itemCategory = ref(null);

    const restaurantId = computed(() => store.state.profile?.restaurant_id ?? null);

    const categoryForm = reactive({ name: '', description: '' });
    const itemImageInput = ref(null);
    const itemForm = reactive({
      name: '',
      description: '',
      price: null,
      is_available: true,
      imagePreview: '',
      imageFile: null,
    });

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }

    function loadMenu() {
      if (!restaurantId.value) return;
      loading.value = true;
      API.menu
        .categories(restaurantId.value)
        .then((res) => {
          categories.value = res.data ?? [];
        })
        .catch(() => toast.error('Failed to load menu.'))
        .finally(() => (loading.value = false));
    }

    function openCategoryModal(cat = null) {
      editingCategory.value = cat;
      categoryForm.name = cat?.name ?? '';
      categoryForm.description = cat?.description ?? '';
      categoryModalBs = categoryModalBs || new Modal(categoryModal.value);
      categoryModalBs.show();
    }

    function saveCategory() {
      saving.value = true;
      const payload = { name: categoryForm.name, description: categoryForm.description || null };
      const promise = editingCategory.value
        ? API.menu.updateCategory(restaurantId.value, editingCategory.value.id, payload)
        : API.menu.createCategory(restaurantId.value, payload);
      promise
        .then(() => {
          toast.success(editingCategory.value ? 'Category updated.' : 'Category added.');
          categoryModalBs.hide();
          loadMenu();
        })
        .catch((err) => toast.error(err.response?.data?.message ?? 'Failed to save.'))
        .finally(() => (saving.value = false));
    }

    function deleteCategory(cat) {
      if (!confirm(`Delete category "${cat.name}" and all its items?`)) return;
      API.menu
        .deleteCategory(restaurantId.value, cat.id)
        .then(() => {
          toast.success('Category deleted.');
          loadMenu();
        })
        .catch(() => toast.error('Failed to delete.'));
    }

    function onItemImageChange(e) {
      const file = e.target.files?.[0];
      itemForm.imageFile = file || null;
      itemForm.imagePreview = '';
      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = () => (itemForm.imagePreview = reader.result);
        reader.readAsDataURL(file);
      }
    }

    function openItemModal(item = null, cat = null) {
      editingItem.value = item;
      itemCategory.value = cat;
      itemForm.name = item?.name ?? '';
      itemForm.description = item?.description ?? '';
      itemForm.price = item?.price != null ? item.price : null;
      itemForm.is_available = item?.is_available ?? true;
      itemForm.imagePreview = '';
      itemForm.imageFile = null;
      if (itemImageInput.value) itemImageInput.value.value = '';
      itemModalBs = itemModalBs || new Modal(itemModal.value);
      itemModalBs.show();
    }

    function saveItem() {
      saving.value = true;
      const payload = {
        name: itemForm.name,
        description: itemForm.description || null,
        price: itemForm.price,
        is_available: !!itemForm.is_available,
      };
      const imageFile = itemForm.imageFile || null;
      const promise = editingItem.value
        ? API.menu.updateItem(restaurantId.value, editingItem.value.id, payload, imageFile)
        : API.menu.createItem(restaurantId.value, itemCategory.value.id, payload, imageFile);
      promise
        .then(() => {
          toast.success(editingItem.value ? 'Item updated.' : 'Item added.');
          itemModalBs.hide();
          loadMenu();
        })
        .catch((err) => toast.error(err.response?.data?.message ?? 'Failed to save.'))
        .finally(() => (saving.value = false));
    }

    function deleteItem(item) {
      if (!confirm(`Delete "${item.name}"?`)) return;
      API.menu
        .deleteItem(restaurantId.value, item.id)
        .then(() => {
          toast.success('Item deleted.');
          loadMenu();
        })
        .catch(() => toast.error('Failed to delete.'));
    }

    onMounted(() => loadMenu());

    return {
      categories,
      loading,
      saving,
      restaurantId,
      categoryForm,
      itemForm,
      itemImageInput,
      editingCategory,
      editingItem,
      categoryModal,
      itemModal,
      formatMoney,
      onItemImageChange,
      openCategoryModal,
      saveCategory,
      deleteCategory,
      openItemModal,
      saveItem,
      deleteItem,
    };
  },
};
</script>

<style scoped>
.table-menu-align th,
.table-menu-align td {
  text-align: center;
  vertical-align: middle;
}
.table-menu-align td:last-child {
  text-align: center;
}
</style>

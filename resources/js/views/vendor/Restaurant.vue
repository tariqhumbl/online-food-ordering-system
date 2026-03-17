<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/vendor/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">My Restaurant</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">My Restaurant</h2>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">Restaurant details and settings.</p>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">Loading...</p>
    </div>

    <div v-else-if="!hasRestaurant && !showCreate" class="card">
      <div class="card-body text-center py-5">
        <p class="text-muted mb-3">You don't have a restaurant yet.</p>
        <button type="button" class="btn btn-primary" @click="showCreate = true">Create restaurant</button>
      </div>
    </div>

    <div v-else class="card">
      <div class="card-header">
        <h5 class="mb-0">{{ hasRestaurant ? 'Edit restaurant' : 'Create restaurant' }}</h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="save">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <input v-model="form.name" type="text" class="form-control" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">Slug <span class="text-danger">*</span></label>
              <input v-model="form.slug" type="text" class="form-control" required />
            </div>
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
          <div class="mt-3">
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Saving...' : (hasRestaurant ? 'Update' : 'Create') }}
            </button>
            <button v-if="showCreate && !hasRestaurant" type="button" class="btn btn-secondary ms-2" @click="showCreate = false">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { http } from '@/services/http_service';
import * as authService from '@/services/auth_service';
import { ref, reactive, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useToast } from 'vue-toastification';

function slugify(text) {
  return String(text).toLowerCase().trim().replace(/\s+/g, '-').replace(/[^\w\-]+/g, '').replace(/\-\-+/g, '-');
}

export default {
  name: 'VendorRestaurant',
  setup() {
    const store = useStore();
    const toast = useToast();
    const restaurant = ref(null);
    const loading = ref(true);
    const saving = ref(false);
    const formError = ref('');
    const showCreate = ref(false);

    const form = reactive({
      name: '',
      slug: '',
      address: '',
      phone: '',
      email: '',
      description: '',
      delivery_fee: null,
      estimated_delivery_minutes: null,
    });

    const hasRestaurant = computed(() => restaurant.value != null);
    const restaurantId = computed(() => store.state.profile?.restaurant_id ?? null);

    function loadRestaurant() {
      const rid = restaurantId.value;
      if (!rid) {
        loading.value = false;
        return;
      }
      API.restaurants
        .get(rid)
        .then((res) => {
          restaurant.value = res.data;
          form.name = res.data.name ?? '';
          form.slug = res.data.slug ?? '';
          form.address = res.data.address ?? '';
          form.phone = res.data.phone ?? '';
          form.email = res.data.email ?? '';
          form.description = res.data.description ?? '';
          form.delivery_fee = res.data.delivery_fee ?? null;
          form.estimated_delivery_minutes = res.data.estimated_delivery_minutes ?? null;
        })
        .catch(() => {
          restaurant.value = null;
        })
        .finally(() => (loading.value = false));
    }

    function refetchProfile() {
      return http()
        .get('/api/auth/profile')
        .then((res) => {
          const user = res.data;
          localStorage.setItem('user', JSON.stringify(user));
          store.commit('SET_PROFILE', user);
        });
    }

    function save() {
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
      const promise = hasRestaurant.value
        ? API.restaurants.update(restaurant.value.id, payload)
        : API.restaurants.create(payload);

      promise
        .then((res) => {
          toast.success(hasRestaurant.value ? 'Restaurant updated.' : 'Restaurant created.');
          if (!hasRestaurant.value) {
            restaurant.value = res.data;
            form.name = res.data.name ?? '';
            form.slug = res.data.slug ?? '';
            form.address = res.data.address ?? '';
            form.phone = res.data.phone ?? '';
            form.email = res.data.email ?? '';
            form.description = res.data.description ?? '';
            form.delivery_fee = res.data.delivery_fee ?? null;
            form.estimated_delivery_minutes = res.data.estimated_delivery_minutes ?? null;
            showCreate.value = false;
            const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
            currentUser.restaurant_id = res.data.id;
            localStorage.setItem('user', JSON.stringify(currentUser));
            store.commit('SET_PROFILE', currentUser);
          } else {
            restaurant.value = { ...restaurant.value, ...payload };
            showCreate.value = false;
          }
        })
        .catch((err) => {
          formError.value =
            err.response?.data?.message ||
            Object.values(err.response?.data?.errors || {}).flat().join(' ') ||
            'Failed to save.';
        })
        .finally(() => (saving.value = false));
    }

    onMounted(() => {
      if (restaurantId.value) {
        loadRestaurant();
      } else {
        loading.value = false;
      }
    });

    return {
      restaurant,
      loading,
      saving,
      form,
      formError,
      showCreate,
      hasRestaurant,
      save,
    };
  },
};
</script>

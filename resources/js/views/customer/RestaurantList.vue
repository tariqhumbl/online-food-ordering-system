<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/customer/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Restaurants</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">Restaurants</h2>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">Browse restaurants and place orders.</p>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">Loading restaurants...</p>
    </div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-else-if="!restaurants.length" class="card">
      <div class="card-body text-center py-5 text-muted">No restaurants available at the moment.</div>
    </div>
    <div v-else class="row g-4">
      <div
        v-for="r in restaurants"
        :key="r.id"
        class="col-md-6 col-lg-4"
      >
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">{{ r.name }}</h5>
            <p class="card-text text-muted small mb-2">{{ r.description || 'No description.' }}</p>
            <p class="card-text small mb-1">
              <i class="ph-duotone ph-map-pin me-1"></i>{{ r.address || '—' }}
            </p>
            <p class="card-text small mb-2">
              <i class="ph-duotone ph-truck me-1"></i>
              Delivery: {{ r.delivery_fee != null ? formatMoney(r.delivery_fee) : 'Free' }}
              <span v-if="r.estimated_delivery_minutes" class="ms-1">· {{ r.estimated_delivery_minutes }} min</span>
            </p>
            <router-link
              :to="`/customer/restaurants/${r.id}`"
              class="btn btn-primary btn-sm"
            >
              View menu
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <nav v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-4">
      <ul class="pagination mb-0">
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
</template>

<script>
import API from '@/services/api_service';
import { ref, reactive, onMounted, watch } from 'vue';
import { useToast } from 'vue-toastification';

export default {
  name: 'CustomerRestaurantList',
  setup() {
    const toast = useToast();
    const restaurants = ref([]);
    const loading = ref(false);
    const error = ref('');
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      prev_page_url: null,
      next_page_url: null,
    });
    const pageNumbers = ref([]);

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }

    function load() {
      loading.value = true;
      error.value = '';
      API.restaurants
        .list({ page: pagination.current_page })
        .then((res) => {
          restaurants.value = res.data.data ?? [];
          pagination.current_page = res.data.current_page ?? 1;
          pagination.last_page = res.data.last_page ?? 1;
          pagination.prev_page_url = res.data.prev_page_url ?? null;
          pagination.next_page_url = res.data.next_page_url ?? null;
        })
        .catch((err) => {
          error.value = err.response?.data?.message ?? 'Failed to load restaurants.';
          toast.error(error.value);
        })
        .finally(() => (loading.value = false));
    }

    function goToPage(page) {
      if (page < 1 || page > pagination.last_page) return;
      pagination.current_page = page;
      load();
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

    onMounted(() => load());

    return {
      restaurants,
      loading,
      error,
      pagination,
      pageNumbers,
      formatMoney,
      goToPage,
    };
  },
};
</script>

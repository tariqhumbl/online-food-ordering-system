<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/customer/dashboard">Home</router-link></li>
              <li class="breadcrumb-item"><router-link to="/customer/restaurants">Restaurants</router-link></li>
              <li class="breadcrumb-item" aria-current="page">{{ restaurant?.name ?? 'Menu' }}</li>
            </ul>
          </div>
          <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h2 class="mb-0">{{ restaurant?.name ?? 'Menu' }}</h2>
            <div>
              <router-link to="/customer/cart" class="btn btn-outline-primary btn-sm me-2">
                <i class="ph-duotone ph-shopping-cart"></i> Cart ({{ cartItemCount }})
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">Loading menu...</p>
    </div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-else-if="!restaurant" class="alert alert-warning">Restaurant not found.</div>
    <div v-else>
      <p class="text-muted mb-4">{{ restaurant.description || 'Choose items below.' }}</p>

      <div
        v-for="cat in menuCategories"
        :key="cat.id"
        class="card mb-4"
      >
        <div class="card-header">
          <h5 class="mb-0">{{ cat.name }}</h5>
          <small v-if="cat.description" class="text-muted">{{ cat.description }}</small>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div
              v-for="item in cat.menu_items"
              :key="item.id"
              class="col-12 col-md-6"
            >
              <div class="d-flex justify-content-between align-items-start border rounded p-3">
                <div class="flex-grow-1">
                  <h6 class="mb-1">{{ item.name }}</h6>
                  <p v-if="item.description" class="small text-muted mb-2">{{ item.description }}</p>
                  <span class="fw-semibold">{{ formatMoney(item.price) }}</span>
                </div>
                <div class="ms-2">
                  <button
                    v-if="!item.is_available"
                    type="button"
                    class="btn btn-sm btn-secondary"
                    disabled
                  >
                    Unavailable
                  </button>
                  <button
                    v-else
                    type="button"
                    class="btn btn-sm btn-primary"
                    @click="addToCart(item)"
                  >
                    Add to cart
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="replaceCartModal" ref="replaceCartModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Replace cart?</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Your cart has items from <strong>{{ cartRestaurantName }}</strong>. Add items from
            <strong>{{ restaurant?.name }}</strong> instead? This will clear your current cart.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="confirmReplaceCart">Replace cart</button>
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
import { useStore } from 'vuex';
import { useToast } from 'vue-toastification';
import { Modal } from 'bootstrap';

export default {
  name: 'CustomerRestaurantMenu',
  setup() {
    const route = useRoute();
    const store = useStore();
    const toast = useToast();
    const restaurant = ref(null);
    const loading = ref(false);
    const error = ref('');
    const replaceCartModal = ref(null);
    let pendingItem = null;
    let replaceModalBs = null;

    const cartRestaurantId = computed(() => store.getters.cartRestaurantId);
    const cartRestaurantName = computed(() => store.getters.cartRestaurantName);
    const cartItemCount = computed(() => store.getters.cartItemCount);

    const menuCategories = computed(() => {
      if (!restaurant.value?.menu_categories) return [];
      return restaurant.value.menu_categories;
    });

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }

    function addToCart(item) {
      const rid = Number(route.params.id);
      const rname = restaurant.value?.name ?? '';

      if (store.getters.cartItemCount > 0 && store.getters.cartRestaurantId !== rid) {
        pendingItem = item;
        replaceModalBs = replaceModalBs || new Modal(replaceCartModal.value);
        replaceModalBs.show();
        return;
      }
      if (store.getters.cartItemCount === 0) {
        store.commit('CART_SET_RESTAURANT', { id: rid, name: rname });
      }
      store.commit('CART_ADD_ITEM', { menuItem: item, quantity: 1 });
      store.commit('OPEN_CART_SIDEBAR');
      toast.success(`"${item.name}" added to cart`);
    }

    function confirmReplaceCart() {
      if (!pendingItem || !restaurant.value) return;
      replaceModalBs?.hide();
      const rid = Number(route.params.id);
      const rname = restaurant.value.name;
      store.commit('CART_SET', {
        restaurantId: rid,
        restaurantName: rname,
        items: [
          {
            menu_item_id: pendingItem.id,
            name: pendingItem.name,
            price: Number(pendingItem.price),
            quantity: 1,
            image_url: pendingItem.image_url ?? null,
          },
        ],
      });
      store.commit('OPEN_CART_SIDEBAR');
      toast.success(`Cart replaced. "${pendingItem.name}" added.`);
      pendingItem = null;
    }

    onMounted(() => {
      const id = route.params.id;
      if (!id) return;
      loading.value = true;
      API.restaurants
        .get(id)
        .then((res) => {
          restaurant.value = res.data;
        })
        .catch((err) => {
          error.value = err.response?.data?.message ?? 'Failed to load menu.';
          toast.error(error.value);
        })
        .finally(() => (loading.value = false));
    });

    return {
      restaurant,
      loading,
      error,
      menuCategories,
      cartItemCount,
      cartRestaurantName,
      formatMoney,
      addToCart,
      confirmReplaceCart,
      replaceCartModal,
    };
  },
};
</script>

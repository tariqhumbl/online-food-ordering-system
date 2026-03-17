<template>
  <div class="restaurant-detail-page">
    <!-- Header -->
    <header class="restaurant-header">
      <div class="container">
        <div class="restaurant-header-inner">
          <router-link to="/" class="restaurant-logo">
            <i class="ph-duotone ph-fork-knife"></i>
            <span>FoodOrder</span>
          </router-link>
          <div class="restaurant-header-actions">
            <router-link to="/" class="btn btn-ghost">
              <i class="ph-duotone ph-arrow-left"></i> Back to home
            </router-link>
            <template v-if="isCustomer">
              <button type="button" class="btn btn-primary btn-cart" @click="openCartSidebar">
                <i class="ph-duotone ph-shopping-cart"></i>
                <span>Cart</span>
                <span v-if="cartItemCount > 0" class="cart-badge">{{ cartItemCount }}</span>
              </button>
            </template>
            <template v-else>
              <router-link :to="registerUrl" class="btn btn-primary">Register to order</router-link>
              <router-link :to="loginUrl" class="btn btn-ghost">Login</router-link>
            </template>
          </div>
        </div>
      </div>
    </header>

    <main class="restaurant-main">
      <div class="container">
        <div v-if="loading" class="restaurant-loading">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-3 text-muted">Loading menu...</p>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
        <div v-else-if="!restaurant" class="alert alert-warning">Restaurant not found.</div>
        <template v-else>
          <!-- Restaurant hero -->
          <section class="restaurant-hero">
            <div class="restaurant-hero-content">
              <h1 class="restaurant-name">{{ restaurant.name }}</h1>
              <p class="restaurant-desc">{{ restaurant.description || 'Choose items below and add to cart.' }}</p>
              <div class="restaurant-meta">
                <span v-if="restaurant.address" class="meta-item">
                  <i class="ph-duotone ph-map-pin"></i> {{ restaurant.address }}
                </span>
                <span class="meta-item">
                  <i class="ph-duotone ph-package"></i> Delivery {{ restaurant.delivery_fee != null ? formatMoney(restaurant.delivery_fee) : 'Free' }}
                  <template v-if="restaurant.estimated_delivery_minutes"> · {{ restaurant.estimated_delivery_minutes }} min</template>
                </span>
              </div>
            </div>
          </section>

          <!-- Menu by category -->
          <section v-for="cat in menuCategories" :key="cat.id" class="menu-category">
            <div class="menu-category-header">
              <h2 class="menu-category-title">{{ cat.name }}</h2>
              <p v-if="cat.description" class="menu-category-desc">{{ cat.description }}</p>
            </div>
            <div class="menu-item-grid">
              <article
                v-for="item in cat.menu_items"
                :key="item.id"
                class="menu-item-card"
                :class="{ 'menu-item-card--unavailable': !item.is_available }"
              >
                <div class="menu-item-image-wrap">
                  <img
                    v-if="item.image_url && !imageErrorMap[item.id]"
                    :src="item.image_url"
                    :alt="item.name"
                    class="menu-item-image"
                    :data-item-id="item.id"
                    @error="onImageError"
                  />
                  <div v-if="!item.image_url || imageErrorMap[item.id]" class="menu-item-placeholder">
                    <i class="ph-duotone ph-fork-knife"></i>
                  </div>
                </div>
                <div class="menu-item-body">
                  <h3 class="menu-item-name">{{ item.name }}</h3>
                  <p v-if="item.description" class="menu-item-desc">{{ item.description }}</p>
                  <div class="menu-item-footer">
                    <span class="menu-item-price">{{ formatMoney(item.price) }}</span>
                    <button
                      v-if="!item.is_available"
                      type="button"
                      class="btn btn-item btn-item--disabled"
                      disabled
                    >
                      Unavailable
                    </button>
                    <button
                      v-else
                      type="button"
                      class="btn btn-item btn-item--add"
                      @click="addToCart(item)"
                    >
                      <i class="ph-duotone ph-plus"></i> Add to cart
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </section>
        </template>
      </div>
    </main>

    <!-- Register / Login required modal -->
    <div class="modal fade" id="loginModal" ref="loginModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Register or log in</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            You need an account to add items to your cart and place orders. Register as a customer or log in if you already have an account.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <router-link :to="registerUrl" class="btn btn-primary">Register</router-link>
            <router-link :to="loginUrl" class="btn btn-outline-primary">Login</router-link>
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
import { isLoggedIn, getUser } from '@/services/auth_service';

export default {
  name: 'RestaurantDetail',
  setup() {
    const route = useRoute();
    const store = useStore();
    const toast = useToast();
    const restaurant = ref(null);
    const loading = ref(false);
    const error = ref('');
    const loginModal = ref(null);
    const imageErrorMap = ref({});
    let loginModalBs = null;

    function onImageError(e) {
      const id = e.target?.dataset?.itemId;
      if (id) imageErrorMap.value[id] = true;
    }

    const menuCategories = computed(() => restaurant.value?.menu_categories ?? []);
    const isCustomer = computed(() => {
      if (!isLoggedIn()) return false;
      const u = getUser();
      return u?.role_id === 3;
    });
    const cartItemCount = computed(() => store.getters.cartItemCount ?? 0);
    const redirectQuery = computed(() => `redirect=${encodeURIComponent(route.fullPath)}`);
    const loginUrl = computed(() => `/login?${redirectQuery.value}`);
    const registerUrl = computed(() => `/register?${redirectQuery.value}`);

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }

    function addToCart(item) {
      if (!isCustomer.value) {
        loginModalBs = loginModalBs || new Modal(loginModal.value);
        loginModalBs.show();
        return;
      }
      const rid = Number(route.params.id);
      const rname = restaurant.value?.name ?? '';
      const cartRestaurantId = store.getters.cartRestaurantId;
      if (store.getters.cartItemCount > 0 && cartRestaurantId !== rid) {
        if (!confirm(`Your cart has items from another restaurant. Replace with items from ${rname}?`)) return;
        store.commit('CART_SET', {
          restaurantId: rid,
          restaurantName: rname,
          items: [{ menu_item_id: item.id, name: item.name, price: Number(item.price), quantity: 1 }],
        });
      } else {
        if (store.getters.cartItemCount === 0) {
          store.commit('CART_SET_RESTAURANT', { id: rid, name: rname });
        }
        store.commit('CART_ADD_ITEM', { menuItem: item, quantity: 1 });
        store.commit('OPEN_CART_SIDEBAR');
      }
      toast.success(`"${item.name}" added to cart`);
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
        .catch(() => {
          error.value = 'Failed to load menu.';
        })
        .finally(() => (loading.value = false));
    });

    function openCartSidebar() {
      store.commit('OPEN_CART_SIDEBAR');
    }

    return {
      restaurant,
      loading,
      error,
      menuCategories,
      isCustomer,
      cartItemCount,
      loginUrl,
      registerUrl,
      loginModal,
      imageErrorMap,
      formatMoney,
      addToCart,
      openCartSidebar,
      onImageError,
    };
  },
};
</script>

<style scoped>
.restaurant-detail-page {
  min-height: 100vh;
  background: #f8f9fa;
}
.restaurant-header {
  background: #fff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  position: sticky;
  top: 0;
  z-index: 100;
}
.restaurant-header-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 0;
}
.restaurant-logo {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #0d6efd;
  text-decoration: none;
  font-weight: 600;
  font-size: 1.25rem;
}
.restaurant-logo i {
  font-size: 1.5rem;
}
.restaurant-logo:hover {
  color: #0a58ca;
}
.restaurant-header-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.btn-ghost {
  color: #495057;
  background: transparent;
  border: 1px solid #dee2e6;
  padding: 0.35rem 0.75rem;
  border-radius: 8px;
  font-size: 0.875rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  transition: background 0.2s, color 0.2s;
}
.btn-ghost:hover {
  background: #f8f9fa;
  color: #0d6efd;
}
.btn-cart {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  position: relative;
  padding: 0.35rem 0.85rem;
}
.cart-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #dc3545;
  color: #fff;
  font-size: 0.7rem;
  min-width: 1.25rem;
  height: 1.25rem;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
}
.restaurant-main {
  padding: 1.5rem 0 3rem;
}
.restaurant-loading {
  text-align: center;
  padding: 4rem 0;
}
.restaurant-hero {
  background: #fff;
  border-radius: 12px;
  padding: 1.75rem 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
.restaurant-name {
  font-size: 1.75rem;
  font-weight: 700;
  color: #212529;
  margin-bottom: 0.5rem;
}
.restaurant-desc {
  color: #6c757d;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
  line-height: 1.5;
}
.restaurant-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  font-size: 0.875rem;
  color: #6c757d;
}
.restaurant-meta .meta-item {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}
.restaurant-meta .ph-duotone {
  font-size: 1rem;
  opacity: 0.8;
}
.menu-category {
  margin-bottom: 2.5rem;
}
.menu-category-header {
  margin-bottom: 1rem;
}
.menu-category-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #212529;
  margin-bottom: 0.25rem;
}
.menu-category-desc {
  font-size: 0.875rem;
  color: #6c757d;
  margin: 0;
}
.menu-item-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}
.menu-item-card {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.2s;
}
.menu-item-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.menu-item-card--unavailable {
  opacity: 0.75;
}
.menu-item-image-wrap {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 10;
  background: #f1f3f5;
}
.menu-item-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.menu-item-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e9ecef;
  color: #adb5bd;
}
.menu-item-placeholder i {
  font-size: 2.5rem;
}
.menu-item-body {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}
.menu-item-name {
  font-size: 1.05rem;
  font-weight: 600;
  color: #212529;
  margin-bottom: 0.35rem;
}
.menu-item-desc {
  font-size: 0.8125rem;
  color: #6c757d;
  margin-bottom: 0.75rem;
  line-height: 1.4;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.menu-item-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  margin-top: auto;
}
.menu-item-price {
  font-weight: 600;
  color: #0d6efd;
  font-size: 1rem;
}
.btn-item {
  padding: 0.4rem 0.75rem;
  border-radius: 8px;
  font-size: 0.8125rem;
  font-weight: 500;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  transition: background 0.2s, transform 0.1s;
}
.btn-item:active {
  transform: scale(0.98);
}
.btn-item--add {
  background: #0d6efd;
  color: #fff;
}
.btn-item--add:hover {
  background: #0b5ed7;
}
.btn-item--disabled {
  background: #e9ecef;
  color: #6c757d;
  cursor: not-allowed;
}
@media (max-width: 768px) {
  .menu-item-grid {
    grid-template-columns: 1fr;
  }
  .restaurant-header-inner {
    flex-wrap: wrap;
    gap: 0.5rem;
  }
}
</style>

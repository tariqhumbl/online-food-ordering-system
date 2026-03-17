<template>
  <Teleport to="body">
    <Transition name="cart-overlay">
      <div
        v-if="isOpen"
        class="cart-sidebar-overlay"
        aria-hidden="true"
        @click="close"
      />
    </Transition>
    <Transition name="cart-slide">
      <aside
        v-if="isOpen"
        class="cart-sidebar"
        role="dialog"
        aria-label="Shopping cart"
      >
        <div class="cart-sidebar-header">
          <div class="cart-sidebar-title">
            <i class="ph-duotone ph-shopping-cart"></i>
            <span>Your cart</span>
            <span v-if="cartItemCount > 0" class="cart-sidebar-badge">{{ cartItemCount }}</span>
          </div>
          <button
            type="button"
            class="cart-sidebar-close"
            aria-label="Close cart"
            @click="close"
          >
            <i class="ph-duotone ph-x"></i>
          </button>
        </div>

        <div v-if="!cartItemCount" class="cart-sidebar-empty">
          <i class="ph-duotone ph-shopping-bag"></i>
          <p>Your cart is empty</p>
          <router-link to="/" class="cart-sidebar-btn cart-sidebar-btn-primary" @click="close">
            Browse restaurants
          </router-link>
        </div>

        <template v-else>
          <div class="cart-sidebar-restaurant">
            <i class="ph-duotone ph-storefront"></i>
            <span>{{ cartRestaurantName }}</span>
          </div>

          <div class="cart-sidebar-list">
            <div
              v-for="item in cartItems"
              :key="item.menu_item_id"
              class="cart-sidebar-item"
            >
              <div class="cart-sidebar-item-image">
                <img
                  v-if="item.image_url"
                  :src="item.image_url"
                  :alt="item.name"
                />
                <div v-else class="cart-sidebar-item-placeholder">
                  <i class="ph-duotone ph-fork-knife"></i>
                </div>
              </div>
              <div class="cart-sidebar-item-body">
                <div class="cart-sidebar-item-name">{{ item.name }}</div>
                <div class="cart-sidebar-item-meta">
                  <span class="cart-sidebar-item-price">{{ formatMoney(item.price) }}</span>
                  <div class="cart-sidebar-item-qty">
                    <button
                      type="button"
                      class="cart-sidebar-qty-btn"
                      :disabled="(item.quantity || 1) <= 1"
                      @click="updateQty(item.menu_item_id, (item.quantity || 1) - 1)"
                    >
                      −
                    </button>
                    <span class="cart-sidebar-qty-num">{{ item.quantity }}</span>
                    <button
                      type="button"
                      class="cart-sidebar-qty-btn"
                      @click="updateQty(item.menu_item_id, (item.quantity || 1) + 1)"
                    >
                      +
                    </button>
                  </div>
                </div>
                <div class="cart-sidebar-item-row">
                  <span class="cart-sidebar-item-subtotal">{{ formatMoney((item.price || 0) * (item.quantity || 0)) }}</span>
                  <button
                    type="button"
                    class="cart-sidebar-item-remove"
                    title="Remove"
                    @click="removeItem(item.menu_item_id)"
                  >
                    <i class="ph-duotone ph-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="cart-sidebar-footer">
            <div class="cart-sidebar-subtotal">
              <span>Subtotal</span>
              <strong>{{ formatMoney(cartSubtotal) }}</strong>
            </div>
            <div class="cart-sidebar-actions">
              <router-link
                to="/customer/cart"
                class="cart-sidebar-btn cart-sidebar-btn-outline"
                @click="close"
              >
                View full cart
              </router-link>
              <router-link
                to="/customer/checkout"
                class="cart-sidebar-btn cart-sidebar-btn-primary"
                @click="close"
              >
                Checkout
              </router-link>
            </div>
          </div>
        </template>
      </aside>
    </Transition>
  </Teleport>
</template>

<script>
import { computed, watch } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'CartSidebar',
  setup() {
    const store = useStore();

    const isOpen = computed(() => store.state.cartSidebarOpen);
    const cartItems = computed(() => store.getters.cartItems);
    const cartItemCount = computed(() => store.getters.cartItemCount);
    const cartSubtotal = computed(() => store.getters.cartSubtotal);
    const cartRestaurantId = computed(() => store.getters.cartRestaurantId);
    const cartRestaurantName = computed(() => store.getters.cartRestaurantName);

    function close() {
      store.commit('CLOSE_CART_SIDEBAR');
    }

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }

    function updateQty(menuItemId, quantity) {
      store.commit('CART_UPDATE_QUANTITY', { menuItemId, quantity });
    }

    function removeItem(menuItemId) {
      store.commit('CART_REMOVE_ITEM', menuItemId);
    }

    watch(isOpen, (open) => {
      if (open) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    });

    return {
      isOpen,
      cartItems,
      cartItemCount,
      cartSubtotal,
      cartRestaurantId,
      cartRestaurantName,
      close,
      formatMoney,
      updateQty,
      removeItem,
    };
  },
};
</script>

<style scoped>
.cart-sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 1040;
  backdrop-filter: blur(2px);
}

.cart-sidebar {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  max-width: 400px;
  background: #fff;
  z-index: 1050;
  display: flex;
  flex-direction: column;
  box-shadow: -4px 0 24px rgba(0, 0, 0, 0.12);
}

.cart-sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #eee;
  flex-shrink: 0;
}

.cart-sidebar-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1a1a1a;
}

.cart-sidebar-title i {
  font-size: 1.5rem;
  color: #0d6efd;
}

.cart-sidebar-badge {
  background: #0d6efd;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
}

.cart-sidebar-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border: none;
  background: #f5f5f5;
  border-radius: 10px;
  color: #666;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}

.cart-sidebar-close:hover {
  background: #eee;
  color: #1a1a1a;
}

.cart-sidebar-close i {
  font-size: 1.25rem;
}

.cart-sidebar-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  text-align: center;
}

.cart-sidebar-empty i {
  font-size: 4rem;
  color: #ddd;
  margin-bottom: 1rem;
}

.cart-sidebar-empty p {
  color: #666;
  margin-bottom: 1.5rem;
}

.cart-sidebar-restaurant {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #f8f9fa;
  font-size: 0.9rem;
  color: #495057;
}

.cart-sidebar-restaurant i {
  font-size: 1.1rem;
  color: #0d6efd;
}

.cart-sidebar-list {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.5rem;
}

.cart-sidebar-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid #eee;
}

.cart-sidebar-item:last-child {
  border-bottom: none;
}

.cart-sidebar-item-image {
  flex-shrink: 0;
  width: 64px;
  height: 64px;
  border-radius: 10px;
  overflow: hidden;
  background: #f5f5f5;
}

.cart-sidebar-item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cart-sidebar-item-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #bbb;
}

.cart-sidebar-item-placeholder i {
  font-size: 1.5rem;
}

.cart-sidebar-item-body {
  flex: 1;
  min-width: 0;
}

.cart-sidebar-item-name {
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 0.25rem;
}

.cart-sidebar-item-meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.cart-sidebar-item-price {
  font-size: 0.9rem;
  color: #0d6efd;
  font-weight: 500;
}

.cart-sidebar-item-qty {
  display: inline-flex;
  align-items: center;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  overflow: hidden;
}

.cart-sidebar-qty-btn {
  width: 28px;
  height: 28px;
  border: none;
  background: #f8f9fa;
  color: #495057;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s;
}

.cart-sidebar-qty-btn:hover:not(:disabled) {
  background: #e9ecef;
}

.cart-sidebar-qty-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.cart-sidebar-qty-num {
  min-width: 28px;
  text-align: center;
  font-size: 0.9rem;
  font-weight: 500;
}

.cart-sidebar-item-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 0.5rem;
}

.cart-sidebar-item-subtotal {
  font-weight: 600;
  color: #1a1a1a;
}

.cart-sidebar-item-remove {
  padding: 0.25rem;
  border: none;
  background: none;
  color: #dc3545;
  cursor: pointer;
  border-radius: 6px;
  transition: background 0.2s;
}

.cart-sidebar-item-remove:hover {
  background: rgba(220, 53, 69, 0.1);
}

.cart-sidebar-item-remove i {
  font-size: 1.1rem;
}

.cart-sidebar-footer {
  padding: 1.25rem 1.5rem;
  border-top: 1px solid #eee;
  background: #fff;
  flex-shrink: 0;
}

.cart-sidebar-subtotal {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.1rem;
  margin-bottom: 1rem;
}

.cart-sidebar-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.cart-sidebar-btn {
  display: block;
  text-align: center;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.2s, color 0.2s;
}

.cart-sidebar-btn-primary {
  background: #0d6efd;
  color: #fff;
  border: none;
}

.cart-sidebar-btn-primary:hover {
  background: #0b5ed7;
  color: #fff;
}

.cart-sidebar-btn-outline {
  background: transparent;
  color: #0d6efd;
  border: 2px solid #0d6efd;
}

.cart-sidebar-btn-outline:hover {
  background: rgba(13, 110, 253, 0.08);
  color: #0d6efd;
}

/* Transitions */
.cart-overlay-enter-active,
.cart-overlay-leave-active {
  transition: opacity 0.25s ease;
}

.cart-overlay-enter-from,
.cart-overlay-leave-to {
  opacity: 0;
}

.cart-slide-enter-active,
.cart-slide-leave-active {
  transition: transform 0.3s ease;
}

.cart-slide-enter-from,
.cart-slide-leave-to {
  transform: translateX(100%);
}

@media (max-width: 480px) {
  .cart-sidebar {
    max-width: 100%;
  }
}
</style>

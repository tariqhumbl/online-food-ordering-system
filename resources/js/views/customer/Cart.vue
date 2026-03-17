<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/customer/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Cart</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">Cart</h2>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">Review your order and proceed to checkout.</p>

    <div v-if="!cartItemCount" class="card">
      <div class="card-body text-center py-5">
        <p class="text-muted mb-3">Your cart is empty.</p>
        <router-link to="/customer/restaurants" class="btn btn-primary">Browse restaurants</router-link>
      </div>
    </div>

    <div v-else>
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>{{ cartRestaurantName }}</span>
          <router-link :to="`/customer/restaurants/${cartRestaurantId}`" class="btn btn-sm btn-outline-primary">
            Back to menu
          </router-link>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-align-middle">
              <thead>
                <tr>
                  <th>Item</th>
                  <th class="text-end">Price</th>
                  <th class="text-center">Qty</th>
                  <th class="text-end">Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in cartItems" :key="item.menu_item_id">
                  <td>{{ item.name }}</td>
                  <td class="text-end">{{ formatMoney(item.price) }}</td>
                  <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        @click="updateQty(item.menu_item_id, (item.quantity || 1) - 1)"
                      >
                        −
                      </button>
                      <span class="px-2">{{ item.quantity }}</span>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        @click="updateQty(item.menu_item_id, (item.quantity || 1) + 1)"
                      >
                        +
                      </button>
                    </div>
                  </td>
                  <td class="text-end">{{ formatMoney((item.price || 0) * (item.quantity || 0)) }}</td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger"
                      @click="removeItem(item.menu_item_id)"
                    >
                      Remove
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="button" class="btn btn-outline-secondary" @click="clearCart">Clear cart</button>
            <div>
              <strong>Subtotal:</strong> {{ formatMoney(cartSubtotal) }}
            </div>
          </div>
        </div>
      </div>
      <router-link to="/customer/checkout" class="btn btn-primary btn-lg">Proceed to checkout</router-link>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import { useStore } from 'vuex';
import { useToast } from 'vue-toastification';

export default {
  name: 'Cart',
  setup() {
    const store = useStore();
    const toast = useToast();

    const cartItems = computed(() => store.getters.cartItems);
    const cartItemCount = computed(() => store.getters.cartItemCount);
    const cartSubtotal = computed(() => store.getters.cartSubtotal);
    const cartRestaurantId = computed(() => store.getters.cartRestaurantId);
    const cartRestaurantName = computed(() => store.getters.cartRestaurantName);

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

    function clearCart() {
      store.commit('CART_CLEAR');
      toast.success('Cart cleared.');
    }

    return {
      cartItems,
      cartItemCount,
      cartSubtotal,
      cartRestaurantId,
      cartRestaurantName,
      formatMoney,
      updateQty,
      removeItem,
      clearCart,
    };
  },
};
</script>

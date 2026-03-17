<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/customer/dashboard">Home</router-link></li>
              <li class="breadcrumb-item"><router-link to="/customer/cart">Cart</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Checkout</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">Checkout</h2>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!cartItemCount && !orderSuccess" class="card">
      <div class="card-body text-center py-5">
        <p class="text-muted mb-3">Your cart is empty.</p>
        <router-link to="/customer/restaurants" class="btn btn-primary">Browse restaurants</router-link>
      </div>
    </div>

    <div v-else-if="orderSuccess" class="card">
      <div class="card-body text-center py-5">
        <div class="text-success mb-3">
          <i class="ph-duotone ph-check-circle" style="font-size: 3rem;"></i>
        </div>
        <h5 class="mb-2">Order placed successfully</h5>
        <p class="text-muted mb-3">Order number: <strong>{{ placedOrder?.order_number }}</strong></p>
        <router-link :to="`/customer/orders/${placedOrder?.id}`" class="btn btn-primary me-2">Track order</router-link>
        <router-link to="/customer/orders" class="btn btn-outline-primary">My orders</router-link>
      </div>
    </div>

    <div v-else class="row">
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">Delivery details</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Delivery address <span class="text-danger">*</span></label>
              <input
                v-model="form.delivery_address"
                type="text"
                class="form-control"
                placeholder="Street, city, postal code"
                required
              />
            </div>
            <div class="mb-3">
              <label class="form-label">Phone</label>
              <input v-model="form.customer_phone" type="text" class="form-control" placeholder="Contact number" />
            </div>
            <div class="mb-0">
              <label class="form-label">Notes</label>
              <textarea v-model="form.notes" class="form-control" rows="2" placeholder="Delivery instructions"></textarea>
            </div>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">Payment method</h5>
            <small class="text-muted">Choose how you would like to pay</small>
          </div>
          <div class="card-body">
            <div class="payment-methods">
              <label class="payment-method-option" :class="{ active: form.payment_method === 'cash' }">
                <input v-model="form.payment_method" type="radio" name="payment_method" value="cash" />
                <span class="payment-method-icon"><i class="ph-duotone ph-money"></i></span>
                <div class="payment-method-text">
                  <span class="payment-method-label">Cash on delivery</span>
                  <span class="payment-method-desc">Pay with cash when your order is delivered</span>
                </div>
              </label>
              <label class="payment-method-option" :class="{ active: form.payment_method === 'card' }">
                <input v-model="form.payment_method" type="radio" name="payment_method" value="card" />
                <span class="payment-method-icon"><i class="ph-duotone ph-credit-card"></i></span>
                <div class="payment-method-text">
                  <span class="payment-method-label">Card</span>
                  <span class="payment-method-desc">Credit or debit card (pay on delivery or at pickup)</span>
                </div>
              </label>
              <label class="payment-method-option" :class="{ active: form.payment_method === 'online' }">
                <input v-model="form.payment_method" type="radio" name="payment_method" value="online" />
                <span class="payment-method-icon"><i class="ph-duotone ph-device-mobile"></i></span>
                <div class="payment-method-text">
                  <span class="payment-method-label">Online payment</span>
                  <span class="payment-method-desc">Pay now via payment gateway</span>
                </div>
              </label>
              <label class="payment-method-option" :class="{ active: form.payment_method === 'wallet' }">
                <input v-model="form.payment_method" type="radio" name="payment_method" value="wallet" />
                <span class="payment-method-icon"><i class="ph-duotone ph-wallet"></i></span>
                <div class="payment-method-text">
                  <span class="payment-method-label">Wallet</span>
                  <span class="payment-method-desc">Pay from your account wallet</span>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Order summary</h5>
          </div>
          <div class="card-body">
            <p class="mb-2">{{ cartRestaurantName }}</p>
            <ul class="list-unstyled small mb-3">
              <li v-for="item in cartItems" :key="item.menu_item_id" class="d-flex justify-content-between">
                <span>{{ item.name }} × {{ item.quantity }}</span>
                <span>{{ formatMoney((item.price || 0) * (item.quantity || 0)) }}</span>
              </li>
            </ul>
            <hr />
            <div class="d-flex justify-content-between mb-2">
              <span>Subtotal</span>
              <span>{{ formatMoney(cartSubtotal) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Delivery fee</span>
              <span>{{ formatMoney(deliveryFee) }}</span>
            </div>
            <div class="d-flex justify-content-between fw-bold">
              <span>Total</span>
              <span>{{ formatMoney(total) }}</span>
            </div>
            <div v-if="formError" class="alert alert-danger mt-3 mb-0 small">{{ formError }}</div>
            <button
              type="button"
              class="btn btn-primary w-100 mt-3"
              :disabled="submitting || !form.delivery_address?.trim()"
              @click="placeOrder"
            >
              {{ submitting ? 'Placing order...' : 'Place order' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import { useToast } from 'vue-toastification';

export default {
  name: 'Checkout',
  setup() {
    const router = useRouter();
    const store = useStore();
    const toast = useToast();

    const form = ref({
      delivery_address: '',
      customer_phone: '',
      notes: '',
      payment_method: 'cash',
    });
    const formError = ref('');
    const submitting = ref(false);
    const orderSuccess = ref(false);
    const placedOrder = ref(null);
    const restaurantDetail = ref(null);

    const cartItems = computed(() => store.getters.cartItems);
    const cartItemCount = computed(() => store.getters.cartItemCount);
    const cartSubtotal = computed(() => store.getters.cartSubtotal);
    const cartRestaurantId = computed(() => store.getters.cartRestaurantId);
    const cartRestaurantName = computed(() => store.getters.cartRestaurantName);

    const deliveryFee = computed(() => {
      if (restaurantDetail.value?.delivery_fee != null) return Number(restaurantDetail.value.delivery_fee);
      return 0;
    });
    const total = computed(() => cartSubtotal.value + deliveryFee.value);

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }

    function placeOrder() {
      if (!form.value.delivery_address?.trim()) return;
      formError.value = '';
      submitting.value = true;
      const payload = {
        restaurant_id: store.getters.cartRestaurantId,
        delivery_address: form.value.delivery_address.trim(),
        customer_phone: form.value.customer_phone?.trim() || undefined,
        notes: form.value.notes?.trim() || undefined,
        items: store.getters.cartItems.map((i) => ({
          menu_item_id: i.menu_item_id,
          quantity: i.quantity,
        })),
      };
      API.orders
        .create(payload)
        .then((res) => {
          placedOrder.value = res.data;
          store.commit('CART_CLEAR');
          orderSuccess.value = true;
          toast.success('Order placed successfully.');
          API.payments
            .create({
              food_order_id: res.data.id,
              amount: res.data.total,
              method: form.value.payment_method || 'cash',
            })
            .catch(() => {});
        })
        .catch((err) => {
          formError.value =
            err.response?.data?.message ||
            (err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(' ') : null) ||
            'Failed to place order.';
          toast.error(formError.value);
        })
        .finally(() => (submitting.value = false));
    }

    onMounted(() => {
      const rid = store.getters.cartRestaurantId;
      if (rid) {
        API.restaurants.get(rid).then((res) => {
          restaurantDetail.value = res.data;
        }).catch(() => {});
      }
    });

    return {
      form,
      formError,
      submitting,
      orderSuccess,
      placedOrder,
      cartItems,
      cartItemCount,
      cartSubtotal,
      cartRestaurantName,
      deliveryFee,
      total,
      formatMoney,
      placeOrder,
    };
  },
};
</script>

<style scoped>
.payment-methods {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.payment-method-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  cursor: pointer;
  transition: border-color 0.2s, background-color 0.2s;
}
.payment-method-option:hover {
  border-color: #ced4da;
  background-color: #f8f9fa;
}
.payment-method-option.active {
  border-color: #0d6efd;
  background-color: rgba(13, 110, 253, 0.06);
}
.payment-method-option input {
  flex-shrink: 0;
  margin: 0;
  accent-color: #0d6efd;
}
.payment-method-icon {
  flex-shrink: 0;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 8px;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #495057;
}
.payment-method-option.active .payment-method-icon {
  background: rgba(13, 110, 253, 0.15);
  color: #0d6efd;
}
.payment-method-icon i {
  font-size: 1.25rem;
}
.payment-method-text {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  min-width: 0;
}
.payment-method-label {
  font-weight: 600;
  color: #212529;
}
.payment-method-desc {
  font-size: 0.8125rem;
  color: #6c757d;
}
</style>

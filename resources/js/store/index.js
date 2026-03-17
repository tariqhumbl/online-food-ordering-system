import { createStore } from 'vuex';

const store = createStore({
  state: {
    isLoading: true,
    backTo: null,
    profile: null,
    theme: 'light', /* 'light' | 'dark' - synced with localStorage on init and when user toggles */
    baseUrl: window.location.origin,
    apiURL: window.location.origin,
    cartSidebarOpen: false,
    cart: {
      restaurantId: null,
      restaurantName: null,
      items: [],
    },
  },
  mutations: {
    SET_LOADING(state, value) {
      state.isLoading = value;
    },
    SET_BACK_TO(state, value) {
      state.backTo = value;
    },
    SET_PROFILE(state, profile) {
      state.profile = profile;
    },
    CART_SET(state, payload) {
      state.cart = {
        restaurantId: payload.restaurantId ?? null,
        restaurantName: payload.restaurantName ?? null,
        items: payload.items ?? [],
      };
      try {
        localStorage.setItem('food_order_cart', JSON.stringify(state.cart));
      } catch (_) {}
    },
    CART_ADD_ITEM(state, { menuItem, quantity = 1 }) {
      const existing = state.cart.items.find((i) => i.menu_item_id === menuItem.id);
      if (existing) {
        existing.quantity += quantity;
      } else {
        state.cart.items.push({
          menu_item_id: menuItem.id,
          name: menuItem.name,
          price: Number(menuItem.price),
          quantity,
          image_url: menuItem.image_url ?? null,
        });
      }
      try {
        localStorage.setItem('food_order_cart', JSON.stringify(state.cart));
      } catch (_) {}
    },
    CART_UPDATE_QUANTITY(state, { menuItemId, quantity }) {
      const item = state.cart.items.find((i) => i.menu_item_id === menuItemId);
      if (!item) return;
      if (quantity <= 0) {
        state.cart.items = state.cart.items.filter((i) => i.menu_item_id !== menuItemId);
      } else {
        item.quantity = quantity;
      }
      try {
        localStorage.setItem('food_order_cart', JSON.stringify(state.cart));
      } catch (_) {}
    },
    CART_REMOVE_ITEM(state, menuItemId) {
      state.cart.items = state.cart.items.filter((i) => i.menu_item_id !== menuItemId);
      try {
        localStorage.setItem('food_order_cart', JSON.stringify(state.cart));
      } catch (_) {}
    },
    CART_CLEAR(state) {
      state.cart = { restaurantId: null, restaurantName: null, items: [] };
      try {
        localStorage.removeItem('food_order_cart');
      } catch (_) {}
    },
    CART_SET_RESTAURANT(state, { id, name }) {
      state.cart.restaurantId = id;
      state.cart.restaurantName = name;
      try {
        localStorage.setItem('food_order_cart', JSON.stringify(state.cart));
      } catch (_) {}
    },
    OPEN_CART_SIDEBAR(state) {
      state.cartSidebarOpen = true;
    },
    CLOSE_CART_SIDEBAR(state) {
      state.cartSidebarOpen = false;
    },
    SET_THEME(state, value) {
      state.theme = value === 'dark' ? 'dark' : 'light';
    },
  },
  actions: {
    fetchUserProfile({ commit }) {
      return new Promise((resolve) => {
        const userData = JSON.parse(localStorage.getItem("user")) || null;
        if (userData) {
          commit("SET_PROFILE", userData);
        }
        resolve(userData);
      });
    },
    hydrateCart({ state }) {
      try {
        const raw = localStorage.getItem('food_order_cart');
        if (raw) {
          const parsed = JSON.parse(raw);
          if (parsed && Array.isArray(parsed.items)) {
            state.cart = {
              restaurantId: parsed.restaurantId ?? null,
              restaurantName: parsed.restaurantName ?? null,
              items: parsed.items ?? [],
            };
          }
        }
      } catch (_) {}
    },
  },
  getters: {
    theme: (state) => state.theme,
    userRole: (state) => state.profile?.role_id ?? null,
    getImagePath: (state) => (path) => `${state.baseUrl}/storage/${path}`,
    cartItems: (state) => state.cart.items,
    cartRestaurantId: (state) => state.cart.restaurantId,
    cartRestaurantName: (state) => state.cart.restaurantName,
    cartItemCount: (state) => state.cart.items.reduce((n, i) => n + (i.quantity || 0), 0),
    cartSubtotal: (state) =>
      state.cart.items.reduce((sum, i) => sum + (Number(i.price) || 0) * (i.quantity || 0), 0),
    cartOrderPayload: (state) => ({
      restaurant_id: state.cart.restaurantId,
      items: state.cart.items.map((i) => ({ menu_item_id: i.menu_item_id, quantity: i.quantity })),
    }),
    cartSidebarOpen: (state) => state.cartSidebarOpen,
  },
});
// Check for the token in the URL
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');

if (token) {
    localStorage.setItem('auth_token', token); // Store the token
    window.location.href = '/dashboard'; // Redirect to the dashboard
}
export default store;


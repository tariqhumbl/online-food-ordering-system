<template>
    <Loading v-if="isLoading" />
    <router-view />
    <CartSidebar />
  </template>

  <script>
  import { computed, onMounted, watch } from 'vue';
  import Loading from './components/common/Loading.vue';
  import CartSidebar from './components/cart/CartSidebar.vue';
  import { useStore } from 'vuex';
  import { useRoute } from 'vue-router';
  import { initTheme, getTheme } from '@/utils/theme';

  export default {
    components: {
      Loading,
      CartSidebar,
    },
    setup() {
      const store = useStore();
      const route = useRoute();

      const isLoading = computed(() => store.state.isLoading);

      onMounted(() => {
        initTheme(); // Re-apply saved theme so dark/light works on every page
        store.commit('SET_THEME', getTheme());
        store.dispatch('hydrateCart');
        setTimeout(() => {
          store.commit('SET_LOADING', false);
        }, 500);
      });

      watch(
        () => route.path,
        (currentRoute, previousRoute) => {
          initTheme();
          store.commit('SET_THEME', getTheme());
          store.commit('SET_BACK_TO', previousRoute);
        }
      );

      return { isLoading };
    },
  };
  </script>

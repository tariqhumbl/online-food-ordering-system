<template>
  <div class="layout" :class="{ 'pc-dark': theme === 'dark' }" :data-pc-theme="theme">
    <Sidebar />
    <Navbar />
    <div class="pc-container">
      <router-view />
    </div>
    <Footer />
  </div>
</template>

<script>
import { computed, onMounted, watch } from 'vue';
import { useStore } from 'vuex';
import { useRoute } from 'vue-router';
import { initTheme, getTheme } from '@/utils/theme';
import Navbar from '@/components/common/Navbar.vue';
import Footer from '@/components/common/Footer.vue';
import Sidebar from '@/components/common/Sidebar.vue';

export default {
  name: 'CustomerHome',
  components: { Navbar, Sidebar, Footer },
  setup() {
    const store = useStore();
    const route = useRoute();
    const theme = computed(() => store.state.theme === 'dark' ? 'dark' : null);
    function applyTheme() {
      initTheme();
      store.commit('SET_THEME', getTheme());
    }
    onMounted(applyTheme);
    watch(() => route.path, applyTheme);
    return { theme };
  },
};
</script>

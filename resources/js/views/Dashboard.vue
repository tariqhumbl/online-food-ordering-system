<template>
  <div class="layout">
    <Sidebar />
    <Navbar />
    <div class="pc-container">
      <div class="pc-content">
        <div class="dashboard-content">
          <h1 class="mb-3">Dashboard</h1>
          <p class="text-muted">You are logged in. Add your modules and data here later.</p>
        </div>
      </div>
    </div>
    <Footer />
  </div>
</template>

<script>
import Navbar from '@/components/common/Navbar.vue';
import Footer from '@/components/common/Footer.vue';
import Sidebar from '@/components/common/Sidebar.vue';

export default {
  name: 'Dashboard',
  components: {
    Navbar,
    Sidebar,
    Footer,
  },
};
</script>

<style scoped>
.dashboard-content {
  padding: 1.5rem 0;
}
</style>

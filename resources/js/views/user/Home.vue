<template>
    <div class="layout">
      <Sidebar />
      <Navbar />
      <div class="pc-container">
        <router-view />
      </div>
      <Footer />
    </div>
  </template>

  <script>
  import Navbar from '@/components/common/Navbar.vue';
  import Footer from '@/components/common/Footer.vue';
  import Sidebar from '@/components/common/Sidebar.vue';

  export default {
    name: 'Home',
    components: {
      Navbar,
      Sidebar,
      Footer,
    },
  };
  </script>

  <style scoped>

  .content {
    flex-grow: 1;
    padding: 20px;
  }
  </style>

<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link :to="dashboardLink">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Settings</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">Settings</h2>
          </div>
        </div>
      </div>
    </div>

    <p class="text-muted mb-4">Manage your account and preferences. All links below stay within your dashboard.</p>

    <div class="row g-4">
      <!-- Profile -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="avtar avtar-lg bg-light-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                <i class="ph-duotone ph-user-circle f-24"></i>
              </div>
              <div>
                <h5 class="mb-0">Profile</h5>
                <small class="text-muted">Name, email &amp; account info</small>
              </div>
            </div>
            <p class="text-muted small mb-3">Update your display name and email address.</p>
            <router-link :to="'/update-profile'" class="btn btn-outline-primary btn-sm">Edit profile</router-link>
          </div>
        </div>
      </div>

      <!-- Security -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="avtar avtar-lg bg-light-warning rounded-circle d-flex align-items-center justify-content-center me-3">
                <i class="ph-duotone ph-key f-24"></i>
              </div>
              <div>
                <h5 class="mb-0">Security</h5>
                <small class="text-muted">Password &amp; security</small>
              </div>
            </div>
            <p class="text-muted small mb-3">Change your password to keep your account secure.</p>
            <router-link :to="'/change-password'" class="btn btn-outline-primary btn-sm">Change password</router-link>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="avtar avtar-lg bg-light-info rounded-circle d-flex align-items-center justify-content-center me-3">
                <i class="ph-duotone ph-bell f-24"></i>
              </div>
              <div>
                <h5 class="mb-0">Notifications</h5>
                <small class="text-muted">Order &amp; activity alerts</small>
              </div>
            </div>
            <p class="text-muted small mb-3">View and manage your notification history.</p>
            <router-link :to="notificationsLink" class="btn btn-outline-primary btn-sm">View notifications</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'SettingsPage',
  setup() {
    const store = useStore();
    const roleId = computed(() => store.state.profile?.role_id);
    const dashboardLink = computed(() => {
      const map = { 1: '/admin/dashboard', 2: '/vendor/dashboard', 3: '/customer/dashboard', 4: '/rider/dashboard' };
      return map[roleId.value] || '/customer/dashboard';
    });
    const notificationsLink = computed(() => {
      const map = { 1: '/admin/notifications', 2: '/vendor/notifications', 3: '/customer/notifications', 4: '/rider/notifications' };
      return map[roleId.value] || '/customer/notifications';
    });
    return { dashboardLink, notificationsLink };
  },
};
</script>

<style scoped>
.hover-lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}
</style>

<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link :to="dashboardLink">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Notifications</li>
            </ul>
          </div>
          <div class="col-12">
            <h2 class="mb-0">Notifications</h2>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between py-3">
        <h5 class="mb-0">All notifications</h5>
        <button
          v-if="unreadCount > 0"
          type="button"
          class="btn btn-sm btn-outline-primary"
          @click="markAllRead"
        >
          Mark all as read
        </button>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2 text-muted small">Loading...</p>
        </div>
        <div v-else-if="!notifications.length" class="text-center py-5 text-muted">
          No notifications yet.
        </div>
        <ul v-else class="list-group list-group-flush">
          <li
            v-for="n in notifications"
            :key="n.id"
            class="list-group-item d-flex align-items-start"
            :class="{ 'bg-light': !n.read_at }"
          >
            <div class="flex-shrink-0 me-3">
              <div
                class="avtar avtar-s rounded-circle d-flex align-items-center justify-content-center"
                :class="n.type === 'order_created' ? 'bg-light-primary' : 'bg-light-info'"
              >
                <i class="ph-duotone ph-bell"></i>
              </div>
            </div>
            <div class="flex-grow-1 min-w-0">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-1">{{ n.title }}</h6>
                <span class="text-muted small">{{ timeAgo(n.created_at) }}</span>
              </div>
              <p v-if="n.message" class="mb-2 text-muted small">{{ n.message }}</p>
              <router-link
                v-if="orderLink(n)"
                :to="orderLink(n)"
                class="btn btn-sm btn-outline-primary"
                @click="markOneRead(n.id)"
              >
                View order
              </router-link>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'NotificationsPage',
  setup() {
    const store = useStore();
    const notifications = ref([]);
    const unreadCount = ref(0);
    const loading = ref(true);

    const roleId = computed(() => store.state.profile?.role_id);
    const dashboardLink = computed(() => {
      const map = { 1: '/admin/dashboard', 2: '/vendor/dashboard', 3: '/customer/dashboard', 4: '/rider/dashboard' };
      return map[roleId.value] || '/customer/dashboard';
    });

    function timeAgo(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      const now = new Date();
      const s = Math.floor((now - d) / 1000);
      if (s < 60) return 'Just now';
      if (s < 3600) return Math.floor(s / 60) + ' min ago';
      if (s < 86400) return Math.floor(s / 3600) + ' hour' + (Math.floor(s / 3600) > 1 ? 's' : '') + ' ago';
      if (s < 604800) return Math.floor(s / 86400) + ' day' + (Math.floor(s / 86400) > 1 ? 's' : '') + ' ago';
      return d.toLocaleDateString();
    }

    function orderLink(n) {
      const orderId = n.data?.order_id;
      if (!orderId) return null;
      if (roleId.value === 3) return `/customer/orders/${orderId}`;
      if (roleId.value === 2) return '/vendor/orders';
      if (roleId.value === 1) return '/admin/orders';
      return null;
    }

    async function load() {
      loading.value = true;
      try {
        const res = await API.notifications.list({ per_page: 50 });
        notifications.value = res.data.notifications || [];
        unreadCount.value = res.data.unread_count ?? 0;
      } catch (_) {}
      finally {
        loading.value = false;
      }
    }

    async function markOneRead(id) {
      try {
        await API.notifications.markAsRead(id);
        const n = notifications.value.find((x) => x.id === id);
        if (n) n.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      } catch (_) {}
    }

    async function markAllRead() {
      try {
        await API.notifications.markAsRead();
        notifications.value.forEach((n) => { n.read_at = n.read_at || new Date().toISOString(); });
        unreadCount.value = 0;
      } catch (_) {}
    }

    onMounted(load);

    return {
      notifications,
      unreadCount,
      loading,
      dashboardLink,
      timeAgo,
      orderLink,
      markOneRead,
      markAllRead,
    };
  },
};
</script>

<template>
<header class="pc-header">
    <div class="header-wrapper">
        <!-- [Mobile Media Block] start -->
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <!-- ======= Menu collapse Icon ===== -->
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="dropdown pc-h-item d-inline-flex d-md-none">
                    <a class="pc-head-link dropdown-toggle arrow-none m-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ph-duotone ph-magnifying-glass"></i>
                    </a>
                    <div class="dropdown-menu pc-h-dropdown drp-search">
                        <form class="px-3">
                            <div class="mb-0 d-flex align-items-center">
                                <input
                                    ref="searchInput"
                                    v-model="searchText"
                                    type="search"
                                    class="form-control border-0 shadow-none"
                                    placeholder="Search..."
                                    @input="searchInPage"
                                />
                                <button type="button" class="btn btn-light-secondary btn-search" @click="searchInPage">Search</button>
                            </div>
                        </form>
                    </div>
                </li>
                <li class="pc-h-item d-none d-md-inline-flex">
                    <form class="form-search" @submit.prevent="searchInPage">
                        <span class="input-icon">
                            <i class="ph-duotone ph-magnifying-glass"></i>
                        </span>
                        <input 
                            v-model="searchText"
                            type="search" 
                            class="form-control with-icon" 
                            placeholder="Search..." 
                            @input="searchInPage"
                        />
                    </form>
                </li>
            </ul>
        </div>
        <!-- [Mobile Media Block end] -->
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li v-if="isCustomer" class="pc-h-item">
                    <button type="button" class="pc-head-link position-relative border-0 bg-transparent" aria-label="Open cart" @click="openCartSidebar">
                        <i class="ph-duotone ph-shopping-cart"></i>
                        <span v-if="cartItemCount > 0" class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">{{ cartItemCount }}</span>
                    </button>
                </li>
                <li class="dropdown pc-h-item d-none d-md-inline-flex">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="ph-duotone ph-sun-dim" v-if="currentTheme === 'light'"></i>
                        <i class="ph-duotone ph-moon" v-else></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <button type="button" class="dropdown-item border-0 bg-transparent w-100 text-start d-flex align-items-center" @click="applyTheme('dark')">
                            <i class="ph-duotone ph-moon me-2"></i>
                            <span>Dark</span>
                        </button>
                        <button type="button" class="dropdown-item border-0 bg-transparent w-100 text-start d-flex align-items-center" @click="applyTheme('light')">
                            <i class="ph-duotone ph-sun-dim me-2"></i>
                            <span>Light</span>
                        </button>
                        <button type="button" class="dropdown-item border-0 bg-transparent w-100 text-start d-flex align-items-center" @click="applyTheme('default')">
                            <i class="ph-duotone ph-cpu me-2"></i>
                            <span>Default</span>
                        </button>
                    </div>
                </li>

                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ph-duotone ph-diamonds-four"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <a href="#!" class="dropdown-item">
                            <i class="ph-duotone ph-user"></i>
                            <span>My Account</span>
                        </a>
                        <router-link :to="settingsPagePath" class="dropdown-item">
                            <i class="ph-duotone ph-gear"></i>
                            <span>Settings</span>
                        </router-link>
                        <a href="#!" class="dropdown-item">
                            <i class="ph-duotone ph-lifebuoy"></i>
                            <span>Support</span>
                        </a>
                        <a href="#!" class="dropdown-item">
                            <i class="ph-duotone ph-lock-key"></i>
                            <span>Lock Screen</span>
                        </a>

                        <a href="#!" class="dropdown-item" @click="logout">
                            <i class="ph-duotone ph-power"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </li>
                <li v-if="isLoggedIn" class="pc-h-item">
                    <router-link :to="notificationsPagePath" class="pc-head-link position-relative arrow-none me-0 d-flex align-items-center" aria-label="Notifications">
                        <i class="ph-duotone ph-bell"></i>
                        <span v-if="notificationUnreadCount > 0" class="badge bg-success pc-h-badge">{{ notificationUnreadCount }}</span>
                    </router-link>
                </li>
                <li class="dropdown pc-h-item header-user-profile" ref="profileDropdownWrap">
                    <a
                        class="pc-head-link dropdown-toggle arrow-none me-0"
                        href="#"
                        role="button"
                        aria-expanded="false"
                        @click.prevent="toggleProfileDropdown"
                    >
                        <img src="/public/assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar" />
                    </a>
                    <div
                        v-show="profileDropdownOpen"
                        class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown show"
                    >
                        <div class="dropdown-header d-flex align-items-center justify-content-between">
                            <h5 class="m-0">Profile</h5>
                        </div>
                        <div class="dropdown-body">
                            <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
                                <ul class="list-group list-group-flush w-100">
                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img src="/public/assets/images/user/avatar-2.jpg" alt="user-image" class="wid-50 rounded-circle" />
                                            </div>
                                            <div class="flex-grow-1 mx-3">
                                                <h5 class="mb-0">{{ userName }}</h5>
                                                <a class="link-primary" :href="`mailto:${userEmail}`">{{ userEmail }}</a>
                                            </div>
                                            <span class="badge bg-primary">PRO</span>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="/change-password" class="dropdown-item" >
                                            <span class="d-flex align-items-center">
                                                <i class="ph-duotone ph-key"></i>
                                                <span>Change password</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="/update-profile" class="dropdown-item">
                                            <span class="d-flex align-items-center">
                                                <i class="ph-duotone ph-user-circle"></i>
                                                <span>Edit profile</span>
                                            </span>
                                        </a>
                                        <router-link :to="notificationsPagePath" class="dropdown-item">
                                            <span class="d-flex align-items-center">
                                                <i class="ph-duotone ph-bell"></i>
                                                <span>Notifications</span>
                                            </span>
                                        </router-link>
                                        <router-link :to="settingsPagePath" class="dropdown-item">
                                            <span class="d-flex align-items-center">
                                                <i class="ph-duotone ph-gear-six"></i>
                                                <span>Settings</span>
                                            </span>
                                        </router-link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="dropdown-footer border-top p-2">
                            <button type="button" class="dropdown-item border-0 bg-transparent w-100 text-start d-flex align-items-center text-danger" @click.prevent="logout">
                                <i class="ph-duotone ph-power me-2"></i>
                                <span>Logout</span>
                            </button>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Change Password Modal -->
</header>
</template>

<script>
import * as authService from '@/services/auth_service';
import API from '@/services/api_service';
import { getTheme, setTheme } from '@/utils/theme';
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';

const NOTIFICATION_POLL_INTERVAL = 30000; // 30 seconds

export default {
    setup() {
        const store = useStore();
        const router = useRouter();
        const toast = useToast();
        const currentTheme = computed(() => store.state.theme || 'light');
        const userRole = computed(() => store.state.profile?.role_id);
        const isCustomer = computed(() => userRole.value === 3);
        const isLoggedIn = computed(() => !!authService.getAccessToken());
        const cartItemCount = computed(() => store.getters.cartItemCount ?? 0);
        const userName = computed(() => store.state.profile?.name || 'User');
        const userEmail = computed(() => store.state.profile?.email || '');

        const notifications = ref([]);
        const notificationsLoading = ref(false);
        const notificationUnreadCount = ref(0);
        let notificationPollTimer = null;

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

        const notificationsPagePath = computed(() => {
            const roleId = store.state.profile?.role_id;
            const map = { 1: '/admin/notifications', 2: '/vendor/notifications', 3: '/customer/notifications', 4: '/rider/notifications' };
            return map[roleId] || '/customer/notifications';
        });
        const settingsPagePath = computed(() => {
            const roleId = store.state.profile?.role_id;
            const map = { 1: '/admin/settings', 2: '/vendor/settings', 3: '/customer/settings', 4: '/rider/settings' };
            return map[roleId] || '/customer/settings';
        });


        async function fetchNotifications() {
            if (!authService.getAccessToken()) return;
            notificationsLoading.value = true;
            try {
                const res = await API.notifications.list({ per_page: 20 });
                notifications.value = res.data.notifications || [];
                notificationUnreadCount.value = res.data.unread_count ?? 0;
            } catch (_) {
                // ignore
            } finally {
                notificationsLoading.value = false;
            }
        }

        async function markNotificationRead(id) {
            try {
                await API.notifications.markAsRead(id);
                const n = notifications.value.find((x) => x.id === id);
                if (n) n.read_at = new Date().toISOString();
                notificationUnreadCount.value = Math.max(0, notificationUnreadCount.value - 1);
            } catch (_) {}
        }

        async function markAllNotificationsRead() {
            try {
                await API.notifications.markAsRead();
                notifications.value.forEach((n) => { n.read_at = n.read_at || new Date().toISOString(); });
                notificationUnreadCount.value = 0;
            } catch (_) {}
        }

        function applyTheme(mode) {
            const value = mode === 'default' ? 'light' : mode;
            setTheme(value);
            store.commit('SET_THEME', value);
        }

        function openCartSidebar() {
            store.commit('OPEN_CART_SIDEBAR');
        }

        const searchInput = ref(null);
        const searchText = ref('');
        const profileDropdownOpen = ref(false);
        const profileDropdownWrap = ref(null);

        function toggleProfileDropdown() {
            profileDropdownOpen.value = !profileDropdownOpen.value;
        }

        function closeProfileDropdown(event) {
            if (profileDropdownWrap.value && !profileDropdownWrap.value.contains(event.target)) {
                profileDropdownOpen.value = false;
            }
        }

        onMounted(() => {
            if (!store.state.profile) {
                store.dispatch('fetchUserProfile');
            }
            if (authService.getAccessToken()) {
                fetchNotifications();
                notificationPollTimer = setInterval(fetchNotifications, NOTIFICATION_POLL_INTERVAL);
            }
            window.addEventListener('keydown', handleKeyDown);
            document.addEventListener('click', closeProfileDropdown);
        });

        onBeforeUnmount(() => {
            if (notificationPollTimer) clearInterval(notificationPollTimer);
            window.removeEventListener('keydown', handleKeyDown);
            document.removeEventListener('click', closeProfileDropdown);
        });

        function logout() {
            profileDropdownOpen.value = false;
            authService.logout().catch(() => {
                toast.error('Something went wrong. Please try again.');
            });
        }

        function handleKeyDown(event) {
            // Check for Ctrl+F (Windows) or Cmd+F (Mac)
            if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
                event.preventDefault(); // Prevent default browser search
                searchInput.value.focus();
            }
        }

        const searchInPage = () => {
            // Remove previous highlights
            const highlights = document.getElementsByClassName('search-highlight');
            while (highlights.length > 0) {
                const highlight = highlights[0];
                const parent = highlight.parentNode;
                parent.replaceChild(document.createTextNode(highlight.textContent), highlight);
                parent.normalize();
            }

            if (!searchText.value) return;

            // Search through all text nodes
            const walker = document.createTreeWalker(
                document.body,
                NodeFilter.SHOW_TEXT,
                null,
                false
            );

            let node;
            while (node = walker.nextNode()) {
                const text = node.textContent;
                const index = text.toLowerCase().indexOf(searchText.value.toLowerCase());

                if (index >= 0) {
                    const span = document.createElement('span');
                    span.className = 'search-highlight';

                    const before = text.substring(0, index);
                    const match = text.substring(index, index + searchText.value.length);
                    const after = text.substring(index + searchText.value.length);

                    const textNode = document.createTextNode(before);
                    const matchNode = document.createElement('span');
                    matchNode.className = 'search-highlight';
                    matchNode.textContent = match;
                    const afterNode = document.createTextNode(after);

                    const parent = node.parentNode;
                    parent.replaceChild(textNode, node);
                    parent.insertBefore(matchNode, textNode.nextSibling);
                    parent.insertBefore(afterNode, matchNode.nextSibling);
                }
            }
        };

        return {
            isCustomer,
            isLoggedIn,
            cartItemCount,
            openCartSidebar,
            userRole,
            userName,
            userEmail,
            profileDropdownOpen,
            toggleProfileDropdown,
            profileDropdownWrap,
            logout,
            handleKeyDown,
            searchInput,
            searchText,
            searchInPage,
            currentTheme,
            applyTheme,
            notificationUnreadCount,
            notificationsPagePath,
            settingsPagePath,
        };
    },
};
</script>

<style>
.search-highlight {
    background-color: yellow;
    padding: 2px;
    border-radius: 2px;
}

.input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    pointer-events: none;
    z-index: 2;
}

.form-search {
    position: relative;
    display: flex;
    align-items: center;
}

.form-search .form-control.with-icon {
    padding-left: 40px;
}

.form-search .btn-search {
    position: absolute;
    right: 5px;
    background: transparent;
    border: none;
    color: #666;
    padding: 5px;
    transition: color 0.3s ease;
}

.form-search .btn-search:hover {
    color: #333;
}

.icon-search {
    position: absolute;
    left: 10px;
    color: #666;
}
</style>

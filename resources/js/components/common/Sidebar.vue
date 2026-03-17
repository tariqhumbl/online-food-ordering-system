<template>
    <nav class="pc-sidebar">
        <div class="navbar-wrapper">
            <!-- Logo Section (click to go to landing page) -->
            <div class="m-header">
                <a href="/" class="b-brand text-primary" @click.prevent="goToLanding">
                    <img
                        src="/public/assets/images/logo-dark.svg"
                        alt="logo image"
                        class="logo-lg"
                    />
                    <span
                        class="badge bg-brand-color-2 rounded-pill ms-1 theme-version"
                        >v1.3.0</span
                    >
                </a>
            </div>

            <div class="navbar-content">
                <ul class="pc-navbar">
                    <li class="pc-item pc-caption">
                        <label>Navigation</label>
                        <i class="ph-duotone ph-gauge"></i>
                    </li>

                    <!-- Dynamic Sidebar Items -->
                    <template
                        v-for="item in (sidebarItems[userRole] || sidebarItems[3])"
                        :key="item.title"
                    >
                        <!-- Dropdown Menu -->
                        <li v-if="item.children" class="pc-item pc-hasmenu">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i :class="item.icon"></i>
                                </span>
                                <span class="pc-mtext">{{ item.title }}</span>
                                <span class="pc-arrow">
                                    <i class="material-icons">expand_more</i>
                                </span>
                            </a>
                            <ul class="pc-submenu">
                                <li
                                    v-for="child in item.children"
                                    :key="child.title"
                                    class="pc-item"
                                >
                                    <a :href="child.link" class="pc-link">
                                        <span class="pc-micon">
                                            <i :class="child.icon"></i>
                                        </span>
                                        <span class="pc-mtext">{{
                                            child.title
                                        }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Regular Sidebar Item -->
                        <li v-else class="pc-item">
                            <a :href="item.link" class="pc-link">
                                <span class="pc-micon">
                                    <i :class="item.icon"></i>
                                </span>
                                <span class="pc-mtext">{{ item.title }}</span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- User Profile Section -->
            <div class="card pc-user-card sidebar-profile-block" ref="sidebarProfileWrap">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <img
                                src="/public/assets/images/user/avatar-1.jpg"
                                alt="user-image"
                                class="user-avatar wid-45 rounded-circle"
                            />
                        </div>
                        <div class="flex-grow-1 ms-3 min-w-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 me-2 min-w-0">
                                    <h6 class="mb-0 text-truncate">{{ userName }}</h6>
                                    <small class="d-block">{{ userRoleLabel }}</small>
                                </div>
                                <div class="flex-shrink-0">
                                    <button
                                        type="button"
                                        class="btn btn-icon btn-link-secondary avatar border-0 bg-transparent p-0"
                                        aria-label="Open menu"
                                        :aria-expanded="sidebarProfileOpen"
                                        @click.stop="toggleSidebarProfile"
                                    >
                                        <i class="ph-duotone ph-diamonds-four f-20"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-show="sidebarProfileOpen"
                        class="sidebar-profile-dropdown"
                    >
                        <ul class="list-unstyled mb-0">
                            <li>
                                <router-link
                                    to="/update-profile"
                                    class="pc-user-links d-flex align-items-center"
                                    @click="closeDropdown"
                                >
                                    <i class="ph-duotone ph-user-circle"></i>
                                    <span>Edit profile</span>
                                </router-link>
                            </li>
                            <li>
                                <router-link
                                    :to="settingsPagePath"
                                    class="pc-user-links d-flex align-items-center"
                                    @click="closeDropdown"
                                >
                                    <i class="ph-duotone ph-gear"></i>
                                    <span>Settings</span>
                                </router-link>
                            </li>
                            <li class="border-top">
                                <a
                                    href="#"
                                    class="pc-user-links d-flex align-items-center text-danger"
                                    @click.prevent="handleLogout"
                                >
                                    <i class="ph-duotone ph-power"></i>
                                    <span>Logout</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
import * as authService from "@/services/auth_service";
import { computed, onMounted, onBeforeUnmount, ref } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";

export default {
    setup() {
        const store = useStore();
        const router = useRouter();
        const sidebarProfileOpen = ref(false);
        const sidebarProfileWrap = ref(null);

        function closeSidebarProfile(event) {
            if (sidebarProfileWrap.value && !sidebarProfileWrap.value.contains(event.target)) {
                sidebarProfileOpen.value = false;
            }
        }

        function toggleSidebarProfile() {
            sidebarProfileOpen.value = !sidebarProfileOpen.value;
        }

        // Get user details from Vuex
        const userRole = computed(() => store.state.profile?.role_id);
        const userName = computed(() => store.state.profile?.name || "User");

        // Map role_id to readable labels
        const roleLabels = {
            1: "Admin",
            2: "Vendor",
            3: "Customer",
            4: "Delivery Rider",
        };
        const userRoleLabel = computed(
            () => roleLabels[userRole.value] || "User",
        );
        const settingsPagePath = computed(() => {
            const map = { 1: "/admin/settings", 2: "/vendor/settings", 3: "/customer/settings", 4: "/rider/settings" };
            return map[userRole.value] || "/customer/settings";
        });

        // Sidebar items - Online Food Ordering System
        const sidebarItems = {
            1: [
                { title: "Dashboard", link: "/admin/dashboard", icon: "ph-duotone ph-house-line" },
                { title: "Restaurants", link: "/admin/restaurants", icon: "ph-duotone ph-storefront" },
                { title: "Riders", link: "/admin/riders", icon: "ph-duotone ph-package" },
                { title: "Orders", link: "/admin/orders", icon: "ph-duotone ph-list-checks" },
                { title: "Update Profile", link: "/update-profile", icon: "ph-duotone ph-user" },
            ],
            2: [
                { title: "Dashboard", link: "/vendor/dashboard", icon: "ph-duotone ph-house-line" },
                { title: "My Restaurant", link: "/vendor/restaurant", icon: "ph-duotone ph-storefront" },
                { title: "Menu", link: "/vendor/menu", icon: "ph-duotone ph-cooking-pot" },
                { title: "Orders", link: "/vendor/orders", icon: "ph-duotone ph-list-checks" },
                { title: "Update Profile", link: "/update-profile", icon: "ph-duotone ph-user" },
            ],
            3: [
                { title: "Dashboard", link: "/customer/dashboard", icon: "ph-duotone ph-house-line" },
                { title: "Restaurants", link: "/customer/restaurants", icon: "ph-duotone ph-storefront" },
                { title: "My Orders", link: "/customer/orders", icon: "ph-duotone ph-list-checks" },
                { title: "Update Profile", link: "/update-profile", icon: "ph-duotone ph-user" },
            ],
            4: [
                { title: "Dashboard", link: "/rider/dashboard", icon: "ph-duotone ph-house-line" },
                { title: "Deliveries", link: "/rider/deliveries", icon: "ph-duotone ph-package" },
                { title: "Notifications", link: "/rider/notifications", icon: "ph-duotone ph-bell" },
                { title: "Settings", link: "/rider/settings", icon: "ph-duotone ph-gear" },
                { title: "Update Profile", link: "/update-profile", icon: "ph-duotone ph-user" },
            ],
        };

        onMounted(() => {
            if (!store.state.profile) {
                store.dispatch("fetchUserProfile");
            }
            document.addEventListener("click", closeSidebarProfile);
        });

        onBeforeUnmount(() => {
            document.removeEventListener("click", closeSidebarProfile);
        });

        function closeDropdown() {
            sidebarProfileOpen.value = false;
        }

        function handleLogout() {
            sidebarProfileOpen.value = false;
            authService.logout().catch(() => {});
        }

        function goToLanding() {
            router.push('/');
        }

        return {
            userRole,
            userName,
            userRoleLabel,
            settingsPagePath,
            sidebarItems,
            sidebarProfileOpen,
            sidebarProfileWrap,
            toggleSidebarProfile,
            closeDropdown,
            handleLogout,
            goToLanding,
        };
    },
};
</script>

<style scoped>
.sidebar-profile-block {
    position: relative;
}
.sidebar-profile-dropdown {
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    margin-bottom: 4px;
    background: var(--pc-sidebar-background, #fff);
    border: 1px solid var(--pc-sidebar-submenu-border-color, #eee);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1050;
    padding: 0.5rem 0;
    min-width: 100%;
}
.sidebar-profile-dropdown .pc-user-links {
    display: flex !important;
    flex-direction: row;
    align-items: center;
    padding: 0.5rem 1rem;
    text-decoration: none;
    color: var(--pc-sidebar-color, #39465f);
}
.sidebar-profile-dropdown .pc-user-links i {
    margin-right: 0.5rem;
    margin-bottom: 0;
    font-size: 1.1rem;
}
.sidebar-profile-dropdown .pc-user-links:hover {
    background: rgba(0, 0, 0, 0.06);
}
.sidebar-profile-dropdown li {
    list-style: none;
}
.sidebar-profile-dropdown .text-danger:hover {
    background: rgba(220, 53, 69, 0.1);
}
</style>

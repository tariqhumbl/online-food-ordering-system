<template>
  <div class="auth-main v1">
    <div class="auth-wrapper">
      <div class="auth-form">
        <div class="card my-5 shadow-sm">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <img
                src="/public/assets/images/authentication/profile.jpg"
                alt="Profile"
                class="rounded-circle img-fluid mb-3"
                height="120"
                width="120"
                style="object-fit: cover;"
              />
              <h4 class="fw-semibold mb-1">Profile Update</h4>
              <p class="text-muted small mb-0">
                View and edit your account information
              </p>
              <p class="mt-2 mb-0">
                <router-link :to="dashboardLink" class="link-primary">
                  <i class="ph-duotone ph-arrow-left me-1"></i>Back to Dashboard
                </router-link>
              </p>
            </div>

            <div v-if="loading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted small">Loading profile...</p>
            </div>

            <form v-else @submit.prevent="submitProfile" class="needs-validation">
              <!-- Read-only info -->
              <div class="mb-3">
                <label class="form-label text-muted small">Role</label>
                <div class="form-control bg-light border-0">
                  {{ roleLabel }}
                </div>
              </div>
              <div v-if="profile.restaurant_id" class="mb-3">
                <label class="form-label text-muted small">Restaurant ID</label>
                <div class="form-control bg-light border-0">
                  {{ profile.restaurant_id }}
                </div>
              </div>

              <hr class="my-4" />

              <!-- Editable fields -->
              <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  placeholder="Your name"
                  required
                  maxlength="150"
                />
                <small v-if="errors.name" class="text-danger">{{ errors.name }}</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input
                  v-model="form.email"
                  type="email"
                  class="form-control"
                  placeholder="your@email.com"
                  required
                />
                <small v-if="errors.email" class="text-danger">{{ errors.email }}</small>
              </div>

              <div v-if="errors.general" class="alert alert-danger py-2 small">
                {{ errors.general }}
              </div>

              <div class="d-grid gap-2 mt-4">
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="saving"
                >
                  <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status"></span>
                  {{ saving ? 'Saving...' : 'Update Profile' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useStore } from 'vuex';
import { useToast } from 'vue-toastification';
import * as authService from '@/services/auth_service';

const ROLE_LABELS = { 1: 'Admin', 2: 'Vendor', 3: 'Customer', 4: 'Rider' };

export default {
  name: 'UpdateProfile',
  setup() {
    const router = useRouter();
    const route = useRoute();
    const store = useStore();
    const toast = useToast();

    const loading = ref(true);
    const saving = ref(false);
    const profile = reactive({
      id: null,
      name: '',
      email: '',
      role_id: null,
      restaurant_id: null,
    });
    const form = reactive({ name: '', email: '' });
    const errors = reactive({ name: '', email: '', general: '' });

    const roleLabel = computed(() =>
      profile.role_id != null ? ROLE_LABELS[profile.role_id] || 'User' : '—'
    );

    const dashboardLink = computed(() => {
      const roleId = store.state.profile?.role_id ?? profile.role_id;
      const pathMap = {
        1: '/admin/dashboard',
        2: '/vendor/dashboard',
        3: '/customer/dashboard',
        4: '/rider/dashboard',
      };
      return pathMap[roleId] || '/dashboard';
    });

    function clearErrors() {
      errors.name = '';
      errors.email = '';
      errors.general = '';
    }

    function applyProfileToForm(data) {
      if (!data) return;
      profile.id = data.id ?? profile.id;
      profile.name = data.name ?? '';
      profile.email = data.email ?? '';
      profile.role_id = data.role_id ?? profile.role_id;
      profile.restaurant_id = data.restaurant_id ?? profile.restaurant_id;
      form.name = profile.name;
      form.email = profile.email;
    }

    async function loadProfile() {
      loading.value = true;
      clearErrors();
      // Show existing info immediately from store or localStorage so old information appears right away
      const fromStore = store.state.profile;
      const fromStorage = authService.getUser();
      const existing = fromStore || fromStorage;
      if (existing) {
        applyProfileToForm(existing);
      }
      try {
        const data = await authService.getProfile();
        applyProfileToForm(data);
      } catch (err) {
        if (err.response?.status === 401) {
          router.push('/login');
          return;
        }
        // Keep the existing data we already filled from store/storage; only toast if we had nothing
        if (!existing) {
          toast.error('Failed to load profile.');
        }
      } finally {
        loading.value = false;
      }
    }

    async function submitProfile() {
      saving.value = true;
      clearErrors();
      try {
        const data = await authService.updateProfile({
          name: form.name.trim(),
          email: form.email.trim(),
        });
        Object.assign(profile, data);
        authService.setUser(data);
        store.commit('SET_PROFILE', data);
        toast.success('Profile updated successfully.');
        router.push(dashboardLink.value);
      } catch (err) {
        if (err.response?.status === 422 && err.response?.data?.errors) {
          const e = err.response.data.errors;
          errors.name = Array.isArray(e.name) ? e.name[0] : e.name || '';
          errors.email = Array.isArray(e.email) ? e.email[0] : e.email || '';
        } else {
          errors.general = err.response?.data?.message || 'Something went wrong. Please try again.';
        }
        toast.error('Could not update profile.');
      } finally {
        saving.value = false;
      }
    }

    onMounted(() => {
      loadProfile();
    });

    return {
      loading,
      saving,
      profile,
      form,
      errors,
      roleLabel,
      dashboardLink,
      submitProfile,
    };
  },
};
</script>

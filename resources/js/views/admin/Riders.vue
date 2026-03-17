<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/admin/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Riders</li>
            </ul>
          </div>
          <div class="col-12">
            <div class="page-header-title d-flex flex-wrap align-items-center justify-content-between gap-2">
              <h2 class="mb-0">Delivery Riders</h2>
              <button type="button" class="btn btn-primary" @click="openAddModal">
                <i class="ph-duotone ph-plus me-1"></i> Add Rider
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <p class="text-muted mb-4">Create and manage delivery rider accounts. Riders can log in to the rider portal to view and manage assigned deliveries.</p>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Loading riders...</p>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
        <div v-else-if="!riders.length" class="text-center py-5 text-muted">
          <i class="ph-duotone ph-package" style="font-size: 3rem; opacity: 0.5;"></i>
          <p class="mt-3 mb-0">No riders yet.</p>
          <p class="small">Click <strong>Add Rider</strong> to create a delivery rider account.</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Joined</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in riders" :key="r.id">
                <td><strong>{{ r.name }}</strong></td>
                <td>{{ r.email }}</td>
                <td>{{ formatDate(r.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add Rider Modal -->
    <div class="modal fade" id="riderModal" ref="riderModal" tabindex="-1" aria-labelledby="riderModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="riderModalLabel">Add Rider</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="createRider">
            <div class="modal-body">
              <p class="text-muted small mb-3">The rider will use this email and password to log in to the rider portal.</p>
              <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input v-model="form.name" type="text" class="form-control" placeholder="Full name" required />
                <small v-if="errors.name" class="text-danger">{{ errors.name }}</small>
              </div>
              <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input v-model="form.email" type="email" class="form-control" placeholder="rider@example.com" required />
                <small v-if="errors.email" class="text-danger">{{ errors.email }}</small>
              </div>
              <div class="mb-3">
                <label class="form-label">Password <span class="text-danger">*</span></label>
                <input v-model="form.password" type="password" class="form-control" placeholder="Min. 8 characters" minlength="8" required />
                <small v-if="errors.password" class="text-danger">{{ errors.password }}</small>
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm password <span class="text-danger">*</span></label>
                <input v-model="form.password_confirmation" type="password" class="form-control" placeholder="Repeat password" required />
                <small v-if="errors.password_confirmation" class="text-danger">{{ errors.password_confirmation }}</small>
              </div>
              <div v-if="formError" class="alert alert-danger mb-0">{{ formError }}</div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                {{ saving ? 'Creating...' : 'Create Rider' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { Modal } from 'bootstrap';
import { ref, reactive, onMounted } from 'vue';
import { useToast } from 'vue-toastification';

export default {
  name: 'AdminRiders',
  setup() {
    const toast = useToast();
    const riderModal = ref(null);
    let modalBs = null;

    const riders = ref([]);
    const loading = ref(false);
    const error = ref('');
    const saving = ref(false);
    const formError = ref('');
    const errors = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    });

    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    });

    function resetForm() {
      form.name = '';
      form.email = '';
      form.password = '';
      form.password_confirmation = '';
      formError.value = '';
      errors.name = '';
      errors.email = '';
      errors.password = '';
      errors.password_confirmation = '';
    }

    function formatDate(value) {
      if (!value) return '—';
      const d = new Date(value);
      return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    }

    async function loadRiders() {
      loading.value = true;
      error.value = '';
      try {
        const { data } = await API.admin.riders.list();
        riders.value = Array.isArray(data) ? data : [];
      } catch (e) {
        error.value = e.response?.data?.message || 'Failed to load riders.';
      } finally {
        loading.value = false;
      }
    }

    function openAddModal() {
      resetForm();
      if (!modalBs) modalBs = new Modal(riderModal.value);
      modalBs.show();
    }

    async function createRider() {
      saving.value = true;
      formError.value = '';
      errors.name = '';
      errors.email = '';
      errors.password = '';
      errors.password_confirmation = '';

      try {
        await API.admin.riders.create({
          name: form.name.trim(),
          email: form.email.trim(),
          password: form.password,
          password_confirmation: form.password_confirmation,
        });
        toast.success('Rider created successfully. Login credentials have been sent to their email.');
        modalBs.hide();
        loadRiders();
      } catch (e) {
        const res = e.response;
        if (res?.status === 422 && res.data?.errors) {
          const errs = res.data.errors;
          if (errs.name) errors.name = Array.isArray(errs.name) ? errs.name[0] : errs.name;
          if (errs.email) errors.email = Array.isArray(errs.email) ? errs.email[0] : errs.email;
          if (errs.password) errors.password = Array.isArray(errs.password) ? errs.password[0] : errs.password;
          if (errs.password_confirmation) errors.password_confirmation = Array.isArray(errs.password_confirmation) ? errs.password_confirmation[0] : errs.password_confirmation;
        } else {
          formError.value = res?.data?.message || 'Failed to create rider.';
        }
      } finally {
        saving.value = false;
      }
    }

    onMounted(() => {
      loadRiders();
    });

    return {
      riders,
      loading,
      error,
      form,
      errors,
      formError,
      saving,
      riderModal,
      openAddModal,
      createRider,
      formatDate,
    };
  },
};
</script>

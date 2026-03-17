<template>
  <div class="modal fade" id="editTailorModal" tabindex="-1" aria-labelledby="editTailorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editTailorLabel">Change Tailor Shop</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="updateTailorInformation">
            <div class="row">
              <div class="col-md-12">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Tailor*</label>
                  <select class="form-select shadow-sm" v-model="form.user_id" required>
                    <option value="">Select Tailor</option>
                    <option v-for="tailor in allTailors" :key="tailor.user.id" :value="tailor.user.id">
                      {{ tailor.user.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-12">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Tailor Shop*</label>
                  <select class="form-select shadow-sm" v-model="form.tailor_shop_id" required>
                    <option value="">Select Tailor Shop</option>
                    <option v-for="shop in tailorShops" :key="shop.id" :value="shop.id">
                      {{ shop.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-12">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Status*</label>
                  <select class="form-select shadow-sm" v-model="form.status" required>
                    <option value="">Select Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="text-end">
              <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary ms-2" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Update
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import * as tailorService from '../../services/tailor_service';
import { ref, onMounted, watch } from 'vue';
import { useToast } from "vue-toastification";
import { useStore } from 'vuex';
import { Modal } from "bootstrap";

export default {
  name: 'EditTailor',
  props: {
    tailor: {
      type: Object,
      required: true,
    },
  },
  emits: ['tailor-updated'],
  setup(props, { emit }) {
    const store = useStore();
    const toast = useToast();
    const form = ref({
      user_id: '',
      tailor_shop_id: '',
      status: ''
    });
    const loading = ref(false);
    const tailorShops = ref([]);
    const allTailors = ref([]);
    const errors = ref([]);

    const getTailorShop = async () => {
      try {
        store.state.isLoading = true;
        const response = await tailorService.getTailorShop();
        tailorShops.value = response.data;
        store.state.isLoading = false;
      } catch (error) {
        console.error('Error fetching tailor shops:', error);
        toast.error("Failed to fetch tailor shops!");
        store.state.isLoading = false;
      }
    };

    const getTailorList = async () => {
      try {
        store.state.isLoading = true;
        const response = await tailorService.getTailorList();
        allTailors.value = response.data.data;
        console.log('allTailors sdfsdfsf:', allTailors.value);

        store.state.isLoading = false;
      } catch (error) {
        console.error('Error fetching tailors:', error);
        toast.error("Failed to fetch tailors!");
        store.state.isLoading = false;
      }
    };

    const updateTailorInformation = async () => {
      loading.value = true;
      try {
        if (!props.tailor || !props.tailor.id) {
          throw new Error('Invalid tailor data');
        }
        if (!form.value.user_id || !form.value.tailor_shop_id || !form.value.status) {
          throw new Error('All fields are required');
        }
        const updateData = {
          user_id: form.value.user_id,
          tailor_shop_id: form.value.tailor_shop_id,
          status: form.value.status
        };
        const response = await tailorService.updateTailorInformation(props.tailor.id, updateData);
        emit('tailor-updated', response.data);
        Modal.getInstance(document.getElementById("editTailorModal"))?.hide();
        toast.success("Tailor updated successfully!");
      } catch (error) {
        console.error('Error updating tailor:', error);
        console.error('Error details:', {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        toast.error(error.response?.data?.message || error.message || "Failed to update tailor!");
      } finally {
        loading.value = false;
      }
    };

    const hideModal = () => {
      const modalElement = document.getElementById('editTailorModal');
      if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }
      }
    };

    const showModal = () => {
      new Modal(document.getElementById("editTailorModal")).show();
    };

    watch(
      () => props.tailor,
      (newTailor) => {
        if (newTailor) {
          form.value = {
            user_id: newTailor.user?.id || '',
            tailor_shop_id: newTailor.tailor_shop_id || newTailor.tailor_shop?.id || '',
            status: newTailor.status || ''
          };
        }
      },
      { immediate: true }
    );

    onMounted(() => {
      getTailorShop();
      getTailorList();
    });

    return {
      form,
      loading,
      tailorShops,
      allTailors,
      updateTailorInformation,
      showModal,
      hideModal,
      errors
    };
  },
};
</script>

<style scoped>
.modal {
    background-color: rgba(0, 0, 0, 0.5);
}
</style>

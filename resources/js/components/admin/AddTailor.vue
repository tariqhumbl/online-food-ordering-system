<template>
<div class="modal fade" id="addTailorModal" tabindex="-1" aria-labelledby="addTailorShopLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTailorShopLabel">Add New Tailor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="CreateTailor">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Name*</label>
                                <input type="text" class="form-control shadow-sm" name="name" v-model="tailor.name" placeholder="Tailor Name" />
                            </div>
                            <small v-if="errors.name" class="text-danger">{{ errors.name }}</small>

                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email*</label>
                                <input type="email" class="form-control shadow-sm" name="email" v-model="tailor.email" placeholder="example@gmail.com" />
                            </div>
                            <small v-if="errors.email" class="text-danger">{{ errors.email }}</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tailor Shop*</label>
                                <select class="form-select shadow-sm" name="tailor_shop_id" v-model="tailor.tailor_shop_id">
                                    <option disabled value="">Select a tailor shop</option>
                                    <option v-for="tailor in tailorShops" :key="tailor.id" :value="tailor.id">
                                        {{ tailor.name }}
                                    </option>
                                </select>
                            </div>
                            <small v-if="errors.name" class="text-danger">{{ errors.name }}</small>

                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary ms-2" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Save
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
import {
    reactive,
    ref,
    toRefs,
    onMounted

} from 'vue';
import {
    useToast
} from "vue-toastification";
import {
    useStore
} from 'vuex';
import {
    Modal
} from "bootstrap";
export default {
    name: 'AddTailor',
    emits: ['close', 'tailor-added'],
    setup(props, ctx) {
        const store = useStore();
        const toast = useToast();
        const loading = ref(false);
        const state = reactive({
            tailorShops: [],
            tailor: {
                name: '',
                email: '',
                tailor_shop_id: '',
            },
            errors: {},
        });

        getTailorShop();
        async function getTailorShop() {
            try {
                store.state.isLoading = true;
                const response = await tailorService.getTailorShop();
                state.tailorShops = response.data;
                store.state.isLoading = false;
            } catch (error) {
                console.log('Error:', error);
                toast.error("Some error occurred, please try again!");
                store.state.isLoading = false;
            }
        }

        const showModal = () => new Modal(document.getElementById("addTailorModal")).show();

        function hideModal() {
            addTailorModal.value = false;
        }

        async function CreateTailor() {
            try {
                state.errors = {};
                loading.value = true;
                store.state.isLoading = true;
                const response = await tailorService.CreateTailor(state.tailor);
                for (const key in state.tailor) {
                    state.tailor[key] = '';
                }
                if (response.status !== 200 && response.status !== 201) {
                    throw new Error(`Unexpected response status: ${response.status}`);
                }
                ctx.emit('tailorAdded', response.data.tailor);
                const modalElement = document.getElementById("addTailorModal");
                if (modalElement) {
                    const modal = Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
                toast.success("Tailor added successfully and credentials sent to the provided email.");
            } catch (error) {
                console.error("Error Occurred:", error);
                if (error.response) {
                    console.error("Error Response:", error.response);
                }
                if (error.response && error.response.status === 422) {
                    state.errors = Object.fromEntries(
                        Object.entries(error.response.data.errors).map(([field, [message]]) => [field, message])
                    );
                } else {
                    state.errors = {
                        general: 'Something went wrong'
                    };
                }
                toast.error("Some error occurred, please try again!");
            } finally {
                loading.value = false;
                store.state.isLoading = false;
            }
        }

        return {
            ...toRefs(state),
            loading,
            showModal,
            hideModal,
            CreateTailor
        };
    },
};
</script>

<style scoped>
.modal {
    background-color: rgba(0, 0, 0, 0.5);
}
</style>

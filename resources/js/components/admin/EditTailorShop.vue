<template>
<!-- Bootstrap Modal -->
<div class="modal fade" id="editTailorShopModal" tabindex="-1" aria-labelledby="editTailorShopLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTailorShopLabel">Edit Tailor Shop</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="UpdateTailorShop">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Name*</label>
                                <input type="text" class="form-control shadow-sm" name="name" v-model="tailorShop.name" placeholder="Your Shop Name" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email*</label>
                                <input type="email" class="form-control shadow-sm" name="email" v-model="tailorShop.email" placeholder="example@gmail.com" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Contact* </label>
                                <input type="text" class="form-control shadow-sm" name="contact" v-model="tailorShop.contact" placeholder="03XX-XXXXXXX" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Address*</label>
                                <input type="text" class="form-control shadow-sm" name="address" v-model="tailorShop.address" placeholder="City Center, Bank Road Saddar, Rawalpandi, Punjab" />
                            </div>
                        </div>
                    </div>

                    <!-- Status Dropdown -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status*</label>
                                <select class="form-select shadow-sm" v-model="tailorShop.status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <small v-if="errors.status" class="text-danger mt-2">{{ errors.status }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="mb-3 p-3 bg-light rounded shadow-sm">
                        <div class="d-flex align-items-center">
                            <img :src="shopLogoUrl" alt="Shop Logo" class="rounded shadow me-3" ref="shopLogoImageDisplay" width="150" height="150" style="object-fit: cover;" />
                            <div class="ms-4">
                                <h5 class="mb-2 text-primary">Shop Logo</h5>
                                <div class="d-flex mt-3">
                                    <label class="btn btn-outline-primary me-2" for="change-picture">
                                        <i class="bi bi-image-fill me-1"></i> Change
                                        <input class="form-control" @change="attachImage" type="file" name="logo" id="change-picture" hidden accept="image/*" />
                                    </label>
                                    <button type="button" @click="removeImage" class="btn btn-outline-danger">
                                        <i class="bi bi-trash-fill me-1"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                        <small v-if="errors.logo" class="text-danger mt-2">{{ errors.logo }}</small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Close
                        </button>
                        <button type="submit" class="btn btn-primary ms-3">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import * as tailorShopService from '../../services/tailorShop_service';
import {
    reactive,
    ref,
    toRefs,
    computed
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
    props: {
        shopData: {
            type: Object,
            required: true
        }
    },
    setup(props, ctx) {
        const store = useStore();
        const toast = useToast();
        const state = reactive({
            tailorShop: {
                name: '',
                email: '',
                contact: '',
                address: '',
                logo: '',
                status: 'active'
            },
            errors: {},
        });

        const shopLogoImageDisplay = ref(null);
        const shopLogoUrl = computed(() => {
            if (state.tailorShop.logo) {
                return store.getters.getImagePath(state.tailorShop.logo);
            }
            return '/assets/images/placeholder/placeholder-image.png';
        });

        const showModal = () => {
            // Set the shop data when modal is shown
            state.tailorShop = { ...props.shopData };
            new Modal(document.getElementById("editTailorShopModal")).show();
        };

        function hideModal() {
            Modal.getInstance(document.getElementById("editTailorShopModal"))?.hide();
        }

        async function UpdateTailorShop() {
            try {
                state.errors = {};
                store.state.isLoading = true;

                const formData = new FormData();
                for (let key in state.tailorShop) {
                    if (key === "files") {
                        state.tailorShop[key].forEach((file) => {
                            formData.append("files[]", file);
                        });
                    } else {
                        formData.append(key, state.tailorShop[key]);
                    }
                }
                formData.append('_method', 'PUT'); // Laravel method spoofing for PUT request

                const response = await tailorShopService.updateTailorShop(props.shopData.id, formData);
                if (response.status !== 200) {
                    throw new Error(`Unexpected response status: ${response.status}`);
                }

                ctx.emit('tailorShopUpdated', response.data.data);
                hideModal();
                toast.success("Tailor shop updated successfully");
                store.state.isLoading = false;
            } catch (error) {
                store.state.isLoading = false;
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
            }
        }

        function attachImage(event) {
            state.tailorShop.logo = event.target.files[0];
            let reader = new FileReader();
            reader.addEventListener(
                "load",
                function () {
                    shopLogoImageDisplay.value.src = reader.result;
                }.bind(this),
                false
            );
            reader.readAsDataURL(state.tailorShop.logo);
        }

        function removeImage() {
            state.tailorShop.logo = null;
            shopLogoImageDisplay.value.src = '/assets/images/placeholder/placeholder-image.png';
        }

        return {
            ...toRefs(state),
            showModal,
            hideModal,
            UpdateTailorShop,
            shopLogoImageDisplay,
            shopLogoUrl,
            attachImage,
            removeImage,
        };
    }
};
</script> 
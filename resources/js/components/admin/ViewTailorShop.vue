<template>
<!-- Bootstrap Modal -->
<div class="modal fade" id="viewTailorShopModal" tabindex="-1" aria-labelledby="viewTailorShopLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTailorShopLabel">View Tailor Shop Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <!-- Shop Logo -->
                        <div class="text-center mb-4">
                            <img :src="shopLogoUrl" alt="Shop Logo" class="img-fluid rounded shadow" style="max-height: 200px; width: auto;" />
                        </div>
                        <hr class="my-4">
                        <!-- Shop Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Shop Name</label>
                                    <p class="form-control-plaintext">{{ shopData?.name || 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Email</label>
                                    <p class="form-control-plaintext">{{ shopData?.email || 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Contact</label>
                                    <p class="form-control-plaintext">{{ shopData?.contact || 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <p class="form-control-plaintext">
                                        <span :class="['badge', shopData?.status === 'active' ? 'bg-success' : 'bg-danger']">
                                            {{ shopData?.status || 'N/A' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Address</label>
                            <p class="form-control-plaintext">{{ shopData?.address || 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import { computed } from 'vue';
import { useStore } from 'vuex';
import { Modal } from "bootstrap";

export default {
    props: {
        shopData: {
            type: Object,
            required: true
        }
    },
    setup(props) {
        const store = useStore();

        const shopLogoUrl = computed(() => {
            if (props.shopData && props.shopData.logo) {
                return store.getters.getImagePath(props.shopData.logo);
            }
            return '/assets/images/placeholder/placeholder-image.png';
        });

        const showModal = () => {
            new Modal(document.getElementById("viewTailorShopModal")).show();
        };

        const hideModal = () => {
            Modal.getInstance(document.getElementById("viewTailorShopModal"))?.hide();
        };

        return {
            shopLogoUrl,
            showModal,
            hideModal
        };
    }
};
</script>

<style scoped>
.form-control-plaintext {
    padding: 0.375rem 0;
    margin-bottom: 0;
    line-height: 1.5;
    color: #495057;
    background-color: transparent;
    border: solid transparent;
    border-width: 1px 0;
}
</style> 
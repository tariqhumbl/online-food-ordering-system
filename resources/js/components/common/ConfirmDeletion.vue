<template>
    <div class="modal fade modal-danger text-start" id="confirmDeletionModal" ref="confirmDeletionModal" tabindex="-1"
        aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form v-on:submit.prevent="$emit('confirmedDeletion')">
                    <div class="modal-header">
                        <slot name="title" />
                    </div>
                    <div class="modal-body">
                        <div class="form form-horizontal">
                            <div class="row">
                                <div class="col-12">
                                    <slot name="content" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" v-on:click="hideModal">Cancel</button>
                        <button type="submit" class="btn btn-primary me-1">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import { Modal } from 'bootstrap';
export default {
    name: "ConfirmDeletion",
    setup() {
        let modal = {
            type: Object,
            default: {}
        };

        const confirmDeletionModal = ref(null);
        function showModal() {
            modal = new Modal(confirmDeletionModal.value);
            modal.show();
        }
        function hideModal() {
            modal.hide();
        }

        return {
            confirmDeletionModal,
            showModal,
            hideModal
        }
    },
}
</script>

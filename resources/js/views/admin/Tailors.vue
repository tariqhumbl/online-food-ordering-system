<template>
    <div>
        <div class="">
            <div class="pc-content">
                <!-- [ breadcrumb ] start -->
                <div class="page-header">
                    <div class="page-block">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <ul class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="/admin/dashboard">Home</a>
                                    </li>
                                    <li
                                        class="breadcrumb-item"
                                        aria-current="page"
                                    >
                                        Managers
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-12">
                                <div class="page-header-title mt-2">
                                    <h2 class="mb-0">Managers list</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ breadcrumb ] end -->

                <!-- [ Main Content ] start -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card table-card">
                            <div class="card-header">
                                <div
                                    class="d-sm-flex align-items-center justify-content-between"
                                >
                                    <div
                                        class="input-group"
                                        style="max-width: 400px"
                                    >
                                        <label
                                            class="d-flex align-items-center w-100"
                                        >
                                            <span class="me-2">Search:</span>
                                            <input
                                                v-model="searchQuery"
                                                type="text"
                                                class="form-control form-control-sm"
                                                placeholder="Search by name..."
                                            />
                                        </label>
                                    </div>

                                    <div class="text-end">
                                        <button
                                            class="btn btn-primary"
                                            @click="showAddTailorModal"
                                        >
                                            <i class="ti ti-plus f-18"></i> Add
                                            Tailor
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table
                                        class="table table-hover table-striped"
                                    >
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-end">#</th>
                                                <th class="text-center">
                                                    Name
                                                </th>
                                                <th class="text-center">
                                                    Email
                                                </th>
                                                <th class="text-center">
                                                    Tailor Shop
                                                </th>
                                                <th class="text-center">
                                                    Status
                                                </th>
                                                <th class="text-center">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    userTailor, index
                                                ) in filteredTailors"
                                                :key="index.id"
                                            >
                                                <td class="text-end">
                                                    {{
                                                        index +
                                                        1 +
                                                        (pages.current_page -
                                                            1) *
                                                            pages.per_page
                                                    }}
                                                </td>
                                                <td class="text-center">
                                                    {{ userTailor.user.name }}
                                                </td>
                                                <td class="text-center">
                                                    {{ userTailor.user.email }}
                                                </td>
                                                <td class="text-center">
                                                    {{
                                                        userTailor.tailor_shop
                                                            .name
                                                    }}
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        :class="[
                                                            'badge',
                                                            {
                                                                'bg-primary':
                                                                    userTailor.status ===
                                                                    'pending',
                                                                'bg-success':
                                                                    userTailor.status ===
                                                                    'approved',
                                                                'bg-danger':
                                                                    userTailor.status ===
                                                                    'rejected',
                                                            },
                                                        ]"
                                                        >{{
                                                            userTailor.status
                                                        }}</span
                                                    >
                                                </td>
                                                <td class="text-center">
                                                    <ul
                                                        class="list-inline mb-0"
                                                    >
                                                        <li
                                                            class="list-inline-item"
                                                            title="View"
                                                        >
                                                            <a
                                                                href="#"
                                                                class="avtar avtar-xs btn-link-primary"
                                                                @click.prevent="
                                                                    viewTailor(
                                                                        userTailor,
                                                                    )
                                                                "
                                                            >
                                                                <i
                                                                    class="ti ti-eye f-18"
                                                                ></i>
                                                            </a>
                                                        </li>
                                                        <li
                                                            class="list-inline-item"
                                                            title="Edit"
                                                        >
                                                            <a
                                                                href="#"
                                                                class="avtar avtar-xs btn-link-success"
                                                                @click.prevent="
                                                                    editTailor(
                                                                        userTailor,
                                                                    )
                                                                "
                                                            >
                                                                <i
                                                                    class="ti ti-edit-circle f-18"
                                                                ></i>
                                                            </a>
                                                        </li>
                                                        <li
                                                            class="list-inline-item"
                                                            title="Delete"
                                                        >
                                                            <a
                                                                href="#"
                                                                class="avtar avtar-xs btn-link-danger"
                                                                @click.prevent="
                                                                    deleteTailorHandler(
                                                                        userTailor.id,
                                                                    )
                                                                "
                                                            >
                                                                <i
                                                                    class="ti ti-trash f-18"
                                                                ></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div
                                class="card-footer d-flex justify-content-center"
                            >
                                <Pagination
                                    :pages="pages"
                                    v-on:loadMore="loadMoreRecords"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Main Content ] end -->
            </div>

            <!-- Add Tailor Shop Modal -->
            <!-- Add Tailor Modal -->
            <AddTailor ref="addTailorModal" @tailor-added="handleTailorAdded" />
            <EditTailor
                ref="editTailorModal"
                :tailor="selectedTailor"
                @tailor-updated="handleTailorUpdated"
            />
            <ViewTailor ref="viewTailorModal" :tailor="selectedTailor" />
            <ConfirmDeletion
                ref="confirmDeletionModal"
                :blog="selectedTailor"
                @confirmedDeletion="deleteTailor"
            >
                <template #title>
                    <h4 class="modal-title text-danger">Delete Tailor</h4>
                </template>
                <template #content>
                    <p>
                        Are you sure you want to delete
                        <span class="fw-bold text-danger">{{
                            selectedTailor?.user?.name
                        }}</span>
                        ?
                    </p>
                </template>
            </ConfirmDeletion>
        </div>
    </div>
</template>

<script>
import * as tailorService from "../../services/tailor_service";
import AddTailor from "@/components/admin/AddTailor.vue";
import EditTailor from "@/components/admin/EditTailor.vue";
import ViewTailor from "@/components/admin/ViewTailor.vue";
import Pagination from "../../components/common/Pagination.vue";
import ConfirmDeletion from "../../components/common/ConfirmDeletion.vue";
import { ref, reactive, computed, toRefs, nextTick, watch } from "vue";
import { useToast } from "vue-toastification";
import { useStore } from "vuex";
export default {
    name: "Tailors",
    components: {
        AddTailor,
        EditTailor,
        ViewTailor,
        Pagination,
        ConfirmDeletion,
    },
    setup() {
        const store = useStore();
        const toast = useToast();
        const state = reactive({
            tailors: [],
            userTailor: {},
            searchQuery: "",
            pages: {
                current_page: "",
                last_page: "",
                total: "",
                per_page: "",
                from: "",
                prev_page_url: null,
                next_page_url: null,
            },
            errors: {},
        });
        const searchQuery = ref("");
        const selectedTailor = ref({});
        const addTailorModal = ref();
        const editTailorModal = ref();
        const viewTailorModal = ref();
        const confirmDeletionModal = ref();

        // Add computed property for filtered tailors
        const filteredTailors = computed(() => {
            if (!state.searchQuery) return state.tailors;
            const query = state.searchQuery.toLowerCase();
            return state.tailors.filter(
                (tailor) =>
                    tailor.user.name.toLowerCase().includes(query) ||
                    tailor.user.email.toLowerCase().includes(query) ||
                    tailor.tailor_shop.name.toLowerCase().includes(query),
            );
        });

        // Watch for changes in selectedTailor to trigger reload
        watch(selectedTailor, () => {
            getTailorList();
        });

        getTailorList();
        async function getTailorList() {
            try {
                store.state.isLoading = true;
                const response = await tailorService.getTailorList();
                state.tailors = response.data.data;
                console.log("Tailors:", state.tailors);
                state.pages = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    total: response.data.total,
                    per_page: response.data.per_page,
                    from: response.data.from,
                    prev_page_url: response.data.prev_page_url,
                    next_page_url: response.data.next_page_url,
                };
                store.state.isLoading = false;
            } catch (error) {
                console.log("Error:", error);
                toast.error("Some error occurred, please try again!");
                store.state.isLoading = false;
            }
        }
        async function loadMoreRecords(page) {
            try {
                store.state.isLoading = true;
                const response = await tailorService.loadMoreTailors(page);
                state.tailors = response.data.data;
                state.pages = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    total: response.data.total,
                    per_page: response.data.per_page,
                    from: response.data.from,
                    prev_page_url: response.data.prev_page_url,
                    next_page_url: response.data.next_page_url,
                };
                store.state.isLoading = false;
            } catch (error) {
                console.log(error);
                toast.error("Some error occurred, please try again!");
                store.state.isLoading = false;
            }
        }
        const showAddTailorModal = () => addTailorModal.value?.showModal();
        const editTailor = (tailor) => {
            selectedTailor.value = { ...tailor };
            nextTick(() => {
                editTailorModal.value?.showModal();
            });
        };
        const viewTailor = (tailor) => {
            selectedTailor.value = { ...tailor };
            viewTailorModal.value?.showModal();
        };
        const handleTailorAdded = async (newTailor) => {
            try {
                await getTailorList();
                // Remove the code that reopens the modal
                selectedTailor.value = {};
            } catch (error) {
                console.error("Error adding tailor:", error);
                toast.error("Failed to add tailor!");
            }
        };
        const handleTailorUpdated = async (updatedTailor) => {
            try {
                await getTailorList();
                // Don't reopen the modal after update
                selectedTailor.value = {};
            } catch (error) {
                console.error("Error updating tailor:", error);
                toast.error("Failed to update tailor!");
            }
        };
        const deleteTailorHandler = async (id) => {
            selectedTailor.value = {
                ...state.tailors.find((tailor) => tailor.id === id),
            };
            confirmDeletionModal.value?.showModal();
        };
        const deleteTailor = async () => {
            try {
                await tailorService.deleteTailor(selectedTailor.value.id);
                await getTailorList();
                confirmDeletionModal.value?.hideModal();
                toast.success("Tailor deleted successfully!");
            } catch (error) {
                console.error("Error deleting tailor:", error);
                toast.error("Failed to delete tailor!");
            }
        };
        return {
            ...toRefs(state),
            filteredTailors,
            selectedTailor,
            addTailorModal,
            editTailorModal,
            viewTailorModal,
            confirmDeletionModal,
            showAddTailorModal,
            editTailor,
            viewTailor,
            handleTailorAdded,
            handleTailorUpdated,
            deleteTailorHandler,
            deleteTailor,
            loadMoreRecords,
        };
    },
};
</script>

<style scoped>
/* Add your styles here or copy from TailorShops.vue if needed */
</style>

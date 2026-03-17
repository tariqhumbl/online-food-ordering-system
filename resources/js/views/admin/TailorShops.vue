<template>
<div class="">
    <div class="pc-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page">Tailor Shops</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title mt-2">
                            <h2 class="mb-0">Tailor Shop list</h2>
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
                        <div class="d-sm-flex align-items-center justify-content-between">
                            <div class="input-group" style="max-width: 400px;">
                                <label class="d-flex align-items-center w-100">
                                    <span class="me-2">Search:</span>
                                    <input v-model="searchQuery" type="text" class="form-control form-control-sm" placeholder="Search by name..." />
                                </label>
                            </div>

                            <div class="text-end">
                                <button class="btn btn-primary" @click="showAddTailorShopModal">
                                    <i class="ti ti-plus f-18"></i> Add Shop
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-end">#</th>
                                        <th class="text-start">Logo</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Email</th>
                                        <th class="text-center">Contact</th>
                                        <th class="text-center">Address</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(tailorShop, index) in filteredTailorShops" :key="index.id">
                                        <td class="text-end">{{(index + 1) + ((pages.current_page - 1) * pages.per_page) }}</td>
                                        <td class="text-center">
                                            <img :src="$store.getters.getImagePath(tailorShop.logo || 'logos/no-logo.png')" alt="shop-logo" class="wid-40 rounded" style="max-height: 60px" />

                                        </td>
                                        <td class="text-center">{{ tailorShop.name }}</td>
                                        <td class="text-center">{{ tailorShop.email }}</td>
                                        <td class="text-center">{{ tailorShop.contact }}</td>
                                        <td class="text-center">{{ tailorShop.address }}</td>
                                        <td class="text-center">
                                            <span :class="['badge', tailorShop.status === 'active' ? 'bg-success' : 'bg-danger']">{{ tailorShop.status }}</span>
                                        </td>
                                        <td class="text-center">
                                            <ul class="list-inline mb-0">
                                                <li class="list-inline-item" title="View">
                                                    <a href="#" class="avtar avtar-xs btn-link-primary" @click.prevent="showViewTailorShopModal(tailorShop)">
                                                        <i class="ti ti-eye f-18"></i>
                                                    </a>
                                                </li>
                                                <li class="list-inline-item" title="Edit">
                                                    <a href="#" class="avtar avtar-xs btn-link-success" @click.prevent="showEditTailorShopModal(tailorShop)">
                                                        <i class="ti ti-edit-circle f-18"></i>
                                                    </a>
                                                </li>
                                                <li class="list-inline-item" title="Delete">
                                                    <a href="#" class="avtar avtar-xs btn-link-danger" @click.prevent="showConfirmDeletionModal(tailorShop)">
                                                        <i class="ti ti-trash f-18"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredTailorShops.length === 0">
                                        <td colspan="8" class="text-center">No shops found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                     <div class="card-footer d-flex justify-content-center">
                        <Pagination :pages="pages" v-on:loadMore="loadMoreRecords" />
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>

    <!-- Add Tailor Shop Modal -->
    <AddTailorShop ref="addTailorShopModal" @tailorShopAdded="handleTailorShopAdded" />
    <EditTailorShop ref="editTailorShopModal" :shopData="selectedShop" @tailorShopUpdated="handleTailorShopUpdated" />
    <ViewTailorShop ref="viewTailorShopModal" :shopData="selectedShop" />
    <ConfirmDeletion v-bind:blog="blog" v-on:confirmedDeletion="deleteTailorShops" ref="confirmDeletionModal">
    <template #title>
        <h4 class="modal-title text-danger">Delete Tailor Shop</h4>
    </template>
    <template #content>
        <p>Are you sure want to delete <span class="fw-bold text-danger">{{ tailorShop.name }}</span> ?</p>
    </template>
</ConfirmDeletion>
</div>
</template>

<script>
import * as tailorShopService from '../../services/tailorShop_service';
import AddTailorShop from '../../components/admin/AddTailorShop.vue';
import EditTailorShop from '../../components/admin/EditTailorShop.vue';
import ViewTailorShop from '../../components/admin/ViewTailorShop.vue';
import ConfirmDeletion from '../../components/common/ConfirmDeletion.vue';
import Pagination from '../../components/common/Pagination.vue';


import {
    ref,
    toRefs,
    reactive,
    onMounted,
    computed,
    nextTick
} from 'vue';
import {
    useToast
} from "vue-toastification";
import {
    useStore
} from 'vuex';
export default {
    name: 'TailorShops',
    components: {
        AddTailorShop,
        EditTailorShop,
        ViewTailorShop,
        ConfirmDeletion,
        Pagination
    },
    setup() {

        const store = useStore();
        const toast = useToast();
        const state = reactive({
            tailorShops: [],
            tailorShop: {},
            searchQuery: "",
             pages: {
                current_page: '',
                last_page: '',
                total: '',
                per_page: '',
                from: '',
                prev_page_url: null,
                next_page_url: null
            },
            errors: {}
        });

        const addTailorShopModal = ref();
        const editTailorShopModal = ref();
        const viewTailorShopModal = ref();
        const selectedShop = ref(null);

        const showAddTailorShopModal = () => addTailorShopModal.value ?.showModal();
        const showEditTailorShopModal = (shop) => {
            selectedShop.value = { ...shop };
            nextTick(() => {
                editTailorShopModal.value?.showModal();
            });
        };
        const showViewTailorShopModal = (shop) => {
            selectedShop.value = shop;
            viewTailorShopModal.value?.showModal();
        };
        loadTailorShops();
        async function loadTailorShops() {
            try {
                store.state.isLoading = true;
                const response = await tailorShopService.loadTailorShops();
                state.tailorShops = response.data.data;
                console.log('Tailor Shops:', response.data);
                 state.pages = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    total: response.data.total,
                    per_page: response.data.per_page,
                    from: response.data.from,
                    prev_page_url: response.data.prev_page_url,
                    next_page_url: response.data.next_page_url
                };
                store.state.isLoading = false;
            } catch (error) {
                console.log('Error:', error);
                toast.error("Some error occurred, please try again!");
                store.state.isLoading = false;
            }
        }

          async function loadMoreRecords(page) {
            try {
                store.state.isLoading = true;
                const response = await tailorShopService.loadMoreTailorShops(page);
                state.tailorShops = response.data.data
                state.pages = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    total: response.data.total,
                    per_page: response.data.per_page,
                    from: response.data.from,
                    prev_page_url: response.data.prev_page_url,
                    next_page_url: response.data.next_page_url
                };
                store.state.isLoading = false;
            } catch (error) {
                console.log(error);
                toast.error("Some error occurred, please try again!");
                store.state.isLoading = false;
            }
        }

        const confirmDeletionModal = ref(null);
        function showConfirmDeletionModal(tailorShop) {
            state.tailorShop = tailorShop
            confirmDeletionModal.value.showModal();
        }

        const filteredTailorShops = computed(() => {
            return state.tailorShops.filter(shop =>
                shop.name.toLowerCase().includes(state.searchQuery.toLowerCase())
            );
        });
        onMounted(loadTailorShops);

        const handleTailorShopUpdated = (updatedShop) => {
            const index = state.tailorShops.findIndex(shop => shop.id === updatedShop.id);
            if (index !== -1) {
                state.tailorShops[index] = updatedShop;
            }
        };

        const handleTailorShopAdded = (newShop) => {
            state.tailorShops.unshift(newShop);
        };

         async function deleteTailorShops() {
            try {
                store.state.isLoading = true;
                const response = await tailorShopService.deleteTailorShops(state.tailorShop.id)
                state.tailorShops = state.tailorShops.filter(tShp => {
                    return tShp.id != state.tailorShop.id;
                });
                confirmDeletionModal.value.hideModal();
                store.state.isLoading = false;
                toast.success("Tailor shop deleted successfully");
            } catch (error) {
                console.error("Error Occurred:", error);
                toast.error("Some error occurred, please try again!");
            }
        }
        return {
            ...toRefs(state),
            addTailorShopModal,
            editTailorShopModal,
            viewTailorShopModal,
            selectedShop,
            showAddTailorShopModal,
            showEditTailorShopModal,
            showViewTailorShopModal,
            handleTailorShopUpdated,
            handleTailorShopAdded,
            filteredTailorShops,
            showConfirmDeletionModal,
            confirmDeletionModal,
            deleteTailorShops,
            loadMoreRecords

        };
    }
}
</script>

<style scoped>
/* Add your styles here */
</style>

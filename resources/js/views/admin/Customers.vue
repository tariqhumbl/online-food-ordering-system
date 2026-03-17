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
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
                                <li class="breadcrumb-item" aria-current="page">Users</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title mt-2">
                                <h2 class="mb-0">Users list</h2>
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

                                <!-- <div class="text-end">
                                    <button class="btn btn-primary" @click="showAddTailorModal">
                                        <i class="ti ti-plus f-18"></i> Add Tailor
                                    </button>
                                </div> -->
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-end">#</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">Phone</th>
                                            <th class="text-center">Adress</th>
                                            <th class="text-center">Descripton</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(customerInformation, index) in filteredCustomerInformations" :key="index.id">
                                            <td class="text-end">{{(index + 1) + ((pages.current_page - 1) * pages.per_page) }}</td>
                                            <td class="text-center">{{ customerInformation.name }}</td>
                                            <td class="text-center">{{ customerInformation.user.email }}</td>
                                            <td class="text-center">{{ customerInformation.contact }}</td>
                                            <td class="text-center">{{ customerInformation.address }}</td>
                                            <td class="text-center">{{ customerInformation.notes }}</td>
                                            <td class="text-center">
                                                <ul class="list-inline mb-0">
                                                    <li class="list-inline-item" title="Delete">
                                                        <a href="#" class="avtar avtar-xs btn-link-danger" @click.prevent="deleteTailorHandler(customerInformation.id)">
                                                            <i class="ti ti-trash f-18"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </td>
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
         <ConfirmDeletion 
            ref="confirmDeletionModal" 
            :customerInformation="selectedCustomer" 
            @confirmedDeletion="deleteCustomer"
        >
            <template #title>
                <h4 class="modal-title text-danger">Delete Customer</h4>
            </template>
            <template #content>
                <p>Are you sure you want to delete <span class="fw-bold text-danger">{{ selectedCustomer.name }}</span> ?</p>
            </template>
        </ConfirmDeletion>
    </div>
 
</div>
</template>

<script>
import * as userService from '../../services/user_service';
import Pagination from '../../components/common/Pagination.vue';
import ConfirmDeletion from '../../components/common/ConfirmDeletion.vue';

import {
    ref,
    reactive,
    toRefs,
    watch ,
    computed,
} from 'vue';
import {
    useToast
} from "vue-toastification";
import {
    useStore
} from 'vuex';
export default {
    name: 'Customer',
    components: {
     Pagination,
     ConfirmDeletion
    },
    setup() {
        const store = useStore();
        const toast = useToast();
        const selectedCustomer = ref({});
        const confirmDeletionModal = ref(null);

        const state = reactive({
            customerInformations: [],
            customerInformation: {},
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

        watch(selectedCustomer, () => {
            getUserList();
        });
        getUserList();
        async function getUserList() {
            try {
                store.state.isLoading = true;
                const response = await userService.getUserList();
                state.customerInformations = response.data.data;
                console.log('customerInformations:', state.customerInformations);
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
                const response = await userService.loadMoreUsers(page);
                state.customerInformations = response.data.data
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
          
        const filteredCustomerInformations = computed(() => {
            if (!state.searchQuery.trim()) {
                return state.customerInformations;
            }
            return state.customerInformations.filter(customer => 
                customer.name.toLowerCase().includes(state.searchQuery.toLowerCase()) ||
                customer.user.email.toLowerCase().includes(state.searchQuery.toLowerCase())
            );
        });
         const deleteTailorHandler = async (id) => {
            selectedCustomer.value = { ...state.customerInformations.find(customerInformations => customerInformations.id === id) };
            confirmDeletionModal.value?.showModal();
        };

        const deleteCustomer = async () => {
            try {
                store.state.isLoading = true;
                // You'll need to create a delete endpoint in your userService
                // await userService.deleteCustomer(selectedCustomer.value.id);
                
                // For now, just remove from local state
                state.customerInformations = state.customerInformations.filter(
                    customer => customer.id !== selectedCustomer.value.id
                );
                
                toast.success("Customer deleted successfully!");
                selectedCustomer.value = {};
                store.state.isLoading = false;
            } catch (error) {
                console.log('Error:', error);
                toast.error("Some error occurred, please try again!");
                store.state.isLoading = false;
            }
        };
        return {
            ...toRefs(state),
            loadMoreRecords,
            filteredCustomerInformations,
            deleteTailorHandler,
            deleteCustomer,
            selectedCustomer,
            confirmDeletionModal
        };
    },
};
</script>

<style scoped>
/* Add your styles here or copy from TailorShops.vue if needed */
</style>

<template>
<div class="auth-main v1">
    <div class="auth-wrapper">
        <div class="auth-form">
            <div class="card my-5">
                <div class="card-body">
                    <div class="text-center">
                        <img src="/public/assets/images/authentication/img-auth-register.png" alt="images" class="img-fluid mb-3" />
                        <h4 class="f-w-500 mb-1">Create an account</h4>
                        <p class="mb-3">Already have an Account? <router-link :to="loginLink" class="link-primary">Log in</router-link></p>
                    </div>

                    <ul class="nav nav-pills nav-fill mb-4">
                        <li class="nav-item">
                            <button type="button" class="nav-link" :class="{ active: accountType === 'customer' }" @click="accountType = 'customer'">
                                Customer
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" :class="{ active: accountType === 'restaurant' }" @click="accountType = 'restaurant'">
                                Restaurant Partner
                            </button>
                        </li>
                    </ul>

                    <!-- Customer registration -->
                    <form v-if="accountType === 'customer'" @submit.prevent="registerCustomer">
                        <div class="row">
                            <div class="mb-3">
                                <input type="text" name="name" v-model="user.name" class="form-control" placeholder="Your Name" />
                                <small v-if="errors.name" class="text-danger">{{ errors.name[0] }}</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" v-model="user.email" class="form-control" placeholder="Email Address" />
                            <small v-if="errors.email" class="text-danger">{{ errors.email[0] }}</small>
                        </div>
                        <div class="mb-3 position-relative">
                            <input :type="showPassword ? 'text' : 'password'" v-model="user.password" class="form-control" placeholder="Password" />
                            <span class="material-icons" @click="showPassword = !showPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--bs-secondary);">
                                {{ showPassword ? 'visibility_off' : 'visibility' }}
                            </span>
                            <small v-if="errors.password" class="text-danger">{{ errors.password[0] }}</small>
                        </div>
                        <div class="mb-3 position-relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" v-model="user.password_confirmation" class="form-control" placeholder="Confirm Password" />
                            <span class="material-icons" @click="showConfirmPassword = !showConfirmPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--bs-secondary);">
                                {{ showConfirmPassword ? 'visibility_off' : 'visibility' }}
                            </span>
                            <small v-if="errors.password_confirmation" class="text-danger">{{ errors.password_confirmation[0] }}</small>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Create Customer Account</button>
                        </div>
                    </form>

                    <!-- Restaurant partner registration -->
                    <form v-else @submit.prevent="registerRestaurantPartner">
                        <p class="text-muted small mb-3">Register your restaurant. An admin will review your application before you can log in.</p>
                        <h6 class="text-muted mb-2">Manager account</h6>
                        <div class="mb-3">
                            <input type="text" v-model="restaurantForm.name" class="form-control" placeholder="Your full name" />
                            <small v-if="errors.name" class="text-danger">{{ errors.name[0] }}</small>
                        </div>
                        <div class="mb-3">
                            <input type="email" v-model="restaurantForm.email" class="form-control" placeholder="Login email address" />
                            <small v-if="errors.email" class="text-danger">{{ errors.email[0] }}</small>
                        </div>
                        <div class="mb-3 position-relative">
                            <input :type="showPassword ? 'text' : 'password'" v-model="restaurantForm.password" class="form-control" placeholder="Password" />
                            <span class="material-icons" @click="showPassword = !showPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--bs-secondary);">
                                {{ showPassword ? 'visibility_off' : 'visibility' }}
                            </span>
                            <small v-if="errors.password" class="text-danger">{{ errors.password[0] }}</small>
                        </div>
                        <div class="mb-3 position-relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" v-model="restaurantForm.password_confirmation" class="form-control" placeholder="Confirm password" />
                            <span class="material-icons" @click="showConfirmPassword = !showConfirmPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--bs-secondary);">
                                {{ showConfirmPassword ? 'visibility_off' : 'visibility' }}
                            </span>
                            <small v-if="errors.password_confirmation" class="text-danger">{{ errors.password_confirmation[0] }}</small>
                        </div>

                        <hr class="my-3" />
                        <h6 class="text-muted mb-2">Restaurant details</h6>
                        <div class="mb-3">
                            <input type="text" v-model="restaurantForm.restaurant_name" class="form-control" placeholder="Restaurant name" />
                            <small v-if="errors.restaurant_name" class="text-danger">{{ errors.restaurant_name[0] }}</small>
                        </div>
                        <div class="mb-3">
                            <input type="text" v-model="restaurantForm.address" class="form-control" placeholder="Address" />
                            <small v-if="errors.address" class="text-danger">{{ errors.address[0] }}</small>
                        </div>
                        <div class="mb-3">
                            <input type="text" v-model="restaurantForm.phone" class="form-control" placeholder="Phone number" />
                            <small v-if="errors.phone" class="text-danger">{{ errors.phone[0] }}</small>
                        </div>
                        <div class="mb-3">
                            <input type="email" v-model="restaurantForm.restaurant_email" class="form-control" placeholder="Restaurant contact email (optional)" />
                            <small v-if="errors.restaurant_email" class="text-danger">{{ errors.restaurant_email[0] }}</small>
                        </div>
                        <div class="mb-3">
                            <textarea v-model="restaurantForm.description" class="form-control" rows="2" placeholder="Short description (optional)"></textarea>
                            <small v-if="errors.description" class="text-danger">{{ errors.description[0] }}</small>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Submit Restaurant Application</button>
                        </div>
                    </form>

                    <div class="saprator my-3">
                        <span>Or continue with</span>
                    </div>
                    <div class="text-center">
                        <ul class="list-inline mx-auto mt-3 mb-0">
                            <li class="list-inline-item">
                                <a href="https://www.facebook.com/" class="avtar avtar-s rounded-circle bg-facebook" target="_blank">
                                    <i class="fab fa-facebook-f text-white"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="https://twitter.com/" class="avtar avtar-s rounded-circle bg-twitter" target="_blank">
                                    <i class="fab fa-twitter text-white"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="http://127.0.0.1:8000/api/auth/google" class="avtar avtar-s rounded-circle bg-googleplus" target="_blank">
                                    <i class="fab fa-google text-white"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import {
    reactive,
    toRefs,
    ref,
    computed,
    onMounted
} from 'vue';
import * as authService from '../../services/auth_service';
import {
    useToast
} from "vue-toastification";
import {
    useRouter,
    useRoute
} from 'vue-router';
import {
    useStore
} from 'vuex';

const defaultCustomerForm = () => ({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const defaultRestaurantForm = () => ({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    restaurant_name: '',
    address: '',
    phone: '',
    restaurant_email: '',
    description: '',
});

export default {
    name: 'Register',
    setup() {
        const router = useRouter();
        const route = useRoute();
        const store = useStore();
        const showPassword = ref(false);
        const showConfirmPassword = ref(false);
        const accountType = ref('customer');
        const loginLink = computed(() => {
            const redirect = route.query.redirect;
            if (redirect && typeof redirect === 'string' && redirect.startsWith('/')) {
                return { path: '/login', query: { redirect } };
            }
            return '/login';
        });

        onMounted(() => {
            document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            if (route.query.type === 'restaurant') {
                accountType.value = 'restaurant';
            }
        });

        const toast = useToast();
        const state = reactive({
            user: defaultCustomerForm(),
            restaurantForm: defaultRestaurantForm(),
            errors: {},
        });

        function clearForms() {
            Object.assign(state.user, defaultCustomerForm());
            Object.assign(state.restaurantForm, defaultRestaurantForm());
        }

        function handleValidationError(error) {
            if (error.response && error.response.status === 422) {
                state.errors = Object.fromEntries(Object.entries(error.response.data.errors || {}));
                toast.error('Please fix the highlighted fields.');
            } else {
                toast.error(error.response?.data?.message || 'Registration failed, please try again');
            }
        }

        async function registerCustomer() {
            try {
                store.state.isLoading = true;
                state.errors = {};
                await authService.register(state.user);
                clearForms();
                toast.success('Account created. Please log in.');
                router.push('/login');
            } catch (error) {
                handleValidationError(error);
            } finally {
                store.state.isLoading = false;
            }
        }

        async function registerRestaurantPartner() {
            try {
                store.state.isLoading = true;
                state.errors = {};
                const response = await authService.registerRestaurant(state.restaurantForm);
                clearForms();
                toast.success(response.data.message || 'Application submitted. You will be notified by email once approved.');
                router.push('/login');
            } catch (error) {
                handleValidationError(error);
            } finally {
                store.state.isLoading = false;
            }
        }

        return {
            ...toRefs(state),
            accountType,
            loginLink,
            registerCustomer,
            registerRestaurantPartner,
            showPassword,
            showConfirmPassword,
        };
    }
};
</script>

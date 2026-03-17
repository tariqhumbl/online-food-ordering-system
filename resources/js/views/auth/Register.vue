<template>
<div class="auth-main v1">
    <div class="auth-wrapper">
        <div class="auth-form">
            <div class="card my-5">
                <div class="card-body">
                    <form @submit.prevent="register">
                        <div class="text-center">
                            <img src="/public/assets/images/authentication/img-auth-register.png" alt="images" class="img-fluid mb-3" />
                            <h4 class="f-w-500 mb-1">Register with your email</h4>
                            <p class="mb-3">Already have an Account? <router-link :to="loginLink" class="link-primary">Log in</router-link></p>
                        </div>
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
                            <button type="submit" class="btn btn-primary">Create Account</button>
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

export default {
    name: 'Register',
    setup() {
        const router = useRouter();
        const route = useRoute();
        const store = useStore();
        const showPassword = ref(false);
        const loginLink = computed(() => {
            const redirect = route.query.redirect;
            if (redirect && typeof redirect === 'string' && redirect.startsWith('/')) {
                return { path: '/login', query: { redirect } };
            }
            return '/login';
        });
        const showConfirmPassword = ref(false);

        onMounted(() => {
            // Remove any Bootstrap modal backdrop left after navigating from restaurant page modal
            document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
        const toast = useToast();
        const state = reactive({
            user: {
                name: '',
                email: '',
                password: '',
                password_confirmation: ''
            },
            errors: {},
        });

        async function register() {
            try {
                store.state.isLoading = true;
                state.errors = {};
                const response = await authService.register(state.user);
                console.log(response);

                for (const key in state.user) {
                    state.user[key] = '';
                }

                toast.success("Account created. Please log in.");
                store.state.isLoading = false;
                const redirect = route.query.redirect;
                if (redirect && typeof redirect === 'string' && redirect.startsWith('/')) {
                    router.push(`/login?redirect=${encodeURIComponent(redirect)}`);
                } else {
                    router.push('/login');
                }
            } catch (error) {
                    store.state.isLoading = false;

                    if (error.response && error.response.status === 422) {
                    state.errors = Object.fromEntries(Object.entries(error.response.data.errors));
                    toast.error("Invalid username/password");
                    } else {
                    toast.error("Registration failed, please try again");
                    }
                }
        }

        return {
            ...toRefs(state),
            loginLink,
            register,
            showPassword,
            showConfirmPassword
        }
    }
};
</script>

<template>
<div class="auth-main v1">
    <div class="auth-wrapper">

        <div class="auth-form">
            <div class="card my-5">
                <div class="card-body">
                    <form v-on:submit.prevent="login">
                        <div class="text-center">
                            <router-link to="/" class="login-logo d-inline-flex align-items-center justify-content-center gap-2 text-primary text-decoration-none mb-3">
                                <i class="ph-duotone ph-fork-knife" style="font-size: 2.5rem;"></i>
                                <span class="fw-semibold" style="font-size: 1.5rem;">FoodOrder</span>
                            </router-link>
                            <h4 class="f-w-500 mb-1">Login with your email</h4>
                            <p class="mb-3">Don't have an Account? <router-link :to="registerLink" class="link-primary ms-1">Create Account</router-link></p>
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" v-model="user.email" class="form-control" id="floatingInput" placeholder="Email Address" />
                            <small v-if="errors.email" class="text-danger">{{ errors.email[0] }}</small>

                        </div>
                        <div class="mb-3 position-relative">
                            <input :type="showPassword ? 'text' : 'password'" v-model="user.password" class="form-control" placeholder="Password" />
                            <span class="material-icons" @click="showPassword = !showPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--bs-secondary);">
                                {{ showPassword ? 'visibility_off' : 'visibility' }}
                            </span>
                            <small v-if="errors.password" class="text-danger">{{ errors.password[0] }}</small>

                        </div>
                        <div class="d-flex mt-1 justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input input-primary" type="checkbox" id="customCheckc1" checked="" />
                                <label class="form-check-label text-muted" for="customCheckc1">Remember me?</label>
                            </div>
                            <a href="/forgot-request">
                                <h6 class="f-w-400 mb-0">Forgot Password?</h6>
                            </a>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                        <div class="saprator my-3">
                            <span>Or continue with</span>
                        </div>
                        <div class="text-center">
                            <ul class="list-inline mx-auto mt-3 mb-0">
                                <li class="list-inline-item">
                                    <a href="/auth/facebook" class="avtar avtar-s rounded-circle bg-facebook">
                                        <i class="fab fa-facebook-f text-white"></i>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="http://127.0.0.1:8000/api/auth/google" class="avtar avtar-s rounded-circle bg-googleplus">
                                        <i class="fab fa-google text-white"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
</template>

<script>
import * as authService from '../../services/auth_service';
import {
    useStore
} from 'vuex';
import {
    reactive,
    toRefs,
    ref,
    computed,
    onMounted
} from 'vue';
import {
    useToast
} from "vue-toastification";
import {
    useRouter,
    useRoute
} from 'vue-router';

export default {
    name: 'Login',
    setup() {
        const store = useStore();
        const router = useRouter();
        const route = useRoute();
        const showPassword = ref(false);
        const toast = useToast();
        const state = reactive({
            user: {
                email: '',
                password: '',
            },
            errors: {}
        });

        const registerLink = computed(() => {
            const redirect = route.query.redirect;
            if (redirect && typeof redirect === 'string' && redirect.startsWith('/')) {
                return { path: '/register', query: { redirect } };
            }
            return '/register';
        });

        onMounted(() => {
            // Remove any Bootstrap modal backdrop left after navigating from restaurant page modal
            document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });

        async function login() {
            try {
                state.errors = {};
                store.state.isLoading = true;

                const response = await authService.login(state.user);
                const userData = response.data.user;
                localStorage.setItem("user", JSON.stringify(userData));
                localStorage.setItem('auth_token', JSON.stringify(userData));
                store.commit("SET_PROFILE", userData);
                await store.dispatch("fetchUserProfile");
                const redirect = route.query.redirect;
                const roleId = userData.role_id;
                if (redirect && typeof redirect === 'string' && redirect.startsWith('/') && roleId === 3) {
                    router.push(redirect);
                } else {
                    const rolePath = { 1: 'admin', 2: 'vendor', 3: 'customer', 4: 'rider' };
                    router.push(`/${rolePath[roleId] || 'customer'}/dashboard`);
                }

                toast.success("Login successful");
                store.state.isLoading = false;
            } catch (error) {
                console.error("Login error:", error);
                state.errors = error.response?.data?.errors || {};
                if (error.response?.status === 422) {
                    toast.error("Invalid email or password");
                } else if (error.response?.status === 403) {
                    toast.warning(error.response?.data?.message || "Your account is not approved yet.");
                } else {
                    toast.error(error.response?.data?.message || "Some error occurred, Please try again.");
                }
                store.state.isLoading = false;
        }

        }

        return {
            ...toRefs(state),
            registerLink,
            login,
            showPassword,
        };
    }
}
</script>

<style scoped>
.login-logo {
  cursor: pointer;
  transition: opacity 0.2s;
}
.login-logo:hover {
  opacity: 0.85;
}
</style>

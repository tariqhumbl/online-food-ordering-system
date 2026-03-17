<template>
<div class="auth-main v1">
    <div class="auth-wrapper">
        <div class="auth-form">
            <div class="card my-5">
                <div class="card-body">
                    <form @submit.prevent="changePassword">
                        <div class="text-center">
                            <img src="/public/assets/images/authentication/img-auth-login.png" alt="images" class="img-fluid mb-3" />
                            <h4 class="f-w-500 mb-1">Change Password</h4>
                            <p class="mb-3">Don't have an Account? <a href="/register" class="link-primary ms-1">Create Account</a></p>
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" v-model="user.email" class="form-control" placeholder="Email Address" />
                            <small v-if="errors?.email" class="text-danger">
                                {{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}
                            </small>
                        </div>

                        <div class="mb-3 position-relative">
                            <input :type="showPassword ? 'text' : 'password'" v-model="user.current_password" class="form-control" placeholder=" Current Password" />
                            <span class="material-icons" @click="showPassword = !showPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--bs-secondary);">
                                {{ showPassword ? 'visibility_off' : 'visibility' }}
                            </span>
                            <small v-if="errors.password" class="text-danger">
                                {{ Array.isArray(errors.current_password) ? errors.current_password[0] : errors.current_password }}
                            </small>
                        </div>
                        <div class="mb-3 position-relative">
                            <input :type="showPassword ? 'text' : 'password'" v-model="user.password" class="form-control" placeholder=" New  Password" />
                            <span class="material-icons" @click="showPassword = !showPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--bs-secondary);">
                                {{ showPassword ? 'visibility_off' : 'visibility' }}
                            </span>
                            <small v-if="errors.password" class="text-danger">
                                {{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}
                            </small>
                        </div>
                        <div class="mb-3 position-relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" v-model="user.password_confirmation" class="form-control" placeholder="Confirm Password" />
                            <span class="material-icons" @click="showConfirmPassword = !showConfirmPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--bs-secondary);">
                                {{ showConfirmPassword ? 'visibility_off' : 'visibility' }}
                            </span>
                            <small v-if="errors.password_confirmation" class="text-danger">
                                {{ Array.isArray(errors.password_confirmation) ? errors.password_confirmation[0] : errors.password_confirmation }}
                            </small>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Update Password</button>
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
    reactive,
    toRefs,
    ref,
    onMounted
} from 'vue';
import { useRoute, useRouter } from "vue-router";
import {
    useStore
} from 'vuex';
import {
    useToast
} from "vue-toastification";

export default {
    name: 'ChangePassword',
    setup() {
        const router = useRouter();
        const showPassword = ref(false);
        const showConfirmPassword = ref(false);
        const route = useRoute();
        const store = useStore();
        const toast = useToast();
        const state = reactive({
            user: {
                email: '',
                current_password: '',
                password: '',
                password_confirmation: '',
            },
            errors: {}
        });

        onMounted(() => {
            if (route.query.email) {
                state.user.email = route.query.email;
            }
        });

        async function changePassword() {
            try {
                store.state.isLoading = true;
                state.errors = {};

                await authService.changePassword(state.user);
                console.log('function called', state.user);
                
                toast.success("Password changed successfully!");
                setTimeout(() => {
                    store.state.isLoading = false;
                    router.push('/login');
                }, 1000);

            } catch (error) {
                store.state.isLoading = false;

                if (error.response && error.response.status === 422) {
                    state.errors = Object.fromEntries(
                        Object.entries(error.response.data.errors).map(([field, [message]]) => [field, message])
                    );
                    toast.error("Some error occurred, please check the fields.");
                } else {
                    state.errors = {
                        general: 'Something went wrong!'
                    };
                    toast.error("Something went wrong, please try again.");
                }
            }
        }

        return {
            ...toRefs(state),
            changePassword,
            showPassword,
            showConfirmPassword
        }
    }
}

</script>

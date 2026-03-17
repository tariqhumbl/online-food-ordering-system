<template>
<div class="auth-main v1">
    <div class="auth-wrapper">
        <div class="auth-form">
            <div class="card my-5">
                <div class="card-body">
                    <form action="" v-on:submit.prevent="resetPasswordRequest()">
                        <div class="text-center">
                            <img src="/public/assets/images/authentication/img-auth-fporgot-password.png" alt="images" class="img-fluid mb-3" />
                            <h4 class="f-w-500 mb-1">Forgot Password</h4>
                            <p class="mb-3">Back to <a href="/login" class="link-primary ms-1">Log in</a></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" v-model="user.email" id="floatingInput" placeholder="Email Address" />
                            <small v-if="errors.email" class="text-danger">
                                {{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}
                            </small>
                        </div>
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">Send reset email</button>
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
    toRefs
} from 'vue';
import {
    useRouter
} from 'vue-router';
import {
    useStore
} from 'vuex';
import {
    useToast
} from "vue-toastification";

export default {
    name: 'ForgotPassword',
    setup() {
        const toast = useToast();
        const state = reactive({
            user: {
                email: '',
            },

            errors: {}
        });

        const router = useRouter();
        const store = useStore();

        async function resetPasswordRequest() {
            try {
                store.state.isLoading = true;
                state.errors = {};
                const response = await authService.forgotPassword(state.user);
                toast.success("We sent a verification code to the email address you provided!");
                store.state.isLoading = false;
                router.push({ path: '/reset-password', query: { email: state.user.email } });
            } catch (error) {
                store.state.isLoading = false;

                if (error.response && error.response.status === 422) {
                    state.errors = Object.fromEntries(
                        Object.entries(error.response.data.errors).map(([field, [message]]) => [field, message])
                    );
                } else {
                    state.errors = {
                        general: 'Some error occurred, please try again'
                    };
                }

                // Show error toast if login fails
                toast.error("Invalid email!");
            }
        }
        return {
            ...toRefs(state),
            resetPasswordRequest
        }
    }
}
</script>

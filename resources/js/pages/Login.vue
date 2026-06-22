<template>
    <section class="vh-100">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow-2-strong" style="border-radius: 1rem; background: lightsteelblue;">
                        <div class="card-body p-5">
                            <h1 class="mb-4 text-center fw-bold">Sign In</h1>
                            <form @submit.prevent="login">
                                <div class="form-outline mb-4">
                                    <input type="text"
                                           :class="['form-control form-control-lg', { 'is-invalid': errors?.email }]"
                                           placeholder="Email" ref="autofocus" v-model="form.email"
                                           :disabled="processing" />
                                    <span v-if="errors?.email" class="text-danger form-label" style="padding-left: 5px">
                                        {{ errors?.email[0] }}
                                    </span>
                                </div>
                                <div class="form-outline mb-4">
                                    <input type="password"
                                           :class="['form-control form-control-lg', { 'is-invalid': errors?.password }]"
                                           placeholder="Password" v-model="form.password" :disabled="processing" />
                                    <span v-if="errors?.password" class="text-danger form-label"
                                          style="padding-left: 5px">
                                        {{ errors?.password[0] }}
                                    </span>
                                </div>
                                <div class="d-grid mb-2">
                                    <button class="btn btn-primary btn-lg" type="submit" :disabled="processing">
                                        <span v-show="processing" class="spinner-border spinner-border-sm" role="status"
                                              aria-hidden="true"></span>
                                        Login
                                    </button>
                                </div>
                            </form>
                            <div class="py-3 text-center fs-5">
                                Don't have an account? <RouterLink :to="{ path: '/register' }">Register</RouterLink>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const processing = ref(false);
const form = ref({
    email: null,
    password: null

});

const errors = ref(null);
const router = useRouter();
const autofocus = ref(null);
const login = async () => {
    processing.value = true;
    const auth = useAuthStore();
    const response = await auth.login(form.value);
    if (response.data.success) {
        router.push('/');
    }
    else {
        errors.value = response.data.errors;
        setTimeout(() => {
            autofocus.value.focus();
            autofocus.value.select();
        }, 5);
    }
    processing.value = false;
}

onMounted(() => {
    autofocus.value.focus();
});
</script>

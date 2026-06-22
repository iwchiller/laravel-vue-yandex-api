<script setup>
import { onMounted, ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { useAuthStore } from '@/stores/auth';

const errors = ref(null);
const map_object = ref({
    page_id: 1,
    user_id: 0,
    reviews_url: '',
    business_id: 0,
    business_title: '',
    rating: '',
    ratings_count: 0,
    reviews_count: 0
});

const token = ref('');
const router = useRouter();
const processing = ref(false);

const check_url = async () => {
    processing.value = true;
    const response = await axios.post('/api/check',
        map_object.value,
        { headers: { Authorization: `Bearer ${token.value}` }
        });
    console.log(response.data);
    if (response.data.success) {
        map_object.value.business_id = response.data.business_id;
        map_object.value.business_title = response.data.business_title;
        const auth = useAuthStore();
        auth.getUser();
    } else {
        errors.value = response.data.errors;
    }
    processing.value = false;
}

onMounted(() => {
    const auth = useAuthStore();
    token.value = auth.token;
    map_object.value.user_id = auth.user.id;
    map_object.value.business_id = auth.map_object.business_id;
    map_object.value.business_title = auth.map_object.business_title;
    map_object.value.reviews_url = auth.map_object.reviews_url;
    map_object.value.rating = auth.map_object.rating;
    map_object.value.ratings_count = auth.map_object.ratings_count;
    map_object.value.reviews_count = auth.map_object.reviews_count;
//    console.log(map_object.value);
});

</script>

<template>
    <section class="vh-10">
        <div class="container">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12">
                    <div class="card shadow-2-strong" style="border-radius: 1rem; background: lightsteelblue;">
                        <div class="card-body p-5">
                            <h1 class="mb-4 text-center fw-bold">Parameters</h1>
                            <form @submit.prevent="check_url">
                                <div class="form-outline mb-4">
                                    <input type="text"
                                           :class="['form-control form-control-lg',
                                                { 'is-invalid': map_object.business_id < 1 },
                                                {'is-valid': map_object.business_id > 0}]"
                                           placeholder="Yandex Map Url" ref="autofocus" v-model="map_object.reviews_url"
                                           :disabled="processing" />
                                    <span v-if="errors?.reviews_url" class="text-danger form-label" style="padding-left: 5px">
                                        {{ errors?.reviews_url[0] }}
                                    </span>
                                </div>
                                <div v-if="(map_object.business_id > 0) && !(errors?.reviews_url)" class="form-outline mb-4">
                                    <input type="text"
                                           :class="['form-control form-control-lg']"
                                           placeholder="Object title" ref="autofocus"
                                           v-model="map_object.business_title"
                                           readonly="readonly" />
                                </div>
                                <div class="d-grid mb-2">
                                    <button class="btn btn-primary btn-lg" type="submit" :disabled="processing">
                                    <span v-show="processing" class="spinner-border spinner-border-sm" role="status"
                                          aria-hidden="true"></span>
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>

</style>

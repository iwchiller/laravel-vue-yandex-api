<script setup>
import axios from "axios";
import {onMounted, ref, watch} from "vue";
import { useAuthStore } from '@/stores/auth';
import { useRoute } from 'vue-router';
import Paginate from  './Paginate.vue';
import MapObject from  './MapObject.vue';

const errors = ref(null);
const reviews = ref({
    page_id: 1,
    business_id: 0,
    business_title: '',
    rating: '',
    ratings_count: '',
    reviews_count: ''
});

const route = useRoute();
const processing = ref(false);
const token = ref('');
const reviews_data = ref([]);
const page_id = ref(0);
const pages_count = ref(1);

const get_reviews = async () => {
    processing.value = true;
//    console.log(reviews.value.page_id);
    reviews.value.page_id = page_id.value;
    const response = await axios.post('/api/reviews',
        reviews.value,
        { headers: { Authorization: `Bearer ${token.value}` }
        });
//    console.log(response.data);
    if (response.data.success) {
        reviews_data.value = response.data.reviews;
        pages_count.value = response.data?.params.totalPages ?? 0;
        if (pages_count.value >= 12) {
            pages_count.value = 12;
        }
    } else {
        errors.value = response.data.errors;
    }
    processing.value = false;
}

watch(() => route.query.page,
    (new_page_id) => {
        page_id.value = Number(new_page_id);
//        console.log(page_id.value);
        get_reviews();
    }
)

const rus_date = (iso_date) => {
    let new_date = new Date(iso_date);
    return new Intl.DateTimeFormat('ru-Ru', {dateStyle: "full"}).format(new_date);
}

onMounted(() => {
    const auth = useAuthStore();
    token.value = auth.token;
    reviews.value.business_id = auth.map_object?.business_id;
    reviews.value.business_title = auth.map_object?.business_title;
    reviews.value.rating = auth.map_object?.rating;
    reviews.value.ratings_count = auth.map_object?.ratings_count;
    reviews.value.reviews_count = auth.map_object?.reviews_count;
    console.log(reviews.value.business_id);
    console.log(reviews.value.business_title);
    page_id.value = Number(route.query?.page ?? 1);
    get_reviews();
});

</script>

<template>
    <div>
        <h1 class="fw-bold">Reviews</h1>
    </div>
    <MapObject :map_obj="reviews" />
    <Paginate :pages_count="pages_count" :active_page="page_id" :disabled="processing" />
    <span v-if="errors?.reviews_url || errors?.http_get_csrf_token" class="text-danger form-label" style="padding-left: 5px">
         {{ errors?.reviews_url[0] }}
    </span>
    <span v-show="processing" class="spinner-border spinner-border-sm" role="status"
          aria-hidden="true"></span>
    <div v-for="review in reviews_data">
        <div class="review_card">
            <div class="review_card-footer border-1">
                <div>{{ review.author_name }}</div>
                <div>{{ review.author_level }}</div>
                <div>{{ review.rating }}★</div>
                <div>{{ rus_date(review.updatedTime) }}</div>
            </div>
            <div class="review_card-text">
                {{ review.text}}
            </div>
        </div>
    </div>
    <Paginate :pages_count="pages_count" :active_page="page_id" :disabled="processing" />
</template>

<style scoped>

.review_card {
    padding: 10px;
    margin-bottom: 10px;
    background-color: #9eeaf9;
}

.review_card-footer {
    display: flex;
    flex-direction: row;
    gap: 20px;
}

.review_card-footer > div {
    padding: 5px;
    background-color: #ffda6a;
}
</style>

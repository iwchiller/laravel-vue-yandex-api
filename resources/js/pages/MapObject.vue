<script setup>

const props = defineProps({
    map_obj: null
});

</script>

<template>

<div class="map_object_title">
    <h4>{{ (map_obj?.business_id > 0  ? map_obj?.business_title : 'No object selected on the Yandex map') }}</h4>
    <div class="rating" v-if="map_obj.business_id > 0">
        <div>Rating: {{ map_obj?.rating }}</div>
        <div>Ratings count: {{ map_obj?.ratings_count }}</div>
        <div>Reviews count: {{ map_obj?.reviews_count }}</div>
    </div>
</div>

</template>

<style scoped>

.map_object_title {
    display: flex;
    gap: 50px;
    align-items: center;
    padding: 10px 0;
}

.rating {
    display: flex;
    gap: 20px;
    font-size: 14px;
}

.rating > div {
    background-color: #9eeaf9;
    padding: 10px;
}

</style>

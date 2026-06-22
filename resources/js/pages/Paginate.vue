<script setup>

import { RouterLink } from "vue-router";

const props = defineProps({
    pages_count: Number,
    active_page: Number
});

</script>

<template>
    <nav v-if="pages_count>1" aria-label="Навигация по страницам">
        <ul class="pagination">
            <li v-for="n in pages_count" class="page-item">
                <RouterLink :to="{ path: '/', query: {'page': n} }" :class="['page-link', { 'active': n == active_page }]">{{ n }}</RouterLink>
            </li>
        </ul>
    </nav>
</template>

<style scoped>

</style>

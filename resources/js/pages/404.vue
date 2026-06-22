<script setup>

</script>

<template>
    <div class="d-flex align-items-center justify-content-center vh-100" style="background: whitesmoke">
        <div class="text-center">
            <h1 class="fw-bold" style="font-size: 200px">404</h1>
            <p class="fs-3"><span class="text-danger">Opps!</span> Page not found.</p>
            <p class="lead">The page you’re looking for doesn’t exist.</p>
            <router-link :to="{ path: '/' }" class="btn btn-primary pt-1">Go Home</router-link>
        </div>
    </div>
</template>

<style scoped>

</style>

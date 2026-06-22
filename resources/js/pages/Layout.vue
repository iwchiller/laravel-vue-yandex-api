<template>
    <div>
        <header
            class="text-bg-success d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4 border-bottom">
            <div class="col-md-3 mb-2 mb-md-0 d-flex">
                <a href="/" class="d-inline-flex link-body-emphasis text-decoration-none">
                    <strong class="fs-3 ms-2 text-white">Yandex Map Reviews</strong>
                </a>
            </div>
            <ul class="nav nav-underline col-12 col-md-auto mb-2 justify-content-end mb-md-0">
                <li class="nav-item">
                    <router-link :to="{ path: '/' }"
                                 :class="['nav-link mx-2 text-white', { 'active': $route.path == '/' }]">Reviews</router-link>
                </li>
                <li class="nav-item"><router-link :to="{ path: '/dashboard' }"
                                                  :class="['nav-link mx-2 text-white', { 'active': $route.path == '/dashboard' }]">Parameters</router-link>
                </li>
            </ul>
            <div class="col-md-3 text-end">
                Welcome, {{ auth.user?.name }}
                <button type="button" class="btn btn-danger me-2" v-if="auth.user"
                        @click="auth.logout()">Logout</button>
            </div>
        </header>
        <div class="container mt-5">
            <router-view></router-view>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
const auth = useAuthStore();
</script>

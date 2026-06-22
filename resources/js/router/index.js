import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
    {
        path: "/login",
        component: () => import("@/pages/Login.vue"),
    },
    {
        path: "/register",
        component: () => import("@/pages/Register.vue"),
    },
    {
        path: "/",
        component: () => import("@/pages/Layout.vue"),
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: "/",
                component: () => import("@/pages/Reviews.vue"),
            },
            {
                path: "/dashboard",
                component: () => import("@/pages/Dashboard.vue")
            }
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        component: () => import("@/pages/404.vue"),
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore()
    if (auth.token && !auth.user) {
        await auth.getUser();
    }

    if (to.meta.requiresAuth && !auth.user) {
        next('/login')
    } else if (auth.user && ['/login', '/register'].includes(to.path)) {
        next('/');
    }
    else {
        next()
    }
})

export default router

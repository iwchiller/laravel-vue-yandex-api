import { defineStore } from 'pinia'
import axios from 'axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        map_object: null,
        token: localStorage.getItem('token') || null,
    }),

    actions: {
        async register(form) {
            return await axios.post('/api/register', form);
        },

        async login(form) {
            const response = await axios.post('/api/login', form)
            if (response.data.success) {
                this.token = response.data.token
                this.user = response.data.user
                localStorage.setItem('token', this.token)
            }
            return response;
        },

        async getUser() {
            if (!this.token) return
            const response = await axios.get('/api/user', {
                headers: { Authorization: `Bearer ${this.token}` },
            })
            console.log('auth.getUser');
            console.log(response.data);
            this.user = response.data?.user;
            this.map_object = response.data?.map_object;
           // console.log(this.map_object);
        },

        async logout() {
            await axios.post('/api/logout', {}, {
                headers: { Authorization: `Bearer ${this.token}` },
            })
            this.token = null
            this.user = null
            localStorage.removeItem('token')
            router.push('/login')
        },
    },
})

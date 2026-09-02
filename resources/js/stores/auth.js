import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('auth_user')) || null,
        token: localStorage.getItem('auth_token') || null,
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        async login(credentials) {
            this.loading = true

            try {
                const response = await api.post('/login', credentials)

                this.token = response.data.token
                this.user = response.data.user

                localStorage.setItem(
                    'auth_token',
                    this.token
                )

                localStorage.setItem(
                    'auth_user',
                    JSON.stringify(this.user)
                )

                return response.data
            } finally {
                this.loading = false
            }
        },

        async fetchUser() {
            const response = await api.get('/user')

            this.user = response.data.user

            localStorage.setItem(
                'auth_user',
                JSON.stringify(this.user)
            )

            return this.user
        },

        async logout() {
            try {
                if (this.token) {
                    await api.post('/logout')
                }
            } finally {
                this.clearAuth()
            }
        },

        clearAuth() {
            this.token = null
            this.user = null

            localStorage.removeItem('auth_token')
            localStorage.removeItem('auth_user')
        },
    },
})
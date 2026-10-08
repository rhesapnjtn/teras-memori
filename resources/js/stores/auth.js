import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(
            localStorage.getItem('auth_user')
        ) || null,

        token: localStorage.getItem(
            'auth_token'
        ) || null,

        loading: false,

        initialized: false,
    }),

    getters: {
        isAuthenticated: (state) => {
            return !!state.token && !!state.user
        },
    },

    actions: {
        /*
        |--------------------------------------------------------------------------
        | Register
        |--------------------------------------------------------------------------
        */

        async register(data) {
            this.loading = true

            try {
                const response = await api.post(
                    '/register',
                    data
                )

                this.token =
                    response.data.token

                this.user =
                    response.data.user

                localStorage.setItem(
                    'auth_token',
                    this.token
                )

                localStorage.setItem(
                    'auth_user',
                    JSON.stringify(this.user)
                )

                this.initialized = true

                return response.data
            } catch (error) {
                this.clearAuth()

                throw error
            } finally {
                this.loading = false
            }
        },

        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        async login(credentials) {
            this.loading = true

            try {
                const response = await api.post(
                    '/login',
                    credentials
                )

                this.token =
                    response.data.token

                this.user =
                    response.data.user

                localStorage.setItem(
                    'auth_token',
                    this.token
                )

                localStorage.setItem(
                    'auth_user',
                    JSON.stringify(this.user)
                )

                this.initialized = true

                return response.data
            } catch (error) {
                this.clearAuth()

                throw error
            } finally {
                this.loading = false
            }
        },

        /*
        |--------------------------------------------------------------------------
        | Fetch User
        |--------------------------------------------------------------------------
        |
        | Memastikan token yang tersimpan
        | benar-benar masih valid di backend.
        |
        */

        async fetchUser() {
            if (!this.token) {
                this.clearAuth()

                return null
            }

            try {
                const response = await api.get(
                    '/user'
                )

                this.user =
                    response.data.user

                localStorage.setItem(
                    'auth_user',
                    JSON.stringify(this.user)
                )

                return this.user
            } catch (error) {
                this.clearAuth()

                throw error
            }
        },

        /*
        |--------------------------------------------------------------------------
        | Initialize Authentication
        |--------------------------------------------------------------------------
        |
        | Dipanggil oleh router sebelum masuk
        | ke halaman dashboard.
        |
        */

        async initialize() {
            /*
            |--------------------------------------------------------------------------
            | Sudah dicek sebelumnya
            |--------------------------------------------------------------------------
            */

            if (this.initialized) {
                return this.isAuthenticated
            }

            /*
            |--------------------------------------------------------------------------
            | Tidak ada token
            |--------------------------------------------------------------------------
            */

            if (!this.token) {
                this.clearAuth()

                this.initialized = true

                return false
            }

            /*
            |--------------------------------------------------------------------------
            | Validasi token ke backend
            |--------------------------------------------------------------------------
            */

            try {
                await this.fetchUser()

                this.initialized = true

                return true
            } catch (error) {
                this.clearAuth()

                this.initialized = true

                return false
            }
        },

        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        async logout() {
            try {
                if (this.token) {
                    await api.post(
                        '/logout'
                    )
                }
            } catch (error) {
                console.error(
                    'Logout error:',
                    error
                )
            } finally {
                this.clearAuth()

                this.initialized = true
            }
        },

        /*
        |--------------------------------------------------------------------------
        | Clear Authentication
        |--------------------------------------------------------------------------
        */

        clearAuth() {
            this.token = null

            this.user = null

            localStorage.removeItem(
                'auth_token'
            )

            localStorage.removeItem(
                'auth_user'
            )
        },
    },
})

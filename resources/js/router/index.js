import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
    /*
    |--------------------------------------------------------------------------
    | PUBLIC WEBSITE
    |--------------------------------------------------------------------------
    */

    {
        path: '/',
        component: () => import('../layouts/PublicLayout.vue'),

        children: [
            {
                path: '',
                name: 'home',
                component: () =>
                    import('../pages/public/Home.vue'),
            },

            {
                path: 'services',
                name: 'services',
                component: () =>
                    import('../pages/public/Services.vue'),
            },

            {
                path: 'portfolio',
                name: 'portfolio',
                component: () =>
                    import('../pages/public/Portfolio.vue'),
            },

            {
                path: 'about',
                name: 'about',
                component: () =>
                    import('../pages/public/About.vue'),
            },

            {
                path: 'order',
                name: 'order',
                component: () =>
                    import('../pages/public/Order.vue'),
            },

            /*
            |--------------------------------------------------------------------------
            | ORDER SUCCESS
            |--------------------------------------------------------------------------
            |
            | URL:
            | /order/success
            |
            */

            {
                path: 'order/success',
                name: 'order-success',
                component: () =>
                    import('../pages/public/OrderSuccess.vue'),
            },
            {
                path: 'track-order',
                name: 'track-order',
                component: () => import('../pages/public/TrackOrder.vue'),
},
        ],
    },


    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */

    {
        path: '/login',
        name: 'login',
        component: () =>
            import('../pages/auth/Login.vue'),
    },


    /*
    |--------------------------------------------------------------------------
    | ADMIN DASHBOARD
    |--------------------------------------------------------------------------
    */

    {
        path: '/dashboard',

        component: () =>
            import('../layouts/DashboardLayout.vue'),

        meta: {
            requiresAuth: true,
        },

        children: [
            {
                path: '',
                name: 'dashboard',
                component: () =>
                    import('../pages/dashboard/Dashboard.vue'),
            },

            {
                path: 'services',
                name: 'dashboard.services',
                component: () =>
                    import('../pages/dashboard/Services.vue'),
            },

            {
                path: 'orders',
                name: 'dashboard.orders',
                component: () =>
                    import('../pages/dashboard/Orders.vue'),
            },

            {
                path: 'customers',
                name: 'dashboard.customers',
                component: () =>
                    import('../pages/dashboard/Customers.vue'),
            },

            {
                path: 'portfolio',
                name: 'dashboard.portfolio',
                component: () =>
                    import('../pages/dashboard/Portfolio.vue'),
            },

            {
                path: 'reviews',
                name: 'dashboard.reviews',
                component: () =>
                    import('../pages/dashboard/Reviews.vue'),
            },

            {
                path: 'chat',
                name: 'dashboard.chat',
                component: () =>
                    import('../pages/dashboard/Chat.vue'),
            },
        ],
    },
]


/*
|--------------------------------------------------------------------------
| ROUTER
|--------------------------------------------------------------------------
*/

const router = createRouter({
    history: createWebHistory(),

    routes,

    scrollBehavior() {
        return {
            top: 0,
        }
    },
})


/*
|--------------------------------------------------------------------------
| AUTH GUARD
|--------------------------------------------------------------------------
*/

router.beforeEach((to) => {
    const auth = useAuthStore()

    /*
    |--------------------------------------------------------------------------
    | Protected dashboard
    |--------------------------------------------------------------------------
    */

    if (
        to.meta.requiresAuth &&
        !auth.isAuthenticated
    ) {
        return {
            name: 'login',
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Prevent authenticated user from login page
    |--------------------------------------------------------------------------
    */

    if (
        to.name === 'login' &&
        auth.isAuthenticated
    ) {
        return {
            name: 'dashboard',
        }
    }


    return true
})


export default router


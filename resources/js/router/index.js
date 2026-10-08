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
            {
    path: 'chat',
    name: 'chat',
    component: () => import('../pages/public/Chat.vue'),
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
            {
                path: 'profile',
                name: 'profile',
                component: () => import('../pages/public/Profile.vue'),
                meta: {
                    requiresAuth: true,
                },
            },
            {
                path: 'member',
                name: 'member',
                component: () => import('../pages/public/Member.vue'),
                meta: {
                    requiresAuth: true,
                },
            },
            {
                path: 'order-history',
                name: 'order-history',
                component: () => import('../pages/public/OrderHistory.vue'),
                meta: {
                    requiresAuth: true,
                },
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

    {
        path: '/register',
        name: 'register',
        component: () =>
            import('../pages/auth/Register.vue'),
    },


    /*
    |--------------------------------------------------------------------------
    | ADMIN DASHBOARD
    |--------------------------------------------------------------------------
    */

    {
        path: '/admin',

        component: () =>
            import('../layouts/DashboardLayout.vue'),

        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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

            {
                path: 'roles',
                name: 'dashboard.roles',
                component: () =>
                    import('../pages/dashboard/Roles.vue'),
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
    | Admin only
    |--------------------------------------------------------------------------
    */

    if (to.meta.requiresAdmin) {
        const roles = auth.user?.roles?.map(
            (r) => r.name
        ) || []

        const isStaff =
            roles.includes('admin') ||
            roles.includes('superadmin')

        if (!isStaff) {
            return {
                name: 'home',
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Prevent authenticated user from login/register page
    |--------------------------------------------------------------------------
    */

    if (
        (to.name === 'login' || to.name === 'register') &&
        auth.isAuthenticated &&
        !to.query.redirect
    ) {
        const roles = auth.user?.roles?.map(
            (r) => r.name
        ) || []

        if (
            roles.includes('admin') ||
            roles.includes('superadmin')
        ) {
            return { name: 'dashboard' }
        }

        return { name: 'home' }
    }


    return true
})


export default router


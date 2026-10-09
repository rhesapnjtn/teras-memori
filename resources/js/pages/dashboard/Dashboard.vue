<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../services/api'

const loading = ref(true)
const errorMessage = ref('')

const stats = ref({
    orders: 0,
    revenue: 0,
    customers: 0,
    reviews: 0,
    completedOrders: 0,
    pendingOrders: 0,
})

const recentOrders = ref([])

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value || 0))
}

const formatDate = (date) => {
    if (!date) return '—'

    const parsedDate = new Date(date)

    if (Number.isNaN(parsedDate.getTime())) {
        return '—'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(parsedDate)
}

const statusLabel = (status) => {
    const labels = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        processing: 'Processing',
        completed: 'Completed',
        cancelled: 'Cancelled',
    }

    return labels[status] || status || 'Unknown'
}

const statusClass = (status) => {
    const classes = {
        pending: 'bg-amber-50 text-amber-600',
        confirmed: 'bg-blue-50 text-blue-600',
        processing: 'bg-purple-50 text-purple-600',
        completed: 'bg-emerald-50 text-emerald-600',
        cancelled: 'bg-red-50 text-red-600',
    }

    return classes[status] || 'bg-[#191919]/5 text-[#191919]/40'
}

const getOrderTotal = (order) => {
    return Number(
        order.total_amount ??
        order.total ??
        order.amount ??
        0
    )
}

const statCards = computed(() => [
    {
        label: 'Total Orders',
        value: stats.value.orders,
        suffix: 'all orders',
        icon: '↗',
    },
    {
        label: 'Total Revenue',
        value: formatCurrency(stats.value.revenue),
        suffix: 'non-cancelled orders',
        icon: 'Rp',
    },
    {
        label: 'Customers',
        value: stats.value.customers,
        suffix: 'registered customers',
        icon: '◌',
    },
    {
        label: 'Reviews',
        value: stats.value.reviews,
        suffix: 'customer reviews',
        icon: '★',
    },
])

const fetchDashboard = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const [
            ordersResponse,
            customersResponse,
            reviewsResponse,
        ] = await Promise.all([
            api.get('/orders'),
            api.get('/admin/customers'),
            api.get('/admin/reviews'),
        ])

        const orders = ordersResponse.data?.data || []
        const customers = customersResponse.data?.data || []
        const reviews = reviewsResponse.data?.data || []

        stats.value.orders = orders.length
        stats.value.customers = customers.length
        stats.value.reviews = reviews.length

        stats.value.completedOrders = orders.filter(
            (order) => order.status === 'completed'
        ).length

        stats.value.pendingOrders = orders.filter(
            (order) => order.status === 'pending'
        ).length

        stats.value.revenue = orders
            .filter((order) => order.status !== 'cancelled')
            .reduce((total, order) => {
                return total + getOrderTotal(order)
            }, 0)

        recentOrders.value = [...orders]
            .sort((a, b) => {
                return (
                    new Date(b.created_at || 0) -
                    new Date(a.created_at || 0)
                )
            })
            .slice(0, 5)
    } catch (error) {
        console.error('Failed to load dashboard:', error)

        errorMessage.value =
            error.response?.data?.message ||
            'Gagal mengambil data dashboard.'
    } finally {
        loading.value = false
    }
}

onMounted(fetchDashboard)
</script>

<template>
    <section>

        <!-- =====================================================
             HEADER
        ====================================================== -->
        <div class="mb-10">

            <p
                class="text-[9px] font-semibold uppercase tracking-[0.35em] text-[#191919]/30"
            >
                Overview / Dashboard
            </p>

            <div
                class="mt-3 flex flex-col justify-between gap-5 lg:flex-row lg:items-end"
            >

                <div>

                    <h1
                        class="text-4xl font-medium tracking-[-0.06em] sm:text-5xl"
                    >
                        DASHBOARD<span class="text-[#E85D75]">.</span>
                    </h1>

                    <p
                        class="mt-4 max-w-xl text-sm leading-6 text-[#191919]/40"
                    >
                        Monitor your Teras Memori studio from one place.
                    </p>

                </div>


                <!-- Studio status -->
                <div
                    class="hidden border-l border-[#191919]/10 pl-5 text-right sm:block"
                >

                    <p
                        class="text-[8px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Studio Status
                    </p>

                    <div
                        class="mt-2 flex items-center justify-end gap-2"
                    >

                        <span
                            class="h-1.5 w-1.5 rounded-full bg-[#E85D75]"
                        ></span>

                        <span
                            class="text-[9px] font-semibold uppercase tracking-[0.2em]"
                        >
                            Active
                        </span>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             ERROR
        ====================================================== -->
        <div
            v-if="errorMessage"
            class="mb-6 border-l-2 border-red-400 bg-red-50 px-5 py-4"
        >

            <p class="text-xs text-red-600">
                {{ errorMessage }}
            </p>

            <button
                type="button"
                class="mt-3 text-[8px] font-semibold uppercase tracking-[0.2em] text-red-500 underline"
                @click="fetchDashboard"
            >
                Try again
            </button>

        </div>


        <!-- =====================================================
             LOADING
        ====================================================== -->
        <div
            v-if="loading"
            class="border border-[#191919]/10 bg-white p-10"
        >

            <div
                class="flex min-h-[300px] items-center justify-center"
            >

                <div class="text-center">

                    <div
                        class="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-[#191919]/10 border-t-[#E85D75]"
                    ></div>

                    <p
                        class="mt-5 text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Loading dashboard
                    </p>

                </div>

            </div>

        </div>


        <!-- =====================================================
             DASHBOARD CONTENT
        ====================================================== -->
        <div v-else>

            <!-- =================================================
                 STAT CARDS
            ================================================== -->
            <div
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
            >

                <article
                    v-for="stat in statCards"
                    :key="stat.label"
                    class="group relative overflow-hidden border border-[#191919]/10 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-[#E85D75]/30"
                >

                    <div
                        class="flex items-start justify-between"
                    >

                        <p
                            class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                        >
                            {{ stat.label }}
                        </p>

                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full border border-[#191919]/10 text-[10px] text-[#E85D75] transition duration-300 group-hover:border-[#E85D75]/30"
                        >
                            {{ stat.icon }}
                        </span>

                    </div>


                    <div class="mt-10">

                        <p
                            class="text-3xl font-medium tracking-[-0.05em] text-[#191919]"
                        >
                            {{ stat.value }}
                        </p>

                        <p
                            class="mt-2 text-[8px] uppercase tracking-[0.25em] text-[#191919]/25"
                        >
                            {{ stat.suffix }}
                        </p>

                    </div>


                    <div
                        class="absolute bottom-0 left-0 h-px w-0 bg-[#E85D75] transition-all duration-500 group-hover:w-full"
                    ></div>

                </article>

            </div>


            <!-- =================================================
                 SECONDARY STATS
            ================================================== -->
            <div
                class="mt-4 grid gap-4 sm:grid-cols-2"
            >

                <!-- Completed -->
                <div
                    class="flex items-center justify-between border border-[#191919]/10 bg-white px-6 py-5"
                >

                    <div>

                        <p
                            class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                        >
                            Completed Orders
                        </p>

                        <p
                            class="mt-3 text-2xl font-medium tracking-[-0.04em]"
                        >
                            {{ stats.completedOrders }}
                        </p>

                    </div>

                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-sm text-emerald-600"
                    >
                        ✓
                    </span>

                </div>


                <!-- Pending -->
                <div
                    class="flex items-center justify-between border border-[#191919]/10 bg-white px-6 py-5"
                >

                    <div>

                        <p
                            class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                        >
                            Pending Orders
                        </p>

                        <p
                            class="mt-3 text-2xl font-medium tracking-[-0.04em]"
                        >
                            {{ stats.pendingOrders }}
                        </p>

                    </div>

                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-50 text-sm text-amber-600"
                    >
                        !
                    </span>

                </div>

            </div>


            <!-- =================================================
                 MAIN CONTENT
            ================================================== -->
            <div
                class="mt-6 grid gap-6 xl:grid-cols-[1.6fr_0.8fr]"
            >

                <!-- =================================================
                     RECENT ORDERS
                ================================================== -->
                <section
                    class="overflow-hidden border border-[#191919]/10 bg-white"
                >

                    <!-- Header -->
                    <div
                        class="flex flex-col justify-between gap-4 border-b border-[#191919]/10 px-6 py-6 sm:flex-row sm:items-end sm:px-7"
                    >

                        <div>

                            <p
                                class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                            >
                                Latest activity
                            </p>

                            <h2
                                class="mt-2 text-2xl font-medium tracking-[-0.04em]"
                            >
                                RECENT ORDERS<span
                                    class="text-[#E85D75]"
                                >.</span>
                            </h2>

                        </div>


                        <RouterLink
                            :to="{ name: 'dashboard.orders' }"
                            class="text-[8px] font-semibold uppercase tracking-[0.25em] text-[#E85D75] transition hover:text-[#191919]"
                        >
                            View all →
                        </RouterLink>

                    </div>


                    <!-- Orders -->
                    <div
                        v-if="recentOrders.length"
                        class="divide-y divide-[#191919]/10"
                    >

                        <div
                            v-for="(order, index) in recentOrders"
                            :key="order.id"
                            class="grid gap-4 px-6 py-5 transition hover:bg-[#FFF8FA] sm:grid-cols-[40px_1.4fr_1fr_110px] sm:items-center sm:px-7"
                        >

                            <!-- Number -->
                            <span
                                class="hidden text-[9px] font-medium tracking-[0.2em] text-[#E85D75] sm:block"
                            >
                                {{ String(index + 1).padStart(2, '0') }}
                            </span>


                            <!-- Customer -->
                            <div>

                                <p class="text-sm font-semibold">
                                    {{ order.customer?.name || 'Customer' }}
                                </p>

                                <p
                                    class="mt-1 text-[9px] uppercase tracking-[0.18em] text-[#191919]/30"
                                >
                                    {{
                                        order.order_number ||
                                        `Order #${order.id}`
                                    }}
                                </p>

                            </div>


                            <!-- Amount -->
                            <div>

                                <p
                                    class="text-sm text-[#191919]/55"
                                >
                                    {{ formatCurrency(getOrderTotal(order)) }}
                                </p>

                                <p
                                    class="mt-1 text-[9px] text-[#191919]/25"
                                >
                                    {{ formatDate(order.created_at) }}
                                </p>

                            </div>


                            <!-- Status -->
                            <div>

                                <span
                                    class="inline-flex px-3 py-2 text-[8px] font-semibold uppercase tracking-[0.15em]"
                                    :class="statusClass(order.status)"
                                >
                                    {{ statusLabel(order.status) }}
                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- Empty -->
                    <div
                        v-else
                        class="flex min-h-[260px] items-center justify-center px-6 text-center"
                    >

                        <div>

                            <div
                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-[#E85D75]/20 bg-[#F4A6B8]/10 text-xl text-[#E85D75]"
                            >
                                ↗
                            </div>

                            <h3
                                class="mt-6 text-lg font-medium"
                            >
                                No orders yet
                            </h3>

                            <p
                                class="mt-2 text-sm text-[#191919]/35"
                            >
                                New customer orders will appear here.
                            </p>

                        </div>

                    </div>

                </section>


                <!-- =================================================
                     QUICK SUMMARY
                ================================================== -->
                <section
                    class="relative overflow-hidden bg-[#191919] p-7 text-white"
                >

                    <!-- Decorative -->
                    <div
                        class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full border border-white/10"
                    ></div>

                    <div
                        class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full border border-[#E85D75]/30"
                    ></div>

                    <div
                        class="pointer-events-none absolute -bottom-20 -left-20 h-40 w-40 rounded-full bg-[#E85D75]/10 blur-[70px]"
                    ></div>


                    <p
                        class="relative text-[8px] font-semibold uppercase tracking-[0.3em] text-white/35"
                    >
                        Studio snapshot
                    </p>


                    <h2
                        class="relative mt-4 max-w-xs text-3xl font-medium leading-tight tracking-[-0.05em]"
                    >
                        KEEP THE
                        <br />
                        MEMORIES
                        <br />
                        MOVING<span class="text-[#E85D75]">.</span>
                    </h2>


                    <!-- Summary -->
                    <div class="relative mt-10 space-y-5">

                        <div
                            class="flex items-center justify-between border-b border-white/10 pb-4"
                        >

                            <span
                                class="text-[8px] uppercase tracking-[0.2em] text-white/35"
                            >
                                Orders
                            </span>

                            <span class="text-sm">
                                {{ stats.orders }}
                            </span>

                        </div>


                        <div
                            class="flex items-center justify-between border-b border-white/10 pb-4"
                        >

                            <span
                                class="text-[8px] uppercase tracking-[0.2em] text-white/35"
                            >
                                Revenue
                            </span>

                            <span class="text-sm">
                                {{ formatCurrency(stats.revenue) }}
                            </span>

                        </div>


                        <div
                            class="flex items-center justify-between border-b border-white/10 pb-4"
                        >

                            <span
                                class="text-[8px] uppercase tracking-[0.2em] text-white/35"
                            >
                                Completed
                            </span>

                            <span class="text-sm">
                                {{ stats.completedOrders }}
                            </span>

                        </div>


                        <div
                            class="flex items-center justify-between border-b border-white/10 pb-4"
                        >

                            <span
                                class="text-[8px] uppercase tracking-[0.2em] text-white/35"
                            >
                                Pending
                            </span>

                            <span class="text-sm">
                                {{ stats.pendingOrders }}
                            </span>

                        </div>


                        <div
                            class="flex items-center justify-between"
                        >

                            <span
                                class="text-[8px] uppercase tracking-[0.2em] text-white/35"
                            >
                                Reviews
                            </span>

                            <span class="text-sm">
                                {{ stats.reviews }}
                            </span>

                        </div>

                    </div>


                    <!-- Footer -->
                    <div
                        class="relative mt-10 border-t border-white/10 pt-5"
                    >

                        <p
                            class="text-[8px] uppercase tracking-[0.2em] leading-5 text-white/30"
                        >
                            Teras Memori Studio
                            <br />
                            Creative photo editing & restoration
                        </p>

                    </div>

                </section>

            </div>


            <!-- =================================================
                 QUICK ACTIONS
            ================================================== -->
            <section class="mt-6">

                <div class="mb-5">

                    <p
                        class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Quick actions
                    </p>

                </div>


                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    <RouterLink
                        :to="{ name: 'dashboard.orders' }"
                        class="group border border-[#191919]/10 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-[#E85D75]/30"
                    >

                        <span
                            class="text-[9px] tracking-[0.25em] text-[#E85D75]"
                        >
                            01
                        </span>

                        <h3
                            class="mt-8 text-lg font-medium tracking-[-0.03em] transition group-hover:text-[#E85D75]"
                        >
                            Manage Orders
                        </h3>

                        <p
                            class="mt-2 text-xs leading-5 text-[#191919]/35"
                        >
                            Review and process customer orders.
                        </p>

                        <span
                            class="mt-6 block text-sm text-[#191919]/20 transition group-hover:translate-x-2 group-hover:text-[#E85D75]"
                        >
                            →
                        </span>

                    </RouterLink>


                    <RouterLink
                        :to="{ name: 'dashboard.services' }"
                        class="group border border-[#191919]/10 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-[#E85D75]/30"
                    >

                        <span
                            class="text-[9px] tracking-[0.25em] text-[#E85D75]"
                        >
                            02
                        </span>

                        <h3
                            class="mt-8 text-lg font-medium tracking-[-0.03em] transition group-hover:text-[#E85D75]"
                        >
                            Manage Services
                        </h3>

                        <p
                            class="mt-2 text-xs leading-5 text-[#191919]/35"
                        >
                            Manage editing services and pricing.
                        </p>

                        <span
                            class="mt-6 block text-sm text-[#191919]/20 transition group-hover:translate-x-2 group-hover:text-[#E85D75]"
                        >
                            →
                        </span>

                    </RouterLink>


                    <RouterLink
                        :to="{ name: 'dashboard.portfolio' }"
                        class="group border border-[#191919]/10 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-[#E85D75]/30"
                    >

                        <span
                            class="text-[9px] tracking-[0.25em] text-[#E85D75]"
                        >
                            03
                        </span>

                        <h3
                            class="mt-8 text-lg font-medium tracking-[-0.03em] transition group-hover:text-[#E85D75]"
                        >
                            Manage Portfolio
                        </h3>

                        <p
                            class="mt-2 text-xs leading-5 text-[#191919]/35"
                        >
                            Update projects displayed to visitors.
                        </p>

                        <span
                            class="mt-6 block text-sm text-[#191919]/20 transition group-hover:translate-x-2 group-hover:text-[#E85D75]"
                        >
                            →
                        </span>

                    </RouterLink>


                    <RouterLink
                        :to="{ name: 'dashboard.reviews' }"
                        class="group border border-[#191919]/10 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-[#E85D75]/30"
                    >

                        <span
                            class="text-[9px] tracking-[0.25em] text-[#E85D75]"
                        >
                            04
                        </span>

                        <h3
                            class="mt-8 text-lg font-medium tracking-[-0.03em] transition group-hover:text-[#E85D75]"
                        >
                            Manage Reviews
                        </h3>

                        <p
                            class="mt-2 text-xs leading-5 text-[#191919]/35"
                        >
                            Publish and manage customer feedback.
                        </p>

                        <span
                            class="mt-6 block text-sm text-[#191919]/20 transition group-hover:translate-x-2 group-hover:text-[#E85D75]"
                        >
                            →
                        </span>

                    </RouterLink>

                </div>

            </section>

        </div>

    </section>
</template>
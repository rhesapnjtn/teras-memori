```vue
<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()

const customers = ref([])
const loading = ref(false)
const error = ref('')

const searchQuery = ref('')
const selectedCustomer = ref(null)
const showDetail = ref(false)
const detailLoading = ref(false)
const detailError = ref('')

const fetchCustomers = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await api.get('/admin/customers')

        customers.value =
            response.data.data ??
            response.data ??
            []
    } catch (err) {
        console.error('Fetch customers error:', err)

        error.value =
            err.response?.data?.message ||
            'Failed to load customers.'

        if (err.response?.status === 401) {
            router.push({ name: 'login' })
        }
    } finally {
        loading.value = false
    }
}

const filteredCustomers = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    if (!query) {
        return customers.value
    }

    return customers.value.filter((customer) => {
        const name = customer.name?.toLowerCase() || ''
        const email = customer.email?.toLowerCase() || ''
        const phone = customer.phone?.toLowerCase() || ''
        const address = customer.address?.toLowerCase() || ''

        return (
            name.includes(query) ||
            email.includes(query) ||
            phone.includes(query) ||
            address.includes(query)
        )
    })
})

const totalOrders = computed(() => {
    return customers.value.reduce(
        (total, customer) =>
            total + Number(customer.orders_count || 0),
        0
    )
})

const formatCurrency = (value) => {
    const amount = Number(value || 0)

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount)
}

const formatDate = (value) => {
    if (!value) return '-'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value))
}

const formatDateTime = (value) => {
    if (!value) return '-'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value))
}

const getInitials = (name) => {
    if (!name) return '?'

    const words = name.trim().split(/\s+/)

    if (words.length === 1) {
        return words[0].slice(0, 2).toUpperCase()
    }

    return (
        words[0].charAt(0) +
        words[words.length - 1].charAt(0)
    ).toUpperCase()
}

const getOrderStatusClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-[#FFF4D6] text-[#8A6500]'

        case 'confirmed':
            return 'bg-[#E9E8FF] text-[#5147A8]'

        case 'processing':
            return 'bg-[#E8F3FF] text-[#2563A8]'

        case 'completed':
            return 'bg-[#E7F6EC] text-[#287A45]'

        case 'cancelled':
            return 'bg-[#FBE9EC] text-[#B33A4A]'

        default:
            return 'bg-[#F1EEEE] text-[#5F5A5B]'
    }
}

const getPaymentStatusClass = (status) => {
    switch (status) {
        case 'paid':
            return 'bg-[#E7F6EC] text-[#287A45]'

        case 'pending':
            return 'bg-[#FFF4D6] text-[#8A6500]'

        case 'failed':
            return 'bg-[#FBE9EC] text-[#B33A4A]'

        case 'expired':
            return 'bg-[#F1EEEE] text-[#5F5A5B]'

        case 'refunded':
            return 'bg-[#E9E8FF] text-[#5147A8]'

        default:
            return 'bg-[#F1EEEE] text-[#5F5A5B]'
    }
}

const getOrderStatusLabel = (status) => {
    const labels = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        processing: 'Processing',
        completed: 'Completed',
        cancelled: 'Cancelled',
    }

    return labels[status] || status || '-'
}

const getPaymentStatusLabel = (status) => {
    const labels = {
        pending: 'Pending',
        paid: 'Paid',
        failed: 'Failed',
        expired: 'Expired',
        refunded: 'Refunded',
    }

    return labels[status] || status || '-'
}

const openCustomer = async (customer) => {
    selectedCustomer.value = customer
    showDetail.value = true
    detailLoading.value = true
    detailError.value = ''

    try {
        const response = await api.get(
            `/admin/customers/${customer.id}`
        )

        selectedCustomer.value =
            response.data.data ??
            response.data
    } catch (err) {
        console.error('Fetch customer detail error:', err)

        detailError.value =
            err.response?.data?.message ||
            'Failed to load customer details.'
    } finally {
        detailLoading.value = false
    }
}

const closeCustomer = () => {
    showDetail.value = false
    selectedCustomer.value = null
    detailError.value = ''
}

const getOrderServices = (order) => {
    if (!order?.items?.length) {
        return 'No services'
    }

    return order.items
        .map((item) => {
            return (
                item.service?.name ||
                item.service?.title ||
                'Service'
            )
        })
        .join(', ')
}

const getOrderTotal = (order) => {
    if (order?.total_amount !== undefined) {
        return order.total_amount
    }

    if (!order?.items?.length) {
        return 0
    }

    return order.items.reduce((total, item) => {
        const subtotal =
            item.subtotal ??
            item.total ??
            Number(item.price || item.service?.price || 0) *
                Number(item.quantity || 1)

        return total + Number(subtotal || 0)
    }, 0)
}

onMounted(() => {
    fetchCustomers()
})
</script>

<template>
    <div class="min-h-full bg-[#FFF8FA] text-[#191919]">

        <!-- =====================================================
             HEADER
        ====================================================== -->
        <section
            class="border-b border-[#191919]/10 px-6 py-8 md:px-10 md:py-10"
        >
            <div
                class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
            >
                <div>
                    <p
                        class="mb-3 text-[10px] font-semibold uppercase tracking-[0.28em] text-[#E85D75]"
                    >
                        Management / Customers
                    </p>

                    <h1
                        class="text-4xl font-light tracking-[-0.04em] md:text-5xl"
                    >
                        Customers.
                    </h1>

                    <p
                        class="mt-3 max-w-xl text-sm leading-6 text-[#191919]/55"
                    >
                        Manage customer information, order history,
                        payments, and their relationship with
                        Teras Memori.
                    </p>
                </div>

                <button
                    type="button"
                    @click="fetchCustomers"
                    :disabled="loading"
                    class="inline-flex h-11 items-center justify-center border border-[#191919]/15 px-5 text-[10px] font-semibold uppercase tracking-[0.2em] transition hover:border-[#191919]/40 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ loading ? 'Refreshing...' : 'Refresh Customers' }}
                </button>
            </div>
        </section>


        <!-- =====================================================
             SUMMARY
        ====================================================== -->
        <section
            class="border-b border-[#191919]/10 px-6 py-6 md:px-10"
        >
            <div class="grid gap-4 sm:grid-cols-2">

                <div
                    class="border border-[#191919]/10 bg-white p-6"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                            >
                                Total Customers
                            </p>

                            <p
                                class="mt-4 text-4xl font-light tracking-[-0.04em]"
                            >
                                {{ customers.length }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-[#F4A6B8]/20 text-sm"
                        >
                            ○
                        </div>
                    </div>
                </div>


                <div
                    class="border border-[#191919]/10 bg-white p-6"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                            >
                                Total Orders
                            </p>

                            <p
                                class="mt-4 text-4xl font-light tracking-[-0.04em]"
                            >
                                {{ totalOrders }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-[#E85D75]/10 text-sm text-[#E85D75]"
                        >
                            #
                        </div>
                    </div>
                </div>

            </div>
        </section>


        <!-- =====================================================
             SEARCH
        ====================================================== -->
        <section
            class="border-b border-[#191919]/10 px-6 py-5 md:px-10"
        >
            <div class="relative w-full lg:max-w-xl">

                <span
                    class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#191919]/35"
                >
                    ⌕
                </span>

                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search name, email, phone..."
                    class="h-12 w-full border border-[#191919]/12 bg-white pl-11 pr-4 text-sm outline-none transition placeholder:text-[#191919]/30 focus:border-[#E85D75]"
                />

            </div>
        </section>


        <!-- =====================================================
             ERROR
        ====================================================== -->
        <section
            v-if="error"
            class="px-6 pt-6 md:px-10"
        >
            <div
                class="border border-[#B33A4A]/20 bg-[#FBE9EC] p-5 text-sm text-[#B33A4A]"
            >
                {{ error }}
            </div>
        </section>


        <!-- =====================================================
             CUSTOMER TABLE
        ====================================================== -->
        <section
            class="px-6 py-8 md:px-10 md:py-10"
        >

            <!-- Loading -->
            <div
                v-if="loading"
                class="flex min-h-[300px] items-center justify-center"
            >
                <div class="text-center">

                    <div
                        class="mx-auto mb-4 h-8 w-8 animate-spin rounded-full border-2 border-[#191919]/10 border-t-[#E85D75]"
                    ></div>

                    <p
                        class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                    >
                        Loading customers
                    </p>

                </div>
            </div>


            <!-- Empty -->
            <div
                v-else-if="filteredCustomers.length === 0"
                class="flex min-h-[300px] items-center justify-center border border-dashed border-[#191919]/15 bg-white/40"
            >
                <div class="text-center">

                    <div
                        class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-[#191919]/10 text-xl"
                    >
                        ○
                    </div>

                    <h2 class="text-xl font-light">
                        No customers found.
                    </h2>

                    <p
                        class="mt-2 text-sm text-[#191919]/45"
                    >
                        Try changing your search.
                    </p>

                </div>
            </div>


            <!-- Table -->
            <div
                v-else
                class="overflow-hidden border border-[#191919]/10 bg-white"
            >

                <div class="overflow-x-auto">

                    <table
                        class="w-full min-w-[850px] border-collapse"
                    >

                        <thead>
                            <tr
                                class="border-b border-[#191919]/10 bg-[#F4F0F1]/60"
                            >
                                <th
                                    class="px-6 py-4 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Customer
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Contact
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Orders
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Joined
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>


                        <tbody>

                            <tr
                                v-for="customer in filteredCustomers"
                                :key="customer.id"
                                class="border-b border-[#191919]/8 last:border-b-0 transition hover:bg-[#FFF8FA]"
                            >

                                <!-- Customer -->
                                <td class="px-6 py-5">

                                    <div
                                        class="flex items-center gap-4"
                                    >

                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F4A6B8]/20 text-xs font-medium text-[#B7465B]"
                                        >
                                            {{ getInitials(customer.name) }}
                                        </div>

                                        <div class="min-w-0">

                                            <p
                                                class="truncate text-sm font-medium"
                                            >
                                                {{ customer.name || 'Unknown Customer' }}
                                            </p>

                                            <p
                                                class="mt-1 text-[10px] uppercase tracking-[0.12em] text-[#191919]/30"
                                            >
                                                ID {{ customer.id }}
                                            </p>

                                        </div>

                                    </div>

                                </td>


                                <!-- Contact -->
                                <td class="px-6 py-5">

                                    <p
                                        class="text-sm text-[#191919]/75"
                                    >
                                        {{ customer.email || 'No email' }}
                                    </p>

                                    <p
                                        v-if="customer.phone"
                                        class="mt-1 text-xs text-[#191919]/40"
                                    >
                                        {{ customer.phone }}
                                    </p>

                                </td>


                                <!-- Orders -->
                                <td class="px-6 py-5">

                                    <span
                                        class="inline-flex min-w-10 items-center justify-center rounded-full bg-[#191919]/[0.05] px-3 py-1.5 text-[10px] font-semibold"
                                    >
                                        {{ customer.orders_count || 0 }}
                                    </span>

                                </td>


                                <!-- Joined -->
                                <td class="px-6 py-5">

                                    <p class="text-sm">
                                        {{ formatDate(customer.created_at) }}
                                    </p>

                                    <p
                                        class="mt-1 text-[10px] text-[#191919]/35"
                                    >
                                        {{ formatDateTime(customer.created_at) }}
                                    </p>

                                </td>


                                <!-- Action -->
                                <td class="px-6 py-5 text-right">

                                    <button
                                        type="button"
                                        @click="openCustomer(customer)"
                                        class="border border-[#191919]/12 px-4 py-2 text-[9px] font-semibold uppercase tracking-[0.18em] transition hover:border-[#191919] hover:bg-[#191919] hover:text-white"
                                    >
                                        View Details
                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <div
                    class="flex items-center justify-between border-t border-[#191919]/10 px-6 py-4"
                >

                    <p
                        class="text-[10px] uppercase tracking-[0.16em] text-[#191919]/40"
                    >
                        Showing

                        <span class="text-[#191919]">
                            {{ filteredCustomers.length }}
                        </span>

                        customers
                    </p>

                    <p
                        class="text-[10px] uppercase tracking-[0.16em] text-[#191919]/40"
                    >
                        Total

                        <span class="text-[#191919]">
                            {{ customers.length }}
                        </span>
                    </p>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CUSTOMER DETAIL
        ====================================================== -->
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >

            <div
                v-if="showDetail && selectedCustomer"
                class="fixed inset-0 z-50 overflow-y-auto bg-[#191919]/45 p-4 backdrop-blur-sm md:p-8"
                @click.self="closeCustomer"
            >

                <div
                    class="mx-auto min-h-full max-w-6xl py-4 md:py-8"
                >

                    <div
                        class="overflow-hidden bg-[#FFF8FA] shadow-2xl"
                    >

                        <!-- Detail Header -->
                        <div
                            class="flex items-start justify-between border-b border-[#191919]/10 bg-white px-6 py-6 md:px-8"
                        >

                            <div class="flex items-center gap-5">

                                <div
                                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#F4A6B8]/25 text-sm font-medium text-[#B7465B]"
                                >
                                    {{ getInitials(selectedCustomer.name) }}
                                </div>

                                <div>

                                    <p
                                        class="mb-2 text-[9px] font-semibold uppercase tracking-[0.25em] text-[#E85D75]"
                                    >
                                        Customer Profile
                                    </p>

                                    <h2
                                        class="text-2xl font-light tracking-[-0.03em] md:text-3xl"
                                    >
                                        {{ selectedCustomer.name || 'Unknown Customer' }}
                                    </h2>

                                    <p
                                        class="mt-2 text-xs text-[#191919]/45"
                                    >
                                        Customer ID #{{ selectedCustomer.id }}
                                    </p>

                                </div>

                            </div>


                            <button
                                type="button"
                                @click="closeCustomer"
                                class="flex h-10 w-10 items-center justify-center border border-[#191919]/10 text-lg transition hover:border-[#191919] hover:bg-[#191919] hover:text-white"
                            >
                                ×
                            </button>

                        </div>


                        <!-- Loading Detail -->
                        <div
                            v-if="detailLoading"
                            class="flex min-h-[350px] items-center justify-center"
                        >

                            <div class="text-center">

                                <div
                                    class="mx-auto mb-4 h-8 w-8 animate-spin rounded-full border-2 border-[#191919]/10 border-t-[#E85D75]"
                                ></div>

                                <p
                                    class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Loading customer
                                </p>

                            </div>

                        </div>


                        <!-- Detail Error -->
                        <div
                            v-else-if="detailError"
                            class="p-8"
                        >

                            <div
                                class="border border-[#B33A4A]/20 bg-[#FBE9EC] p-5 text-sm text-[#B33A4A]"
                            >
                                {{ detailError }}
                            </div>

                        </div>


                        <!-- Detail Content -->
                        <div
                            v-else
                            class="p-6 md:p-8"
                        >

                            <!-- Customer Information -->
                            <div
                                class="grid gap-6 lg:grid-cols-3"
                            >

                                <div
                                    class="border border-[#191919]/10 bg-white p-6"
                                >

                                    <p
                                        class="mb-5 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                    >
                                        Contact
                                    </p>

                                    <div class="space-y-4">

                                        <div>
                                            <p
                                                class="text-[9px] uppercase tracking-[0.15em] text-[#191919]/30"
                                            >
                                                Email
                                            </p>

                                            <p
                                                class="mt-2 break-all text-sm"
                                            >
                                                {{ selectedCustomer.email || 'No email' }}
                                            </p>
                                        </div>

                                        <div>
                                            <p
                                                class="text-[9px] uppercase tracking-[0.15em] text-[#191919]/30"
                                            >
                                                Phone
                                            </p>

                                            <p
                                                class="mt-2 text-sm"
                                            >
                                                {{ selectedCustomer.phone || 'No phone' }}
                                            </p>
                                        </div>

                                    </div>

                                </div>


                                <div
                                    class="border border-[#191919]/10 bg-white p-6"
                                >

                                    <p
                                        class="mb-5 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                    >
                                        Address
                                    </p>

                                    <p
                                        class="text-sm leading-7 text-[#191919]/60"
                                    >
                                        {{ selectedCustomer.address || 'No address provided.' }}
                                    </p>

                                </div>


                                <div
                                    class="border border-[#191919]/10 bg-[#191919] p-6 text-white"
                                >

                                    <p
                                        class="mb-5 text-[9px] font-semibold uppercase tracking-[0.2em] text-white/35"
                                    >
                                        Customer Since
                                    </p>

                                    <p
                                        class="text-3xl font-light tracking-[-0.04em]"
                                    >
                                        {{ formatDate(selectedCustomer.created_at) }}
                                    </p>

                                    <p
                                        class="mt-3 text-xs text-white/35"
                                    >
                                        {{ selectedCustomer.orders?.length || 0 }}
                                        order(s)
                                    </p>

                                </div>

                            </div>


                            <!-- Orders -->
                            <div
                                class="mt-6 border border-[#191919]/10 bg-white"
                            >

                                <div
                                    class="flex flex-col gap-3 border-b border-[#191919]/10 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                                >

                                    <div>

                                        <p
                                            class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                        >
                                            Order History
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-[#191919]/40"
                                        >
                                            All projects created by this customer.
                                        </p>

                                    </div>

                                    <div
                                        class="inline-flex w-fit rounded-full bg-[#F4A6B8]/15 px-3 py-1.5 text-[9px] font-semibold uppercase tracking-[0.12em] text-[#B7465B]"
                                    >
                                        {{ selectedCustomer.orders?.length || 0 }}
                                        Orders
                                    </div>

                                </div>


                                <div
                                    v-if="selectedCustomer.orders?.length"
                                    class="divide-y divide-[#191919]/8"
                                >

                                    <div
                                        v-for="order in selectedCustomer.orders"
                                        :key="order.id"
                                        class="px-6 py-6"
                                    >

                                        <div
                                            class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                                        >

                                            <div class="min-w-0">

                                                <div
                                                    class="flex flex-wrap items-center gap-3"
                                                >

                                                    <span
                                                        class="font-mono text-sm font-medium"
                                                    >
                                                        #{{ order.order_number }}
                                                    </span>

                                                    <span
                                                        class="rounded-full px-3 py-1.5 text-[9px] font-semibold uppercase tracking-[0.12em]"
                                                        :class="getOrderStatusClass(order.status)"
                                                    >
                                                        {{ getOrderStatusLabel(order.status) }}
                                                    </span>

                                                </div>


                                                <p
                                                    class="mt-2 text-xs text-[#191919]/35"
                                                >
                                                    {{ formatDateTime(order.created_at) }}
                                                </p>


                                                <p
                                                    class="mt-4 max-w-2xl text-sm leading-6 text-[#191919]/55"
                                                >
                                                    {{ getOrderServices(order) }}
                                                </p>

                                            </div>


                                            <div
                                                class="shrink-0 lg:text-right"
                                            >

                                                <p
                                                    class="text-[9px] uppercase tracking-[0.15em] text-[#191919]/30"
                                                >
                                                    Total
                                                </p>

                                                <p
                                                    class="mt-2 text-lg font-medium"
                                                >
                                                    {{ formatCurrency(getOrderTotal(order)) }}
                                                </p>

                                                <div
                                                    v-if="order.payment"
                                                    class="mt-3"
                                                >

                                                    <span
                                                        class="rounded-full px-3 py-1.5 text-[9px] font-semibold uppercase tracking-[0.12em]"
                                                        :class="getPaymentStatusClass(order.payment.status)"
                                                    >
                                                        Payment:
                                                        {{ getPaymentStatusLabel(order.payment.status) }}
                                                    </span>

                                                </div>

                                            </div>

                                        </div>


                                        <!-- Order Items -->
                                        <div
                                            v-if="order.items?.length"
                                            class="mt-5 grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
                                        >

                                            <div
                                                v-for="item in order.items"
                                                :key="item.id"
                                                class="border border-[#191919]/8 bg-[#FFF8FA] p-4"
                                            >

                                                <p
                                                    class="text-xs font-medium"
                                                >
                                                    {{
                                                        item.service?.name ||
                                                        item.service?.title ||
                                                        'Service'
                                                    }}
                                                </p>

                                                <div
                                                    class="mt-3 flex items-center justify-between gap-3"
                                                >

                                                    <span
                                                        class="text-[10px] text-[#191919]/40"
                                                    >
                                                        Qty {{ item.quantity }}
                                                    </span>

                                                    <span
                                                        class="text-xs font-medium"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                item.subtotal ??
                                                                item.total ??
                                                                Number(
                                                                    item.price ||
                                                                    item.service?.price ||
                                                                    0
                                                                ) *
                                                                Number(
                                                                    item.quantity || 1
                                                                )
                                                            )
                                                        }}
                                                    </span>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <div
                                    v-else
                                    class="px-6 py-12 text-center"
                                >

                                    <div
                                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[#191919]/10 text-xl text-[#191919]/35"
                                    >
                                        #
                                    </div>

                                    <p
                                        class="text-sm text-[#191919]/45"
                                    >
                                        This customer has no orders yet.
                                    </p>

                                </div>

                            </div>


                            <!-- Reviews -->
                            <div
                                class="mt-6 border border-[#191919]/10 bg-white"
                            >

                                <div
                                    class="border-b border-[#191919]/10 px-6 py-5"
                                >

                                    <p
                                        class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                    >
                                        Reviews
                                    </p>

                                </div>


                                <div
                                    v-if="selectedCustomer.reviews?.length"
                                    class="divide-y divide-[#191919]/8"
                                >

                                    <div
                                        v-for="review in selectedCustomer.reviews"
                                        :key="review.id"
                                        class="px-6 py-6"
                                    >

                                        <div
                                            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                        >

                                            <div>

                                                <div
                                                    class="flex items-center gap-2"
                                                >

                                                    <span
                                                        class="text-sm tracking-[0.15em] text-[#E85D75]"
                                                    >
                                                        {{
                                                            '★'.repeat(
                                                                Number(
                                                                    review.rating || 0
                                                                )
                                                            )
                                                        }}
                                                    </span>

                                                    <span
                                                        class="text-xs text-[#191919]/35"
                                                    >
                                                        {{
                                                            review.rating || 0
                                                        }}/5
                                                    </span>

                                                </div>

                                                <p
                                                    v-if="review.comment"
                                                    class="mt-4 max-w-2xl text-sm leading-7 text-[#191919]/60"
                                                >
                                                    {{ review.comment }}
                                                </p>

                                            </div>

                                            <span
                                                class="shrink-0 text-[10px] text-[#191919]/35"
                                            >
                                                {{ formatDate(review.created_at) }}
                                            </span>

                                        </div>

                                    </div>

                                </div>


                                <div
                                    v-else
                                    class="px-6 py-12 text-center"
                                >

                                    <p
                                        class="text-sm text-[#191919]/45"
                                    >
                                        This customer has not submitted a review yet.
                                    </p>

                                </div>

                            </div>


                            <!-- Footer Meta -->
                            <div
                                class="mt-6 border border-[#191919]/10 bg-[#191919] p-6 text-white"
                            >

                                <div
                                    class="grid gap-6 sm:grid-cols-3"
                                >

                                    <div>

                                        <p
                                            class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-white/40"
                                        >
                                            Customer ID
                                        </p>

                                        <p
                                            class="font-mono text-xs"
                                        >
                                            {{ selectedCustomer.id }}
                                        </p>

                                    </div>


                                    <div>

                                        <p
                                            class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-white/40"
                                        >
                                            Created
                                        </p>

                                        <p class="text-xs">
                                            {{ formatDateTime(selectedCustomer.created_at) }}
                                        </p>

                                    </div>


                                    <div>

                                        <p
                                            class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-white/40"
                                        >
                                            Updated
                                        </p>

                                        <p class="text-xs">
                                            {{ formatDateTime(selectedCustomer.updated_at) }}
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- Modal Footer -->
                        <div
                            class="flex flex-col gap-3 border-t border-[#191919]/10 bg-white px-6 py-5 sm:flex-row sm:items-center sm:justify-between md:px-8"
                        >

                            <p
                                class="text-[9px] uppercase tracking-[0.15em] text-[#191919]/35"
                            >
                                Teras Memori / Customer Management
                            </p>

                            <button
                                type="button"
                                @click="closeCustomer"
                                class="h-11 border border-[#191919] bg-[#191919] px-6 text-[10px] font-semibold uppercase tracking-[0.18em] text-white transition hover:border-[#E85D75] hover:bg-[#E85D75]"
                            >
                                Close Details
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </Transition>

    </div>
</template>
```

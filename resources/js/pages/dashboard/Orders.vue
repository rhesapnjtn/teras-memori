```vue
<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../services/api'

const orders = ref([])
const loading = ref(true)
const errorMessage = ref('')

const selectedOrder = ref(null)
const showDetail = ref(false)

const updating = ref(false)
const updateMessage = ref('')
const updateError = ref('')

const searchQuery = ref('')
const statusFilter = ref('all')

const orderStatuses = [
    'pending',
    'confirmed',
    'processing',
    'completed',
    'cancelled',
]

const paymentStatuses = [
    'pending',
    'paid',
    'failed',
    'expired',
    'refunded',
]

const fetchOrders = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/orders')

        orders.value = response.data?.data || []
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Gagal mengambil data orders.'
    } finally {
        loading.value = false
    }
}

const filteredOrders = computed(() => {
    let result = [...orders.value]

    if (statusFilter.value !== 'all') {
        result = result.filter(
            (order) => order.status === statusFilter.value
        )
    }

    const query = searchQuery.value.trim().toLowerCase()

    if (query) {
        result = result.filter((order) => {
            const orderNumber =
                order.order_number?.toLowerCase() || ''

            const customerName =
                order.customer?.name?.toLowerCase() || ''

            const customerEmail =
                order.customer?.email?.toLowerCase() || ''

            return (
                orderNumber.includes(query) ||
                customerName.includes(query) ||
                customerEmail.includes(query)
            )
        })
    }

    return result
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value || 0))
}

const formatDate = (date) => {
    if (!date) return '—'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date))
}

const formatDateTime = (date) => {
    if (!date) return '—'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date))
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

const paymentLabel = (status) => {
    const labels = {
        pending: 'Pending',
        paid: 'Paid',
        failed: 'Failed',
        expired: 'Expired',
        refunded: 'Refunded',
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

const paymentClass = (status) => {
    const classes = {
        pending: 'text-amber-600',
        paid: 'text-emerald-600',
        failed: 'text-red-500',
        expired: 'text-[#191919]/35',
        refunded: 'text-blue-600',
    }

    return classes[status] || 'text-[#191919]/35'
}

const openDetail = (order) => {
    selectedOrder.value = JSON.parse(JSON.stringify(order))
    updateMessage.value = ''
    updateError.value = ''
    showDetail.value = true
}

const closeDetail = () => {
    if (updating.value) return

    showDetail.value = false
    selectedOrder.value = null
}

const updateOrderStatus = async () => {
    if (!selectedOrder.value) return

    updating.value = true
    updateMessage.value = ''
    updateError.value = ''

    try {
        const response = await api.patch(
            `/orders/${selectedOrder.value.id}/status`,
            {
                status: selectedOrder.value.status,
            }
        )

        const updatedOrder =
            response.data?.data || response.data?.order

        const index = orders.value.findIndex(
            (order) => order.id === selectedOrder.value.id
        )

        if (index !== -1) {
            orders.value[index] = {
                ...orders.value[index],
                ...(updatedOrder || {
                    status: selectedOrder.value.status,
                }),
            }

            selectedOrder.value = {
                ...selectedOrder.value,
                ...(updatedOrder || {
                    status: selectedOrder.value.status,
                }),
            }
        }

        updateMessage.value = 'Status order berhasil diperbarui.'
    } catch (error) {
        updateError.value =
            error.response?.data?.message ||
            'Gagal memperbarui status order.'
    } finally {
        updating.value = false
    }
}

const updatePaymentStatus = async () => {
    if (!selectedOrder.value) return

    updating.value = true
    updateMessage.value = ''
    updateError.value = ''

    try {
        const response = await api.patch(
            `/orders/${selectedOrder.value.id}/payment`,
            {
                status: selectedOrder.value.payment?.status,
            }
        )

        const updatedOrder =
            response.data?.data || response.data?.order

        const index = orders.value.findIndex(
            (order) => order.id === selectedOrder.value.id
        )

        if (index !== -1) {
            orders.value[index] = {
                ...orders.value[index],
                ...(updatedOrder || {}),
            }

            selectedOrder.value = {
                ...selectedOrder.value,
                ...(updatedOrder || {}),
            }
        }

        updateMessage.value = 'Status pembayaran berhasil diperbarui.'
    } catch (error) {
        updateError.value =
            error.response?.data?.message ||
            'Gagal memperbarui status pembayaran.'
    } finally {
        updating.value = false
    }
}

onMounted(fetchOrders)
</script>

<template>
    <section>
        <!-- Header -->
        <div class="mb-10">
            <p
                class="text-[9px] font-semibold uppercase tracking-[0.35em] text-[#191919]/30"
            >
                Management / Orders
            </p>

            <div
                class="mt-3 flex flex-col justify-between gap-5 lg:flex-row lg:items-end"
            >
                <div>
                    <h1
                        class="text-4xl font-medium tracking-[-0.06em] sm:text-5xl"
                    >
                        ORDERS<span class="text-[#E85D75]">.</span>
                    </h1>

                    <p
                        class="mt-4 max-w-xl text-sm leading-6 text-[#191919]/40"
                    >
                        Manage customer orders, services, payments, and
                        production status.
                    </p>
                </div>

                <div
                    class="border-l border-[#191919]/10 pl-5"
                >
                    <p
                        class="text-[8px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Total orders
                    </p>

                    <p
                        class="mt-2 text-2xl font-medium tracking-[-0.04em]"
                    >
                        {{ orders.length }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Error -->
        <div
            v-if="errorMessage"
            class="mb-6 border-l-2 border-red-400 bg-red-50 px-5 py-4"
        >
            <p class="text-xs text-red-600">
                {{ errorMessage }}
            </p>
        </div>

        <!-- Loading -->
        <div
            v-if="loading"
            class="border border-[#191919]/10 bg-white p-10"
        >
            <div class="flex min-h-[350px] items-center justify-center">
                <div class="text-center">
                    <div
                        class="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-[#191919]/10 border-t-[#E85D75]"
                    ></div>

                    <p
                        class="mt-5 text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Loading orders
                    </p>
                </div>
            </div>
        </div>

        <div v-else>
            <!-- Filters -->
            <div
                class="mb-6 flex flex-col gap-3 border border-[#191919]/10 bg-white p-4 md:flex-row"
            >
                <div class="relative flex-1">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search order, customer, email..."
                        class="h-11 w-full border border-[#191919]/10 bg-[#FFF8FA] px-4 text-xs text-[#191919] outline-none transition placeholder:text-[#191919]/25 focus:border-[#E85D75]/40"
                    />
                </div>

                <select
                    v-model="statusFilter"
                    class="h-11 border border-[#191919]/10 bg-[#FFF8FA] px-4 text-xs text-[#191919] outline-none focus:border-[#E85D75]/40 md:w-48"
                >
                    <option value="all">
                        All statuses
                    </option>

                    <option
                        v-for="status in orderStatuses"
                        :key="status"
                        :value="status"
                    >
                        {{ statusLabel(status) }}
                    </option>
                </select>
            </div>

            <!-- Empty -->
            <div
                v-if="!filteredOrders.length"
                class="border border-[#191919]/10 bg-white"
            >
                <div
                    class="flex min-h-[350px] items-center justify-center px-6 text-center"
                >
                    <div>
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-[#E85D75]/20 bg-[#F4A6B8]/10 text-xl text-[#E85D75]"
                        >
                            ↗
                        </div>

                        <h2 class="mt-6 text-lg font-medium">
                            {{
                                orders.length
                                    ? 'No orders found'
                                    : 'No orders yet'
                            }}
                        </h2>

                        <p
                            class="mt-2 max-w-sm text-sm leading-6 text-[#191919]/35"
                        >
                            {{
                                orders.length
                                    ? 'Try changing your search or status filter.'
                                    : 'Customer orders will appear here once someone starts a project.'
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div
                v-else
                class="overflow-hidden border border-[#191919]/10 bg-white"
            >
                <!-- Table Header -->
                <div
                    class="hidden grid-cols-[1.25fr_1.1fr_0.8fr_0.8fr_110px] border-b border-[#191919]/10 px-7 py-4 text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30 lg:grid"
                >
                    <span>Order</span>
                    <span>Customer</span>
                    <span>Total</span>
                    <span>Status</span>
                    <span></span>
                </div>

                <!-- Rows -->
                <div
                    v-for="order in filteredOrders"
                    :key="order.id"
                    class="grid gap-5 border-b border-[#191919]/10 px-6 py-6 last:border-b-0 lg:grid-cols-[1.25fr_1.1fr_0.8fr_0.8fr_110px] lg:items-center lg:px-7"
                >
                    <!-- Order -->
                    <div>
                        <p
                            class="text-sm font-semibold tracking-[-0.01em]"
                        >
                            {{
                                order.order_number ||
                                `TM-${String(order.id).padStart(4, '0')}`
                            }}
                        </p>

                        <p
                            class="mt-1 text-[9px] uppercase tracking-[0.18em] text-[#191919]/30"
                        >
                            {{ formatDate(order.created_at) }}
                        </p>
                    </div>

                    <!-- Customer -->
                    <div>
                        <p class="text-sm text-[#191919]/70">
                            {{ order.customer?.name || 'Customer' }}
                        </p>

                        <p
                            class="mt-1 truncate text-[9px] text-[#191919]/30"
                        >
                            {{ order.customer?.email || '—' }}
                        </p>
                    </div>

                    <!-- Total -->
                    <div>
                        <p class="text-sm font-medium">
                            {{ formatCurrency(order.total) }}
                        </p>

                        <p
                            v-if="order.payment"
                            class="mt-1 text-[8px] uppercase tracking-[0.15em]"
                            :class="paymentClass(order.payment.status)"
                        >
                            {{ paymentLabel(order.payment.status) }}
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

                    <!-- Action -->
                    <div class="lg:text-right">
                        <button
                            type="button"
                            class="text-[8px] font-semibold uppercase tracking-[0.22em] text-[#E85D75] transition hover:text-[#191919]"
                            @click="openDetail(order)"
                        >
                            View detail →
                        </button>
                    </div>
                </div>
            </div>

            <!-- Result Count -->
            <div
                v-if="filteredOrders.length"
                class="mt-4 flex justify-between px-1 text-[8px] uppercase tracking-[0.25em] text-[#191919]/25"
            >
                <span>
                    Showing {{ filteredOrders.length }} of {{ orders.length }}
                </span>

                <span>
                    Teras Memori / Orders
                </span>
            </div>
        </div>

        <!-- Detail Modal -->
        <Transition name="fade">
            <div
                v-if="showDetail && selectedOrder"
                class="fixed inset-0 z-50 flex items-center justify-center bg-[#191919]/50 p-4 backdrop-blur-sm"
                @click.self="closeDetail"
            >
                <div
                    class="max-h-[90vh] w-full max-w-3xl overflow-y-auto bg-[#FFF8FA] shadow-2xl"
                >
                    <!-- Modal Header -->
                    <div
                        class="sticky top-0 z-10 flex items-start justify-between border-b border-[#191919]/10 bg-[#FFF8FA]/95 px-6 py-6 backdrop-blur-xl sm:px-8"
                    >
                        <div>
                            <p
                                class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                            >
                                Order detail
                            </p>

                            <h2
                                class="mt-2 text-2xl font-medium tracking-[-0.04em]"
                            >
                                {{
                                    selectedOrder.order_number ||
                                    `ORDER #${selectedOrder.id}`
                                }}<span class="text-[#E85D75]">.</span>
                            </h2>
                        </div>

                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-[#191919]/10 text-sm transition hover:border-[#E85D75]/30 hover:text-[#E85D75]"
                            @click="closeDetail"
                        >
                            ×
                        </button>
                    </div>

                    <div class="space-y-6 p-6 sm:p-8">
                        <!-- Customer -->
                        <div
                            class="border border-[#191919]/10 bg-white p-6"
                        >
                            <p
                                class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                            >
                                Customer
                            </p>

                            <div class="mt-5 grid gap-5 sm:grid-cols-3">
                                <div>
                                    <p
                                        class="text-[8px] uppercase tracking-[0.2em] text-[#191919]/25"
                                    >
                                        Name
                                    </p>

                                    <p class="mt-2 text-sm font-medium">
                                        {{
                                            selectedOrder.customer?.name ||
                                            '—'
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <p
                                        class="text-[8px] uppercase tracking-[0.2em] text-[#191919]/25"
                                    >
                                        Email
                                    </p>

                                    <p
                                        class="mt-2 break-all text-sm text-[#191919]/60"
                                    >
                                        {{
                                            selectedOrder.customer?.email ||
                                            '—'
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <p
                                        class="text-[8px] uppercase tracking-[0.2em] text-[#191919]/25"
                                    >
                                        Phone
                                    </p>

                                    <p class="mt-2 text-sm text-[#191919]/60">
                                        {{
                                            selectedOrder.customer?.phone ||
                                            '—'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Info -->
                        <div
                            class="border border-[#191919]/10 bg-white p-6"
                        >
                            <p
                                class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                            >
                                Order information
                            </p>

                            <div class="mt-5 space-y-4">
                                <div
                                    v-for="item in selectedOrder.items || []"
                                    :key="item.id"
                                    class="flex items-center justify-between gap-5 border-b border-[#191919]/10 pb-4"
                                >
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{
                                                item.service?.name ||
                                                'Service'
                                            }}
                                        </p>

                                        <p
                                            class="mt-1 text-[9px] uppercase tracking-[0.18em] text-[#191919]/30"
                                        >
                                            Qty {{ item.quantity }}
                                        </p>
                                    </div>

                                    <p class="text-sm">
                                        {{
                                            formatCurrency(
                                                Number(item.price || 0) *
                                                    Number(item.quantity || 0)
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="flex items-center justify-between pt-2"
                                >
                                    <span
                                        class="text-[8px] font-semibold uppercase tracking-[0.25em] text-[#191919]/30"
                                    >
                                        Total
                                    </span>

                                    <span
                                        class="text-xl font-medium tracking-[-0.03em]"
                                    >
                                        {{
                                            formatCurrency(
                                                selectedOrder.total
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div
                            v-if="selectedOrder.notes"
                            class="border border-[#191919]/10 bg-white p-6"
                        >
                            <p
                                class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                            >
                                Customer notes
                            </p>

                            <p
                                class="mt-4 text-sm leading-7 text-[#191919]/55"
                            >
                                {{ selectedOrder.notes }}
                            </p>
                        </div>

                        <!-- Status Management -->
                        <div
                            class="grid gap-6 md:grid-cols-2"
                        >
                            <div
                                class="border border-[#191919]/10 bg-white p-6"
                            >
                                <p
                                    class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                                >
                                    Order status
                                </p>

                                <select
                                    v-model="selectedOrder.status"
                                    class="mt-5 h-11 w-full border border-[#191919]/10 bg-[#FFF8FA] px-4 text-xs outline-none focus:border-[#E85D75]/40"
                                >
                                    <option
                                        v-for="status in orderStatuses"
                                        :key="status"
                                        :value="status"
                                    >
                                        {{ statusLabel(status) }}
                                    </option>
                                </select>

                                <button
                                    type="button"
                                    class="mt-3 w-full bg-[#191919] px-5 py-3 text-[8px] font-semibold uppercase tracking-[0.25em] text-white transition hover:bg-[#E85D75] disabled:cursor-not-allowed disabled:opacity-40"
                                    :disabled="updating"
                                    @click="updateOrderStatus"
                                >
                                    {{
                                        updating
                                            ? 'Updating...'
                                            : 'Update order status'
                                    }}
                                </button>
                            </div>

                            <div
                                class="border border-[#191919]/10 bg-white p-6"
                            >
                                <p
                                    class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                                >
                                    Payment status
                                </p>

                                <select
                                    v-model="
                                        selectedOrder.payment.status
                                    "
                                    class="mt-5 h-11 w-full border border-[#191919]/10 bg-[#FFF8FA] px-4 text-xs outline-none focus:border-[#E85D75]/40"
                                >
                                    <option
                                        v-for="status in paymentStatuses"
                                        :key="status"
                                        :value="status"
                                    >
                                        {{ paymentLabel(status) }}
                                    </option>
                                </select>

                                <button
                                    type="button"
                                    class="mt-3 w-full border border-[#191919]/10 bg-white px-5 py-3 text-[8px] font-semibold uppercase tracking-[0.25em] text-[#191919] transition hover:border-[#E85D75]/40 hover:text-[#E85D75] disabled:cursor-not-allowed disabled:opacity-40"
                                    :disabled="updating || !selectedOrder.payment"
                                    @click="updatePaymentStatus"
                                >
                                    {{
                                        updating
                                            ? 'Updating...'
                                            : 'Update payment'
                                    }}
                                </button>
                            </div>
                        </div>

                        <!-- Update Feedback -->
                        <div
                            v-if="updateMessage"
                            class="border-l-2 border-emerald-400 bg-emerald-50 px-5 py-4"
                        >
                            <p class="text-xs text-emerald-600">
                                {{ updateMessage }}
                            </p>
                        </div>

                        <div
                            v-if="updateError"
                            class="border-l-2 border-red-400 bg-red-50 px-5 py-4"
                        >
                            <p class="text-xs text-red-600">
                                {{ updateError }}
                            </p>
                        </div>

                        <!-- Metadata -->
                        <div
                            class="flex flex-col gap-3 border-t border-[#191919]/10 pt-5 text-[8px] uppercase tracking-[0.2em] text-[#191919]/25 sm:flex-row sm:justify-between"
                        >
                            <span>
                                Created
                                {{ formatDateTime(selectedOrder.created_at) }}
                            </span>

                            <span>
                                Updated
                                {{ formatDateTime(selectedOrder.updated_at) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </section>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
```

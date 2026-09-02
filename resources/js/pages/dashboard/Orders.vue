<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const orders = ref([])
const loading = ref(false)
const error = ref('')

const selectedOrder = ref(null)
const showDetail = ref(false)

const showPhotoPreview = ref(false)
const selectedPhotoIndex = ref(0)

const updating = ref(false)
const updateMessage = ref('')
const updateError = ref('')

const searchQuery = ref('')
const statusFilter = ref('all')

/*
|--------------------------------------------------------------------------
| Options
|--------------------------------------------------------------------------
*/

const orderStatuses = [
    {
        value: 'pending',
        label: 'Pending',
    },
    {
        value: 'confirmed',
        label: 'Confirmed',
    },
    {
        value: 'processing',
        label: 'Processing',
    },
    {
        value: 'completed',
        label: 'Completed',
    },
    {
        value: 'cancelled',
        label: 'Cancelled',
    },
]

const paymentStatuses = [
    {
        value: 'pending',
        label: 'Pending',
    },
    {
        value: 'paid',
        label: 'Paid',
    },
    {
        value: 'failed',
        label: 'Failed',
    },
    {
        value: 'expired',
        label: 'Expired',
    },
    {
        value: 'refunded',
        label: 'Refunded',
    },
]

/*
|--------------------------------------------------------------------------
| Fetch Orders
|--------------------------------------------------------------------------
*/

const fetchOrders = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await api.get('/admin/orders')

        orders.value = response.data.data ?? response.data ?? []
    } catch (err) {
        console.error('Fetch orders error:', err)

        error.value =
            err.response?.data?.message ||
            'Failed to load orders.'

        if (err.response?.status === 401) {
            router.push({ name: 'login' })
        }
    } finally {
        loading.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const filteredOrders = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    return orders.value.filter((order) => {
        const matchesStatus =
            statusFilter.value === 'all' ||
            order.status === statusFilter.value

        if (!matchesStatus) {
            return false
        }

        if (!query) {
            return true
        }

        const orderNumber =
            order.order_number?.toLowerCase() || ''

        const customerName =
            order.customer?.name?.toLowerCase() || ''

        const customerEmail =
            order.customer?.email?.toLowerCase() || ''

        const customerPhone =
            order.customer?.phone?.toLowerCase() || ''

        return (
            orderNumber.includes(query) ||
            customerName.includes(query) ||
            customerEmail.includes(query) ||
            customerPhone.includes(query)
        )
    })
})

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {
    const amount = Number(value || 0)

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount)
}

const formatDate = (value) => {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value))
}

const formatDateTime = (value) => {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value))
}

const formatFileSize = (bytes) => {
    const size = Number(bytes || 0)

    if (!size) {
        return '0 KB'
    }

    if (size < 1024) {
        return `${size} B`
    }

    if (size < 1024 * 1024) {
        return `${(size / 1024).toFixed(1)} KB`
    }

    return `${(size / (1024 * 1024)).toFixed(1)} MB`
}

/*
|--------------------------------------------------------------------------
| Labels
|--------------------------------------------------------------------------
*/

const getStatusLabel = (status) => {
    const item = orderStatuses.find(
        (statusItem) => statusItem.value === status
    )

    return item?.label || status || '-'
}

const getPaymentStatusLabel = (status) => {
    const item = paymentStatuses.find(
        (statusItem) => statusItem.value === status
    )

    return item?.label || status || '-'
}

/*
|--------------------------------------------------------------------------
| Status Classes
|--------------------------------------------------------------------------
*/

const getStatusClass = (status) => {
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

const getPaymentClass = (status) => {
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

/*
|--------------------------------------------------------------------------
| Open Detail
|--------------------------------------------------------------------------
*/

const openDetail = async (order) => {
    selectedOrder.value = order
    showDetail.value = true

    updateMessage.value = ''
    updateError.value = ''

    await refreshSelectedOrder()
}

const closeDetail = () => {
    showDetail.value = false
    selectedOrder.value = null

    showPhotoPreview.value = false
    selectedPhotoIndex.value = 0

    updateMessage.value = ''
    updateError.value = ''
}

/*
|--------------------------------------------------------------------------
| Refresh Selected Order
|--------------------------------------------------------------------------
*/

const refreshSelectedOrder = async () => {
    if (!selectedOrder.value?.id) {
        return
    }

    try {
        const response = await api.get(
            `/admin/orders/${selectedOrder.value.id}`
        )

        const data =
            response.data.data ??
            response.data

        selectedOrder.value = data

        const index = orders.value.findIndex(
            (order) => order.id === data.id
        )

        if (index !== -1) {
            orders.value[index] = data
        }

        const files = selectedOrder.value.files || []

        if (selectedPhotoIndex.value >= files.length) {
            selectedPhotoIndex.value = Math.max(
                files.length - 1,
                0
            )
        }

        if (files.length === 0) {
            showPhotoPreview.value = false
        }
    } catch (err) {
        console.error('Refresh selected order error:', err)

        updateError.value =
            err.response?.data?.message ||
            'Failed to refresh order details.'
    }
}

/*
|--------------------------------------------------------------------------
| Selected Photo
|--------------------------------------------------------------------------
*/

const selectedPhoto = computed(() => {
    const files = selectedOrder.value?.files || []

    return files[selectedPhotoIndex.value] || null
})

/*
|--------------------------------------------------------------------------
| Photo Preview
|--------------------------------------------------------------------------
*/

const openPhotoPreview = (index) => {
    const files = selectedOrder.value?.files || []

    if (!files.length) {
        return
    }

    selectedPhotoIndex.value = index
    showPhotoPreview.value = true

    document.body.style.overflow = 'hidden'
}

const closePhotoPreview = () => {
    showPhotoPreview.value = false
    document.body.style.overflow = ''
}

const nextPhoto = () => {
    const files = selectedOrder.value?.files || []

    if (!files.length) {
        return
    }

    selectedPhotoIndex.value =
        (selectedPhotoIndex.value + 1) % files.length
}

const previousPhoto = () => {
    const files = selectedOrder.value?.files || []

    if (!files.length) {
        return
    }

    selectedPhotoIndex.value =
        (selectedPhotoIndex.value - 1 + files.length) %
        files.length
}

/*
|--------------------------------------------------------------------------
| Open Original Photo
|--------------------------------------------------------------------------
*/

const openOriginalPhoto = () => {
    if (!selectedPhoto.value?.file_url) {
        return
    }

    window.open(
        selectedPhoto.value.file_url,
        '_blank',
        'noopener,noreferrer'
    )
}

/*
|--------------------------------------------------------------------------
| Download Photo
|--------------------------------------------------------------------------
*/

const downloadPhoto = async () => {
    if (!selectedPhoto.value?.file_url) {
        return
    }

    try {
        const response = await fetch(
            selectedPhoto.value.file_url
        )

        if (!response.ok) {
            throw new Error('Failed to download file.')
        }

        const blob = await response.blob()

        const url = window.URL.createObjectURL(blob)

        const link = document.createElement('a')

        link.href = url
        link.download =
            selectedPhoto.value.file_name ||
            `order-photo-${selectedPhotoIndex.value + 1}`

        document.body.appendChild(link)
        link.click()
        link.remove()

        window.URL.revokeObjectURL(url)
    } catch (err) {
        console.error('Download photo error:', err)

        openOriginalPhoto()
    }
}

/*
|--------------------------------------------------------------------------
| Keyboard Navigation
|--------------------------------------------------------------------------
*/

const handleKeyboard = (event) => {
    if (!showPhotoPreview.value) {
        return
    }

    if (event.key === 'Escape') {
        closePhotoPreview()
        return
    }

    if (event.key === 'ArrowRight') {
        nextPhoto()
        return
    }

    if (event.key === 'ArrowLeft') {
        previousPhoto()
    }
}

/*
|--------------------------------------------------------------------------
| Update Order Status
|--------------------------------------------------------------------------
*/

const updateOrderStatus = async (status) => {
    if (!selectedOrder.value?.id || updating.value) {
        return
    }

    updating.value = true
    updateMessage.value = ''
    updateError.value = ''

    try {
        const response = await api.patch(
            `/admin/orders/${selectedOrder.value.id}/status`,
            {
                status,
            }
        )

        const data =
            response.data.data ??
            response.data.order ??
            response.data

        if (data?.id) {
            selectedOrder.value = data
        } else {
            selectedOrder.value.status = status
        }

        const index = orders.value.findIndex(
            (order) =>
                order.id === selectedOrder.value.id
        )

        if (index !== -1) {
            orders.value[index] = {
                ...orders.value[index],
                ...selectedOrder.value,
            }
        }

        updateMessage.value =
            'Order status updated successfully.'

        await refreshSelectedOrder()
    } catch (err) {
        console.error('Update order status error:', err)

        updateError.value =
            err.response?.data?.message ||
            'Failed to update order status.'
    } finally {
        updating.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Update Payment Status
|--------------------------------------------------------------------------
*/

const updatePaymentStatus = async (status) => {
    if (!selectedOrder.value?.id || updating.value) {
        return
    }

    updating.value = true
    updateMessage.value = ''
    updateError.value = ''

    try {
        const response = await api.patch(
            `/admin/orders/${selectedOrder.value.id}/payment`,
            {
                status,
            }
        )

        const data =
            response.data.data ??
            response.data.order ??
            response.data

        if (data?.id) {
            selectedOrder.value = data
        }

        updateMessage.value =
            'Payment status updated successfully.'

        await refreshSelectedOrder()
    } catch (err) {
        console.error(
            'Update payment status error:',
            err
        )

        updateError.value =
            err.response?.data?.message ||
            'Failed to update payment status.'
    } finally {
        updating.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    fetchOrders()

    window.addEventListener(
        'keydown',
        handleKeyboard
    )
})

onUnmounted(() => {
    window.removeEventListener(
        'keydown',
        handleKeyboard
    )

    document.body.style.overflow = ''
})
</script>

<template>
    <div class="min-h-full bg-[#FFF8FA] text-[#191919]">

        <!-- ========================================================= -->
        <!-- PAGE HEADER -->
        <!-- ========================================================= -->

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
                        Management / Orders
                    </p>

                    <h1
                        class="text-4xl font-light tracking-[-0.04em] md:text-5xl"
                    >
                        Orders.
                    </h1>

                    <p
                        class="mt-3 max-w-xl text-sm leading-6 text-[#191919]/55"
                    >
                        Manage incoming projects, customer requests,
                        uploaded photos, payments, and production status.
                    </p>
                </div>

                <button
                    type="button"
                    @click="fetchOrders"
                    :disabled="loading"
                    class="inline-flex h-11 items-center justify-center border border-[#191919]/15 px-5 text-[10px] font-semibold uppercase tracking-[0.2em] transition hover:border-[#191919]/40 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ loading ? 'Refreshing...' : 'Refresh Orders' }}
                </button>
            </div>
        </section>

        <!-- ========================================================= -->
        <!-- FILTER BAR -->
        <!-- ========================================================= -->

        <section
            class="border-b border-[#191919]/10 px-6 py-5 md:px-10"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <!-- Search -->

                <div class="relative w-full lg:max-w-md">
                    <span
                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#191919]/35"
                    >
                        ⌕
                    </span>

                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search order, customer, email..."
                        class="h-12 w-full border border-[#191919]/12 bg-white pl-11 pr-4 text-sm outline-none transition placeholder:text-[#191919]/30 focus:border-[#E85D75]"
                    />
                </div>

                <!-- Status -->

                <div
                    class="flex flex-wrap items-center gap-2"
                >
                    <button
                        type="button"
                        @click="statusFilter = 'all'"
                        class="h-10 border px-4 text-[10px] font-semibold uppercase tracking-[0.16em] transition"
                        :class="
                            statusFilter === 'all'
                                ? 'border-[#191919] bg-[#191919] text-white'
                                : 'border-[#191919]/12 bg-white hover:border-[#191919]/30'
                        "
                    >
                        All
                    </button>

                    <button
                        v-for="status in orderStatuses"
                        :key="status.value"
                        type="button"
                        @click="statusFilter = status.value"
                        class="h-10 border px-4 text-[10px] font-semibold uppercase tracking-[0.16em] transition"
                        :class="
                            statusFilter === status.value
                                ? 'border-[#191919] bg-[#191919] text-white'
                                : 'border-[#191919]/12 bg-white hover:border-[#191919]/30'
                        "
                    >
                        {{ status.label }}
                    </button>
                </div>
            </div>
        </section>

        <!-- ========================================================= -->
        <!-- ERROR -->
        <!-- ========================================================= -->

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

        <!-- ========================================================= -->
        <!-- TABLE -->
        <!-- ========================================================= -->

        <section class="px-6 py-8 md:px-10 md:py-10">

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
                        Loading orders
                    </p>
                </div>
            </div>

            <!-- Empty -->

            <div
                v-else-if="filteredOrders.length === 0"
                class="flex min-h-[300px] items-center justify-center border border-dashed border-[#191919]/15 bg-white/40"
            >
                <div class="text-center">
                    <div
                        class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-[#191919]/10 text-xl"
                    >
                        ○
                    </div>

                    <h2 class="text-xl font-light">
                        No orders found.
                    </h2>

                    <p
                        class="mt-2 text-sm text-[#191919]/45"
                    >
                        Try changing your search or status filter.
                    </p>
                </div>
            </div>

            <!-- Desktop Table -->

            <div
                v-else
                class="overflow-hidden border border-[#191919]/10 bg-white"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] border-collapse">
                        <thead>
                            <tr
                                class="border-b border-[#191919]/10 bg-[#F4F0F1]/60"
                            >
                                <th
                                    class="px-6 py-4 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Order
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Customer
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Date
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Total
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                >
                                    Status
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
                                v-for="order in filteredOrders"
                                :key="order.id"
                                class="border-b border-[#191919]/8 last:border-b-0 transition hover:bg-[#FFF8FA]"
                            >
                                <!-- Order -->

                                <td class="px-6 py-5">
                                    <div
                                        class="font-mono text-sm font-medium"
                                    >
                                        #{{ order.order_number }}
                                    </div>

                                    <div
                                        class="mt-1 text-[10px] uppercase tracking-[0.14em] text-[#191919]/35"
                                    >
                                        ID {{ order.id }}
                                    </div>
                                </td>

                                <!-- Customer -->

                                <td class="px-6 py-5">
                                    <div
                                        class="text-sm font-medium"
                                    >
                                        {{
                                            order.customer?.name ||
                                            'Unknown Customer'
                                        }}
                                    </div>

                                    <div
                                        v-if="order.customer?.email"
                                        class="mt-1 text-xs text-[#191919]/45"
                                    >
                                        {{ order.customer.email }}
                                    </div>
                                </td>

                                <!-- Date -->

                                <td class="px-6 py-5">
                                    <div class="text-sm">
                                        {{
                                            formatDate(
                                                order.created_at
                                            )
                                        }}
                                    </div>

                                    <div
                                        class="mt-1 text-[10px] text-[#191919]/35"
                                    >
                                        {{
                                            formatDateTime(
                                                order.created_at
                                            )
                                        }}
                                    </div>
                                </td>

                                <!-- Total -->

                                <td class="px-6 py-5">
                                    <div
                                        class="text-sm font-medium"
                                    >
                                        {{
                                            formatCurrency(
                                                order.total_amount
                                            )
                                        }}
                                    </div>
                                </td>

                                <!-- Status -->

                                <td class="px-6 py-5">
                                    <div
                                        class="inline-flex rounded-full px-3 py-1.5 text-[9px] font-semibold uppercase tracking-[0.12em]"
                                        :class="
                                            getStatusClass(
                                                order.status
                                            )
                                        "
                                    >
                                        {{
                                            getStatusLabel(
                                                order.status
                                            )
                                        }}
                                    </div>
                                </td>

                                <!-- Action -->

                                <td class="px-6 py-5 text-right">
                                    <button
                                        type="button"
                                        @click="openDetail(order)"
                                        class="border border-[#191919]/12 px-4 py-2 text-[9px] font-semibold uppercase tracking-[0.18em] transition hover:border-[#191919] hover:bg-[#191919] hover:text-white"
                                    >
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->

                <div
                    class="flex items-center justify-between border-t border-[#191919]/10 px-6 py-4"
                >
                    <p
                        class="text-[10px] uppercase tracking-[0.16em] text-[#191919]/40"
                    >
                        Showing
                        <span class="text-[#191919]">
                            {{ filteredOrders.length }}
                        </span>
                        orders
                    </p>

                    <p
                        class="text-[10px] uppercase tracking-[0.16em] text-[#191919]/40"
                    >
                        Total
                        <span class="text-[#191919]">
                            {{ orders.length }}
                        </span>
                    </p>
                </div>
            </div>
        </section>

        <!-- ========================================================= -->
        <!-- DETAIL MODAL -->
        <!-- ========================================================= -->

        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showDetail && selectedOrder"
                class="fixed inset-0 z-50 overflow-y-auto bg-[#191919]/45 p-4 backdrop-blur-sm md:p-8"
                @click.self="closeDetail"
            >
                <div
                    class="mx-auto min-h-full max-w-6xl py-4 md:py-8"
                >
                    <div
                        class="overflow-hidden bg-[#FFF8FA] shadow-2xl"
                    >

                        <!-- Modal Header -->

                        <div
                            class="flex items-start justify-between border-b border-[#191919]/10 bg-white px-6 py-6 md:px-8"
                        >
                            <div>
                                <p
                                    class="mb-2 text-[9px] font-semibold uppercase tracking-[0.25em] text-[#E85D75]"
                                >
                                    Order Details
                                </p>

                                <h2
                                    class="text-2xl font-light tracking-[-0.03em] md:text-3xl"
                                >
                                    #{{
                                        selectedOrder.order_number
                                    }}
                                </h2>

                                <p
                                    class="mt-2 text-xs text-[#191919]/45"
                                >
                                    {{
                                        formatDateTime(
                                            selectedOrder.created_at
                                        )
                                    }}
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="closeDetail"
                                class="flex h-10 w-10 items-center justify-center border border-[#191919]/10 text-lg transition hover:border-[#191919] hover:bg-[#191919] hover:text-white"
                            >
                                ×
                            </button>
                        </div>

                        <!-- Modal Content -->

                        <div class="p-6 md:p-8">

                            <!-- Messages -->

                            <div
                                v-if="updateMessage"
                                class="mb-6 border border-[#287A45]/15 bg-[#E7F6EC] p-4 text-sm text-[#287A45]"
                            >
                                {{ updateMessage }}
                            </div>

                            <div
                                v-if="updateError"
                                class="mb-6 border border-[#B33A4A]/15 bg-[#FBE9EC] p-4 text-sm text-[#B33A4A]"
                            >
                                {{ updateError }}
                            </div>

                            <!-- Top Grid -->

                            <div
                                class="grid gap-6 lg:grid-cols-3"
                            >

                                <!-- Customer -->

                                <div
                                    class="border border-[#191919]/10 bg-white p-6"
                                >
                                    <div
                                        class="mb-5 flex items-center justify-between"
                                    >
                                        <p
                                            class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                        >
                                            Customer
                                        </p>

                                        <span
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-[#F4A6B8]/20 text-sm"
                                        >
                                            {{
                                                selectedOrder.customer?.name
                                                    ?.charAt(0)
                                                    ?.toUpperCase() ||
                                                '?'
                                            }}
                                        </span>
                                    </div>

                                    <h3
                                        class="text-lg font-medium"
                                    >
                                        {{
                                            selectedOrder.customer?.name ||
                                            'Unknown'
                                        }}
                                    </h3>

                                    <div
                                        class="mt-4 space-y-2 text-sm text-[#191919]/55"
                                    >
                                        <p>
                                            {{
                                                selectedOrder.customer?.email ||
                                                'No email'
                                            }}
                                        </p>

                                        <p>
                                            {{
                                                selectedOrder.customer?.phone ||
                                                'No phone'
                                            }}
                                        </p>

                                        <p
                                            v-if="
                                                selectedOrder.customer
                                                    ?.address
                                            "
                                            class="leading-6"
                                        >
                                            {{
                                                selectedOrder.customer
                                                    .address
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Order Info -->

                                <div
                                    class="border border-[#191919]/10 bg-white p-6"
                                >
                                    <p
                                        class="mb-5 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                    >
                                        Order Information
                                    </p>

                                    <div class="space-y-4">
                                        <div
                                            class="flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-xs text-[#191919]/45"
                                            >
                                                Status
                                            </span>

                                            <span
                                                class="rounded-full px-3 py-1.5 text-[9px] font-semibold uppercase tracking-[0.12em]"
                                                :class="
                                                    getStatusClass(
                                                        selectedOrder.status
                                                    )
                                                "
                                            >
                                                {{
                                                    getStatusLabel(
                                                        selectedOrder.status
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-xs text-[#191919]/45"
                                            >
                                                Total
                                            </span>

                                            <span
                                                class="text-sm font-medium"
                                            >
                                                {{
                                                    formatCurrency(
                                                        selectedOrder.total_amount
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-xs text-[#191919]/45"
                                            >
                                                Created
                                            </span>

                                            <span
                                                class="text-xs"
                                            >
                                                {{
                                                    formatDate(
                                                        selectedOrder.created_at
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment -->

                                <div
                                    class="border border-[#191919]/10 bg-white p-6"
                                >
                                    <p
                                        class="mb-5 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                    >
                                        Payment
                                    </p>

                                    <div
                                        v-if="selectedOrder.payment"
                                        class="space-y-4"
                                    >
                                        <div
                                            class="flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-xs text-[#191919]/45"
                                            >
                                                Status
                                            </span>

                                            <span
                                                class="rounded-full px-3 py-1.5 text-[9px] font-semibold uppercase tracking-[0.12em]"
                                                :class="
                                                    getPaymentClass(
                                                        selectedOrder.payment.status
                                                    )
                                                "
                                            >
                                                {{
                                                    getPaymentStatusLabel(
                                                        selectedOrder.payment
                                                            .status
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-xs text-[#191919]/45"
                                            >
                                                Method
                                            </span>

                                            <span
                                                class="text-xs font-medium capitalize"
                                            >
                                                {{
                                                    selectedOrder.payment
                                                        .method
                                                        ?.replaceAll(
                                                            '_',
                                                            ' '
                                                        ) ||
                                                    '-'
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-xs text-[#191919]/45"
                                            >
                                                Amount
                                            </span>

                                            <span
                                                class="text-sm font-medium"
                                            >
                                                {{
                                                    formatCurrency(
                                                        selectedOrder.payment
                                                            .amount
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            v-if="
                                                selectedOrder.payment
                                                    .transaction_id
                                            "
                                            class="border-t border-[#191919]/8 pt-4"
                                        >
                                            <p
                                                class="mb-1 text-[9px] uppercase tracking-[0.15em] text-[#191919]/35"
                                            >
                                                Transaction ID
                                            </p>

                                            <p
                                                class="break-all font-mono text-[10px]"
                                            >
                                                {{
                                                    selectedOrder.payment
                                                        .transaction_id
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-else
                                        class="text-sm text-[#191919]/45"
                                    >
                                        No payment information.
                                    </div>
                                </div>
                            </div>

                            <!-- ================================================= -->
                            <!-- ITEMS -->
                            <!-- ================================================= -->

                            <div
                                class="mt-6 border border-[#191919]/10 bg-white"
                            >
                                <div
                                    class="border-b border-[#191919]/10 px-6 py-5"
                                >
                                    <p
                                        class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                    >
                                        Services
                                    </p>
                                </div>

                                <div
                                    v-if="
                                        selectedOrder.items?.length
                                    "
                                    class="divide-y divide-[#191919]/8"
                                >
                                    <div
                                        v-for="item in selectedOrder.items"
                                        :key="item.id"
                                        class="flex flex-col gap-3 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div>
                                            <h3
                                                class="text-sm font-medium"
                                            >
                                                {{
                                                    item.service?.name ||
                                                    item.service?.title ||
                                                    'Service'
                                                }}
                                            </h3>

                                            <p
                                                class="mt-1 text-xs text-[#191919]/40"
                                            >
                                                Quantity:
                                                {{ item.quantity }}
                                            </p>
                                        </div>

                                        <div
                                            class="text-sm font-medium"
                                        >
                                            {{
                                                formatCurrency(
                                                    item.subtotal ??
                                                        item.total ??
                                                        (
                                                            Number(
                                                                item.price ||
                                                                    item.service
                                                                        ?.price ||
                                                                    0
                                                            ) *
                                                            Number(
                                                                item.quantity ||
                                                                    1
                                                            )
                                                        )
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="px-6 py-8 text-sm text-[#191919]/45"
                                >
                                    No service items found.
                                </div>

                                <div
                                    class="flex items-center justify-between border-t border-[#191919]/10 bg-[#F4F0F1]/40 px-6 py-5"
                                >
                                    <span
                                        class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                    >
                                        Order Total
                                    </span>

                                    <span
                                        class="text-lg font-medium"
                                    >
                                        {{
                                            formatCurrency(
                                                selectedOrder.total_amount
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>

                            <!-- ================================================= -->
                            <!-- NOTES -->
                            <!-- ================================================= -->

                            <div
                                v-if="selectedOrder.notes"
                                class="mt-6 border border-[#191919]/10 bg-white p-6"
                            >
                                <p
                                    class="mb-4 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                >
                                    Customer Notes
                                </p>

                                <p
                                    class="whitespace-pre-line text-sm leading-7 text-[#191919]/65"
                                >
                                    {{ selectedOrder.notes }}
                                </p>
                            </div>

                            <!-- ================================================= -->
                            <!-- CUSTOMER PHOTOS -->
                            <!-- ================================================= -->

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
                                            Customer Photos
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-[#191919]/40"
                                        >
                                            Photos uploaded with this order.
                                        </p>
                                    </div>

                                    <div
                                        class="inline-flex w-fit rounded-full bg-[#F4A6B8]/15 px-3 py-1.5 text-[9px] font-semibold uppercase tracking-[0.12em] text-[#B7465B]"
                                    >
                                        {{
                                            selectedOrder.files?.length ||
                                            0
                                        }}
                                        Photos
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        selectedOrder.files?.length
                                    "
                                    class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5"
                                >
                                    <button
                                        v-for="(
                                            file, index
                                        ) in selectedOrder.files"
                                        :key="file.id"
                                        type="button"
                                        @click="
                                            openPhotoPreview(index)
                                        "
                                        class="group relative aspect-square overflow-hidden bg-[#F4F0F1] text-left"
                                    >
                                        <img
                                            :src="file.file_url"
                                            :alt="
                                                file.file_name ||
                                                `Customer photo ${
                                                    index + 1
                                                }`
                                            "
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        />

                                        <!-- Overlay -->

                                        <div
                                            class="absolute inset-0 flex items-end bg-gradient-to-t from-black/65 via-black/0 to-transparent p-3 opacity-0 transition group-hover:opacity-100"
                                        >
                                            <div class="min-w-0">
                                                <p
                                                    class="truncate text-[10px] font-medium text-white"
                                                >
                                                    {{
                                                        file.file_name
                                                    }}
                                                </p>

                                                <p
                                                    class="mt-1 text-[9px] text-white/65"
                                                >
                                                    {{
                                                        formatFileSize(
                                                            file.file_size
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Index -->

                                        <span
                                            class="absolute left-2 top-2 flex h-7 min-w-7 items-center justify-center rounded-full bg-white/90 px-2 text-[9px] font-semibold"
                                        >
                                            {{ index + 1 }}
                                        </span>
                                    </button>
                                </div>

                                <div
                                    v-else
                                    class="px-6 py-12 text-center"
                                >
                                    <div
                                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[#191919]/10 text-xl text-[#191919]/35"
                                    >
                                        ◇
                                    </div>

                                    <p
                                        class="text-sm text-[#191919]/45"
                                    >
                                        No photos uploaded for this order.
                                    </p>
                                </div>
                            </div>

                            <!-- ================================================= -->
                            <!-- MANAGEMENT -->
                            <!-- ================================================= -->

                            <div
                                class="mt-6 grid gap-6 lg:grid-cols-2"
                            >

                                <!-- Order Status -->

                                <div
                                    class="border border-[#191919]/10 bg-white p-6"
                                >
                                    <p
                                        class="mb-5 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                    >
                                        Update Order Status
                                    </p>

                                    <div
                                        class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                                    >
                                        <button
                                            v-for="status in orderStatuses"
                                            :key="status.value"
                                            type="button"
                                            @click="
                                                updateOrderStatus(
                                                    status.value
                                                )
                                            "
                                            :disabled="updating"
                                            class="min-h-11 border px-3 py-2 text-[9px] font-semibold uppercase tracking-[0.12em] transition disabled:cursor-not-allowed disabled:opacity-50"
                                            :class="
                                                selectedOrder.status ===
                                                status.value
                                                    ? 'border-[#191919] bg-[#191919] text-white'
                                                    : 'border-[#191919]/12 bg-white hover:border-[#191919]/35'
                                            "
                                        >
                                            {{ status.label }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Payment Status -->

                                <div
                                    class="border border-[#191919]/10 bg-white p-6"
                                >
                                    <p
                                        class="mb-5 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                                    >
                                        Update Payment
                                    </p>

                                    <div
                                        v-if="selectedOrder.payment"
                                        class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                                    >
                                        <button
                                            v-for="status in paymentStatuses"
                                            :key="status.value"
                                            type="button"
                                            @click="
                                                updatePaymentStatus(
                                                    status.value
                                                )
                                            "
                                            :disabled="updating"
                                            class="min-h-11 border px-3 py-2 text-[9px] font-semibold uppercase tracking-[0.12em] transition disabled:cursor-not-allowed disabled:opacity-50"
                                            :class="
                                                selectedOrder.payment
                                                    .status ===
                                                status.value
                                                    ? 'border-[#191919] bg-[#191919] text-white'
                                                    : 'border-[#191919]/12 bg-white hover:border-[#191919]/35'
                                            "
                                        >
                                            {{ status.label }}
                                        </button>
                                    </div>

                                    <p
                                        v-else
                                        class="text-sm text-[#191919]/45"
                                    >
                                        Payment has not been created yet.
                                    </p>
                                </div>
                            </div>

                            <!-- ================================================= -->
                            <!-- METADATA -->
                            <!-- ================================================= -->

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
                                            Order ID
                                        </p>

                                        <p
                                            class="font-mono text-xs"
                                        >
                                            {{ selectedOrder.id }}
                                        </p>
                                    </div>

                                    <div>
                                        <p
                                            class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-white/40"
                                        >
                                            Created
                                        </p>

                                        <p
                                            class="text-xs"
                                        >
                                            {{
                                                formatDateTime(
                                                    selectedOrder.created_at
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p
                                            class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-white/40"
                                        >
                                            Updated
                                        </p>

                                        <p
                                            class="text-xs"
                                        >
                                            {{
                                                formatDateTime(
                                                    selectedOrder.updated_at
                                                )
                                            }}
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
                                Teras Memori / Order Management
                            </p>

                            <button
                                type="button"
                                @click="closeDetail"
                                class="h-11 border border-[#191919] bg-[#191919] px-6 text-[10px] font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[#E85D75] hover:border-[#E85D75]"
                            >
                                Close Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ========================================================= -->
        <!-- PHOTO LIGHTBOX -->
        <!-- ========================================================= -->

        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="
                    showPhotoPreview &&
                    selectedPhoto
                "
                class="fixed inset-0 z-[70] flex flex-col bg-[#191919]/95 text-white"
            >

                <!-- Lightbox Header -->

                <div
                    class="flex items-center justify-between border-b border-white/10 px-5 py-4 md:px-8"
                >
                    <div class="min-w-0">
                        <p
                            class="text-[9px] font-semibold uppercase tracking-[0.2em] text-white/40"
                        >
                            Customer Photo
                        </p>

                        <p
                            class="mt-1 truncate text-sm"
                        >
                            {{ selectedPhoto.file_name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="openOriginalPhoto"
                            class="hidden h-10 items-center border border-white/15 px-4 text-[9px] font-semibold uppercase tracking-[0.16em] transition hover:border-white/40 sm:inline-flex"
                        >
                            Open original ↗
                        </button>

                        <button
                            type="button"
                            @click="downloadPhoto"
                            class="hidden h-10 items-center border border-white/15 px-4 text-[9px] font-semibold uppercase tracking-[0.16em] transition hover:border-white/40 sm:inline-flex"
                        >
                            Download ↓
                        </button>

                        <button
                            type="button"
                            @click="closePhotoPreview"
                            class="flex h-10 w-10 items-center justify-center border border-white/15 text-xl transition hover:border-white/50"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <!-- Image Area -->

                <div
                    class="relative flex min-h-0 flex-1 items-center justify-center px-14 py-6 md:px-24"
                >

                    <!-- Previous -->

                    <button
                        v-if="
                            selectedOrder.files?.length > 1
                        "
                        type="button"
                        @click="previousPhoto"
                        class="absolute left-3 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/15 bg-white/5 text-xl transition hover:border-white/40 hover:bg-white/10 md:left-8"
                    >
                        ←
                    </button>

                    <!-- Image -->

                    <img
                        :src="selectedPhoto.file_url"
                        :alt="
                            selectedPhoto.file_name ||
                            'Customer photo'
                        "
                        class="max-h-full max-w-full object-contain"
                    />

                    <!-- Next -->

                    <button
                        v-if="
                            selectedOrder.files?.length > 1
                        "
                        type="button"
                        @click="nextPhoto"
                        class="absolute right-3 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/15 bg-white/5 text-xl transition hover:border-white/40 hover:bg-white/10 md:right-8"
                    >
                        →
                    </button>
                </div>

                <!-- Lightbox Footer -->

                <div
                    class="flex flex-col items-center justify-between gap-3 border-t border-white/10 px-5 py-4 sm:flex-row md:px-8"
                >
                    <div
                        class="text-[9px] uppercase tracking-[0.18em] text-white/40"
                    >
                        {{
                            formatFileSize(
                                selectedPhoto.file_size
                            )
                        }}
                    </div>

                    <div
                        class="text-[10px] font-semibold uppercase tracking-[0.2em]"
                    >
                        {{
                            selectedPhotoIndex + 1
                        }}
                        /
                        {{
                            selectedOrder.files?.length ||
                            0
                        }}
                    </div>

                    <div
                        class="hidden text-[9px] uppercase tracking-[0.15em] text-white/35 md:block"
                    >
                        ← → Navigate &nbsp; • &nbsp; ESC Close
                    </div>

                    <div
                        class="flex gap-2 sm:hidden"
                    >
                        <button
                            type="button"
                            @click="openOriginalPhoto"
                            class="h-9 border border-white/15 px-3 text-[9px] uppercase tracking-[0.12em]"
                        >
                            Original
                        </button>

                        <button
                            type="button"
                            @click="downloadPhoto"
                            class="h-9 border border-white/15 px-3 text-[9px] uppercase tracking-[0.12em]"
                        >
                            Download
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
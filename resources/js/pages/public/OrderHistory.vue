<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const auth = useAuthStore()
const orders = ref([])
const loading = ref(true)
const errorMessage = ref('')

const fetchOrders = async () => {
    loading.value = true
    errorMessage.value = ''
    try {
        const response = await api.get('/orders')
        orders.value = response.data.data || response.data || []
    } catch (error) {
        console.error('Failed to load orders:', error)
        errorMessage.value = error.response?.data?.message || 'Gagal memuat riwayat pesanan'
    } finally {
        loading.value = false
    }
}

const formatCurrency = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(price || 0))
}

const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const statusLabel = (status) => {
    const labels = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        processing: 'Processing',
        completed: 'Completed',
        cancelled: 'Cancelled'
    }
    return labels[status] || status
}

const paymentLabel = (status) => {
    const labels = {
        pending: 'Pending',
        paid: 'Paid',
        failed: 'Failed',
        expired: 'Expired',
        refunded: 'Refunded'
    }
    return labels[status] || status
}

const statusClass = (status) => {
    const classes = {
        pending: 'bg-[#FFF4D6] text-[#8A6500]',
        confirmed: 'bg-[#E9E8FF] text-[#5147A8]',
        processing: 'bg-[#E8F3FF] text-[#2563A8]',
        completed: 'bg-[#E7F6EC] text-[#287A45]',
        cancelled: 'bg-[#FBE9EC] text-[#B33A4A]'
    }
    return classes[status] || 'bg-[#F1EEEE] text-[#5F5A5B]'
}

const paymentClass = (status) => {
    const classes = {
        paid: 'bg-[#E7F6EC] text-[#287A45]',
        pending: 'bg-[#FFF4D6] text-[#8A6500]',
        failed: 'bg-[#FBE9EC] text-[#B33A4A]',
        expired: 'bg-[#F1EEEE] text-[#5F5A5B]',
        refunded: 'bg-[#E9E8FF] text-[#5147A8]'
    }
    return classes[status] || 'bg-[#F1EEEE] text-[#5F5A5B]'
}

onMounted(async () => {
    if (!auth.isAuthenticated) return
    await fetchOrders()
})
</script>

<template>
    <div class="min-h-screen bg-[#FFF8FA] py-12">
        <div class="mx-auto max-w-4xl px-6">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-semibold tracking-wide">Riwayat Pesanan</h1>
                    <p class="mt-1 text-sm text-gray-600">Lacak status dan detail pesanan Anda</p>
                </div>
            </div>

            <div v-if="errorMessage" class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</div>

            <div v-if="loading" class="space-y-4">
                <div v-for="i in 4" :key="i" class="animate-pulse border-b border-[#191919]/10 py-6">
                    <div class="h-6 w-1/3 bg-gray-200 rounded mb-2"></div>
                    <div class="h-4 w-1/4 bg-gray-200 rounded"></div>
                </div>
            </div>

            <div v-else-if="orders.length === 0" class="border-y border-[#191919]/10 py-20 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <p class="mt-4 text-lg font-medium text-gray-900">Belum ada pesanan</p>
                <p class="mt-2 text-gray-500">Mulai pesan layanan pertama Anda</p>
                <RouterLink
                    :to="{ name: 'services' }"
                    class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#E85D75] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-white hover:bg-[#E85D75]/90"
                >
                    Lihat Layanan
                    <span>→</span>
                </RouterLink>
            </div>

            <div v-else class="border-y border-[#191919]/10">
                <RouterLink
                    v-for="order in orders"
                    :key="order.id"
                    :to="{ name: 'order-success', query: { orderId: order.id } }"
                    class="group relative block overflow-hidden border-b border-[#191919]/10 py-6 last:border-b-0 hover:bg-[#191919]/2 transition-colors"
                >
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="text-[10px] tracking-[0.2em] text-[#E85D75]">{{ order.order_number }}</span>
                            <div>
                                <p class="font-medium text-[#191919]">{{ order.items?.[0]?.service?.name || 'Layanan' }}</p>
                                <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-2 md:flex-row md:items-center gap-4 md:w-1/3">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-full text-xs font-medium" :class="statusClass(order.status)">
                                    {{ statusLabel(order.status) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-medium" :class="paymentClass(order.payment?.status)">
                                    {{ paymentLabel(order.payment?.status) }}
                                </span>
                            </div>
                            <div class="text-right md:w-auto">
                                <p class="font-semibold text-[#191919]">{{ formatCurrency(order.total_amount) }}</p>
                                <p class="text-xs text-gray-500">Total</p>
                            </div>
                            <span class="text-lg text-gray-300 group-hover:text-[#E85D75] transition-colors">→</span>
                        </div>
                    </div>
                </RouterLink>
            </div>
        </div>
    </div>
</template>
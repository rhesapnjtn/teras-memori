<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const router = useRouter()
const auth = useAuthStore()

const loading = ref(false)
const errorMessage = ref('')
const member = ref(null)
const transactions = ref([])

const fetchMember = async () => {
    loading.value = true
    errorMessage.value = ''
    try {
        const response = await api.get('/member')
        member.value = response.data.member
        transactions.value = response.data.transactions || []
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Gagal memuat data member'
    } finally {
        loading.value = false
    }
}

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID').format(val || 0)
}

const formatDate = (val) => {
    if (!val) return '-'
    return new Date(val).toLocaleString('id-ID')
}

const tierColor = computed(() => {
    const t = member.value?.member_tier
    if (t === 'platinum') return 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white'
    if (t === 'gold') return 'bg-yellow-500 text-white'
    if (t === 'silver') return 'bg-gray-400 text-white'
    return 'bg-amber-700 text-white'
})

onMounted(async () => {
    if (!auth.isAuthenticated) {
        router.push({ name: 'login' })
        return
    }
    await fetchMember()
})
</script>

<template>
    <div class="min-h-screen bg-[#FFF8FA] py-12">
        <div class="mx-auto max-w-4xl px-6">
            <h1 class="text-2xl font-semibold tracking-wide">Member Area</h1>
            <p class="mt-1 text-sm text-gray-600">Loyalty points, tier member & riwayat transaksi poin</p>

            <div v-if="errorMessage" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMessage }}</div>

            <div v-if="loading" class="mt-6 text-sm text-gray-500">Memuat...</div>

            <div v-else class="space-y-6">
                <div class="mt-6 rounded-xl border bg-white p-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Status Member</p>
                            <p class="mt-1 text-lg font-semibold">{{ member?.is_member ? 'Aktif' : 'Belum Jadi Member' }}</p>
                        </div>
                        <div :class="['rounded-full px-4 py-2 text-sm font-medium capitalize', tierColor]">
                            Tier: {{ member?.member_tier }}
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg border p-4">
                            <p class="text-sm text-gray-500">Total Poin</p>
                            <p class="mt-1 text-2xl font-semibold">{{ formatCurrency(member?.points) }}</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <p class="text-sm text-gray-500">Bergabung Sejak</p>
                            <p class="mt-1 text-sm">{{ formatDate(member?.member_joined_at) }}</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <p class="text-sm text-gray-500">Progress ke Tier Berikutnya</p>
                            <div class="mt-2 h-2 w-full rounded-full bg-gray-200">
                                <div class="h-2 rounded-full bg-indigo-600" :style="{ width: member?.progress_to_next_tier + '%' }"></div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">{{ member?.progress_to_next_tier }}% menuju {{ member?.next_tier }}</p>
                        </div>
                    </div>

                    <div class="mt-6 text-sm text-gray-600">
                        <p><strong>Manfaat Kreatif:</strong></p>
                        <ul class="mt-2 list-disc space-y-1 pl-4">
                            <li>Bronze – Poin dasar, akses member dashboard</li>
                            <li>Silver (≥1.000) – 1.2x poin per transaksi</li>
                            <li>Gold (≥5.000) – 1.5x poin per transaksi</li>
                            <li>Platinum (≥10.000) – 2.0x poin per transaksi</li>
                        </ul>
                        <p class="mt-2 text-xs text-gray-500">Poin dihitung otomatis saat pembayaran terverifikasi (paid). 1 poin = Rp10.000 (dasar), dikalikan multiplier sesuai tier.</p>
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-6">
                    <h2 class="text-lg font-medium">Riwayat Transaksi Poin</h2>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-gray-500">
                                    <th class="py-2 pr-4">Tanggal</th>
                                    <th class="py-2 pr-4">Tipe</th>
                                    <th class="py-2 pr-4">Poin</th>
                                    <th class="py-2 pr-4">Order</th>
                                    <th class="py-2 pr-4">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tx in transactions" :key="tx.id" class="border-b last:border-0">
                                    <td class="py-2 pr-4">{{ formatDate(tx.created_at) }}</td>
                                    <td class="py-2 pr-4 capitalize">{{ tx.type }}</td>
                                    <td class="py-2 pr-4 font-medium" :class="tx.points >= 0 ? 'text-green-600' : 'text-red-600'">{{ tx.points > 0 ? '+' : '' }}{{ formatCurrency(tx.points) }}</td>
                                    <td class="py-2 pr-4">{{ tx.order?.order_number || '-' }}</td>
                                    <td class="py-2 pr-4 text-gray-600">{{ tx.description || '-' }}</td>
                                </tr>
                                <tr v-if="transactions.length === 0">
                                    <td colspan="5" class="py-4 text-center text-gray-500">Belum ada transaksi poin</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
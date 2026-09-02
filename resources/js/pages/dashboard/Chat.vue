<script setup>
import { onMounted, ref } from 'vue'
import api from '../../services/api'

const chats = ref([])
const loading = ref(true)
const errorMessage = ref('')

const fetchChats = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/chats')
        chats.value = response.data?.data || []
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Gagal mengambil data chat.'
    } finally {
        loading.value = false
    }
}

const formatDate = (date) => {
    if (!date) return '—'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date))
}

onMounted(fetchChats)
</script>

<template>
    <section>
        <!-- Header -->
        <div class="mb-10">
            <p
                class="text-[9px] font-semibold uppercase tracking-[0.35em] text-[#191919]/30"
            >
                Engagement / Chat
            </p>

            <h2
                class="mt-3 text-4xl font-medium tracking-[-0.06em] sm:text-5xl"
            >
                CHAT<span class="text-[#E85D75]">.</span>
            </h2>

            <p class="mt-4 max-w-xl text-sm leading-6 text-[#191919]/40">
                Manage conversations and communicate with Teras Memori
                customers.
            </p>
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
            <div class="flex min-h-[250px] items-center justify-center">
                <div class="text-center">
                    <div
                        class="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-[#191919]/10 border-t-[#E85D75]"
                    ></div>

                    <p
                        class="mt-5 text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Loading conversations
                    </p>
                </div>
            </div>
        </div>

        <!-- Chat list -->
        <div
            v-else-if="chats.length"
            class="overflow-hidden border border-[#191919]/10 bg-white"
        >
            <div
                class="hidden grid-cols-[1.2fr_1fr_1fr_120px] border-b border-[#191919]/10 px-7 py-4 text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30 md:grid"
            >
                <span>Customer</span>
                <span>Messages</span>
                <span>Last activity</span>
                <span>Status</span>
            </div>

            <div
                v-for="chat in chats"
                :key="chat.id"
                class="grid gap-4 border-b border-[#191919]/10 px-6 py-6 last:border-b-0 md:grid-cols-[1.2fr_1fr_1fr_120px] md:items-center md:px-7"
            >
                <!-- Customer -->
                <div>
                    <p class="text-sm font-semibold">
                        {{ chat.customer?.name || 'Customer' }}
                    </p>

                    <p
                        class="mt-1 text-[9px] uppercase tracking-[0.2em] text-[#191919]/30"
                    >
                        Chat #{{ chat.id }}
                    </p>
                </div>

                <!-- Messages -->
                <div>
                    <span
                        class="text-sm text-[#191919]/50"
                    >
                        {{ chat.chat_messages?.length || chat.messages_count || 0 }}
                        messages
                    </span>
                </div>

                <!-- Date -->
                <div>
                    <span class="text-sm text-[#191919]/50">
                        {{ formatDate(chat.updated_at) }}
                    </span>
                </div>

                <!-- Status -->
                <div>
                    <span
                        class="inline-flex items-center gap-2 text-[9px] font-semibold uppercase tracking-[0.2em]"
                        :class="
                            chat.status === 'open'
                                ? 'text-[#E85D75]'
                                : 'text-[#191919]/30'
                        "
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full"
                            :class="
                                chat.status === 'open'
                                    ? 'bg-[#E85D75]'
                                    : 'bg-[#191919]/20'
                            "
                        ></span>

                        {{ chat.status || 'unknown' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div
            v-else
            class="border border-[#191919]/10 bg-white p-10"
        >
            <div
                class="flex min-h-[250px] items-center justify-center text-center"
            >
                <div>
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-[#E85D75]/20 bg-[#F4A6B8]/10"
                    >
                        <span class="text-xl text-[#E85D75]">◌</span>
                    </div>

                    <h3 class="mt-6 text-lg font-medium">
                        No conversations yet
                    </h3>

                    <p class="mt-2 text-sm text-[#191919]/35">
                        Customer conversations will appear here.
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
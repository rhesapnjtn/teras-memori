```vue
<script setup>
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
} from 'vue'
import api from '../../services/api'
import echo from '../../echo'

const chats = ref([])
const selectedChat = ref(null)

const loading = ref(true)
const loadingDetail = ref(false)
const sending = ref(false)
const closing = ref(false)

const errorMessage = ref('')
const successMessage = ref('')

const searchQuery = ref('')
const statusFilter = ref('all')
const messageInput = ref('')

const messagesContainer = ref(null)

/*
|--------------------------------------------------------------------------
| Realtime
|--------------------------------------------------------------------------
|
| Dashboard Admin menggunakan PRIVATE CHANNEL.
|
| Channel:
| private-admin-chat.{chat_id}
|
| Customer tetap menggunakan:
| chat.{public_token}
|
|--------------------------------------------------------------------------
*/

const subscribedChatIds = new Set()

const subscribeToChat = (chatId) => {
    if (!chatId) return

    if (subscribedChatIds.has(chatId)) {
        return
    }

    subscribedChatIds.add(chatId)

    echo
        .private(`admin-chat.${chatId}`)
        .listen('.message.sent', async (event) => {
            console.log(
                `Realtime admin message received for chat ${chatId}:`,
                event
            )

            const newMessage = event?.message

            if (!newMessage) return

            /*
            |--------------------------------------------------------------------------
            | Update conversation list
            |--------------------------------------------------------------------------
            */

            const chatIndex = chats.value.findIndex(
                (chat) =>
                    Number(chat.id) === Number(chatId)
            )

            if (chatIndex !== -1) {
                const chatItem = chats.value[chatIndex]

                const currentCount =
                    Number(chatItem.messages_count) || 0

                /*
                |--------------------------------------------------------------------------
                | Hindari count double
                |--------------------------------------------------------------------------
                */

                const alreadyExistsInSelected =
                    selectedChat.value?.messages?.some(
                        (message) =>
                            Number(message.id) ===
                            Number(newMessage.id)
                    )

                if (!alreadyExistsInSelected) {
                    chatItem.messages_count =
                        currentCount + 1
                }

                chatItem.updated_at =
                    newMessage.created_at ||
                    new Date().toISOString()

                /*
                |--------------------------------------------------------------------------
                | Pindahkan chat terbaru ke atas
                |--------------------------------------------------------------------------
                */

                const updatedChat =
                    chats.value.splice(chatIndex, 1)[0]

                chats.value.unshift(updatedChat)
            }

            /*
            |--------------------------------------------------------------------------
            | Update selected chat
            |--------------------------------------------------------------------------
            */

            if (
                selectedChat.value &&
                Number(selectedChat.value.id) ===
                    Number(chatId)
            ) {
                if (!selectedChat.value.messages) {
                    selectedChat.value.messages = []
                }

                /*
                |--------------------------------------------------------------------------
                | Hindari duplicate message
                |--------------------------------------------------------------------------
                */

                const exists =
                    selectedChat.value.messages.some(
                        (message) =>
                            Number(message.id) ===
                            Number(newMessage.id)
                    )

                if (!exists) {
                    selectedChat.value.messages.push(
                        newMessage
                    )

                    await nextTick()

                    scrollToBottom()
                }
            }
        })
}

/*
|--------------------------------------------------------------------------
| Unsubscribe
|--------------------------------------------------------------------------
*/

const unsubscribeFromChat = (chatId) => {
    if (!chatId) return

    echo.leave(
        `private-admin-chat.${chatId}`
    )

    subscribedChatIds.delete(chatId)
}

/*
|--------------------------------------------------------------------------
| Subscribe semua chat
|--------------------------------------------------------------------------
*/

const subscribeToAllChats = () => {
    chats.value.forEach((chat) => {
        if (chat?.id) {
            subscribeToChat(chat.id)
        }
    })
}

/*
|--------------------------------------------------------------------------
| Fetch conversations
|--------------------------------------------------------------------------
*/

const fetchChats = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/admin/chats')

        chats.value = response.data?.data || []

        /*
        |--------------------------------------------------------------------------
        | Subscribe realtime setiap conversation
        |--------------------------------------------------------------------------
        */

        subscribeToAllChats()
    } catch (error) {
        console.error(
            'Failed to load chats:',
            error
        )

        errorMessage.value =
            error.response?.data?.message ||
            'Gagal mengambil data chat.'
    } finally {
        loading.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const filteredChats = computed(() => {
    const query =
        searchQuery.value.trim().toLowerCase()

    return chats.value.filter((chat) => {
        const customerName =
            chat.customer?.name?.toLowerCase() || ''

        const customerEmail =
            chat.customer?.email?.toLowerCase() || ''

        const matchesSearch =
            !query ||
            customerName.includes(query) ||
            customerEmail.includes(query) ||
            String(chat.id).includes(query)

        const matchesStatus =
            statusFilter.value === 'all' ||
            chat.status === statusFilter.value

        return (
            matchesSearch &&
            matchesStatus
        )
    })
})

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const openChats = computed(() => {
    return chats.value.filter(
        (chat) => chat.status === 'open'
    ).length
})

const closedChats = computed(() => {
    return chats.value.filter(
        (chat) => chat.status === 'closed'
    ).length
})

/*
|--------------------------------------------------------------------------
| Select conversation
|--------------------------------------------------------------------------
*/

const selectChat = async (chat) => {
    if (!chat?.id) return

    selectedChat.value = null
    loadingDetail.value = true
    errorMessage.value = ''
    successMessage.value = ''

    try {
        const response = await api.get(
            `/admin/chats/${chat.id}`
        )

        selectedChat.value =
            response.data?.data || null

        /*
        |--------------------------------------------------------------------------
        | Pastikan private channel chat aktif
        |--------------------------------------------------------------------------
        */

        if (selectedChat.value?.id) {
            subscribeToChat(
                selectedChat.value.id
            )
        }

        await nextTick()

        scrollToBottom()
    } catch (error) {
        console.error(
            'Failed to load chat:',
            error
        )

        errorMessage.value =
            error.response?.data?.message ||
            'Gagal mengambil detail chat.'
    } finally {
        loadingDetail.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Refresh selected chat
|--------------------------------------------------------------------------
*/

const refreshSelectedChat = async () => {
    if (!selectedChat.value?.id) return

    await selectChat({
        id: selectedChat.value.id,
    })

    await fetchChats()
}

/*
|--------------------------------------------------------------------------
| Send message
|--------------------------------------------------------------------------
*/

const sendMessage = async () => {
    const message =
        messageInput.value.trim()

    if (!message) return
    if (!selectedChat.value?.id) return

    if (
        selectedChat.value.status ===
        'closed'
    ) {
        return
    }

    if (sending.value) return

    sending.value = true
    errorMessage.value = ''
    successMessage.value = ''

    try {
        const response = await api.post(
            `/admin/chats/${selectedChat.value.id}/messages`,
            {
                message,
            }
        )

        selectedChat.value =
            response.data?.data ||
            selectedChat.value

        /*
        |--------------------------------------------------------------------------
        | Pastikan private channel tetap aktif
        |--------------------------------------------------------------------------
        */

        if (selectedChat.value?.id) {
            subscribeToChat(
                selectedChat.value.id
            )
        }

        messageInput.value = ''

        successMessage.value =
            'Pesan berhasil dikirim.'

        await nextTick()

        scrollToBottom()
    } catch (error) {
        console.error(
            'Failed to send message:',
            error
        )

        errorMessage.value =
            error.response?.data?.message ||
            error.response?.data?.errors?.message?.[0] ||
            'Gagal mengirim pesan.'
    } finally {
        sending.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Keyboard
|--------------------------------------------------------------------------
*/

const handleMessageKeydown = (event) => {
    if (
        event.key === 'Enter' &&
        !event.shiftKey
    ) {
        event.preventDefault()

        sendMessage()
    }
}

/*
|--------------------------------------------------------------------------
| Close chat
|--------------------------------------------------------------------------
*/

const closeChat = async () => {
    if (!selectedChat.value?.id) return

    if (
        selectedChat.value.status ===
        'closed'
    ) {
        return
    }

    if (closing.value) return

    const confirmed = window.confirm(
        'Yakin ingin menutup percakapan ini?'
    )

    if (!confirmed) return

    closing.value = true
    errorMessage.value = ''
    successMessage.value = ''

    try {
        await api.patch(
            `/admin/chats/${selectedChat.value.id}/close`
        )

        selectedChat.value.status =
            'closed'

        /*
        |--------------------------------------------------------------------------
        | Update list tanpa refresh halaman
        |--------------------------------------------------------------------------
        */

        const chatIndex =
            chats.value.findIndex(
                (chat) =>
                    Number(chat.id) ===
                    Number(
                        selectedChat.value.id
                    )
            )

        if (chatIndex !== -1) {
            chats.value[chatIndex].status =
                'closed'
        }

        successMessage.value =
            'Chat berhasil ditutup.'
    } catch (error) {
        console.error(
            'Failed to close chat:',
            error
        )

        errorMessage.value =
            error.response?.data?.message ||
            'Gagal menutup chat.'
    } finally {
        closing.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Date / time
|--------------------------------------------------------------------------
*/

const formatDate = (date) => {
    if (!date) return '—'

    const parsed = new Date(date)

    if (
        Number.isNaN(
            parsed.getTime()
        )
    ) {
        return '—'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(parsed)
}

const formatTime = (date) => {
    if (!date) return ''

    const parsed = new Date(date)

    if (
        Number.isNaN(
            parsed.getTime()
        )
    ) {
        return ''
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(parsed)
}

/*
|--------------------------------------------------------------------------
| Message helpers
|--------------------------------------------------------------------------
*/

const getMessages = computed(() => {
    return selectedChat.value?.messages || []
})

const getMessageSender = (message) => {
    if (
        message?.sender_type ===
        'admin'
    ) {
        return 'You'
    }

    return (
        message?.user?.name ||
        selectedChat.value?.customer?.name ||
        'Customer'
    )
}

/*
|--------------------------------------------------------------------------
| Scroll
|--------------------------------------------------------------------------
*/

const scrollToBottom = async () => {
    await nextTick()

    if (!messagesContainer.value) {
        return
    }

    messagesContainer.value.scrollTop =
        messagesContainer.value.scrollHeight
}

/*
|--------------------------------------------------------------------------
| Clear alerts
|--------------------------------------------------------------------------
*/

const clearAlerts = () => {
    errorMessage.value = ''
    successMessage.value = ''
}

/*
|--------------------------------------------------------------------------
| Close detail
|--------------------------------------------------------------------------
*/

const clearSelectedChat = () => {
    selectedChat.value = null
    messageInput.value = ''
    clearAlerts()
}

/*
|--------------------------------------------------------------------------
| Escape
|--------------------------------------------------------------------------
*/

const handleEscape = (event) => {
    if (event.key === 'Escape') {
        if (selectedChat.value) {
            clearSelectedChat()
        }
    }
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    fetchChats()

    window.addEventListener(
        'keydown',
        handleEscape
    )
})

onUnmounted(() => {
    window.removeEventListener(
        'keydown',
        handleEscape
    )

    /*
    |--------------------------------------------------------------------------
    | Leave semua private channel
    |--------------------------------------------------------------------------
    */

    subscribedChatIds.forEach(
        (chatId) => {
            echo.leave(
                `private-admin-chat.${chatId}`
            )
        }
    )

    subscribedChatIds.clear()
})
</script>
```




<template>
    <section class="min-w-0">
        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="mb-10">
            <p
                class="text-[9px] font-semibold uppercase tracking-[0.35em] text-[#191919]/30"
            >
                Engagement / Chat
            </p>

            <div
                class="mt-3 flex flex-col justify-between gap-5 lg:flex-row lg:items-end"
            >
                <div>
                    <h2
                        class="text-4xl font-medium tracking-[-0.06em] sm:text-5xl"
                    >
                        CHAT<span class="text-[#E85D75]">.</span>
                    </h2>

                    <p
                        class="mt-4 max-w-xl text-sm leading-6 text-[#191919]/40"
                    >
                        Manage conversations and communicate
                        with Teras Memori customers.
                    </p>
                </div>

                <!-- Stats -->

                <div
                    class="grid grid-cols-2 border border-[#191919]/10 bg-white sm:grid-cols-3"
                >
                    <div
                        class="border-r border-[#191919]/10 px-5 py-4"
                    >
                        <p
                            class="text-[8px] uppercase tracking-[0.25em] text-[#191919]/30"
                        >
                            Total
                        </p>

                        <p
                            class="mt-2 text-xl font-medium"
                        >
                            {{ chats.length }}
                        </p>
                    </div>

                    <div
                        class="border-r border-[#191919]/10 px-5 py-4"
                    >
                        <p
                            class="text-[8px] uppercase tracking-[0.25em] text-[#191919]/30"
                        >
                            Open
                        </p>

                        <p
                            class="mt-2 text-xl font-medium text-[#E85D75]"
                        >
                            {{ openChats }}
                        </p>
                    </div>

                    <div
                        class="hidden px-5 py-4 sm:block"
                    >
                        <p
                            class="text-[8px] uppercase tracking-[0.25em] text-[#191919]/30"
                        >
                            Closed
                        </p>

                        <p
                            class="mt-2 text-xl font-medium text-[#191919]/40"
                        >
                            {{ closedChats }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- =====================================================
             ALERTS
        ====================================================== -->

        <div
            v-if="errorMessage"
            class="mb-4 flex items-start justify-between gap-4 border-l-2 border-red-400 bg-red-50 px-5 py-4"
        >
            <p class="text-xs leading-5 text-red-600">
                {{ errorMessage }}
            </p>

            <button
                type="button"
                class="text-xs text-red-400 hover:text-red-600"
                @click="errorMessage = ''"
            >
                ×
            </button>
        </div>

        <div
            v-if="successMessage"
            class="mb-4 flex items-start justify-between gap-4 border-l-2 border-[#E85D75] bg-[#FFF0F3] px-5 py-4"
        >
            <p class="text-xs leading-5 text-[#C6475E]">
                {{ successMessage }}
            </p>

            <button
                type="button"
                class="text-xs text-[#E85D75] hover:text-[#C6475E]"
                @click="successMessage = ''"
            >
                ×
            </button>
        </div>

        <!-- =====================================================
             FILTER BAR
        ====================================================== -->

        <div
            class="mb-5 flex flex-col gap-3 border border-[#191919]/10 bg-white p-4 sm:flex-row"
        >
            <!-- Search -->

            <div class="relative flex-1">
                <input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Search customer..."
                    class="w-full border border-[#191919]/10 bg-[#FFF8FA] px-4 py-3 pr-10 text-sm outline-none transition placeholder:text-[#191919]/25 focus:border-[#E85D75]/50"
                />

                <span
                    class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[#191919]/25"
                >
                    ⌕
                </span>
            </div>

            <!-- Status -->

            <select
                v-model="statusFilter"
                class="border border-[#191919]/10 bg-[#FFF8FA] px-4 py-3 text-xs uppercase tracking-[0.15em] text-[#191919]/50 outline-none transition focus:border-[#E85D75]/50"
            >
                <option value="all">
                    All status
                </option>

                <option value="open">
                    Open
                </option>

                <option value="closed">
                    Closed
                </option>
            </select>

            <!-- Refresh -->

            <button
                type="button"
                @click="fetchChats"
                :disabled="loading"
                class="border border-[#191919]/10 px-5 py-3 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/50 transition hover:border-[#E85D75]/40 hover:text-[#E85D75] disabled:cursor-not-allowed disabled:opacity-40"
            >
                Refresh
            </button>
        </div>

        <!-- =====================================================
             MAIN CHAT AREA
        ====================================================== -->

        <div
            class="overflow-hidden border border-[#191919]/10 bg-white"
        >
            <!-- Loading -->

            <div
                v-if="loading"
                class="flex min-h-[550px] items-center justify-center"
            >
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

            <!-- Chat Interface -->

            <div
                v-else
                class="grid min-h-[650px] lg:grid-cols-[320px_1fr]"
            >
                <!-- =================================================
                     CONVERSATION LIST
                ================================================== -->

                <aside
                    class="border-b border-[#191919]/10 lg:border-b-0 lg:border-r"
                >
                    <div
                        class="flex items-center justify-between border-b border-[#191919]/10 px-5 py-4"
                    >
                        <div>
                            <p
                                class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                            >
                                Conversations
                            </p>

                            <p
                                class="mt-1 text-xs text-[#191919]/40"
                            >
                                {{ filteredChats.length }}
                                conversations
                            </p>
                        </div>

                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-[#FFF0F3] text-[10px] text-[#E85D75]"
                        >
                            {{ openChats }}
                        </span>
                    </div>

                    <!-- Empty filtered -->

                    <div
                        v-if="filteredChats.length === 0"
                        class="flex min-h-[250px] items-center justify-center px-6 text-center"
                    >
                        <div>
                            <div
                                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-[#E85D75]/20 bg-[#FFF0F3]"
                            >
                                <span
                                    class="text-lg text-[#E85D75]"
                                >
                                    ◌
                                </span>
                            </div>

                            <p
                                class="mt-5 text-sm font-medium"
                            >
                                No conversations
                            </p>

                            <p
                                class="mt-2 text-xs leading-5 text-[#191919]/30"
                            >
                                Try another search or filter.
                            </p>
                        </div>
                    </div>

                    <!-- Conversations -->

                    <div
                        v-else
                        class="max-h-[590px] overflow-y-auto"
                    >
                        <button
                            v-for="chat in filteredChats"
                            :key="chat.id"
                            type="button"
                            @click="selectChat(chat)"
                            class="group w-full border-b border-[#191919]/10 px-5 py-5 text-left transition last:border-b-0 hover:bg-[#FFF8FA]"
                            :class="
                                selectedChat?.id === chat.id
                                    ? 'bg-[#FFF0F3]'
                                    : ''
                            "
                        >
                            <div
                                class="flex items-start justify-between gap-4"
                            >
                                <div class="min-w-0">
                                    <div
                                        class="flex items-center gap-2"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 shrink-0 rounded-full"
                                            :class="
                                                chat.status === 'open'
                                                    ? 'bg-[#E85D75]'
                                                    : 'bg-[#191919]/15'
                                            "
                                        ></span>

                                        <p
                                            class="truncate text-sm font-semibold"
                                        >
                                            {{
                                                chat.customer?.name ||
                                                'Customer'
                                            }}
                                        </p>
                                    </div>

                                    <p
                                        class="mt-2 truncate text-[9px] uppercase tracking-[0.18em] text-[#191919]/30"
                                    >
                                        {{
                                            chat.customer?.email ||
                                            'No email'
                                        }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 text-[8px] uppercase tracking-[0.15em] text-[#191919]/25"
                                >
                                    #{{ chat.id }}
                                </span>
                            </div>

                            <div
                                class="mt-4 flex items-center justify-between"
                            >
                                <span
                                    class="text-[10px] text-[#191919]/35"
                                >
                                    {{
                                        chat.messages_count ??
                                        chat.messages?.length ??
                                        0
                                    }}
                                    messages
                                </span>

                                <span
                                    class="text-[9px] text-[#191919]/25"
                                >
                                    {{ formatDate(chat.updated_at) }}
                                </span>
                            </div>
                        </button>
                    </div>
                </aside>

                <!-- =================================================
                     CHAT DETAIL
                ================================================== -->

                <main class="relative flex min-w-0 flex-col">
                    <!-- No selected chat -->

                    <div
                        v-if="!selectedChat && !loadingDetail"
                        class="flex min-h-[650px] flex-1 items-center justify-center px-6 text-center"
                    >
                        <div class="max-w-sm">
                            <div
                                class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-[#E85D75]/20 bg-[#FFF0F3]"
                            >
                                <span
                                    class="text-2xl text-[#E85D75]"
                                >
                                    ◌
                                </span>
                            </div>

                            <h3
                                class="mt-7 text-xl font-medium tracking-[-0.03em]"
                            >
                                Select a conversation
                            </h3>

                            <p
                                class="mt-3 text-sm leading-6 text-[#191919]/35"
                            >
                                Choose a customer from the
                                conversation list to view and
                                reply to their messages.
                            </p>
                        </div>
                    </div>

                    <!-- Detail loading -->

                    <div
                        v-else-if="loadingDetail"
                        class="flex min-h-[650px] flex-1 items-center justify-center"
                    >
                        <div class="text-center">
                            <div
                                class="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-[#191919]/10 border-t-[#E85D75]"
                            ></div>

                            <p
                                class="mt-5 text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                            >
                                Loading conversation
                            </p>
                        </div>
                    </div>

                    <!-- Chat -->

                    <template v-else>
                        <!-- Chat header -->

                        <div
                            class="flex items-center justify-between gap-4 border-b border-[#191919]/10 px-5 py-5 sm:px-7"
                        >
                            <div class="flex min-w-0 items-center gap-4">
                                <!-- Mobile close -->

                                <button
                                    type="button"
                                    @click="clearSelectedChat"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-[#191919]/10 text-[#191919]/40 transition hover:border-[#E85D75]/40 hover:text-[#E85D75] lg:hidden"
                                    aria-label="Back"
                                >
                                    ←
                                </button>

                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#191919] text-xs font-medium text-white"
                                >
                                    {{
                                        (
                                            selectedChat.customer?.name ||
                                            'C'
                                        )
                                            .charAt(0)
                                            .toUpperCase()
                                    }}
                                </div>

                                <div class="min-w-0">
                                    <div
                                        class="flex items-center gap-3"
                                    >
                                        <h3
                                            class="truncate text-sm font-semibold"
                                        >
                                            {{
                                                selectedChat.customer?.name ||
                                                'Customer'
                                            }}
                                        </h3>

                                        <span
                                            class="inline-flex items-center gap-1.5 text-[8px] font-semibold uppercase tracking-[0.2em]"
                                            :class="
                                                selectedChat.status ===
                                                'open'
                                                    ? 'text-[#E85D75]'
                                                    : 'text-[#191919]/30'
                                            "
                                        >
                                            <span
                                                class="h-1.5 w-1.5 rounded-full"
                                                :class="
                                                    selectedChat.status ===
                                                    'open'
                                                        ? 'bg-[#E85D75]'
                                                        : 'bg-[#191919]/20'
                                                "
                                            ></span>

                                            {{
                                                selectedChat.status ||
                                                'unknown'
                                            }}
                                        </span>
                                    </div>

                                    <p
                                        class="mt-1 truncate text-[9px] text-[#191919]/30"
                                    >
                                        {{
                                            selectedChat.customer?.email ||
                                            'No email'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex shrink-0 items-center gap-2"
                            >
                                <button
                                    type="button"
                                    @click="refreshSelectedChat"
                                    :disabled="
                                        loadingDetail ||
                                        sending ||
                                        closing
                                    "
                                    class="flex h-9 w-9 items-center justify-center rounded-full border border-[#191919]/10 text-sm text-[#191919]/40 transition hover:border-[#E85D75]/40 hover:text-[#E85D75] disabled:cursor-not-allowed disabled:opacity-30"
                                    aria-label="Refresh"
                                    title="Refresh"
                                >
                                    ↻
                                </button>

                                <button
                                    v-if="
                                        selectedChat.status ===
                                        'open'
                                    "
                                    type="button"
                                    @click="closeChat"
                                    :disabled="
                                        closing ||
                                        sending
                                    "
                                    class="hidden rounded-full border border-[#191919]/10 px-4 py-2 text-[8px] font-semibold uppercase tracking-[0.18em] text-[#191919]/45 transition hover:border-red-300 hover:text-red-500 disabled:cursor-not-allowed disabled:opacity-40 sm:block"
                                >
                                    {{
                                        closing
                                            ? 'Closing...'
                                            : 'Close chat'
                                    }}
                                </button>
                            </div>
                        </div>

                        <!-- Mobile close -->

                        <div
                            v-if="
                                selectedChat.status ===
                                'open'
                            "
                            class="border-b border-[#191919]/10 px-5 py-3 sm:hidden"
                        >
                            <button
                                type="button"
                                @click="closeChat"
                                :disabled="
                                    closing ||
                                    sending
                                "
                                class="w-full border border-[#191919]/10 py-2.5 text-[8px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45 transition hover:border-red-300 hover:text-red-500 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                {{
                                    closing
                                        ? 'Closing...'
                                        : 'Close chat'
                                }}
                            </button>
                        </div>

                        <!-- Messages -->

                        <div
                            ref="messagesContainer"
                            class="flex-1 space-y-5 overflow-y-auto bg-[#FFF8FA] px-4 py-6 sm:px-7"
                        >
                            <!-- Empty messages -->

                            <div
                                v-if="getMessages.length === 0"
                                class="flex min-h-[420px] items-center justify-center text-center"
                            >
                                <div>
                                    <p
                                        class="text-sm text-[#191919]/35"
                                    >
                                        No messages yet.
                                    </p>

                                    <p
                                        class="mt-2 text-xs text-[#191919]/25"
                                    >
                                        Start the conversation below.
                                    </p>
                                </div>
                            </div>

                            <!-- Messages -->

                            <div
                                v-for="message in getMessages"
                                :key="message.id"
                                class="flex"
                                :class="
                                    message.sender_type ===
                                    'admin'
                                        ? 'justify-end'
                                        : 'justify-start'
                                "
                            >
                                <div
                                    class="max-w-[85%] sm:max-w-[70%]"
                                >
                                    <div
                                        class="mb-1 flex items-center gap-2"
                                        :class="
                                            message.sender_type ===
                                            'admin'
                                                ? 'justify-end'
                                                : 'justify-start'
                                        "
                                    >
                                        <span
                                            class="text-[8px] font-semibold uppercase tracking-[0.18em] text-[#191919]/25"
                                        >
                                            {{
                                                getMessageSender(
                                                    message
                                                )
                                            }}
                                        </span>

                                        <span
                                            class="text-[8px] text-[#191919]/20"
                                        >
                                            {{
                                                formatTime(
                                                    message.created_at
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        class="px-4 py-3 text-sm leading-6"
                                        :class="
                                            message.sender_type ===
                                            'admin'
                                                ? 'rounded-2xl rounded-br-sm bg-[#191919] text-white'
                                                : 'rounded-2xl rounded-bl-sm border border-[#191919]/10 bg-white text-[#191919]/70'
                                        "
                                    >
                                        {{
                                            message.message
                                        }}
                                    </div>

                                    <p
                                        v-if="
                                            message.read_at &&
                                            message.sender_type ===
                                                'admin'
                                        "
                                        class="mt-1 text-right text-[8px] text-[#191919]/20"
                                    >
                                        Read
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Composer -->

                        <div
                            class="border-t border-[#191919]/10 bg-white p-4 sm:p-5"
                        >
                            <!-- Closed -->

                            <div
                                v-if="
                                    selectedChat.status ===
                                    'closed'
                                "
                                class="flex items-center justify-between gap-4 bg-[#F4F0F1] px-4 py-4"
                            >
                                <div>
                                    <p
                                        class="text-xs font-medium text-[#191919]/60"
                                    >
                                        Conversation closed
                                    </p>

                                    <p
                                        class="mt-1 text-[10px] text-[#191919]/30"
                                    >
                                        This chat can no longer
                                        receive replies.
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 text-[8px] font-semibold uppercase tracking-[0.2em] text-[#191919]/25"
                                >
                                    Closed
                                </span>
                            </div>

                            <!-- Open -->

                            <div v-else>
                                <div
                                    class="flex items-end gap-3"
                                >
                                    <textarea
                                        v-model="messageInput"
                                        @keydown="
                                            handleMessageKeydown
                                        "
                                        rows="2"
                                        maxlength="5000"
                                        :disabled="sending"
                                        placeholder="Write a message..."
                                        class="min-h-[72px] flex-1 resize-none border border-[#191919]/10 bg-[#FFF8FA] px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-[#191919]/25 focus:border-[#E85D75]/50 disabled:cursor-not-allowed disabled:opacity-50"
                                    ></textarea>

                                    <button
                                        type="button"
                                        @click="sendMessage"
                                        :disabled="
                                            sending ||
                                            !messageInput.trim()
                                        "
                                        class="flex h-[72px] shrink-0 items-center gap-3 bg-[#E85D75] px-5 text-[9px] font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-[#D94F67] disabled:cursor-not-allowed disabled:opacity-40"
                                    >
                                        <span>
                                            {{
                                                sending
                                                    ? 'Sending'
                                                    : 'Send'
                                            }}
                                        </span>

                                        <span
                                            v-if="!sending"
                                            class="text-sm"
                                        >
                                            →
                                        </span>

                                        <span
                                            v-else
                                            class="h-3 w-3 animate-spin rounded-full border border-white/30 border-t-white"
                                        ></span>
                                    </button>
                                </div>

                                <div
                                    class="mt-2 flex items-center justify-between"
                                >
                                    <p
                                        class="text-[8px] uppercase tracking-[0.15em] text-[#191919]/20"
                                    >
                                        Enter to send · Shift + Enter
                                        for new line
                                    </p>

                                    <p
                                        class="text-[8px] text-[#191919]/20"
                                    >
                                        {{
                                            messageInput.length
                                        }}/5000
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </main>
            </div>
        </div>
    </section>
</template>
```

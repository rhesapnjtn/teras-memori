<script setup>
import {
    nextTick,
    onMounted,
    onUnmounted,
    ref,
} from 'vue'

import api from '../../services/api'
import echo from '../../echo'

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const email = ref(
    localStorage.getItem(
        'chat_customer_email'
    ) || ''
)

const orderNumber = ref(
    localStorage.getItem(
        'chat_order_number'
    ) || ''
)

const customer = ref(null)
const chat = ref(null)

const chatToken = ref(
    localStorage.getItem(
        'chat_public_token'
    ) || ''
)

const chatId = ref(
    localStorage.getItem(
        'chat_id'
    ) || ''
)

const message = ref('')

const loading = ref(false)
const sending = ref(false)

const errorMessage = ref('')

const started = ref(false)

const messagesContainer = ref(null)

/*
|--------------------------------------------------------------------------
| Realtime
|--------------------------------------------------------------------------
*/

let chatChannel = null
let subscribedChatToken = null

/*
|--------------------------------------------------------------------------
| Scroll
|--------------------------------------------------------------------------
*/

const scrollToBottom = async () => {
    await nextTick()

    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight
    }
}

/*
|--------------------------------------------------------------------------
| Save Chat Data
|--------------------------------------------------------------------------
*/

const saveChatData = (chatData) => {
    if (!chatData) {
        return
    }

    /*
    |--------------------------------------------------------------------------
    | Save Chat ID
    |--------------------------------------------------------------------------
    */

    if (chatData.id) {
        chatId.value = String(chatData.id)

        localStorage.setItem(
            'chat_id',
            String(chatData.id)
        )
    }

    /*
    |--------------------------------------------------------------------------
    | Save Customer
    |--------------------------------------------------------------------------
    */

    if (chatData.customer) {
        customer.value =
            chatData.customer

        if (chatData.customer.email) {
            email.value =
                chatData.customer.email

            localStorage.setItem(
                'chat_customer_email',
                chatData.customer.email
            )
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Save Public Token
    |--------------------------------------------------------------------------
    */

    if (chatData.public_token) {
        saveChatToken(
            chatData.public_token
        )
    }
}

/*
|--------------------------------------------------------------------------
| Save Token
|--------------------------------------------------------------------------
*/

const saveChatToken = (token) => {
    if (!token) {
        return
    }

    chatToken.value = token

    localStorage.setItem(
        'chat_public_token',
        token
    )
}

/*
|--------------------------------------------------------------------------
| Realtime
|--------------------------------------------------------------------------
*/

const subscribeToChat = (token) => {
    if (!token) {
        return
    }

    /*
    |--------------------------------------------------------------------------
    | Jangan subscribe ulang
    |--------------------------------------------------------------------------
    */

    if (
        subscribedChatToken === token
    ) {
        return
    }

    /*
    |--------------------------------------------------------------------------
    | Leave channel sebelumnya
    |--------------------------------------------------------------------------
    */

    if (subscribedChatToken) {
        echo.leave(
            `chat.${subscribedChatToken}`
        )
    }

    subscribedChatToken = token

    /*
    |--------------------------------------------------------------------------
    | Subscribe public channel
    |--------------------------------------------------------------------------
    |
    | Channel:
    |
    | chat.UUID_TOKEN
    |
    */

    chatChannel = echo
        .channel(`chat.${token}`)
        .listen(
            '.message.sent',
            async (event) => {
                console.log(
                    'Realtime message received:',
                    event
                )

                const newMessage =
                    event?.message

                if (!newMessage) {
                    return
                }

                if (!chat.value) {
                    return
                }

                /*
                |--------------------------------------------------------------------------
                | Cek duplicate
                |--------------------------------------------------------------------------
                */

                const exists =
                    chat.value.messages?.some(
                        (item) =>
                            Number(item.id) ===
                            Number(
                                newMessage.id
                            )
                    )

                if (exists) {
                    return
                }

                /*
                |--------------------------------------------------------------------------
                | Pastikan messages array tersedia
                |--------------------------------------------------------------------------
                */

                if (
                    !chat.value.messages
                ) {
                    chat.value.messages = []
                }

                /*
                |--------------------------------------------------------------------------
                | Tambahkan message
                |--------------------------------------------------------------------------
                */

                chat.value.messages.push(
                    newMessage
                )

                await scrollToBottom()
            }
        )
}

/*
|--------------------------------------------------------------------------
| Load Chat Using Saved Token
|--------------------------------------------------------------------------
|
| Digunakan ketika:
|
| - User refresh halaman
| - Token masih tersimpan
| - Chat ID masih tersimpan
|
| Tidak perlu meminta email + order
| karena token sudah menjadi credential chat.
|
|--------------------------------------------------------------------------
*/

const loadChatByToken = async () => {
    if (
        !chatId.value ||
        !chatToken.value
    ) {
        return false
    }

    loading.value = true
    errorMessage.value = ''

    try {
        const response =
            await api.get(
                `/chats/${chatId.value}`,
                {
                    headers: {
                        'X-Chat-Token':
                            chatToken.value,
                    },
                }
            )

        const chatData =
            response.data.data

        if (!chatData) {
            throw new Error(
                'Data chat tidak ditemukan.'
            )
        }

        chat.value = chatData

        saveChatData(chatData)

        /*
        |--------------------------------------------------------------------------
        | Subscribe Realtime
        |--------------------------------------------------------------------------
        */

        if (chatToken.value) {
            subscribeToChat(
                chatToken.value
            )
        }

        started.value = true

        await scrollToBottom()

        return true
    } catch (error) {
        console.error(
            'Load chat by token error:',
            error
        )

        /*
        |--------------------------------------------------------------------------
        | Token invalid / expired / chat tidak valid
        |--------------------------------------------------------------------------
        */

        if (
            error.response?.status ===
                403 ||
            error.response?.status === 404
        ) {
            localStorage.removeItem(
                'chat_public_token'
            )

            localStorage.removeItem(
                'chat_id'
            )

            chatToken.value = ''
            chatId.value = ''

            chat.value = null
            started.value = false

            errorMessage.value =
                'Sesi chat sudah tidak valid. Silakan masukkan kembali data pesanan Anda.'
        } else {
            errorMessage.value =
                error.response?.data
                    ?.message ||
                'Gagal memuat chat.'
        }

        return false
    } finally {
        loading.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Load Chat Using Email + Order Number
|--------------------------------------------------------------------------
|
| Digunakan ketika user belum mempunyai
| chat token.
|
|--------------------------------------------------------------------------
*/

const loadChatByCredentials = async () => {
    if (
        !email.value.trim() ||
        !orderNumber.value.trim()
    ) {
        return false
    }

    loading.value = true
    errorMessage.value = ''

    try {
        const response =
            await api.get(
                '/chats/customer',
                {
                    params: {
                        email:
                            email.value.trim(),

                        order_number:
                            orderNumber.value.trim(),
                    },
                }
            )

        customer.value =
            response.data.customer

        chat.value =
            response.data.data

        /*
        |--------------------------------------------------------------------------
        | Save credentials
        |--------------------------------------------------------------------------
        */

        if (
            customer.value?.email
        ) {
            email.value =
                customer.value.email

            localStorage.setItem(
                'chat_customer_email',
                customer.value.email
            )
        }

        localStorage.setItem(
            'chat_order_number',
            orderNumber.value.trim()
        )

        /*
        |--------------------------------------------------------------------------
        | Save chat data
        |--------------------------------------------------------------------------
        */

        if (chat.value) {
            saveChatData(
                chat.value
            )
        }

        /*
        |--------------------------------------------------------------------------
        | Token dari response
        |--------------------------------------------------------------------------
        */

        if (
            response.data.public_token
        ) {
            saveChatToken(
                response.data.public_token
            )
        }

        started.value = true

        /*
        |--------------------------------------------------------------------------
        | Subscribe Realtime
        |--------------------------------------------------------------------------
        */

        if (
            chat.value &&
            chatToken.value
        ) {
            subscribeToChat(
                chatToken.value
            )

            await scrollToBottom()
        }

        return true
    } catch (error) {
        console.error(
            'Load chat by credentials error:',
            error
        )

        if (
            error.response?.status ===
            404
        ) {
            return false
        }

        errorMessage.value =
            error.response?.data
                ?.message ||
            'Gagal memuat chat.'

        return false
    } finally {
        loading.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Create Chat
|--------------------------------------------------------------------------
|
| Membuat / mengambil chat menggunakan:
|
| - Email
| - Order Number
|
|--------------------------------------------------------------------------
*/

const createChat = async () => {
    if (!email.value.trim()) {
        errorMessage.value =
            'Masukkan email terlebih dahulu.'

        return
    }

    if (!orderNumber.value.trim()) {
        errorMessage.value =
            'Masukkan nomor order terlebih dahulu.'

        return
    }

    loading.value = true
    errorMessage.value = ''

    try {
        const response =
            await api.post(
                '/chats',
                {
                    email:
                        email.value.trim(),

                    order_number:
                        orderNumber.value.trim(),
                }
            )

        chat.value =
            response.data.data

        /*
        |--------------------------------------------------------------------------
        | Save Customer
        |--------------------------------------------------------------------------
        */

        if (
            response.data.customer
        ) {
            customer.value =
                response.data.customer
        }

        /*
        |--------------------------------------------------------------------------
        | Save Chat Data
        |--------------------------------------------------------------------------
        */

        if (chat.value) {
            saveChatData(
                chat.value
            )
        }

        /*
        |--------------------------------------------------------------------------
        | Token dari response
        |--------------------------------------------------------------------------
        */

        if (
            response.data.public_token
        ) {
            saveChatToken(
                response.data.public_token
            )
        }

        /*
        |--------------------------------------------------------------------------
        | Token dari resource
        |--------------------------------------------------------------------------
        */

        if (
            chat.value?.public_token
        ) {
            saveChatToken(
                chat.value.public_token
            )
        }

        /*
        |--------------------------------------------------------------------------
        | Save Credentials
        |--------------------------------------------------------------------------
        */

        localStorage.setItem(
            'chat_customer_email',
            email.value.trim()
        )

        localStorage.setItem(
            'chat_order_number',
            orderNumber.value.trim()
        )

        started.value = true

        /*
        |--------------------------------------------------------------------------
        | Subscribe Realtime
        |--------------------------------------------------------------------------
        */

        if (chatToken.value) {
            subscribeToChat(
                chatToken.value
            )
        }

        await scrollToBottom()
    } catch (error) {
        console.error(
            'Create chat error:',
            error
        )

        errorMessage.value =
            error.response?.data
                ?.message ||
            'Gagal membuat percakapan.'
    } finally {
        loading.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Start / Continue Chat
|--------------------------------------------------------------------------
*/

const startChat = async () => {
    if (!email.value.trim()) {
        errorMessage.value =
            'Masukkan email terlebih dahulu.'

        return
    }

    if (!orderNumber.value.trim()) {
        errorMessage.value =
            'Masukkan nomor order terlebih dahulu.'

        return
    }

    loading.value = true
    errorMessage.value = ''

    try {
        /*
        |--------------------------------------------------------------------------
        | Coba cari chat yang sudah ada
        |--------------------------------------------------------------------------
        */

        const existingChat =
            await loadChatByCredentials()

        if (existingChat) {
            return
        }

        /*
        |--------------------------------------------------------------------------
        | Jika belum ada chat,
        | buat chat baru.
        |--------------------------------------------------------------------------
        */

        await createChat()
    } finally {
        loading.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Load Messages
|--------------------------------------------------------------------------
*/

const loadMessages = async () => {
    if (!chat.value?.id) {
        return
    }

    if (!chatToken.value) {
        errorMessage.value =
            'Chat token tidak ditemukan.'

        return
    }

    try {
        const response =
            await api.get(
                `/chats/${chat.value.id}`,
                {
                    headers: {
                        'X-Chat-Token':
                            chatToken.value,
                    },
                }
            )

        chat.value =
            response.data.data

        saveChatData(
            chat.value
        )

        /*
        |--------------------------------------------------------------------------
        | Subscribe Realtime
        |--------------------------------------------------------------------------
        */

        if (chatToken.value) {
            subscribeToChat(
                chatToken.value
            )
        }

        await scrollToBottom()
    } catch (error) {
        console.error(
            'Load messages error:',
            error
        )

        errorMessage.value =
            error.response?.data
                ?.message ||
            'Gagal memuat pesan.'
    }
}

/*
|--------------------------------------------------------------------------
| Send Message
|--------------------------------------------------------------------------
*/

const sendMessage = async () => {
    if (
        !message.value.trim() ||
        !chat.value?.id ||
        sending.value
    ) {
        return
    }

    if (!chatToken.value) {
        errorMessage.value =
            'Chat token tidak ditemukan.'

        return
    }

    sending.value = true
    errorMessage.value = ''

    const text =
        message.value.trim()

    try {
        const response =
            await api.post(
                `/chats/${chat.value.id}/messages`,
                {
                    message: text,
                },
                {
                    headers: {
                        'X-Chat-Token':
                            chatToken.value,
                    },
                }
            )

        chat.value =
            response.data.data

        /*
        |--------------------------------------------------------------------------
        | Save Chat Data
        |--------------------------------------------------------------------------
        */

        saveChatData(
            chat.value
        )

        /*
        |--------------------------------------------------------------------------
        | Pastikan realtime tetap aktif
        |--------------------------------------------------------------------------
        */

        if (chatToken.value) {
            subscribeToChat(
                chatToken.value
            )
        }

        message.value = ''

        await scrollToBottom()
    } catch (error) {
        console.error(
            'Send message error:',
            error
        )

        errorMessage.value =
            error.response?.data
                ?.message ||
            'Gagal mengirim pesan.'
    } finally {
        sending.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Keydown Handler
|--------------------------------------------------------------------------
*/

const handleKeydown = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault()
        sendMessage()
    }
}

/*
|--------------------------------------------------------------------------
| Format Time
|--------------------------------------------------------------------------
*/

const formatTime = (date) => {
    if (!date) {
        return ''
    }

    const parsed =
        new Date(date)

    if (
        Number.isNaN(
            parsed.getTime()
        )
    ) {
        return ''
    }

    return parsed.toLocaleTimeString(
        'id-ID',
        {
            hour: '2-digit',
            minute: '2-digit',
        }
    )
}

/*
|--------------------------------------------------------------------------
| Reset Chat
|--------------------------------------------------------------------------
*/

const resetChat = () => {
    /*
    |--------------------------------------------------------------------------
    | Leave realtime channel
    |--------------------------------------------------------------------------
    */

    if (subscribedChatToken) {
        echo.leave(
            `chat.${subscribedChatToken}`
        )
    }

    subscribedChatToken = null
    chatChannel = null

    /*
    |--------------------------------------------------------------------------
    | Clear local storage
    |--------------------------------------------------------------------------
    */

    localStorage.removeItem(
        'chat_customer_email'
    )

    localStorage.removeItem(
        'chat_order_number'
    )

    localStorage.removeItem(
        'chat_public_token'
    )

    localStorage.removeItem(
        'chat_id'
    )

    /*
    |--------------------------------------------------------------------------
    | Reset state
    |--------------------------------------------------------------------------
    */

    email.value = ''
    orderNumber.value = ''

    customer.value = null
    chat.value = null

    chatToken.value = ''
    chatId.value = ''

    started.value = false

    message.value = ''
    errorMessage.value = ''
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(async () => {
    /*
    |--------------------------------------------------------------------------
    | Jika token + chat ID masih tersedia,
    | langsung buka chat.
    |--------------------------------------------------------------------------
    */

    if (
        chatToken.value &&
        chatId.value
    ) {
        const loaded =
            await loadChatByToken()

        if (loaded) {
            return
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Kalau tidak ada token,
    | tampilkan form login chat.
    |--------------------------------------------------------------------------
    */

    started.value = false
})

onUnmounted(() => {
    if (subscribedChatToken) {
        echo.leave(
            `chat.${subscribedChatToken}`
        )
    }

    subscribedChatToken = null
    chatChannel = null
})
</script>


<template>
    <main
        class="min-h-screen bg-[#FFF8FA] px-6 py-16 text-[#191919] sm:px-10 md:py-24"
    >
        <div class="mx-auto max-w-5xl">

            <!-- =====================================================
                 HEADER
            ====================================================== -->

            <div class="mb-12">

                <div
                    class="mb-6 flex items-center gap-4"
                >
                    <span
                        class="h-px w-12 bg-[#E85D75]"
                    ></span>

                    <span
                        class="text-[10px] uppercase tracking-[0.4em] text-[#191919]/40"
                    >
                        Teras Memori / Support
                    </span>
                </div>

                <h1
                    class="max-w-4xl text-6xl font-medium leading-[0.85] tracking-[-0.07em] sm:text-7xl md:text-8xl"
                >
                    LET'S
                    <span
                        class="text-[#191919]/20"
                    >
                        TALK.
                    </span>
                </h1>

                <p
                    class="mt-8 max-w-xl text-sm leading-7 text-[#191919]/45 md:text-base"
                >
                    Punya pertanyaan mengenai jasa
                    editing, pesanan, atau proses
                    pengerjaan? Chat langsung
                    dengan tim Teras Memori.
                </p>

            </div>


            <!-- =====================================================
                 ERROR
            ====================================================== -->

            <div
                v-if="errorMessage"
                class="mb-6 border border-[#E85D75]/20 bg-[#E85D75]/5 px-5 py-4 text-sm text-[#E85D75]"
            >
                {{ errorMessage }}
            </div>


            <!-- =====================================================
                 CUSTOMER FORM
            ====================================================== -->

            <section
                v-if="!started"
                class="overflow-hidden rounded-[2rem] border border-[#191919]/10 bg-white"
            >

                <div
                    class="grid md:grid-cols-[0.8fr_1.2fr]"
                >

                    <!-- LEFT -->

                    <div
                        class="bg-[#191919] p-8 text-white sm:p-12"
                    >

                        <span
                            class="text-[9px] uppercase tracking-[0.35em] text-white/30"
                        >
                            Customer support
                        </span>

                        <h2
                            class="mt-6 text-4xl font-medium leading-[0.95] tracking-[-0.05em] sm:text-5xl"
                        >
                            How can we
                            <span
                                class="text-[#E85D75]"
                            >
                                help?
                            </span>
                        </h2>

                        <p
                            class="mt-8 text-sm leading-7 text-white/40"
                        >
                            Masukkan email dan nomor
                            order yang kamu gunakan
                            saat melakukan pemesanan.
                        </p>

                    </div>


                    <!-- RIGHT -->

                    <div
                        class="flex flex-col justify-center p-8 sm:p-12"
                    >

                        <!-- EMAIL -->

                        <label
                            class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/35"
                        >
                            Your email
                        </label>

                        <input
                            v-model="email"
                            type="email"
                            autocomplete="email"
                            placeholder="nama@email.com"
                            @keyup.enter="startChat"
                            class="mt-4 w-full border-b border-[#191919]/15 bg-transparent px-0 py-4 text-lg outline-none transition placeholder:text-[#191919]/20 focus:border-[#E85D75]"
                        />


                        <!-- ORDER NUMBER -->

                        <label
                            class="mt-8 text-[9px] uppercase tracking-[0.3em] text-[#191919]/35"
                        >
                            Order number
                        </label>

                        <input
                            v-model="orderNumber"
                            type="text"
                            autocomplete="off"
                            placeholder="TM-2026-0001"
                            @keyup.enter="startChat"
                            class="mt-4 w-full border-b border-[#191919]/15 bg-transparent px-0 py-4 text-lg uppercase outline-none transition placeholder:text-[#191919]/20 focus:border-[#E85D75]"
                        />


                        <!-- BUTTON -->

                        <button
                            type="button"
                            @click="startChat"
                            :disabled="loading"
                            class="mt-8 inline-flex items-center justify-center gap-4 rounded-full bg-[#191919] px-7 py-4 text-[10px] font-semibold uppercase tracking-[0.25em] text-white transition hover:bg-[#E85D75] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                loading
                                    ? 'Checking...'
                                    : 'Start Chat'
                            }}

                            <span>
                                →
                            </span>
                        </button>

                    </div>

                </div>

            </section>


            <!-- =====================================================
                 CHAT
            ====================================================== -->

            <section
                v-else
                class="overflow-hidden rounded-[2rem] border border-[#191919]/10 bg-white"
            >

                <!-- =================================================
                     CHAT HEADER
                ================================================== -->

                <div
                    class="flex flex-col gap-5 border-b border-[#191919]/10 px-6 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8"
                >

                    <div
                        class="flex items-center gap-4"
                    >

                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-full bg-[#E85D75]/10 text-[#E85D75]"
                        >
                            TM
                        </div>

                        <div>

                            <p
                                class="text-sm font-medium"
                            >
                                Teras Memori
                            </p>

                            <div
                                class="mt-1 flex items-center gap-2"
                            >

                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-green-500"
                                ></span>

                                <span
                                    class="text-[9px] uppercase tracking-[0.2em] text-[#191919]/30"
                                >
                                    Support
                                </span>

                            </div>

                        </div>

                    </div>


                    <div
                        class="flex items-center gap-4"
                    >

                        <span
                            class="max-w-[220px] truncate text-xs text-[#191919]/35"
                        >
                            {{ customer?.email }}
                        </span>

                        <button
                            type="button"
                            @click="resetChat"
                            class="text-[9px] uppercase tracking-[0.2em] text-[#191919]/30 transition hover:text-[#E85D75]"
                        >
                            Change
                        </button>

                    </div>

                </div>


                <!-- =================================================
                     MESSAGES
                ================================================== -->

                <div
                    ref="messagesContainer"
                    class="h-[500px] space-y-5 overflow-y-auto bg-[#FFF8FA]/60 px-5 py-8 sm:px-8"
                >

                    <!-- EMPTY -->

                    <div
                        v-if="
                            !chat?.messages?.length
                        "
                        class="flex h-full flex-col items-center justify-center text-center"
                    >

                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-full border border-[#191919]/10"
                        >
                            <span
                                class="text-xl text-[#E85D75]"
                            >
                                ✦
                            </span>
                        </div>

                        <p
                            class="mt-5 text-sm text-[#191919]/45"
                        >
                            Belum ada pesan.
                        </p>

                        <p
                            class="mt-2 text-xs text-[#191919]/25"
                        >
                            Kirim pesan pertama
                            untuk memulai
                            percakapan.
                        </p>

                    </div>


                    <!-- MESSAGE -->

                    <div
                        v-for="item in chat?.messages || []"
                        :key="item.id"
                        class="flex"
                        :class="
                            item.sender_type ===
                            'customer'
                                ? 'justify-end'
                                : 'justify-start'
                        "
                    >

                        <div
                            class="max-w-[85%] sm:max-w-[70%]"
                        >

                            <div
                                class="rounded-2xl px-5 py-3.5 text-sm leading-6"
                                :class="
                                    item.sender_type ===
                                    'customer'
                                        ? 'rounded-br-md bg-[#191919] text-white'
                                        : 'rounded-bl-md border border-[#191919]/10 bg-white text-[#191919]/70'
                                "
                            >
                                {{ item.message }}
                            </div>

                            <div
                                class="mt-2 text-[9px] uppercase tracking-[0.15em] text-[#191919]/25"
                                :class="
                                    item.sender_type ===
                                    'customer'
                                        ? 'text-right'
                                        : 'text-left'
                                "
                            >
                                {{
                                    formatTime(
                                        item.created_at
                                    )
                                }}
                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     CLOSED
                ================================================== -->

                <div
                    v-if="
                        chat?.status ===
                        'closed'
                    "
                    class="border-t border-[#191919]/10 bg-[#191919]/[0.02] px-6 py-5 text-center"
                >

                    <p
                        class="text-xs text-[#191919]/40"
                    >
                        Percakapan ini sudah
                        ditutup oleh admin.
                    </p>

                </div>


                <!-- =================================================
                     INPUT
                ================================================== -->

                <div
                    v-else
                    class="border-t border-[#191919]/10 p-5 sm:p-6"
                >

                    <div
                        class="flex items-end gap-3 rounded-2xl border border-[#191919]/10 bg-[#FFF8FA] p-2 pl-5 focus-within:border-[#E85D75]/40"
                    >

                        <textarea
                            v-model="message"
                            rows="1"
                            placeholder="Tulis pesan..."
                            @keydown="handleKeydown"
                            class="max-h-32 min-h-[44px] flex-1 resize-none bg-transparent py-2.5 text-sm outline-none placeholder:text-[#191919]/25"
                        ></textarea>

                        <button
                            type="button"
                            @click="sendMessage"
                            :disabled="
                                sending ||
                                !message.trim()
                            "
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#191919] text-white transition hover:bg-[#E85D75] disabled:cursor-not-allowed disabled:opacity-30"
                            aria-label="Send message"
                        >
                            →
                        </button>

                    </div>

                    <p
                        class="mt-3 text-center text-[9px] uppercase tracking-[0.2em] text-[#191919]/20"
                    >
                        Enter untuk kirim ·
                        Shift + Enter untuk baris baru
                    </p>

                </div>

            </section>

        </div>
    </main>
</template>

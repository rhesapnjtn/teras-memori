<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const isOpen = ref(false)
const isVisible = ref(false)
const unreadCount = ref(0)
let pulseTimeout = null

const chatPages = ['chat', 'order', 'services', 'portfolio']

const shouldShowWidget = () => {
  const publicRoutes = ['home', 'services', 'portfolio', 'about', 'order', 'track-order', 'order-success', 'chat', 'profile', 'member', 'order-history']
  return publicRoutes.includes(route.name)
}

const checkExistingChat = async () => {
  if (!auth.isAuthenticated) return
  
  try {
    const response = await fetch('/api/chats/customer', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json',
      },
    })
    
    if (response.ok) {
      const data = await response.json()
      if (data.data?.id) {
        unreadCount.value = data.data.messages?.filter(m => m.sender_type === 'admin' && !m.read_at).length || 0
      }
    }
  } catch (error) {
    console.debug('Chat widget: could not check existing chat')
  }
}

const openChat = () => {
  if (auth.isAuthenticated) {
    router.push({ name: 'chat' })
  } else {
    router.push({ name: 'login', query: { redirect: 'chat' } })
  }
  isOpen.value = false
}

const handlePulse = () => {
  if (unreadCount.value > 0 && !isOpen.value) {
    pulseTimeout = setTimeout(() => {
      isVisible.value = !isVisible.value
      handlePulse()
    }, 2000)
  } else {
    isVisible.value = true
  }
}

onMounted(() => {
  if (shouldShowWidget()) {
    checkExistingChat()
    handlePulse()
    
    const handleStorageChange = () => {
      checkExistingChat()
    }
    window.addEventListener('storage', handleStorageChange)
    
    return () => {
      window.removeEventListener('storage', handleStorageChange)
      if (pulseTimeout) clearTimeout(pulseTimeout)
    }
  }
})

onUnmounted(() => {
  if (pulseTimeout) clearTimeout(pulseTimeout)
})

watch(() => route.name, () => {
  if (shouldShowWidget() && !isVisible.value) {
    checkExistingChat()
    handlePulse()
  }
})
</script>

<template>
  <Teleport to="body">
    <div v-if="shouldShowWidget()" class="fixed bottom-6 right-6 z-50">
      <!-- Chat Button -->
      <button
        @click="openChat"
        :class="[
          'fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#E85D75] shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl focus:outline-none focus:ring-4 focus:ring-[#E85D75]/40',
          { 'animate-bounce': unreadCount > 0 && !isOpen }
        ]"
        :aria-label="unreadCount > 0 ? `Chat (${unreadCount} pesan baru)` : 'Mulai chat dengan kami'"
        type="button"
      >
        <!-- Chat Icon -->
        <svg
          class="h-7 w-7 text-white transition-transform duration-300"
          :class="{ 'rotate-6': unreadCount > 0 }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
          />
        </svg>
        
        <!-- Unread Badge -->
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-white text-[#E85D75] text-[10px] font-bold ring-2 ring-[#FFF8FA] animate-ping"
          aria-label="{{ unreadCount }} pesan belum dibaca"
        >
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </span>
        
        <!-- Pulse Ring for Unread -->
        <span
          v-if="unreadCount > 0 && isVisible"
          class="absolute inset-0 rounded-full bg-[#E85D75] opacity-20 animate-ping"
          aria-hidden="true"
        ></span>
      </button>

      <!-- Tooltip -->
      <div
        class="absolute bottom-16 right-0 mb-2 w-48 rounded-lg bg-[#191919] px-3 py-2 text-center text-[11px] font-medium text-white opacity-0 invisible transition-all duration-200 group-hover:opacity-100 group-hover:visible group-hover:mb-3"
        role="tooltip"
      >
        <p class="whitespace-nowrap">Butuh bantuan? Chat kami</p>
        <p v-if="unreadCount > 0" class="mt-1 text-[#E85D75]">Ada {{ unreadCount }} pesan baru</p>
        <div class="mt-1.5 h-px bg-white/20"></div>
        <p class="mt-1.5 text-[10px] text-white/60">Klik untuk memulai</p>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
/* Ensure teleport works correctly */
:deep(.fixed) {
  position: fixed;
}
</style>
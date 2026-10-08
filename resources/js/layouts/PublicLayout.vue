<script setup>
import { ref } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const route = useRoute()
const auth = useAuthStore()

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)

const logout = async () => {
    userMenuOpen.value = false
    mobileMenuOpen.value = false
    try {
        await api.post('/logout')
    } catch (error) {
        console.error('Logout error:', error)
    } finally {
        auth.clearAuth()
    }
}

const navigation = [
    { name: 'Beranda', route: 'home' },
    { name: 'Layanan', route: 'services' },
    { name: 'Portofolio', route: 'portfolio' },
    { name: 'Tentang', route: 'about' },
    { name: 'Lacak Pesanan', route: 'track-order' },
]

const isActive = (routeName) => route.name === routeName

const getAvatarUrl = (avatar) => {
    if (!avatar) return null
    if (avatar.startsWith('http')) return avatar
    return `/storage/${avatar}`
}
</script>

<template>
    <div class="min-h-screen bg-[#FFF8FA] text-[#191919]">

        <!-- =====================================================
             HEADER
        ====================================================== -->

        <header class="sticky top-0 z-50 border-b border-[#191919]/10 bg-[#FFF8FA]/90 backdrop-blur-xl">
            <div class="mx-auto flex h-[76px] max-w-[1600px] items-center justify-between px-6 sm:px-10 lg:px-16">

                <!-- Mobile hamburger button -->
                <button
                    type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-[#191919]/10 transition duration-300 hover:border-[#E85D75] md:hidden"
                    :aria-expanded="mobileMenuOpen"
                >
                    <div class="flex w-4 flex-col gap-1.5">
                        <span class="h-px w-full bg-[#191919] transition duration-300" :class="{ 'translate-y-[4px] rotate-45': mobileMenuOpen }"></span>
                        <span class="h-px w-full bg-[#191919] transition duration-300" :class="{ '-rotate-45': mobileMenuOpen }"></span>
                    </div>
                </button>

                <!-- Logo (desktop) -->
                <RouterLink :to="{ name: 'home' }" class="hidden items-center gap-3 md:flex">
                    <div class="relative flex h-9 w-9 items-center justify-center rounded-full border border-[#191919]/20 transition duration-500 hover:border-[#E85D75]">
                        <div class="h-2 w-2 rounded-full bg-[#E85D75] transition duration-500 hover:scale-150"></div>
                    </div>
                    <div class="leading-none">
                        <div class="text-[13px] font-semibold tracking-[0.12em]">TERAS</div>
                        <div class="mt-1 text-[9px] tracking-[0.35em] text-[#191919]/45">MEMORI</div>
                    </div>
                </RouterLink>

                <!-- Desktop navigation -->
                <nav class="hidden items-center gap-7 md:flex lg:gap-9">
                    <RouterLink
                        v-for="item in navigation"
                        :key="item.route"
                        :to="{ name: item.route }"
                        class="group relative whitespace-nowrap py-2 text-[10px] uppercase tracking-[0.2em] transition duration-300 lg:tracking-[0.25em]"
                        :class="isActive(item.route) ? 'text-[#191919]' : 'text-[#191919]/40 hover:text-[#191919]'"
                    >
                        {{ item.name }}
                        <span
                            class="absolute -bottom-1 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-[#E85D75] transition duration-300"
                            :class="isActive(item.route) ? 'scale-100 opacity-100' : 'scale-0 opacity-0'"
                        ></span>
                    </RouterLink>
                </nav>

                <!-- Desktop user menu / CTA -->
                <div class="hidden items-center gap-3 md:flex">
                    <RouterLink
                        v-if="!auth.isAuthenticated"
                        :to="{ name: 'login' }"
                        class="relative inline-flex items-center overflow-hidden rounded-full bg-[#191919] px-6 py-2.5 text-[10px] font-medium uppercase tracking-[0.25em] text-white shadow-sm transition duration-500 hover:bg-[#E85D75]"
                    >
                        <span class="relative z-10">Login</span>
                    </RouterLink>

                    <RouterLink
                        v-if="!auth.isAuthenticated"
                        :to="{ name: 'order' }"
                        class="group hidden items-center gap-4 rounded-full bg-[#191919] px-5 py-3 text-[9px] font-semibold uppercase tracking-[0.22em] text-white transition duration-500 hover:-translate-y-0.5 hover:bg-[#E85D75] sm:inline-flex"
                    >
                        <span>Start a project</span>
                        <span class="transition duration-300 group-hover:translate-x-1">→</span>
                    </RouterLink>

                    <div v-if="auth.isAuthenticated" class="relative">
                        <button
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2 rounded-full bg-white py-1.5 pl-1.5 pr-4 text-xs shadow-sm ring-1 ring-gray-200 hover:ring-[#E85D75]"
                        >
                            <img
                                v-if="getAvatarUrl(auth.user?.avatar)"
                                :src="getAvatarUrl(auth.user?.avatar)"
                                class="h-7 w-7 rounded-full object-cover"
                            />
                            <div v-else class="flex h-7 w-7 items-center justify-center rounded-full bg-[#E85D75] text-[10px] font-bold text-white">
                                {{ (auth.user?.name || 'U').charAt(0).toUpperCase() }}
                            </div>
                            <span class="font-medium">{{ auth.user?.name || 'Profil' }}</span>
                        </button>
                        <div v-if="userMenuOpen" class="absolute right-0 mt-2 flex w-48 flex-col rounded-lg bg-white py-1 shadow-lg ring-1 ring-gray-200">
                            <RouterLink :to="{ name: 'profile' }" class="px-4 py-2 text-sm hover:bg-gray-50">Profil Saya</RouterLink>
                            <RouterLink :to="{ name: 'member' }" class="px-4 py-2 text-sm hover:bg-gray-50">Member Saya</RouterLink>
                            <RouterLink :to="{ name: 'order-history' }" class="px-4 py-2 text-sm hover:bg-gray-50">Riwayat Pesanan</RouterLink>
                            <button @click="logout()" class="px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-50">Logout</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- =====================================================
             MOBILE SIDEBAR
        ====================================================== -->

        <!-- Overlay -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileMenuOpen"
                @click="mobileMenuOpen = false"
                class="fixed inset-0 z-40 bg-black/40 md:hidden"
            ></div>
        </Transition>

        <!-- Sidebar panel -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-y-0 left-0 z-50 flex w-80 max-w-[85vw] flex-col bg-[#FFF8FA] shadow-2xl md:hidden"
            >
                <!-- Sidebar header with logo + user info -->
                <div class="flex items-center justify-between border-b border-[#191919]/10 px-6 py-5">
                    <RouterLink :to="{ name: 'home' }" @click="mobileMenuOpen = false" class="flex items-center gap-3">
                        <div class="relative flex h-9 w-9 items-center justify-center rounded-full border border-[#191919]/20">
                            <div class="h-2 w-2 rounded-full bg-[#E85D75]"></div>
                        </div>
                        <div class="leading-none">
                            <div class="text-[13px] font-semibold tracking-[0.12em]">TERAS</div>
                            <div class="mt-1 text-[9px] tracking-[0.35em] text-[#191919]/45">MEMORI</div>
                        </div>
                    </RouterLink>
                    <button
                        @click="mobileMenuOpen = false"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-[#191919]/10 hover:border-[#E85D75]"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Logged-in user card -->
                <div v-if="auth.isAuthenticated" class="mx-4 mt-4 flex items-center gap-3 rounded-xl border border-[#191919]/10 bg-white p-4">
                    <img
                        v-if="getAvatarUrl(auth.user?.avatar)"
                        :src="getAvatarUrl(auth.user?.avatar)"
                        class="h-12 w-12 rounded-full object-cover"
                    />
                    <div v-else class="flex h-12 w-12 items-center justify-center rounded-full bg-[#E85D75] text-lg font-bold text-white">
                        {{ (auth.user?.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ auth.user?.name || 'User' }}</p>
                        <p class="truncate text-xs text-gray-500">{{ auth.user?.email || '' }}</p>
                        <p v-if="auth.user?.is_member" class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-[#E85D75]">
                            {{ auth.user?.member_tier }} member
                        </p>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto px-4 py-4">
                    <div class="space-y-1">
                        <RouterLink
                            v-for="(item, index) in navigation"
                            :key="item.route"
                            :to="{ name: item.route }"
                            @click="mobileMenuOpen = false"
                            class="group flex items-center justify-between rounded-lg px-4 py-3 transition hover:bg-[#191919]/5"
                            :class="isActive(item.route) ? 'bg-[#191919]/5' : ''"
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] tracking-[0.2em] text-[#E85D75]">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>
                                <span
                                    class="text-base font-medium"
                                    :class="isActive(item.route) ? 'text-[#191919]' : 'text-[#191919]/50'"
                                >
                                    {{ item.name }}
                                </span>
                            </div>
                            <span class="text-lg text-[#191919]/20 transition group-hover:translate-x-1 group-hover:text-[#E85D75]">→</span>
                        </RouterLink>
                    </div>
                </nav>

                <!-- Sidebar footer -->
                <div class="border-t border-[#191919]/10 p-4">
                    <template v-if="auth.isAuthenticated">
                        <RouterLink
                            :to="{ name: 'profile' }"
                            @click="mobileMenuOpen = false"
                            class="mb-2 flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm hover:bg-[#191919]/5"
                        >
                            <svg class="h-4 w-4 text-[#191919]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profil Saya
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'member' }"
                            @click="mobileMenuOpen = false"
                            class="mb-2 flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm hover:bg-[#191919]/5"
                        >
                            <svg class="h-4 w-4 text-[#191919]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                            Member Saya
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'order-history' }"
                            @click="mobileMenuOpen = false"
                            class="mb-2 flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm hover:bg-[#191919]/5"
                        >
                            <svg class="h-4 w-4 text-[#191919]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Riwayat Pesanan
                        </RouterLink>
                        <button
                            @click="logout()"
                            class="flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-sm text-red-600 hover:bg-red-50"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </template>
                    <RouterLink
                        v-else
                        :to="{ name: 'order' }"
                        @click="mobileMenuOpen = false"
                        class="flex items-center justify-center rounded-full bg-[#E85D75] px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.25em] text-white"
                    >
                        Start a project
                    </RouterLink>
                </div>
            </div>
        </Transition>

        <!-- =====================================================
             PAGE CONTENT
        ====================================================== -->

        <main>
            <RouterView />
        </main>
    </div>
</template>

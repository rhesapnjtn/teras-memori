<script setup>
import { ref } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const route = useRoute()
const auth = useAuthStore()

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)

const handleBlur = () => {
    setTimeout(() => {
        userMenuOpen.value = false
    }, 150)
}

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
    {
        name: 'Beranda',
        route: 'home',
    },
    {
        name: 'Layanan',
        route: 'services',
    },
    {
        name: 'Portofolio',
        route: 'portfolio',
    },
    {
        name: 'Tentang',
        route: 'about',
    },
    {
        name: 'Lacak Pesanan',
        route: 'track-order',
    },
]

const isActive = (routeName) => {
    return route.name === routeName
}

const getAvatarUrl = (avatar) => {
    if (!avatar) return null
    if (avatar.startsWith('http')) return avatar
    return `/storage/${avatar}`
}

const closeMobileMenu = () => {
    mobileMenuOpen.value = false
}
</script>


<template>
    <div class="min-h-screen bg-[#FFF8FA] text-[#191919]">

        <!-- =====================================================
             HEADER
        ====================================================== -->

        <header
            class="sticky top-0 z-50 border-b border-[#191919]/10 bg-[#FFF8FA]/90 backdrop-blur-xl"
        >

            <div
                class="mx-auto flex h-[76px] max-w-[1600px] items-center justify-between px-6 sm:px-10 lg:px-16"
            >

                <!-- =================================================
                     LOGO
                ================================================== -->

                <RouterLink
                    :to="{ name: 'home' }"
                    @click="closeMobileMenu"
                    class="group flex items-center gap-3"
                >

                    <div
                        class="relative flex h-9 w-9 items-center justify-center rounded-full border border-[#191919]/20 transition duration-500 group-hover:border-[#E85D75]"
                    >

                        <div
                            class="h-2 w-2 rounded-full bg-[#E85D75] transition duration-500 group-hover:scale-150"
                        ></div>

                    </div>


                    <div class="leading-none">

                        <div
                            class="text-[13px] font-semibold tracking-[0.12em]"
                        >
                            TERAS
                        </div>

                        <div
                            class="mt-1 text-[9px] tracking-[0.35em] text-[#191919]/45"
                        >
                            MEMORI
                        </div>

                    </div>

                </RouterLink>


                <!-- =================================================
                     DESKTOP NAVIGATION
                ================================================== -->

                <nav
                    class="hidden items-center gap-7 md:flex lg:gap-9"
                >

                    <RouterLink
                        v-for="item in navigation"
                        :key="item.route"
                        :to="{ name: item.route }"
                        class="group relative whitespace-nowrap py-2 text-[10px] uppercase tracking-[0.2em] transition duration-300 lg:tracking-[0.25em]"
                        :class="
                            isActive(item.route)
                                ? 'text-[#191919]'
                                : 'text-[#191919]/40 hover:text-[#191919]'
                        "
                    >

                        {{ item.name }}


                        <!-- Active indicator -->

                        <span
                            class="absolute -bottom-1 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-[#E85D75] transition duration-300"
                            :class="
                                isActive(item.route)
                                    ? 'scale-100 opacity-100'
                                    : 'scale-0 opacity-0'
                            "
                        ></span>

                    </RouterLink>

                </nav>


                <!-- =================================================
                     CTA
                ================================================== -->

                <div class="hidden items-center gap-3 md:flex">

                    <RouterLink
                        :to="{ name: 'order' }"
                        class="group relative inline-flex items-center overflow-hidden rounded-full bg-[#191919] px-6 py-2.5 text-[10px] font-medium uppercase tracking-[0.25em] text-white shadow-sm transition duration-500 hover:bg-[#E85D75] focus:outline-none focus:ring-2 focus:ring-[#E85D75]/40 focus:ring-offset-2 focus:ring-offset-[#FFF8FA]"
                    >
                        <span class="relative z-10">
                            Pesan Sekarang
                        </span>

                        <span
                            class="absolute inset-0 translate-y-full bg-[#E85D75] transition duration-500 ease-out group-hover:translate-y-0"
                        ></span>

                    </RouterLink>

                </div>


                <!-- =================================================
                     RIGHT SIDE
                ================================================== -->

                <div class="flex items-center gap-5">

                    <!-- CTA (guest) -->

                    <RouterLink
                        v-if="!auth.isAuthenticated"
                        :to="{ name: 'order' }"
                        class="group hidden items-center gap-4 rounded-full bg-[#191919] px-5 py-3 text-[9px] font-semibold uppercase tracking-[0.22em] text-white transition duration-500 hover:-translate-y-0.5 hover:bg-[#E85D75] sm:inline-flex"
                    >

                        <span>
                            Start a project
                        </span>

                        <span
                            class="transition duration-300 group-hover:translate-x-1"
                        >
                            →
                        </span>

                    </RouterLink>

                    <!-- User Menu (authenticated) -->

                    <div v-else class="relative">

                        <button
                            type="button"
                            @click="userMenuOpen = !userMenuOpen"
                            @blur="handleBlur"
                            class="group flex items-center gap-3 rounded-full bg-white py-1.5 pl-1.5 pr-4 text-xs shadow-sm ring-1 ring-[#191919]/10 hover:ring-[#E85D75]"
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

                            <svg class="h-4 w-4 text-[#191919]/60 transition duration-300 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>

                        </button>

                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="opacity-0 scale-95 -translate-y-1"
                            enter-to-class="opacity-100 scale-100 translate-y-0"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="opacity-100 scale-100 translate-y-0"
                            leave-to-class="opacity-0 scale-95 -translate-y-1"
                        >

                            <div
                                v-if="userMenuOpen"
                                class="absolute right-0 mt-2 w-48 origin-top-right rounded-lg border border-[#191919]/10 bg-white shadow-lg py-1"
                            >

                                <RouterLink
                                    :to="{ name: 'profile' }"
                                    @click="userMenuOpen = false"
                                    class="block px-4 py-2 text-sm text-[#191919] hover:bg-[#191919]/5"
                                >
                                    Profil Saya
                                </RouterLink>

                                <RouterLink
                                    :to="{ name: 'member' }"
                                    @click="userMenuOpen = false"
                                    class="block px-4 py-2 text-sm text-[#191919] hover:bg-[#191919]/5"
                                >
                                    Member Saya
                                </RouterLink>

                                <RouterLink
                                    :to="{ name: 'order-history' }"
                                    @click="userMenuOpen = false"
                                    class="block px-4 py-2 text-sm text-[#191919] hover:bg-[#191919]/5"
                                >
                                    Riwayat Pesanan
                                </RouterLink>

                                <hr class="my-2 border-[#191919]/10" />

                                <button
                                    @click="logout"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-[#191919]/5"
                                >
                                    Logout
                                </button>

                            </div>

                        </Transition>

                    </div>


                    <!-- Mobile menu button -->

                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-[#191919]/10 transition duration-300 hover:border-[#E85D75] md:hidden"
                        aria-label="Toggle navigation"
                        :aria-expanded="mobileMenuOpen"
                    >

                        <div class="flex w-4 flex-col gap-1.5">

                            <span
                                class="h-px w-full bg-[#191919] transition duration-300"
                                :class="
                                    mobileMenuOpen
                                        ? 'translate-y-[4px] rotate-45'
                                        : ''
                                "
                            ></span>


                            <span
                                class="h-px w-full bg-[#191919] transition duration-300"
                                :class="
                                    mobileMenuOpen
                                        ? '-rotate-45'
                                        : ''
                                "
                            ></span>

                        </div>

                    </button>

                </div>

            </div>


            <!-- =================================================
                 MOBILE MENU
            ================================================== -->

            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-3"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-3"
            >

                <div
                    v-if="mobileMenuOpen"
                    class="border-t border-[#191919]/10 bg-[#FFF8FA] md:hidden"
                >

                    <nav class="px-6 py-7 sm:px-10">

                        <div class="space-y-1">

                            <RouterLink
                                v-for="(item, index) in navigation"
                                :key="item.route"
                                :to="{ name: item.route }"
                                @click="closeMobileMenu"
                                class="group flex items-center justify-between border-b border-[#191919]/[0.07] py-5"
                            >

                                <div class="flex items-center gap-5">

                                    <span
                                        class="text-[9px] tracking-[0.2em] text-[#E85D75]"
                                    >
                                        {{ String(index + 1).padStart(2, '0') }}
                                    </span>


                                    <span
                                        class="text-2xl font-medium tracking-[-0.04em]"
                                        :class="
                                            isActive(item.route)
                                                ? 'text-[#191919]'
                                                : 'text-[#191919]/50'
                                        "
                                    >
                                        {{ item.name }}
                                    </span>

                                </div>


                                <span
                                    class="text-xl text-[#191919]/20 transition duration-300 group-hover:translate-x-1 group-hover:text-[#E85D75]"
                                >
                                    →
                                </span>

                            </RouterLink>

                        </div>

                        <RouterLink
                            :to="{ name: 'order' }"
                            @click="closeMobileMenu"
                            class="mt-5 flex items-center justify-center gap-2 px-6 py-5 text-[10px] font-semibold uppercase tracking-[0.25em] text-[#E85D75] transition duration-300 hover:text-[#191919]"
                        >

                            <span>Pesan Sekarang</span>

                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>

                        </RouterLink>

                        <!-- Authenticated user menu in mobile -->
                        <div v-if="auth.isAuthenticated" class="mt-6 border-t border-[#191919]/10 pt-4">

                            <div class="mx-4 mb-3 flex items-center gap-3">
                                <img
                                    v-if="getAvatarUrl(auth.user?.avatar)"
                                    :src="getAvatarUrl(auth.user?.avatar)"
                                    class="h-10 w-10 rounded-full object-cover"
                                />
                                <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-[#E85D75] text-lg font-bold text-white">
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

                            <RouterLink
                                :to="{ name: 'profile' }"
                                @click="closeMobileMenu"
                                class="mb-2 flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm text-[#191919] hover:text-[#E85D75]"
                            >
                                <svg class="h-4 w-4 text-[#191919]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profil Saya
                            </RouterLink>

                            <RouterLink
                                :to="{ name: 'member' }"
                                @click="closeMobileMenu"
                                class="mb-2 flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm text-[#191919] hover:text-[#E85D75]"
                            >
                                <svg class="h-4 w-4 text-[#191919]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                </svg>
                                Member Saya
                            </RouterLink>

                            <RouterLink
                                :to="{ name: 'order-history' }"
                                @click="closeMobileMenu"
                                class="mb-2 flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm text-[#191919] hover:text-[#E85D75]"
                            >
                                <svg class="h-4 w-4 text-[#191919]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                Riwayat Pesanan
                            </RouterLink>

                            <button
                                @click="logout"
                                class="flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-sm text-red-600 hover:bg-red-50"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </button>

                        </div>

                    </nav>

                </div>

            </Transition>

        </header>


        <!-- =====================================================
             PAGE CONTENT
        ====================================================== -->

        <main>
            <RouterView />
        </main>


        <!-- =====================================================
             FOOTER
        ====================================================== -->

        <footer
            class="border-t border-[#191919]/10 bg-[#FFF8FA]"
        >

            <div
                class="mx-auto max-w-[1600px] px-6 py-16 sm:px-10 md:py-20 lg:px-16"
            >

                <!-- =================================================
                     MAIN FOOTER
                ================================================== -->

                <div
                    class="grid gap-12 md:grid-cols-[1.4fr_0.6fr_0.6fr]"
                >

                    <!-- =================================================
                         STUDIO
                    ================================================== -->

                    <div>

                        <div
                            class="flex items-center gap-3"
                        >

                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full border border-[#191919]/15"
                            >

                                <div
                                    class="h-2 w-2 rounded-full bg-[#E85D75]"
                                ></div>

                            </div>


                            <div class="leading-none">

                                <div
                                    class="text-sm font-semibold tracking-[0.12em]"
                                >
                                    TERAS
                                </div>


                                <div
                                    class="mt-1 text-[9px] tracking-[0.35em] text-[#191919]/40"
                                >
                                    MEMORI
                                </div>

                            </div>

                        </div>


                        <p
                            class="mt-7 max-w-md text-2xl font-medium leading-[1.15] tracking-[-0.04em] text-[#191919]/70 sm:text-3xl"
                        >
                            We turn photographs into
                            <span class="text-[#E85D75]">
                                lasting memories.
                            </span>
                        </p>

                    </div>


                    <!-- =================================================
                         EXPLORE
                    ================================================== -->

                    <div>

                        <span
                            class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                        >
                            Explore
                        </span>


                        <div
                            class="mt-6 flex flex-col items-start gap-4"
                        >

                            <RouterLink
                                v-for="item in navigation"
                                :key="item.route"
                                :to="{ name: item.route }"
                                class="text-sm text-[#191919]/50 transition duration-300 hover:translate-x-1 hover:text-[#E85D75]"
                            >
                                {{ item.name }}
                            </RouterLink>

                        </div>

                    </div>


                    <!-- =================================================
                         STUDIO SERVICES
                    ================================================== -->

                    <div>

                        <span
                            class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                        >
                            Studio
                        </span>


                        <div class="mt-6 space-y-4">

                            <p
                                class="text-sm leading-6 text-[#191919]/45"
                            >
                                Image editing
                                <br />
                                Retouching
                                <br />
                                Restoration
                                <br />
                                Background removal
                            </p>


                            <RouterLink
                                :to="{ name: 'track-order' }"
                                class="inline-flex items-center gap-3 text-[9px] uppercase tracking-[0.25em] text-[#E85D75] transition duration-300 hover:gap-5"
                            >
                                Track your order

                                <span>
                                    →
                                </span>

                            </RouterLink>


<RouterLink
                            :to="{ name: 'order' }"
                            class="flex w-fit items-center gap-3 text-[9px] uppercase tracking-[0.25em] text-[#191919]/45 transition duration-300 hover:gap-5 hover:text-[#E85D75]"
                        >
                            Start a project

                            <span>
                                →
                            </span>

                        </RouterLink>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     FOOTER BOTTOM
                ================================================== -->

                <div
                    class="mt-16 flex flex-col gap-5 border-t border-[#191919]/10 pt-7 text-[9px] uppercase tracking-[0.25em] text-[#191919]/25 sm:flex-row sm:items-center sm:justify-between"
                >

                    <span>
                        Teras Memori Studio
                    </span>


                    <span>
                        Your photo / Our craft
                    </span>


                    <span>
                        © {{ new Date().getFullYear() }}
                    </span>

                </div>

            </div>

        </footer>

    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: all 0.4s ease;
}

.mobile-menu-enter-from {
    opacity: 0;
    transform: translateY(-8px);
}

.mobile-menu-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>

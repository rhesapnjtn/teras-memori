<script setup>
import { computed, ref } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const mobileMenuOpen = ref(false)

const menuSections = [
    {
        label: 'Overview',
        items: [
            {
                name: 'Dashboard',
                route: 'dashboard',
                icon: 'grid',
            },
        ],
    },
    {
        label: 'Management',
        items: [
            {
                name: 'Services',
                route: 'dashboard.services',
                icon: 'layers',
            },
            {
                name: 'Orders',
                route: 'dashboard.orders',
                icon: 'clipboard',
            },
            {
                name: 'Customers',
                route: 'dashboard.customers',
                icon: 'users',
            },
            {
                name: 'Portfolio',
                route: 'dashboard.portfolio',
                icon: 'image',
            },
        ],
    },
    {
        label: 'Engagement',
        items: [
            {
                name: 'Reviews',
                route: 'dashboard.reviews',
                icon: 'star',
            },
            {
                name: 'Chat',
                route: 'dashboard.chat',
                icon: 'message',
            },
        ],
    },
    {
        label: 'Access',
        items: [
            {
                name: 'Roles',
                route: 'dashboard.roles',
                icon: 'shield',
            },
        ],
    },
]

const isActive = (item) => {
    return route.name === item.route
}

const closeMobileMenu = () => {
    mobileMenuOpen.value = false
}

const logout = async () => {
    await auth.logout()
    router.push({ name: 'login' })
}

const userName = computed(() => {
    return auth.user?.name || 'Administrator'
})

const userEmail = computed(() => {
    return auth.user?.email || 'admin@teras-memori.test'
})

const initials = computed(() => {
    return userName.value
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase()
})
</script>

<template>
    <div class="min-h-screen bg-[#FFF8FA] text-[#191919]">

        <!-- ========================================= -->
        <!-- MOBILE OVERLAY -->
        <!-- ========================================= -->

        <Transition name="fade">
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 z-40 bg-[#191919]/30 backdrop-blur-sm lg:hidden"
                @click="closeMobileMenu"
            ></div>
        </Transition>


        <!-- ========================================= -->
        <!-- SIDEBAR -->
        <!-- ========================================= -->

        <aside
            class="
                fixed inset-y-0 left-0 z-50 flex w-[280px] flex-col
                border-r border-[#191919]/10 bg-[#FFF8FA]
                transition-transform duration-500
                lg:translate-x-0
            "
            :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
        >

            <!-- Logo -->
            <div class="flex h-[92px] shrink-0 items-center border-b border-[#191919]/10 px-7">
                <RouterLink
                    :to="{ name: 'dashboard' }"
                    class="group flex items-center gap-4"
                    @click="closeMobileMenu"
                >
                    <div
                        class="
                            relative flex h-10 w-10 items-center justify-center
                            rounded-full border border-[#E85D75]/30
                            transition duration-500
                            group-hover:rotate-12 group-hover:border-[#E85D75]
                        "
                    >
                        <div class="h-4 w-4 rounded-full bg-[#E85D75]"></div>

                        <span
                            class="
                                absolute -right-1 -top-1 h-2 w-2
                                rounded-full bg-[#191919]
                            "
                        ></span>
                    </div>

                    <div class="leading-none">
                        <div class="text-[17px] font-semibold tracking-[-0.04em]">
                            TERAS
                        </div>

                        <div
                            class="
                                mt-1 text-[9px] font-medium uppercase
                                tracking-[0.35em] text-[#191919]/40
                            "
                        >
                            MEMORI
                        </div>
                    </div>
                </RouterLink>
            </div>


            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 py-7">

                <div
                    v-for="section in menuSections"
                    :key="section.label"
                    class="mb-8 last:mb-0"
                >

                    <!-- Section label -->
                    <div
                        class="
                            mb-3 px-4 text-[8px] font-semibold uppercase
                            tracking-[0.35em] text-[#191919]/30
                        "
                    >
                        {{ section.label }}
                    </div>


                    <!-- Menu -->
                    <div class="space-y-1">

                        <RouterLink
                            v-for="item in section.items"
                            :key="item.route"
                            :to="{ name: item.route }"
                            class="
                                group relative flex items-center gap-4
                                rounded-xl px-4 py-3.5
                                text-sm transition-all duration-300
                            "
                            :class="
                                isActive(item)
                                    ? 'bg-[#191919] text-white'
                                    : 'text-[#191919]/55 hover:bg-[#F4A6B8]/10 hover:text-[#191919]'
                            "
                            @click="closeMobileMenu"
                        >

                            <!-- Active indicator -->
                            <span
                                v-if="isActive(item)"
                                class="
                                    absolute -left-4 top-1/2 h-5 w-1
                                    -translate-y-1/2 rounded-r-full
                                    bg-[#E85D75]
                                "
                            ></span>


                            <!-- Icons -->
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center"
                                :class="
                                    isActive(item)
                                        ? 'text-[#F4A6B8]'
                                        : 'text-[#191919]/35 group-hover:text-[#E85D75]'
                                "
                            >

                                <!-- Grid -->
                                <svg
                                    v-if="item.icon === 'grid'"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    class="h-5 w-5"
                                >
                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                    <rect x="14" y="14" width="7" height="7" rx="1" />
                                </svg>


                                <!-- Layers -->
                                <svg
                                    v-else-if="item.icon === 'layers'"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    class="h-5 w-5"
                                >
                                    <path d="m12 3 9 5-9 5-9-5 9-5Z" />
                                    <path d="m3 12 9 5 9-5" />
                                    <path d="m3 16 9 5 9-5" />
                                </svg>


                                <!-- Clipboard -->
                                <svg
                                    v-else-if="item.icon === 'clipboard'"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    class="h-5 w-5"
                                >
                                    <rect x="5" y="4" width="14" height="17" rx="2" />
                                    <path d="M9 4V3h6v1" />
                                    <path d="M9 9h6M9 13h6M9 17h4" />
                                </svg>


                                <!-- Users -->
                                <svg
                                    v-else-if="item.icon === 'users'"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    class="h-5 w-5"
                                >
                                    <circle cx="9" cy="8" r="3" />
                                    <path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6" />
                                    <path d="M16 5.5a3 3 0 0 1 0 5.8" />
                                    <path d="M18 14.5c1.8.9 3 2.8 3 5" />
                                </svg>


                                <!-- Image -->
                                <svg
                                    v-else-if="item.icon === 'image'"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    class="h-5 w-5"
                                >
                                    <rect x="3" y="4" width="18" height="16" rx="2" />
                                    <circle cx="8.5" cy="9" r="1.5" />
                                    <path d="m3 17 5-5 4 4 3-3 6 6" />
                                </svg>


                                <!-- Star -->
                                <svg
                                    v-else-if="item.icon === 'star'"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    class="h-5 w-5"
                                >
                                    <path
                                        d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"
                                    />
                                </svg>


                                <!-- Message -->
                                <svg
                                    v-else-if="item.icon === 'message'"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    class="h-5 w-5"
                                >
                                    <path
                                        d="M20 11.5a7.5 7.5 0 0 1-8 7.5 8.6 8.6 0 0 1-4-.9L4 20l1.2-3.6A7.3 7.3 0 0 1 4.5 12 7.5 7.5 0 1 1 20 11.5Z"
                                    />
                                    <path d="M8 12h.01M12 12h.01M16 12h.01" />
                                </svg>


                                <!-- Shield -->
                                <svg
                                    v-else-if="item.icon === 'shield'"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    class="h-5 w-5"
                                >
                                    <path
                                        d="M12 3l7 3v5.5c0 4.4-3 8.3-7 9.5-4-1.2-7-5.1-7-9.5V6l7-3Z"
                                    />
                                </svg>

                            </span>


                            <!-- Name -->
                            <span class="flex-1">
                                {{ item.name }}
                            </span>


                            <!-- Active arrow -->
                            <span
                                v-if="isActive(item)"
                                class="text-[#F4A6B8]"
                            >
                                →
                            </span>

                        </RouterLink>

                    </div>
                </div>

            </nav>


            <!-- Sidebar bottom -->
            <div class="shrink-0 border-t border-[#191919]/10 p-5">

                <!-- User -->
                <div class="mb-4 flex items-center gap-3 px-2">

                    <div
                        class="
                            flex h-9 w-9 shrink-0 items-center justify-center
                            rounded-full bg-[#F4A6B8]/30
                            text-[10px] font-semibold text-[#E85D75]
                        "
                    >
                        {{ initials }}
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold">
                            {{ userName }}
                        </p>

                        <p class="truncate text-[9px] text-[#191919]/35">
                            {{ userEmail }}
                        </p>
                    </div>

                </div>


                <!-- Logout -->
                <button
                    type="button"
                    @click="logout"
                    class="
                        group flex w-full items-center gap-3 rounded-xl
                        px-4 py-3 text-left text-xs font-medium
                        text-[#191919]/45 transition duration-300
                        hover:bg-red-50 hover:text-red-600
                    "
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        class="h-5 w-5"
                    >
                        <path d="M10 17l5-5-5-5" />
                        <path d="M15 12H3" />
                        <path d="M21 19V5a2 2 0 0 0-2-2h-6" />
                    </svg>

                    <span>Logout</span>
                </button>

            </div>

        </aside>


        <!-- ========================================= -->
        <!-- MAIN -->
        <!-- ========================================= -->

        <div class="min-h-screen lg:ml-[280px]">

            <!-- Topbar -->
            <header
                class="
                    sticky top-0 z-30 flex h-[92px] items-center
                    justify-between border-b border-[#191919]/10
                    bg-[#FFF8FA]/90 px-6 backdrop-blur-xl
                    sm:px-8 lg:px-10
                "
            >

                <div class="flex items-center gap-4">

                    <!-- Mobile menu -->
                    <button
                        type="button"
                        class="
                            flex h-10 w-10 items-center justify-center
                            rounded-full border border-[#191919]/10
                            transition hover:border-[#E85D75]
                            lg:hidden
                        "
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <svg
                            v-if="!mobileMenuOpen"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            class="h-5 w-5"
                        >
                            <path d="M4 7h16M4 12h16M4 17h16" />
                        </svg>

                        <svg
                            v-else
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            class="h-5 w-5"
                        >
                            <path d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>


                    <div>
                        <div
                            class="
                                text-[9px] font-semibold uppercase
                                tracking-[0.35em] text-[#191919]/30
                            "
                        >
                            Teras Memori / Admin
                        </div>

                        <h1
                            class="
                                mt-1 text-xl font-medium tracking-[-0.04em]
                                sm:text-2xl
                            "
                        >
                            {{ route.name === 'dashboard' ? 'Overview' : route.meta?.title || 'Management' }}
                        </h1>
                    </div>

                </div>


                <!-- Right -->
                <div class="flex items-center gap-4">

                    <RouterLink
                        :to="{ name: 'home' }"
                        class="
                            hidden items-center gap-3 text-[9px] font-medium
                            uppercase tracking-[0.25em] text-[#191919]/40
                            transition hover:text-[#E85D75]
                            sm:flex
                        "
                    >
                        <span>View website</span>
                        <span>↗</span>
                    </RouterLink>


                    <div class="hidden h-7 w-px bg-[#191919]/10 sm:block"></div>


                    <div
                        class="
                            flex h-9 w-9 items-center justify-center
                            rounded-full border border-[#E85D75]/20
                            bg-[#F4A6B8]/15 text-[10px]
                            font-semibold text-[#E85D75]
                        "
                    >
                        {{ initials }}
                    </div>

                </div>

            </header>


            <!-- Content -->
            <main class="min-h-[calc(100vh-92px)] px-6 py-8 sm:px-8 sm:py-10 lg:px-10">

                <RouterView />

            </main>

        </div>

    </div>
</template>


<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
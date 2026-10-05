```vue
<script setup>
import { ref } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'

const route = useRoute()

const mobileMenuOpen = ref(false)

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

                    <!-- CTA -->

                    <RouterLink
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


                        <!-- =================================================
                             MOBILE CTA
                        ================================================== -->

                        <RouterLink
                            :to="{ name: 'order' }"
                            @click="closeMobileMenu"
                            class="mt-7 flex items-center justify-between bg-[#E85D75] px-6 py-5 text-[10px] font-semibold uppercase tracking-[0.25em] text-white"
                        >

                            <span>
                                Start a project
                            </span>


                            <span class="text-lg">
                                →
                            </span>

                        </RouterLink>


                        <!-- =================================================
                             MOBILE ADMIN
                        ================================================== -->

                        <RouterLink
                            :to="{ name: 'login' }"
                            @click="closeMobileMenu"
                            class="mt-5 block text-center text-[9px] uppercase tracking-[0.3em] text-[#191919]/30 transition hover:text-[#E85D75]"
                        >
                            Admin access
                        </RouterLink>

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
```

                <!-- Mobile Menu Button -->

                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="group relative flex h-11 w-11 items-center justify-center rounded-full border border-[#191919]/15 transition duration-500 md:hidden"
                    :class="mobileMenuOpen ? 'border-[#E85D75] bg-[#E85D75]/10' : 'hover:border-[#E85D75]'"
                    aria-label="Toggle menu"
                >

                    <div class="relative flex h-3.5 w-5 flex-col justify-between">

                        <span
                            class="h-px w-full origin-left bg-[#191919] transition duration-500"
                            :class="mobileMenuOpen ? 'rotate-45 translate-y-1' : ''"
                        ></span>

                        <span
                            class="h-px w-3/4 bg-[#191919] transition duration-500"
                            :class="mobileMenuOpen ? 'opacity-0' : ''"
                        ></span>

                        <span
                            class="h-px w-full origin-left bg-[#191919] transition duration-500"
                            :class="mobileMenuOpen ? '-rotate-45 -translate-y-1' : ''"
                        ></span>

                    </div>

                </button>

            </div>

        </header>


        <!-- =====================================================
             MOBILE MENU
        ====================================================== -->

        <Transition name="mobile-menu">

            <div
                v-if="mobileMenuOpen"
                class="fixed inset-x-0 top-[76px] z-40 bg-[#FFF8FA]/98 backdrop-blur-xl md:hidden"
            >

                <div
                    class="flex flex-col divide-y divide-[#191919]/10 border-b border-[#191919]/10"
                >

                    <RouterLink
                        v-for="item in navigation"
                        :key="item.route"
                        :to="{ name: item.route }"
                        @click="closeMobileMenu"
                        class="flex items-center justify-between px-6 py-4 text-[11px] uppercase tracking-[0.3em] transition duration-300"
                        :class="isActive(item.route) ? 'text-[#191919]' : 'text-[#191919]/40 hover:text-[#191919]'"
                    >

                        {{ item.name }}

                        <span
                            v-if="isActive(item.route)"
                            class="h-1.5 w-1.5 rounded-full bg-[#E85D75]"
                        ></span>

                    </RouterLink>

                    <RouterLink
                        :to="{ name: 'order' }"
                        @click="closeMobileMenu"
                        class="flex items-center justify-center gap-2 px-6 py-5 text-[11px] font-medium uppercase tracking-[0.3em] text-[#E85D75] transition duration-300 hover:text-[#191919]"
                    >

                        <span>Pesan Sekarang</span>

                        <svg
                            class="h-3 w-3"
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

                </div>

            </div>

        </Transition>


        <!-- =====================================================
             PAGE CONTENT
        ====================================================== -->

        <main>

            <router-view />

        </main>


        <!-- =====================================================
             FOOTER
        ====================================================== -->

        <footer class="border-t border-[#191919]/10 bg-[#FFF8FA]">

            <div class="mx-auto max-w-[1600px] px-6 py-14 sm:px-10 lg:px-16">

                <div
                    class="flex flex-col items-start justify-between gap-10 lg:flex-row lg:items-center"
                >

                    <!-- Logo -->
                    <RouterLink
                        :to="{ name: 'home' }"
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


                    <!-- Nav -->
                    <nav
                        class="flex flex-wrap items-center gap-x-7 gap-y-3 text-[10px] uppercase tracking-[0.25em] text-[#191919]/40"
                    >

                        <RouterLink
                            :to="{ name: 'home' }"
                            class="transition duration-300 hover:text-[#191919]"
                        >
                            Beranda
                        </RouterLink>

                        <RouterLink
                            :to="{ name: 'services' }"
                            class="transition duration-300 hover:text-[#191919]"
                        >
                            Layanan
                        </RouterLink>

                        <RouterLink
                            :to="{ name: 'portfolio' }"
                            class="transition duration-300 hover:text-[#191919]"
                        >
                            Portofolio
                        </RouterLink>

                        <RouterLink
                            :to="{ name: 'order' }"
                            class="transition duration-300 hover:text-[#191919]"
                        >
                            Pesan
                        </RouterLink>

                        <RouterLink
                            :to="{ name: 'track-order' }"
                            class="transition duration-300 hover:text-[#191919]"
                        >
                            Lacak Pesanan
                        </RouterLink>

                    </nav>

                </div>


                <div
                    class="mt-10 flex flex-col items-start justify-between gap-4 border-t border-[#191919]/10 pt-8 sm:flex-row sm:items-center"
                >

                    <p
                        class="text-[10px] uppercase tracking-[0.25em] text-[#191919]/30"
                    >
                        &copy; {{ new Date().getFullYear() }} Teras Memori. All rights reserved.
                    </p>

                    <p
                        class="text-[10px] uppercase tracking-[0.25em] text-[#191919]/25"
                    >
                        Crafted with care for timeless memories.
                    </p>

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

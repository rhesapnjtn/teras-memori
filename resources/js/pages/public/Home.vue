<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../services/api'

const services = ref([])
const portfolios = ref([])
const loading = ref(true)
const errorMessage = ref('')

const fetchHomeData = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const [servicesResponse, portfoliosResponse] = await Promise.all([
            api.get('/services'),
            api.get('/portfolios'),
        ])

        services.value =
            servicesResponse.data.data?.slice(0, 4) || []

        portfolios.value =
            portfoliosResponse.data.data?.slice(0, 6) || []
    } catch (error) {
        console.error('Failed to load home data:', error)

        errorMessage.value =
            'Some content could not be loaded.'
    } finally {
        loading.value = false
    }
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(Number(price))
}

const featuredPortfolio = computed(() => {
    return portfolios.value[0] || null
})

onMounted(fetchHomeData)
</script>

<template>
    <div class="overflow-hidden bg-[#FFF8FA] text-[#191919]">

        <!-- =====================================================
             HERO — ARTIST INTRODUCTION
        ====================================================== -->
        <section
            class="relative min-h-[calc(100vh-80px)] overflow-hidden border-b border-[#191919]/10"
        >

            <!-- Large decorative pink circle -->
            <div
                class="pointer-events-none absolute -right-[18rem] -top-[14rem] h-[650px] w-[650px] rounded-full bg-[#F4A6B8]/30 blur-[2px]"
            ></div>

            <!-- Small pink dot -->
            <div
                class="pointer-events-none absolute left-[7%] top-[32%] h-3 w-3 rounded-full bg-[#E85D75]"
            ></div>

            <!-- Background typography -->
            <div
                class="pointer-events-none absolute -bottom-12 left-0 select-none text-[20vw] font-bold leading-none tracking-[-0.1em] text-[#191919]/[0.025]"
            >
                ART
            </div>


            <div
                class="relative mx-auto max-w-[1700px] px-6 pb-20 pt-14 sm:px-10 sm:pb-28 lg:px-16 lg:pt-20"
            >

                <!-- Top Meta -->
                <div
                    class="mb-16 flex items-center justify-between sm:mb-20"
                >

                    <div class="flex items-center gap-4">

                        <span
                            class="text-[10px] font-bold tracking-[0.35em]"
                        >
                            TM
                        </span>

                        <span
                            class="h-px w-10 bg-[#191919]/30"
                        ></span>

                        <span
                            class="text-[9px] font-semibold uppercase tracking-[0.3em] text-[#8A7077]"
                        >
                            Independent Photo Studio
                        </span>

                    </div>


                    <span
                        class="hidden text-[9px] uppercase tracking-[0.3em] text-[#A9959A] sm:block"
                    >
                        Indonesia / 2026
                    </span>

                </div>


                <!-- Hero Composition -->
                <div
                    class="grid items-center gap-14 lg:grid-cols-[0.9fr_1.1fr] lg:gap-4"
                >

                    <!-- LEFT -->
                    <div
                        class="relative z-20"
                    >

                        <span
                            class="mb-7 inline-block text-[10px] font-bold uppercase tracking-[0.4em] text-[#E85D75]"
                        >
                            Visual Art & Photo Editing
                        </span>


                        <h1
                            class="max-w-4xl text-[17vw] font-semibold leading-[0.76] tracking-[-0.09em] sm:text-[13vw] lg:text-[8.5vw]"
                        >

                            <span class="block">
                                WE
                            </span>

                            <span
                                class="ml-[9vw] block text-[#E85D75] sm:ml-[5vw]"
                            >
                                SHAPE.
                            </span>

                            <span class="block">
                                STORIES.
                            </span>

                        </h1>


                        <div
                            class="mt-12 max-w-xl"
                        >

                            <p
                                class="text-base leading-7 text-[#716467] sm:text-lg"
                            >
                                Teras Memori adalah creative photo studio
                                yang memperlakukan setiap foto sebagai
                                sebuah karya — bukan sekadar gambar.
                            </p>


                            <div
                                class="mt-9 flex flex-wrap items-center gap-6"
                            >

                                <RouterLink
                                    :to="{ name: 'order' }"
                                    class="group inline-flex items-center gap-4 rounded-full bg-[#191919] px-7 py-4 text-[10px] font-bold uppercase tracking-[0.25em] text-white transition duration-500 hover:bg-[#E85D75]"
                                >

                                    Start a project

                                    <span
                                        class="transition duration-500 group-hover:translate-x-1"
                                    >
                                        →
                                    </span>

                                </RouterLink>


                                <RouterLink
                                    :to="{ name: 'portfolio' }"
                                    class="group inline-flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.25em] text-[#514548]"
                                >

                                    Explore our work

                                    <span
                                        class="transition duration-500 group-hover:translate-x-2"
                                    >
                                        ↗
                                    </span>

                                </RouterLink>

                            </div>

                        </div>

                    </div>


                    <!-- RIGHT — ARTWORK -->
                    <div
                        class="relative mx-auto w-full max-w-2xl lg:-ml-8"
                    >

                        <!-- Artwork number -->
                        <div
                            class="absolute -left-4 top-8 z-30 flex h-16 w-16 items-center justify-center rounded-full border border-[#191919]/10 bg-white/90 shadow-xl backdrop-blur-md sm:-left-8"
                        >

                            <div class="text-center">

                                <span
                                    class="block text-[7px] font-bold uppercase tracking-[0.2em] text-[#9B858B]"
                                >
                                    Work
                                </span>

                                <span
                                    class="mt-1 block text-xs font-bold text-[#E85D75]"
                                >
                                    001
                                </span>

                            </div>

                        </div>


                        <!-- Pink frame -->
                        <div
                            class="absolute -right-5 -top-5 h-32 w-32 rounded-full border-[18px] border-[#F4A6B8]/40 sm:-right-10 sm:-top-8"
                        ></div>


                        <!-- Main Artwork -->
                        <div
                            class="relative z-10 ml-auto w-[88%] overflow-hidden rounded-[2.5rem] bg-[#F4A6B8] shadow-[0_40px_100px_rgba(117,65,78,0.16)]"
                        >

                            <div
                                class="aspect-[0.82]"
                            >

                                <img
                                    src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=1600&q=90"
                                    alt="Teras Memori creative artwork"
                                    class="h-full w-full object-cover transition duration-[1500ms] hover:scale-105"
                                />

                            </div>


                            <!-- Artwork overlay -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-[#191919]/65 via-transparent to-transparent"
                            ></div>


                            <!-- Artwork caption -->
                            <div
                                class="absolute bottom-7 left-7 right-7"
                            >

                                <div
                                    class="flex items-end justify-between"
                                >

                                    <div>

                                        <span
                                            class="text-[8px] font-bold uppercase tracking-[0.35em] text-white/60"
                                        >
                                            Study No. 001
                                        </span>

                                        <h2
                                            class="mt-2 text-xl font-medium text-white sm:text-2xl"
                                        >
                                            Light / Memory
                                        </h2>

                                    </div>


                                    <span
                                        class="text-[9px] uppercase tracking-[0.25em] text-white/50"
                                    >
                                        2026
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- Floating artist card -->
                        <div
                            class="absolute -bottom-8 left-0 z-30 hidden w-56 rounded-[1.5rem] border border-[#191919]/10 bg-white p-6 shadow-[0_25px_70px_rgba(25,25,25,0.10)] sm:block"
                        >

                            <div
                                class="flex items-center justify-between"
                            >

                                <span
                                    class="text-[8px] font-bold uppercase tracking-[0.3em] text-[#A0838A]"
                                >
                                    The Artist's Note
                                </span>

                                <span
                                    class="text-[#E85D75]"
                                >
                                    ✦
                                </span>

                            </div>


                            <p
                                class="mt-4 font-serif text-lg italic leading-6 text-[#413638]"
                            >
                                “Every detail has a story.”
                            </p>

                        </div>


                        <!-- Side text -->
                        <div
                            class="absolute -right-2 bottom-20 z-20 hidden [writing-mode:vertical-rl] text-[8px] font-bold uppercase tracking-[0.35em] text-[#A98B92] sm:block"
                        >
                            Crafted with intention

                        </div>

                    </div>

                </div>


                <!-- Hero bottom -->
                <div
                    class="mt-24 flex items-center justify-between border-t border-[#191919]/10 pt-6"
                >

                    <span
                        class="text-[9px] font-bold uppercase tracking-[0.3em] text-[#A28D92]"
                    >
                        Scroll to discover
                    </span>


                    <div
                        class="flex items-center gap-3"
                    >

                        <span
                            class="h-px w-16 bg-[#191919]/20"
                        ></span>

                        <span
                            class="text-[#E85D75]"
                        >
                            ↓
                        </span>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             ARTIST STATEMENT
        ====================================================== -->
        <section
            class="relative border-b border-[#191919]/10 bg-white"
        >

            <div
                class="mx-auto max-w-[1700px] px-6 py-28 sm:px-10 md:py-40 lg:px-16"
            >

                <div
                    class="grid gap-16 lg:grid-cols-[0.25fr_1fr]"
                >

                    <!-- Number -->
                    <div>

                        <span
                            class="text-[9px] font-bold uppercase tracking-[0.35em] text-[#E85D75]"
                        >
                            01 — Artist Statement
                        </span>

                    </div>


                    <!-- Statement -->
                    <div>

                        <h2
                            class="max-w-7xl text-4xl font-medium leading-[1.02] tracking-[-0.055em] sm:text-5xl md:text-6xl lg:text-8xl"
                        >

                            We don't simply edit photographs.

                            <span
                                class="text-[#E7B8C1]"
                            >
                                We shape light, emotion,
                                texture and memory.
                            </span>

                        </h2>


                        <div
                            class="mt-14 flex flex-col justify-between gap-10 border-t border-[#191919]/10 pt-8 md:flex-row md:items-end"
                        >

                            <p
                                class="max-w-xl text-sm leading-7 text-[#75686B]"
                            >
                                Dari retouching yang subtle hingga
                                restoration foto lama, setiap proses
                                dikerjakan dengan perhatian terhadap
                                detail dan karakter visual.
                            </p>


                            <RouterLink
                                :to="{ name: 'about' }"
                                class="group inline-flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.25em]"
                            >

                                About the studio

                                <span
                                    class="text-[#E85D75] transition group-hover:translate-x-2"
                                >
                                    →
                                </span>

                            </RouterLink>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             MARQUEE
        ====================================================== -->
        <section
            class="overflow-hidden border-b border-[#191919]/10 bg-[#FFF8FA] py-8"
        >

            <div
                class="flex w-max animate-[marquee_28s_linear_infinite] items-center gap-10 whitespace-nowrap"
            >

                <template
                    v-for="item in 8"
                    :key="item"
                >

                    <span
                        class="font-serif text-4xl italic tracking-[-0.04em] text-[#E85D75]/50 sm:text-6xl"
                    >
                        EDIT
                    </span>

                    <span
                        class="text-lg text-[#E85D75]"
                    >
                        ✦
                    </span>

                    <span
                        class="text-4xl font-semibold tracking-[-0.05em] text-[#191919]/10 sm:text-6xl"
                    >
                        RETOUCH
                    </span>

                    <span
                        class="text-lg text-[#E85D75]"
                    >
                        ✦
                    </span>

                    <span
                        class="font-serif text-4xl italic tracking-[-0.04em] text-[#E85D75]/50 sm:text-6xl"
                    >
                        RESTORE
                    </span>

                    <span
                        class="text-lg text-[#E85D75]"
                    >
                        ✦
                    </span>

                </template>

            </div>

        </section>


        <!-- =====================================================
             SERVICES — ARTISTIC MENU
        ====================================================== -->
        <section
            class="border-b border-[#191919]/10"
        >

            <div
                class="mx-auto max-w-[1700px] px-6 py-28 sm:px-10 md:py-40 lg:px-16"
            >

                <div
                    class="grid gap-14 lg:grid-cols-[0.35fr_1fr]"
                >

                    <!-- Heading -->
                    <div>

                        <span
                            class="text-[9px] font-bold uppercase tracking-[0.35em] text-[#E85D75]"
                        >
                            02 — The Practice
                        </span>


                        <h2
                            class="mt-6 max-w-sm text-5xl font-semibold leading-[0.95] tracking-[-0.06em] sm:text-6xl"
                        >
                            Our
                            <span
                                class="font-serif italic text-[#E85D75]"
                            >
                                craft.
                            </span>
                        </h2>


                        <p
                            class="mt-7 max-w-xs text-sm leading-7 text-[#796A6F]"
                        >
                            Setiap layanan adalah bagian dari
                            proses artistik kami untuk membuat
                            foto terlihat lebih hidup.
                        </p>

                    </div>


                    <!-- Services list -->
                    <div>

                        <!-- Loading -->
                        <div
                            v-if="loading"
                            class="divide-y divide-[#191919]/10 border-y border-[#191919]/10"
                        >

                            <div
                                v-for="item in 4"
                                :key="item"
                                class="h-32 animate-pulse bg-[#FFF8FA]"
                            ></div>

                        </div>


                        <!-- Error -->
                        <div
                            v-else-if="errorMessage"
                            class="border-y border-[#191919]/10 py-16"
                        >

                            <p
                                class="text-sm text-[#75686B]"
                            >
                                {{ errorMessage }}
                            </p>

                            <button
                                type="button"
                                @click="fetchHomeData"
                                class="mt-6 rounded-full bg-[#191919] px-6 py-3 text-[9px] font-bold uppercase tracking-[0.25em] text-white"
                            >
                                Try again
                            </button>

                        </div>


                        <!-- Services -->
                        <div
                            v-else-if="services.length"
                            class="border-y border-[#191919]/10"
                        >

                            <RouterLink
                                v-for="(service, index) in services"
                                :key="service.id"
                                :to="{
                                    name: 'order',
                                    query: {
                                        service: service.id,
                                    },
                                }"
                                class="group relative grid gap-5 border-b border-[#191919]/10 py-9 last:border-b-0 sm:grid-cols-[80px_1fr_auto] sm:items-center"
                            >

                                <!-- Number -->
                                <span
                                    class="text-[10px] font-bold text-[#C9ADB4]"
                                >
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>


                                <!-- Main -->
                                <div>

                                    <h3
                                        class="text-2xl font-semibold tracking-[-0.03em] transition duration-500 group-hover:translate-x-3 group-hover:text-[#E85D75] sm:text-3xl md:text-4xl"
                                    >
                                        {{ service.name }}
                                    </h3>

                                    <p
                                        v-if="service.description"
                                        class="mt-2 max-w-lg text-sm leading-6 text-[#8A797E]"
                                    >
                                        {{ service.description }}
                                    </p>

                                </div>


                                <!-- Price -->
                                <div
                                    class="flex items-center justify-between gap-5 sm:justify-end"
                                >

                                    <span
                                        class="text-[9px] font-bold uppercase tracking-[0.2em] text-[#9A878D]"
                                    >
                                        From Rp
                                        {{ formatPrice(service.price) }}
                                    </span>


                                    <span
                                        class="flex h-11 w-11 items-center justify-center rounded-full border border-[#191919]/15 transition duration-500 group-hover:rotate-45 group-hover:border-[#E85D75] group-hover:bg-[#E85D75] group-hover:text-white"
                                    >
                                        ↗
                                    </span>

                                </div>

                            </RouterLink>

                        </div>


                        <!-- Empty -->
                        <div
                            v-else
                            class="border-y border-[#191919]/10 py-16 text-center"
                        >

                            <p
                                class="text-sm text-[#8A797E]"
                            >
                                No services available.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             PORTFOLIO — GALLERY
        ====================================================== -->
        <section
            class="relative border-b border-[#191919]/10 bg-[#191919] text-white"
        >

            <!-- Pink glow -->
            <div
                class="pointer-events-none absolute -right-40 top-20 h-96 w-96 rounded-full bg-[#E85D75]/20 blur-[120px]"
            ></div>


            <div
                class="relative mx-auto max-w-[1700px] px-6 py-28 sm:px-10 md:py-40 lg:px-16"
            >

                <!-- Heading -->
                <div
                    class="mb-20 flex flex-col justify-between gap-10 md:flex-row md:items-end"
                >

                    <div>

                        <span
                            class="text-[9px] font-bold uppercase tracking-[0.35em] text-[#F4A6B8]"
                        >
                            03 — Selected Works
                        </span>


                        <h2
                            class="mt-6 text-5xl font-semibold tracking-[-0.06em] sm:text-6xl md:text-8xl"
                        >
                            The
                            <span
                                class="font-serif font-normal italic text-[#F4A6B8]"
                            >
                                gallery.
                            </span>
                        </h2>

                    </div>


                    <RouterLink
                        :to="{ name: 'portfolio' }"
                        class="group inline-flex items-center gap-3 text-[9px] font-bold uppercase tracking-[0.25em] text-white/50"
                    >

                        View all works

                        <span
                            class="text-[#F4A6B8] transition group-hover:translate-x-2"
                        >
                            →
                        </span>

                    </RouterLink>

                </div>


                <!-- Loading -->
                <div
                    v-if="loading"
                    class="grid gap-8 md:grid-cols-2"
                >

                    <div
                        v-for="item in 4"
                        :key="item"
                        class="aspect-[4/3] animate-pulse rounded-[2rem] bg-white/5"
                    ></div>

                </div>


                <!-- Gallery -->
                <div
                    v-else-if="portfolios.length"
                    class="grid gap-x-8 gap-y-20 md:grid-cols-12"
                >

                    <RouterLink
                        v-for="(portfolio, index) in portfolios"
                        :key="portfolio.id"
                        :to="{ name: 'portfolio' }"
                        class="group md:col-span-7"
                        :class="
                            index % 3 === 1
                                ? 'md:col-span-5 md:mt-32'
                                : index % 3 === 2
                                    ? 'md:col-span-6 md:ml-[18%]'
                                    : ''
                        "
                    >

                        <!-- Image -->
                        <div
                            class="relative overflow-hidden rounded-[2rem] bg-[#2B2527]"
                            :class="
                                index % 3 === 1
                                    ? 'aspect-[0.85]'
                                    : 'aspect-[1.15]'
                            "
                        >

                            <img
                                v-if="portfolio.image"
                                :src="portfolio.image"
                                :alt="portfolio.title"
                                class="h-full w-full object-cover transition duration-[1200ms] group-hover:scale-105"
                                loading="lazy"
                            />


                            <!-- Fallback -->
                            <div
                                v-else
                                class="flex h-full items-center justify-center bg-[#30282B]"
                            >

                                <span
                                    class="text-[9px] font-bold uppercase tracking-[0.3em] text-white/20"
                                >
                                    Teras Memori
                                </span>

                            </div>


                            <!-- Overlay -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent"
                            ></div>


                            <!-- Work number -->
                            <div
                                class="absolute left-6 top-6 flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-black/20 text-[9px] font-bold backdrop-blur-md"
                            >
                                {{ String(index + 1).padStart(2, '0') }}
                            </div>


                            <!-- Information -->
                            <div
                                class="absolute bottom-7 left-7 right-7 flex items-end justify-between"
                            >

                                <div>

                                    <span
                                        class="text-[8px] font-bold uppercase tracking-[0.3em] text-white/50"
                                    >
                                        {{ portfolio.category || 'Artwork' }}
                                    </span>

                                    <h3
                                        class="mt-2 text-2xl font-medium tracking-[-0.03em]"
                                    >
                                        {{ portfolio.title }}
                                    </h3>

                                </div>


                                <span
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#F4A6B8] text-[#191919] transition duration-500 group-hover:rotate-45"
                                >
                                    ↗
                                </span>

                            </div>

                        </div>


                        <!-- Meta -->
                        <div
                            class="mt-5 flex items-center justify-between"
                        >

                            <span
                                class="text-[8px] font-bold uppercase tracking-[0.3em] text-white/25"
                            >
                                Teras Memori — Work
                            </span>

                            <span
                                class="text-[9px] text-white/30"
                            >
                                2026
                            </span>

                        </div>

                    </RouterLink>

                </div>


                <!-- Empty -->
                <div
                    v-else
                    class="border-y border-white/10 py-16 text-center"
                >

                    <p
                        class="text-sm text-white/30"
                    >
                        No portfolio projects available.
                    </p>

                </div>

            </div>

        </section>


        <!-- =====================================================
             BEFORE / AFTER CONCEPT
        ====================================================== -->
        <section
            class="border-b border-[#191919]/10 bg-[#FFF8FA]"
        >

            <div
                class="mx-auto max-w-[1700px] px-6 py-28 sm:px-10 md:py-40 lg:px-16"
            >

                <div
                    class="grid gap-16 lg:grid-cols-[0.35fr_1fr]"
                >

                    <!-- Heading -->
                    <div>

                        <span
                            class="text-[9px] font-bold uppercase tracking-[0.35em] text-[#E85D75]"
                        >
                            04 — Transformation
                        </span>


                        <h2
                            class="mt-6 text-5xl font-semibold leading-[0.95] tracking-[-0.06em] sm:text-6xl"
                        >
                            Before
                            <span
                                class="font-serif italic text-[#E85D75]"
                            >
                                meets
                            </span>
                            after.
                        </h2>


                        <p
                            class="mt-7 max-w-sm text-sm leading-7 text-[#77686D]"
                        >
                            Editing bukan tentang mengubah semuanya.
                            Kadang, hanya perlu menemukan kembali
                            keindahan yang sudah ada.
                        </p>

                    </div>


                    <!-- Before / After visual -->
                    <div
                        class="relative"
                    >

                        <div
                            class="grid gap-4 sm:grid-cols-2"
                        >

                            <!-- Before -->
                            <div
                                class="group relative overflow-hidden rounded-[2rem] bg-[#E5D9DC]"
                            >

                                <div
                                    class="aspect-[0.85]"
                                >

                                    <img
                                        src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=1000&q=85"
                                        alt="Portrait before editing"
                                        class="h-full w-full object-cover grayscale-[0.3] transition duration-700 group-hover:scale-105"
                                    />

                                </div>


                                <div
                                    class="absolute left-5 top-5 rounded-full bg-white/85 px-4 py-2 text-[8px] font-bold uppercase tracking-[0.25em] text-[#625357] backdrop-blur-md"
                                >
                                    Original
                                </div>

                            </div>


                            <!-- After -->
                            <div
                                class="group relative mt-8 overflow-hidden rounded-[2rem] bg-[#F4A6B8] sm:mt-16"
                            >

                                <div
                                    class="aspect-[0.85]"
                                >

                                    <img
                                        src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=1000&q=85"
                                        alt="Portrait after editing"
                                        class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                    />

                                </div>


                                <!-- Pink artistic overlay -->
                                <div
                                    class="absolute inset-0 bg-[#E85D75]/10 mix-blend-multiply"
                                ></div>


                                <div
                                    class="absolute left-5 top-5 rounded-full bg-[#E85D75] px-4 py-2 text-[8px] font-bold uppercase tracking-[0.25em] text-white"
                                >
                                    Teras Memori
                                </div>

                            </div>

                        </div>


                        <!-- Center symbol -->
                        <div
                            class="absolute left-1/2 top-1/2 z-20 flex h-14 w-14 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white text-[#E85D75] shadow-xl"
                        >
                            →
                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             PROCESS
        ====================================================== -->
        <section
            class="border-b border-[#191919]/10 bg-white"
        >

            <div
                class="mx-auto max-w-[1700px] px-6 py-28 sm:px-10 md:py-40 lg:px-16"
            >

                <div
                    class="mb-20 flex flex-col justify-between gap-8 md:flex-row md:items-end"
                >

                    <div>

                        <span
                            class="text-[9px] font-bold uppercase tracking-[0.35em] text-[#E85D75]"
                        >
                            05 — Our Process
                        </span>

                        <h2
                            class="mt-6 text-5xl font-semibold tracking-[-0.06em] sm:text-6xl md:text-7xl"
                        >
                            From raw
                            <span
                                class="font-serif italic text-[#E85D75]"
                            >
                                to refined.
                            </span>
                        </h2>

                    </div>


                    <p
                        class="max-w-sm text-sm leading-7 text-[#77696D]"
                    >
                        A simple process built around communication,
                        precision and creative intention.
                    </p>

                </div>


                <!-- Process -->
                <div
                    class="grid gap-px overflow-hidden rounded-[2rem] border border-[#191919]/10 bg-[#191919]/10 md:grid-cols-3"
                >

                    <!-- 01 -->
                    <div
                        class="bg-[#FFF8FA] p-8 md:p-12"
                    >

                        <div
                            class="flex items-center justify-between"
                        >

                            <span
                                class="text-xs font-bold text-[#E85D75]"
                            >
                                01
                            </span>

                            <span
                                class="text-xl text-[#E85D75]/50"
                            >
                                ✦
                            </span>

                        </div>


                        <h3
                            class="mt-20 text-2xl font-semibold tracking-[-0.03em]"
                        >
                            Tell us your story.
                        </h3>


                        <p
                            class="mt-4 text-sm leading-7 text-[#77696D]"
                        >
                            Ceritakan kebutuhan, mood dan hasil
                            visual yang kamu bayangkan.
                        </p>

                    </div>


                    <!-- 02 -->
                    <div
                        class="bg-white p-8 md:p-12"
                    >

                        <div
                            class="flex items-center justify-between"
                        >

                            <span
                                class="text-xs font-bold text-[#E85D75]"
                            >
                                02
                            </span>

                            <span
                                class="text-xl text-[#E85D75]/50"
                            >
                                ✦
                            </span>

                        </div>


                        <h3
                            class="mt-20 text-2xl font-semibold tracking-[-0.03em]"
                        >
                            We create.
                        </h3>


                        <p
                            class="mt-4 text-sm leading-7 text-[#77696D]"
                        >
                            Kami mengolah setiap detail dengan
                            pendekatan visual yang sesuai karakter
                            foto.
                        </p>

                    </div>


                    <!-- 03 -->
                    <div
                        class="bg-[#191919] p-8 text-white md:p-12"
                    >

                        <div
                            class="flex items-center justify-between"
                        >

                            <span
                                class="text-xs font-bold text-[#F4A6B8]"
                            >
                                03
                            </span>

                            <span
                                class="text-xl text-[#F4A6B8]"
                            >
                                ✦
                            </span>

                        </div>


                        <h3
                            class="mt-20 text-2xl font-semibold tracking-[-0.03em]"
                        >
                            Keep the memory.
                        </h3>


                        <p
                            class="mt-4 text-sm leading-7 text-white/45"
                        >
                            Hasil akhir siap digunakan, dibagikan,
                            dicetak, atau disimpan.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FINAL ARTIST CTA
        ====================================================== -->
        <section
            class="relative overflow-hidden bg-[#E85D75]"
        >

            <!-- Large typography -->
            <div
                class="pointer-events-none absolute -bottom-16 left-0 select-none text-[24vw] font-bold leading-none tracking-[-0.1em] text-white/[0.06]"
            >
                CREATE
            </div>


            <!-- Circles -->
            <div
                class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full border border-white/20"
            ></div>

            <div
                class="pointer-events-none absolute -right-4 top-20 h-64 w-64 rounded-full border border-white/15"
            ></div>


            <div
                class="relative mx-auto max-w-[1700px] px-6 py-32 sm:px-10 md:py-44 lg:px-16"
            >

                <div
                    class="max-w-5xl"
                >

                    <span
                        class="text-[9px] font-bold uppercase tracking-[0.4em] text-white/65"
                    >
                        Let's make something beautiful
                    </span>


                    <h2
                        class="mt-8 text-6xl font-semibold leading-[0.88] tracking-[-0.07em] text-white sm:text-7xl md:text-9xl"
                    >
                        Your photo.
                        <br />

                        <span
                            class="font-serif font-normal italic text-white/55"
                        >
                            Our canvas.
                        </span>
                    </h2>


                    <div
                        class="mt-12 flex flex-col justify-between gap-10 border-t border-white/20 pt-8 md:flex-row md:items-end"
                    >

                        <p
                            class="max-w-md text-sm leading-7 text-white/70"
                        >
                            Punya foto yang ingin dibuat lebih
                            spesial? Mari ubah menjadi sesuatu
                            yang layak untuk diingat.
                        </p>


                        <RouterLink
                            :to="{ name: 'order' }"
                            class="group inline-flex items-center gap-5 self-start rounded-full bg-white px-8 py-5 text-[10px] font-bold uppercase tracking-[0.25em] text-[#E85D75] transition duration-500 hover:-translate-y-1 hover:bg-[#191919] hover:text-white"
                        >

                            Start a project

                            <span
                                class="transition duration-500 group-hover:translate-x-2"
                            >
                                →
                            </span>

                        </RouterLink>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FOOTER NOTE
        ====================================================== -->
        <div
            class="flex flex-col justify-between gap-4 bg-[#FFF8FA] px-6 py-8 text-[8px] font-bold uppercase tracking-[0.3em] text-[#A28D92] sm:flex-row sm:px-10 lg:px-16"
        >

            <span>
                Teras Memori
            </span>

            <span>
                Visual Art / Photo Editing
            </span>

            <span>
                © {{ new Date().getFullYear() }}
            </span>

        </div>

    </div>
</template>


<style scoped>
@keyframes marquee {
    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(-50%);
    }
}
</style>
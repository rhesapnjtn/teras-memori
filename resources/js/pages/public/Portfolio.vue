```vue
<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import api from '../../services/api'

const portfolios = ref([])
const loading = ref(true)
const errorMessage = ref('')
const activeCategory = ref('All')
const selectedPortfolio = ref(null)

const fetchPortfolios = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/portfolios')

        portfolios.value = response.data.data || []
    } catch (error) {
        console.error('Failed to load portfolios:', error)

        errorMessage.value =
            'Unable to load our portfolio right now. Please try again.'
    } finally {
        loading.value = false
    }
}

const categories = computed(() => {
    const values = portfolios.value
        .map((portfolio) => portfolio.category)
        .filter(Boolean)

    return ['All', ...new Set(values)]
})

const filteredPortfolios = computed(() => {
    if (activeCategory.value === 'All') {
        return portfolios.value
    }

    return portfolios.value.filter(
        (portfolio) => portfolio.category === activeCategory.value
    )
})

const openPortfolio = (portfolio) => {
    selectedPortfolio.value = portfolio
    document.body.style.overflow = 'hidden'
}

const closePortfolio = () => {
    selectedPortfolio.value = null
    document.body.style.overflow = ''
}

const handleKeydown = (event) => {
    if (event.key === 'Escape' && selectedPortfolio.value) {
        closePortfolio()
    }
}

onMounted(() => {
    fetchPortfolios()
    window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
    document.body.style.overflow = ''
})
</script>

<template>
    <div class="min-h-screen overflow-hidden bg-[#FFF8FA] text-[#191919]">

        <!-- =====================================================
             HERO
        ====================================================== -->
        <section
            class="relative overflow-hidden border-b border-[#191919]/10"
        >

            <!-- Editorial grid -->
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.035]"
                style="
                    background-image:
                        linear-gradient(rgba(25,25,25,.5) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(25,25,25,.5) 1px, transparent 1px);
                    background-size: 80px 80px;
                "
            ></div>

            <!-- Decorative circles -->
            <div
                class="pointer-events-none absolute -left-32 top-10 h-[500px] w-[500px] rounded-full border border-[#E85D75]/10"
            ></div>

            <div
                class="pointer-events-none absolute -left-10 top-40 h-[300px] w-[300px] rounded-full bg-[#F4A6B8]/15 blur-[100px]"
            ></div>

            <div
                class="pointer-events-none absolute -right-32 -top-32 h-[450px] w-[450px] rounded-full border border-[#191919]/[0.04]"
            ></div>


            <div
                class="relative mx-auto max-w-[1600px] px-6 pb-24 pt-20 sm:px-10 md:pb-32 md:pt-28 lg:px-16"
            >

                <!-- Label -->
                <div class="mb-10 flex items-center gap-4">

                    <span
                        class="h-px w-12 bg-[#E85D75]"
                    ></span>

                    <span
                        class="text-[10px] uppercase tracking-[0.4em] text-[#191919]/40"
                    >
                        03 — Selected Work
                    </span>

                </div>


                <!-- Heading -->
                <div
                    class="grid gap-14 lg:grid-cols-[1fr_250px] lg:items-end"
                >

                    <div>

                        <h1
                            class="max-w-6xl text-[17vw] font-medium leading-[0.75] tracking-[-0.085em] sm:text-[13vw] lg:text-[10vw]"
                        >
                            OUR
                            <span class="text-[#191919]/15">
                                WORK.
                            </span>
                        </h1>

                    </div>


                    <!-- Artwork number -->
                    <div class="hidden lg:block">

                        <div class="relative mx-auto h-36 w-36">

                            <div
                                class="absolute inset-0 rounded-full border border-[#191919]/10"
                            ></div>

                            <div
                                class="absolute inset-4 rounded-full border border-[#E85D75]/20"
                            ></div>

                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <span
                                    class="text-[9px] uppercase tracking-[0.35em] text-[#191919]/35"
                                >
                                    TM / 03
                                </span>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- Intro -->
                <div
                    class="mt-16 grid gap-8 border-t border-[#191919]/10 pt-8 md:grid-cols-[1fr_300px]"
                >

                    <p
                        class="max-w-2xl text-base leading-7 text-[#191919]/55 md:text-lg"
                    >
                        A collection of photographs we've edited,
                        restored and refined. Every image has its own
                        story. Our work exists to make that story
                        worth remembering.
                    </p>

                    <div
                        class="text-xs leading-6 text-[#191919]/30 md:text-right"
                    >
                        Selected projects
                        <br />
                        Teras Memori Studio
                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FILTER
        ====================================================== -->
        <section
            class="border-b border-[#191919]/10"
        >

            <div
                class="mx-auto flex max-w-[1600px] flex-wrap items-center gap-2 px-6 py-6 sm:px-10 lg:px-16"
            >

                <button
                    v-for="category in categories"
                    :key="category"
                    type="button"
                    @click="activeCategory = category"
                    class="rounded-full border px-5 py-2.5 text-[10px] uppercase tracking-[0.25em] transition duration-300"
                    :class="
                        activeCategory === category
                            ? 'border-[#191919] bg-[#191919] text-white'
                            : 'border-[#191919]/10 text-[#191919]/40 hover:border-[#E85D75]/50 hover:text-[#E85D75]'
                    "
                >
                    {{ category }}
                </button>

            </div>

        </section>


        <!-- =====================================================
             PORTFOLIO GRID
        ====================================================== -->
        <section>

            <div
                class="mx-auto max-w-[1600px] px-6 py-20 sm:px-10 md:py-28 lg:px-16"
            >

                <!-- Loading -->
                <div
                    v-if="loading"
                    class="grid gap-x-6 gap-y-16 md:grid-cols-2"
                >

                    <div
                        v-for="item in 4"
                        :key="item"
                        class="animate-pulse"
                    >

                        <div
                            class="aspect-[4/5] bg-[#191919]/[0.04]"
                        ></div>

                        <div
                            class="mt-5 h-5 w-2/3 bg-[#191919]/[0.05]"
                        ></div>

                        <div
                            class="mt-3 h-3 w-1/3 bg-[#191919]/[0.04]"
                        ></div>

                    </div>

                </div>


                <!-- Error -->
                <div
                    v-else-if="errorMessage"
                    class="border-y border-[#191919]/10 py-20 text-center"
                >

                    <p class="text-sm text-[#191919]/45">
                        {{ errorMessage }}
                    </p>

                    <button
                        type="button"
                        @click="fetchPortfolios"
                        class="mt-6 rounded-full border border-[#191919]/15 px-6 py-3 text-xs uppercase tracking-[0.2em] transition duration-300 hover:border-[#191919]/40 hover:bg-[#191919]/5"
                    >
                        Try again
                    </button>

                </div>


                <!-- Empty -->
                <div
                    v-else-if="filteredPortfolios.length === 0"
                    class="border-y border-[#191919]/10 py-20 text-center"
                >

                    <p class="text-sm text-[#191919]/40">
                        No projects found in this category.
                    </p>

                </div>


                <!-- Portfolio -->
                <div
                    v-else
                    class="grid gap-x-6 gap-y-20 md:grid-cols-2"
                >

                    <article
                        v-for="(portfolio, index) in filteredPortfolios"
                        :key="portfolio.id"
                        class="group cursor-pointer"
                        @click="openPortfolio(portfolio)"
                    >

                        <!-- Image -->
                        <div
                            class="relative overflow-hidden bg-[#F4F0F1]"
                            :class="
                                index % 3 === 1
                                    ? 'aspect-[4/5] md:mt-24'
                                    : 'aspect-[4/5]'
                            "
                        >

                            <!-- Number -->
                            <div
                                class="absolute left-5 top-5 z-10 flex h-9 w-9 items-center justify-center rounded-full border border-white/40 bg-[#191919]/20 backdrop-blur-sm"
                            >
                                <span
                                    class="text-[9px] text-white/80"
                                >
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>
                            </div>


                            <!-- Image -->
                            <img
                                v-if="portfolio.image"
                                :src="portfolio.image"
                                :alt="portfolio.title"
                                class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                                loading="lazy"
                            />


                            <!-- Fallback -->
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-[#F4F0F1]"
                            >

                                <div class="text-center">

                                    <span
                                        class="text-[10px] uppercase tracking-[0.3em] text-[#191919]/20"
                                    >
                                        Teras Memori
                                    </span>

                                    <div
                                        class="mx-auto mt-4 h-px w-10 bg-[#E85D75]"
                                    ></div>

                                </div>

                            </div>


                            <!-- Overlay -->
                            <div
                                class="absolute inset-0 bg-[#191919]/0 transition duration-500 group-hover:bg-[#191919]/20"
                            ></div>


                            <!-- View -->
                            <div
                                class="absolute bottom-5 right-5 flex h-14 w-14 translate-y-3 items-center justify-center rounded-full bg-white text-[#191919] opacity-0 shadow-lg transition duration-500 group-hover:translate-y-0 group-hover:opacity-100"
                            >
                                <span class="text-lg">
                                    ↗
                                </span>
                            </div>

                        </div>


                        <!-- Info -->
                        <div
                            class="mt-5 flex items-start justify-between gap-5"
                        >

                            <div>

                                <h2
                                    class="text-xl font-medium tracking-[-0.025em] text-[#191919]/80 transition duration-300 group-hover:text-[#191919] sm:text-2xl"
                                >
                                    {{ portfolio.title }}
                                </h2>


                                <p
                                    v-if="portfolio.description"
                                    class="mt-2 max-w-md text-sm leading-6 text-[#191919]/35"
                                >
                                    {{ portfolio.description }}
                                </p>

                            </div>


                            <span
                                v-if="portfolio.category"
                                class="shrink-0 pt-1 text-[9px] uppercase tracking-[0.25em] text-[#191919]/25 transition-colors duration-300 group-hover:text-[#E85D75]"
                            >
                                {{ portfolio.category }}
                            </span>

                        </div>

                    </article>

                </div>

            </div>

        </section>


        <!-- =====================================================
             STATEMENT
        ====================================================== -->
        <section
            class="border-t border-[#191919]/10"
        >

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div
                    class="grid gap-14 lg:grid-cols-[0.4fr_1fr]"
                >

                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            Our philosophy
                        </span>

                        <div
                            class="mt-8 hidden h-px w-20 bg-[#E85D75] lg:block"
                        ></div>

                    </div>


                    <div>

                        <p
                            class="max-w-5xl text-4xl font-medium leading-[1.03] tracking-[-0.055em] text-[#191919]/80 sm:text-5xl md:text-6xl lg:text-7xl"
                        >
                            Good editing should never make a photograph
                            feel
                            <span class="text-[#191919]/20">
                                edited.
                            </span>
                        </p>


                        <p
                            class="mt-8 max-w-2xl text-sm leading-7 text-[#191919]/40 md:text-base"
                        >
                            We believe the best work is often invisible.
                            Color, light, texture and detail should work
                            together naturally — keeping the photograph's
                            original character intact.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CREATIVE STATEMENT
        ====================================================== -->
        <section
            class="border-t border-[#191919]/10"
        >

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div
                    class="relative overflow-hidden rounded-[2rem] bg-[#191919]"
                >

                    <!-- Decorative pink shape -->
                    <div
                        class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full border border-[#E85D75]/30"
                    ></div>

                    <div
                        class="pointer-events-none absolute -right-10 top-10 h-64 w-64 rounded-full border border-white/10"
                    ></div>

                    <div
                        class="pointer-events-none absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-[#E85D75]/10 blur-[90px]"
                    ></div>


                    <div
                        class="relative grid gap-12 px-6 py-20 sm:px-10 md:px-16 md:py-28 lg:grid-cols-[0.3fr_1fr] lg:items-end"
                    >

                        <div>

                            <span
                                class="text-[10px] uppercase tracking-[0.35em] text-white/30"
                            >
                                03 / The archive
                            </span>

                            <p
                                class="mt-5 text-xs leading-6 text-white/25"
                            >
                                Every project becomes part of
                                the visual language of Teras Memori.
                            </p>

                        </div>


                        <div>

                            <p
                                class="text-4xl font-medium leading-[0.95] tracking-[-0.055em] text-white sm:text-5xl md:text-7xl"
                            >
                                Images are more than
                                <span class="text-white/25">
                                    pictures.
                                </span>

                                <br />

                                They are pieces of
                                <span class="text-[#E85D75]">
                                    memory.
                                </span>
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CTA
        ====================================================== -->
        <section
            class="border-t border-[#191919]/10"
        >

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div
                    class="group relative overflow-hidden rounded-[2rem] bg-[#E85D75]"
                >

                    <!-- Decorative circles -->
                    <div
                        class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full border border-white/20 transition-transform duration-1000 group-hover:scale-110"
                    ></div>

                    <div
                        class="pointer-events-none absolute -right-8 top-8 h-60 w-60 rounded-full border border-white/15 transition-transform duration-1000 group-hover:scale-125"
                    ></div>

                    <div
                        class="pointer-events-none absolute -bottom-20 -left-20 h-60 w-60 rounded-full bg-white/10 blur-[70px]"
                    ></div>


                    <div
                        class="relative px-6 py-24 text-center text-white sm:px-10 md:py-32"
                    >

                        <span
                            class="text-[10px] uppercase tracking-[0.4em] text-white/60"
                        >
                            Have a project?
                        </span>


                        <h2
                            class="mx-auto mt-6 max-w-5xl text-5xl font-medium leading-[0.88] tracking-[-0.065em] sm:text-6xl md:text-8xl"
                        >
                            Let's create something
                            <span class="text-white/45">
                                worth remembering.
                            </span>
                        </h2>


                        <p
                            class="mx-auto mt-8 max-w-md text-sm leading-6 text-white/65"
                        >
                            Have a photograph in mind?
                            Tell us what you need and let's
                            make it meaningful.
                        </p>


                        <RouterLink
                            :to="{ name: 'order' }"
                            class="group/button mt-10 inline-flex items-center gap-6 rounded-full bg-white px-8 py-5 text-xs font-semibold uppercase tracking-[0.2em] text-[#191919] transition duration-500 hover:scale-105"
                        >

                            Start a project

                            <span
                                class="transition-transform duration-500 group-hover/button:translate-x-2"
                            >
                                →
                            </span>

                        </RouterLink>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             PORTFOLIO MODAL
        ====================================================== -->
        <Transition name="modal">

            <div
                v-if="selectedPortfolio"
                class="fixed inset-0 z-[100] overflow-y-auto bg-[#191919]/90 px-4 py-6 backdrop-blur-md sm:px-8 sm:py-10"
                @click.self="closePortfolio"
            >

                <div
                    class="mx-auto flex min-h-full max-w-6xl items-center justify-center"
                >

                    <div class="relative w-full">

                        <!-- Close -->
                        <button
                            type="button"
                            @click="closePortfolio"
                            class="absolute right-0 top-0 z-20 flex h-12 w-12 items-center justify-center rounded-full border border-white/15 bg-[#191919]/50 text-white/60 backdrop-blur-sm transition hover:border-white/40 hover:text-white"
                            aria-label="Close"
                        >
                            ×
                        </button>


                        <!-- Image -->
                        <div
                            class="overflow-hidden bg-[#F4F0F1]"
                        >

                            <img
                                v-if="selectedPortfolio.image"
                                :src="selectedPortfolio.image"
                                :alt="selectedPortfolio.title"
                                class="max-h-[75vh] w-full object-contain"
                            />


                            <div
                                v-else
                                class="flex aspect-video items-center justify-center"
                            >

                                <span
                                    class="text-xs uppercase tracking-[0.3em] text-[#191919]/20"
                                >
                                    Teras Memori
                                </span>

                            </div>

                        </div>


                        <!-- Detail -->
                        <div
                            class="grid gap-6 border-x border-b border-white/10 bg-[#111] p-6 sm:p-8 md:grid-cols-[1fr_auto] md:items-end"
                        >

                            <div>

                                <span
                                    v-if="selectedPortfolio.category"
                                    class="text-[9px] uppercase tracking-[0.3em] text-white/30"
                                >
                                    {{ selectedPortfolio.category }}
                                </span>


                                <h2
                                    class="mt-3 text-3xl font-medium tracking-[-0.04em] text-white sm:text-4xl"
                                >
                                    {{ selectedPortfolio.title }}
                                </h2>


                                <p
                                    v-if="selectedPortfolio.description"
                                    class="mt-4 max-w-2xl text-sm leading-7 text-white/35"
                                >
                                    {{ selectedPortfolio.description }}
                                </p>

                            </div>


                            <div class="text-left md:text-right">

                                <p
                                    class="text-[9px] uppercase tracking-[0.3em] text-white/20"
                                >
                                    Project
                                </p>

                                <p class="mt-2 text-sm text-white/50">
                                    #{{ String(selectedPortfolio.id).padStart(3, '0') }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </Transition>


        <!-- =====================================================
             FOOTER
        ====================================================== -->
        <div
            class="mx-auto flex max-w-[1600px] flex-col justify-between gap-4 border-t border-[#191919]/10 px-6 py-8 text-[9px] uppercase tracking-[0.3em] text-[#191919]/25 sm:flex-row sm:px-10 lg:px-16"
        >

            <span>
                Teras Memori
            </span>

            <span>
                Selected Work
            </span>

            <span>
                © {{ new Date().getFullYear() }}
            </span>

        </div>

    </div>
</template>


<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
```

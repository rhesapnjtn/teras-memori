<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../services/api'

const portfolios = ref([])
const loading = ref(true)
const errorMessage = ref('')

const selectedCategory = ref('All')
const selectedPortfolio = ref(null)
const showDetail = ref(false)

/*
|--------------------------------------------------------------------------
| Fetch Public Portfolios
|--------------------------------------------------------------------------
*/

const fetchPortfolios = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/portfolios')

        portfolios.value = response.data?.data || []
    } catch (error) {
        console.error(error)

        errorMessage.value =
            error.response?.data?.message ||
            'Unable to load our portfolio.'
    } finally {
        loading.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

const categories = computed(() => {
    const values = portfolios.value
        .map((portfolio) => portfolio.category)
        .filter(Boolean)

    return ['All', ...new Set(values)]
})

/*
|--------------------------------------------------------------------------
| Filtered Portfolios
|--------------------------------------------------------------------------
*/

const filteredPortfolios = computed(() => {
    if (selectedCategory.value === 'All') {
        return portfolios.value
    }

    return portfolios.value.filter(
        (portfolio) =>
            portfolio.category === selectedCategory.value
    )
})

/*
|--------------------------------------------------------------------------
| Image URL
|--------------------------------------------------------------------------
*/

const imageUrl = (portfolio) => {
    if (!portfolio?.image) {
        return null
    }

    if (
        portfolio.image.startsWith('http://') ||
        portfolio.image.startsWith('https://') ||
        portfolio.image.startsWith('/')
    ) {
        return portfolio.image
    }

    return `/storage/${portfolio.image}`
}

/*
|--------------------------------------------------------------------------
| Open Detail
|--------------------------------------------------------------------------
*/

const openDetail = async (portfolio) => {
    selectedPortfolio.value = portfolio
    showDetail.value = true

    try {
        const response = await api.get(
            `/portfolios/${portfolio.id}`
        )

        selectedPortfolio.value =
            response.data?.data || portfolio
    } catch (error) {
        console.error(error)
    }
}

/*
|--------------------------------------------------------------------------
| Close Detail
|--------------------------------------------------------------------------
*/

const closeDetail = () => {
    showDetail.value = false
    selectedPortfolio.value = null
}

/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(fetchPortfolios)
</script>

<template>
    <main class="bg-[#FFF8FA] text-[#191919]">

        <!-- ========================================================= -->
        <!-- HERO -->
        <!-- ========================================================= -->

        <section
            class="relative overflow-hidden border-b border-[#191919]/10 px-6 pb-20 pt-24 md:px-10 md:pb-28 md:pt-32"
        >
            <!-- Background Circle -->

            <div
                class="pointer-events-none absolute -right-32 -top-32 h-[420px] w-[420px] rounded-full border border-[#E85D75]/10"
            ></div>

            <div
                class="pointer-events-none absolute -right-20 -top-20 h-[260px] w-[260px] rounded-full border border-[#E85D75]/10"
            ></div>

            <div class="mx-auto max-w-7xl">

                <div
                    class="grid gap-12 lg:grid-cols-[1.3fr_0.7fr] lg:items-end"
                >

                    <!-- LEFT -->

                    <div>
                        <p
                            class="text-[9px] font-semibold uppercase tracking-[0.4em] text-[#E85D75]"
                        >
                            Selected Work
                        </p>

                        <h1
                            class="mt-6 max-w-4xl text-5xl font-medium leading-[0.9] tracking-[-0.07em] sm:text-7xl lg:text-8xl"
                        >
                            STORIES
                            <br />

                            <span class="text-[#E85D75]">
                                WORTH
                            </span>

                            <br />

                            REMEMBERING<span
                                class="text-[#E85D75]"
                            >.</span>
                        </h1>
                    </div>

                    <!-- RIGHT -->

                    <div class="lg:pb-2">

                        <div
                            class="border-l border-[#191919]/15 pl-6"
                        >
                            <p
                                class="max-w-sm text-sm leading-7 text-[#191919]/45"
                            >
                                A collection of photographs
                                transformed with care,
                                precision, and a little
                                creative direction.
                            </p>

                            <div
                                class="mt-8 flex items-center gap-5"
                            >
                                <span
                                    class="h-px w-10 bg-[#E85D75]"
                                ></span>

                                <span
                                    class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#191919]/35"
                                >
                                    Teras Memori Studio
                                </span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- ========================================================= -->
        <!-- CATEGORY FILTER -->
        <!-- ========================================================= -->

        <section
            class="sticky top-0 z-20 border-b border-[#191919]/10 bg-[#FFF8FA]/90 px-6 py-5 backdrop-blur-xl md:px-10"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >

                <div
                    class="flex flex-wrap items-center gap-2"
                >
                    <button
                        v-for="category in categories"
                        :key="category"
                        type="button"
                        class="px-4 py-2 text-[8px] font-semibold uppercase tracking-[0.2em] transition"
                        :class="
                            selectedCategory === category
                                ? 'bg-[#191919] text-white'
                                : 'border border-[#191919]/10 text-[#191919]/45 hover:border-[#E85D75]/30 hover:text-[#E85D75]'
                        "
                        @click="
                            selectedCategory = category
                        "
                    >
                        {{ category }}
                    </button>
                </div>

                <p
                    class="text-[8px] uppercase tracking-[0.25em] text-[#191919]/25"
                >
                    {{ filteredPortfolios.length }}
                    Selected Works
                </p>

            </div>
        </section>

        <!-- ========================================================= -->
        <!-- PORTFOLIO -->
        <!-- ========================================================= -->

        <section
            class="px-6 py-20 md:px-10 md:py-28"
        >
            <div class="mx-auto max-w-7xl">

                <!-- LOADING -->

                <div
                    v-if="loading"
                    class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="index in 6"
                        :key="index"
                        class="animate-pulse"
                    >
                        <div
                            class="aspect-[4/5] bg-[#F4F0F1]"
                        ></div>

                        <div class="mt-5">
                            <div
                                class="h-2 w-20 bg-[#191919]/5"
                            ></div>

                            <div
                                class="mt-3 h-5 w-2/3 bg-[#191919]/5"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- ERROR -->

                <div
                    v-else-if="errorMessage"
                    class="border border-red-200 bg-red-50 px-6 py-10 text-center"
                >
                    <p
                        class="text-sm text-red-500"
                    >
                        {{ errorMessage }}
                    </p>

                    <button
                        type="button"
                        class="mt-5 border border-red-300 px-5 py-3 text-[8px] font-semibold uppercase tracking-[0.2em] text-red-500 transition hover:bg-red-500 hover:text-white"
                        @click="fetchPortfolios"
                    >
                        Try again
                    </button>
                </div>

                <!-- EMPTY -->

                <div
                    v-else-if="!filteredPortfolios.length"
                    class="flex min-h-[420px] items-center justify-center border border-[#191919]/10 bg-white px-6 text-center"
                >
                    <div>

                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-[#E85D75]/20 text-xl text-[#E85D75]"
                        >
                            TM
                        </div>

                        <p
                            class="mt-7 text-[9px] font-semibold uppercase tracking-[0.3em] text-[#191919]/30"
                        >
                            No selected work
                        </p>

                        <h2
                            class="mt-3 text-2xl font-medium tracking-[-0.04em]"
                        >
                            Nothing here yet<span
                                class="text-[#E85D75]"
                            >.</span>
                        </h2>

                        <p
                            class="mx-auto mt-3 max-w-sm text-sm leading-6 text-[#191919]/35"
                        >
                            New work will appear here as
                            soon as it is published by the
                            studio.
                        </p>

                    </div>
                </div>

                <!-- GRID -->

                <div
                    v-else
                    class="grid gap-x-6 gap-y-16 md:grid-cols-2 lg:grid-cols-3"
                >

                    <article
                        v-for="(portfolio, index) in filteredPortfolios"
                        :key="portfolio.id"
                        class="group cursor-pointer"
                        @click="openDetail(portfolio)"
                    >

                        <!-- IMAGE -->

                        <div
                            class="relative aspect-[4/5] overflow-hidden bg-[#F4F0F1]"
                        >

                            <img
                                v-if="imageUrl(portfolio)"
                                :src="imageUrl(portfolio)"
                                :alt="
                                    portfolio.title ||
                                    'Teras Memori Portfolio'
                                "
                                class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                            />

                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center"
                            >
                                <div class="text-center">

                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-[#191919]/10 text-sm text-[#E85D75]"
                                    >
                                        TM
                                    </div>

                                    <p
                                        class="mt-4 text-[8px] uppercase tracking-[0.25em] text-[#191919]/25"
                                    >
                                        No Image
                                    </p>

                                </div>
                            </div>

                            <!-- Overlay -->

                            <div
                                class="absolute inset-0 bg-[#191919]/0 transition duration-500 group-hover:bg-[#191919]/10"
                            ></div>

                            <!-- Number -->

                            <div
                                class="absolute left-5 top-5"
                            >
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-[#FFF8FA]/90 text-[8px] font-semibold backdrop-blur-sm"
                                >
                                    {{
                                        String(index + 1).padStart(
                                            2,
                                            '0'
                                        )
                                    }}
                                </span>
                            </div>

                            <!-- View -->

                            <div
                                class="absolute bottom-5 right-5 flex h-11 w-11 translate-y-3 items-center justify-center rounded-full bg-white text-sm opacity-0 shadow-lg transition duration-500 group-hover:translate-y-0 group-hover:opacity-100"
                            >
                                ↗
                            </div>

                        </div>

                        <!-- CONTENT -->

                        <div class="mt-5">

                            <div
                                class="flex items-center justify-between gap-4"
                            >

                                <p
                                    class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#E85D75]"
                                >
                                    {{
                                        portfolio.category ||
                                        'Selected Work'
                                    }}
                                </p>

                                <span
                                    class="text-[8px] uppercase tracking-[0.2em] text-[#191919]/20"
                                >
                                    TM
                                </span>

                            </div>

                            <h2
                                class="mt-3 text-2xl font-medium tracking-[-0.05em] transition group-hover:text-[#E85D75]"
                            >
                                {{
                                    portfolio.title ||
                                    'Untitled Work'
                                }}
                            </h2>

                            <p
                                v-if="portfolio.description"
                                class="mt-3 line-clamp-2 text-sm leading-6 text-[#191919]/40"
                            >
                                {{
                                    portfolio.description
                                }}
                            </p>

                            <div
                                class="mt-5 flex items-center gap-3"
                            >
                                <span
                                    class="h-px w-7 bg-[#191919]/15 transition-all duration-500 group-hover:w-12 group-hover:bg-[#E85D75]"
                                ></span>

                                <span
                                    class="text-[8px] font-semibold uppercase tracking-[0.22em] text-[#191919]/30"
                                >
                                    View project
                                </span>
                            </div>

                        </div>

                    </article>

                </div>

            </div>
        </section>

        <!-- ========================================================= -->
        <!-- CLOSING -->
        <!-- ========================================================= -->

        <section
            class="border-t border-[#191919]/10 bg-[#191919] px-6 py-24 text-white md:px-10 md:py-32"
        >
            <div class="mx-auto max-w-7xl">

                <div
                    class="grid gap-10 lg:grid-cols-[1fr_auto] lg:items-end"
                >

                    <div>
                        <p
                            class="text-[9px] font-semibold uppercase tracking-[0.35em] text-[#F4A6B8]"
                        >
                            Your story
                        </p>

                        <h2
                            class="mt-6 max-w-4xl text-4xl font-medium leading-[0.95] tracking-[-0.06em] sm:text-6xl"
                        >
                            HAVE A PHOTO
                            <br />
                            WORTH
                            <span class="text-[#F4A6B8]">
                                REMEMBERING?
                            </span>
                        </h2>
                    </div>

                    <RouterLink
                        to="/order"
                        class="inline-flex items-center justify-center gap-4 border border-white/20 px-7 py-4 text-[8px] font-semibold uppercase tracking-[0.25em] transition hover:border-[#F4A6B8] hover:bg-[#F4A6B8] hover:text-[#191919]"
                    >
                        Start a project

                        <span class="text-base">
                            →
                        </span>
                    </RouterLink>

                </div>

            </div>
        </section>

        <!-- ========================================================= -->
        <!-- DETAIL MODAL -->
        <!-- ========================================================= -->

        <Transition name="fade">

            <div
                v-if="
                    showDetail &&
                    selectedPortfolio
                "
                class="fixed inset-0 z-50 flex items-center justify-center bg-[#191919]/70 p-4 backdrop-blur-md"
                @click.self="closeDetail"
            >

                <div
                    class="relative max-h-[92vh] w-full max-w-6xl overflow-y-auto bg-[#FFF8FA]"
                >

                    <!-- HEADER -->

                    <div
                        class="sticky top-0 z-20 flex items-center justify-between border-b border-[#191919]/10 bg-[#FFF8FA]/90 px-6 py-5 backdrop-blur-xl md:px-8"
                    >

                        <div>

                            <p
                                class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#E85D75]"
                            >
                                Selected Work
                            </p>

                            <h2
                                class="mt-2 text-xl font-medium tracking-[-0.04em] md:text-2xl"
                            >
                                {{
                                    selectedPortfolio.title
                                }}<span
                                    class="text-[#E85D75]"
                                >.</span>
                            </h2>

                        </div>

                        <button
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-[#191919]/10 text-lg transition hover:border-[#E85D75]/30 hover:text-[#E85D75]"
                            @click="closeDetail"
                        >
                            ×
                        </button>

                    </div>

                    <!-- BODY -->

                    <div
                        class="grid lg:grid-cols-[1.2fr_0.8fr]"
                    >

                        <!-- IMAGE -->

                        <div
                            class="bg-[#F4F0F1]"
                        >

                            <img
                                v-if="
                                    imageUrl(
                                        selectedPortfolio
                                    )
                                "
                                :src="
                                    imageUrl(
                                        selectedPortfolio
                                    )
                                "
                                :alt="
                                    selectedPortfolio.title
                                "
                                class="max-h-[75vh] w-full object-contain"
                            />

                            <div
                                v-else
                                class="flex min-h-[500px] items-center justify-center"
                            >
                                <span
                                    class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/25"
                                >
                                    No image available
                                </span>
                            </div>

                        </div>

                        <!-- INFORMATION -->

                        <div
                            class="flex flex-col justify-between p-7 md:p-10"
                        >

                            <div>

                                <p
                                    class="text-[8px] font-semibold uppercase tracking-[0.3em] text-[#E85D75]"
                                >
                                    {{
                                        selectedPortfolio.category ||
                                        'Selected Work'
                                    }}
                                </p>

                                <h3
                                    class="mt-5 text-4xl font-medium leading-none tracking-[-0.06em] md:text-5xl"
                                >
                                    {{
                                        selectedPortfolio.title
                                    }}<span
                                        class="text-[#E85D75]"
                                    >.</span>
                                </h3>

                                <div
                                    v-if="
                                        selectedPortfolio.description
                                    "
                                    class="mt-8 border-t border-[#191919]/10 pt-7"
                                >

                                    <p
                                        class="text-[8px] font-semibold uppercase tracking-[0.25em] text-[#191919]/30"
                                    >
                                        About the project
                                    </p>

                                    <p
                                        class="mt-4 text-sm leading-7 text-[#191919]/50"
                                    >
                                        {{
                                            selectedPortfolio.description
                                        }}
                                    </p>

                                </div>

                            </div>

                            <div class="mt-12">

                                <div
                                    class="flex items-center gap-4"
                                >
                                    <span
                                        class="h-px w-10 bg-[#E85D75]"
                                    ></span>

                                    <span
                                        class="text-[8px] font-semibold uppercase tracking-[0.25em] text-[#191919]/30"
                                    >
                                        Teras Memori
                                    </span>
                                </div>

                                <p
                                    class="mt-5 text-xs leading-6 text-[#191919]/30"
                                >
                                    Every image deserves
                                    thoughtful attention.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </Transition>

    </main>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
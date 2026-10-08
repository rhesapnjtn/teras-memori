<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../services/api'

const services = ref([])
const loading = ref(true)
const errorMessage = ref('')

const fetchServices = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/services')

        services.value = response.data.data || []
    } catch (error) {
        console.error('Failed to load services:', error)

        errorMessage.value =
            'Unable to load our services right now. Please try again.'
    } finally {
        loading.value = false
    }
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(Number(price))
}

onMounted(fetchServices)
</script>

<template>
    <div class="min-h-screen overflow-hidden bg-[#FFF8FA] text-[#191919]">

        <!-- =====================================================
             HERO
        ====================================================== -->
        <section class="relative overflow-hidden border-b border-[#191919]/10">

            <!-- Editorial background -->
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.035]"
                style="
                    background-image:
                        linear-gradient(rgba(25,25,25,.5) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(25,25,25,.5) 1px, transparent 1px);
                    background-size: 80px 80px;
                "
            ></div>

            <!-- Decorative circle -->
            <div
                class="pointer-events-none absolute -right-32 -top-32 h-[520px] w-[520px] rounded-full border border-[#E85D75]/10"
            ></div>

            <div
                class="pointer-events-none absolute right-[-100px] top-[100px] h-[300px] w-[300px] rounded-full bg-[#F4A6B8]/20 blur-[100px]"
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
                            class="text-[9px] font-bold uppercase tracking-[0.35em] text-[#E85D75]"
                        >
                            02 — Layanan Kami
                        </span>

                </div>


                <!-- Main heading -->
                <div
                    class="grid gap-14 lg:grid-cols-[1fr_250px] lg:items-end"
                >

                    <div>

                        <h1
                            class="max-w-6xl text-[16vw] font-medium leading-[0.75] tracking-[-0.085em] sm:text-[13vw] lg:text-[10vw]"
                        >
                            LAYANAN
                            <span class="text-[#191919]/15">
                                KAMI.
                            </span>

                            <br />

                            TERAS
                            <span class="text-[#191919]/15">
                                MEMORI.
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
                                    TM / 02
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
                        Mulai dari koreksi halus hingga restorasi lengkap,
                        setiap layanan diperlakukan sebagai proses kreatif
                        yang dirancang untuk menjaga karakter dan emosi
                        di balik setiap foto.
                    </p>


                    <div
                        class="text-xs leading-6 text-[#191919]/35 md:text-right"
                    >
                        Professional image services
                        <br />
                        Crafted with intention.
                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             SERVICES LIST
        ====================================================== -->
        <section>

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-32 lg:px-16"
            >

                <!-- Section heading -->
                <div
                    class="mb-14 flex flex-col justify-between gap-8 md:flex-row md:items-end"
                >

                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/35"
                        >
                            Our practice
                        </span>

                        <h2
                            class="mt-5 max-w-xl text-5xl font-medium leading-[0.95] tracking-[-0.055em] sm:text-6xl"
                        >
                            Seni menghidupkan
                            <span class="text-[#191919]/20">
                                setiap kenangan.
                            </span>
                        </h2>

                    </div>


                    <p
                        class="max-w-sm text-sm leading-6 text-[#191919]/40"
                    >
                        Setiap foto berhak mendapatkan perhatian.
                        Pilih layanan yang paling sesuai untuk kebutuhan Anda.
                    </p>

                </div>


                <!-- Loading -->
                <div
                    v-if="loading"
                    class="border-y border-[#191919]/10"
                >

                    <div
                        v-for="item in 4"
                        :key="item"
                        class="h-40 animate-pulse border-b border-[#191919]/10 bg-[#191919]/[0.02] last:border-b-0"
                    ></div>

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
                        @click="fetchServices"
                        class="mt-6 rounded-full border border-[#191919]/15 px-6 py-3 text-xs uppercase tracking-[0.2em] transition duration-300 hover:border-[#191919]/40 hover:bg-[#191919]/5"
                    >
                        Try again
                    </button>

                </div>


                <!-- Empty -->
                <div
                    v-else-if="services.length === 0"
                    class="border-y border-[#191919]/10 py-20 text-center"
                >

                    <p class="text-sm text-[#191919]/40">
                        No services available at the moment.
                    </p>

                </div>


                <!-- Services -->
                <div
                    v-else
                    class="border-y border-[#191919]/10"
                >

                    <RouterLink
                        v-for="(service, index) in services"
                        :key="service.id"
                        :to="{
                            name: 'order',
                            query: { service: service.id }
                        }"
                        class="group relative block overflow-hidden border-b border-[#191919]/10 py-10 last:border-b-0 md:py-14"
                    >

                        <!-- Hover background -->
                        <div
                            class="pointer-events-none absolute inset-0 -translate-x-full bg-[#E85D75]/[0.035] transition-transform duration-700 ease-out group-hover:translate-x-0"
                        ></div>


                        <!-- Pink vertical accent -->
                        <div
                            class="pointer-events-none absolute bottom-0 left-0 top-0 w-px origin-bottom scale-y-0 bg-[#E85D75] transition-transform duration-500 group-hover:scale-y-100"
                        ></div>


                        <div
                            class="relative grid gap-8 lg:grid-cols-[100px_1fr_280px_80px] lg:items-center"
                        >

                            <!-- Number -->
                            <div>

                                <span
                                    class="text-xs tracking-[0.2em] text-[#191919]/25 transition-colors duration-500 group-hover:text-[#E85D75]"
                                >
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>

                            </div>


                            <!-- Service information -->
                            <div>

                                <div
                                    class="mb-3 flex items-center gap-3"
                                >

                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-[#E85D75] opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                                    ></span>

                                    <span
                                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/25"
                                    >
                                        Creative service
                                    </span>

                                </div>


                                <h2
                                    class="text-3xl font-medium tracking-[-0.045em] text-[#191919]/80 transition-all duration-500 group-hover:translate-x-3 group-hover:text-[#191919] sm:text-4xl md:text-5xl lg:text-6xl"
                                >
                                    {{ service.name }}
                                </h2>


                                <p
                                    class="mt-4 max-w-2xl text-sm leading-6 text-[#191919]/40 transition-colors duration-500 group-hover:text-[#191919]/55"
                                >
                                    {{ service.description }}
                                </p>

                            </div>


                            <!-- Price + duration -->
                            <div
                                class="grid grid-cols-2 gap-6 lg:block"
                            >

                                <div>

                                    <p
                                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/25"
                                    >
                                        Starting from
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-medium text-[#191919]/65"
                                    >
                                        Rp {{ formatPrice(service.price) }}
                                    </p>

                                </div>


                                <div class="lg:mt-6">

                                    <p
                                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/25"
                                    >
                                        Turnaround
                                    </p>

                                    <p
                                        class="mt-2 text-sm text-[#191919]/60"
                                    >
                                        {{ service.duration || 'Sesuai kebutuhan' }}
                                    </p>

                                </div>

                            </div>


                            <!-- Arrow -->
                            <div
                                class="flex justify-start lg:justify-end"
                            >

                                <span
                                    class="flex h-14 w-14 items-center justify-center rounded-full border border-[#191919]/10 text-[#191919]/30 transition-all duration-500 group-hover:rotate-45 group-hover:border-[#191919] group-hover:bg-[#191919] group-hover:text-white"
                                >
                                    ↗
                                </span>

                            </div>

                        </div>

                    </RouterLink>

                </div>

            </div>

        </section>


        <!-- =====================================================
             ARTIST STATEMENT
        ====================================================== -->
        <section class="border-t border-[#191919]/10">

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div
                    class="grid gap-16 lg:grid-cols-[0.35fr_1fr]"
                >

                    <!-- Label -->
                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            Artist statement
                        </span>

                        <div
                            class="mt-8 hidden h-px w-20 bg-[#E85D75] lg:block"
                        ></div>

                    </div>


                    <!-- Statement -->
                    <div>

                        <p
                            class="max-w-5xl text-4xl font-medium leading-[1.03] tracking-[-0.055em] text-[#191919]/80 sm:text-5xl md:text-6xl lg:text-7xl"
                        >
                            We don't believe in changing a photograph
                            just for the sake of change.

                            <span class="text-[#191919]/20">
                                We refine it.
                            </span>

                            <br />

                            We preserve its feeling,
                            its story,
                            and everything that makes it
                            <span class="text-[#E85D75]">
                                yours.
                            </span>
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             HOW IT WORKS
        ====================================================== -->
        <section class="border-t border-[#191919]/10">

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <!-- Heading -->
                <div
                    class="mb-16 grid gap-8 lg:grid-cols-[0.45fr_1fr]"
                >

                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            The process
                        </span>

                        <h2
                            class="mt-5 text-5xl font-medium leading-[0.9] tracking-[-0.055em] sm:text-6xl"
                        >
                            Simple.
                            <span class="text-[#191919]/20">
                                Intentional.
                            </span>
                        </h2>

                    </div>


                    <p
                        class="max-w-md self-end text-sm leading-6 text-[#191919]/40"
                    >
                        No complicated process.
                        Just a clear brief, careful work,
                        and a result that feels right.
                    </p>

                </div>


                <!-- Steps -->
                <div
                    class="grid border-y border-[#191919]/10 md:grid-cols-3"
                >

                    <!-- Step 01 -->
                    <div
                        class="border-b border-[#191919]/10 px-0 py-10 md:border-b-0 md:border-r md:px-10 md:py-12 md:first:pl-0"
                    >

                        <span
                            class="text-xs text-[#E85D75]"
                        >
                            01
                        </span>

                        <h3
                            class="mt-8 text-2xl font-medium tracking-[-0.035em]"
                        >
                            Choose your service.
                        </h3>

                        <p
                            class="mt-4 max-w-sm text-sm leading-6 text-[#191919]/40"
                        >
                            Select the type of editing or restoration
                            your photograph needs.
                        </p>

                    </div>


                    <!-- Step 02 -->
                    <div
                        class="border-b border-[#191919]/10 px-0 py-10 md:border-b-0 md:border-r md:px-10 md:py-12"
                    >

                        <span
                            class="text-xs text-[#E85D75]"
                        >
                            02
                        </span>

                        <h3
                            class="mt-8 text-2xl font-medium tracking-[-0.035em]"
                        >
                            Tell us the story.
                        </h3>

                        <p
                            class="mt-4 max-w-sm text-sm leading-6 text-[#191919]/40"
                        >
                            Share the details and tell us what
                            you want the final image to feel like.
                        </p>

                    </div>


                    <!-- Step 03 -->
                    <div
                        class="px-0 py-10 md:px-10 md:py-12 md:last:pr-0"
                    >

                        <span
                            class="text-xs text-[#E85D75]"
                        >
                            03
                        </span>

                        <h3
                            class="mt-8 text-2xl font-medium tracking-[-0.035em]"
                        >
                            Let us craft it.
                        </h3>

                        <p
                            class="mt-4 max-w-sm text-sm leading-6 text-[#191919]/40"
                        >
                            We carefully work through the image
                            and deliver the finished result.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CTA
        ====================================================== -->
        <section class="border-t border-[#191919]/10">

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


                    <!-- CTA content -->
                    <div
                        class="relative px-6 py-24 text-center text-white sm:px-10 md:py-32"
                    >

                        <span
                            class="text-[10px] uppercase tracking-[0.4em] text-white/60"
                        >
                            Have a photograph in mind?
                        </span>


                        <h2
                            class="mx-auto mt-6 max-w-5xl text-5xl font-medium leading-[0.88] tracking-[-0.065em] sm:text-6xl md:text-8xl"
                        >
                            Let's make it
                            <span class="text-white/45">
                                memorable.
                            </span>
                        </h2>


                        <p
                            class="mx-auto mt-8 max-w-md text-sm leading-6 text-white/65"
                        >
                            Choose a service and let's start
                            creating something meaningful.
                        </p>


                        <RouterLink
                            :to="{ name: 'login' }"
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
             FOOTER NOTE
        ====================================================== -->
        <div
            class="mx-auto flex max-w-[1600px] flex-col justify-between gap-4 border-t border-[#191919]/10 px-6 py-8 text-[9px] uppercase tracking-[0.3em] text-[#191919]/25 sm:flex-row sm:px-10 lg:px-16"
        >

            <span>
                Teras Memori
            </span>

            <span>
                Image as a form of memory
            </span>

            <span>
                © {{ new Date().getFullYear() }}
            </span>

        </div>

    </div>
</template>

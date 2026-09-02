```vue
<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

const order = ref(null)

onMounted(() => {
    const storedOrder = sessionStorage.getItem('latest_order')

    if (storedOrder) {
        try {
            order.value = JSON.parse(storedOrder)
        } catch (error) {
            console.error('Failed to parse latest order:', error)
        }
    }
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(Number(price || 0))
}

const orderNumber = computed(() => {
    if (!order.value) return '—'

    return (
        order.value.order_number ||
        order.value.code ||
        `#${String(order.value.id).padStart(5, '0')}`
    )
})

const customerName = computed(() => {
    return order.value?.customer?.name ||
        order.value?.customer_name ||
        'there'
})

const serviceName = computed(() => {
    if (order.value?.service?.name) {
        return order.value.service.name
    }

    if (order.value?.order_items?.length) {
        return order.value.order_items[0]?.service?.name ||
            'Selected service'
    }

    if (order.value?.items?.length) {
        return order.value.items[0]?.service?.name ||
            'Selected service'
    }

    return 'Selected service'
})

const quantity = computed(() => {
    if (order.value?.quantity) {
        return order.value.quantity
    }

    if (order.value?.order_items?.length) {
        return order.value.order_items.reduce(
            (total, item) => total + Number(item.quantity || 0),
            0
        )
    }

    if (order.value?.items?.length) {
        return order.value.items.reduce(
            (total, item) => total + Number(item.quantity || 0),
            0
        )
    }

    return 1
})

const total = computed(() => {
    return (
        order.value?.total ??
        order.value?.grand_total ??
        order.value?.total_amount ??
        0
    )
})
</script>


<template>
    <div
        class="min-h-screen overflow-hidden bg-[#FFF8FA] text-[#191919]"
    >

        <!-- =====================================================
             HERO
        ====================================================== -->
        <section
            class="relative flex min-h-[calc(100vh-76px)] items-center overflow-hidden"
        >

            <!-- Grid -->
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
                class="pointer-events-none absolute -right-40 -top-40 h-[550px] w-[550px] rounded-full border border-[#E85D75]/10"
            ></div>

            <div
                class="pointer-events-none absolute -right-20 top-20 h-[350px] w-[350px] rounded-full bg-[#F4A6B8]/20 blur-[110px]"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-48 -left-32 h-[500px] w-[500px] rounded-full border border-[#191919]/[0.04]"
            ></div>


            <div
                class="relative mx-auto w-full max-w-[1600px] px-6 py-24 sm:px-10 md:py-32 lg:px-16"
            >

                <div
                    class="grid gap-16 lg:grid-cols-[1fr_420px] lg:items-center"
                >

                    <!-- =================================================
                         LEFT
                    ================================================== -->
                    <div>

                        <div
                            class="mb-10 flex items-center gap-4"
                        >

                            <span
                                class="h-px w-12 bg-[#E85D75]"
                            ></span>

                            <span
                                class="text-[10px] uppercase tracking-[0.4em] text-[#191919]/40"
                            >
                                06 — Project Received
                            </span>

                        </div>


                        <div
                            class="relative"
                        >

                            <div
                                class="absolute -left-3 top-2 h-3 w-3 rounded-full bg-[#E85D75] sm:-left-5"
                            ></div>

                            <h1
                                class="max-w-5xl text-[17vw] font-medium leading-[0.78] tracking-[-0.09em] sm:text-[12vw] lg:text-[9vw]"
                            >
                                IT'S
                                <br />

                                <span class="text-[#191919]/15">
                                    IN.
                                </span>
                            </h1>

                        </div>


                        <p
                            class="mt-12 max-w-2xl text-lg leading-8 text-[#191919]/55 md:text-2xl md:leading-10"
                        >
                            Thank you, {{ customerName }}.
                            Your project request has arrived safely.
                            <span class="text-[#191919]/80">
                                Now the creative part begins.
                            </span>
                        </p>


                        <div
                            class="mt-12 flex flex-col gap-5 sm:flex-row sm:items-center"
                        >

                            <RouterLink
                                :to="{ name: 'portfolio' }"
                                class="group inline-flex items-center justify-center gap-7 rounded-full bg-[#191919] px-8 py-5 text-[10px] font-semibold uppercase tracking-[0.25em] text-white transition duration-500 hover:-translate-y-1 hover:bg-[#E85D75]"
                            >

                                <span>
                                    View our work
                                </span>

                                <span
                                    class="transition duration-300 group-hover:translate-x-2"
                                >
                                    →
                                </span>

                            </RouterLink>


                            <RouterLink
                                :to="{ name: 'home' }"
                                class="inline-flex items-center justify-center gap-3 px-5 py-4 text-[10px] uppercase tracking-[0.25em] text-[#191919]/40 transition duration-300 hover:text-[#E85D75]"
                            >
                                Back home
                                <span>↗</span>
                            </RouterLink>

                        </div>

                    </div>


                    <!-- =================================================
                         ORDER CARD
                    ================================================== -->
                    <div>

                        <div
                            class="relative overflow-hidden border border-[#191919]/10 bg-white"
                        >

                            <!-- Pink top line -->
                            <div
                                class="absolute left-0 top-0 h-1 w-full bg-[#E85D75]"
                            ></div>


                            <!-- Card header -->
                            <div
                                class="flex items-center justify-between border-b border-[#191919]/10 px-7 py-6"
                            >

                                <span
                                    class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/40"
                                >
                                    Project receipt
                                </span>

                                <span
                                    class="text-[9px] text-[#E85D75]"
                                >
                                    TM / 06
                                </span>

                            </div>


                            <!-- Card content -->
                            <div class="p-7">

                                <!-- Success mark -->
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-full border border-[#E85D75]/30 bg-[#F4A6B8]/15"
                                >

                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-[#E85D75] text-white"
                                    >
                                        ✓
                                    </div>

                                </div>


                                <div class="mt-8">

                                    <span
                                        class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/30"
                                    >
                                        Order number
                                    </span>

                                    <h2
                                        class="mt-3 text-2xl font-medium tracking-[-0.04em]"
                                    >
                                        {{ orderNumber }}
                                    </h2>

                                </div>


                                <div
                                    class="my-8 h-px w-12 bg-[#E85D75]"
                                ></div>


                                <!-- Details -->
                                <div class="space-y-6">

                                    <div
                                        class="flex items-start justify-between gap-5"
                                    >

                                        <span
                                            class="text-sm text-[#191919]/40"
                                        >
                                            Service
                                        </span>

                                        <span
                                            class="max-w-[220px] text-right text-sm font-medium"
                                        >
                                            {{ serviceName }}
                                        </span>

                                    </div>


                                    <div
                                        class="flex items-center justify-between gap-5"
                                    >

                                        <span
                                            class="text-sm text-[#191919]/40"
                                        >
                                            Quantity
                                        </span>

                                        <span
                                            class="text-sm font-medium"
                                        >
                                            × {{ quantity }}
                                        </span>

                                    </div>


                                    <div
                                        class="flex items-center justify-between gap-5"
                                    >

                                        <span
                                            class="text-sm text-[#191919]/40"
                                        >
                                            Status
                                        </span>

                                        <span
                                            class="inline-flex items-center gap-2 text-[10px] font-medium uppercase tracking-[0.15em] text-[#E85D75]"
                                        >

                                            <span
                                                class="h-1.5 w-1.5 rounded-full bg-[#E85D75]"
                                            ></span>

                                            Received

                                        </span>

                                    </div>

                                </div>


                                <!-- Total -->
                                <div
                                    class="mt-8 border-t border-[#191919]/10 pt-7"
                                >

                                    <div
                                        class="flex items-end justify-between gap-5"
                                    >

                                        <span
                                            class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/35"
                                        >
                                            Estimated total
                                        </span>

                                        <span
                                            class="text-2xl font-medium tracking-[-0.03em]"
                                        >
                                            Rp {{ formatPrice(total) }}
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- Note -->
                        <div
                            class="mt-6 border-l-2 border-[#E85D75] pl-5"
                        >

                            <p
                                class="text-[10px] leading-6 text-[#191919]/35"
                            >
                                We'll review your request and get in touch
                                using the contact information you provided.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             WHAT HAPPENS NEXT
        ====================================================== -->
        <section
            class="border-t border-[#191919]/10"
        >

            <div
                class="mx-auto max-w-[1600px] px-6 py-20 sm:px-10 md:py-28 lg:px-16"
            >

                <div
                    class="grid gap-12 lg:grid-cols-[0.7fr_1.3fr]"
                >

                    <!-- Heading -->
                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            What happens next
                        </span>

                        <h2
                            class="mt-6 max-w-md text-4xl font-medium leading-[1] tracking-[-0.055em] sm:text-5xl"
                        >
                            From request
                            <span class="text-[#E85D75]">
                                to finished image.
                            </span>
                        </h2>

                    </div>


                    <!-- Steps -->
                    <div
                        class="grid border-t border-[#191919]/10 md:grid-cols-3 md:border-t-0"
                    >

                        <div
                            class="border-b border-[#191919]/10 py-7 md:border-l md:border-b-0 md:px-7 md:py-0"
                        >

                            <span
                                class="text-[10px] text-[#E85D75]"
                            >
                                01
                            </span>

                            <h3
                                class="mt-5 text-xl font-medium tracking-[-0.03em]"
                            >
                                We review
                            </h3>

                            <p
                                class="mt-4 text-sm leading-6 text-[#191919]/40"
                            >
                                We look through your request and understand
                                exactly what your photograph needs.
                            </p>

                        </div>


                        <div
                            class="border-b border-[#191919]/10 py-7 md:border-l md:border-b-0 md:px-7 md:py-0"
                        >

                            <span
                                class="text-[10px] text-[#E85D75]"
                            >
                                02
                            </span>

                            <h3
                                class="mt-5 text-xl font-medium tracking-[-0.03em]"
                            >
                                We create
                            </h3>

                            <p
                                class="mt-4 text-sm leading-6 text-[#191919]/40"
                            >
                                Our studio works carefully on the details,
                                tone, balance and feeling of your image.
                            </p>

                        </div>


                        <div
                            class="py-7 md:border-l md:px-7 md:py-0"
                        >

                            <span
                                class="text-[10px] text-[#E85D75]"
                            >
                                03
                            </span>

                            <h3
                                class="mt-5 text-xl font-medium tracking-[-0.03em]"
                            >
                                You receive
                            </h3>

                            <p
                                class="mt-4 text-sm leading-6 text-[#191919]/40"
                            >
                                Once everything is ready, we'll send your
                                finished work and project details.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CREATIVE STATEMENT
        ====================================================== -->
        <section
            class="bg-[#191919] text-white"
        >

            <div
                class="mx-auto max-w-[1600px] px-6 py-20 sm:px-10 md:py-28 lg:px-16"
            >

                <div
                    class="grid gap-12 lg:grid-cols-[120px_1fr]"
                >

                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#F4A6B8]"
                        >
                            TM / NOTE
                        </span>

                    </div>


                    <div>

                        <p
                            class="max-w-5xl text-4xl font-medium leading-[1.05] tracking-[-0.055em] sm:text-5xl md:text-6xl lg:text-7xl"
                        >
                            A photograph is already
                            <span class="text-[#F4A6B8]">
                                a memory.
                            </span>

                            <br />

                            We simply help it
                            <span class="text-[#E85D75]">
                                become its best version.
                            </span>
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FOOTER
        ====================================================== -->
        <footer
            class="border-t border-[#191919]/10 bg-[#FFF8FA]"
        >

            <div
                class="mx-auto max-w-[1600px] px-6 py-8 sm:px-10 lg:px-16"
            >

                <div
                    class="flex flex-col gap-4 text-[9px] uppercase tracking-[0.3em] text-[#191919]/30 sm:flex-row sm:items-center sm:justify-between"
                >

                    <span>
                        Teras Memori
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

```vue
<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const router = useRouter()

const services = ref([])
const loading = ref(true)
const submitting = ref(false)

const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
    service_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    notes: '',
    quantity: 1,
})

/*
|--------------------------------------------------------------------------
| FETCH SERVICES
|--------------------------------------------------------------------------
*/

const fetchServices = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/services')

        services.value = response.data.data || []

        // Jika user datang dari tombol "Order" pada service tertentu
        if (route.query.service) {
            const serviceId = Number(route.query.service)

            const exists = services.value.some(
                (service) => Number(service.id) === serviceId
            )

            if (exists) {
                form.value.service_id = serviceId
            }
        }
    } catch (error) {
        console.error('Failed to load services:', error)

        errorMessage.value =
            'Unable to load services. Please try again.'
    } finally {
        loading.value = false
    }
}

/*
|--------------------------------------------------------------------------
| SELECTED SERVICE
|--------------------------------------------------------------------------
*/

const selectedService = computed(() => {
    return services.value.find(
        (service) =>
            Number(service.id) === Number(form.value.service_id)
    )
})

/*
|--------------------------------------------------------------------------
| SUBTOTAL
|--------------------------------------------------------------------------
*/

const subtotal = computed(() => {
    if (!selectedService.value) {
        return 0
    }

    return (
        Number(selectedService.value.price || 0) *
        Number(form.value.quantity || 1)
    )
})

/*
|--------------------------------------------------------------------------
| FORMAT PRICE
|--------------------------------------------------------------------------
*/

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(
        Number(price || 0)
    )
}

/*
|--------------------------------------------------------------------------
| SUBMIT ORDER
|--------------------------------------------------------------------------
*/

const submitOrder = async () => {
    errorMessage.value = ''
    successMessage.value = ''

    /*
    |--------------------------------------------------------------------------
    | FRONTEND VALIDATION
    |--------------------------------------------------------------------------
    */

    if (!form.value.service_id) {
        errorMessage.value = 'Please select a service.'
        return
    }

    if (!form.value.customer_name.trim()) {
        errorMessage.value = 'Please enter your name.'
        return
    }

    if (!form.value.customer_email.trim()) {
        errorMessage.value = 'Please enter your email.'
        return
    }

    if (!form.value.customer_phone.trim()) {
        errorMessage.value = 'Please enter your phone number.'
        return
    }

    if (Number(form.value.quantity) < 1) {
        errorMessage.value = 'Quantity must be at least 1.'
        return
    }

    submitting.value = true

    try {
        /*
        |--------------------------------------------------------------------------
        | PAYLOAD
        |--------------------------------------------------------------------------
        |
        | Laravel backend expects:
        |
        | customer:
        |   name
        |   email
        |   phone
        |
        | items:
        |   service_id
        |   quantity
        |
        | notes
        |
        */

        const payload = {
            customer: {
                name: form.value.customer_name.trim(),
                email: form.value.customer_email.trim(),
                phone: form.value.customer_phone.trim(),
            },

            items: [
                {
                    service_id: Number(form.value.service_id),
                    quantity: Number(form.value.quantity),
                },
            ],

            notes: form.value.notes?.trim() || '',
        }

        console.log('=================================')
        console.log('SUBMITTING ORDER')
        console.log('=================================')
        console.log(payload)

        /*
        |--------------------------------------------------------------------------
        | CREATE ORDER
        |--------------------------------------------------------------------------
        */

        const response = await api.post('/orders', payload)

        console.log('=================================')
        console.log('ORDER CREATED')
        console.log('=================================')
        console.log(response.data)

        /*
        |--------------------------------------------------------------------------
        | GET CREATED ORDER
        |--------------------------------------------------------------------------
        */

        const createdOrder = response.data?.data

        if (!createdOrder) {
            console.warn(
                'Order created but response.data.data is empty:',
                response.data
            )
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE ORDER TO SESSION STORAGE
        |--------------------------------------------------------------------------
        |
        | OrderSuccess.vue mengambil data dari:
        |
        | sessionStorage.getItem('latest_order')
        |
        */

        if (createdOrder) {
            sessionStorage.setItem(
                'latest_order',
                JSON.stringify(createdOrder)
            )

            console.log(
                'latest_order saved:',
                createdOrder
            )
        }

        /*
        |--------------------------------------------------------------------------
        | SUCCESS MESSAGE
        |--------------------------------------------------------------------------
        */

        successMessage.value =
            response.data?.message ||
            'Order successfully created.'

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        |
        | Jangan menggunakan setTimeout.
        | Langsung pindah ke halaman success setelah API berhasil.
        |
        */

        console.log(
            'Redirecting to route: order-success'
        )

        await router.push({
            name: 'order-success',
        })

        console.log(
            'Redirect completed.'
        )

    } catch (error) {
        console.error(
            '================================='
        )

        console.error(
            'FAILED TO CREATE ORDER'
        )

        console.error(
            '================================='
        )

        console.error(error)

        /*
        |--------------------------------------------------------------------------
        | VALIDATION ERROR - 422
        |--------------------------------------------------------------------------
        */

        if (error.response?.status === 422) {
            const errors =
                error.response.data?.errors

            if (errors) {
                const firstError =
                    Object.values(errors)
                        .flat()
                        .find(Boolean)

                errorMessage.value =
                    firstError ||
                    error.response.data?.message ||
                    'Please check the information you entered.'
            } else {
                errorMessage.value =
                    error.response.data?.message ||
                    'Please check the information you entered.'
            }

            return
        }

        /*
        |--------------------------------------------------------------------------
        | UNAUTHORIZED
        |--------------------------------------------------------------------------
        */

        if (error.response?.status === 401) {
            errorMessage.value =
                'Your session has expired. Please try again.'
            return
        }

        /*
        |--------------------------------------------------------------------------
        | SERVER ERROR
        |--------------------------------------------------------------------------
        */

        if (error.response?.status >= 500) {
            errorMessage.value =
                'Server error. Please try again later.'
            return
        }

        /*
        |--------------------------------------------------------------------------
        | OTHER ERROR
        |--------------------------------------------------------------------------
        */

        errorMessage.value =
            error.response?.data?.message ||
            error.message ||
            'Something went wrong while creating your order.'

    } finally {
        submitting.value = false
    }
}

/*
|--------------------------------------------------------------------------
| INITIALIZE
|--------------------------------------------------------------------------
*/

onMounted(() => {
    fetchServices()
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
            class="relative overflow-hidden border-b border-[#191919]/10"
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
                class="pointer-events-none absolute -right-32 -top-32 h-[500px] w-[500px] rounded-full border border-[#E85D75]/10"
            ></div>

            <div
                class="pointer-events-none absolute -right-10 top-24 h-[300px] w-[300px] rounded-full bg-[#F4A6B8]/20 blur-[100px]"
            ></div>

            <div
                class="pointer-events-none absolute -left-40 bottom-0 h-[400px] w-[400px] rounded-full border border-[#191919]/[0.04]"
            ></div>


            <div
                class="relative mx-auto max-w-[1600px] px-6 pb-20 pt-20 sm:px-10 md:pb-28 md:pt-28 lg:px-16"
            >

                <!-- Label -->

                <div
                    class="mb-10 flex items-center gap-4"
                >

                    <span
                        class="h-px w-12 bg-[#E85D75]"
                    ></span>

                    <span
                        class="text-[10px] uppercase tracking-[0.4em] text-[#191919]/40"
                    >
                        05 — Start a Project
                    </span>

                </div>


                <!-- Heading -->

                <div
                    class="grid gap-12 lg:grid-cols-[1fr_250px] lg:items-end"
                >

                    <div>

                        <h1
                            class="max-w-6xl text-[16vw] font-medium leading-[0.76] tracking-[-0.085em] sm:text-[12vw] lg:text-[9vw]"
                        >

                            LET'S

                            <span
                                class="text-[#191919]/15"
                            >
                                CREATE.
                            </span>

                        </h1>

                    </div>


                    <!-- Circular mark -->

                    <div
                        class="hidden lg:block"
                    >

                        <div
                            class="relative mx-auto h-36 w-36"
                        >

                            <div
                                class="absolute inset-0 rounded-full border border-[#191919]/10"
                            ></div>

                            <div
                                class="absolute inset-4 rounded-full border border-[#E85D75]/25"
                            ></div>

                            <div
                                class="absolute inset-9 rounded-full bg-[#F4A6B8]/20"
                            ></div>

                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >

                                <span
                                    class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/35"
                                >
                                    TM / 05
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
                        class="max-w-3xl text-lg leading-8 text-[#191919]/55 md:text-2xl md:leading-10"
                    >

                        Tell us about the photograph you want to transform,
                        restore or refine.

                        <span
                            class="text-[#191919]/80"
                        >
                            We'll take care of the details.
                        </span>

                    </p>


                    <div
                        class="text-xs leading-6 text-[#191919]/30 md:text-right"
                    >

                        Teras Memori Studio
                        <br />

                        Your photo / Our craft

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             ORDER AREA
        ====================================================== -->

        <section>

            <div
                class="mx-auto max-w-[1600px] px-6 py-20 sm:px-10 md:py-28 lg:px-16"
            >

                <!-- =================================================
                     LOADING
                ================================================== -->

                <div
                    v-if="loading"
                    class="grid gap-12 lg:grid-cols-[1fr_380px]"
                >

                    <div
                        class="animate-pulse"
                    >

                        <div
                            class="h-6 w-40 bg-[#191919]/[0.05]"
                        ></div>


                        <div
                            class="mt-8 grid gap-3 sm:grid-cols-2"
                        >

                            <div
                                v-for="item in 4"
                                :key="item"
                                class="h-36 bg-[#191919]/[0.04]"
                            ></div>

                        </div>


                        <div
                            class="mt-16 h-20 bg-[#191919]/[0.04]"
                        ></div>


                        <div
                            class="mt-5 h-20 bg-[#191919]/[0.04]"
                        ></div>


                        <div
                            class="mt-5 h-40 bg-[#191919]/[0.04]"
                        ></div>

                    </div>


                    <div
                        class="hidden h-96 animate-pulse bg-[#191919]/[0.04] lg:block"
                    ></div>

                </div>


                <!-- =================================================
                     ERROR
                ================================================== -->

                <div
                    v-else-if="errorMessage && services.length === 0"
                    class="border-y border-[#191919]/10 py-24 text-center"
                >

                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-[#E85D75]/30"
                    >

                        <span
                            class="text-[#E85D75]"
                        >
                            !
                        </span>

                    </div>


                    <p
                        class="mt-6 text-sm text-[#191919]/45"
                    >
                        {{ errorMessage }}
                    </p>


                    <button
                        type="button"
                        @click="fetchServices"
                        class="mt-7 rounded-full border border-[#191919]/15 px-7 py-3 text-[10px] uppercase tracking-[0.25em] transition duration-300 hover:border-[#E85D75] hover:text-[#E85D75]"
                    >
                        Try again
                    </button>

                </div>


                <!-- =================================================
                     MAIN
                ================================================== -->

                <div
                    v-else
                    class="grid gap-16 lg:grid-cols-[1fr_380px]"
                >

                    <!-- =================================================
                         FORM
                    ================================================== -->

                    <div>

                        <form
                            @submit.prevent="submitOrder"
                            class="space-y-16"
                        >

                            <!-- =========================================
                                 SERVICE
                            ========================================== -->

                            <div>

                                <div
                                    class="mb-7 flex items-center gap-4"
                                >

                                    <span
                                        class="text-xs text-[#E85D75]"
                                    >
                                        01
                                    </span>

                                    <span
                                        class="text-[10px] uppercase tracking-[0.3em] text-[#191919]/35"
                                    >
                                        Choose a service
                                    </span>

                                </div>


                                <div
                                    class="grid gap-4 sm:grid-cols-2"
                                >

                                    <button
                                        v-for="service in services"
                                        :key="service.id"
                                        type="button"
                                        @click="form.service_id = service.id"
                                        class="group relative overflow-hidden border p-6 text-left transition duration-500"
                                        :class="
                                            Number(form.service_id) === Number(service.id)
                                                ? 'border-[#E85D75] bg-[#F4A6B8]/30 text-[#191919]'
                                                : 'border-[#191919]/10 bg-white/60 text-[#191919] hover:-translate-y-1 hover:border-[#E85D75]/50 hover:bg-white'
                                        "
                                    >

                                        <!-- Active line -->

                                        <div
                                            class="absolute left-0 top-0 h-full w-1 bg-[#E85D75] transition duration-300"
                                            :class="
                                                Number(form.service_id) === Number(service.id)
                                                    ? 'opacity-100'
                                                    : 'opacity-0'
                                            "
                                        ></div>


                                        <div
                                            class="flex items-start justify-between gap-5"
                                        >

                                            <div>

                                                <span
                                                    class="text-[9px] uppercase tracking-[0.25em]"
                                                    :class="
                                                        Number(form.service_id) === Number(service.id)
                                                            ? 'text-[#E85D75]'
                                                            : 'text-[#191919]/25'
                                                    "
                                                >
                                                    Service
                                                </span>


                                                <h2
                                                    class="mt-3 text-xl font-medium tracking-[-0.03em] text-[#191919]"
                                                >
                                                    {{ service.name }}
                                                </h2>

                                            </div>


                                            <span
                                                class="text-[9px] uppercase tracking-[0.15em]"
                                                :class="
                                                    Number(form.service_id) === Number(service.id)
                                                        ? 'text-[#E85D75]'
                                                        : 'text-[#191919]/25'
                                                "
                                            >
                                                {{ service.duration }}
                                            </span>

                                        </div>


                                        <p
                                            class="mt-5 text-xs leading-6"
                                            :class="
                                                Number(form.service_id) === Number(service.id)
                                                    ? 'text-[#191919]/60'
                                                    : 'text-[#191919]/40'
                                            "
                                        >
                                            {{ service.description }}
                                        </p>


                                        <div
                                            class="mt-8 flex items-center justify-between"
                                        >

                                            <span
                                                class="text-sm font-medium text-[#191919]"
                                            >
                                                Rp {{ formatPrice(service.price) }}
                                            </span>


                                            <span
                                                class="text-xl transition duration-300"
                                                :class="
                                                    Number(form.service_id) === Number(service.id)
                                                        ? 'translate-x-1 text-[#E85D75]'
                                                        : 'text-[#191919]/20 group-hover:translate-x-1 group-hover:text-[#E85D75]'
                                                "
                                            >
                                                →
                                            </span>

                                        </div>

                                    </button>

                                </div>

                            </div>


                            <!-- =========================================
                                 CUSTOMER INFORMATION
                            ========================================== -->

                            <div>

                                <div
                                    class="mb-7 flex items-center gap-4"
                                >

                                    <span
                                        class="text-xs text-[#E85D75]"
                                    >
                                        02
                                    </span>

                                    <span
                                        class="text-[10px] uppercase tracking-[0.3em] text-[#191919]/35"
                                    >
                                        Your information
                                    </span>

                                </div>


                                <div
                                    class="grid gap-4"
                                >

                                    <!-- Full Name -->

                                    <div
                                        class="group relative overflow-hidden border border-[#191919]/10 bg-white p-6 transition duration-300 hover:border-[#E85D75]/40"
                                    >

                                        <div
                                            class="absolute left-0 top-0 h-full w-1 bg-[#E85D75] opacity-0 transition duration-300 group-focus-within:opacity-100"
                                        ></div>


                                        <label
                                            class="mb-3 block text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/40"
                                        >
                                            Full name
                                        </label>


                                        <input
                                            v-model="form.customer_name"
                                            type="text"
                                            placeholder="Your name"
                                            autocomplete="name"
                                            class="w-full border-0 bg-transparent p-0 text-base font-medium text-[#191919] outline-none placeholder:text-[#191919]/25 focus:ring-0"
                                        />


                                        <div
                                            class="mt-4 h-px w-full bg-[#191919]/[0.07] transition duration-300 group-focus-within:bg-[#E85D75]/40"
                                        ></div>

                                    </div>


                                    <!-- Email + Phone -->

                                    <div
                                        class="grid gap-4 md:grid-cols-2"
                                    >

                                        <!-- Email -->

                                        <div
                                            class="group relative overflow-hidden border border-[#191919]/10 bg-white p-6 transition duration-300 hover:border-[#E85D75]/40"
                                        >

                                            <div
                                                class="absolute left-0 top-0 h-full w-1 bg-[#E85D75] opacity-0 transition duration-300 group-focus-within:opacity-100"
                                            ></div>


                                            <label
                                                class="mb-3 block text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/40"
                                            >
                                                Email
                                            </label>


                                            <input
                                                v-model="form.customer_email"
                                                type="email"
                                                placeholder="you@example.com"
                                                autocomplete="email"
                                                class="w-full border-0 bg-transparent p-0 text-base font-medium text-[#191919] outline-none placeholder:text-[#191919]/25 focus:ring-0"
                                            />


                                            <div
                                                class="mt-4 h-px w-full bg-[#191919]/[0.07] transition duration-300 group-focus-within:bg-[#E85D75]/40"
                                            ></div>

                                        </div>


                                        <!-- Phone -->

                                        <div
                                            class="group relative overflow-hidden border border-[#191919]/10 bg-white p-6 transition duration-300 hover:border-[#E85D75]/40"
                                        >

                                            <div
                                                class="absolute left-0 top-0 h-full w-1 bg-[#E85D75] opacity-0 transition duration-300 group-focus-within:opacity-100"
                                            ></div>


                                            <label
                                                class="mb-3 block text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/40"
                                            >
                                                WhatsApp / Phone
                                            </label>


                                            <input
                                                v-model="form.customer_phone"
                                                type="tel"
                                                placeholder="+62..."
                                                autocomplete="tel"
                                                class="w-full border-0 bg-transparent p-0 text-base font-medium text-[#191919] outline-none placeholder:text-[#191919]/25 focus:ring-0"
                                            />


                                            <div
                                                class="mt-4 h-px w-full bg-[#191919]/[0.07] transition duration-300 group-focus-within:bg-[#E85D75]/40"
                                            ></div>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!-- =========================================
                                 PROJECT DETAILS
                            ========================================== -->

                            <div>

                                <div
                                    class="mb-7 flex items-center gap-4"
                                >

                                    <span
                                        class="text-xs text-[#E85D75]"
                                    >
                                        03
                                    </span>

                                    <span
                                        class="text-[10px] uppercase tracking-[0.3em] text-[#191919]/35"
                                    >
                                        Project details
                                    </span>

                                </div>


                                <div
                                    class="grid gap-4"
                                >

                                    <!-- Quantity -->

                                    <div
                                        class="group relative overflow-hidden border border-[#191919]/10 bg-white p-6 transition duration-300 hover:border-[#E85D75]/40"
                                    >

                                        <div
                                            class="absolute left-0 top-0 h-full w-1 bg-[#E85D75] opacity-0 transition duration-300 group-focus-within:opacity-100"
                                        ></div>


                                        <label
                                            class="mb-3 block text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/40"
                                        >
                                            Quantity
                                        </label>


                                        <input
                                            v-model.number="form.quantity"
                                            type="number"
                                            min="1"
                                            class="w-32 border-0 bg-transparent p-0 text-base font-medium text-[#191919] outline-none focus:ring-0"
                                        />


                                        <div
                                            class="mt-4 h-px w-full bg-[#191919]/[0.07] transition duration-300 group-focus-within:bg-[#E85D75]/40"
                                        ></div>

                                    </div>


                                    <!-- Notes -->

                                    <div
                                        class="group relative overflow-hidden border border-[#191919]/10 bg-white p-6 transition duration-300 hover:border-[#E85D75]/40"
                                    >

                                        <div
                                            class="absolute left-0 top-0 h-full w-1 bg-[#E85D75] opacity-0 transition duration-300 group-focus-within:opacity-100"
                                        ></div>


                                        <label
                                            class="mb-3 block text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/40"
                                        >
                                            Notes
                                        </label>


                                        <textarea
                                            v-model="form.notes"
                                            rows="5"
                                            placeholder="Tell us anything important about your project..."
                                            class="w-full resize-none border-0 bg-transparent p-0 text-base leading-7 text-[#191919] outline-none placeholder:text-[#191919]/25 focus:ring-0"
                                        ></textarea>


                                        <div
                                            class="mt-4 h-px w-full bg-[#191919]/[0.07] transition duration-300 group-focus-within:bg-[#E85D75]/40"
                                        ></div>

                                    </div>

                                </div>

                            </div>


                            <!-- =========================================
                                 ERROR
                            ========================================== -->

                            <div
                                v-if="errorMessage"
                                class="border border-red-500/20 bg-red-50 px-5 py-4 text-sm text-red-600"
                            >
                                {{ errorMessage }}
                            </div>


                            <!-- =========================================
                                 SUCCESS
                            ========================================== -->

                            <div
                                v-if="successMessage"
                                class="border border-green-500/20 bg-green-50 px-5 py-4 text-sm text-green-600"
                            >
                                {{ successMessage }}
                            </div>


                            <!-- =========================================
                                 SUBMIT
                            ========================================== -->

                            <div
                                class="flex flex-col gap-6 border-t border-[#191919]/10 pt-8 sm:flex-row sm:items-center sm:justify-between"
                            >

                                <RouterLink
                                    :to="{ name: 'services' }"
                                    class="text-[10px] uppercase tracking-[0.25em] text-[#191919]/40 transition duration-300 hover:text-[#E85D75]"
                                >
                                    ← Back to services
                                </RouterLink>


                                <button
                                    type="submit"
                                    :disabled="submitting"
                                    class="group inline-flex items-center justify-center gap-7 rounded-full bg-[#191919] px-9 py-5 text-[10px] font-semibold uppercase tracking-[0.25em] text-white transition duration-500 hover:-translate-y-1 hover:bg-[#E85D75] disabled:cursor-not-allowed disabled:opacity-50"
                                >

                                    <span>
                                        {{
                                            submitting
                                                ? 'Creating...'
                                                : 'Create Order'
                                        }}
                                    </span>


                                    <span
                                        class="transition duration-300 group-hover:translate-x-2"
                                    >
                                        →
                                    </span>

                                </button>

                            </div>

                        </form>

                    </div>


                    <!-- =================================================
                         SUMMARY
                    ================================================== -->

                    <aside
                        class="lg:sticky lg:top-10 lg:self-start"
                    >

                        <div
                            class="relative overflow-hidden border border-[#191919]/10 bg-white"
                        >

                            <!-- Pink accent -->

                            <div
                                class="absolute left-0 top-0 h-1 w-full bg-[#E85D75]"
                            ></div>


                            <!-- Header -->

                            <div
                                class="border-b border-[#191919]/10 px-7 py-6"
                            >

                                <div
                                    class="flex items-center justify-between"
                                >

                                    <span
                                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/40"
                                    >
                                        Order Summary
                                    </span>


                                    <span
                                        class="text-[9px] text-[#E85D75]"
                                    >
                                        TM / 05
                                    </span>

                                </div>

                            </div>


                            <!-- Content -->

                            <div
                                class="p-7"
                            >

                                <!-- No service -->

                                <div
                                    v-if="!selectedService"
                                    class="py-10 text-center"
                                >

                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#F4A6B8]/15"
                                    >

                                        <span
                                            class="text-xl text-[#E85D75]"
                                        >
                                            +
                                        </span>

                                    </div>


                                    <p
                                        class="mx-auto mt-5 max-w-xs text-sm leading-6 text-[#191919]/40"
                                    >
                                        Select a service to see your
                                        project summary.
                                    </p>

                                </div>


                                <!-- Selected -->

                                <div
                                    v-else
                                >

                                    <span
                                        class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/30"
                                    >
                                        Selected service
                                    </span>


                                    <h2
                                        class="mt-3 text-3xl font-medium tracking-[-0.045em] text-[#191919]"
                                    >
                                        {{ selectedService.name }}
                                    </h2>


                                    <p
                                        class="mt-2 text-xs text-[#191919]/40"
                                    >
                                        {{ selectedService.duration }}
                                    </p>


                                    <div
                                        class="my-8 h-px w-12 bg-[#E85D75]"
                                    ></div>


                                    <div
                                        class="space-y-5"
                                    >

                                        <div
                                            class="flex items-center justify-between text-sm"
                                        >

                                            <span
                                                class="text-[#191919]/40"
                                            >
                                                Price
                                            </span>


                                            <span
                                                class="text-[#191919]"
                                            >
                                                Rp {{ formatPrice(selectedService.price) }}
                                            </span>

                                        </div>


                                        <div
                                            class="flex items-center justify-between text-sm"
                                        >

                                            <span
                                                class="text-[#191919]/40"
                                            >
                                                Quantity
                                            </span>


                                            <span
                                                class="text-[#191919]"
                                            >
                                                × {{ form.quantity }}
                                            </span>

                                        </div>

                                    </div>


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
                                                class="text-2xl font-medium tracking-[-0.03em] text-[#191919]"
                                            >
                                                Rp {{ formatPrice(subtotal) }}
                                            </span>

                                        </div>

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
                                By submitting this order, you agree that
                                the information provided may be used to
                                process your project request.
                            </p>

                        </div>

                    </aside>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FINAL NOTE
        ====================================================== -->

        <section
            class="border-t border-[#191919]/10"
        >

            <div
                class="mx-auto max-w-[1600px] px-6 py-20 sm:px-10 md:py-28 lg:px-16"
            >

                <div
                    class="grid gap-8 md:grid-cols-[1fr_auto] md:items-end"
                >

                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            A little reminder
                        </span>


                        <p
                            class="mt-5 max-w-3xl text-3xl font-medium leading-[1.05] tracking-[-0.045em] text-[#191919]/75 sm:text-4xl md:text-5xl"
                        >

                            Every great image starts with

                            <span
                                class="text-[#E85D75]"
                            >
                                an idea.
                            </span>

                        </p>

                    </div>


                    <RouterLink
                        :to="{ name: 'portfolio' }"
                        class="inline-flex items-center gap-5 text-[10px] uppercase tracking-[0.25em] text-[#191919]/40 transition duration-300 hover:text-[#E85D75]"
                    >

                        View our work

                        <span class="text-base">
                            →
                        </span>

                    </RouterLink>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FOOTER
        ====================================================== -->

        <footer
            class="border-t border-[#191919]/10"
        >

            <div
                class="mx-auto flex max-w-[1600px] flex-col justify-between gap-4 px-6 py-8 text-[9px] uppercase tracking-[0.3em] text-[#191919]/30 sm:flex-row sm:px-10 lg:px-16"
            >

                <span>
                    Teras Memori
                </span>


                <span>
                    Start a Project
                </span>


                <span>
                    © {{ new Date().getFullYear() }}
                </span>

            </div>

        </footer>

    </div>

</template>
```

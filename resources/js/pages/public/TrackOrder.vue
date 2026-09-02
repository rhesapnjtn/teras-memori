<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../services/api'

const orderNumber = ref('')
const email = ref('')

const loading = ref(false)
const errorMessage = ref('')
const order = ref(null)

// ================================
// REVIEW STATE
// ================================
const reviewLoading = ref(false)
const reviewError = ref('')
const reviewSuccess = ref('')

const reviewRating = ref(0)
const reviewComment = ref('')

const setRating = (rating) => {
    reviewRating.value = rating
}

const submitReview = async () => {
    reviewError.value = ''
    reviewSuccess.value = ''

    if (!order.value) {
        reviewError.value = 'Order tidak ditemukan.'
        return
    }

    if (reviewRating.value < 1 || reviewRating.value > 5) {
        reviewError.value = 'Please select a rating from 1 to 5.'
        return
    }

    reviewLoading.value = true

    try {
        const response = await api.post('/reviews', {
            customer_id: order.value.customer.id,
            order_id: order.value.id,
            rating: reviewRating.value,
            comment: reviewComment.value.trim() || null,
        })

        const review = response.data.data || response.data

        // Simpan review ke object order
        order.value.review = review

        reviewSuccess.value =
            'Thank you for your review. Your feedback has been submitted and is waiting for publication.'

        // Reset form
        reviewRating.value = 0
        reviewComment.value = ''
    } catch (error) {
        console.error('Failed to submit review:', error)

        if (error.response?.status === 422) {
            const errors = error.response?.data?.errors

            if (errors) {
                const firstError = Object.values(errors).flat()[0]
                reviewError.value =
                    firstError || 'Please check your review.'
            } else {
                reviewError.value =
                    error.response?.data?.message ||
                    'Unable to submit your review.'
            }
        } else {
            reviewError.value =
                'Something went wrong while submitting your review. Please try again.'
        }
    } finally {
        reviewLoading.value = false
    }
}

const trackOrder = async () => {
    errorMessage.value = ''
    order.value = null

    reviewError.value = ''
    reviewSuccess.value = ''
    reviewRating.value = 0
    reviewComment.value = ''

    const cleanOrderNumber = orderNumber.value.trim()
    const cleanEmail = email.value.trim()

    if (!cleanOrderNumber) {
        errorMessage.value = 'Please enter your order number.'
        return
    }

    if (!cleanEmail) {
        errorMessage.value = 'Please enter your email address.'
        return
    }

    loading.value = true

    try {
        const response = await api.post('/orders/track', {
            order_number: cleanOrderNumber,
            email: cleanEmail,
        })

        order.value = response.data.data || response.data
    } catch (error) {
        console.error('Failed to track order:', error)

        if (error.response?.status === 404) {
            errorMessage.value =
                'We could not find an order with those details.'
        } else if (error.response?.status === 422) {
            errorMessage.value =
                'Please check your order number and email address.'
        } else {
            errorMessage.value =
                'Something went wrong. Please try again.'
        }
    } finally {
        loading.value = false
    }
}

const resetTracking = () => {
    order.value = null
    errorMessage.value = ''
    reviewError.value = ''
    reviewSuccess.value = ''
    reviewRating.value = 0
    reviewComment.value = ''
}
</script>

<template>
    <main class="min-h-screen bg-[#FFF8FA] text-[#191919]">

        <!-- =====================================================
             HERO
        ====================================================== -->
        <section
            class="relative overflow-hidden border-b border-[#191919]/10"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.035]"
                style="
                    background-image:
                        linear-gradient(rgba(25,25,25,.5) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(25,25,25,.5) 1px, transparent 1px);
                    background-size: 80px 80px;
                "
            ></div>

            <div
                class="pointer-events-none absolute -right-32 -top-32 h-[450px] w-[450px] rounded-full border border-[#191919]/[0.04]"
            ></div>

            <div
                class="pointer-events-none absolute -left-32 top-20 h-[400px] w-[400px] rounded-full border border-[#E85D75]/10"
            ></div>

            <div
                class="relative mx-auto max-w-[1600px] px-6 pb-20 pt-20 sm:px-10 md:pb-28 md:pt-28 lg:px-16"
            >
                <div class="mb-10 flex items-center gap-4">
                    <span class="h-px w-12 bg-[#E85D75]"></span>

                    <span
                        class="text-[10px] uppercase tracking-[0.4em] text-[#191919]/40"
                    >
                        04 — Track Order
                    </span>
                </div>

                <div
                    class="grid gap-12 lg:grid-cols-[1fr_300px] lg:items-end"
                >
                    <div>
                        <h1
                            class="max-w-6xl text-[15vw] font-medium leading-[0.78] tracking-[-0.085em] sm:text-[12vw] lg:text-[9vw]"
                        >
                            TRACK
                            <span class="text-[#191919]/15">
                                ORDER.
                            </span>
                        </h1>
                    </div>

                    <div class="hidden lg:block">
                        <p
                            class="max-w-xs text-right text-xs leading-6 text-[#191919]/35"
                        >
                            Follow the progress of your project
                            using your order number and email address.
                        </p>
                    </div>
                </div>
            </div>
        </section>


        <!-- =====================================================
             TRACK ORDER
        ====================================================== -->
        <section class="border-b border-[#191919]/10">
            <div
                class="mx-auto max-w-[1600px] px-6 py-20 sm:px-10 md:py-28 lg:px-16"
            >
                <div class="grid gap-16 lg:grid-cols-[0.4fr_1fr]">

                    <!-- LEFT -->
                    <div>
                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            Project status
                        </span>

                        <div
                            class="mt-8 hidden h-px w-20 bg-[#E85D75] lg:block"
                        ></div>

                        <p
                            class="mt-8 max-w-xs text-sm leading-7 text-[#191919]/40"
                        >
                            Enter the order number and email address
                            used when you submitted your project.
                        </p>
                    </div>


                    <!-- RIGHT -->
                    <div>

                        <!-- =================================================
                             SEARCH FORM
                        ================================================== -->
                        <form
                            v-if="!order"
                            @submit.prevent="trackOrder"
                            class="max-w-3xl"
                        >

                            <!-- Order Number -->
                            <div>
                                <label
                                    for="order-number"
                                    class="text-[10px] uppercase tracking-[0.3em] text-[#191919]/40"
                                >
                                    Order number
                                </label>

                                <input
                                    id="order-number"
                                    v-model="orderNumber"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="TM-20260902120727-NXWZ"
                                    class="mt-3 w-full border-b border-[#191919]/15 bg-transparent px-0 py-5 text-lg outline-none transition placeholder:text-[#191919]/15 focus:border-[#E85D75]"
                                />
                            </div>


                            <!-- Email -->
                            <div class="mt-10">
                                <label
                                    for="order-email"
                                    class="text-[10px] uppercase tracking-[0.3em] text-[#191919]/40"
                                >
                                    Email address
                                </label>

                                <input
                                    id="order-email"
                                    v-model="email"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="you@example.com"
                                    class="mt-3 w-full border-b border-[#191919]/15 bg-transparent px-0 py-5 text-lg outline-none transition placeholder:text-[#191919]/15 focus:border-[#E85D75]"
                                />
                            </div>


                            <!-- Error -->
                            <div
                                v-if="errorMessage"
                                class="mt-8 border-l-2 border-[#E85D75] bg-[#E85D75]/5 px-5 py-4"
                            >
                                <p class="text-sm text-[#191919]/60">
                                    {{ errorMessage }}
                                </p>
                            </div>


                            <!-- Submit -->
                            <button
                                type="submit"
                                :disabled="loading"
                                class="mt-10 inline-flex items-center gap-6 rounded-full bg-[#191919] px-8 py-5 text-xs font-semibold uppercase tracking-[0.2em] text-white transition duration-300 hover:bg-[#E85D75] disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span>
                                    {{ loading ? 'Checking...' : 'Check order' }}
                                </span>

                                <span>→</span>
                            </button>
                        </form>


                        <!-- =================================================
                             ORDER RESULT
                        ================================================== -->
                        <div
                            v-else
                            class="max-w-4xl"
                        >

                            <!-- Header -->
                            <div
                                class="border-b border-[#191919]/10 pb-8"
                            >
                                <div
                                    class="flex flex-col justify-between gap-6 sm:flex-row sm:items-start"
                                >

                                    <div>
                                        <span
                                            class="text-[10px] uppercase tracking-[0.3em] text-[#E85D75]"
                                        >
                                            Order found
                                        </span>

                                        <h2
                                            class="mt-4 text-3xl font-medium tracking-[-0.04em] sm:text-4xl"
                                        >
                                            {{ order.order_number }}
                                        </h2>
                                    </div>


                                    <!-- Status -->
                                    <div
                                        class="inline-flex w-fit items-center rounded-full border border-[#191919]/10 px-4 py-2"
                                    >
                                        <span
                                            class="mr-2 h-2 w-2 rounded-full bg-[#E85D75]"
                                        ></span>

                                        <span
                                            class="text-[10px] uppercase tracking-[0.25em] text-[#191919]/50"
                                        >
                                            {{ order.status }}
                                        </span>
                                    </div>

                                </div>
                            </div>


                            <!-- Customer -->
                            <div
                                class="grid gap-8 border-b border-[#191919]/10 py-8 sm:grid-cols-2"
                            >

                                <div>
                                    <span
                                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                                    >
                                        Customer
                                    </span>

                                    <p
                                        class="mt-3 text-base text-[#191919]/70"
                                    >
                                        {{ order.customer?.name || '—' }}
                                    </p>
                                </div>


                                <div>
                                    <span
                                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                                    >
                                        Email
                                    </span>

                                    <p
                                        class="mt-3 break-all text-base text-[#191919]/70"
                                    >
                                        {{ order.customer?.email || '—' }}
                                    </p>
                                </div>

                            </div>


                            <!-- Project Items -->
                            <div
                                v-if="order.items?.length"
                                class="border-b border-[#191919]/10 py-8"
                            >
                                <span
                                    class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                                >
                                    Project
                                </span>

                                <div class="mt-5 space-y-4">

                                    <div
                                        v-for="item in order.items"
                                        :key="item.id"
                                        class="flex items-center justify-between gap-5"
                                    >

                                        <div>
                                            <p
                                                class="text-sm text-[#191919]/70"
                                            >
                                                {{
                                                    item.service?.name ||
                                                    item.service?.title ||
                                                    'Service'
                                                }}
                                            </p>

                                            <p
                                                class="mt-1 text-xs text-[#191919]/30"
                                            >
                                                Quantity:
                                                {{ item.quantity }}
                                            </p>
                                        </div>


                                        <span
                                            class="text-sm text-[#191919]/50"
                                        >
                                            Rp
                                            {{
                                                Number(
                                                    item.subtotal || 0
                                                ).toLocaleString('id-ID')
                                            }}
                                        </span>

                                    </div>

                                </div>
                            </div>


                            <!-- Uploaded Files -->
                            <div
                                v-if="order.files?.length"
                                class="border-b border-[#191919]/10 py-8"
                            >
                                <span
                                    class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                                >
                                    Uploaded files
                                </span>

                                <div class="mt-5 space-y-3">

                                    <a
                                        v-for="file in order.files"
                                        :key="file.id"
                                        :href="file.file_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center justify-between gap-5 border border-[#191919]/10 px-5 py-4 transition hover:border-[#E85D75]/40 hover:bg-white"
                                    >
                                        <span
                                            class="truncate text-sm text-[#191919]/60"
                                        >
                                            {{ file.file_name }}
                                        </span>

                                        <span
                                            class="shrink-0 text-[#E85D75]"
                                        >
                                            ↗
                                        </span>
                                    </a>

                                </div>
                            </div>


                            <!-- Notes -->
                            <div
                                v-if="order.notes"
                                class="border-b border-[#191919]/10 py-8"
                            >
                                <span
                                    class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                                >
                                    Notes
                                </span>

                                <p
                                    class="mt-4 max-w-2xl text-sm leading-7 text-[#191919]/50"
                                >
                                    {{ order.notes }}
                                </p>
                            </div>


                            <!-- Total -->
                            <div
                                class="flex flex-col justify-between gap-8 border-b border-[#191919]/10 py-8 sm:flex-row sm:items-end"
                            >

                                <div>
                                    <span
                                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                                    >
                                        Total
                                    </span>

                                    <p
                                        class="mt-2 text-3xl font-medium tracking-[-0.04em]"
                                    >
                                        Rp
                                        {{
                                            Number(
                                                order.total_amount || 0
                                            ).toLocaleString('id-ID')
                                        }}
                                    </p>
                                </div>


                                <button
                                    type="button"
                                    @click="resetTracking"
                                    class="w-fit rounded-full border border-[#191919]/15 px-6 py-4 text-[10px] font-semibold uppercase tracking-[0.2em] transition hover:border-[#191919]/40"
                                >
                                    Check another order
                                </button>

                            </div>


                            <!-- =================================================
                                 GIVE REVIEW
                            ================================================== -->
                            <section
                                v-if="
                                    order.status === 'completed' &&
                                    !order.review
                                "
                                class="mt-12"
                            >

                                <div
                                    class="border border-[#E85D75]/20 bg-white p-6 sm:p-8 md:p-10"
                                >

                                    <!-- Review Header -->
                                    <div
                                        class="flex flex-col justify-between gap-6 border-b border-[#191919]/10 pb-8 sm:flex-row sm:items-start"
                                    >

                                        <div>
                                            <span
                                                class="text-[9px] uppercase tracking-[0.35em] text-[#E85D75]"
                                            >
                                                Project completed
                                            </span>

                                            <h3
                                                class="mt-4 text-3xl font-medium tracking-[-0.04em] sm:text-4xl"
                                            >
                                                Give Review.
                                            </h3>

                                            <p
                                                class="mt-4 max-w-xl text-sm leading-7 text-[#191919]/40"
                                            >
                                                Your project is complete.
                                                We would love to hear what
                                                you think about our work.
                                            </p>
                                        </div>


                                        <div
                                            class="hidden h-16 w-16 shrink-0 items-center justify-center rounded-full border border-[#E85D75]/20 text-[#E85D75] sm:flex"
                                        >
                                            ★
                                        </div>

                                    </div>


                                    <!-- Review Form -->
                                    <form
                                        @submit.prevent="submitReview"
                                        class="mt-8"
                                    >

                                        <!-- Rating -->
                                        <div>
                                            <label
                                                class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/35"
                                            >
                                                Your rating
                                            </label>


                                            <div
                                                class="mt-5 flex items-center gap-2"
                                            >

                                                <button
                                                    v-for="rating in 5"
                                                    :key="rating"
                                                    type="button"
                                                    @click="setRating(rating)"
                                                    :aria-label="`Rate ${rating} out of 5`"
                                                    class="flex h-12 w-12 items-center justify-center text-2xl transition duration-200 hover:scale-110"
                                                    :class="
                                                        rating <= reviewRating
                                                            ? 'text-[#E85D75]'
                                                            : 'text-[#191919]/15 hover:text-[#E85D75]/60'
                                                    "
                                                >
                                                    ★
                                                </button>

                                            </div>


                                            <p
                                                v-if="reviewRating"
                                                class="mt-2 text-xs text-[#191919]/35"
                                            >
                                                {{ reviewRating }}
                                                / 5
                                            </p>

                                        </div>


                                        <!-- Comment -->
                                        <div class="mt-10">
                                            <label
                                                for="review-comment"
                                                class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/35"
                                            >
                                                Your feedback
                                            </label>

                                            <textarea
                                                id="review-comment"
                                                v-model="reviewComment"
                                                rows="5"
                                                maxlength="2000"
                                                placeholder="Tell us about your experience..."
                                                class="mt-4 w-full resize-none border border-[#191919]/10 bg-[#FFF8FA] px-5 py-5 text-sm leading-7 outline-none transition placeholder:text-[#191919]/20 focus:border-[#E85D75]"
                                            ></textarea>

                                            <div
                                                class="mt-2 text-right text-[9px] uppercase tracking-[0.2em] text-[#191919]/20"
                                            >
                                                {{ reviewComment.length }}
                                                / 2000
                                            </div>
                                        </div>


                                        <!-- Error -->
                                        <div
                                            v-if="reviewError"
                                            class="mt-6 border-l-2 border-[#E85D75] bg-[#E85D75]/5 px-5 py-4"
                                        >
                                            <p
                                                class="text-sm text-[#191919]/60"
                                            >
                                                {{ reviewError }}
                                            </p>
                                        </div>


                                        <!-- Submit -->
                                        <button
                                            type="submit"
                                            :disabled="reviewLoading"
                                            class="mt-8 inline-flex items-center gap-6 rounded-full bg-[#191919] px-8 py-5 text-xs font-semibold uppercase tracking-[0.2em] text-white transition duration-300 hover:bg-[#E85D75] disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <span>
                                                {{
                                                    reviewLoading
                                                        ? 'Submitting...'
                                                        : 'Submit review'
                                                }}
                                            </span>

                                            <span>→</span>
                                        </button>

                                    </form>


                                    <!-- Success -->
                                    <div
                                        v-if="reviewSuccess"
                                        class="mt-8 border border-[#E85D75]/20 bg-[#E85D75]/5 p-6"
                                    >

                                        <div class="flex gap-4">

                                            <div
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#E85D75] text-white"
                                            >
                                                ✓
                                            </div>

                                            <div>
                                                <p
                                                    class="text-sm font-medium text-[#191919]/70"
                                                >
                                                    Review submitted
                                                </p>

                                                <p
                                                    class="mt-2 text-sm leading-6 text-[#191919]/45"
                                                >
                                                    {{
                                                        reviewSuccess
                                                    }}
                                                </p>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </section>


                            <!-- =================================================
                                 REVIEW ALREADY EXISTS
                            ================================================== -->
                            <section
                                v-else-if="
                                    order.status === 'completed' &&
                                    order.review
                                "
                                class="mt-12"
                            >

                                <div
                                    class="border border-[#191919]/10 bg-white p-6 sm:p-8"
                                >

                                    <div
                                        class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between"
                                    >

                                        <div>
                                            <span
                                                class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                                            >
                                                Your review
                                            </span>

                                            <div
                                                class="mt-4 flex gap-1 text-xl text-[#E85D75]"
                                            >
                                                <span
                                                    v-for="star in 5"
                                                    :key="star"
                                                    :class="
                                                        star <= order.review.rating
                                                            ? 'text-[#E85D75]'
                                                            : 'text-[#191919]/10'
                                                    "
                                                >
                                                    ★
                                                </span>
                                            </div>

                                            <p
                                                v-if="order.review.comment"
                                                class="mt-5 max-w-2xl text-sm leading-7 text-[#191919]/50"
                                            >
                                                "{{ order.review.comment }}"
                                            </p>
                                        </div>


                                        <span
                                            class="w-fit rounded-full border border-[#191919]/10 px-4 py-2 text-[9px] uppercase tracking-[0.25em] text-[#191919]/35"
                                        >
                                            {{
                                                order.review.is_published
                                                    ? 'Published'
                                                    : 'Waiting for publication'
                                            }}
                                        </span>

                                    </div>

                                </div>

                            </section>

                        </div>

                    </div>
                </div>
            </div>
        </section>


        <!-- =====================================================
             CTA
        ====================================================== -->
        <section class="border-t border-[#191919]/10">
            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-32 lg:px-16"
            >

                <div
                    class="grid gap-10 md:grid-cols-[1fr_auto] md:items-end"
                >

                    <div>
                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            Need something else?
                        </span>

                        <h2
                            class="mt-5 max-w-3xl text-4xl font-medium leading-[0.95] tracking-[-0.055em] sm:text-5xl md:text-6xl"
                        >
                            Start a new project
                            <span class="text-[#191919]/20">
                                with us.
                            </span>
                        </h2>
                    </div>


                    <RouterLink
                        :to="{ name: 'order' }"
                        class="inline-flex w-fit items-center gap-5 rounded-full bg-[#E85D75] px-7 py-4 text-xs font-semibold uppercase tracking-[0.2em] text-white transition hover:scale-105"
                    >
                        Start a project
                        <span>→</span>
                    </RouterLink>

                </div>

            </div>
        </section>

    </main>
</template>

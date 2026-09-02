<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../services/api'

const order = ref(null)

const loadingOrder = ref(false)
const orderError = ref('')

const review = ref(null)
const reviewLoading = ref(false)
const reviewSubmitting = ref(false)
const reviewError = ref('')
const reviewSuccess = ref('')

const showReviewForm = ref(false)

const reviewForm = ref({
    rating: 0,
    comment: '',
})

const hoverRating = ref(0)

const loadOrder = async () => {
    loadingOrder.value = true
    orderError.value = ''

    const storedOrder = sessionStorage.getItem('latest_order')

    if (storedOrder) {
        try {
            order.value = JSON.parse(storedOrder)
        } catch (error) {
            console.error('Failed to parse latest order:', error)
            order.value = null
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil data order terbaru dari API
    |--------------------------------------------------------------------------
    |
    | Data sessionStorage hanya digunakan sebagai fallback.
    | Jika order sudah ada, kita ambil versi terbaru dari backend.
    |
    */

    if (order.value?.id) {
        try {
            const response = await api.get(
                `/orders/${order.value.id}`
            )

            order.value = response.data.data || response.data

            sessionStorage.setItem(
                'latest_order',
                JSON.stringify(order.value)
            )
        } catch (error) {
            console.error('Failed to refresh order:', error)

            /*
             * Jangan menghapus data sessionStorage.
             * Halaman tetap bisa ditampilkan menggunakan data terakhir.
             */
        }
    }

    loadingOrder.value = false

    if (!order.value) {
        orderError.value =
            'We could not find your latest project information.'
    }
}


/*
|--------------------------------------------------------------------------
| Files
|--------------------------------------------------------------------------
*/

const files = computed(() => {
    return order.value?.files || []
})


/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(price || 0))
}

const formatFileSize = (bytes) => {
    if (!bytes) {
        return '0 KB'
    }

    const mb = bytes / (1024 * 1024)

    if (mb >= 1) {
        return `${mb.toFixed(1)} MB`
    }

    return `${Math.max(1, Math.round(bytes / 1024))} KB`
}


/*
|--------------------------------------------------------------------------
| Order Status
|--------------------------------------------------------------------------
*/

const statusLabel = computed(() => {
    const status = order.value?.status

    const labels = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        processing: 'Processing',
        completed: 'Completed',
        cancelled: 'Cancelled',
    }

    return labels[status] || status || 'Pending'
})

const statusDescription = computed(() => {
    const status = order.value?.status

    const descriptions = {
        pending:
            'Your project has been received and is waiting for studio review.',

        confirmed:
            'Your project has been reviewed and confirmed by the studio.',

        processing:
            'Our team is currently working on your project.',

        completed:
            'Your project has been completed. You can now leave a review.',

        cancelled:
            'This project has been cancelled.',
    }

    return (
        descriptions[status] ||
        'Your project has been received by Teras Memori.'
    )
})


/*
|--------------------------------------------------------------------------
| Review
|--------------------------------------------------------------------------
*/

const canReview = computed(() => {
    return order.value?.status === 'completed'
})

const selectedRating = computed(() => {
    return hoverRating.value || reviewForm.value.rating
})

const ratingLabels = {
    1: 'Very poor',
    2: 'Poor',
    3: 'Good',
    4: 'Very good',
    5: 'Excellent',
}

const ratingLabel = computed(() => {
    return ratingLabels[selectedRating.value] || 'Select a rating'
})


/*
|--------------------------------------------------------------------------
| Load Existing Review
|--------------------------------------------------------------------------
*/

const loadReview = async () => {
    if (!order.value?.id) {
        return
    }

    /*
     * Review hanya dicek jika order sudah completed.
     */
    if (order.value.status !== 'completed') {
        review.value = null
        return
    }

    reviewLoading.value = true
    reviewError.value = ''

    try {
        const response = await api.get(
            `/orders/${order.value.id}/review`
        )

        review.value = response.data.data || null
    } catch (error) {
        /*
         * 404 / belum ada review tidak dianggap sebagai
         * error fatal untuk halaman.
         */
        if (error.response?.status === 404) {
            review.value = null
        } else {
            console.error('Failed to load review:', error)

            reviewError.value =
                'Unable to check your review right now.'
        }
    } finally {
        reviewLoading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Review Actions
|--------------------------------------------------------------------------
*/

const setRating = (rating) => {
    reviewForm.value.rating = rating
}

const openReviewForm = () => {
    reviewError.value = ''
    reviewSuccess.value = ''
    showReviewForm.value = true
}

const closeReviewForm = () => {
    if (reviewSubmitting.value) {
        return
    }

    showReviewForm.value = false
    hoverRating.value = 0
}

const submitReview = async () => {
    reviewError.value = ''
    reviewSuccess.value = ''

    if (!order.value) {
        reviewError.value =
            'Order information could not be found.'
        return
    }

    if (order.value.status !== 'completed') {
        reviewError.value =
            'You can only review a completed project.'
        return
    }

    if (!reviewForm.value.rating) {
        reviewError.value =
            'Please select a rating from 1 to 5 stars.'
        return
    }

    if (
        reviewForm.value.comment &&
        reviewForm.value.comment.length > 2000
    ) {
        reviewError.value =
            'Your review must not exceed 2000 characters.'
        return
    }

    /*
     * Customer ID berasal dari order.
     * Jadi kita tidak meminta customer mengisi ID secara manual.
     */
    const customerId =
        order.value.customer?.id ||
        order.value.customer_id

    if (!customerId) {
        reviewError.value =
            'Customer information could not be found.'
        return
    }

    reviewSubmitting.value = true

    try {
        const response = await api.post('/reviews', {
            customer_id: customerId,
            order_id: order.value.id,
            rating: reviewForm.value.rating,
            comment: reviewForm.value.comment.trim() || null,
        })

        review.value = response.data.data || null

        reviewSuccess.value =
            'Thank you. Your review has been submitted and is waiting for approval.'

        showReviewForm.value = false

        reviewForm.value = {
            rating: 0,
            comment: '',
        }

        hoverRating.value = 0
    } catch (error) {
        console.error('Failed to submit review:', error)

        if (error.response?.status === 422) {
            const errors = error.response.data?.errors

            if (errors) {
                const firstError = Object.values(errors)[0]

                reviewError.value = Array.isArray(firstError)
                    ? firstError[0]
                    : firstError
            } else {
                reviewError.value =
                    error.response.data?.message ||
                    'Please check your review information.'
            }
        } else {
            reviewError.value =
                error.response?.data?.message ||
                'Unable to submit your review right now. Please try again.'
        }
    } finally {
        reviewSubmitting.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(async () => {
    await loadOrder()

    if (order.value) {
        await loadReview()
    }
})
</script>

<template>
    <main class="min-h-screen bg-[#FFF8FA] text-[#191919]">

        <!-- ========================================================= -->
        <!-- HEADER -->
        <!-- ========================================================= -->

        <header
            class="border-b border-black/10 px-6 py-6 md:px-10"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between"
            >
                <RouterLink
                    to="/"
                    class="group flex items-center gap-3"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-black/15 text-[10px] font-semibold"
                    >
                        TM
                    </span>

                    <span
                        class="text-[11px] font-semibold uppercase tracking-[0.25em]"
                    >
                        Teras Memori
                    </span>
                </RouterLink>

                <RouterLink
                    to="/"
                    class="text-[9px] font-semibold uppercase tracking-[0.2em] text-black/45 transition hover:text-[#E85D75]"
                >
                    Back to website
                </RouterLink>
            </div>
        </header>


        <!-- ========================================================= -->
        <!-- NO ORDER -->
        <!-- ========================================================= -->

        <section
            v-if="!order"
            class="flex min-h-[70vh] items-center px-6 py-20 md:px-10"
        >
            <div
                class="mx-auto w-full max-w-2xl text-center"
            >
                <p
                    class="text-[10px] font-semibold uppercase tracking-[0.3em] text-[#E85D75]"
                >
                    Order not found
                </p>

                <h1
                    class="mt-5 text-5xl font-light tracking-[-0.05em] md:text-7xl"
                >
                    Nothing here.
                </h1>

                <p
                    class="mx-auto mt-6 max-w-md text-sm leading-7 text-black/45"
                >
                    We couldn't find your latest project information.
                    Please return to the website and start a new project.
                </p>

                <RouterLink
                    to="/order"
                    class="mt-10 inline-flex items-center gap-4 rounded-full bg-[#191919] px-7 py-4 text-[10px] font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-[#E85D75]"
                >
                    Start a project

                    <span class="text-base">
                        →
                    </span>
                </RouterLink>
            </div>
        </section>


        <!-- ========================================================= -->
        <!-- SUCCESS -->
        <!-- ========================================================= -->

        <template v-else>

            <!-- ===================================================== -->
            <!-- HERO -->
            <!-- ===================================================== -->

            <section
                class="relative overflow-hidden border-b border-black/10 px-6 py-20 md:px-10 md:py-28"
            >

                <!-- Decorative circles -->

                <div
                    class="pointer-events-none absolute -right-32 -top-32 h-80 w-80 rounded-full border border-[#E85D75]/20 md:h-[30rem] md:w-[30rem]"
                ></div>

                <div
                    class="pointer-events-none absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-[#F4A6B8]/10 blur-3xl"
                ></div>


                <div
                    class="relative mx-auto max-w-7xl"
                >

                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-full bg-[#E85D75] text-2xl text-white"
                    >
                        ✓
                    </div>


                    <p
                        class="mt-8 text-[10px] font-semibold uppercase tracking-[0.35em] text-[#E85D75]"
                    >
                        Project received
                    </p>


                    <h1
                        class="mt-4 max-w-4xl text-6xl font-light leading-[0.9] tracking-[-0.06em] sm:text-7xl md:text-8xl"
                    >
                        THANK
                        <span class="font-semibold italic">
                            YOU.
                        </span>
                    </h1>


                    <p
                        class="mt-8 max-w-xl text-sm leading-7 text-black/50 md:text-base"
                    >
                        Your project has been successfully submitted.
                        We've received your brief and will review
                        everything before getting back to you.
                    </p>

                </div>

            </section>


            <!-- ===================================================== -->
            <!-- ORDER INFORMATION -->
            <!-- ===================================================== -->

            <section
                class="px-6 py-16 md:px-10 md:py-24"
            >

                <div
                    class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1fr_360px]"
                >

                    <!-- ================================================= -->
                    <!-- MAIN -->
                    <!-- ================================================= -->

                    <div class="space-y-10">

                        <!-- Order number -->

                        <div
                            class="rounded-3xl border border-black/10 bg-white p-7 md:p-9"
                        >

                            <p
                                class="text-[9px] font-semibold uppercase tracking-[0.25em] text-black/35"
                            >
                                Order number
                            </p>


                            <div
                                class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                            >

                                <p
                                    class="break-all text-xl font-medium tracking-tight md:text-2xl"
                                >
                                    {{ order.order_number }}
                                </p>


                                <span
                                    class="inline-flex w-fit rounded-full bg-[#F4A6B8]/15 px-4 py-2 text-[9px] font-semibold uppercase tracking-[0.15em] text-[#E85D75]"
                                >
                                    {{ statusLabel }}
                                </span>

                            </div>


                            <p
                                class="mt-5 max-w-xl text-xs leading-6 text-black/40"
                            >
                                {{ statusDescription }}
                            </p>

                        </div>


                        <!-- ================================================= -->
                        <!-- COMPLETED REVIEW -->
                        <!-- ================================================= -->

                        <div
                            v-if="canReview"
                            class="overflow-hidden rounded-3xl border border-[#E85D75]/20 bg-white"
                        >

                            <!-- Existing review -->

                            <div
                                v-if="review"
                                class="p-7 md:p-9"
                            >

                                <div
                                    class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"
                                >

                                    <div>

                                        <p
                                            class="text-[9px] font-semibold uppercase tracking-[0.25em] text-[#E85D75]"
                                        >
                                            Your review
                                        </p>

                                        <h2
                                            class="mt-2 text-2xl font-medium tracking-tight"
                                        >
                                            Thank you for your feedback.
                                        </h2>

                                    </div>


                                    <span
                                        class="inline-flex w-fit rounded-full bg-[#F4A6B8]/15 px-4 py-2 text-[9px] font-semibold uppercase tracking-[0.15em] text-[#E85D75]"
                                    >
                                        Submitted
                                    </span>

                                </div>


                                <div class="mt-7">

                                    <div
                                        class="flex items-center gap-1"
                                    >
                                        <span
                                            v-for="star in 5"
                                            :key="star"
                                            class="text-2xl"
                                            :class="
                                                star <= review.rating
                                                    ? 'text-[#E85D75]'
                                                    : 'text-black/10'
                                            "
                                        >
                                            ★
                                        </span>
                                    </div>


                                    <p
                                        v-if="review.comment"
                                        class="mt-5 max-w-2xl text-sm leading-7 text-black/55"
                                    >
                                        “{{ review.comment }}”
                                    </p>

                                </div>


                                <div
                                    class="mt-7 border-t border-black/10 pt-5"
                                >

                                    <p
                                        class="text-[9px] leading-5 text-black/35"
                                    >
                                        Your review has been received.
                                        It may be reviewed by the studio
                                        before appearing publicly.
                                    </p>

                                </div>

                            </div>


                            <!-- Review success -->

                            <div
                                v-else-if="reviewSuccess"
                                class="p-7 md:p-9"
                            >

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-[#E85D75] text-xl text-white"
                                >
                                    ✓
                                </div>


                                <p
                                    class="mt-6 text-[9px] font-semibold uppercase tracking-[0.25em] text-[#E85D75]"
                                >
                                    Review submitted
                                </p>


                                <h2
                                    class="mt-2 text-2xl font-medium tracking-tight"
                                >
                                    Thank you for your feedback.
                                </h2>


                                <p
                                    class="mt-4 max-w-xl text-sm leading-7 text-black/45"
                                >
                                    {{ reviewSuccess }}
                                </p>

                            </div>


                            <!-- Review form -->

                            <div
                                v-else-if="showReviewForm"
                                class="p-7 md:p-9"
                            >

                                <div
                                    class="flex items-start justify-between gap-5"
                                >

                                    <div>

                                        <p
                                            class="text-[9px] font-semibold uppercase tracking-[0.25em] text-[#E85D75]"
                                        >
                                            Share your experience
                                        </p>


                                        <h2
                                            class="mt-2 text-2xl font-medium tracking-tight"
                                        >
                                            How was the result?
                                        </h2>

                                    </div>


                                    <button
                                        type="button"
                                        @click="closeReviewForm"
                                        :disabled="reviewSubmitting"
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-black/10 text-lg text-black/40 transition hover:border-black/30 hover:text-black disabled:cursor-not-allowed disabled:opacity-40"
                                        aria-label="Close review form"
                                    >
                                        ×
                                    </button>

                                </div>


                                <!-- Rating -->

                                <div class="mt-8">

                                    <p
                                        class="text-[9px] font-semibold uppercase tracking-[0.25em] text-black/35"
                                    >
                                        Rating
                                    </p>


                                    <div
                                        class="mt-4 flex items-center gap-2"
                                        @mouseleave="hoverRating = 0"
                                    >

                                        <button
                                            v-for="star in 5"
                                            :key="star"
                                            type="button"
                                            @mouseenter="hoverRating = star"
                                            @click="setRating(star)"
                                            class="text-4xl leading-none transition duration-200 hover:scale-110"
                                            :class="
                                                star <= selectedRating
                                                    ? 'text-[#E85D75]'
                                                    : 'text-black/10'
                                            "
                                            :aria-label="`${star} star${star > 1 ? 's' : ''}`"
                                        >
                                            ★
                                        </button>

                                    </div>


                                    <p
                                        class="mt-3 text-[10px] uppercase tracking-[0.18em] text-black/35"
                                    >
                                        {{ ratingLabel }}
                                    </p>

                                </div>


                                <!-- Comment -->

                                <div class="mt-8">

                                    <label
                                        for="review-comment"
                                        class="text-[9px] font-semibold uppercase tracking-[0.25em] text-black/35"
                                    >
                                        Your thoughts
                                    </label>


                                    <textarea
                                        id="review-comment"
                                        v-model="reviewForm.comment"
                                        rows="5"
                                        maxlength="2000"
                                        placeholder="Tell us what you think about the result..."
                                        class="mt-4 w-full resize-none rounded-2xl border border-black/10 bg-[#FFF8FA] px-5 py-4 text-sm leading-7 outline-none transition placeholder:text-black/25 focus:border-[#E85D75]/50 focus:ring-2 focus:ring-[#E85D75]/10"
                                    ></textarea>


                                    <div
                                        class="mt-2 text-right text-[9px] text-black/25"
                                    >
                                        {{ reviewForm.comment.length }} / 2000
                                    </div>

                                </div>


                                <!-- Error -->

                                <div
                                    v-if="reviewError"
                                    class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-xs leading-6 text-red-600"
                                >
                                    {{ reviewError }}
                                </div>


                                <!-- Submit -->

                                <button
                                    type="button"
                                    @click="submitReview"
                                    :disabled="reviewSubmitting"
                                    class="mt-7 inline-flex w-full items-center justify-center gap-5 rounded-full bg-[#191919] px-7 py-4 text-[10px] font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-[#E85D75] disabled:cursor-not-allowed disabled:opacity-50"
                                >

                                    <span>
                                        {{
                                            reviewSubmitting
                                                ? 'Submitting...'
                                                : 'Submit review'
                                        }}
                                    </span>


                                    <span
                                        v-if="!reviewSubmitting"
                                        class="text-lg"
                                    >
                                        →
                                    </span>

                                </button>

                            </div>


                            <!-- Review CTA -->

                            <div
                                v-else
                                class="p-7 md:p-9"
                            >

                                <div
                                    class="flex flex-col gap-8 md:flex-row md:items-end md:justify-between"
                                >

                                    <div>

                                        <p
                                            class="text-[9px] font-semibold uppercase tracking-[0.25em] text-[#E85D75]"
                                        >
                                            Project completed
                                        </p>


                                        <h2
                                            class="mt-2 text-2xl font-medium tracking-tight"
                                        >
                                            Tell us what you think.
                                        </h2>


                                        <p
                                            class="mt-4 max-w-xl text-sm leading-7 text-black/45"
                                        >
                                            Your feedback helps us improve
                                            and helps future customers
                                            understand our work.
                                        </p>

                                    </div>


                                    <button
                                        type="button"
                                        @click="openReviewForm"
                                        class="group inline-flex w-fit shrink-0 items-center gap-5 rounded-full bg-[#E85D75] px-7 py-4 text-[10px] font-semibold uppercase tracking-[0.2em] text-white transition hover:scale-[1.02] hover:bg-[#191919]"
                                    >

                                        Give a review

                                        <span
                                            class="text-lg transition-transform duration-300 group-hover:translate-x-1"
                                        >
                                            →
                                        </span>

                                    </button>

                                </div>

                            </div>

                        </div>


                        <!-- Review loading -->

                        <div
                            v-if="canReview && reviewLoading"
                            class="rounded-3xl border border-black/10 bg-white px-7 py-5 md:px-9"
                        >
                            <p
                                class="text-[9px] uppercase tracking-[0.2em] text-black/30"
                            >
                                Checking review status...
                            </p>
                        </div>


                        <!-- Review error -->

                        <div
                            v-if="canReview && reviewError && !showReviewForm"
                            class="rounded-2xl border border-black/10 bg-white px-5 py-4 text-xs leading-6 text-black/45"
                        >
                            {{ reviewError }}
                        </div>


                        <!-- ================================================= -->
                        <!-- FILES -->
                        <!-- ================================================= -->

                        <div
                            class="rounded-3xl border border-black/10 bg-white p-7 md:p-9"
                        >

                            <div
                                class="flex flex-col gap-4 border-b border-black/10 pb-6 sm:flex-row sm:items-end sm:justify-between"
                            >

                                <div>

                                    <p
                                        class="text-[9px] font-semibold uppercase tracking-[0.25em] text-[#E85D75]"
                                    >
                                        Project assets
                                    </p>


                                    <h2
                                        class="mt-2 text-2xl font-medium tracking-tight"
                                    >
                                        Your photos
                                    </h2>

                                </div>


                                <span
                                    class="text-[9px] uppercase tracking-[0.18em] text-black/35"
                                >
                                    {{ files.length }}
                                    file{{ files.length === 1 ? '' : 's' }}
                                </span>

                            </div>


                            <!-- No files -->

                            <div
                                v-if="!files.length"
                                class="py-12 text-center"
                            >

                                <div
                                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-black/[0.03] text-xl text-black/30"
                                >
                                    —
                                </div>


                                <p
                                    class="mt-5 text-sm font-medium"
                                >
                                    No photos attached
                                </p>


                                <p
                                    class="mx-auto mt-2 max-w-sm text-xs leading-5 text-black/40"
                                >
                                    No files were attached to this project.
                                </p>

                            </div>


                            <!-- Files -->

                            <div
                                v-else
                                class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4"
                            >

                                <a
                                    v-for="file in files"
                                    :key="file.id"
                                    :href="file.file_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group relative aspect-square overflow-hidden rounded-2xl bg-black/5"
                                >

                                    <img
                                        :src="file.file_url"
                                        :alt="file.file_name"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    />


                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"
                                    ></div>


                                    <div
                                        class="absolute bottom-0 left-0 right-0 translate-y-2 p-4 opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100"
                                    >

                                        <p
                                            class="truncate text-[10px] font-medium text-white"
                                        >
                                            {{ file.file_name }}
                                        </p>


                                        <p
                                            class="mt-1 text-[9px] text-white/60"
                                        >
                                            {{ formatFileSize(file.file_size) }}
                                        </p>

                                    </div>

                                </a>

                            </div>


                            <p
                                v-if="files.length"
                                class="mt-6 text-[9px] uppercase tracking-[0.15em] text-black/30"
                            >
                                Click an image to view the original file
                            </p>

                        </div>


                        <!-- ================================================= -->
                        <!-- NOTES -->
                        <!-- ================================================= -->

                        <div
                            v-if="order.notes"
                            class="rounded-3xl border border-black/10 bg-white p-7 md:p-9"
                        >

                            <p
                                class="text-[9px] font-semibold uppercase tracking-[0.25em] text-[#E85D75]"
                            >
                                Your brief
                            </p>


                            <p
                                class="mt-5 whitespace-pre-line text-sm leading-7 text-black/60"
                            >
                                {{ order.notes }}
                            </p>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- SUMMARY -->
                    <!-- ================================================= -->

                    <aside
                        class="lg:sticky lg:top-8 lg:self-start"
                    >

                        <div
                            class="overflow-hidden rounded-3xl bg-[#191919] text-white"
                        >

                            <div
                                class="border-b border-white/10 px-7 py-6"
                            >

                                <p
                                    class="text-[9px] font-semibold uppercase tracking-[0.3em] text-[#F4A6B8]"
                                >
                                    Project summary
                                </p>


                                <h2
                                    class="mt-2 text-xl font-medium"
                                >
                                    {{ order.customer?.name }}
                                </h2>

                            </div>


                            <div class="space-y-6 px-7 py-7">

                                <!-- Service -->

                                <div>

                                    <p
                                        class="text-[9px] uppercase tracking-[0.2em] text-white/35"
                                    >
                                        Service
                                    </p>


                                    <div
                                        v-if="order.items?.length"
                                        class="mt-3 space-y-3"
                                    >

                                        <div
                                            v-for="item in order.items"
                                            :key="item.id"
                                            class="flex items-start justify-between gap-4"
                                        >

                                            <div>

                                                <p
                                                    class="text-sm font-medium"
                                                >
                                                    {{ item.service?.name }}
                                                </p>


                                                <p
                                                    class="mt-1 text-[10px] text-white/35"
                                                >
                                                    {{ item.quantity }} ×
                                                    {{ formatPrice(item.price) }}
                                                </p>

                                            </div>


                                            <p
                                                class="shrink-0 text-xs text-white/60"
                                            >
                                                {{ formatPrice(item.subtotal) }}
                                            </p>

                                        </div>

                                    </div>

                                </div>


                                <!-- Photos -->

                                <div
                                    class="flex items-center justify-between border-t border-white/10 pt-5"
                                >

                                    <p
                                        class="text-[9px] uppercase tracking-[0.2em] text-white/35"
                                    >
                                        Uploaded photos
                                    </p>


                                    <p class="text-sm">
                                        {{ files.length }}
                                    </p>

                                </div>


                                <!-- Status -->

                                <div
                                    class="flex items-center justify-between border-t border-white/10 pt-5"
                                >

                                    <p
                                        class="text-[9px] uppercase tracking-[0.2em] text-white/35"
                                    >
                                        Status
                                    </p>


                                    <p
                                        class="text-xs text-[#F4A6B8]"
                                    >
                                        {{ statusLabel }}
                                    </p>

                                </div>


                                <!-- Total -->

                                <div
                                    class="border-t border-white/10 pt-6"
                                >

                                    <p
                                        class="text-[9px] uppercase tracking-[0.2em] text-white/35"
                                    >
                                        Estimated total
                                    </p>


                                    <p
                                        class="mt-2 text-3xl font-light tracking-tight"
                                    >
                                        {{ formatPrice(order.total_amount) }}
                                    </p>

                                </div>

                            </div>


                            <div
                                class="border-t border-white/10 px-7 py-5"
                            >

                                <p
                                    class="text-[9px] leading-5 text-white/30"
                                >
                                    Final pricing may be adjusted after
                                    the studio reviews your project.
                                </p>

                            </div>

                        </div>

                    </aside>

                </div>

            </section>


            <!-- ========================================================= -->
            <!-- NEXT STEP -->
            <!-- ========================================================= -->

            <section
                class="bg-[#191919] px-6 py-20 text-white md:px-10 md:py-28"
            >

                <div
                    class="mx-auto flex max-w-7xl flex-col gap-10 md:flex-row md:items-end md:justify-between"
                >

                    <div>

                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.3em] text-[#F4A6B8]"
                        >
                            What's next?
                        </p>


                        <h2
                            class="mt-5 max-w-3xl text-4xl font-light leading-tight tracking-[-0.04em] md:text-6xl"
                        >
                            <template v-if="order.status === 'completed'">
                                Your project is
                                <span class="font-semibold italic">
                                    complete.
                                </span>
                            </template>

                            <template v-else>
                                We'll review your project
                                <span class="font-semibold italic">
                                    shortly.
                                </span>
                            </template>
                        </h2>


                        <p
                            class="mt-6 max-w-xl text-sm leading-7 text-white/40"
                        >
                            <template v-if="order.status === 'completed'">
                                Thank you for trusting Teras Memori
                                with your photographs.
                            </template>

                            <template v-else>
                                Keep your order number somewhere safe.
                                Our studio team will use it when communicating
                                with you about the project.
                            </template>
                        </p>

                    </div>


                    <RouterLink
                        to="/"
                        class="group inline-flex w-fit shrink-0 items-center gap-5 rounded-full bg-white px-7 py-4 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#191919] transition hover:bg-[#E85D75] hover:text-white"
                    >
                        Back to website

                        <span
                            class="text-lg transition-transform duration-300 group-hover:translate-x-1"
                        >
                            →
                        </span>
                    </RouterLink>

                </div>

            </section>


            <!-- ========================================================= -->
            <!-- FOOTER -->
            <!-- ========================================================= -->

            <footer
                class="bg-[#191919] px-6 pb-10 md:px-10"
            >

                <div
                    class="mx-auto flex max-w-7xl flex-col gap-4 border-t border-white/10 pt-7 sm:flex-row sm:items-center sm:justify-between"
                >

                    <p
                        class="text-[9px] uppercase tracking-[0.25em] text-white/25"
                    >
                        TERAS MEMORI STUDIO
                    </p>


                    <p
                        class="text-[9px] uppercase tracking-[0.2em] text-white/20"
                    >
                        We turn photographs into lasting memories.
                    </p>

                </div>

            </footer>

        </template>

    </main>
</template>


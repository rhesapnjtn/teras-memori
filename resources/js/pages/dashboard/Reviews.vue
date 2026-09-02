```vue
<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../services/api'

const reviews = ref([])
const loading = ref(true)
const errorMessage = ref('')
const searchQuery = ref('')
const ratingFilter = ref('All')

const selectedReview = ref(null)
const showDeleteModal = ref(false)
const reviewToDelete = ref(null)
const actionLoading = ref(false)

const fetchReviews = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/admin/reviews')

        reviews.value = response.data.data || []
    } catch (error) {
        console.error('Failed to load reviews:', error)

        errorMessage.value =
            'Unable to load reviews right now. Please try again.'
    } finally {
        loading.value = false
    }
}

const totalReviews = computed(() => reviews.value.length)

const publishedReviews = computed(() => {
    return reviews.value.filter(
        (review) => review.is_published
    ).length
})

const draftReviews = computed(() => {
    return reviews.value.filter(
        (review) => !review.is_published
    ).length
})

const averageRating = computed(() => {
    if (!reviews.value.length) {
        return '0.0'
    }

    const total = reviews.value.reduce(
        (sum, review) => sum + Number(review.rating || 0),
        0
    )

    return (total / reviews.value.length).toFixed(1)
})

const filteredReviews = computed(() => {
    const query = searchQuery.value.toLowerCase().trim()

    return reviews.value.filter((review) => {
        const matchesSearch =
            !query ||
            review.customer?.name
                ?.toLowerCase()
                .includes(query) ||
            review.order?.order_number
                ?.toLowerCase()
                .includes(query) ||
            review.comment
                ?.toLowerCase()
                .includes(query)

        const matchesRating =
            ratingFilter.value === 'All' ||
            Number(review.rating) === Number(ratingFilter.value)

        return matchesSearch && matchesRating
    })
})

const formatDate = (date) => {
    if (!date) return '-'

    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

const formatDateTime = (date) => {
    if (!date) return '-'

    return new Date(date).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const getInitials = (name) => {
    if (!name) return '?'

    return name
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase()
}

const openReview = async (review) => {
    selectedReview.value = review

    try {
        const response = await api.get(
            `/admin/reviews/${review.id}`
        )

        selectedReview.value = response.data.data
    } catch (error) {
        console.error('Failed to load review detail:', error)
    }
}

const closeReview = () => {
    selectedReview.value = null
}

const toggleVisibility = async (review) => {
    actionLoading.value = true

    try {
        const response = await api.patch(
            `/admin/reviews/${review.id}/visibility`
        )

        const updatedReview = response.data.data

        const index = reviews.value.findIndex(
            (item) => item.id === review.id
        )

        if (index !== -1) {
            reviews.value[index] = updatedReview
        }

        if (
            selectedReview.value &&
            selectedReview.value.id === review.id
        ) {
            selectedReview.value = updatedReview
        }
    } catch (error) {
        console.error(
            'Failed to update review visibility:',
            error
        )
    } finally {
        actionLoading.value = false
    }
}

const confirmDelete = (review) => {
    reviewToDelete.value = review
    showDeleteModal.value = true
}

const cancelDelete = () => {
    reviewToDelete.value = null
    showDeleteModal.value = false
}

const deleteReview = async () => {
    if (!reviewToDelete.value) return

    actionLoading.value = true

    try {
        await api.delete(
            `/admin/reviews/${reviewToDelete.value.id}`
        )

        reviews.value = reviews.value.filter(
            (review) =>
                review.id !== reviewToDelete.value.id
        )

        if (
            selectedReview.value?.id ===
            reviewToDelete.value.id
        ) {
            selectedReview.value = null
        }

        cancelDelete()
    } catch (error) {
        console.error('Failed to delete review:', error)
    } finally {
        actionLoading.value = false
    }
}

const renderStars = (rating) => {
    return Array.from(
        { length: 5 },
        (_, index) => index < Number(rating)
    )
}

onMounted(() => {
    fetchReviews()
})
</script>

<template>
    <div class="space-y-8">

        <!-- =====================================================
             HEADER
        ====================================================== -->
        <section
            class="relative overflow-hidden border-b border-[#191919]/10 pb-10"
        >

            <div
                class="pointer-events-none absolute -right-20 -top-32 h-72 w-72 rounded-full border border-[#E85D75]/10"
            ></div>

            <div
                class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 rounded-full bg-[#F4A6B8]/10 blur-[70px]"
            ></div>

            <div class="relative">

                <div class="mb-5 flex items-center gap-3">

                    <span
                        class="h-px w-10 bg-[#E85D75]"
                    ></span>

                    <span
                        class="text-[9px] uppercase tracking-[0.35em] text-[#191919]/35"
                    >
                        Customer feedback
                    </span>

                </div>

                <div
                    class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end"
                >

                    <div>

                        <h1
                            class="text-5xl font-medium leading-[0.9] tracking-[-0.065em] text-[#191919] sm:text-6xl"
                        >
                            REVIEWS<span class="text-[#191919]/15">.</span>
                        </h1>

                        <p
                            class="mt-5 max-w-xl text-sm leading-6 text-[#191919]/40"
                        >
                            Manage customer feedback, control
                            publication visibility and keep track
                            of the experience behind every project.
                        </p>

                    </div>

                    <div
                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/25 lg:text-right"
                    >
                        Teras Memori
                        <br />
                        Studio feedback
                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             STATISTICS
        ====================================================== -->
        <section
            class="grid gap-px overflow-hidden border border-[#191919]/10 bg-[#191919]/10 sm:grid-cols-2 lg:grid-cols-4"
        >

            <!-- Total -->
            <div class="bg-[#FFF8FA] p-6">

                <div
                    class="flex items-start justify-between"
                >

                    <span
                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Total reviews
                    </span>

                    <span
                        class="text-xs text-[#191919]/20"
                    >
                        01
                    </span>

                </div>

                <p
                    class="mt-8 text-4xl font-medium tracking-[-0.05em]"
                >
                    {{ totalReviews }}
                </p>

            </div>


            <!-- Average -->
            <div class="bg-[#FFF8FA] p-6">

                <div
                    class="flex items-start justify-between"
                >

                    <span
                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Average rating
                    </span>

                    <span class="text-[#E85D75]">
                        ★
                    </span>

                </div>

                <div
                    class="mt-7 flex items-end gap-3"
                >

                    <p
                        class="text-4xl font-medium tracking-[-0.05em]"
                    >
                        {{ averageRating }}
                    </p>

                    <span
                        class="mb-1 text-xs text-[#191919]/30"
                    >
                        / 5
                    </span>

                </div>

            </div>


            <!-- Published -->
            <div class="bg-[#FFF8FA] p-6">

                <div
                    class="flex items-start justify-between"
                >

                    <span
                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Published
                    </span>

                    <span
                        class="h-2 w-2 rounded-full bg-[#E85D75]"
                    ></span>

                </div>

                <p
                    class="mt-8 text-4xl font-medium tracking-[-0.05em]"
                >
                    {{ publishedReviews }}
                </p>

            </div>


            <!-- Draft -->
            <div class="bg-[#FFF8FA] p-6">

                <div
                    class="flex items-start justify-between"
                >

                    <span
                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Draft
                    </span>

                    <span
                        class="h-2 w-2 rounded-full bg-[#191919]/20"
                    ></span>

                </div>

                <p
                    class="mt-8 text-4xl font-medium tracking-[-0.05em]"
                >
                    {{ draftReviews }}
                </p>

            </div>

        </section>


        <!-- =====================================================
             FILTER
        ====================================================== -->
        <section
            class="flex flex-col gap-4 border-y border-[#191919]/10 py-5 lg:flex-row lg:items-center lg:justify-between"
        >

            <!-- Search -->
            <div class="relative w-full lg:max-w-md">

                <span
                    class="pointer-events-none absolute left-0 top-1/2 -translate-y-1/2 text-sm text-[#191919]/25"
                >
                    ⌕
                </span>

                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search customer, order or review..."
                    class="w-full border-b border-[#191919]/15 bg-transparent py-3 pl-7 pr-3 text-sm text-[#191919] outline-none placeholder:text-[#191919]/25 focus:border-[#E85D75]"
                />

            </div>


            <!-- Rating filter -->
            <div
                class="flex flex-wrap items-center gap-2"
            >

                <span
                    class="mr-2 text-[9px] uppercase tracking-[0.25em] text-[#191919]/25"
                >
                    Rating
                </span>

                <button
                    v-for="rating in ['All', 5, 4, 3, 2, 1]"
                    :key="rating"
                    type="button"
                    @click="ratingFilter = rating"
                    class="rounded-full border px-4 py-2 text-[9px] uppercase tracking-[0.2em] transition duration-300"
                    :class="
                        ratingFilter === rating
                            ? 'border-[#191919] bg-[#191919] text-white'
                            : 'border-[#191919]/10 text-[#191919]/35 hover:border-[#E85D75]/40 hover:text-[#E85D75]'
                    "
                >
                    <template v-if="rating === 'All'">
                        All
                    </template>

                    <template v-else>
                        {{ rating }} ★
                    </template>
                </button>

            </div>

        </section>


        <!-- =====================================================
             ERROR
        ====================================================== -->
        <div
            v-if="errorMessage"
            class="border-y border-[#191919]/10 py-20 text-center"
        >

            <p class="text-sm text-[#191919]/45">
                {{ errorMessage }}
            </p>

            <button
                type="button"
                @click="fetchReviews"
                class="mt-6 rounded-full border border-[#191919]/15 px-6 py-3 text-[10px] uppercase tracking-[0.2em] transition hover:border-[#191919]/40"
            >
                Try again
            </button>

        </div>


        <!-- =====================================================
             LOADING
        ====================================================== -->
        <div
            v-else-if="loading"
            class="space-y-4"
        >

            <div
                v-for="item in 5"
                :key="item"
                class="animate-pulse border border-[#191919]/10 p-6"
            >

                <div
                    class="h-4 w-32 bg-[#191919]/[0.05]"
                ></div>

                <div
                    class="mt-5 h-3 w-64 bg-[#191919]/[0.04]"
                ></div>

                <div
                    class="mt-3 h-3 w-40 bg-[#191919]/[0.04]"
                ></div>

            </div>

        </div>


        <!-- =====================================================
             EMPTY
        ====================================================== -->
        <div
            v-else-if="filteredReviews.length === 0"
            class="border-y border-[#191919]/10 py-24 text-center"
        >

            <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-[#191919]/10"
            >
                <span class="text-xl text-[#191919]/20">
                    ★
                </span>
            </div>

            <p
                class="mt-6 text-sm text-[#191919]/40"
            >
                No reviews found.
            </p>

            <p
                class="mt-2 text-xs text-[#191919]/25"
            >
                Try changing your search or rating filter.
            </p>

        </div>


        <!-- =====================================================
             REVIEWS LIST
        ====================================================== -->
        <section
            v-else
            class="space-y-3"
        >

            <article
                v-for="review in filteredReviews"
                :key="review.id"
                class="group border border-[#191919]/10 bg-[#FFF8FA] transition duration-300 hover:border-[#191919]/20"
            >

                <div
                    class="flex flex-col gap-6 p-5 sm:p-6 lg:flex-row lg:items-center"
                >

                    <!-- Customer -->
                    <div
                        class="flex min-w-0 items-center gap-4 lg:w-[250px]"
                    >

                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#191919] text-[10px] font-medium text-white"
                        >
                            {{ getInitials(review.customer?.name) }}
                        </div>

                        <div class="min-w-0">

                            <h2
                                class="truncate text-sm font-medium text-[#191919]/80"
                            >
                                {{ review.customer?.name || 'Unknown customer' }}
                            </h2>

                            <p
                                class="mt-1 truncate text-[10px] text-[#191919]/30"
                            >
                                {{ review.order?.order_number || 'No order' }}
                            </p>

                        </div>

                    </div>


                    <!-- Rating -->
                    <div class="lg:w-[130px]">

                        <div class="flex gap-0.5">

                            <span
                                v-for="(active, index) in renderStars(review.rating)"
                                :key="index"
                                class="text-sm"
                                :class="
                                    active
                                        ? 'text-[#E85D75]'
                                        : 'text-[#191919]/10'
                                "
                            >
                                ★
                            </span>

                        </div>

                        <p
                            class="mt-1 text-[9px] uppercase tracking-[0.2em] text-[#191919]/25"
                        >
                            {{ review.rating }}/5
                        </p>

                    </div>


                    <!-- Comment -->
                    <div class="min-w-0 flex-1">

                        <p
                            class="line-clamp-2 text-sm leading-6 text-[#191919]/50"
                        >
                            {{
                                review.comment ||
                                'No comment provided.'
                            }}
                        </p>

                        <p
                            class="mt-2 text-[9px] uppercase tracking-[0.2em] text-[#191919]/20"
                        >
                            {{ formatDate(review.created_at) }}
                        </p>

                    </div>


                    <!-- Status -->
                    <div
                        class="flex items-center gap-3 lg:w-[110px] lg:justify-center"
                    >

                        <span
                            class="flex items-center gap-2 text-[9px] uppercase tracking-[0.2em]"
                            :class="
                                review.is_published
                                    ? 'text-[#E85D75]'
                                    : 'text-[#191919]/30'
                            "
                        >

                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="
                                    review.is_published
                                        ? 'bg-[#E85D75]'
                                        : 'bg-[#191919]/20'
                                "
                            ></span>

                            {{
                                review.is_published
                                    ? 'Published'
                                    : 'Draft'
                            }}

                        </span>

                    </div>


                    <!-- Actions -->
                    <div
                        class="flex items-center gap-2 lg:ml-auto"
                    >

                        <button
                            type="button"
                            @click="openReview(review)"
                            class="rounded-full border border-[#191919]/10 px-4 py-2 text-[9px] uppercase tracking-[0.2em] text-[#191919]/40 transition hover:border-[#191919]/30 hover:text-[#191919]"
                        >
                            View
                        </button>

                        <button
                            type="button"
                            @click="toggleVisibility(review)"
                            :disabled="actionLoading"
                            class="rounded-full border px-4 py-2 text-[9px] uppercase tracking-[0.2em] transition disabled:cursor-not-allowed disabled:opacity-40"
                            :class="
                                review.is_published
                                    ? 'border-[#191919]/10 text-[#191919]/35 hover:border-[#191919]/30'
                                    : 'border-[#E85D75]/30 text-[#E85D75] hover:bg-[#E85D75]/5'
                            "
                        >
                            {{
                                review.is_published
                                    ? 'Unpublish'
                                    : 'Publish'
                            }}
                        </button>

                        <button
                            type="button"
                            @click="confirmDelete(review)"
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-[#191919]/10 text-sm text-[#191919]/30 transition hover:border-red-300 hover:text-red-500"
                            aria-label="Delete review"
                        >
                            ×
                        </button>

                    </div>

                </div>

            </article>

        </section>


        <!-- =====================================================
             REVIEW DETAIL MODAL
        ====================================================== -->
        <Transition name="modal">

            <div
                v-if="selectedReview"
                class="fixed inset-0 z-[100] overflow-y-auto bg-[#191919]/80 px-4 py-6 backdrop-blur-md sm:px-8 sm:py-10"
                @click.self="closeReview"
            >

                <div
                    class="mx-auto flex min-h-full max-w-2xl items-center justify-center"
                >

                    <div
                        class="relative w-full overflow-hidden border border-white/10 bg-[#FFF8FA]"
                    >

                        <!-- Close -->
                        <button
                            type="button"
                            @click="closeReview"
                            class="absolute right-5 top-5 z-10 flex h-10 w-10 items-center justify-center rounded-full border border-[#191919]/10 text-xl text-[#191919]/40 transition hover:border-[#191919]/30 hover:text-[#191919]"
                            aria-label="Close"
                        >
                            ×
                        </button>


                        <div class="p-7 sm:p-10">

                            <!-- Label -->
                            <div class="flex items-center gap-3">

                                <span
                                    class="h-px w-8 bg-[#E85D75]"
                                ></span>

                                <span
                                    class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                                >
                                    Review detail
                                </span>

                            </div>


                            <!-- Customer -->
                            <div
                                class="mt-10 flex items-center gap-4"
                            >

                                <div
                                    class="flex h-14 w-14 items-center justify-center rounded-full bg-[#191919] text-xs font-medium text-white"
                                >
                                    {{
                                        getInitials(
                                            selectedReview.customer?.name
                                        )
                                    }}
                                </div>

                                <div>

                                    <h2
                                        class="text-xl font-medium tracking-[-0.025em] text-[#191919]/80"
                                    >
                                        {{
                                            selectedReview.customer?.name ||
                                            'Unknown customer'
                                        }}
                                    </h2>

                                    <p
                                        class="mt-1 text-xs text-[#191919]/30"
                                    >
                                        {{
                                            selectedReview.order
                                                ?.order_number ||
                                            'No order'
                                        }}
                                    </p>

                                </div>

                            </div>


                            <!-- Rating -->
                            <div class="mt-8">

                                <div class="flex gap-1">

                                    <span
                                        v-for="(active, index) in renderStars(
                                            selectedReview.rating
                                        )"
                                        :key="index"
                                        class="text-xl"
                                        :class="
                                            active
                                                ? 'text-[#E85D75]'
                                                : 'text-[#191919]/10'
                                        "
                                    >
                                        ★
                                    </span>

                                </div>

                                <p
                                    class="mt-2 text-[9px] uppercase tracking-[0.25em] text-[#191919]/25"
                                >
                                    {{ selectedReview.rating }} out of 5
                                </p>

                            </div>


                            <!-- Comment -->
                            <div
                                class="mt-10 border-y border-[#191919]/10 py-8"
                            >

                                <p
                                    class="text-2xl font-medium leading-[1.15] tracking-[-0.035em] text-[#191919]/75"
                                >
                                    {{
                                        selectedReview.comment ||
                                        'No comment provided.'
                                    }}
                                </p>

                            </div>


                            <!-- Meta -->
                            <div
                                class="mt-8 grid gap-6 sm:grid-cols-2"
                            >

                                <div>

                                    <span
                                        class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/25"
                                    >
                                        Submitted
                                    </span>

                                    <p
                                        class="mt-2 text-sm text-[#191919]/55"
                                    >
                                        {{
                                            formatDateTime(
                                                selectedReview.created_at
                                            )
                                        }}
                                    </p>

                                </div>


                                <div>

                                    <span
                                        class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/25"
                                    >
                                        Status
                                    </span>

                                    <p
                                        class="mt-2 flex items-center gap-2 text-sm"
                                        :class="
                                            selectedReview.is_published
                                                ? 'text-[#E85D75]'
                                                : 'text-[#191919]/45'
                                        "
                                    >

                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="
                                                selectedReview.is_published
                                                    ? 'bg-[#E85D75]'
                                                    : 'bg-[#191919]/20'
                                            "
                                        ></span>

                                        {{
                                            selectedReview.is_published
                                                ? 'Published'
                                                : 'Draft'
                                        }}

                                    </p>

                                </div>

                            </div>


                            <!-- Actions -->
                            <div
                                class="mt-10 flex flex-col gap-3 sm:flex-row"
                            >

                                <button
                                    type="button"
                                    @click="
                                        toggleVisibility(
                                            selectedReview
                                        )
                                    "
                                    :disabled="actionLoading"
                                    class="flex-1 rounded-full bg-[#191919] px-6 py-4 text-[9px] font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-[#E85D75] disabled:opacity-40"
                                >
                                    {{
                                        selectedReview.is_published
                                            ? 'Unpublish review'
                                            : 'Publish review'
                                    }}
                                </button>

                                <button
                                    type="button"
                                    @click="
                                        confirmDelete(
                                            selectedReview
                                        )
                                    "
                                    class="rounded-full border border-[#191919]/10 px-6 py-4 text-[9px] uppercase tracking-[0.2em] text-[#191919]/40 transition hover:border-red-300 hover:text-red-500"
                                >
                                    Delete
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </Transition>


        <!-- =====================================================
             DELETE MODAL
        ====================================================== -->
        <Transition name="modal">

            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-[110] flex items-center justify-center bg-[#191919]/70 px-5 backdrop-blur-sm"
            >

                <div
                    class="w-full max-w-md border border-[#191919]/10 bg-[#FFF8FA] p-7 sm:p-9"
                >

                    <span
                        class="text-[9px] uppercase tracking-[0.3em] text-[#E85D75]"
                    >
                        Delete review
                    </span>

                    <h2
                        class="mt-5 text-3xl font-medium leading-[0.95] tracking-[-0.05em] text-[#191919]"
                    >
                        Remove this review<span
                            class="text-[#191919]/15"
                        >?</span>
                    </h2>

                    <p
                        class="mt-5 text-sm leading-6 text-[#191919]/40"
                    >
                        Review dari
                        <strong class="font-medium text-[#191919]/65">
                            {{
                                reviewToDelete?.customer?.name ||
                                'customer'
                            }}
                        </strong>
                        akan dihapus secara permanen.
                    </p>

                    <div
                        class="mt-8 flex flex-col gap-3 sm:flex-row"
                    >

                        <button
                            type="button"
                            @click="cancelDelete"
                            class="flex-1 rounded-full border border-[#191919]/10 px-5 py-3 text-[9px] uppercase tracking-[0.2em] text-[#191919]/40 transition hover:border-[#191919]/30"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            @click="deleteReview"
                            :disabled="actionLoading"
                            class="flex-1 rounded-full bg-[#191919] px-5 py-3 text-[9px] uppercase tracking-[0.2em] text-white transition hover:bg-red-500 disabled:opacity-40"
                        >
                            {{
                                actionLoading
                                    ? 'Deleting...'
                                    : 'Delete review'
                            }}
                        </button>

                    </div>

                </div>

            </div>

        </Transition>

    </div>
</template>


<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
```

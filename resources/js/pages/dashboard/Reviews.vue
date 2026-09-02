<script setup>
import { onMounted, ref } from 'vue'
import api from '../../services/api'

const reviews = ref([])
const loading = ref(true)
const errorMessage = ref('')

const fetchReviews = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/reviews')
        reviews.value = response.data?.data || []
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Gagal mengambil data reviews.'
    } finally {
        loading.value = false
    }
}

const ratingStars = (rating) => {
    return Math.max(0, Math.min(5, Number(rating || 0)))
}

onMounted(fetchReviews)
</script>

<template>
    <section>
        <!-- Header -->
        <div class="mb-10">
            <p
                class="text-[9px] font-semibold uppercase tracking-[0.35em] text-[#191919]/30"
            >
                Engagement / Reviews
            </p>

            <h2
                class="mt-3 text-4xl font-medium tracking-[-0.06em] sm:text-5xl"
            >
                REVIEWS<span class="text-[#E85D75]">.</span>
            </h2>

            <p class="mt-4 max-w-xl text-sm leading-6 text-[#191919]/40">
                See what customers say about their experience with Teras
                Memori.
            </p>
        </div>

        <!-- Error -->
        <div
            v-if="errorMessage"
            class="mb-6 border-l-2 border-red-400 bg-red-50 px-5 py-4"
        >
            <p class="text-xs text-red-600">
                {{ errorMessage }}
            </p>
        </div>

        <!-- Loading -->
        <div
            v-if="loading"
            class="border border-[#191919]/10 bg-white p-10"
        >
            <div class="flex min-h-[250px] items-center justify-center">
                <div class="text-center">
                    <div
                        class="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-[#191919]/10 border-t-[#E85D75]"
                    ></div>

                    <p
                        class="mt-5 text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Loading reviews
                    </p>
                </div>
            </div>
        </div>

        <!-- Reviews -->
        <div
            v-else-if="reviews.length"
            class="grid gap-5 md:grid-cols-2"
        >
            <article
                v-for="review in reviews"
                :key="review.id"
                class="border border-[#191919]/10 bg-white p-7 transition duration-300 hover:-translate-y-1 hover:border-[#E85D75]/30"
            >
                <div class="flex items-start justify-between gap-5">
                    <div>
                        <p class="text-sm font-semibold">
                            {{ review.customer?.name || 'Customer' }}
                        </p>

                        <p
                            class="mt-1 text-[9px] uppercase tracking-[0.2em] text-[#191919]/30"
                        >
                            {{ review.order?.order_number || 'Order' }}
                        </p>
                    </div>

                    <div class="flex gap-1 text-[#E85D75]">
                        <span
                            v-for="star in 5"
                            :key="star"
                            class="text-sm"
                        >
                            {{ star <= ratingStars(review.rating) ? '★' : '☆' }}
                        </span>
                    </div>
                </div>

                <div class="my-6 h-px w-10 bg-[#E85D75]"></div>

                <p class="text-sm leading-7 text-[#191919]/55">
                    {{ review.comment || 'No comment provided.' }}
                </p>

                <div
                    class="mt-7 flex items-center justify-between border-t border-[#191919]/10 pt-5"
                >
                    <span
                        class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/25"
                    >
                        Review #{{ review.id }}
                    </span>

                    <span
                        class="text-[9px] uppercase tracking-[0.2em] text-[#E85D75]"
                    >
                        {{ review.rating }}/5
                    </span>
                </div>
            </article>
        </div>

        <!-- Empty -->
        <div
            v-else
            class="border border-[#191919]/10 bg-white p-10"
        >
            <div
                class="flex min-h-[250px] items-center justify-center text-center"
            >
                <div>
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-[#E85D75]/20 bg-[#F4A6B8]/10"
                    >
                        <span class="text-xl text-[#E85D75]">★</span>
                    </div>

                    <h3 class="mt-6 text-lg font-medium">
                        No reviews yet
                    </h3>

                    <p class="mt-2 text-sm text-[#191919]/35">
                        Customer reviews will appear here.
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
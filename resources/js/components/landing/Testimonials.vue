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
        const response = await api.get('/reviews/published')

        reviews.value = response.data.data || []
    } catch (error) {
        console.error('Failed to load reviews:', error)

        errorMessage.value =
            'Unable to load customer reviews right now.'
    } finally {
        loading.value = false
    }
}

const formatDate = (date) => {
    if (!date) return ''

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        year: 'numeric',
    }).format(new Date(date))
}

onMounted(() => {
    fetchReviews()
})
</script>

<template>
    <section
        class="border-t border-[#191919]/10 bg-[#FFF8FA]"
    >
        <div
            class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
        >

            <!-- Header -->
            <div
                class="grid gap-12 lg:grid-cols-[0.4fr_1fr]"
            >

                <!-- Label -->
                <div>
                    <span
                        class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                    >
                        05 — Kind words
                    </span>

                    <div
                        class="mt-8 hidden h-px w-20 bg-[#E85D75] lg:block"
                    ></div>

                    <p
                        class="mt-8 max-w-xs text-sm leading-7 text-[#191919]/40"
                    >
                        A few words from people who trusted
                        Teras Memori with their memories.
                    </p>
                </div>


                <!-- Heading -->
                <div>
                    <h2
                        class="max-w-5xl text-5xl font-medium leading-[0.9] tracking-[-0.06em] text-[#191919]/80 sm:text-6xl md:text-7xl lg:text-8xl"
                    >
                        Words from
                        <span class="text-[#191919]/15">
                            our clients.
                        </span>
                    </h2>
                </div>

            </div>


            <!-- Loading -->
            <div
                v-if="loading"
                class="mt-20 grid gap-6 md:grid-cols-2 lg:grid-cols-3"
            >

                <div
                    v-for="item in 3"
                    :key="item"
                    class="animate-pulse border border-[#191919]/10 bg-white p-8"
                >

                    <div class="h-4 w-24 bg-[#191919]/5"></div>

                    <div class="mt-8 h-20 bg-[#191919]/5"></div>

                    <div class="mt-10 h-4 w-32 bg-[#191919]/5"></div>

                </div>

            </div>


            <!-- Error -->
            <div
                v-else-if="errorMessage"
                class="mt-20 border-y border-[#191919]/10 py-16 text-center"
            >
                <p class="text-sm text-[#191919]/40">
                    {{ errorMessage }}
                </p>
            </div>


            <!-- Empty -->
            <div
                v-else-if="reviews.length === 0"
                class="mt-20 border-y border-[#191919]/10 py-16 text-center"
            >
                <p class="text-sm text-[#191919]/40">
                    No published reviews yet.
                </p>
            </div>


            <!-- Reviews -->
            <div
                v-else
                class="mt-20 grid gap-6 md:grid-cols-2 lg:grid-cols-3"
            >

                <article
                    v-for="review in reviews"
                    :key="review.id"
                    class="group flex min-h-[320px] flex-col justify-between border border-[#191919]/10 bg-white p-7 transition duration-500 hover:-translate-y-1 hover:border-[#E85D75]/30 hover:shadow-[0_20px_60px_rgba(25,25,25,0.06)] sm:p-8"
                >

                    <!-- Top -->
                    <div>

                        <!-- Rating -->
                        <div class="flex items-center gap-1">
                            <span
                                v-for="star in 5"
                                :key="star"
                                class="text-sm"
                                :class="
                                    star <= review.rating
                                        ? 'text-[#E85D75]'
                                        : 'text-[#191919]/10'
                                "
                            >
                                ★
                            </span>
                        </div>


                        <!-- Quote -->
                        <blockquote
                            v-if="review.comment"
                            class="mt-8 text-xl font-medium leading-[1.25] tracking-[-0.025em] text-[#191919]/70"
                        >
                            “{{ review.comment }}”
                        </blockquote>

                        <blockquote
                            v-else
                            class="mt-8 text-xl font-medium leading-[1.25] tracking-[-0.025em] text-[#191919]/30"
                        >
                            No written feedback.
                        </blockquote>

                    </div>


                    <!-- Bottom -->
                    <div
                        class="mt-12 flex items-end justify-between gap-5 border-t border-[#191919]/10 pt-6"
                    >

                        <div>

                            <p
                                class="text-sm font-medium text-[#191919]/70"
                            >
                                {{ review.customer?.name || 'Client' }}
                            </p>

                            <p
                                class="mt-1 text-[9px] uppercase tracking-[0.25em] text-[#191919]/25"
                            >
                                Teras Memori Client
                            </p>

                        </div>


                        <span
                            class="text-[9px] uppercase tracking-[0.2em] text-[#191919]/25"
                        >
                            {{ formatDate(review.created_at) }}
                        </span>

                    </div>

                </article>

            </div>

        </div>
    </section>
</template>

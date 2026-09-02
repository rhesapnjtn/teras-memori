```vue
<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../services/api'

const portfolios = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)

const error = ref('')
const successMessage = ref('')

const searchQuery = ref('')
const categoryFilter = ref('all')

const showForm = ref(false)
const showPreview = ref(false)

const editingPortfolio = ref(null)
const selectedPortfolio = ref(null)

const form = ref({
    title: '',
    slug: '',
    description: '',
    category: '',
    image: null,
    is_published: true,
})

const imagePreview = ref(null)

const fetchPortfolios = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await api.get('/admin/portfolios')

        portfolios.value =
            response.data.data ??
            response.data ??
            []
    } catch (err) {
        console.error('Fetch portfolios error:', err)

        error.value =
            err.response?.data?.message ||
            'Failed to load portfolios.'
    } finally {
        loading.value = false
    }
}

const categories = computed(() => {
    const values = portfolios.value
        .map((portfolio) => portfolio.category)
        .filter(Boolean)

    return [...new Set(values)]
})

const filteredPortfolios = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    return portfolios.value.filter((portfolio) => {
        const matchesCategory =
            categoryFilter.value === 'all' ||
            portfolio.category === categoryFilter.value

        if (!matchesCategory) {
            return false
        }

        if (!query) {
            return true
        }

        const title =
            portfolio.title?.toLowerCase() || ''

        const description =
            portfolio.description?.toLowerCase() || ''

        const category =
            portfolio.category?.toLowerCase() || ''

        return (
            title.includes(query) ||
            description.includes(query) ||
            category.includes(query)
        )
    })
})

const publishedCount = computed(() => {
    return portfolios.value.filter(
        (portfolio) => portfolio.is_published
    ).length
})

const draftCount = computed(() => {
    return portfolios.value.filter(
        (portfolio) => !portfolio.is_published
    ).length
})

const formatDate = (value) => {
    if (!value) return '-'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value))
}

const getImageUrl = (image) => {
    if (!image) {
        return null
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://') ||
        image.startsWith('/')
    ) {
        return image
    }

    return `/storage/${image}`
}

const generateSlug = (title) => {
    return title
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/--+/g, '-')
}

const handleTitleInput = () => {
    if (!editingPortfolio.value) {
        form.value.slug = generateSlug(form.value.title)
    }
}

const resetForm = () => {
    form.value = {
        title: '',
        slug: '',
        description: '',
        category: '',
        image: null,
        is_published: true,
    }

    imagePreview.value = null
    editingPortfolio.value = null
}

const openCreateForm = () => {
    resetForm()

    error.value = ''
    successMessage.value = ''

    showForm.value = true
}

const openEditForm = (portfolio) => {
    editingPortfolio.value = portfolio

    form.value = {
        title: portfolio.title || '',
        slug: portfolio.slug || '',
        description: portfolio.description || '',
        category: portfolio.category || '',
        image: null,
        is_published: Boolean(portfolio.is_published),
    }

    imagePreview.value =
        getImageUrl(portfolio.image)

    error.value = ''
    successMessage.value = ''

    showForm.value = true
}

const closeForm = () => {
    if (saving.value) return

    showForm.value = false
    resetForm()
}

const handleImageChange = (event) => {
    const file = event.target.files?.[0]

    if (!file) {
        form.value.image = null
        return
    }

    form.value.image = file

    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value)
    }

    imagePreview.value = URL.createObjectURL(file)
}

const removeSelectedImage = () => {
    form.value.image = null

    if (editingPortfolio.value) {
        imagePreview.value =
            getImageUrl(editingPortfolio.value.image)
    } else {
        imagePreview.value = null
    }
}

const savePortfolio = async () => {
    saving.value = true
    error.value = ''
    successMessage.value = ''

    try {
        const formData = new FormData()

        formData.append(
            'title',
            form.value.title
        )

        formData.append(
            'slug',
            form.value.slug
        )

        formData.append(
            'description',
            form.value.description || ''
        )

        formData.append(
            'category',
            form.value.category || ''
        )

        formData.append(
            'is_published',
            form.value.is_published ? '1' : '0'
        )

        if (form.value.image) {
            formData.append(
                'image',
                form.value.image
            )
        }

        if (editingPortfolio.value) {
            /*
             * Laravel + multipart/form-data PUT/PATCH
             * menggunakan method spoofing.
             */
            formData.append('_method', 'PUT')

            const response = await api.post(
                `/admin/portfolios/${editingPortfolio.value.id}`,
                formData,
                {
                    headers: {
                        'Content-Type':
                            'multipart/form-data',
                    },
                }
            )

            const updated =
                response.data.data ??
                response.data

            const index = portfolios.value.findIndex(
                (portfolio) =>
                    portfolio.id === updated.id
            )

            if (index !== -1) {
                portfolios.value[index] = updated
            }

            successMessage.value =
                'Portfolio updated successfully.'
        } else {
            const response = await api.post(
                '/admin/portfolios',
                formData,
                {
                    headers: {
                        'Content-Type':
                            'multipart/form-data',
                    },
                }
            )

            const created =
                response.data.data ??
                response.data

            portfolios.value.unshift(created)

            successMessage.value =
                'Portfolio created successfully.'
        }

        showForm.value = false
        resetForm()

    } catch (err) {
        console.error('Save portfolio error:', err)

        if (err.response?.status === 422) {
            const validationErrors =
                err.response.data.errors

            if (validationErrors) {
                error.value = Object.values(
                    validationErrors
                )
                    .flat()
                    .join(' ')
            } else {
                error.value =
                    err.response?.data?.message ||
                    'Please check the form.'
            }
        } else {
            error.value =
                err.response?.data?.message ||
                'Failed to save portfolio.'
        }
    } finally {
        saving.value = false
    }
}

const confirmDelete = async (portfolio) => {
    const confirmed = window.confirm(
        `Delete portfolio "${portfolio.title}"?`
    )

    if (!confirmed) {
        return
    }

    deleting.value = true
    error.value = ''
    successMessage.value = ''

    try {
        await api.delete(
            `/admin/portfolios/${portfolio.id}`
        )

        portfolios.value =
            portfolios.value.filter(
                (item) => item.id !== portfolio.id
            )

        successMessage.value =
            'Portfolio deleted successfully.'
    } catch (err) {
        console.error(
            'Delete portfolio error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Failed to delete portfolio.'
    } finally {
        deleting.value = false
    }
}

const openPreview = (portfolio) => {
    selectedPortfolio.value = portfolio
    showPreview.value = true

    document.body.style.overflow = 'hidden'
}

const closePreview = () => {
    showPreview.value = false
    selectedPortfolio.value = null

    document.body.style.overflow = ''
}

onMounted(() => {
    fetchPortfolios()
})
</script>

<template>
    <div class="min-h-full bg-[#FFF8FA] text-[#191919]">

        <!-- =====================================================
             HEADER
        ====================================================== -->
        <section
            class="border-b border-[#191919]/10 px-6 py-8 md:px-10 md:py-10"
        >
            <div
                class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
            >

                <div>
                    <p
                        class="mb-3 text-[10px] font-semibold uppercase tracking-[0.28em] text-[#E85D75]"
                    >
                        Management / Portfolio
                    </p>

                    <h1
                        class="text-4xl font-light tracking-[-0.04em] md:text-5xl"
                    >
                        Portfolio.
                    </h1>

                    <p
                        class="mt-3 max-w-xl text-sm leading-6 text-[#191919]/55"
                    >
                        Manage selected work, project images,
                        categories, descriptions, and publication
                        status.
                    </p>
                </div>


                <button
                    type="button"
                    @click="openCreateForm"
                    class="inline-flex h-11 items-center justify-center bg-[#191919] px-6 text-[10px] font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-[#E85D75]"
                >
                    + Add Portfolio
                </button>

            </div>
        </section>


        <!-- =====================================================
             SUMMARY
        ====================================================== -->
        <section
            class="border-b border-[#191919]/10 px-6 py-6 md:px-10"
        >

            <div
                class="grid gap-4 sm:grid-cols-3"
            >

                <div
                    class="border border-[#191919]/10 bg-white p-6"
                >
                    <p
                        class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                    >
                        Total Works
                    </p>

                    <p
                        class="mt-4 text-4xl font-light tracking-[-0.04em]"
                    >
                        {{ portfolios.length }}
                    </p>
                </div>


                <div
                    class="border border-[#191919]/10 bg-white p-6"
                >
                    <p
                        class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                    >
                        Published
                    </p>

                    <p
                        class="mt-4 text-4xl font-light tracking-[-0.04em] text-[#287A45]"
                    >
                        {{ publishedCount }}
                    </p>
                </div>


                <div
                    class="border border-[#191919]/10 bg-white p-6"
                >
                    <p
                        class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/40"
                    >
                        Draft
                    </p>

                    <p
                        class="mt-4 text-4xl font-light tracking-[-0.04em] text-[#B7465B]"
                    >
                        {{ draftCount }}
                    </p>
                </div>

            </div>

        </section>


        <!-- =====================================================
             FILTER
        ====================================================== -->
        <section
            class="border-b border-[#191919]/10 px-6 py-5 md:px-10"
        >

            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >

                <div class="relative w-full lg:max-w-md">

                    <span
                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#191919]/35"
                    >
                        ⌕
                    </span>

                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search portfolio..."
                        class="h-12 w-full border border-[#191919]/12 bg-white pl-11 pr-4 text-sm outline-none transition placeholder:text-[#191919]/30 focus:border-[#E85D75]"
                    />

                </div>


                <div
                    class="flex flex-wrap items-center gap-2"
                >

                    <button
                        type="button"
                        @click="categoryFilter = 'all'"
                        class="h-10 border px-4 text-[10px] font-semibold uppercase tracking-[0.16em] transition"
                        :class="
                            categoryFilter === 'all'
                                ? 'border-[#191919] bg-[#191919] text-white'
                                : 'border-[#191919]/12 bg-white hover:border-[#191919]/30'
                        "
                    >
                        All
                    </button>


                    <button
                        v-for="category in categories"
                        :key="category"
                        type="button"
                        @click="categoryFilter = category"
                        class="h-10 border px-4 text-[10px] font-semibold uppercase tracking-[0.16em] transition"
                        :class="
                            categoryFilter === category
                                ? 'border-[#191919] bg-[#191919] text-white'
                                : 'border-[#191919]/12 bg-white hover:border-[#191919]/30'
                        "
                    >
                        {{ category }}
                    </button>

                </div>

            </div>

        </section>


        <!-- =====================================================
             ALERTS
        ====================================================== -->
        <section
            v-if="error"
            class="px-6 pt-6 md:px-10"
        >
            <div
                class="border border-[#B33A4A]/20 bg-[#FBE9EC] p-5 text-sm text-[#B33A4A]"
            >
                {{ error }}
            </div>
        </section>


        <section
            v-if="successMessage"
            class="px-6 pt-6 md:px-10"
        >
            <div
                class="border border-[#287A45]/15 bg-[#E7F6EC] p-5 text-sm text-[#287A45]"
            >
                {{ successMessage }}
            </div>
        </section>


        <!-- =====================================================
             PORTFOLIO GRID
        ====================================================== -->
        <section
            class="px-6 py-8 md:px-10 md:py-10"
        >

            <!-- Loading -->
            <div
                v-if="loading"
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >

                <div
                    v-for="item in 6"
                    :key="item"
                    class="animate-pulse"
                >

                    <div
                        class="aspect-[4/5] bg-[#191919]/[0.05]"
                    ></div>

                    <div
                        class="mt-4 h-5 w-2/3 bg-[#191919]/[0.05]"
                    ></div>

                    <div
                        class="mt-3 h-3 w-1/3 bg-[#191919]/[0.04]"
                    ></div>

                </div>

            </div>


            <!-- Empty -->
            <div
                v-else-if="filteredPortfolios.length === 0"
                class="flex min-h-[350px] items-center justify-center border border-dashed border-[#191919]/15 bg-white/40"
            >

                <div class="text-center">

                    <div
                        class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-[#191919]/10 text-xl"
                    >
                        ◇
                    </div>

                    <h2 class="text-xl font-light">
                        No portfolio found.
                    </h2>

                    <p
                        class="mt-2 text-sm text-[#191919]/45"
                    >
                        Add your first selected work.
                    </p>

                    <button
                        type="button"
                        @click="openCreateForm"
                        class="mt-6 border border-[#191919] bg-[#191919] px-5 py-3 text-[9px] font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[#E85D75]"
                    >
                        Add Portfolio
                    </button>

                </div>

            </div>


            <!-- Grid -->
            <div
                v-else
                class="grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3"
            >

                <article
                    v-for="portfolio in filteredPortfolios"
                    :key="portfolio.id"
                    class="group"
                >

                    <!-- Image -->
                    <button
                        type="button"
                        @click="openPreview(portfolio)"
                        class="relative block aspect-[4/5] w-full overflow-hidden bg-[#F4F0F1] text-left"
                    >

                        <img
                            v-if="getImageUrl(portfolio.image)"
                            :src="getImageUrl(portfolio.image)"
                            :alt="portfolio.title"
                            class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                        />

                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center"
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


                        <!-- Status -->
                        <div
                            class="absolute left-4 top-4 rounded-full px-3 py-1.5 text-[9px] font-semibold uppercase tracking-[0.15em] backdrop-blur-sm"
                            :class="
                                portfolio.is_published
                                    ? 'bg-white/90 text-[#287A45]'
                                    : 'bg-[#191919]/75 text-white'
                            "
                        >
                            {{
                                portfolio.is_published
                                    ? 'Published'
                                    : 'Draft'
                            }}
                        </div>


                        <!-- View -->
                        <div
                            class="absolute bottom-4 right-4 flex h-12 w-12 translate-y-3 items-center justify-center rounded-full bg-white text-[#191919] opacity-0 shadow-lg transition duration-500 group-hover:translate-y-0 group-hover:opacity-100"
                        >
                            ↗
                        </div>

                    </button>


                    <!-- Info -->
                    <div class="mt-5">

                        <div
                            class="flex items-start justify-between gap-4"
                        >

                            <div class="min-w-0">

                                <h2
                                    class="truncate text-xl font-medium tracking-[-0.025em]"
                                >
                                    {{ portfolio.title }}
                                </h2>

                                <p
                                    class="mt-2 text-[9px] uppercase tracking-[0.22em] text-[#E85D75]"
                                >
                                    {{ portfolio.category || 'Uncategorized' }}
                                </p>

                            </div>


                            <span
                                class="shrink-0 text-[10px] text-[#191919]/30"
                            >
                                #{{ String(portfolio.id).padStart(3, '0') }}
                            </span>

                        </div>


                        <p
                            v-if="portfolio.description"
                            class="mt-3 line-clamp-2 text-sm leading-6 text-[#191919]/40"
                        >
                            {{ portfolio.description }}
                        </p>


                        <div
                            class="mt-5 flex items-center justify-between border-t border-[#191919]/8 pt-4"
                        >

                            <span
                                class="text-[9px] uppercase tracking-[0.15em] text-[#191919]/30"
                            >
                                {{ formatDate(portfolio.created_at) }}
                            </span>


                            <div
                                class="flex items-center gap-2"
                            >

                                <button
                                    type="button"
                                    @click="openEditForm(portfolio)"
                                    class="border border-[#191919]/10 px-3 py-2 text-[9px] font-semibold uppercase tracking-[0.14em] transition hover:border-[#191919] hover:bg-[#191919] hover:text-white"
                                >
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    @click="confirmDelete(portfolio)"
                                    :disabled="deleting"
                                    class="border border-[#B33A4A]/15 px-3 py-2 text-[9px] font-semibold uppercase tracking-[0.14em] text-[#B33A4A] transition hover:bg-[#B33A4A] hover:text-white disabled:opacity-50"
                                >
                                    Delete
                                </button>

                            </div>

                        </div>

                    </div>

                </article>

            </div>

        </section>


        <!-- =====================================================
             CREATE / EDIT MODAL
        ====================================================== -->
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >

            <div
                v-if="showForm"
                class="fixed inset-0 z-50 overflow-y-auto bg-[#191919]/45 p-4 backdrop-blur-sm md:p-8"
            >

                <div
                    class="mx-auto flex min-h-full max-w-5xl items-center justify-center"
                >

                    <div
                        class="w-full overflow-hidden bg-[#FFF8FA] shadow-2xl"
                    >

                        <!-- Header -->
                        <div
                            class="flex items-start justify-between border-b border-[#191919]/10 bg-white px-6 py-6 md:px-8"
                        >

                            <div>

                                <p
                                    class="mb-2 text-[9px] font-semibold uppercase tracking-[0.25em] text-[#E85D75]"
                                >
                                    Portfolio Management
                                </p>

                                <h2
                                    class="text-2xl font-light tracking-[-0.03em] md:text-3xl"
                                >
                                    {{
                                        editingPortfolio
                                            ? 'Edit Portfolio.'
                                            : 'New Portfolio.'
                                    }}
                                </h2>

                            </div>


                            <button
                                type="button"
                                @click="closeForm"
                                class="flex h-10 w-10 items-center justify-center border border-[#191919]/10 text-lg transition hover:border-[#191919] hover:bg-[#191919] hover:text-white"
                            >
                                ×
                            </button>

                        </div>


                        <!-- Form -->
                        <form
                            @submit.prevent="savePortfolio"
                            class="p-6 md:p-8"
                        >

                            <div
                                class="grid gap-8 lg:grid-cols-[1fr_360px]"
                            >

                                <!-- Fields -->
                                <div class="space-y-6">

                                    <div>
                                        <label
                                            class="mb-2 block text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                        >
                                            Title
                                        </label>

                                        <input
                                            v-model="form.title"
                                            @input="handleTitleInput"
                                            type="text"
                                            required
                                            placeholder="e.g. Wedding Memories"
                                            class="h-12 w-full border border-[#191919]/12 bg-white px-4 text-sm outline-none transition placeholder:text-[#191919]/25 focus:border-[#E85D75]"
                                        />
                                    </div>


                                    <div>
                                        <label
                                            class="mb-2 block text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                        >
                                            Slug
                                        </label>

                                        <input
                                            v-model="form.slug"
                                            type="text"
                                            required
                                            placeholder="wedding-memories"
                                            class="h-12 w-full border border-[#191919]/12 bg-white px-4 text-sm outline-none transition placeholder:text-[#191919]/25 focus:border-[#E85D75]"
                                        />

                                        <p
                                            class="mt-2 text-[10px] leading-5 text-[#191919]/30"
                                        >
                                            Automatically generated from the
                                            title when creating a new portfolio.
                                        </p>
                                    </div>


                                    <div>
                                        <label
                                            class="mb-2 block text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                        >
                                            Category
                                        </label>

                                        <input
                                            v-model="form.category"
                                            type="text"
                                            placeholder="e.g. Restoration"
                                            class="h-12 w-full border border-[#191919]/12 bg-white px-4 text-sm outline-none transition placeholder:text-[#191919]/25 focus:border-[#E85D75]"
                                        />
                                    </div>


                                    <div>
                                        <label
                                            class="mb-2 block text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                        >
                                            Description
                                        </label>

                                        <textarea
                                            v-model="form.description"
                                            rows="6"
                                            placeholder="Describe this project..."
                                            class="w-full resize-none border border-[#191919]/12 bg-white px-4 py-4 text-sm leading-6 outline-none transition placeholder:text-[#191919]/25 focus:border-[#E85D75]"
                                        ></textarea>
                                    </div>


                                    <label
                                        class="flex cursor-pointer items-center justify-between gap-5 border border-[#191919]/10 bg-white p-5"
                                    >

                                        <div>

                                            <p
                                                class="text-sm font-medium"
                                            >
                                                Publish portfolio
                                            </p>

                                            <p
                                                class="mt-1 text-xs leading-5 text-[#191919]/40"
                                            >
                                                Published works will appear
                                                on the public portfolio page.
                                            </p>

                                        </div>


                                        <input
                                            v-model="form.is_published"
                                            type="checkbox"
                                            class="h-5 w-5 accent-[#E85D75]"
                                        />

                                    </label>

                                </div>


                                <!-- Image -->
                                <div>

                                    <label
                                        class="mb-2 block text-[9px] font-semibold uppercase tracking-[0.2em] text-[#191919]/45"
                                    >
                                        Portfolio Image
                                    </label>


                                    <label
                                        class="group relative block aspect-[4/5] cursor-pointer overflow-hidden border border-dashed border-[#191919]/15 bg-[#F4F0F1]"
                                    >

                                        <img
                                            v-if="imagePreview"
                                            :src="imagePreview"
                                            alt="Portfolio preview"
                                            class="h-full w-full object-cover"
                                        />

                                        <div
                                            v-else
                                            class="flex h-full flex-col items-center justify-center px-6 text-center"
                                        >

                                            <div
                                                class="mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-[#191919]/10 text-xl"
                                            >
                                                +
                                            </div>

                                            <p
                                                class="text-sm font-medium"
                                            >
                                                Choose image
                                            </p>

                                            <p
                                                class="mt-2 text-xs leading-5 text-[#191919]/35"
                                            >
                                                JPG, JPEG, PNG or WEBP
                                            </p>

                                        </div>


                                        <div
                                            class="absolute inset-0 flex items-center justify-center bg-[#191919]/0 transition group-hover:bg-[#191919]/20"
                                        >
                                            <span
                                                class="translate-y-2 bg-white px-4 py-2 text-[9px] font-semibold uppercase tracking-[0.15em] opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100"
                                            >
                                                Change image
                                            </span>
                                        </div>


                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="handleImageChange"
                                        />

                                    </label>


                                    <button
                                        v-if="form.image"
                                        type="button"
                                        @click="removeSelectedImage"
                                        class="mt-3 text-[9px] font-semibold uppercase tracking-[0.15em] text-[#B33A4A] hover:text-[#E85D75]"
                                    >
                                        Remove selected image
                                    </button>

                                </div>

                            </div>


                            <!-- Footer -->
                            <div
                                class="mt-8 flex flex-col-reverse gap-3 border-t border-[#191919]/10 pt-6 sm:flex-row sm:justify-end"
                            >

                                <button
                                    type="button"
                                    @click="closeForm"
                                    :disabled="saving"
                                    class="h-11 border border-[#191919]/12 px-6 text-[10px] font-semibold uppercase tracking-[0.18em] transition hover:border-[#191919]/40 disabled:opacity-50"
                                >
                                    Cancel
                                </button>


                                <button
                                    type="submit"
                                    :disabled="saving"
                                    class="h-11 bg-[#191919] px-7 text-[10px] font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[#E85D75] disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        saving
                                            ? 'Saving...'
                                            : editingPortfolio
                                                ? 'Save Changes'
                                                : 'Create Portfolio'
                                    }}
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </Transition>


        <!-- =====================================================
             IMAGE PREVIEW
        ====================================================== -->
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >

            <div
                v-if="showPreview && selectedPortfolio"
                class="fixed inset-0 z-[70] flex flex-col bg-[#191919]/95 text-white"
                @click.self="closePreview"
            >

                <div
                    class="flex items-center justify-between border-b border-white/10 px-5 py-4 md:px-8"
                >

                    <div class="min-w-0">

                        <p
                            class="text-[9px] font-semibold uppercase tracking-[0.2em] text-white/40"
                        >
                            Portfolio Preview
                        </p>

                        <p
                            class="mt-1 truncate text-sm"
                        >
                            {{ selectedPortfolio.title }}
                        </p>

                    </div>


                    <button
                        type="button"
                        @click="closePreview"
                        class="flex h-10 w-10 items-center justify-center border border-white/15 text-xl transition hover:border-white/50"
                    >
                        ×
                    </button>

                </div>


                <div
                    class="flex min-h-0 flex-1 items-center justify-center p-5 md:p-10"
                >

                    <img
                        v-if="getImageUrl(selectedPortfolio.image)"
                        :src="getImageUrl(selectedPortfolio.image)"
                        :alt="selectedPortfolio.title"
                        class="max-h-full max-w-full object-contain"
                    />

                    <div
                        v-else
                        class="text-center"
                    >

                        <span
                            class="text-xs uppercase tracking-[0.3em] text-white/25"
                        >
                            No image
                        </span>

                    </div>

                </div>


                <div
                    class="border-t border-white/10 px-5 py-5 md:px-8"
                >

                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >

                        <div>

                            <p
                                class="text-[9px] uppercase tracking-[0.2em] text-white/35"
                            >
                                {{ selectedPortfolio.category || 'Uncategorized' }}
                            </p>

                            <p
                                v-if="selectedPortfolio.description"
                                class="mt-2 max-w-2xl text-sm leading-6 text-white/45"
                            >
                                {{ selectedPortfolio.description }}
                            </p>

                        </div>


                        <button
                            type="button"
                            @click="closePreview"
                            class="h-10 border border-white/15 px-5 text-[9px] font-semibold uppercase tracking-[0.16em] transition hover:border-white/40"
                        >
                            Close
                        </button>

                    </div>

                </div>

            </div>

        </Transition>

    </div>
</template>
```

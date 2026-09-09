```vue
<script setup>
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
} from 'vue'
import api from '../../services/api'

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const services = ref([])

const loading = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const searchQuery = ref('')
const statusFilter = ref('all')

const showModal = ref(false)
const editingService = ref(null)

const deletingId = ref(null)
const togglingId = ref(null)

const validationErrors = ref({})

const form = ref({
    name: '',
    description: '',
    price: '',
    duration: '',
    image: '',
    is_active: true,
})

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const formatPrice = (price) => {
    const value = Number(price)

    if (Number.isNaN(value)) {
        return 'Rp 0'
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value)
}

const normalizeNumber = (value) => {
    const number = Number(value)

    return Number.isFinite(number) ? number : 0
}

const getApiMessage = (error, fallback) => {
    return (
        error?.response?.data?.message ||
        error?.response?.data?.error ||
        fallback
    )
}

const getFieldError = (field) => {
    const errors = validationErrors.value

    if (!errors || !errors[field]) {
        return ''
    }

    if (Array.isArray(errors[field])) {
        return errors[field][0]
    }

    return errors[field]
}

const clearMessages = () => {
    errorMessage.value = ''
    successMessage.value = ''
}

const clearValidationErrors = () => {
    validationErrors.value = {}
}

/*
|--------------------------------------------------------------------------
| Image
|--------------------------------------------------------------------------
*/

const isValidImageUrl = computed(() => {
    const value = form.value.image?.trim()

    if (!value) {
        return true
    }

    try {
        new URL(value)
        return true
    } catch {
        return false
    }
})

const imagePreview = computed(() => {
    const image = form.value.image?.trim()

    return image || null
})

/*
|--------------------------------------------------------------------------
| Categories / Filters
|--------------------------------------------------------------------------
*/

const filteredServices = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    return services.value.filter((service) => {
        const matchesSearch =
            !query ||
            service.name?.toLowerCase().includes(query) ||
            service.description?.toLowerCase().includes(query) ||
            service.duration?.toLowerCase().includes(query)

        const matchesStatus =
            statusFilter.value === 'all' ||
            (statusFilter.value === 'active' && service.is_active) ||
            (statusFilter.value === 'inactive' && !service.is_active)

        return matchesSearch && matchesStatus
    })
})

const activeCount = computed(() => {
    return services.value.filter(
        (service) => Boolean(service.is_active)
    ).length
})

const inactiveCount = computed(() => {
    return services.value.filter(
        (service) => !service.is_active
    ).length
})

/*
|--------------------------------------------------------------------------
| Fetch Services
|--------------------------------------------------------------------------
*/

const fetchServices = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/admin/services')

        services.value = response.data.data || []
    } catch (error) {
        console.error('Failed to load services:', error)

        errorMessage.value = getApiMessage(
            error,
            'Unable to load services right now. Please try again.'
        )
    } finally {
        loading.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Modal
|--------------------------------------------------------------------------
*/

const resetForm = () => {
    form.value = {
        name: '',
        description: '',
        price: '',
        duration: '',
        image: '',
        is_active: true,
    }

    editingService.value = null
    clearValidationErrors()
}

const openCreateModal = async () => {
    clearMessages()
    resetForm()

    showModal.value = true

    await nextTick()
    document.body.style.overflow = 'hidden'
}

const openEditModal = async (service) => {
    clearMessages()
    clearValidationErrors()

    editingService.value = service

    form.value = {
        name: service.name || '',
        description: service.description || '',
        price: service.price ?? '',
        duration: service.duration || '',
        image: service.image || '',
        is_active: Boolean(service.is_active),
    }

    showModal.value = true

    await nextTick()
    document.body.style.overflow = 'hidden'
}

const closeModal = () => {
    if (saving.value) {
        return
    }

    showModal.value = false
    editingService.value = null
    clearValidationErrors()

    document.body.style.overflow = ''
}

/*
|--------------------------------------------------------------------------
| Form Validation
|--------------------------------------------------------------------------
*/

const validateForm = () => {
    clearValidationErrors()

    const errors = {}

    if (!form.value.name.trim()) {
        errors.name = ['Service name is required.']
    }

    if (!form.value.description.trim()) {
        errors.description = ['Description is required.']
    }

    if (
        form.value.price === '' ||
        form.value.price === null ||
        normalizeNumber(form.value.price) < 0
    ) {
        errors.price = ['Price must be a valid number.']
    }

    if (!form.value.duration.trim()) {
        errors.duration = ['Duration is required.']
    }

    if (!isValidImageUrl.value) {
        errors.image = ['Image must be a valid URL.']
    }

    validationErrors.value = errors

    return Object.keys(errors).length === 0
}

/*
|--------------------------------------------------------------------------
| Save Service
|--------------------------------------------------------------------------
*/

const saveService = async () => {
    clearMessages()

    if (!validateForm()) {
        return
    }

    saving.value = true

    try {
        const payload = {
            name: form.value.name.trim(),
            description: form.value.description.trim(),
            price: normalizeNumber(form.value.price),
            duration: form.value.duration.trim(),
            image: form.value.image?.trim() || null,
            is_active: Boolean(form.value.is_active),
        }

        let response

        if (editingService.value) {
            response = await api.put(
                `/admin/services/${editingService.value.id}`,
                payload
            )
        } else {
            response = await api.post(
                '/admin/services',
                payload
            )
        }

        const savedService = response.data.data

        if (editingService.value) {
            const index = services.value.findIndex(
                (service) =>
                    service.id === editingService.value.id
            )

            if (index !== -1 && savedService) {
                services.value[index] = savedService
            }

            successMessage.value =
                'Service berhasil diperbarui.'
        } else {
            if (savedService) {
                services.value.unshift(savedService)
            } else {
                await fetchServices()
            }

            successMessage.value =
                'Service berhasil ditambahkan.'
        }

        closeModal()
    } catch (error) {
        console.error('Failed to save service:', error)

        if (error?.response?.status === 422) {
            validationErrors.value =
                error.response.data.errors || {}

            errorMessage.value =
                error.response.data.message ||
                'Please check the form and try again.'
        } else {
            errorMessage.value = getApiMessage(
                error,
                'Unable to save service right now. Please try again.'
            )
        }
    } finally {
        saving.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Delete Service
|--------------------------------------------------------------------------
*/

const deleteService = async (service) => {
    clearMessages()

    const confirmed = window.confirm(
        `Are you sure you want to delete "${service.name}"?`
    )

    if (!confirmed) {
        return
    }

    deletingId.value = service.id

    try {
        await api.delete(
            `/admin/services/${service.id}`
        )

        services.value = services.value.filter(
            (item) => item.id !== service.id
        )

        successMessage.value =
            'Service berhasil dihapus.'
    } catch (error) {
        console.error('Failed to delete service:', error)

        errorMessage.value = getApiMessage(
            error,
            'Unable to delete service right now. Please try again.'
        )
    } finally {
        deletingId.value = null
    }
}

/*
|--------------------------------------------------------------------------
| Toggle Active Status
|--------------------------------------------------------------------------
*/

const toggleStatus = async (service) => {
    clearMessages()

    togglingId.value = service.id

    try {
        const payload = {
            name: service.name,
            description: service.description,
            price: normalizeNumber(service.price),
            duration: service.duration,
            image: service.image || null,
            is_active: !Boolean(service.is_active),
        }

        const response = await api.put(
            `/admin/services/${service.id}`,
            payload
        )

        const updatedService = response.data.data

        const index = services.value.findIndex(
            (item) => item.id === service.id
        )

        if (index !== -1) {
            services.value[index] =
                updatedService || {
                    ...service,
                    is_active: !service.is_active,
                }
        }

        successMessage.value = service.is_active
            ? 'Service berhasil dinonaktifkan.'
            : 'Service berhasil diaktifkan.'
    } catch (error) {
        console.error(
            'Failed to toggle service status:',
            error
        )

        errorMessage.value = getApiMessage(
            error,
            'Unable to update service status right now.'
        )
    } finally {
        togglingId.value = null
    }
}

/*
|--------------------------------------------------------------------------
| Escape Key
|--------------------------------------------------------------------------
*/

const handleKeydown = (event) => {
    if (
        event.key === 'Escape' &&
        showModal.value &&
        !saving.value
    ) {
        closeModal()
    }
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    fetchServices()

    window.addEventListener(
        'keydown',
        handleKeydown
    )
})

onUnmounted(() => {
    window.removeEventListener(
        'keydown',
        handleKeydown
    )

    document.body.style.overflow = ''
})
</script>

<template>
    <div class="min-h-screen bg-[#FFF8FA] text-[#191919]">

        <!-- =====================================================
             HEADER
        ====================================================== -->
        <section class="border-b border-[#191919]/10">

            <div
                class="mx-auto max-w-[1600px] px-6 py-10 sm:px-10 lg:px-16"
            >

                <div
                    class="flex flex-col justify-between gap-8 md:flex-row md:items-end"
                >

                    <div>

                        <div
                            class="mb-5 flex items-center gap-3"
                        >
                            <span
                                class="h-px w-10 bg-[#E85D75]"
                            ></span>

                            <span
                                class="text-[9px] uppercase tracking-[0.35em] text-[#191919]/35"
                            >
                                Studio Management
                            </span>
                        </div>

                        <h1
                            class="text-5xl font-medium tracking-[-0.06em] sm:text-6xl md:text-7xl"
                        >
                            Services
                        </h1>

                        <p
                            class="mt-4 max-w-xl text-sm leading-6 text-[#191919]/40"
                        >
                            Kelola layanan editing yang tersedia
                            untuk customer Teras Memori.
                        </p>

                    </div>


                    <!-- Add Button -->
                    <button
                        type="button"
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-4 rounded-full bg-[#191919] px-7 py-4 text-[10px] font-semibold uppercase tracking-[0.2em] text-white transition duration-300 hover:bg-[#E85D75]"
                    >
                        <span class="text-lg leading-none">
                            +
                        </span>

                        Add Service
                    </button>

                </div>

            </div>

        </section>


        <!-- =====================================================
             ALERTS
        ====================================================== -->
        <div
            v-if="errorMessage || successMessage"
            class="mx-auto max-w-[1600px] px-6 pt-6 sm:px-10 lg:px-16"
        >

            <!-- Error -->
            <div
                v-if="errorMessage"
                class="flex items-start justify-between gap-5 border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700"
            >

                <p>
                    {{ errorMessage }}
                </p>

                <button
                    type="button"
                    class="text-lg leading-none text-red-400 hover:text-red-700"
                    @click="errorMessage = ''"
                    aria-label="Close error"
                >
                    ×
                </button>

            </div>


            <!-- Success -->
            <div
                v-if="successMessage"
                class="mt-3 flex items-start justify-between gap-5 border border-[#E85D75]/20 bg-[#E85D75]/5 px-5 py-4 text-sm text-[#191919]/70"
            >

                <p>
                    {{ successMessage }}
                </p>

                <button
                    type="button"
                    class="text-lg leading-none text-[#191919]/30 hover:text-[#191919]"
                    @click="successMessage = ''"
                    aria-label="Close success"
                >
                    ×
                </button>

            </div>

        </div>


        <!-- =====================================================
             STATS
        ====================================================== -->
        <section>

            <div
                class="mx-auto grid max-w-[1600px] gap-px border-b border-[#191919]/10 bg-[#191919]/10 sm:grid-cols-3"
            >

                <!-- Total -->
                <div
                    class="bg-[#FFF8FA] px-6 py-8 sm:px-10 lg:px-16"
                >

                    <span
                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Total services
                    </span>

                    <div
                        class="mt-3 text-4xl font-medium tracking-[-0.05em]"
                    >
                        {{ services.length }}
                    </div>

                </div>


                <!-- Active -->
                <div
                    class="bg-[#FFF8FA] px-6 py-8 sm:px-10 lg:px-16"
                >

                    <span
                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Active
                    </span>

                    <div
                        class="mt-3 text-4xl font-medium tracking-[-0.05em] text-[#E85D75]"
                    >
                        {{ activeCount }}
                    </div>

                </div>


                <!-- Inactive -->
                <div
                    class="bg-[#FFF8FA] px-6 py-8 sm:px-10 lg:px-16"
                >

                    <span
                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Inactive
                    </span>

                    <div
                        class="mt-3 text-4xl font-medium tracking-[-0.05em] text-[#191919]/35"
                    >
                        {{ inactiveCount }}
                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FILTER
        ====================================================== -->
        <section class="border-b border-[#191919]/10">

            <div
                class="mx-auto flex max-w-[1600px] flex-col gap-5 px-6 py-6 sm:px-10 md:flex-row md:items-center md:justify-between lg:px-16"
            >

                <!-- Search -->
                <div class="relative w-full md:max-w-md">

                    <span
                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-[#191919]/30"
                    >
                        ⌕
                    </span>

                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search services..."
                        class="w-full rounded-full border border-[#191919]/10 bg-white px-11 py-3 text-sm outline-none transition placeholder:text-[#191919]/25 focus:border-[#E85D75]/50"
                    />

                </div>


                <!-- Status Filter -->
                <div
                    class="flex flex-wrap gap-2"
                >

                    <button
                        v-for="filter in [
                            { value: 'all', label: 'All' },
                            { value: 'active', label: 'Active' },
                            { value: 'inactive', label: 'Inactive' },
                        ]"
                        :key="filter.value"
                        type="button"
                        @click="statusFilter = filter.value"
                        class="rounded-full border px-5 py-2.5 text-[9px] uppercase tracking-[0.25em] transition duration-300"
                        :class="
                            statusFilter === filter.value
                                ? 'border-[#191919] bg-[#191919] text-white'
                                : 'border-[#191919]/10 text-[#191919]/40 hover:border-[#E85D75]/50 hover:text-[#E85D75]'
                        "
                    >
                        {{ filter.label }}
                    </button>

                </div>

            </div>

        </section>


        <!-- =====================================================
             SERVICES TABLE
        ====================================================== -->
        <section>

            <div
                class="mx-auto max-w-[1600px] px-6 py-10 sm:px-10 lg:px-16"
            >

                <!-- Loading -->
                <div
                    v-if="loading"
                    class="overflow-hidden border border-[#191919]/10 bg-white"
                >

                    <div
                        v-for="item in 5"
                        :key="item"
                        class="animate-pulse border-b border-[#191919]/5 p-6 last:border-b-0"
                    >

                        <div
                            class="flex items-center gap-5"
                        >

                            <div
                                class="h-12 w-12 bg-[#191919]/5"
                            ></div>

                            <div class="flex-1">

                                <div
                                    class="h-4 w-48 bg-[#191919]/5"
                                ></div>

                                <div
                                    class="mt-3 h-3 w-72 max-w-full bg-[#191919]/5"
                                ></div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Error / Retry -->
                <div
                    v-else-if="errorMessage && services.length === 0"
                    class="border-y border-[#191919]/10 py-20 text-center"
                >

                    <p
                        class="text-sm text-[#191919]/45"
                    >
                        Unable to load services.
                    </p>

                    <button
                        type="button"
                        @click="fetchServices"
                        class="mt-6 rounded-full border border-[#191919]/15 px-6 py-3 text-[10px] uppercase tracking-[0.2em] transition hover:border-[#191919]/40 hover:bg-[#191919]/5"
                    >
                        Try again
                    </button>

                </div>


                <!-- Empty -->
                <div
                    v-else-if="filteredServices.length === 0"
                    class="border-y border-[#191919]/10 py-20 text-center"
                >

                    <p
                        class="text-sm text-[#191919]/40"
                    >
                        No services found.
                    </p>

                    <button
                        v-if="searchQuery || statusFilter !== 'all'"
                        type="button"
                        @click="searchQuery = ''; statusFilter = 'all'"
                        class="mt-5 text-[9px] uppercase tracking-[0.25em] text-[#E85D75] hover:underline">
                        Clear filters
                    </button>

                </div>


                <!-- Table -->
                <div
                    v-else
                    class="overflow-x-auto border border-[#191919]/10 bg-white"
                >

                    <table
                        class="w-full min-w-[900px] border-collapse"
                    >

                        <thead>

                            <tr
                                class="border-b border-[#191919]/10 bg-[#191919]/[0.025]"
                            >

                                <th
                                    class="w-20 px-6 py-5 text-left text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/30"
                                >
                                    #
                                </th>

                                <th
                                    class="px-6 py-5 text-left text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/30"
                                >
                                    Service
                                </th>

                                <th
                                    class="px-6 py-5 text-left text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/30"
                                >
                                    Price
                                </th>

                                <th
                                    class="px-6 py-5 text-left text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/30"
                                >
                                    Duration
                                </th>

                                <th
                                    class="px-6 py-5 text-left text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/30"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-5 text-right text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/30"
                                >
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr
                                v-for="(service, index) in filteredServices"
                                :key="service.id"
                                class="group border-b border-[#191919]/5 transition duration-300 hover:bg-[#FFF8FA] last:border-b-0"
                            >

                                <!-- Number -->
                                <td
                                    class="px-6 py-6 align-top"
                                >

                                    <span
                                        class="text-[10px] tracking-[0.2em] text-[#191919]/25"
                                    >
                                        {{ String(index + 1).padStart(2, '0') }}
                                    </span>

                                </td>


                                <!-- Service -->
                                <td
                                    class="px-6 py-6"
                                >

                                    <div
                                        class="flex items-center gap-5"
                                    >

                                        <!-- Image -->
                                        <div
                                            class="relative h-16 w-16 shrink-0 overflow-hidden bg-[#F4F0F1]"
                                        >

                                            <img
                                                v-if="service.image"
                                                :src="service.image"
                                                :alt="service.name"
                                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                loading="lazy"
                                            />

                                            <div
                                                v-else
                                                class="flex h-full w-full items-center justify-center"
                                            >

                                                <span
                                                    class="text-[8px] uppercase tracking-[0.2em] text-[#191919]/20"
                                                >
                                                    TM
                                                </span>

                                            </div>

                                        </div>


                                        <!-- Text -->
                                        <div class="min-w-0">

                                            <h2
                                                class="text-base font-medium tracking-[-0.02em] text-[#191919]/80"
                                            >
                                                {{ service.name }}
                                            </h2>

                                            <p
                                                v-if="service.description"
                                                class="mt-1 max-w-xl truncate text-xs text-[#191919]/35"
                                            >
                                                {{ service.description }}
                                            </p>

                                        </div>

                                    </div>

                                </td>


                                <!-- Price -->
                                <td
                                    class="whitespace-nowrap px-6 py-6 align-middle"
                                >

                                    <span
                                        class="text-sm font-medium text-[#191919]/70"
                                    >
                                        {{ formatPrice(service.price) }}
                                    </span>

                                </td>


                                <!-- Duration -->
                                <td
                                    class="px-6 py-6 align-middle"
                                >

                                    <span
                                        class="text-xs text-[#191919]/45"
                                    >
                                        {{ service.duration || '—' }}
                                    </span>

                                </td>


                                <!-- Status -->
                                <td
                                    class="px-6 py-6 align-middle"
                                >

                                    <button
                                        type="button"
                                        @click="toggleStatus(service)"
                                        :disabled="
                                            togglingId === service.id
                                        "
                                        class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-[9px] uppercase tracking-[0.18em] transition duration-300 disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="
                                            service.is_active
                                                ? 'border-[#E85D75]/20 bg-[#E85D75]/5 text-[#E85D75] hover:border-[#E85D75]/40'
                                                : 'border-[#191919]/10 bg-[#191919]/[0.03] text-[#191919]/35 hover:border-[#191919]/25'
                                        "
                                    >

                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="
                                                service.is_active
                                                    ? 'bg-[#E85D75]'
                                                    : 'bg-[#191919]/20'
                                            "
                                        ></span>

                                        {{
                                            togglingId === service.id
                                                ? 'Updating...'
                                                : service.is_active
                                                    ? 'Active'
                                                    : 'Inactive'
                                        }}

                                    </button>

                                </td>


                                <!-- Action -->
                                <td
                                    class="px-6 py-6 text-right align-middle"
                                >

                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >

                                        <!-- Edit -->
                                        <button
                                            type="button"
                                            @click="
                                                openEditModal(service)
                                            "
                                            class="rounded-full border border-[#191919]/10 px-4 py-2.5 text-[9px] uppercase tracking-[0.18em] text-[#191919]/45 transition duration-300 hover:border-[#191919]/25 hover:bg-[#191919]/5 hover:text-[#191919]"
                                        >
                                            Edit
                                        </button>


                                        <!-- Delete -->
                                        <button
                                            type="button"
                                            @click="
                                                deleteService(service)
                                            "
                                            :disabled="
                                                deletingId === service.id
                                            "
                                            class="rounded-full border border-red-200 px-4 py-2.5 text-[9px] uppercase tracking-[0.18em] text-red-500 transition duration-300 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            {{
                                                deletingId === service.id
                                                    ? 'Deleting...'
                                                    : 'Delete'
                                            }}
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- Count -->
                <div
                    v-if="!loading && filteredServices.length > 0"
                    class="mt-5 flex justify-between text-[9px] uppercase tracking-[0.25em] text-[#191919]/25"
                >

                    <span>
                        Showing {{ filteredServices.length }}
                        of {{ services.length }} services
                    </span>

                    <span>
                        Teras Memori Studio
                    </span>

                </div>

            </div>

        </section>


        <!-- =====================================================
             MODAL
        ====================================================== -->
        <Transition name="modal">

            <div
                v-if="showModal"
                class="fixed inset-0 z-[100] overflow-y-auto bg-[#191919]/70 px-4 py-6 backdrop-blur-md sm:px-8 sm:py-10"
                @click.self="closeModal"
            >

                <div
                    class="mx-auto flex min-h-full max-w-3xl items-center justify-center"
                >

                    <div
                        class="relative w-full overflow-hidden rounded-[1.5rem] bg-[#FFF8FA] shadow-2xl"
                    >

                        <!-- Modal Header -->
                        <div
                            class="flex items-start justify-between gap-6 border-b border-[#191919]/10 px-6 py-6 sm:px-8"
                        >

                            <div>

                                <span
                                    class="text-[9px] uppercase tracking-[0.35em] text-[#191919]/30"
                                >
                                    {{
                                        editingService
                                            ? 'Edit service'
                                            : 'New service'
                                    }}
                                </span>

                                <h2
                                    class="mt-2 text-2xl font-medium tracking-[-0.04em]"
                                >
                                    {{
                                        editingService
                                            ? 'Update Service'
                                            : 'Create Service'
                                    }}
                                </h2>

                            </div>


                            <!-- Close -->
                            <button
                                type="button"
                                @click="closeModal"
                                :disabled="saving"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[#191919]/10 text-xl text-[#191919]/40 transition hover:border-[#191919]/25 hover:text-[#191919] disabled:cursor-not-allowed disabled:opacity-40"
                                aria-label="Close"
                            >
                                ×
                            </button>

                        </div>


                        <!-- Form -->
                        <form
                            @submit.prevent="saveService"
                            class="px-6 py-7 sm:px-8"
                        >

                            <div class="grid gap-6">

                                <!-- Name -->
                                <div>

                                    <label
                                        class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/40"
                                    >
                                        Service name
                                    </label>

                                    <input
                                        v-model="form.name"
                                        type="text"
                                        maxlength="255"
                                        placeholder="e.g. Photo Editing"
                                        class="mt-2 w-full border-0 border-b bg-transparent px-0 py-3 text-base outline-none transition placeholder:text-[#191919]/20"
                                        :class="
                                            getFieldError('name')
                                                ? 'border-red-400'
                                                : 'border-[#191919]/10 focus:border-[#E85D75]'
                                        "
                                    />

                                    <p
                                        v-if="getFieldError('name')"
                                        class="mt-2 text-xs text-red-500"
                                    >
                                        {{ getFieldError('name') }}
                                    </p>

                                </div>


                                <!-- Description -->
                                <div>

                                    <label
                                        class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/40"
                                    >
                                        Description
                                    </label>

                                    <textarea
                                        v-model="form.description"
                                        rows="4"
                                        maxlength="2000"
                                        placeholder="Describe this service..."
                                        class="mt-2 w-full resize-none border border-[#191919]/10 bg-white px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-[#191919]/20 focus:border-[#E85D75]"
                                        :class="
                                            getFieldError('description')
                                                ? 'border-red-400'
                                                : ''
                                        "
                                    ></textarea>

                                    <p
                                        v-if="
                                            getFieldError(
                                                'description'
                                            )
                                        "
                                        class="mt-2 text-xs text-red-500"
                                    >
                                        {{
                                            getFieldError(
                                                'description'
                                            )
                                        }}
                                    </p>

                                </div>


                                <!-- Price + Duration -->
                                <div
                                    class="grid gap-6 sm:grid-cols-2"
                                >

                                    <!-- Price -->
                                    <div>

                                        <label
                                            class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/40"
                                        >
                                            Price
                                        </label>

                                        <div
                                            class="mt-2 flex items-center border-b border-[#191919]/10"
                                            :class="
                                                getFieldError('price')
                                                    ? 'border-red-400'
                                                    : 'focus-within:border-[#E85D75]'
                                            "
                                        >

                                            <span
                                                class="text-sm text-[#191919]/35"
                                            >
                                                Rp
                                            </span>

                                            <input
                                                v-model="form.price"
                                                type="number"
                                                min="0"
                                                step="1"
                                                placeholder="50000"
                                                class="w-full bg-transparent px-2 py-3 text-base outline-none placeholder:text-[#191919]/20"
                                            />

                                        </div>

                                        <p
                                            v-if="getFieldError('price')"
                                            class="mt-2 text-xs text-red-500"
                                        >
                                            {{ getFieldError('price') }}
                                        </p>

                                    </div>


                                    <!-- Duration -->
                                    <div>

                                        <label
                                            class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/40"
                                        >
                                            Duration
                                        </label>

                                        <input
                                            v-model="form.duration"
                                            type="text"
                                            maxlength="100"
                                            placeholder="1-2 Days"
                                            class="mt-2 w-full border-0 border-b bg-transparent px-0 py-3 text-base outline-none transition placeholder:text-[#191919]/20"
                                            :class="
                                                getFieldError('duration')
                                                    ? 'border-red-400'
                                                    : 'border-[#191919]/10 focus:border-[#E85D75]'
                                            "
                                        />

                                        <p
                                            v-if="
                                                getFieldError(
                                                    'duration'
                                                )
                                            "
                                            class="mt-2 text-xs text-red-500"
                                        >
                                            {{
                                                getFieldError(
                                                    'duration'
                                                )
                                            }}
                                        </p>

                                    </div>

                                </div>


                                <!-- Image URL -->
                                <div>

                                    <label
                                        class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/40"
                                    >
                                        Image URL
                                    </label>

                                    <input
                                        v-model="form.image"
                                        type="url"
                                        placeholder="https://..."
                                        class="mt-2 w-full border-0 border-b bg-transparent px-0 py-3 text-sm outline-none transition placeholder:text-[#191919]/20"
                                        :class="
                                            getFieldError('image')
                                                ? 'border-red-400'
                                                : 'border-[#191919]/10 focus:border-[#E85D75]'
                                        "
                                    />

                                    <p
                                        v-if="getFieldError('image')"
                                        class="mt-2 text-xs text-red-500"
                                    >
                                        {{ getFieldError('image') }}
                                    </p>


                                    <!-- Preview -->
                                    <div
                                        v-if="imagePreview"
                                        class="mt-4 overflow-hidden border border-[#191919]/10 bg-[#F4F0F1]"
                                    >

                                        <img
                                            :src="imagePreview"
                                            alt="Image preview"
                                            class="max-h-64 w-full object-contain"
                                        />

                                    </div>

                                </div>


                                <!-- Status -->
                                <div
                                    class="flex items-center justify-between border-y border-[#191919]/10 py-5"
                                >

                                    <div>

                                        <p
                                            class="text-[9px] uppercase tracking-[0.25em] text-[#191919]/40"
                                        >
                                            Service status
                                        </p>

                                        <p
                                            class="mt-1 text-sm text-[#191919]/45"
                                        >
                                            Inactive services won't be
                                            available for new orders.
                                        </p>

                                    </div>


                                    <button
                                        type="button"
                                        @click="
                                            form.is_active =
                                                !form.is_active
                                        "
                                        class="relative h-7 w-12 shrink-0 rounded-full transition duration-300"
                                        :class="
                                            form.is_active
                                                ? 'bg-[#E85D75]'
                                                : 'bg-[#191919]/15'
                                        "
                                        :aria-pressed="
                                            form.is_active
                                        "
                                    >

                                        <span
                                            class="absolute top-1 h-5 w-5 rounded-full bg-white shadow transition duration-300"
                                            :class="
                                                form.is_active
                                                    ? 'left-6'
                                                    : 'left-1'
                                            "
                                        ></span>

                                    </button>

                                </div>

                            </div>


                            <!-- Footer -->
                            <div
                                class="mt-8 flex flex-col-reverse gap-3 border-t border-[#191919]/10 pt-6 sm:flex-row sm:justify-end"
                            >

                                <button
                                    type="button"
                                    @click="closeModal"
                                    :disabled="saving"
                                    class="rounded-full border border-[#191919]/10 px-6 py-3 text-[9px] uppercase tracking-[0.2em] text-[#191919]/45 transition hover:border-[#191919]/25 hover:text-[#191919] disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    :disabled="saving"
                                    class="rounded-full bg-[#191919] px-7 py-3 text-[9px] font-semibold uppercase tracking-[0.2em] text-white transition duration-300 hover:bg-[#E85D75] disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        saving
                                            ? 'Saving...'
                                            : editingService
                                                ? 'Update Service'
                                                : 'Create Service'
                                    }}
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </Transition>


        <!-- =====================================================
             FOOTER
        ====================================================== -->
        <footer
            class="border-t border-[#191919]/10"
        >

            <div
                class="mx-auto flex max-w-[1600px] flex-col justify-between gap-3 px-6 py-8 text-[9px] uppercase tracking-[0.25em] text-[#191919]/25 sm:flex-row sm:px-10 lg:px-16"
            >

                <span>
                    Teras Memori
                </span>

                <span>
                    Service Management
                </span>

                <span>
                    © {{ new Date().getFullYear() }}
                </span>

            </div>

        </footer>

    </div>
</template>


<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
```

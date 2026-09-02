```vue
<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../services/api'

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const services = ref([])
const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)

const errorMessage = ref('')
const successMessage = ref('')

const searchQuery = ref('')
const statusFilter = ref('all')

const showModal = ref(false)
const editingService = ref(null)

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
    return new Intl.NumberFormat('id-ID').format(Number(price || 0))
}

const resetMessages = () => {
    errorMessage.value = ''
    successMessage.value = ''
}

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
}


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const filteredServices = computed(() => {
    let result = [...services.value]

    // Search
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase()

        result = result.filter((service) => {
            return (
                service.name?.toLowerCase().includes(query) ||
                service.description?.toLowerCase().includes(query)
            )
        })
    }

    // Status filter
    if (statusFilter.value === 'active') {
        result = result.filter((service) => service.is_active)
    }

    if (statusFilter.value === 'inactive') {
        result = result.filter((service) => !service.is_active)
    }

    return result
})

const activeCount = computed(() => {
    return services.value.filter((service) => service.is_active).length
})

const inactiveCount = computed(() => {
    return services.value.filter((service) => !service.is_active).length
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
        const response = await api.get('/services')

        services.value = response.data?.data || []
    } catch (error) {
        console.error('Failed to fetch services:', error)

        errorMessage.value =
            error.response?.data?.message ||
            'Gagal mengambil data services.'
    } finally {
        loading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Open Add Modal
|--------------------------------------------------------------------------
*/

const openCreateModal = () => {
    resetMessages()
    resetForm()

    showModal.value = true
}


/*
|--------------------------------------------------------------------------
| Open Edit Modal
|--------------------------------------------------------------------------
*/

const openEditModal = (service) => {
    resetMessages()

    editingService.value = service

    form.value = {
        name: service.name || '',
        description: service.description || '',
        price: service.price || '',
        duration: service.duration || '',
        image: service.image || '',
        is_active: Boolean(service.is_active),
    }

    showModal.value = true
}


/*
|--------------------------------------------------------------------------
| Close Modal
|--------------------------------------------------------------------------
*/

const closeModal = () => {
    if (saving.value) return

    showModal.value = false

    setTimeout(() => {
        resetForm()
    }, 200)
}


/*
|--------------------------------------------------------------------------
| Save Service
|--------------------------------------------------------------------------
*/

const saveService = async () => {
    resetMessages()

    if (!form.value.name.trim()) {
        errorMessage.value = 'Nama service wajib diisi.'
        return
    }

    if (!form.value.price) {
        errorMessage.value = 'Harga service wajib diisi.'
        return
    }

    saving.value = true

    try {
        const payload = {
            name: form.value.name.trim(),
            description: form.value.description?.trim() || '',
            price: Number(form.value.price),
            duration: form.value.duration?.trim() || '',
            image: form.value.image?.trim() || null,
            is_active: Boolean(form.value.is_active),
        }

        if (editingService.value) {
            const response = await api.put(
                `/services/${editingService.value.id}`,
                payload
            )

            const updatedService =
                response.data?.data || response.data?.service

            if (updatedService) {
                const index = services.value.findIndex(
                    (service) => service.id === updatedService.id
                )

                if (index !== -1) {
                    services.value[index] = updatedService
                }
            }

            successMessage.value =
                response.data?.message ||
                'Service berhasil diperbarui.'
        } else {
            const response = await api.post('/services', payload)

            const newService =
                response.data?.data || response.data?.service

            if (newService) {
                services.value.unshift(newService)
            } else {
                await fetchServices()
            }

            successMessage.value =
                response.data?.message ||
                'Service berhasil ditambahkan.'
        }

        showModal.value = false
        resetForm()

    } catch (error) {
        console.error('Failed to save service:', error)

        errorMessage.value =
            error.response?.data?.message ||
            'Gagal menyimpan service.'
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
    const confirmed = window.confirm(
        `Yakin ingin menghapus service "${service.name}"?`
    )

    if (!confirmed) return

    resetMessages()
    deleting.value = true

    try {
        const response = await api.delete(`/services/${service.id}`)

        services.value = services.value.filter(
            (item) => item.id !== service.id
        )

        successMessage.value =
            response.data?.message ||
            'Service berhasil dihapus.'

    } catch (error) {
        console.error('Failed to delete service:', error)

        errorMessage.value =
            error.response?.data?.message ||
            'Gagal menghapus service.'
    } finally {
        deleting.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Toggle Status
|--------------------------------------------------------------------------
*/

const toggleStatus = async (service) => {
    resetMessages()

    const newStatus = !service.is_active

    try {
        const response = await api.put(
            `/services/${service.id}`,
            {
                name: service.name,
                description: service.description,
                price: Number(service.price),
                duration: service.duration,
                image: service.image,
                is_active: newStatus,
            }
        )

        const updatedService =
            response.data?.data || response.data?.service

        if (updatedService) {
            const index = services.value.findIndex(
                (item) => item.id === updatedService.id
            )

            if (index !== -1) {
                services.value[index] = updatedService
            }
        } else {
            service.is_active = newStatus
        }

        successMessage.value = newStatus
            ? 'Service diaktifkan.'
            : 'Service dinonaktifkan.'

    } catch (error) {
        console.error('Failed to toggle service:', error)

        errorMessage.value =
            error.response?.data?.message ||
            'Gagal mengubah status service.'
    }
}


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    fetchServices()
})
</script>


<template>
    <section class="relative">


        <!-- ====================================================== -->
        <!-- PAGE HEADER -->
        <!-- ====================================================== -->

        <div
            class="
                mb-10 flex flex-col gap-6
                lg:flex-row lg:items-end lg:justify-between
            "
        >

            <div>

                <div
                    class="
                        mb-5 flex items-center gap-3
                        text-[9px] uppercase tracking-[0.35em]
                        text-[#191919]/30
                    "
                >
                    <span class="h-px w-8 bg-[#E85D75]"></span>
                    Management / 01
                </div>

                <h2
                    class="
                        text-5xl font-medium leading-none
                        tracking-[-0.06em]
                        sm:text-6xl
                    "
                >
                    SERVICES<span class="text-[#E85D75]">.</span>
                </h2>

                <p
                    class="
                        mt-5 max-w-xl text-sm leading-6
                        text-[#191919]/40
                    "
                >
                    Manage the photo editing services offered by
                    Teras Memori.
                </p>

            </div>


            <!-- Add button -->

            <button
                type="button"
                @click="openCreateModal"
                class="
                    group inline-flex items-center justify-center
                    gap-6 bg-[#191919] px-6 py-4
                    text-[9px] font-semibold uppercase
                    tracking-[0.25em] text-white
                    transition duration-500
                    hover:-translate-y-1
                    hover:bg-[#E85D75]
                "
            >
                <span>Add service</span>

                <span
                    class="
                        text-lg font-normal
                        transition duration-300
                        group-hover:translate-x-2
                    "
                >
                    +
                </span>
            </button>

        </div>


        <!-- ====================================================== -->
        <!-- ALERTS -->
        <!-- ====================================================== -->

        <Transition name="fade">

            <div
                v-if="successMessage"
                class="
                    mb-6 border-l-2 border-[#E85D75]
                    bg-[#F4A6B8]/10 px-5 py-4
                "
            >
                <div class="flex items-center justify-between gap-4">

                    <p class="text-xs text-[#191919]/60">
                        {{ successMessage }}
                    </p>

                    <button
                        type="button"
                        class="text-xs text-[#191919]/30 hover:text-[#E85D75]"
                        @click="successMessage = ''"
                    >
                        ×
                    </button>

                </div>
            </div>

        </Transition>


        <Transition name="fade">

            <div
                v-if="errorMessage"
                class="
                    mb-6 border-l-2 border-red-400
                    bg-red-50 px-5 py-4
                "
            >
                <div class="flex items-center justify-between gap-4">

                    <p class="text-xs leading-5 text-red-600">
                        {{ errorMessage }}
                    </p>

                    <button
                        type="button"
                        class="text-xs text-red-400 hover:text-red-600"
                        @click="errorMessage = ''"
                    >
                        ×
                    </button>

                </div>
            </div>

        </Transition>


        <!-- ====================================================== -->
        <!-- STATISTICS -->
        <!-- ====================================================== -->

        <div
            class="
                mb-8 grid border border-[#191919]/10
                bg-white sm:grid-cols-3
            "
        >

            <!-- Total -->

            <div
                class="
                    border-b border-[#191919]/10
                    p-6 sm:border-b-0 sm:border-r
                "
            >
                <div
                    class="
                        text-[9px] uppercase tracking-[0.3em]
                        text-[#191919]/30
                    "
                >
                    Total services
                </div>

                <div
                    class="
                        mt-4 text-4xl font-medium
                        tracking-[-0.05em]
                    "
                >
                    {{ services.length }}
                </div>

            </div>


            <!-- Active -->

            <div
                class="
                    border-b border-[#191919]/10
                    p-6 sm:border-b-0 sm:border-r
                "
            >
                <div
                    class="
                        text-[9px] uppercase tracking-[0.3em]
                        text-[#191919]/30
                    "
                >
                    Active
                </div>

                <div
                    class="
                        mt-4 flex items-center gap-3
                        text-4xl font-medium
                        tracking-[-0.05em]
                    "
                >
                    {{ activeCount }}

                    <span
                        class="
                            h-2 w-2 rounded-full
                            bg-[#E85D75]
                        "
                    ></span>
                </div>

            </div>


            <!-- Inactive -->

            <div class="p-6">

                <div
                    class="
                        text-[9px] uppercase tracking-[0.3em]
                        text-[#191919]/30
                    "
                >
                    Inactive
                </div>

                <div
                    class="
                        mt-4 text-4xl font-medium
                        tracking-[-0.05em]
                    "
                >
                    {{ inactiveCount }}
                </div>

            </div>

        </div>


        <!-- ====================================================== -->
        <!-- FILTER BAR -->
        <!-- ====================================================== -->

        <div
            class="
                mb-6 flex flex-col gap-4
                lg:flex-row lg:items-center lg:justify-between
            "
        >

            <!-- Search -->

            <div class="relative w-full lg:max-w-md">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    class="
                        pointer-events-none absolute left-4
                        top-1/2 h-4 w-4
                        -translate-y-1/2 text-[#191919]/25
                    "
                >
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m16 16 4 4"></path>
                </svg>

                <input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Search services..."
                    class="
                        w-full border border-[#191919]/10
                        bg-white py-3.5 pl-11 pr-4
                        text-xs outline-none
                        placeholder:text-[#191919]/25
                        transition
                        focus:border-[#E85D75]
                    "
                />

            </div>


            <!-- Filter -->

            <div
                class="
                    flex items-center gap-1
                    border border-[#191919]/10
                    bg-white p-1
                "
            >

                <button
                    type="button"
                    @click="statusFilter = 'all'"
                    class="
                        px-4 py-2.5 text-[9px]
                        uppercase tracking-[0.2em]
                        transition
                    "
                    :class="
                        statusFilter === 'all'
                            ? 'bg-[#191919] text-white'
                            : 'text-[#191919]/40 hover:text-[#191919]'
                    "
                >
                    All
                </button>

                <button
                    type="button"
                    @click="statusFilter = 'active'"
                    class="
                        px-4 py-2.5 text-[9px]
                        uppercase tracking-[0.2em]
                        transition
                    "
                    :class="
                        statusFilter === 'active'
                            ? 'bg-[#191919] text-white'
                            : 'text-[#191919]/40 hover:text-[#191919]'
                    "
                >
                    Active
                </button>

                <button
                    type="button"
                    @click="statusFilter = 'inactive'"
                    class="
                        px-4 py-2.5 text-[9px]
                        uppercase tracking-[0.2em]
                        transition
                    "
                    :class="
                        statusFilter === 'inactive'
                            ? 'bg-[#191919] text-white'
                            : 'text-[#191919]/40 hover:text-[#191919]'
                    "
                >
                    Inactive
                </button>

            </div>

        </div>


        <!-- ====================================================== -->
        <!-- LOADING -->
        <!-- ====================================================== -->

        <div
            v-if="loading"
            class="
                border border-[#191919]/10
                bg-white p-16 text-center
            "
        >

            <div
                class="
                    mx-auto h-8 w-8 animate-spin rounded-full
                    border-2 border-[#191919]/10
                    border-t-[#E85D75]
                "
            ></div>

            <p
                class="
                    mt-5 text-[9px] uppercase
                    tracking-[0.3em] text-[#191919]/30
                "
            >
                Loading services
            </p>

        </div>


        <!-- ====================================================== -->
        <!-- EMPTY -->
        <!-- ====================================================== -->

        <div
            v-else-if="filteredServices.length === 0"
            class="
                border border-[#191919]/10
                bg-white px-6 py-20 text-center
            "
        >

            <div
                class="
                    mx-auto flex h-16 w-16 items-center
                    justify-center rounded-full
                    border border-[#E85D75]/20
                    bg-[#F4A6B8]/10
                "
            >
                <span class="text-2xl text-[#E85D75]">
                    +
                </span>
            </div>

            <h3
                class="
                    mt-7 text-xl font-medium
                    tracking-[-0.03em]
                "
            >
                No services found.
            </h3>

            <p
                class="
                    mx-auto mt-3 max-w-sm
                    text-sm leading-6 text-[#191919]/35
                "
            >
                There are no services matching your current
                search or filter.
            </p>

            <button
                type="button"
                @click="openCreateModal"
                class="
                    mt-7 inline-flex items-center gap-3
                    bg-[#191919] px-5 py-3
                    text-[9px] font-semibold uppercase
                    tracking-[0.2em] text-white
                    transition hover:bg-[#E85D75]
                "
            >
                Add first service
                <span>→</span>
            </button>

        </div>


        <!-- ====================================================== -->
        <!-- SERVICES TABLE -->
        <!-- ====================================================== -->

        <div
            v-else
            class="
                overflow-hidden border border-[#191919]/10
                bg-white
            "
        >

            <!-- Desktop header -->

            <div
                class="
                    hidden border-b border-[#191919]/10
                    px-6 py-4 text-[8px]
                    uppercase tracking-[0.3em]
                    text-[#191919]/30
                    md:grid md:grid-cols-[1fr_150px_130px_110px_100px]
                    md:items-center md:gap-6
                "
            >
                <span>Service</span>
                <span>Price</span>
                <span>Duration</span>
                <span>Status</span>
                <span class="text-right">Action</span>
            </div>


            <!-- Rows -->

            <div
                v-for="(service, index) in filteredServices"
                :key="service.id"
                class="
                    group border-b border-[#191919]/10
                    p-6 last:border-b-0
                    transition duration-300
                    hover:bg-[#FFF8FA]
                "
            >

                <div
                    class="
                        flex flex-col gap-6
                        md:grid md:grid-cols-[1fr_150px_130px_110px_100px]
                        md:items-center md:gap-6
                    "
                >

                    <!-- Service -->

                    <div class="flex min-w-0 items-center gap-5">

                        <!-- Number -->

                        <div
                            class="
                                hidden shrink-0 text-[9px]
                                text-[#E85D75]
                                sm:block
                            "
                        >
                            {{ String(index + 1).padStart(2, '0') }}
                        </div>


                        <!-- Image -->

                        <div
                            class="
                                flex h-14 w-14 shrink-0
                                items-center justify-center
                                overflow-hidden
                                border border-[#191919]/10
                                bg-[#F4F0F1]
                            "
                        >

                            <img
                                v-if="service.image"
                                :src="service.image"
                                :alt="service.name"
                                class="h-full w-full object-cover"
                            />

                            <span
                                v-else
                                class="
                                    text-lg font-medium
                                    text-[#E85D75]/40
                                "
                            >
                                TM
                            </span>

                        </div>


                        <!-- Info -->

                        <div class="min-w-0">

                            <h3
                                class="
                                    truncate text-sm font-semibold
                                    tracking-[-0.02em]
                                "
                            >
                                {{ service.name }}
                            </h3>

                            <p
                                class="
                                    mt-1 line-clamp-2
                                    max-w-lg text-xs leading-5
                                    text-[#191919]/35
                                "
                            >
                                {{ service.description || 'No description.' }}
                            </p>

                        </div>

                    </div>


                    <!-- Price -->

                    <div>

                        <div
                            class="
                                mb-1 text-[8px] uppercase
                                tracking-[0.2em]
                                text-[#191919]/25
                                md:hidden
                            "
                        >
                            Price
                        </div>

                        <span class="text-sm font-medium">
                            Rp {{ formatPrice(service.price) }}
                        </span>

                    </div>


                    <!-- Duration -->

                    <div>

                        <div
                            class="
                                mb-1 text-[8px] uppercase
                                tracking-[0.2em]
                                text-[#191919]/25
                                md:hidden
                            "
                        >
                            Duration
                        </div>

                        <span
                            class="
                                text-xs text-[#191919]/50
                            "
                        >
                            {{ service.duration || '—' }}
                        </span>

                    </div>


                    <!-- Status -->

                    <div>

                        <div
                            class="
                                mb-1 text-[8px] uppercase
                                tracking-[0.2em]
                                text-[#191919]/25
                                md:hidden
                            "
                        >
                            Status
                        </div>

                        <button
                            type="button"
                            @click="toggleStatus(service)"
                            class="
                                inline-flex items-center gap-2
                                text-[9px] font-medium
                                uppercase tracking-[0.15em]
                                transition
                            "
                            :class="
                                service.is_active
                                    ? 'text-[#E85D75]'
                                    : 'text-[#191919]/30'
                            "
                        >

                            <span
                                class="
                                    h-1.5 w-1.5 rounded-full
                                "
                                :class="
                                    service.is_active
                                        ? 'bg-[#E85D75]'
                                        : 'bg-[#191919]/20'
                                "
                            ></span>

                            {{ service.is_active ? 'Active' : 'Inactive' }}

                        </button>

                    </div>


                    <!-- Actions -->

                    <div
                        class="
                            flex items-center justify-between
                            gap-2 md:justify-end
                        "
                    >

                        <button
                            type="button"
                            @click="openEditModal(service)"
                            class="
                                border border-[#191919]/10
                                px-3 py-2 text-[9px]
                                uppercase tracking-[0.15em]
                                text-[#191919]/45
                                transition
                                hover:border-[#E85D75]
                                hover:text-[#E85D75]
                            "
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            :disabled="deleting"
                            @click="deleteService(service)"
                            class="
                                flex h-8 w-8 items-center
                                justify-center
                                text-[#191919]/25
                                transition
                                hover:bg-red-50
                                hover:text-red-500
                                disabled:opacity-40
                            "
                            title="Delete"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                class="h-4 w-4"
                            >
                                <path d="M4 7h16" />
                                <path d="M10 11v6M14 11v6" />
                                <path
                                    d="M6 7l1 14h10l1-14"
                                />
                                <path
                                    d="M9 7V4h6v3"
                                />
                            </svg>
                        </button>

                    </div>

                </div>

            </div>

        </div>


        <!-- ====================================================== -->
        <!-- RESULT COUNT -->
        <!-- ====================================================== -->

        <div
            v-if="!loading && filteredServices.length > 0"
            class="
                mt-5 flex items-center justify-between
                text-[9px] uppercase tracking-[0.25em]
                text-[#191919]/25
            "
        >
            <span>
                Showing {{ filteredServices.length }}
                of {{ services.length }} services
            </span>

            <span>
                Teras Memori / Services
            </span>
        </div>


        <!-- ====================================================== -->
        <!-- MODAL -->
        <!-- ====================================================== -->

        <Transition name="modal">

            <div
                v-if="showModal"
                class="
                    fixed inset-0 z-[100]
                    flex items-center justify-center
                    bg-[#191919]/40 p-5
                    backdrop-blur-sm
                "
                @click.self="closeModal"
            >

                <div
                    class="
                        max-h-[90vh] w-full max-w-2xl
                        overflow-y-auto
                        bg-[#FFF8FA]
                        shadow-2xl
                    "
                >

                    <!-- Modal header -->

                    <div
                        class="
                            flex items-center justify-between
                            border-b border-[#191919]/10
                            px-7 py-6 sm:px-9
                        "
                    >

                        <div>

                            <div
                                class="
                                    text-[8px] uppercase
                                    tracking-[0.3em]
                                    text-[#191919]/30
                                "
                            >
                                Services / {{ editingService ? 'Edit' : 'New' }}
                            </div>

                            <h3
                                class="
                                    mt-2 text-2xl font-medium
                                    tracking-[-0.04em]
                                "
                            >
                                {{ editingService ? 'Edit service' : 'Add service' }}
                            </h3>

                        </div>


                        <button
                            type="button"
                            @click="closeModal"
                            class="
                                flex h-9 w-9 items-center
                                justify-center rounded-full
                                border border-[#191919]/10
                                text-[#191919]/40
                                transition
                                hover:border-[#E85D75]
                                hover:text-[#E85D75]
                            "
                        >
                            ×
                        </button>

                    </div>


                    <!-- Form -->

                    <form
                        @submit.prevent="saveService"
                        class="space-y-6 px-7 py-8 sm:px-9"
                    >

                        <!-- Name -->

                        <div>

                            <label
                                for="service-name"
                                class="
                                    mb-3 block text-[8px]
                                    font-semibold uppercase
                                    tracking-[0.3em]
                                    text-[#191919]/35
                                "
                            >
                                Service name
                            </label>

                            <input
                                id="service-name"
                                v-model="form.name"
                                type="text"
                                placeholder="Photo Editing"
                                required
                                class="
                                    w-full border-0 border-b
                                    border-[#191919]/15
                                    bg-transparent px-0 py-3
                                    text-sm outline-none
                                    transition
                                    placeholder:text-[#191919]/20
                                    focus:border-[#E85D75]
                                "
                            />

                        </div>


                        <!-- Description -->

                        <div>

                            <label
                                for="service-description"
                                class="
                                    mb-3 block text-[8px]
                                    font-semibold uppercase
                                    tracking-[0.3em]
                                    text-[#191919]/35
                                "
                            >
                                Description
                            </label>

                            <textarea
                                id="service-description"
                                v-model="form.description"
                                rows="4"
                                placeholder="Describe this service..."
                                class="
                                    w-full resize-none
                                    border border-[#191919]/10
                                    bg-white px-4 py-3
                                    text-sm outline-none
                                    transition
                                    placeholder:text-[#191919]/20
                                    focus:border-[#E85D75]
                                "
                            ></textarea>

                        </div>


                        <!-- Price + Duration -->

                        <div class="grid gap-6 sm:grid-cols-2">

                            <!-- Price -->

                            <div>

                                <label
                                    for="service-price"
                                    class="
                                        mb-3 block text-[8px]
                                        font-semibold uppercase
                                        tracking-[0.3em]
                                        text-[#191919]/35
                                    "
                                >
                                    Price
                                </label>

                                <div class="relative">

                                    <span
                                        class="
                                            absolute left-0 top-1/2
                                            -translate-y-1/2
                                            text-xs text-[#191919]/30
                                        "
                                    >
                                        Rp
                                    </span>

                                    <input
                                        id="service-price"
                                        v-model="form.price"
                                        type="number"
                                        min="0"
                                        step="1000"
                                        placeholder="50000"
                                        required
                                        class="
                                            w-full border-0 border-b
                                            border-[#191919]/15
                                            bg-transparent
                                            py-3 pl-7 pr-0
                                            text-sm outline-none
                                            transition
                                            placeholder:text-[#191919]/20
                                            focus:border-[#E85D75]
                                        "
                                    />

                                </div>

                            </div>


                            <!-- Duration -->

                            <div>

                                <label
                                    for="service-duration"
                                    class="
                                        mb-3 block text-[8px]
                                        font-semibold uppercase
                                        tracking-[0.3em]
                                        text-[#191919]/35
                                    "
                                >
                                    Duration
                                </label>

                                <input
                                    id="service-duration"
                                    v-model="form.duration"
                                    type="text"
                                    placeholder="1-2 Days"
                                    class="
                                        w-full border-0 border-b
                                        border-[#191919]/15
                                        bg-transparent px-0 py-3
                                        text-sm outline-none
                                        transition
                                        placeholder:text-[#191919]/20
                                        focus:border-[#E85D75]
                                    "
                                />

                            </div>

                        </div>


                        <!-- Image -->

                        <div>

                            <label
                                for="service-image"
                                class="
                                    mb-3 block text-[8px]
                                    font-semibold uppercase
                                    tracking-[0.3em]
                                    text-[#191919]/35
                                "
                            >
                                Image URL
                            </label>

                            <input
                                id="service-image"
                                v-model="form.image"
                                type="url"
                                placeholder="https://..."
                                class="
                                    w-full border-0 border-b
                                    border-[#191919]/15
                                    bg-transparent px-0 py-3
                                    text-sm outline-none
                                    transition
                                    placeholder:text-[#191919]/20
                                    focus:border-[#E85D75]
                                "
                            />

                        </div>


                        <!-- Active -->

                        <label
                            class="
                                flex cursor-pointer items-center
                                justify-between border
                                border-[#191919]/10 bg-white
                                px-5 py-4
                            "
                        >

                            <div>

                                <div class="text-xs font-medium">
                                    Active service
                                </div>

                                <div
                                    class="
                                        mt-1 text-[9px]
                                        text-[#191919]/30
                                    "
                                >
                                    Customers can see this service
                                </div>

                            </div>

                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="sr-only"
                            />

                            <div
                                class="
                                    relative h-6 w-11
                                    rounded-full
                                    transition
                                "
                                :class="
                                    form.is_active
                                        ? 'bg-[#E85D75]'
                                        : 'bg-[#191919]/15'
                                "
                            >
                                <span
                                    class="
                                        absolute top-1 h-4 w-4
                                        rounded-full bg-white
                                        shadow-sm transition
                                    "
                                    :class="
                                        form.is_active
                                            ? 'left-6'
                                            : 'left-1'
                                    "
                                ></span>
                            </div>

                        </label>


                        <!-- Buttons -->

                        <div
                            class="
                                flex flex-col-reverse gap-3
                                border-t border-[#191919]/10
                                pt-7 sm:flex-row sm:justify-end
                            "
                        >

                            <button
                                type="button"
                                @click="closeModal"
                                class="
                                    px-5 py-3 text-[9px]
                                    uppercase tracking-[0.2em]
                                    text-[#191919]/40
                                    transition
                                    hover:text-[#191919]
                                "
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                :disabled="saving"
                                class="
                                    inline-flex items-center
                                    justify-center gap-5
                                    bg-[#191919] px-6 py-4
                                    text-[9px] font-semibold
                                    uppercase tracking-[0.2em]
                                    text-white
                                    transition duration-300
                                    hover:bg-[#E85D75]
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50
                                "
                            >
                                <span>
                                    {{
                                        saving
                                            ? 'Saving...'
                                            : editingService
                                                ? 'Update service'
                                                : 'Create service'
                                    }}
                                </span>

                                <span v-if="!saving">→</span>
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </Transition>

    </section>
</template>


<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-active > div,
.modal-leave-active > div {
    transition:
        opacity 0.3s ease,
        transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
    opacity: 0;
    transform: translateY(15px) scale(0.98);
}
</style>
```

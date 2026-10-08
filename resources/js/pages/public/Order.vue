<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const services = ref([])
const loading = ref(true)
const submitting = ref(false)

const errorMessage = ref('')
const successMessage = ref('')

const fileInput = ref(null)
const isDragging = ref(false)

const selectedFiles = ref([])


/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

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

        /*
        |--------------------------------------------------------------------------
        | PRESELECT SERVICE FROM QUERY
        |--------------------------------------------------------------------------
        */

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
        console.error(error)

        errorMessage.value =
            error.response?.data?.message ||
            'Gagal mengambil data service.'
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
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(price)
}


/*
|--------------------------------------------------------------------------
| FILE CONSTANTS
|--------------------------------------------------------------------------
*/

const MAX_FILES = 10
const MAX_FILE_SIZE = 5 * 1024 * 1024

const allowedTypes = [
    'image/jpeg',
    'image/png',
    'image/webp',
]


/*
|--------------------------------------------------------------------------
| FORMAT FILE SIZE
|--------------------------------------------------------------------------
*/

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
| OPEN FILE PICKER
|--------------------------------------------------------------------------
*/

const openFilePicker = () => {
    fileInput.value?.click()
}


/*
|--------------------------------------------------------------------------
| VALIDATE FILE
|--------------------------------------------------------------------------
*/

const validateFile = (file) => {
    if (!allowedTypes.includes(file.type)) {
        return `${file.name} bukan format gambar yang didukung.`
    }

    if (file.size > MAX_FILE_SIZE) {
        return `${file.name} melebihi ukuran maksimal 5 MB.`
    }

    return null
}


/*
|--------------------------------------------------------------------------
| ADD FILES
|--------------------------------------------------------------------------
*/

const addFiles = (files) => {
    errorMessage.value = ''

    const incomingFiles = Array.from(files || [])

    if (!incomingFiles.length) {
        return
    }

    const remainingSlots =
        MAX_FILES - selectedFiles.value.length

    if (remainingSlots <= 0) {
        errorMessage.value =
            'Maksimal 10 foto dapat diupload.'

        return
    }

    const filesToAdd = incomingFiles.slice(
        0,
        remainingSlots
    )

    if (incomingFiles.length > remainingSlots) {
        errorMessage.value =
            `Maksimal ${MAX_FILES} foto dapat diupload.`
    }

    for (const file of filesToAdd) {
        const validationError = validateFile(file)

        if (validationError) {
            errorMessage.value = validationError
            continue
        }

        /*
        |--------------------------------------------------------------------------
        | PREVENT DUPLICATE FILE
        |--------------------------------------------------------------------------
        */

        const duplicate = selectedFiles.value.some(
            (item) =>
                item.file.name === file.name &&
                item.file.size === file.size &&
                item.file.lastModified === file.lastModified
        )

        if (duplicate) {
            continue
        }

        selectedFiles.value.push({
            file,
            preview: URL.createObjectURL(file),
        })
    }
}


/*
|--------------------------------------------------------------------------
| FILE INPUT CHANGE
|--------------------------------------------------------------------------
*/

const handleFileChange = (event) => {
    addFiles(event.target.files)

    /*
    |--------------------------------------------------------------------------
    | RESET INPUT
    |--------------------------------------------------------------------------
    | Supaya file yang sama bisa dipilih lagi setelah dihapus.
    */

    event.target.value = ''
}


/*
|--------------------------------------------------------------------------
| DRAG EVENTS
|--------------------------------------------------------------------------
*/

const handleDragOver = (event) => {
    event.preventDefault()
    isDragging.value = true
}

const handleDragLeave = (event) => {
    event.preventDefault()
    isDragging.value = false
}

const handleDrop = (event) => {
    event.preventDefault()

    isDragging.value = false

    addFiles(event.dataTransfer.files)
}


/*
|--------------------------------------------------------------------------
| REMOVE FILE
|--------------------------------------------------------------------------
*/

const removeFile = (index) => {
    const item = selectedFiles.value[index]

    if (item?.preview) {
        URL.revokeObjectURL(item.preview)
    }

    selectedFiles.value.splice(index, 1)

    errorMessage.value = ''
}


/*
|--------------------------------------------------------------------------
| CLEAR FILES
|--------------------------------------------------------------------------
*/

const clearFiles = () => {
    selectedFiles.value.forEach((item) => {
        if (item.preview) {
            URL.revokeObjectURL(item.preview)
        }
    })

    selectedFiles.value = []

    errorMessage.value = ''
}


/*
|--------------------------------------------------------------------------
| SUBMIT ORDER
|--------------------------------------------------------------------------
*/

const submitOrder = async () => {
    errorMessage.value = ''
    successMessage.value = ''

    // Check authentication
    if (!auth.isAuthenticated) {
        errorMessage.value = 'Silakan login terlebih dahulu untuk membuat order.'
        router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
        return
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (!form.value.service_id) {
        errorMessage.value =
            'Silakan pilih service terlebih dahulu.'

        return
    }

    if (!form.value.customer_name.trim()) {
        errorMessage.value =
            'Nama customer wajib diisi.'

        return
    }

    if (
        form.value.customer_email &&
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
            form.value.customer_email
        )
    ) {
        errorMessage.value =
            'Format email tidak valid.'

        return
    }

    if (
        !form.value.customer_phone ||
        !form.value.customer_phone.trim()
    ) {
        errorMessage.value =
            'Nomor WhatsApp wajib diisi.'

        return
    }

    if (
        Number(form.value.quantity) < 1 ||
        Number(form.value.quantity) > 100
    ) {
        errorMessage.value =
            'Quantity harus berada antara 1 sampai 100.'

        return
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE FORMDATA
    |--------------------------------------------------------------------------
    */

    const formData = new FormData()

    formData.append(
        'customer[name]',
        form.value.customer_name.trim()
    )

    if (form.value.customer_email.trim()) {
        formData.append(
            'customer[email]',
            form.value.customer_email.trim()
        )
    }

    if (form.value.customer_phone.trim()) {
        formData.append(
            'customer[phone]',
            form.value.customer_phone.trim()
        )
    }

    formData.append(
        'items[0][service_id]',
        String(form.value.service_id)
    )

    formData.append(
        'items[0][quantity]',
        String(form.value.quantity)
    )

    if (form.value.notes.trim()) {
        formData.append(
            'notes',
            form.value.notes.trim()
        )
    }


    /*
    |--------------------------------------------------------------------------
    | APPEND FILES
    |--------------------------------------------------------------------------
    */

    selectedFiles.value.forEach((item) => {
        formData.append(
            'files[]',
            item.file
        )
    })


    /*
    |--------------------------------------------------------------------------
    | SEND REQUEST
    |--------------------------------------------------------------------------
    */

    submitting.value = true

    try {
        const response = await api.post(
            '/orders',
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        )

        const order = response.data.data

        /*
        |--------------------------------------------------------------------------
        | SAVE ORDER FOR SUCCESS PAGE
        |--------------------------------------------------------------------------
        */

        sessionStorage.setItem(
            'latest_order',
            JSON.stringify(order)
        )

        /*
        |--------------------------------------------------------------------------
        | CLEANUP PREVIEWS
        |--------------------------------------------------------------------------
        */

        selectedFiles.value.forEach((item) => {
            if (item.preview) {
                URL.revokeObjectURL(item.preview)
            }
        })

        selectedFiles.value = []

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------

        */

        router.push({
            name: 'order-success',
        })
    } catch (error) {
        console.error(error)

        /*
        |--------------------------------------------------------------------------
        | VALIDATION ERROR
        |--------------------------------------------------------------------------
        */

        if (error.response?.status === 422) {
            const errors = error.response.data?.errors

            if (errors) {
                const firstError =
                    Object.values(errors)[0]?.[0]

                errorMessage.value =
                    firstError ||
                    'Data yang dikirim tidak valid.'
            } else {
                errorMessage.value =
                    error.response.data?.message ||
                    'Data yang dikirim tidak valid.'
            }

            return
        }


        /*
        |--------------------------------------------------------------------------
        | OTHER ERRORS
        |--------------------------------------------------------------------------
        */

        if (error.response?.status === 401) {
            errorMessage.value =
                'Sesi tidak valid. Silakan coba lagi.'
        } else if (error.response?.status === 413) {
            errorMessage.value =
                'Ukuran file terlalu besar.'
        } else if (error.response?.status >= 500) {
            errorMessage.value =
                'Terjadi kesalahan pada server.'
        } else {
            errorMessage.value =
                error.response?.data?.message ||
                'Gagal membuat order. Silakan coba lagi.'
        }
    } finally {
        submitting.value = false
    }
}


/*
|--------------------------------------------------------------------------
| CLEANUP
|--------------------------------------------------------------------------
*/

onMounted(async () => {
    if (!auth.isAuthenticated) {
        router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
        return
    }
    fetchServices()
})
</script>

<template>
    <main class="min-h-screen bg-[#FFF8FA] text-[#191919]">

        <!-- ========================================================= -->
        <!-- HERO -->
        <!-- ========================================================= -->

        <section
            class="relative overflow-hidden border-b border-black/10 px-6 pb-20 pt-12 md:px-10 md:pb-28 md:pt-16"
        >
            <!-- Decorative circle -->
            <div
                class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full border border-[#E85D75]/20 md:h-96 md:w-96"
            ></div>

            <div
                class="pointer-events-none absolute right-12 top-24 h-32 w-32 rounded-full bg-[#F4A6B8]/10 blur-3xl"
            ></div>

            <div class="mx-auto max-w-7xl">
                <RouterLink
                    to="/"
                    class="mb-16 inline-flex items-center gap-3 text-[10px] font-medium uppercase tracking-[0.25em] text-black/50 transition hover:text-[#E85D75]"
                >
                    <span class="text-base">←</span>
                    Back to website
                </RouterLink>

                <div class="max-w-4xl">
                    <p
                        class="mb-5 text-[10px] font-semibold uppercase tracking-[0.35em] text-[#E85D75]"
                    >
                        05 — Start a Project
                    </p>

                    <h1
                        class="text-6xl font-light leading-[0.9] tracking-[-0.06em] sm:text-7xl md:text-8xl lg:text-[9rem]"
                    >
                        LET'S
                        <span class="font-semibold italic">
                            CREATE.
                        </span>
                    </h1>

                    <p
                        class="mt-8 max-w-xl text-sm leading-7 text-black/55 md:text-base"
                    >
                        Tell us what you have in mind, send your
                        photographs, and let the studio take care
                        of the rest.
                    </p>
                </div>
            </div>
        </section>


        <!-- ========================================================= -->
        <!-- ORDER CONTENT -->
        <!-- ========================================================= -->

        <section
            class="px-6 py-16 md:px-10 md:py-24"
        >
            <div
                class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[1fr_360px]"
            >

                <!-- ================================================= -->
                <!-- FORM -->
                <!-- ================================================= -->

                <form
                    class="space-y-16"
                    @submit.prevent="submitOrder"
                >

                    <!-- ============================================= -->
                    <!-- SERVICE -->
                    <!-- ============================================= -->

                    <div>
                        <div
                            class="mb-8 flex items-end justify-between border-b border-black/10 pb-4"
                        >
                            <div>
                                <p
                                    class="mb-2 text-[9px] font-semibold uppercase tracking-[0.3em] text-[#E85D75]"
                                >
                                    01
                                </p>

                                <h2
                                    class="text-2xl font-medium tracking-tight"
                                >
                                    Choose a service
                                </h2>
                            </div>

                            <span
                                class="text-[9px] uppercase tracking-[0.2em] text-black/35"
                            >
                                Required
                            </span>
                        </div>


                        <!-- Loading -->
                        <div
                            v-if="loading"
                            class="grid gap-3 sm:grid-cols-2"
                        >
                            <div
                                v-for="n in 4"
                                :key="n"
                                class="h-28 animate-pulse rounded-2xl bg-black/5"
                            ></div>
                        </div>


                        <!-- Services -->
                        <div
                            v-else
                            class="grid gap-3 sm:grid-cols-2"
                        >
                            <button
                                v-for="service in services"
                                :key="service.id"
                                type="button"
                                class="group relative rounded-2xl border p-5 text-left transition-all duration-300"
                                :class="
                                    Number(form.service_id) === Number(service.id)
                                        ? 'border-[#E85D75] bg-[#E85D75]/5'
                                        : 'border-black/10 bg-white hover:border-black/25'
                                "
                                @click="
                                    form.service_id = service.id
                                "
                            >
                                <span
                                    v-if="
                                        Number(form.service_id) ===
                                        Number(service.id)
                                    "
                                    class="absolute right-4 top-4 flex h-5 w-5 items-center justify-center rounded-full bg-[#E85D75] text-[10px] text-white"
                                >
                                    ✓
                                </span>

                                <p
                                    class="pr-8 text-base font-medium"
                                >
                                    {{ service.name }}
                                </p>

                                <p
                                    v-if="service.description"
                                    class="mt-2 line-clamp-2 text-xs leading-5 text-black/45"
                                >
                                    {{ service.description }}
                                </p>

                                <p
                                    class="mt-5 text-xs font-semibold text-[#E85D75]"
                                >
                                    {{ formatPrice(service.price) }}
                                </p>
                            </button>
                        </div>
                    </div>


                    <!-- ============================================= -->
                    <!-- CUSTOMER -->
                    <!-- ============================================= -->

                    <div>
                        <div
                            class="mb-8 flex items-end justify-between border-b border-black/10 pb-4"
                        >
                            <div>
                                <p
                                    class="mb-2 text-[9px] font-semibold uppercase tracking-[0.3em] text-[#E85D75]"
                                >
                                    02
                                </p>

                                <h2
                                    class="text-2xl font-medium tracking-tight"
                                >
                                    Your information
                                </h2>
                            </div>

                            <span
                                class="text-[9px] uppercase tracking-[0.2em] text-black/35"
                            >
                                Contact
                            </span>
                        </div>


                        <div
                            class="grid gap-x-8 gap-y-8 md:grid-cols-2"
                        >

                            <!-- Name -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-3 block text-[9px] font-semibold uppercase tracking-[0.25em] text-black/45"
                                >
                                    Full name *
                                </label>

                                <input
                                    v-model="form.customer_name"
                                    type="text"
                                    placeholder="Your full name"
                                    class="w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-lg outline-none transition placeholder:text-black/20 focus:border-[#E85D75]"
                                />
                            </div>


                            <!-- Email -->
                            <div>
                                <label
                                    class="mb-3 block text-[9px] font-semibold uppercase tracking-[0.25em] text-black/45"
                                >
                                    Email
                                </label>

                                <input
                                    v-model="form.customer_email"
                                    type="email"
                                    placeholder="you@email.com"
                                    class="w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-base outline-none transition placeholder:text-black/20 focus:border-[#E85D75]"
                                />
                            </div>


                            <!-- Phone -->
                            <div>
                                <label
                                    class="mb-3 block text-[9px] font-semibold uppercase tracking-[0.25em] text-black/45"
                                >
                                    WhatsApp *
                                </label>

                                <input
                                    v-model="form.customer_phone"
                                    type="tel"
                                    placeholder="08xxxxxxxxxx"
                                    class="w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-base outline-none transition placeholder:text-black/20 focus:border-[#E85D75]"
                                />
                            </div>
                        </div>
                    </div>


                    <!-- ============================================= -->
                    <!-- UPLOAD -->
                    <!-- ============================================= -->

                    <div>
                        <div
                            class="mb-8 flex items-end justify-between border-b border-black/10 pb-4"
                        >
                            <div>
                                <p
                                    class="mb-2 text-[9px] font-semibold uppercase tracking-[0.3em] text-[#E85D75]"
                                >
                                    03
                                </p>

                                <h2
                                    class="text-2xl font-medium tracking-tight"
                                >
                                    Upload your photos
                                </h2>
                            </div>

                            <span
                                class="text-right text-[9px] uppercase tracking-[0.15em] text-black/35"
                            >
                                Optional · Max 10
                            </span>
                        </div>


                        <!-- Hidden input -->
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            class="hidden"
                            @change="handleFileChange"
                        />


                        <!-- Drop zone -->
                        <button
                            type="button"
                            class="group relative flex min-h-64 w-full flex-col items-center justify-center overflow-hidden rounded-3xl border-2 border-dashed px-6 py-12 text-center transition-all duration-300"
                            :class="
                                isDragging
                                    ? 'border-[#E85D75] bg-[#E85D75]/5'
                                    : 'border-black/10 bg-white hover:border-[#E85D75]/50 hover:bg-[#E85D75]/[0.02]'
                            "
                            @click="openFilePicker"
                            @dragover="handleDragOver"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop"
                        >
                            <div
                                class="mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-[#F4A6B8]/15 text-2xl text-[#E85D75] transition-transform duration-300 group-hover:scale-110"
                            >
                                +
                            </div>

                            <p
                                class="text-sm font-medium"
                            >
                                Drop your photos here
                            </p>

                            <p
                                class="mt-2 text-xs text-black/40"
                            >
                                or click to browse from your device
                            </p>

                            <p
                                class="mt-5 text-[9px] uppercase tracking-[0.2em] text-black/30"
                            >
                                JPG · JPEG · PNG · WEBP · MAX 5 MB EACH
                            </p>
                        </button>


                        <!-- Selected files -->
                        <div
                            v-if="selectedFiles.length"
                            class="mt-6"
                        >
                            <div
                                class="mb-4 flex items-center justify-between"
                            >
                                <p
                                    class="text-[9px] font-semibold uppercase tracking-[0.2em] text-black/40"
                                >
                                    {{ selectedFiles.length }} / 10 photos
                                </p>

                                <button
                                    type="button"
                                    class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#E85D75] transition hover:text-black"
                                    @click="clearFiles"
                                >
                                    Remove all
                                </button>
                            </div>


                            <div
                                class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4"
                            >
                                <div
                                    v-for="(item, index) in selectedFiles"
                                    :key="`${item.file.name}-${item.file.lastModified}`"
                                    class="group relative aspect-square overflow-hidden rounded-2xl bg-black/5"
                                >
                                    <img
                                        :src="item.preview"
                                        :alt="item.file.name"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    />

                                    <!-- Overlay -->
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"
                                    ></div>


                                    <!-- File info -->
                                    <div
                                        class="absolute bottom-3 left-3 right-3 translate-y-2 opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100"
                                    >
                                        <p
                                            class="truncate text-[10px] font-medium text-white"
                                        >
                                            {{ item.file.name }}
                                        </p>

                                        <p
                                            class="mt-1 text-[9px] text-white/60"
                                        >
                                            {{ formatFileSize(item.file.size) }}
                                        </p>
                                    </div>


                                    <!-- Remove -->
                                    <button
                                        type="button"
                                        class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-sm text-black shadow-sm transition hover:bg-[#E85D75] hover:text-white"
                                        title="Remove photo"
                                        @click.stop="removeFile(index)"
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- ============================================= -->
                    <!-- PROJECT DETAILS -->
                    <!-- ============================================= -->

                    <div>
                        <div
                            class="mb-8 flex items-end justify-between border-b border-black/10 pb-4"
                        >
                            <div>
                                <p
                                    class="mb-2 text-[9px] font-semibold uppercase tracking-[0.3em] text-[#E85D75]"
                                >
                                    04
                                </p>

                                <h2
                                    class="text-2xl font-medium tracking-tight"
                                >
                                    Project details
                                </h2>
                            </div>
                        </div>


                        <div class="space-y-8">

                            <!-- Quantity -->
                            <div>
                                <label
                                    class="mb-3 block text-[9px] font-semibold uppercase tracking-[0.25em] text-black/45"
                                >
                                    Quantity *
                                </label>

                                <input
                                    v-model.number="form.quantity"
                                    type="number"
                                    min="1"
                                    max="100"
                                    class="w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-lg outline-none transition focus:border-[#E85D75]"
                                />

                                <p
                                    class="mt-2 text-[10px] text-black/35"
                                >
                                    Number of photos / items to be edited.
                                </p>
                            </div>


                            <!-- Notes -->
                            <div>
                                <label
                                    class="mb-3 block text-[9px] font-semibold uppercase tracking-[0.25em] text-black/45"
                                >
                                    Brief / Notes
                                </label>

                                <textarea
                                    v-model="form.notes"
                                    rows="5"
                                    maxlength="2000"
                                    placeholder="Tell us about the editing style, references, colors, mood, deadline, or anything else we should know..."
                                    class="w-full resize-none rounded-2xl border border-black/10 bg-white px-5 py-4 text-sm leading-6 outline-none transition placeholder:text-black/25 focus:border-[#E85D75]"
                                ></textarea>

                                <div
                                    class="mt-2 text-right text-[9px] text-black/30"
                                >
                                    {{ form.notes.length }} / 2000
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- ============================================= -->
                    <!-- ERROR -->
                    <!-- ============================================= -->

                    <div
                        v-if="errorMessage"
                        class="rounded-2xl border border-[#E85D75]/30 bg-[#E85D75]/5 px-5 py-4"
                    >
                        <div class="flex gap-3">
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#E85D75] text-[10px] text-white"
                            >
                                !
                            </span>

                            <p
                                class="text-xs leading-5 text-[#b73e54]"
                            >
                                {{ errorMessage }}
                            </p>
                        </div>
                    </div>


                    <!-- ============================================= -->
                    <!-- SUCCESS -->
                    <!-- ============================================= -->

                    <div
                        v-if="successMessage"
                        class="rounded-2xl border border-green-600/20 bg-green-50 px-5 py-4"
                    >
                        <p
                            class="text-xs leading-5 text-green-700"
                        >
                            {{ successMessage }}
                        </p>
                    </div>


                    <!-- ============================================= -->
                    <!-- SUBMIT -->
                    <!-- ============================================= -->

                    <div
                        class="flex flex-col gap-5 border-t border-black/10 pt-8 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p
                            class="max-w-sm text-[10px] leading-5 text-black/40"
                        >
                            By submitting this form, you agree that
                            Teras Memori may contact you regarding
                            this project.
                        </p>

                        <button
                            type="submit"
                            :disabled="submitting"
                            class="group inline-flex min-h-14 items-center justify-center gap-5 rounded-full bg-[#191919] px-8 text-[10px] font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-[#E85D75] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span>
                                {{
                                    submitting
                                        ? 'Sending...'
                                        : 'Submit project'
                                }}
                            </span>

                            <span
                                class="text-lg transition-transform duration-300 group-hover:translate-x-1"
                            >
                                →
                            </span>
                        </button>
                    </div>
                </form>


                <!-- ================================================= -->
                <!-- ORDER SUMMARY -->
                <!-- ================================================= -->

                <aside class="lg:sticky lg:top-28 lg:self-start">

                    <div
                        class="overflow-hidden rounded-3xl bg-[#191919] text-white"
                    >

                        <!-- Header -->
                        <div
                            class="border-b border-white/10 px-6 py-6"
                        >
                            <p
                                class="text-[9px] font-semibold uppercase tracking-[0.3em] text-[#F4A6B8]"
                            >
                                Project summary
                            </p>

                            <h2
                                class="mt-2 text-xl font-medium"
                            >
                                Your brief
                            </h2>
                        </div>


                        <div class="space-y-6 px-6 py-6">

                            <!-- Service -->
                            <div>
                                <p
                                    class="text-[9px] uppercase tracking-[0.2em] text-white/35"
                                >
                                    Service
                                </p>

                                <p
                                    class="mt-2 text-sm font-medium"
                                >
                                    {{
                                        selectedService?.name ||
                                        'Select a service'
                                    }}
                                </p>
                            </div>


                            <!-- Quantity -->
                            <div
                                class="flex items-center justify-between border-t border-white/10 pt-5"
                            >
                                <p
                                    class="text-[9px] uppercase tracking-[0.2em] text-white/35"
                                >
                                    Quantity
                                </p>

                                <p
                                    class="text-sm"
                                >
                                    {{ form.quantity || 0 }}
                                </p>
                            </div>


                            <!-- Photos -->
                            <div
                                class="flex items-center justify-between border-t border-white/10 pt-5"
                            >
                                <p
                                    class="text-[9px] uppercase tracking-[0.2em] text-white/35"
                                >
                                    Photos
                                </p>

                                <p
                                    class="text-sm"
                                >
                                    {{ selectedFiles.length }}
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
                                    {{ formatPrice(subtotal) }}
                                </p>

                                <p
                                    class="mt-2 text-[9px] leading-4 text-white/35"
                                >
                                    Final pricing may be adjusted
                                    after the studio reviews your brief.
                                </p>
                            </div>
                        </div>


                        <!-- Bottom -->
                        <div
                            class="border-t border-white/10 px-6 py-5"
                        >
                            <p
                                class="text-[9px] uppercase tracking-[0.18em] text-white/30"
                            >
                                TERAS MEMORI — CREATIVE PHOTO STUDIO
                            </p>
                        </div>
                    </div>


                    <!-- Help -->
                    <div class="mt-6 px-2">
                        <p
                            class="text-xs leading-6 text-black/40"
                        >
                            Need something different?
                            <RouterLink
                                to="/"
                                class="font-medium text-[#E85D75] underline underline-offset-4"
                            >
                                Contact the studio
                            </RouterLink>
                        </p>
                    </div>
                </aside>
            </div>
        </section>


        <!-- ========================================================= -->
        <!-- FOOTER NOTE -->
        <!-- ========================================================= -->

        <section
            class="border-t border-black/10 px-6 py-12 md:px-10"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <p
                    class="text-[9px] uppercase tracking-[0.25em] text-black/30"
                >
                    Teras Memori Studio
                </p>

                <p
                    class="max-w-md text-right text-xs leading-5 text-black/35"
                >
                    We turn photographs into lasting memories.
                </p>
            </div>
        </section>
    </main>
</template>
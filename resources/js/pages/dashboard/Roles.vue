<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../services/api'

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const roles = ref([])

const loading = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const searchQuery = ref('')

const showModal = ref(false)
const editingRole = ref(null)

const deletingId = ref(null)

const validationErrors = ref({})

const form = ref({
    name: '',
})

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

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

const filteredRoles = computed(() => {
    const query = searchQuery.value
        .toLowerCase()
        .trim()

    if (!query) {
        return roles.value
    }

    return roles.value.filter((role) =>
        role.name.toLowerCase().includes(query)
    )
})

/*
|--------------------------------------------------------------------------
| Fetch Roles
|--------------------------------------------------------------------------
*/

const fetchRoles = async () => {
    loading.value = true
    clearMessages()

    try {
        const response = await api.get(
            '/admin/roles'
        )

        roles.value = response.data.data || []
    } catch (error) {
        errorMessage.value = getApiMessage(
            error,
            'Gagal memuat data role.'
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

const openCreateModal = () => {
    clearMessages()
    clearValidationErrors()

    editingRole.value = null
    form.value = { name: '' }
    showModal.value = true

    document.body.style.overflow = 'hidden'
}

const openEditModal = (role) => {
    clearMessages()
    clearValidationErrors()

    editingRole.value = role
    form.value = { name: role.name }
    showModal.value = true

    document.body.style.overflow = 'hidden'
}

const closeModal = () => {
    if (saving.value) {
        return
    }

    showModal.value = false
    editingRole.value = null
    clearValidationErrors()

    document.body.style.overflow = ''
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const validateForm = () => {
    clearValidationErrors()

    const errors = {}

    if (!form.value.name.trim()) {
        errors.name = ['Role name is required.']
    } else if (!/^[a-z0-9_-]+$/i.test(form.value.name.trim())) {
        errors.name = [
            'Role name hanya boleh huruf, angka, dash, underscore.',
        ]
    }

    validationErrors.value = errors

    return Object.keys(errors).length === 0
}

/*
|--------------------------------------------------------------------------
| Save Role
|--------------------------------------------------------------------------
*/

const saveRole = async () => {
    clearMessages()

    if (!validateForm()) {
        return
    }

    saving.value = true

    try {
        const payload = {
            name: form.value.name.trim(),
        }

        let response

        if (editingRole.value) {
            response = await api.put(
                `/admin/roles/${editingRole.value.id}`,
                payload
            )
        } else {
            response = await api.post(
                '/admin/roles',
                payload
            )
        }

        const savedRole = response.data.data

        if (editingRole.value) {
            const index = roles.value.findIndex(
                (role) => role.id === editingRole.value.id
            )

            if (index !== -1) {
                roles.value[index] = savedRole
            }
        } else {
            roles.value.unshift(savedRole)
        }

        successMessage.value =
            response.data.message || 'Role berhasil disimpan.'

        closeModal()
    } catch (error) {
        if (error.response?.status === 422) {
            const errors = error.response.data?.errors

            if (errors) {
                validationErrors.value = errors
            } else {
                errorMessage.value = getApiMessage(
                    error,
                    'Data tidak valid.'
                )
            }
        } else {
            errorMessage.value = getApiMessage(
                error,
                'Gagal menyimpan role.'
            )
        }
    } finally {
        saving.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Delete Role
|--------------------------------------------------------------------------
*/

const deleteRole = async (role) => {
    if (deletingId.value) {
        return
    }

    const confirmed = window.confirm(
        `Hapus role "${role.name}"?`
    )

    if (!confirmed) {
        return
    }

    clearMessages()
    deletingId.value = role.id

    try {
        const response = await api.delete(
            `/admin/roles/${role.id}`
        )

        roles.value = roles.value.filter(
            (r) => r.id !== role.id
        )

        successMessage.value =
            response.data.message || 'Role berhasil dihapus.'
    } catch (error) {
        errorMessage.value = getApiMessage(
            error,
            'Gagal menghapus role.'
        )
    } finally {
        deletingId.value = null
    }
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    fetchRoles()
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
                            Roles
                        </h1>

                        <p
                            class="mt-4 max-w-xl text-sm leading-6 text-[#191919]/40"
                        >
                            Kelola role pengguna untuk keperluan
                            akses dan otorisasi di masa depan.
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

                        Add Role
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
                class="mt-4 flex items-start justify-between gap-5 border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700"
            >

                <p>
                    {{ successMessage }}
                </p>

                <button
                    type="button"
                    class="text-lg leading-none text-green-400 hover:text-green-700"
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
                        Total roles
                    </span>

                    <div
                        class="mt-3 text-4xl font-medium tracking-[-0.05em]"
                    >
                        {{ roles.length }}
                    </div>

                </div>


                <!-- Total Users -->
                <div
                    class="bg-[#FFF8FA] px-6 py-8 sm:px-10 lg:px-16"
                >

                    <span
                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Total users
                    </span>

                    <div
                        class="mt-3 text-4xl font-medium tracking-[-0.05em] text-[#E85D75]"
                    >
                        {{
                            roles.reduce(
                                (sum, r) => sum + (r.users_count || 0),
                                0
                            )
                        }}
                    </div>

                </div>


                <!-- Search -->
                <div
                    class="bg-[#FFF8FA] px-6 py-8 sm:px-10 lg:px-16"
                >

                    <span
                        class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                    >
                        Search
                    </span>

                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search roles..."
                        class="mt-3 w-full rounded-full border border-[#191919]/10 bg-white px-5 py-2.5 text-sm outline-none transition placeholder:text-[#191919]/25 focus:border-[#E85D75]/50"
                    />

                </div>

            </div>

        </section>


        <!-- =====================================================
             ROLES TABLE
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
                        v-for="item in 3"
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
                    v-else-if="errorMessage && roles.length === 0"
                    class="border-y border-[#191919]/10 py-20 text-center"
                >

                    <p
                        class="text-sm text-[#191919]/45"
                    >
                        Unable to load roles.
                    </p>

                    <button
                        type="button"
                        @click="fetchRoles"
                        class="mt-6 rounded-full border border-[#191919]/15 px-6 py-3 text-[10px] uppercase tracking-[0.2em] transition hover:border-[#191919]/40 hover:bg-[#191919]/5"
                    >
                        Try again
                    </button>

                </div>


                <!-- Empty -->
                <div
                    v-else-if="filteredRoles.length === 0"
                    class="border-y border-[#191919]/10 py-20 text-center"
                >

                    <p
                        class="text-sm text-[#191919]/40"
                    >
                        No roles found.
                    </p>

                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="searchQuery = ''"
                        class="mt-5 text-[9px] uppercase tracking-[0.25em] text-[#E85D75] hover:underline">
                        Clear search
                    </button>

                </div>


                <!-- Table -->
                <div
                    v-else
                    class="overflow-x-auto border border-[#191919]/10 bg-white"
                >

                    <table
                        class="w-full min-w-[600px] border-collapse"
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
                                    Role name
                                </th>

                                <th
                                    class="px-6 py-5 text-left text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/30"
                                >
                                    Users
                                </th>

                                <th
                                    class="px-6 py-5 text-right text-[9px] font-medium uppercase tracking-[0.25em] text-[#191919]/30"
                                >
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr
                                v-for="(role, index) in filteredRoles"
                                :key="role.id"
                                class="border-b border-[#191919]/5 transition hover:bg-[#F4A6B8]/[0.03]"
                            >

                                <!-- Number -->
                                <td
                                    class="px-6 py-5 text-sm text-[#191919]/35"
                                >
                                    {{ String(index + 1).padStart(2, '0') }}
                                </td>


                                <!-- Name -->
                                <td class="px-6 py-5">

                                    <div
                                        class="flex items-center gap-3"
                                    >

                                        <span
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#F4A6B8]/20 text-[10px] font-semibold uppercase text-[#E85D75]"
                                        >
                                            {{ role.name.charAt(0) }}
                                        </span>

                                        <span
                                            class="text-sm font-medium"
                                        >
                                            {{ role.name }}
                                        </span>

                                    </div>

                                </td>


                                <!-- Users -->
                                <td
                                    class="px-6 py-5 text-sm text-[#191919]/50"
                                >
                                    {{ role.users_count || 0 }} users
                                </td>


                                <!-- Actions -->
                                <td class="px-6 py-5">

                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >

                                        <!-- Edit -->
                                        <button
                                            type="button"
                                            @click="openEditModal(role)"
                                            class="rounded-full border border-[#191919]/10 px-4 py-2 text-[9px] uppercase tracking-[0.2em] text-[#191919]/50 transition hover:border-[#E85D75]/50 hover:text-[#E85D75]"
                                        >
                                            Edit
                                        </button>


                                        <!-- Delete -->
                                        <button
                                            v-if="role.name !== 'admin'"
                                            type="button"
                                            :disabled="deletingId === role.id"
                                            @click="deleteRole(role)"
                                            class="rounded-full border border-red-200 px-4 py-2 text-[9px] uppercase tracking-[0.2em] text-red-500 transition hover:border-red-400 hover:bg-red-50 disabled:opacity-50"
                                        >
                                            {{
                                                deletingId === role.id
                                                    ? '...'
                                                    : 'Delete'
                                            }}
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </section>


        <!-- =====================================================
             MODAL
        ====================================================== -->
        <Transition name="fade">

            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-[#191919]/40 p-6 backdrop-blur-sm"
                @click.self="closeModal"
            >

                <div
                    class="w-full max-w-md overflow-hidden border border-[#191919]/10 bg-white shadow-2xl"
                >

                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-[#191919]/10 px-7 py-6"
                    >

                        <div>

                            <p
                                class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/30"
                            >
                                {{
                                    editingRole
                                        ? 'Edit Role'
                                        : 'New Role'
                                }}
                            </p>

                            <h3
                                class="mt-1 text-xl font-medium tracking-[-0.04em]"
                            >
                                {{
                                    editingRole
                                        ? 'Update role'
                                        : 'Create role'
                                }}
                            </h3>

                        </div>

                        <button
                            type="button"
                            @click="closeModal"
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-[#191919]/10 text-lg text-[#191919]/40 transition hover:border-[#191919]/30 hover:text-[#191919]"
                            aria-label="Close modal"
                        >
                            ×
                        </button>

                    </div>


                    <!-- Body -->
                    <div class="px-7 py-7">

                        <form
                            @submit.prevent="saveRole"
                            class="space-y-6"
                        >

                            <!-- Name -->
                            <div>

                                <label
                                    class="mb-3 block text-[9px] font-semibold uppercase tracking-[0.3em] text-[#191919]/35"
                                >
                                    Role name
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="e.g. editor, manager"
                                    required
                                    class="w-full rounded-lg border border-[#191919]/10 bg-[#FFF8FA] px-5 py-3.5 text-sm outline-none transition focus:border-[#E85D75]/50"
                                />

                                <p
                                    v-if="getFieldError('name')"
                                    class="mt-2 text-xs text-red-500"
                                >
                                    {{ getFieldError('name') }}
                                </p>

                            </div>


                            <!-- Actions -->
                            <div
                                class="flex items-center justify-end gap-3 border-t border-[#191919]/10 pt-6"
                            >

                                <button
                                    type="button"
                                    @click="closeModal"
                                    :disabled="saving"
                                    class="rounded-full border border-[#191919]/10 px-6 py-3 text-[9px] uppercase tracking-[0.2em] text-[#191919]/50 transition hover:border-[#191919]/30 hover:text-[#191919] disabled:opacity-50"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    :disabled="saving"
                                    class="rounded-full bg-[#191919] px-7 py-3 text-[9px] font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-[#E85D75] disabled:opacity-50"
                                >
                                    {{
                                        saving
                                            ? 'Saving...'
                                            : editingRole
                                                ? 'Update'
                                                : 'Create'
                                    }}
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </Transition>

    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

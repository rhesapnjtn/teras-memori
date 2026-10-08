<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const router = useRouter()
const auth = useAuthStore()

const loading = ref(false)
const saving = ref(false)
const avatarUploading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    bio: '',
})

const fetchProfile = async () => {
    loading.value = true
    errorMessage.value = ''
    try {
        const response = await api.get('/profile')
        const user = response.data.user || {}
        form.value = {
            name: user.name || '',
            email: user.email || '',
            phone: user.phone || '',
            address: user.address || '',
            bio: user.bio || '',
        }
        if (user) {
            auth.user = user
            localStorage.setItem('auth_user', JSON.stringify(user))
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Gagal memuat profil'
    } finally {
        loading.value = false
    }
}

const saveProfile = async () => {
    saving.value = true
    errorMessage.value = ''
    successMessage.value = ''
    try {
        const response = await api.put('/profile', form.value)
        successMessage.value = response.data.message || 'Profil berhasil diperbarui'
        if (response.data.user) {
            auth.user = response.data.user
            localStorage.setItem('auth_user', JSON.stringify(response.data.user))
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Gagal menyimpan profil'
    } finally {
        saving.value = false
    }
}

const uploadAvatar = async (event) => {
    const file = event.target.files?.[0]
    if (!file) return
    const formData = new FormData()
    formData.append('avatar', file)
    avatarUploading.value = true
    errorMessage.value = ''
    successMessage.value = ''
    try {
        const response = await api.post('/profile/avatar', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        successMessage.value = response.data.message || 'Avatar berhasil diupload'
        if (response.data.user) {
            auth.user = response.data.user
            localStorage.setItem('auth_user', JSON.stringify(response.data.user))
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Gagal upload avatar'
    } finally {
        avatarUploading.value = false
        event.target.value = ''
    }
}

const getAvatarUrl = (avatar) => {
    if (!avatar) return null
    if (avatar.startsWith('http')) return avatar
    return `/storage/${avatar}`
}

onMounted(async () => {
    if (!auth.isAuthenticated) {
        router.push({ name: 'login' })
        return
    }
    await fetchProfile()
})
</script>

<template>
    <div class="min-h-screen bg-[#FFF8FA] py-12">
        <div class="mx-auto max-w-2xl px-6">
            <h1 class="text-2xl font-semibold tracking-wide">Profil Saya</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola informasi pribadi dan akun Anda</p>

            <div v-if="errorMessage" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMessage }}</div>
            <div v-if="successMessage" class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ successMessage }}</div>

            <div class="mt-6 rounded-xl border bg-white p-6">
                <div class="flex items-center gap-4">
                    <img v-if="getAvatarUrl(auth.user?.avatar)" :src="getAvatarUrl(auth.user?.avatar)" class="h-16 w-16 rounded-full object-cover" />
                    <div v-else class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-200 text-sm text-gray-500">AV</div>
                    <div>
                        <label class="cursor-pointer text-sm text-blue-600 hover:underline">
                            <span>{{ avatarUploading ? 'Uploading...' : 'Ubah Foto Profil' }}</span>
                            <input type="file" accept="image/*" class="hidden" @change="uploadAvatar" :disabled="avatarUploading" />
                        </label>
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG, WEBP max 2MB</p>
                    </div>
                </div>

                <form class="mt-6 space-y-4" @submit.prevent="saveProfile">
                    <div>
                        <label class="text-sm text-gray-700">Nama Lengkap</label>
                        <input v-model="form.name" type="text" class="mt-1 w-full rounded-lg border px-3 py-2" required />
                    </div>
                    <div>
                        <label class="text-sm text-gray-700">Email</label>
                        <input v-model="form.email" type="email" class="mt-1 w-full rounded-lg border px-3 py-2" required />
                    </div>
                    <div>
                        <label class="text-sm text-gray-700">No. Telepon</label>
                        <input v-model="form.phone" type="text" class="mt-1 w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div>
                        <label class="text-sm text-gray-700">Alamat</label>
                        <input v-model="form.address" type="text" class="mt-1 w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div>
                        <label class="text-sm text-gray-700">Bio</label>
                        <textarea v-model="form.bio" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2"></textarea>
                    </div>
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-white disabled:opacity-50" :disabled="saving">
                        {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
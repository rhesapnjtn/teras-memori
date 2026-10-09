<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const form = reactive({
    email: '',
    password: '',
})

const errorMessage = ref('')

const showPassword = ref(false)

const submit = async () => {
    errorMessage.value = ''

    try {
        const response = await auth.login(form)

        const roles = response.user?.roles?.map(
            (r) => r.name
        ) || []

        const isAdmin = roles.includes('admin') || roles.includes('superadmin')
        const redirect = route.query.redirect

        if (isAdmin) {
            router.push({ name: 'dashboard' })
        } else {
            router.push(redirect || { name: 'home' })
        }
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Email atau password tidak sesuai.'
    }
}
</script>

<template>
    <main class="min-h-screen overflow-hidden bg-[#FFF8FA] text-[#191919]">

        <!-- ========================================= -->
        <!-- BACKGROUND DECORATION -->
        <!-- ========================================= -->

        <div
            class="pointer-events-none fixed inset-0 opacity-[0.035]"
            style="
                background-image:
                    linear-gradient(rgba(25,25,25,.5) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(25,25,25,.5) 1px, transparent 1px);
                background-size: 80px 80px;
            "
        ></div>

        <div
            class="pointer-events-none fixed -right-48 -top-48 h-[600px] w-[600px] rounded-full border border-[#E85D75]/10"
        ></div>

        <div
            class="pointer-events-none fixed -right-20 top-20 h-[400px] w-[400px] rounded-full bg-[#F4A6B8]/20 blur-[120px]"
        ></div>


        <!-- ========================================= -->
        <!-- TOP BAR -->
        <!-- ========================================= -->

        <header
            class="
                relative z-10 flex h-24 items-center justify-between
                border-b border-[#191919]/10 px-6
                sm:px-10 lg:px-14
            "
        >

            <RouterLink
                :to="{ name: 'home' }"
                class="group flex items-center gap-4"
            >

                <div
                    class="
                        relative flex h-10 w-10 items-center justify-center
                        rounded-full border border-[#E85D75]/30
                        transition duration-500
                        group-hover:rotate-12 group-hover:border-[#E85D75]
                    "
                >
                    <div class="h-4 w-4 rounded-full bg-[#E85D75]"></div>

                    <span
                        class="
                            absolute -right-1 -top-1 h-2 w-2
                            rounded-full bg-[#191919]
                        "
                    ></span>
                </div>

                <div class="leading-none">
                    <div class="text-[17px] font-semibold tracking-[-0.04em]">
                        TERAS
                    </div>

                    <div
                        class="
                            mt-1 text-[9px] uppercase tracking-[0.35em]
                            text-[#191919]/40
                        "
                    >
                        MEMORI
                    </div>
                </div>

            </RouterLink>


            <RouterLink
                :to="{ name: 'home' }"
                class="
                    flex items-center gap-3 text-[9px] uppercase
                    tracking-[0.25em] text-[#191919]/35
                    transition duration-300 hover:text-[#E85D75]
                "
            >
                <span>Back to website</span>
                <span>↗</span>
            </RouterLink>

        </header>


        <!-- ========================================= -->
        <!-- LOGIN CONTENT -->
        <!-- ========================================= -->

        <section
            class="
                relative z-10 flex min-h-[calc(100vh-96px)]
                items-center px-6 py-16
                sm:px-10 lg:px-14
            "
        >

            <div
                class="
                    mx-auto grid w-full max-w-[1250px]
                    overflow-hidden border border-[#191919]/10
                    bg-white/70 backdrop-blur-xl
                    lg:grid-cols-[1.05fr_0.95fr]
                "
            >

                <!-- ================================= -->
                <!-- LEFT / BRANDING -->
                <!-- ================================= -->

                <div
                    class="
                        relative hidden overflow-hidden
                        bg-[#191919] p-10 text-white
                        lg:flex lg:min-h-[650px] lg:flex-col
                        lg:justify-between lg:p-14
                    "
                >

                    <!-- Decorative circles -->
                    <div
                        class="
                            pointer-events-none absolute -right-32 -top-32
                            h-[500px] w-[500px] rounded-full
                            border border-white/10
                        "
                    ></div>

                    <div
                        class="
                            pointer-events-none absolute -bottom-48 -left-48
                            h-[600px] w-[600px] rounded-full
                            border border-[#E85D75]/20
                        "
                    ></div>

                    <div
                        class="
                            pointer-events-none absolute right-20 top-32
                            h-40 w-40 rounded-full
                            bg-[#E85D75]/10 blur-3xl
                        "
                    ></div>


                    <!-- Top -->
                    <div class="relative">

                        <div
                            class="
                                flex items-center gap-3 text-[9px]
                                uppercase tracking-[0.35em]
                                text-white/35
                            "
                        >
                            <span class="h-px w-8 bg-[#E85D75]"></span>
                            Studio / 06
                        </div>

                        <div class="mt-20">

                            <div
                                class="
                                    mb-5 text-[10px] uppercase
                                    tracking-[0.35em] text-[#F4A6B8]
                                "
                            >
                                Welcome back
                            </div>

                            <h1
                                class="
                                    max-w-xl text-7xl font-medium
                                    leading-[0.82] tracking-[-0.07em]
                                    xl:text-8xl
                                "
                            >
                                BACK<br />
                                <span class="text-white/15">TO</span><br />
                                WORK.
                            </h1>

                        </div>

                    </div>


                    <!-- Bottom -->
                    <div class="relative">

                        <div class="mb-8 h-px w-16 bg-[#E85D75]"></div>

                        <p
                            class="
                                max-w-md text-sm leading-7 text-white/40
                            "
                        >
                            The creative workspace behind Teras Memori.
                            Manage projects, services, portfolios and
                            everything that keeps the studio moving.
                        </p>

                        <div
                            class="
                                mt-8 flex items-center justify-between
                                border-t border-white/10 pt-5
                            "
                        >
                            <span
                                class="
                                    text-[9px] uppercase
                                    tracking-[0.3em] text-white/25
                                "
                            >
                                Your photo / Our craft
                            </span>

                            <span
                                class="
                                    text-[9px] uppercase
                                    tracking-[0.25em] text-[#F4A6B8]
                                "
                            >
                                TM / ADMIN
                            </span>
                        </div>

                    </div>

                </div>


                <!-- ================================= -->
                <!-- RIGHT / FORM -->
                <!-- ================================= -->

                <div
                    class="
                        flex min-h-[650px] items-center
                        bg-[#FFF8FA] p-7
                        sm:p-12 lg:p-16
                    "
                >

                    <div class="mx-auto w-full max-w-[420px]">

                        <!-- Heading -->
                        <div class="mb-12">

                            <div
                                class="
                                    mb-5 flex items-center gap-3
                                    text-[9px] uppercase tracking-[0.35em]
                                    text-[#191919]/30
                                "
                            >
                                <span class="h-px w-8 bg-[#E85D75]"></span>
                                Admin access
                            </div>

                            <h2
                                class="
                                    text-5xl font-medium leading-none
                                    tracking-[-0.06em]
                                    sm:text-6xl
                                "
                            >
                                SIGN<br />
                                <span class="text-[#E85D75]">IN.</span>
                            </h2>

                            <p
                                class="
                                    mt-6 max-w-sm text-sm leading-6
                                    text-[#191919]/40
                                "
                            >
                                Access your Teras Memori studio workspace.
                            </p>

                        </div>


                        <!-- Error -->
                        <Transition name="error">

                            <div
                                v-if="errorMessage"
                                class="
                                    mb-6 border-l-2 border-red-400
                                    bg-red-50 px-5 py-4
                                "
                            >
                                <p
                                    class="
                                        text-xs leading-5 text-red-600
                                    "
                                >
                                    {{ errorMessage }}
                                </p>
                            </div>

                        </Transition>


                        <!-- Form -->
                        <form
                            @submit.prevent="submit"
                            class="space-y-7"
                        >

                            <!-- Email -->
                            <div>

                                <label
                                    for="email"
                                    class="
                                        mb-3 block text-[9px] font-semibold
                                        uppercase tracking-[0.3em]
                                        text-[#191919]/35
                                    "
                                >
                                    Email address
                                </label>

                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="admin@teras-memori.test"
                                    required
                                    class="
                                        w-full border-0 border-b
                                        border-[#191919]/15
                                        bg-transparent px-0 py-4
                                        text-sm outline-none
                                        placeholder:text-[#191919]/20
                                        transition duration-300
                                        focus:border-[#E85D75]
                                        focus:ring-0
                                    "
                                />

                            </div>


                            <!-- Password -->
                            <div>

                                <label
                                    for="password"
                                    class="
                                        mb-3 block text-[9px] font-semibold
                                        uppercase tracking-[0.3em]
                                        text-[#191919]/35
                                    "
                                >
                                    Password
                                </label>

                                <div class="relative">

                                    <input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        required
                                        class="
                                            w-full border-0 border-b
                                            border-[#191919]/15
                                            bg-transparent py-4 pl-0 pr-10
                                            text-sm outline-none
                                            placeholder:text-[#191919]/20
                                            transition duration-300
                                            focus:border-[#E85D75]
                                            focus:ring-0
                                        "
                                    />

                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                        :aria-pressed="showPassword"
                                        class="
                                            absolute inset-y-0 right-0 flex
                                            items-center pr-1
                                            text-[#191919]/30
                                            transition duration-300
                                            hover:text-[#E85D75]
                                            focus:outline-none
                                            focus-visible:text-[#E85D75]
                                        "
                                    >

                                        <svg
                                            v-if="!showPassword"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="h-5 w-5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                            />
                                        </svg>

                                        <svg
                                            v-else
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="h-5 w-5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"
                                            />
                                        </svg>

                                    </button>

                                </div>

                            </div>


                            <!-- Submit -->
                            <button
                                type="submit"
                                :disabled="auth.loading"
                                class="
                                    group mt-5 flex w-full
                                    items-center justify-between
                                    bg-[#191919] px-6 py-5
                                    text-[10px] font-semibold uppercase
                                    tracking-[0.25em] text-white
                                    transition duration-500
                                    hover:-translate-y-1
                                    hover:bg-[#E85D75]
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50
                                    disabled:hover:translate-y-0
                                "
                            >

                                <span>
                                    {{ auth.loading ? 'Signing in...' : 'Enter studio' }}
                                </span>

                                <span
                                    class="
                                        text-lg font-normal
                                        transition duration-300
                                        group-hover:translate-x-2
                                    "
                                >
                                    →
                                </span>

                            </button>

                        </form>


                        <!-- Footer note -->
                        <div
                            class="
                                mt-12 border-t border-[#191919]/10
                                pt-6
                            "
                        >

                            <p
                                class="
                                    text-center text-[10px]
                                    text-[#191919]/40
                                "
                            >
                                Belum punya akun?

                                <RouterLink
                                    :to="{ name: 'register' }"
                                    class="
                                        font-semibold text-[#E85D75]
                                        transition hover:underline
                                    "
                                >
                                    Daftar sekarang
                                </RouterLink>

                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- ========================================= -->
        <!-- FOOTER -->
        <!-- ========================================= -->

        <footer
            class="
                relative z-10 border-t border-[#191919]/10
                px-6 py-6 sm:px-10 lg:px-14
            "
        >

            <div
                class="
                    mx-auto flex max-w-[1250px]
                    flex-col gap-3 text-[9px] uppercase
                    tracking-[0.25em] text-[#191919]/25
                    sm:flex-row sm:items-center sm:justify-between
                "
            >
                <span>Teras Memori</span>

                <span>
                    Visual studio & photo editing
                </span>

                <span>
                    © {{ new Date().getFullYear() }}
                </span>
            </div>

        </footer>

    </main>
</template>


<style scoped>
.error-enter-active,
.error-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.error-enter-from,
.error-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>

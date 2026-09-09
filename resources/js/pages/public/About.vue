<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
    <div class="min-h-screen overflow-hidden bg-[#FFF8FA] text-[#191919]">

        <!-- =====================================================
             HERO
        ====================================================== -->
        <section class="relative overflow-hidden border-b border-[#191919]/10">

            <!-- Editorial grid -->
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.035]"
                style="
                    background-image:
                        linear-gradient(rgba(25,25,25,.5) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(25,25,25,.5) 1px, transparent 1px);
                    background-size: 80px 80px;
                "
            ></div>

            <!-- Decorative geometry -->
            <div
                class="pointer-events-none absolute -right-32 -top-32 h-[500px] w-[500px] rounded-full border border-[#E85D75]/10"
            ></div>

            <div
                class="pointer-events-none absolute -right-10 top-24 h-[300px] w-[300px] rounded-full bg-[#F4A6B8]/15 blur-[100px]"
            ></div>

            <div
                class="pointer-events-none absolute -left-40 bottom-0 h-[400px] w-[400px] rounded-full border border-[#191919]/[0.04]"
            ></div>

            <div
                class="relative mx-auto max-w-[1600px] px-6 pb-24 pt-20 sm:px-10 md:pb-32 md:pt-28 lg:px-16"
            >

                <!-- Label -->
                <div class="mb-10 flex items-center gap-4">
                    <span class="h-px w-12 bg-[#E85D75]"></span>

                    <span
                        class="text-[10px] uppercase tracking-[0.4em] text-[#191919]/40"
                    >
                        04 — About The Studio
                    </span>
                </div>


                <!-- Heading -->
                <div
                    class="grid gap-14 lg:grid-cols-[1fr_250px] lg:items-end"
                >

                    <div>
                        <h1
                            class="max-w-6xl text-[17vw] font-medium leading-[0.75] tracking-[-0.085em] sm:text-[13vw] lg:text-[10vw]"
                        >
                            WE ARE
                            <span class="text-[#191919]/15">
                                TERAS.
                            </span>
                        </h1>
                    </div>


                    <!-- Studio mark -->
                    <div class="hidden lg:block">
                        <div class="relative mx-auto h-36 w-36">

                            <div
                                class="absolute inset-0 rounded-full border border-[#191919]/10"
                            ></div>

                            <div
                                class="absolute inset-4 rounded-full border border-[#E85D75]/25"
                            ></div>

                            <div
                                class="absolute inset-8 rounded-full bg-[#E85D75]/10"
                            ></div>

                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <span
                                    class="text-[9px] uppercase tracking-[0.3em] text-[#191919]/40"
                                >
                                    TM / 04
                                </span>
                            </div>

                        </div>
                    </div>

                </div>


                <!-- Intro -->
                <div
                    class="mt-16 grid gap-8 border-t border-[#191919]/10 pt-8 md:grid-cols-[1fr_300px]"
                >

                    <p
                        class="max-w-3xl text-lg leading-8 text-[#191919]/55 md:text-2xl md:leading-10"
                    >
                        Teras Memori is a visual studio built around one
                        simple idea:
                        <span class="text-[#191919]/80">
                            photographs are pieces of memory.
                        </span>
                    </p>

                    <div
                        class="text-xs leading-6 text-[#191919]/30 md:text-right"
                    >
                        Independent visual studio
                        <br />
                        Photo editing & restoration
                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             WHO WE ARE
        ====================================================== -->
        <section class="border-b border-[#191919]/10">

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div class="grid gap-16 lg:grid-cols-[0.4fr_1fr]">

                    <!-- Label -->
                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            Who we are
                        </span>

                        <div
                            class="mt-8 hidden h-px w-20 bg-[#E85D75] lg:block"
                        ></div>

                    </div>


                    <!-- Content -->
                    <div>

                        <h2
                            class="max-w-6xl text-4xl font-medium leading-[1.03] tracking-[-0.055em] text-[#191919]/80 sm:text-5xl md:text-6xl lg:text-7xl"
                        >
                            A small studio with a
                            <span class="text-[#E85D75]">
                                deep respect
                            </span>
                            for photographs.
                        </h2>


                        <div
                            class="mt-12 grid gap-10 md:grid-cols-2"
                        >

                            <p
                                class="text-sm leading-7 text-[#191919]/45 md:text-base"
                            >
                                Teras Memori was created with a simple
                                purpose — helping people take better care
                                of the photographs that matter to them.
                            </p>

                            <p
                                class="text-sm leading-7 text-[#191919]/45 md:text-base"
                            >
                                We combine digital tools, technical
                                precision and a human eye to transform
                                ordinary images into photographs that feel
                                worth keeping.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             THE IDEA
        ====================================================== -->
        <section class="border-b border-[#191919]/10">

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div class="grid gap-16 lg:grid-cols-2 lg:items-center">

                    <!-- Statement -->
                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            The idea
                        </span>

                        <h2
                            class="mt-8 max-w-2xl text-5xl font-medium leading-[0.92] tracking-[-0.06em] sm:text-6xl md:text-7xl"
                        >
                            We don't just edit
                            <span class="text-[#191919]/20">
                                photographs.
                            </span>

                            <br />

                            We help preserve
                            <span class="text-[#E85D75]">
                                moments.
                            </span>
                        </h2>

                    </div>


                    <!-- Explanation -->
                    <div class="relative">

                        <div
                            class="absolute -left-6 -top-6 h-24 w-24 rounded-full border border-[#E85D75]/20"
                        ></div>

                        <div
                            class="relative border-l border-[#191919]/10 pl-8 md:pl-12"
                        >

                            <p
                                class="text-xl leading-9 text-[#191919]/60 md:text-2xl md:leading-10"
                            >
                                A photograph can hold a person, a place,
                                a feeling, or an entire chapter of life.
                            </p>

                            <p
                                class="mt-8 max-w-lg text-sm leading-7 text-[#191919]/35 md:text-base"
                            >
                                That's why every image deserves more than
                                a quick adjustment. It deserves attention,
                                patience and an understanding of what makes
                                the original photograph meaningful.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             OUR PHILOSOPHY
        ====================================================== -->
        <section class="border-b border-[#191919]/10">

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div class="mb-16">

                    <span
                        class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                    >
                        Our philosophy
                    </span>

                    <h2
                        class="mt-5 max-w-3xl text-4xl font-medium leading-[0.95] tracking-[-0.055em] sm:text-5xl md:text-6xl"
                    >
                        Less noise.
                        <span class="text-[#E85D75]">
                            More feeling.
                        </span>
                    </h2>

                </div>


                <!-- Principles -->
                <div
                    class="grid gap-px overflow-hidden border border-[#191919]/10 bg-[#191919]/10 md:grid-cols-3"
                >

                    <!-- Principle -->
                    <div
                        class="group bg-[#FFF8FA] p-8 transition duration-500 hover:bg-white md:p-10"
                    >

                        <span
                            class="text-[10px] tracking-[0.25em] text-[#E85D75]"
                        >
                            01
                        </span>

                        <h3
                            class="mt-16 text-2xl font-medium tracking-[-0.03em]"
                        >
                            Natural
                        </h3>

                        <p
                            class="mt-4 text-sm leading-7 text-[#191919]/35"
                        >
                            Editing should enhance the photograph,
                            not make it feel artificial.
                        </p>

                    </div>


                    <!-- Principle -->
                    <div
                        class="group bg-[#FFF8FA] p-8 transition duration-500 hover:bg-white md:p-10"
                    >

                        <span
                            class="text-[10px] tracking-[0.25em] text-[#E85D75]"
                        >
                            02
                        </span>

                        <h3
                            class="mt-16 text-2xl font-medium tracking-[-0.03em]"
                        >
                            Precise
                        </h3>

                        <p
                            class="mt-4 text-sm leading-7 text-[#191919]/35"
                        >
                            Every color, detail and adjustment has
                            a purpose.
                        </p>

                    </div>


                    <!-- Principle -->
                    <div
                        class="group bg-[#FFF8FA] p-8 transition duration-500 hover:bg-white md:p-10"
                    >

                        <span
                            class="text-[10px] tracking-[0.25em] text-[#E85D75]"
                        >
                            03
                        </span>

                        <h3
                            class="mt-16 text-2xl font-medium tracking-[-0.03em]"
                        >
                            Meaningful
                        </h3>

                        <p
                            class="mt-4 text-sm leading-7 text-[#191919]/35"
                        >
                            The final image should still feel like
                            the memory you started with.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             WHAT WE DO
        ====================================================== -->
        <section class="border-b border-[#191919]/10">

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div
                    class="mb-16 flex items-end justify-between gap-8"
                >

                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            What we do
                        </span>

                        <h2
                            class="mt-5 text-4xl font-medium tracking-[-0.05em] sm:text-5xl md:text-6xl"
                        >
                            Our practice.
                        </h2>

                    </div>

                    <span
                        class="hidden text-[9px] uppercase tracking-[0.3em] text-[#191919]/25 md:block"
                    >
                        Teras Memori Studio
                    </span>

                </div>


                <div
                    class="divide-y divide-[#191919]/10 border-y border-[#191919]/10"
                >

                    <!-- Photo Editing -->
                    <div
                        class="group grid gap-6 py-8 transition duration-500 md:grid-cols-[100px_1fr_auto] md:items-center"
                    >

                        <span
                            class="text-[10px] tracking-[0.25em] text-[#E85D75]"
                        >
                            01
                        </span>

                        <div>

                            <h3
                                class="text-2xl font-medium tracking-[-0.03em] transition duration-300 group-hover:text-[#E85D75] md:text-3xl"
                            >
                                Photo Editing
                            </h3>

                            <p
                                class="mt-2 max-w-xl text-sm leading-6 text-[#191919]/35"
                            >
                                Color, exposure and visual refinement
                                to make every image feel balanced.
                            </p>

                        </div>

                        <span
                            class="hidden text-2xl text-[#191919]/20 transition duration-300 group-hover:translate-x-2 group-hover:text-[#E85D75] md:block"
                        >
                            →
                        </span>

                    </div>


                    <!-- Retouching -->
                    <div
                        class="group grid gap-6 py-8 transition duration-500 md:grid-cols-[100px_1fr_auto] md:items-center"
                    >

                        <span
                            class="text-[10px] tracking-[0.25em] text-[#E85D75]"
                        >
                            02
                        </span>

                        <div>

                            <h3
                                class="text-2xl font-medium tracking-[-0.03em] transition duration-300 group-hover:text-[#E85D75] md:text-3xl"
                            >
                                Photo Retouching
                            </h3>

                            <p
                                class="mt-2 max-w-xl text-sm leading-6 text-[#191919]/35"
                            >
                                Detailed retouching that enhances the
                                image without removing its personality.
                            </p>

                        </div>

                        <span
                            class="hidden text-2xl text-[#191919]/20 transition duration-300 group-hover:translate-x-2 group-hover:text-[#E85D75] md:block"
                        >
                            →
                        </span>

                    </div>


                    <!-- Restoration -->
                    <div
                        class="group grid gap-6 py-8 transition duration-500 md:grid-cols-[100px_1fr_auto] md:items-center"
                    >

                        <span
                            class="text-[10px] tracking-[0.25em] text-[#E85D75]"
                        >
                            03
                        </span>

                        <div>

                            <h3
                                class="text-2xl font-medium tracking-[-0.03em] transition duration-300 group-hover:text-[#E85D75] md:text-3xl"
                            >
                                Photo Restoration
                            </h3>

                            <p
                                class="mt-2 max-w-xl text-sm leading-6 text-[#191919]/35"
                            >
                                Bringing damaged, faded and old photographs
                                back to life.
                            </p>

                        </div>

                        <span
                            class="hidden text-2xl text-[#191919]/20 transition duration-300 group-hover:translate-x-2 group-hover:text-[#E85D75] md:block"
                        >
                            →
                        </span>

                    </div>


                    <!-- Background Removal -->
                    <div
                        class="group grid gap-6 py-8 transition duration-500 md:grid-cols-[100px_1fr_auto] md:items-center"
                    >

                        <span
                            class="text-[10px] tracking-[0.25em] text-[#E85D75]"
                        >
                            04
                        </span>

                        <div>

                            <h3
                                class="text-2xl font-medium tracking-[-0.03em] transition duration-300 group-hover:text-[#E85D75] md:text-3xl"
                            >
                                Background Removal
                            </h3>

                            <p
                                class="mt-2 max-w-xl text-sm leading-6 text-[#191919]/35"
                            >
                                Clean and precise subject isolation for
                                products, portraits and creative work.
                            </p>

                        </div>

                        <span
                            class="hidden text-2xl text-[#191919]/20 transition duration-300 group-hover:translate-x-2 group-hover:text-[#E85D75] md:block"
                        >
                            →
                        </span>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             THE PROCESS
        ====================================================== -->
        <section class="border-b border-[#191919]/10">

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div class="grid gap-16 lg:grid-cols-[0.35fr_1fr]">

                    <div>

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-[#191919]/30"
                        >
                            The process
                        </span>

                        <p
                            class="mt-6 max-w-xs text-sm leading-7 text-[#191919]/35"
                        >
                            Simple for you.
                            <br />
                            Precise for us.
                        </p>

                    </div>


                    <div
                        class="grid gap-px overflow-hidden border border-[#191919]/10 bg-[#191919]/10 sm:grid-cols-3"
                    >

                        <!-- Step 01 -->
                        <div class="bg-[#FFF8FA] p-8 md:p-10">

                            <span
                                class="text-[10px] tracking-[0.25em] text-[#E85D75]"
                            >
                                01
                            </span>

                            <h3
                                class="mt-10 text-xl font-medium tracking-[-0.03em]"
                            >
                                Tell us
                            </h3>

                            <p
                                class="mt-4 text-sm leading-6 text-[#191919]/35"
                            >
                                Share your image and tell us what
                                you want to achieve.
                            </p>

                        </div>


                        <!-- Step 02 -->
                        <div class="bg-[#FFF8FA] p-8 md:p-10">

                            <span
                                class="text-[10px] tracking-[0.25em] text-[#E85D75]"
                            >
                                02
                            </span>

                            <h3
                                class="mt-10 text-xl font-medium tracking-[-0.03em]"
                            >
                                We create
                            </h3>

                            <p
                                class="mt-4 text-sm leading-6 text-[#191919]/35"
                            >
                                Our editing process turns your direction
                                into a refined visual result.
                            </p>

                        </div>


                        <!-- Step 03 -->
                        <div class="bg-[#FFF8FA] p-8 md:p-10">

                            <span
                                class="text-[10px] tracking-[0.25em] text-[#E85D75]"
                            >
                                03
                            </span>

                            <h3
                                class="mt-10 text-xl font-medium tracking-[-0.03em]"
                            >
                                You receive
                            </h3>

                            <p
                                class="mt-4 text-sm leading-6 text-[#191919]/35"
                            >
                                A finished image ready to be used,
                                shared or remembered.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CREATIVE STATEMENT
        ====================================================== -->
        <section class="border-b border-[#191919]/10">

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div
                    class="relative overflow-hidden rounded-[2rem] bg-[#191919]"
                >

                    <!-- Decorative circles -->
                    <div
                        class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full border border-[#E85D75]/30"
                    ></div>

                    <div
                        class="pointer-events-none absolute -right-10 top-10 h-64 w-64 rounded-full border border-white/10"
                    ></div>

                    <div
                        class="pointer-events-none absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-[#E85D75]/10 blur-[80px]"
                    ></div>


                    <div
                        class="relative px-6 py-20 sm:px-10 md:px-16 md:py-28"
                    >

                        <span
                            class="text-[10px] uppercase tracking-[0.35em] text-white/30"
                        >
                            A note from the studio
                        </span>


                        <p
                            class="mt-8 max-w-6xl text-4xl font-medium leading-[0.95] tracking-[-0.055em] text-white sm:text-5xl md:text-7xl"
                        >
                            Every photograph has a story.

                            <br />

                            Our job is to make sure
                            <span class="text-[#E85D75]">
                                it stays alive.
                            </span>
                        </p>


                        <div
                            class="mt-12 flex flex-col gap-6 border-t border-white/10 pt-8 sm:flex-row sm:items-end sm:justify-between"
                        >

                            <p
                                class="max-w-md text-sm leading-7 text-white/30"
                            >
                                Teras Memori exists somewhere between
                                technology and art — where digital
                                precision meets human memory.
                            </p>

                            <span
                                class="text-[9px] uppercase tracking-[0.3em] text-white/20"
                            >
                                Teras Memori / 2026
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CTA
        ====================================================== -->
        <section>

            <div
                class="mx-auto max-w-[1600px] px-6 py-24 sm:px-10 md:py-36 lg:px-16"
            >

                <div
                    class="group relative overflow-hidden rounded-[2rem] bg-[#E85D75]"
                >

                    <!-- Decorative circles -->
                    <div
                        class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full border border-white/20 transition-transform duration-1000 group-hover:scale-110"
                    ></div>

                    <div
                        class="pointer-events-none absolute -right-8 top-8 h-60 w-60 rounded-full border border-white/15 transition-transform duration-1000 group-hover:scale-125"
                    ></div>

                    <div
                        class="pointer-events-none absolute -bottom-20 -left-20 h-60 w-60 rounded-full bg-white/10 blur-[70px]"
                    ></div>


                    <div
                        class="relative px-6 py-24 text-center text-white sm:px-10 md:py-32"
                    >

                        <span
                            class="text-[10px] uppercase tracking-[0.4em] text-white/60"
                        >
                            Ready to create?
                        </span>


                        <h2
                            class="mx-auto mt-6 max-w-5xl text-5xl font-medium leading-[0.88] tracking-[-0.065em] sm:text-6xl md:text-8xl"
                        >
                            Your photo.
                            <br />
                            Our
                            <span class="text-white/45">
                                canvas.
                            </span>
                        </h2>


                        <p
                            class="mx-auto mt-8 max-w-md text-sm leading-6 text-white/65"
                        >
                            Bring us an image, an idea or a memory.
                            We'll take care of the rest.
                        </p>


                        <RouterLink
                            :to="{ name: 'order' }"
                            class="group/button mt-10 inline-flex items-center gap-6 rounded-full bg-white px-8 py-5 text-xs font-semibold uppercase tracking-[0.2em] text-[#191919] transition duration-500 hover:scale-105"
                        >

                            Start a project

                            <span
                                class="transition-transform duration-500 group-hover/button:translate-x-2"
                            >
                                →
                            </span>

                        </RouterLink>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FOOTER NOTE
        ====================================================== -->
        <div
            class="mx-auto flex max-w-[1600px] flex-col justify-between gap-4 border-t border-[#191919]/10 px-6 py-8 text-[9px] uppercase tracking-[0.3em] text-[#191919]/25 sm:flex-row sm:px-10 lg:px-16"
        >

            <span>
                Teras Memori
            </span>

            <span>
                About The Studio
            </span>

            <span>
                © {{ new Date().getFullYear() }}
            </span>

        </div>

    </div>
</template>
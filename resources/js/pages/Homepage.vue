<template>
    <Head title="HOME" />
    <MainLayout>
        <section class="mx-auto mt-8 mb-8 max-w-4xl text-center">
            <h1 class="mb-2 text-4xl font-extrabold text-yellow-700">Welcome to BuildPro Construction</h1>
            <p class="mb-6 text-lg text-gray-700">Track your ongoing projects and schedules with our dynamic calendar below.</p>
        </section>
        <section class="mx-auto mb-12 max-w-4xl">
            <ProjectCalendar />
        </section>
    </MainLayout>
</template>

<script setup>
import ProjectCalendar from '@/components/ProjectCalendar.vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

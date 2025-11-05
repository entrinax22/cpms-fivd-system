<template>
    <Head title="My Projects" />

    <MainLayout>
        <!-- Header Section -->
        <section class="mx-auto mt-8 mb-10 max-w-6xl text-center">
            <h1 class="mb-2 text-4xl font-extrabold text-yellow-700">My Projects</h1>
            <p class="text-lg text-gray-600">Easily monitor your projects’ progress and timelines below.</p>
        </section>

        <!-- Projects List -->
        <section class="mx-auto max-w-6xl space-y-6">
            <div
                v-for="project in projects"
                :key="project.id"
                class="group relative flex flex-col items-start justify-between rounded-2xl border border-gray-100 bg-white p-6 shadow-md transition-all hover:-translate-y-1 hover:shadow-xl sm:flex-row sm:items-center"
            >
                <!-- Left: Project Info -->
                <div class="w-full sm:w-3/4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-gray-800 transition group-hover:text-yellow-700">
                            {{ project.name }}
                        </h3>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold whitespace-nowrap"
                            :class="{
                                'bg-green-100 text-green-700': project.status === 'Completed',
                                'bg-blue-100 text-blue-700': project.status === 'In_progress',
                                'bg-yellow-100 text-yellow-700': project.status === 'Planning',
                                'bg-gray-100 text-gray-700': ['On_hold', 'Cancelled'].includes(project.status),
                            }"
                        >
                            {{ project.status }}
                        </span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Progress</span>
                            <span>{{ getProgress(project.status) }}%</span>
                        </div>
                        <div class="mt-1 h-2 rounded-full bg-gray-200">
                            <div
                                class="h-2 rounded-full transition-all duration-500"
                                :class="progressColor(project.status)"
                                :style="{ width: getProgress(project.status) + '%' }"
                            ></div>
                        </div>
                    </div>

                    <!-- Project Details -->
                    <div class="text-sm text-gray-600">
                        <p><strong>Start:</strong> {{ project.start_date }}</p>
                        <p><strong>End:</strong> {{ project.end_date }}</p>
                        <p><strong>Manager:</strong> {{ project.manager }}</p>
                    </div>
                </div>

                <!-- Right: View Button -->
                <div class="mt-5 text-right sm:mt-0 sm:w-1/4">
                    <button
                        @click="viewProject(project.id)"
                        class="w-full rounded-lg bg-yellow-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-yellow-700 sm:w-auto"
                    >
                        View Details
                    </button>
                </div>

                <!-- Decorative Background Circle -->
                <div
                    class="absolute -right-6 -bottom-6 h-24 w-24 rounded-full bg-yellow-100 opacity-30 transition-transform group-hover:scale-110"
                ></div>
            </div>
        </section>

        <!-- Empty State -->
        <section v-if="!projects.length" class="mx-auto mt-16 max-w-3xl text-center text-gray-500">
            <h3 class="text-lg font-semibold">No projects found</h3>
            <p class="text-sm">You don’t have any active projects right now. Check back later!</p>
        </section>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

const projects = ref([]);

// Fetch projects
const fetchProjects = async () => {
    try {
        const response = await axios.get(route('projects.projectListManager'));
        projects.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching projects:', error);
    }
};

onMounted(fetchProjects);

// Redirect to project details
const viewProject = (id) => {
    window.location.href = `/projects/viewProjectDetails/${id}`;
};

// Progress percentage based on project status
const getProgress = (status) => {
    switch (status) {
        case 'Planning':
            return 10;
        case 'In_progress':
            return 50;
        case 'On_hold':
            return 30;
        case 'Completed':
            return 100;
        case 'Cancelled':
            return 0;
        default:
            return 0;
    }
};

// Color of progress bar based on status
const progressColor = (status) => {
    switch (status) {
        case 'Completed':
            return 'bg-green-500';
        case 'In_progress':
            return 'bg-blue-500';
        case 'Planning':
            return 'bg-yellow-500';
        case 'On_hold':
            return 'bg-gray-400';
        case 'Cancelled':
            return 'bg-red-500';
        default:
            return 'bg-gray-300';
    }
};
</script>

<template>
    <Head title="Project Details" />

    <MainLayout>
        <!-- Header -->
        <section class="mx-auto mt-8 mb-10 max-w-6xl text-center">
            <h1 class="mb-2 text-4xl font-extrabold text-yellow-700">Project Details</h1>
            <p class="text-lg text-gray-600">View key information about your selected project below.</p>
        </section>

        <!-- Project Details Card -->
        <section class="mx-auto max-w-4xl rounded-2xl border border-gray-100 bg-white p-8 shadow-md">
            <!-- Title + Status -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-2xl font-bold text-gray-800">{{ data.project_name }}</h2>
                <span
                    class="mt-2 rounded-full px-4 py-1 text-sm font-semibold sm:mt-0"
                    :class="{
                        'bg-green-100 text-green-700': data.status === 'Completed',
                        'bg-blue-100 text-blue-700': data.status === 'In Progress',
                        'bg-yellow-100 text-yellow-700': data.status === 'Planning',
                        'bg-gray-100 text-gray-700': ['On Hold', 'Cancelled'].includes(data.status),
                    }"
                >
                    {{ data.status }}
                </span>
            </div>

            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="mb-1 flex justify-between text-xs text-gray-500">
                    <span>Progress</span>
                    <span>{{ getProgress(data.status) }}%</span>
                </div>
                <div class="h-2 rounded-full bg-gray-200">
                    <div
                        class="h-2 rounded-full transition-all duration-500"
                        :class="progressColor(data.status)"
                        :style="{ width: getProgress(data.status) + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Project Info -->
            <div class="grid grid-cols-1 gap-4 text-sm text-gray-700 sm:grid-cols-2">
                <div>
                    <p class="font-semibold text-gray-800">Client</p>
                    <p>{{ data.client_name || 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Project Manager</p>
                    <p>{{ data.manager_name || 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Start Date</p>
                    <p>{{ formatDate(data.start_date) }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Estimated End Date</p>
                    <p>{{ formatDate(data.estimated_end_date) }}</p>
                </div>
            </div>

            <!-- Divider -->
            <hr class="my-6 border-gray-200" />

            <!-- Description -->
            <div>
                <h3 class="mb-2 text-lg font-semibold text-gray-800">Description</h3>
                <p class="leading-relaxed whitespace-pre-line text-gray-600">
                    {{ data.project_description || 'No description provided for this project.' }}
                </p>
            </div>

            <!-- Project Progress Timeline -->
            <div class="mt-10">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Project Progress Timeline</h3>
                    <button
                        @click="showAddModal = true"
                        class="rounded-lg bg-yellow-600 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-700"
                    >
                        + Add Progress
                    </button>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="py-6 text-center text-gray-500">Loading progress...</div>

                <!-- Empty -->
                <div v-else-if="!progressList.length" class="py-6 text-center text-gray-500">No progress updates yet.</div>

                <!-- Timeline -->
                <div v-else class="relative space-y-8 border-l border-yellow-400 pl-6">
                    <div v-for="(progress, index) in progressList" :key="index" class="relative">
                        <!-- Circle -->
                        <div class="absolute top-1 -left-3 h-6 w-6 rounded-full border-4 border-yellow-400 bg-white"></div>

                        <div class="rounded-lg bg-yellow-50 p-4 shadow-sm">
                            <div class="mb-2 flex items-center justify-between">
                                <h4 class="font-semibold text-yellow-800">Update on {{ formatDate(progress.progress_date) }}</h4>
                            </div>
                            <p class="mb-3 text-sm text-gray-700">
                                {{ progress.progress_description }}
                            </p>

                            <!-- Attachments -->
                            <div class="space-y-2">
                                <div v-if="progress.image_path">
                                    <img :src="progress.image_path" alt="Progress Image" class="h-48 w-full rounded-lg object-cover shadow" />
                                </div>
                                <div v-if="progress.file_path" class="mt-2">
                                    <a
                                        :href="progress.file_path"
                                        target="_blank"
                                        class="inline-flex items-center text-sm text-yellow-700 hover:underline"
                                    >
                                        📎 View Attached File
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Back Button -->
        <div class="mx-auto mt-8 max-w-4xl text-center">
            <button @click="goBack" class="rounded-lg bg-yellow-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-yellow-700">
                ← Back to My Projects
            </button>
        </div>

        <!-- Add Progress Modal -->
        <div
            v-if="showAddModal"
            @click.self="showAddModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        >
            <div class="w-full max-w-lg transform rounded-2xl bg-white p-6 shadow-2xl transition-all duration-300 ease-in-out">
                <h3 class="mb-4 flex items-center gap-2 text-xl font-bold text-gray-800">
                    <span class="text-yellow-600">🛠️</span> Add Project Progress
                </h3>

                <form @submit.prevent="addProgress" class="space-y-5">
                    <!-- Date -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700"> 📅 Progress Date <span class="text-red-500">*</span> </label>
                        <input
                            v-model="form.progress_date"
                            type="date"
                            class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                            required
                        />
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700"> 📝 Description <span class="text-red-500">*</span> </label>
                        <textarea
                            v-model="form.progress_description"
                            rows="3"
                            placeholder="Describe what was accomplished..."
                            class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                            required
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-400">Keep it clear and concise.</p>
                    </div>

                    <!-- Image Upload with Preview -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">🖼️ Progress Image (optional)</label>
                        <input type="file" @change="onImageChange" accept="image/*" class="w-full text-sm text-gray-600" />
                        <div v-if="imagePreview" class="mt-2 rounded-lg border border-gray-200 bg-gray-50 p-2">
                            <img :src="imagePreview" alt="Preview" class="max-h-48 w-full rounded-lg object-cover" />
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">📎 Attachment (optional)</label>
                        <input type="file" @change="onFileChange" class="w-full text-sm text-gray-600" />
                        <p v-if="fileName" class="mt-1 text-xs text-gray-500">
                            Selected file: <span class="font-medium text-gray-700">{{ fileName }}</span>
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex justify-end space-x-2">
                        <button
                            type="button"
                            @click="showAddModal = false"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-100"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="flex items-center gap-2 rounded-lg bg-yellow-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-yellow-700 disabled:opacity-70"
                        >
                            <span v-if="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ submitting ? 'Saving...' : 'Save Progress' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/layouts/MainLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

const { props } = usePage();
const data = props.data;

const progressList = ref([]);
const loading = ref(true);
const showAddModal = ref(false);
const form = ref({
    progress_date: '',
    progress_description: '',
    image: null,
    file: null,
});

// Fetch existing progress
const fetchProjectProgress = async () => {
    try {
        const response = await axios.get(route('projects.getProjectProgress', data.project_id));
        progressList.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching project progress:', error);
    } finally {
        loading.value = false;
    }
};
const imagePreview = ref(null);
const fileName = ref('');
const submitting = ref(false);

// Handle file inputs with preview
const onImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.image = file;
        imagePreview.value = URL.createObjectURL(file);
    } else {
        imagePreview.value = null;
    }
};

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.file = file;
        fileName.value = file.name;
    } else {
        fileName.value = '';
    }
};

// Upload new progress
const addProgress = async () => {
    submitting.value = true;
    const formData = new FormData();
    formData.append('project_id', data.project_id);
    formData.append('progress_date', form.value.progress_date);
    formData.append('progress_description', form.value.progress_description);
    if (form.value.image) formData.append('image_path', form.value.image);
    if (form.value.file) formData.append('file_path', form.value.file);

    try {
        const response = await axios.post(route('projects.addProgress'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.data.result === true) {
            await fetchProjectProgress();
            form.value = {
                progress_date: '',
                progress_description: '',
                image: null,
                file: null,
            };
            imagePreview.value = null;
            fileName.value = '';
            showAddModal.value = false;
        } else {
            console.error('Error adding progress:', response.data.message);
        }
    } catch (error) {
        console.error('Error adding progress:', error);
    } finally {
        submitting.value = false;
    }
};

onMounted(fetchProjectProgress);

// Helpers
const getProgress = (status) => {
    switch (status) {
        case 'planning':
            return 10;
        case 'in_progress':
            return 50;
        case 'on_hold':
            return 30;
        case 'completed':
            return 100;
        case 'cancelled':
            return 0;
        default:
            return 0;
    }
};

const progressColor = (status) => {
    switch (status) {
        case 'completed':
            return 'bg-green-500';
        case 'in_progress':
            return 'bg-blue-500';
        case 'planning':
            return 'bg-yellow-500';
        case 'on_hold':
            return 'bg-gray-400';
        case 'cancelled':
            return 'bg-red-500';
        default:
            return 'bg-gray-300';
    }
};
const formatDate = (d) => (d ? new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A');
const goBack = () => window.history.back();
</script>

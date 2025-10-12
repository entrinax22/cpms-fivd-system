<template>
    <Head title="Create Project Progress" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Project Progress</h1>
                    <p class="mt-1 text-gray-500">Fill out the form below to add a new project progress update.</p>
                </div>

                <button
                    @click="goBack"
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-gray-700 shadow-sm transition hover:bg-gray-200"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <!-- Form -->
            <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-lg">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Select Project -->
                        <div class="md:col-span-1">
                            <label class="mb-1 block text-sm font-bold tracking-wide text-gray-800 uppercase">Select Project</label>
                            <Multiselect
                                v-model="selectedProject"
                                :options="projects"
                                label="project_name"
                                track-by="project_id"
                                placeholder="Search and select a project"
                                @search-change="fetchProjects"
                                class="w-full"
                                :clear-on-select="true"
                                :allow-empty="true"
                            >
                                <template #option="{ option }"> {{ option.project_name }} — Client: {{ option.client_name }} </template>
                                <template #singleLabel="{ option }"> {{ option.project_name }} — Client: {{ option.client_name }} </template>
                            </Multiselect>
                        </div>

                        <!-- Progress Date -->
                        <div>
                            <label for="progress_date" class="mb-1 block text-sm font-bold tracking-wide text-gray-800 uppercase">
                                Progress Date
                            </label>
                            <input
                                v-model="newProjectProgress.progress_date"
                                type="date"
                                id="progress_date"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- Progress Description -->
                        <div class="md:col-span-2">
                            <label for="progress_description" class="mb-1 block text-sm font-bold tracking-wide text-gray-800 uppercase">
                                Progress Description
                            </label>
                            <textarea
                                v-model="newProjectProgress.progress_description"
                                id="progress_description"
                                rows="4"
                                placeholder="Describe the project progress..."
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            ></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="mb-2 block text-sm font-bold tracking-wide text-gray-800 uppercase"> Progress Image </label>

                            <div v-if="imagePreview" class="mb-3 flex items-center gap-3 rounded-lg border border-blue-200 bg-gray-50 p-2 shadow-sm">
                                <img :src="imagePreview" alt="Preview" class="h-24 w-24 rounded-lg border border-gray-300 object-cover shadow-md" />
                                <span class="text-xs text-gray-600 italic">Preview selected image</span>
                            </div>

                            <label
                                class="flex w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-blue-400 bg-blue-50 p-4 transition duration-200 ease-in-out hover:bg-blue-100"
                            >
                                <span class="font-semibold text-blue-600">Click to upload</span>
                                <span class="text-xs text-gray-500">Accepted types: JPG, PNG, GIF</span>
                                <input type="file" @change="handleImageUpload" accept="image/*" class="hidden" />
                            </label>
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label class="mb-2 block text-sm font-bold tracking-wide text-gray-800 uppercase"> Attachment (Optional) </label>

                            <div
                                v-if="fileName"
                                class="mb-3 flex items-center justify-between rounded-lg border border-green-200 bg-gray-50 p-3 shadow-sm"
                            >
                                <span class="truncate font-medium text-green-600">📎 {{ fileName }}</span>
                                <span class="text-xs text-gray-500">(PDF / DOCX)</span>
                            </div>

                            <label
                                class="flex w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-green-400 bg-green-50 p-4 transition duration-200 ease-in-out hover:bg-green-100"
                            >
                                <span class="font-semibold text-green-600">Click to upload file</span>
                                <span class="text-xs text-gray-500">Accepted types: PDF, DOCX</span>
                                <input type="file" @change="handleFileUpload" accept=".pdf,.doc,.docx" class="hidden" />
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white shadow-md transition hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:ring-offset-1"
                        >
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { toast } from '@/stores/ToastStore';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
const newProjectProgress = ref({
    project_id: null,
    progress_date: '',
    progress_description: '',
    image_path: null,
    file_path: null,
    status: '',
});

const projects = ref([]);
const selectedProject = ref(null);

// Fetch projects for dropdown
const fetchProjects = async (search = '') => {
    const route_url = route('admin.projects.selectList');
    try {
        const response = await axios.get(route_url, { params: { search } });
        projects.value = response.data.data ?? [];
    } catch (error) {
        projects.value = [];
    }
};

onMounted(fetchProjects);

watch(selectedProject, (val) => {
    newProjectProgress.value.project_id = val ? val.project_id : null;
});

const imagePreview = ref(null);
const fileName = ref(null);

const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        newProjectProgress.value.image_path = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        newProjectProgress.value.file_path = file;
        fileName.value = file.name;
    }
};

// Submit form
const submit = async () => {
    const route_url = route('admin.project_progress.store');

    const formData = new FormData();
    formData.append('project_id', newProjectProgress.value.project_id);
    formData.append('progress_date', newProjectProgress.value.progress_date);
    formData.append('progress_description', newProjectProgress.value.progress_description);
    formData.append('status', newProjectProgress.value.status);

    if (newProjectProgress.value.image_path) {
        formData.append('image_path', newProjectProgress.value.image_path);
    }
    if (newProjectProgress.value.file_path) {
        formData.append('file_path', newProjectProgress.value.file_path);
    }

    try {
        const response = await axios.post(route_url, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.data.result) {
            toast.show('Project Progress added successfully!', 'success');
            // Reset form
            newProjectProgress.value = {
                project_id: null,
                progress_date: '',
                progress_description: '',
                image_path: null,
                file_path: null,
                status: '',
            };
            selectedProject.value = null;
            imagePreview.value = null;
            fileName.value = null;
        } else {
            toast.show('Error adding project progress!', 'error');
        }
    } catch (error) {
        console.error(error);
    }
};

function goBack() {
    window.history.back();
}
</script>

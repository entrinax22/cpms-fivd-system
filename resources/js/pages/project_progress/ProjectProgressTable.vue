<template>
    <Head title="Project Progress" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Project Progress Table</h1>
                <p class="text-gray-500">Monitor project updates, progress descriptions, and uploaded files.</p>
            </div>

            <!-- Filters -->
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex w-full items-center gap-4 md:w-1/2">
                    <!-- Search -->
                    <div class="relative w-full">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search progress..."
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                        <span class="absolute top-2.5 right-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.65 6.65a7.5 7.5 0 016.5 9.5z"
                                />
                            </svg>
                        </span>
                    </div>

                    <!-- Rows per page -->
                    <select
                        v-model="rowsPerPage"
                        class="rounded-lg border border-gray-300 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                        <option :value="10">10</option>
                        <option :value="20">20</option>
                        <option :value="50">50</option>
                    </select>
                </div>

                <!-- Add Progress Button -->
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white shadow transition hover:bg-blue-700" @click="goToAdd">
                    + Add Progress
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <BaseTable
                    :columns="columns"
                    :data="progressList"
                    :current-page="pagination.current_page"
                    :total-pages="pagination.last_page"
                    :on-next-page="nextPage"
                    :on-prev-page="prevPage"
                    :per-page="pagination.per_page"
                >
                    <template #progress_date="{ row }">
                        {{ formatDate(row.progress_date) }}
                    </template>

                    <template #actions="{ row }">
                        <button
                            @click="editProgress(row.project_progress_id)"
                            class="rounded bg-yellow-500 px-2 py-1 text-xs font-semibold text-white shadow transition hover:bg-yellow-600"
                        >
                            Edit
                        </button>
                        <button
                            @click="deleteProgress(row.project_progress_id)"
                            class="rounded bg-red-500 px-2 py-1 text-xs font-semibold text-white shadow transition hover:bg-red-600"
                        >
                            Delete
                        </button>
                    </template>

                    <template #image_path="{ row }">
                        <div v-if="row.image_path">
                            <img
                                :src="row.image_path"
                                alt="Progress Image"
                                class="h-12 w-12 cursor-pointer rounded object-cover shadow transition hover:scale-110"
                                @click="openImagePreview(row.image_path)"
                            />
                        </div>
                        <div v-else class="text-sm text-gray-400 italic">No image</div>
                    </template>

                    <template #file_path="{ row }">
                        <div v-if="row.file_path">
                            <a :href="row.file_path" target="_blank" class="text-blue-600 hover:underline">View</a>
                        </div>
                        <div v-else class="text-sm text-gray-400 italic">No file</div>
                    </template>
                </BaseTable>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500/40 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-lg">
                <h2 class="mb-4 text-xl font-semibold">Edit Project Progress</h2>

                <form @submit.prevent="updateProgress">
                    <!-- Project Select -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Project</label>
                        <Multiselect
                            v-model="editProjectProgress.project_id"
                            :options="projects"
                            label="project_name"
                            track-by="project_id"
                            placeholder="Select a project"
                            class="mt-1"
                        >
                            <template #option="{ option }">
                                <div class="flex flex-col">
                                    <span class="font-semibold">{{ option.project_name }}</span>
                                    <span class="text-xs text-gray-500">Client: {{ option.client_name }}</span>
                                </div>
                            </template>
                        </Multiselect>
                    </div>

                    <!-- Progress Date -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Progress Date</label>
                        <input
                            type="date"
                            v-model="editProjectProgress.progress_date"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <!-- Progress Description -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Progress Description</label>
                        <textarea
                            v-model="editProjectProgress.progress_description"
                            rows="3"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Describe the progress..."
                        ></textarea>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-5">
                        <label class="mb-2 block text-sm font-bold text-gray-800 uppercase">Progress Image</label>

                        <!-- Current Preview -->
                        <div
                            v-if="!newImagePreview && editProjectProgress.image_path"
                            class="mb-3 flex items-center gap-3 rounded-lg border border-blue-200 bg-gray-50 p-2 shadow-sm"
                        >
                            <img
                                :src="editProjectProgress.image_path"
                                alt="Current Image"
                                class="h-24 w-24 rounded-lg border border-gray-300 object-cover shadow-md"
                            />
                            <span class="text-xs text-gray-600 italic">Current image preview</span>
                        </div>

                        <!-- New Selected Image Preview -->
                        <div v-if="newImagePreview" class="mb-3 flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 p-2 shadow-sm">
                            <img
                                :src="newImagePreview"
                                alt="New Image Preview"
                                class="h-24 w-24 rounded-lg border border-blue-300 object-cover shadow-md"
                            />
                            <span class="text-xs text-blue-600 italic">New image selected</span>
                        </div>

                        <input
                            type="file"
                            accept="image/*"
                            @change="handleImageChange"
                            class="block w-full rounded-lg border border-gray-300 p-2 text-sm"
                        />
                    </div>

                    <!-- File Upload -->
                    <div class="mb-5">
                        <label class="mb-2 block text-sm font-bold text-gray-800 uppercase">Attachment (Optional)</label>

                        <!-- Current File -->
                        <div
                            v-if="!newFileName && editProjectProgress.file_path"
                            class="mb-3 flex items-center justify-between rounded-lg border border-green-200 bg-gray-50 p-3 shadow-sm"
                        >
                            <a :href="editProjectProgress.file_path" target="_blank" class="font-medium text-green-600 hover:underline"
                                >📄 View current file</a
                            >
                            <span class="text-xs text-gray-500">(PDF / DOCX)</span>
                        </div>

                        <!-- New File Selected -->
                        <div
                            v-if="newFileName"
                            class="mb-3 flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 p-3 shadow-sm"
                        >
                            <span class="font-medium text-blue-700">📎 {{ newFileName }}</span>
                            <span class="text-xs text-blue-500 italic">New file selected</span>
                        </div>

                        <input
                            type="file"
                            accept=".pdf,.doc,.docx,.zip"
                            @change="handleFileChange"
                            class="block w-full rounded-lg border border-gray-300 p-2 text-sm"
                        />
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" class="rounded-md bg-gray-300 px-4 py-2 transition hover:bg-gray-400" @click="handleCloseEditModal">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white shadow transition hover:bg-blue-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Image Preview Modal -->
        <div
            v-if="showImageModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
            @click.self="closeImagePreview"
        >
            <div class="relative">
                <img :src="previewImageSrc" alt="Full Image Preview" class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain shadow-2xl" />
                <button @click="closeImagePreview" class="absolute -top-4 -right-4 rounded-full bg-white p-2 shadow transition hover:bg-gray-200">
                    ✕
                </button>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import BaseTable from '@/components/BaseTable.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { toast } from '@/stores/ToastStore';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

const search = ref('');
const rowsPerPage = ref(10);
const currentPage = ref(1);
const progressList = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 });

const columns = [
    { key: 'project_name', label: 'Project Name' },
    { key: 'progress_description', label: 'Description' },
    { key: 'progress_date', label: 'Progress Date' },
    { key: 'image_path', label: 'Image' },
    { key: 'file_path', label: 'File' },
];

// ============ FETCH TABLE DATA ============
async function fetch() {
    try {
        const response = await axios.get(route('admin.project_progress.list'), {
            params: {
                search: search.value,
                page: currentPage.value,
                per_page: rowsPerPage.value,
            },
        });

        if (response.data.result === true) {
            progressList.value = response.data.data;
            pagination.value = response.data.pagination;
        } else {
            toast.show(response.data.message, 'error');
        }
    } catch (error) {
        console.error(error);
        toast.show('Failed to load project progress.', 'error');
    }
}

onMounted(fetch);
watch([search, rowsPerPage], () => {
    currentPage.value = 1;
    fetch();
});

function formatDate(dateStr) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateStr).toLocaleDateString(undefined, options);
}

function nextPage() {
    if (currentPage.value < pagination.value.last_page) {
        currentPage.value++;
        fetch();
    }
}
function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
        fetch();
    }
}

function goToAdd() {
    router.get(route('admin.project_progress.create'));
}

// ============ EDIT MODAL ============
const showEditModal = ref(false);
const editProjectProgress = ref({});
const projects = ref([]);
const newImageFile = ref(null);
const newFileUpload = ref(null);
const newImagePreview = ref(null);
const newFileName = ref(null);

function handleOpenEditModal() {
    showEditModal.value = true;
}
function handleCloseEditModal() {
    showEditModal.value = false;
    editProjectProgress.value = {};
    newImageFile.value = null;
    newFileUpload.value = null;
    newImagePreview.value = null;
    newFileName.value = null;
}

// ============ FILE HANDLERS ============
function handleImageChange(event) {
    const file = event.target.files[0];
    if (file) {
        newImageFile.value = file;
        newImagePreview.value = URL.createObjectURL(file);
    }
}
function handleFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        newFileUpload.value = file;
        newFileName.value = file.name;
    }
}

// ============ IMAGE PREVIEW MODAL ============
const showImageModal = ref(false);
const previewImageSrc = ref(null);

function openImagePreview(src) {
    previewImageSrc.value = src;
    showImageModal.value = true;
}

function closeImagePreview() {
    showImageModal.value = false;
    previewImageSrc.value = null;
}

// ============ EDIT FUNCTION ============
async function editProgress(project_progress_id) {
    try {
        const response = await axios.get(route('admin.project_progress.edit', { project_progress_id }));
        if (response.data.result === true) {
            projects.value = response.data.projects || [];
            const record = response.data.data;

            const selectedProject = projects.value.find((p) => p.project_id === record.project_id);

            editProjectProgress.value = {
                ...record,
                project_id: selectedProject || null,
            };

            handleOpenEditModal();
        } else {
            toast.show('Failed to load project progress.', 'error');
        }
    } catch (error) {
        console.error(error);
        toast.show('Error fetching project progress.', 'error');
    }
}

// ============ UPDATE FUNCTION ============
async function updateProgress() {
    const formData = new FormData();
    formData.append('project_id', editProjectProgress.value.project_id.project_id);
    formData.append('progress_date', editProjectProgress.value.progress_date);
    formData.append('progress_description', editProjectProgress.value.progress_description);

    if (newImageFile.value) formData.append('image_path', newImageFile.value);
    if (newFileUpload.value) formData.append('file_path', newFileUpload.value);

    try {
        const response = await axios.post(
            route('admin.project_progress.update', { project_progress_id: editProjectProgress.value.project_progress_id }),
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } },
        );

        if (response.data.result === true) {
            toast.show('Project progress updated successfully!', 'success');
            handleCloseEditModal();
            fetch();
        } else {
            toast.show(response.data.message || 'Failed to update project progress.', 'error');
        }
    } catch (error) {
        console.error(error);
        toast.show('Error updating project progress.', 'error');
    }
}

// ============ DELETE FUNCTION ============
async function deleteProgress(project_progress_id) {
    if (!confirm('Are you sure you want to delete this project progress?')) return;

    try {
        const response = await axios.post(route('admin.project_progress.destroy', { project_progress_id }));
        if (response.data.result === true) {
            toast.show('Project progress deleted successfully!', 'success');
            fetch();
        } else {
            toast.show('Failed to delete project progress.', 'error');
        }
    } catch (error) {
        toast.show('Error deleting project progress.', 'error');
    }
}
</script>

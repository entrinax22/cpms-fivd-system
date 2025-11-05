<template>
    <Head title="Project Table" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Project Table</h1>
                <p class="text-gray-500">Manage projects, their details, and actions.</p>
            </div>

            <!-- Controls -->
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex w-full items-center gap-4 md:w-1/2">
                    <!-- Search -->
                    <div class="relative w-full">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search projects..."
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
                    <div class="flex min-w-max items-center gap-2">
                        <select
                            id="rowsPerPage"
                            v-model="rowsPerPage"
                            class="rounded-lg border border-gray-300 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        >
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>

                <!-- Add Project -->
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white shadow transition hover:bg-blue-700" @click="go">+ Add Project</button>
            </div>

            <!-- Projects Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <BaseTable
                    :columns="columns"
                    :data="paginatedProjects"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :on-next-page="nextPage"
                    :on-prev-page="prevPage"
                    :per-page="pagination.per_page"
                >
                    <template #start_date="{ row }">
                        {{ formatDate(row.start_date) }}
                    </template>
                    <template #estimated_end_date="{ row }">
                        {{ formatDate(row.estimated_end_date) }}
                    </template>
                    <template #manager_name="{ row }">
                        <div class="px-3 py-2">
                            <!-- Manager Info -->
                            <div class="flex items-center space-x-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ row.manager_name || 'No Manager Assigned' }}
                                    </p>
                                    <p class="text-xs text-gray-500">Project Manager</p>
                                </div>
                            </div>

                            <!-- Teams -->
                            <div v-if="development_teams[projects.indexOf(row)]?.length" class="mt-2 flex flex-wrap gap-2">
                                <template v-for="team in development_teams[projects.indexOf(row)]" :key="development_teams.team_id">
                                    <span
                                        class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-200"
                                        style="cursor: pointer"
                                    >
                                        {{ team.team_name }}
                                    </span>
                                </template>
                            </div>

                            <div v-if="testing_teams[projects.indexOf(row)]?.length" class="mt-2 flex flex-wrap gap-2">
                                <template v-for="team in testing_teams[projects.indexOf(row)]" :key="testing_teams.testing_team_id">
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-green-200"
                                        style="cursor: pointer"
                                    >
                                        {{ team.team_name }}
                                    </span>
                                </template>
                            </div>

                            <div v-else class="mt-2 text-xs text-gray-400 italic">No development teams assigned</div>
                        </div>
                    </template>

                    <template #actions="{ row }">
                        <button
                            @click="editProject(row.project_id)"
                            class="rounded bg-yellow-500 px-2 py-1 text-xs font-semibold text-white shadow transition hover:bg-yellow-600"
                        >
                            Edit
                        </button>
                        <button
                            @click="deleteProject(row.project_id)"
                            class="rounded bg-red-500 px-2 py-1 text-xs font-semibold text-white shadow transition hover:bg-red-600"
                        >
                            Delete
                        </button>
                    </template>
                </BaseTable>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500/40 backdrop-blur-sm">
            <div class="w-full max-w-3xl rounded-lg bg-white p-6 shadow-lg">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Edit Project</h2>

                <form @submit.prevent="updateProject(editProjectData.project_id)">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Project Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Project Name</label>
                            <input
                                type="text"
                                v-model="editProjectData.project_name"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- Client -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Client</label>
                            <Multiselect
                                v-model="selectedEditClient"
                                :options="clients"
                                label="client_name"
                                track-by="client_id"
                                placeholder="Select client"
                                @search-change="fetchClients"
                            />
                        </div>

                        <!-- Manager -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Project Manager</label>
                            <Multiselect
                                v-model="selectedEditManager"
                                :options="managers"
                                label="manager_name"
                                track-by="manager_id"
                                placeholder="Select project manager"
                                @search-change="fetchManagers"
                            />
                        </div>

                        <!-- Dates -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                v-model="editProjectData.start_date"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estimated End Date</label>
                            <input
                                type="date"
                                v-model="editProjectData.estimated_end_date"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select
                                v-model="editProjectData.status"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option disabled value="">Select Status</option>
                                <option value="planning">Planning</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="on_hold">On Hold</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Project Description</label>
                        <textarea
                            v-model="editProjectData.project_description"
                            rows="4"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
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
    </AdminLayout>
</template>

<script setup>
import BaseTable from '@/components/BaseTable.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { toast } from '@/stores/ToastStore';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

const search = ref('');
const columns = [
    { key: 'project_name', label: 'Project Name' },
    { key: 'client_name', label: 'Client' },
    { key: 'manager_name', label: 'Manager' },
    { key: 'start_date', label: 'Start Date' },
    { key: 'estimated_end_date', label: 'End Date' },
    { key: 'status', label: 'Status' },
];

const projects = ref([]);
const development_teams = ref([]);
const testing_teams = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10 });
const rowsPerPage = ref(10);
const currentPage = ref(1);

async function fetch() {
    const route_url = route('admin.projects.list');
    try {
        const response = await axios.get(route_url, {
            params: { search: search.value, page: currentPage.value, per_page: rowsPerPage.value },
        });
        if (response.data.result === true) {
            projects.value = response.data.data;
            development_teams.value = response.data.development_teams;
            testing_teams.value = response.data.testing_teams;
            pagination.value = response.data.pagination;
        }
    } catch (e) {
        console.error(e);
    }
}

onMounted(fetch);
watch([search, rowsPerPage], fetch);

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
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

const paginatedProjects = computed(() => projects.value);
const totalPages = computed(() => pagination.value.last_page);

function go() {
    router.get(route('admin.projects.create'));
}

// edit modal
const showEditModal = ref(false);
const editProjectData = ref({});
const clients = ref([]);
const managers = ref([]);
const selectedEditClient = ref(null);
const selectedEditManager = ref(null);

watch(selectedEditClient, (val) => {
    editProjectData.value.client_id = val ? val.client_id : null;
});
watch(selectedEditManager, (val) => {
    editProjectData.value.manager_id = val ? val.manager_id : null;
});

function handleCloseEditModal() {
    showEditModal.value = false;
    editProjectData.value = {};
}

async function editProject(project_id) {
    const route_url = route('admin.projects.edit', { project_id });
    try {
        const res = await axios.get(route_url);
        if (res.data.result) {
            const project = res.data.data.project;
            clients.value = res.data.data.clients;
            managers.value = res.data.data.managers;

            editProjectData.value = project;

            selectedEditClient.value = clients.value.find((c) => c.client_id === project.client_id) || null;

            selectedEditManager.value = managers.value.find((m) => m.manager_id === project.manager_id) || null;

            showEditModal.value = true;
        }
    } catch (err) {
        toast.show('Error loading project.', 'error');
        console.error(err);
    }
}

async function updateProject(project_id) {
    const route_url = route('admin.projects.update', { project_id });
    try {
        const res = await axios.post(route_url, editProjectData.value);
        if (res.data.result) {
            toast.show('Project updated successfully!', 'success');
            handleCloseEditModal();
            fetch();
        } else toast.show('Failed to update project', 'error');
    } catch (err) {
        toast.show('Error updating project.', 'error');
        console.error(err);
    }
}

async function deleteProject(project_id) {
    if (!confirm('Are you sure you want to delete this project?')) return;
    const route_url = route('admin.projects.destroy', { project_id });
    try {
        const res = await axios.post(route_url);
        if (res.data.result) {
            toast.show('Project deleted successfully!', 'success');
            fetch();
        } else toast.show('Error deleting project!', 'error');
    } catch (err) {
        toast.show('Error deleting project.', 'error');
        console.error(err);
    }
}
</script>

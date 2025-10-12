<template>
    <Head title="Project Managers Table" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Dashboard Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Project Managers Table</h1>
                <p class="text-gray-500">Manage project managers, their details, and actions.</p>
            </div>

            <!-- Table Controls -->
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <!-- Search & Rows per page grouped -->
                <div class="flex w-full items-center gap-4 md:w-1/2">
                    <!-- Search -->
                    <div class="relative w-full">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search users..."
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
                    <!-- Rows per page selector -->
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
                <!-- Add User Button -->
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white shadow transition hover:bg-blue-700" @click="go">
                    + Add Project Manager
                </button>
            </div>

            <!-- Users Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <BaseTable
                    :columns="columns"
                    :data="paginatedManagers"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :on-next-page="nextPage"
                    :on-prev-page="prevPage"
                    :per-page="pagination.per_page"
                >
                    <template #actions="{ row }">
                        <button
                            @click="editManager(row.manager_id)"
                            class="rounded bg-yellow-500 px-2 py-1 text-xs font-semibold text-white shadow transition hover:bg-yellow-600"
                        >
                            Edit
                        </button>
                        <button
                            @click="deleteManager(row.manager_id)"
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
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg">
                <h2 class="mb-4 text-xl font-semibold">Edit Project Manager</h2>

                <form @submit.prevent="updateManager(editManagerData.id)">
                    <!-- Manager Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Manager Name</label>
                        <input
                            type="text"
                            v-model="editManagerData.manager_name"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <!-- Expertise Area -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Expertise Area</label>
                        <input
                            type="text"
                            v-model="editManagerData.expertise_area"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Contact Info -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Contact Info</label>
                        <input
                            type="text"
                            v-model="editManagerData.contact_information"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Years of Experience -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Years of Experience</label>
                        <input
                            type="number"
                            v-model="editManagerData.years_of_experience"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" class="rounded-md bg-gray-300 px-4 py-2 transition hover:bg-gray-400" @click="handleCloseEditModal()">
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

const search = ref('');
const columns = [
    { key: 'manager_name', label: 'Manager Name' },
    { key: 'expertise_area', label: 'Expertise Area' },
    { key: 'contact_information', label: 'Contact Info' },
    { key: 'years_of_experience', label: 'Years Exp.' },
];

const projectManagers = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
const rowsPerPage = ref(10);
const currentPage = ref(1);

async function fetch() {
    const route_url = route('admin.project-managers.list');
    try {
        const response = await axios.get(route_url, {
            params: {
                search: search.value,
                page: currentPage.value,
                per_page: rowsPerPage.value,
            },
        });
        if (response.data.result === true) {
            projectManagers.value = response.data.data;
            pagination.value = response.data.pagination;
        } else {
            console.error('Failed to fetch users:', response.data.message);
        }
    } catch (error) {
        console.error('Error fetching users:', error);
    }
}

onMounted(fetch);

watch(search, () => {
    currentPage.value = 1;
    fetch();
});

watch(rowsPerPage, () => {
    currentPage.value = 1;
    fetch();
});

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

const paginatedManagers = computed(() => projectManagers.value);
const totalPages = computed(() => pagination.value.last_page);
//ends here for templating

function go() {
    router.get(route('admin.project-manager.create'));
}

const showEditModal = ref(false);
const editManagerData = ref(null);

function handleOpenEditModal(manager) {
    showEditModal.value = true;
    editManagerData.value = { ...manager };
}

function handleCloseEditModal() {
    showEditModal.value = false;
    editManagerData.value = null;
}

async function editManager(manager_id) {
    const route_url = route('admin.project-managers.edit', { manager_id });
    try {
        const response = await axios.get(route_url);
        if (response.data.result === true) {
            handleOpenEditModal(response.data.data);
        } else {
            console.error('Failed to edit manager:', response.data.message);
        }
    } catch (error) {
        console.error('Error editing manager:', error);
    }
}

async function updateManager(manager_id) {
    const route_url = route('admin.project-managers.update', { manager_id });
    try {
        const response = await axios.post(route_url, editManagerData.value);
        if (response.data.result === true) {
            handleCloseEditModal();
            toast.show('Manager updated successfully!', 'success');
            fetch(); // Refresh list
        } else {
            toast.show('Error updating manager!', 'error');
        }
    } catch (error) {
        toast.show('Error updating manager!', 'error');
    }
}

async function deleteManager(id) {
    if (confirm('Are you sure you want to delete this project manager?')) {
        const route_url = route('admin.project-managers.destroy', { id });
        try {
            const response = await axios.post(route_url);
            if (response.data.result === true) {
                fetch();
                toast.show('Manager deleted successfully!', 'success');
            } else {
                toast.show('Error deleting manager!', 'error');
            }
        } catch (error) {
            toast.show('Error deleting manager!', 'error');
        }
    }
}
</script>

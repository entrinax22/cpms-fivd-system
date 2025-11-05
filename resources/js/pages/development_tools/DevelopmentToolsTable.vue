<template>
    <Head title="Development Teams Tools" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Dashboard Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Development Tools Table</h1>
                <p class="text-gray-500">Manage development tools and their details.</p>
            </div>

            <!-- Table Controls -->
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex w-full items-center gap-4 md:w-1/2">
                    <!-- Search -->
                    <div class="relative w-full">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search teams..."
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
                            v-model="rowsPerPage"
                            class="rounded-lg border border-gray-300 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        >
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>

                <!-- Add Team Button -->
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white shadow transition hover:bg-blue-700" @click="go">
                    + Add Development Tool
                </button>
            </div>

            <!-- Teams Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <BaseTable
                    :columns="columns"
                    :data="paginatedTeams"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :on-next-page="nextPage"
                    :on-prev-page="prevPage"
                    :per-page="pagination.per_page"
                >
                    <template #development_team="{ row }">
                        {{ row.development_team ? row.development_team.team_name : 'N/A' }} -
                        {{ row.development_team ? row.development_team.manager.manager_name : 'N/A' }}
                    </template>
                    <template #actions="{ row }">
                        <button
                            @click="editTool(row.tool_id)"
                            class="rounded bg-yellow-500 px-2 py-1 text-xs font-semibold text-white shadow transition hover:bg-yellow-600"
                        >
                            Edit
                        </button>
                        <button
                            @click="deleteTeam(row.tool_id)"
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
                <h2 class="mb-4 text-xl font-semibold">Edit Development Tool</h2>

                <form @submit.prevent="updateTeam(editToolData.tool_id)">
                    <!-- Team Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tool Name</label>
                        <input
                            type="text"
                            v-model="editToolData.tool_name"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <!-- Team Size -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tool Version</label>
                        <input
                            type="text"
                            v-model="editToolData.tool_version"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Specialization -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">License Expiry Date</label>
                        <input
                            type="date"
                            v-model="editToolData.license_expiry_date"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Project Manager -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Project Manager</label>
                        <Multiselect
                            v-model="editToolData.team_id"
                            :options="devTeams"
                            label="team_name"
                            track-by="team_id"
                            placeholder="Select a team"
                        >
                            <template #option="{ option }"> {{ option.team_name }} - ({{ option.manager.manager_name }}) </template>
                        </Multiselect>
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
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

// Table & Pagination
const search = ref('');
const columns = [
    { key: 'tool_name', label: 'Tool Name' },
    { key: 'tool_version', label: 'Tool Version' },
    { key: 'license_expiry_date', label: 'License Expiry Date' },
    { key: 'development_team', label: 'Development Team' },
];

const developmentTools = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
const rowsPerPage = ref(10);
const currentPage = ref(1);

const fetchTools = async () => {
    try {
        const res = await axios.get(route('admin.development-tools.list'), {
            params: { search: search.value, page: currentPage.value, per_page: rowsPerPage.value },
        });
        if (res.data.result) {
            developmentTools.value = res.data.data.map((tool) => ({
                ...tool,
            }));
            pagination.value = res.data.pagination;
        }
    } catch (err) {
        console.error(err);
    }
};

onMounted(fetchTools);

watch([search, rowsPerPage], () => {
    currentPage.value = 1;
    fetchTools();
});

const nextPage = () => {
    if (currentPage.value < pagination.value.last_page) {
        currentPage.value++;
        fetchTools();
    }
};
const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        fetchTools();
    }
};

const paginatedTeams = computed(() => developmentTools.value);
const totalPages = computed(() => pagination.value.last_page);

// Edit Modal
const devTeams = ref([]);
const showEditModal = ref(false);
const editToolData = ref(null);

const handleCloseEditModal = () => {
    showEditModal.value = false;
    editToolData.value = null;
};

const editTool = async (tool_id) => {
    try {
        const res = await axios.get(route('admin.development-tools.edit', { tool_id }));
        if (res.data.result === true) {
            const { tool, development_teams } = res.data.data;
            devTeams.value = development_teams;

            const selectedTeam = development_teams.find((team) => team.team_id === tool.team_id) || null;

            editToolData.value = {
                ...tool,
                team_id: selectedTeam,
            };
            showEditModal.value = true;
        }
    } catch (err) {
        console.error(err);
    }
};

const updateTeam = async (tool_id) => {
    try {
        const payload = {
            ...editToolData.value,
        };
        const res = await axios.post(route('admin.development-tools.update', { tool_id }), payload);
        if (res.data.result) {
            handleCloseEditModal();
            toast.show('Tool updated successfully!', 'success');
            fetchTools();
        } else {
            toast.show('Error updating tool!', 'error');
        }
    } catch (err) {
        toast.show('Error updating tool!', 'error');
        console.error(err);
    }
};

const deleteTeam = async (tool_id) => {
    if (!confirm('Are you sure you want to delete this development tool?')) return;

    try {
        const res = await axios.post(route('admin.development-tools.destroy', { tool_id }));
        if (res.data.result) {
            toast.show('Team deleted successfully!', 'success');
            fetchTools();
        } else {
            toast.show('Error deleting team!', 'error');
        }
    } catch (err) {
        toast.show('Error deleting team!', 'error');
        console.error(err);
    }
};

const go = () => {
    router.get(route('admin.development-tools.create'));
};
</script>

<template>
    <Head title="Clients Table" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Clients Table</h1>
                <p class="text-gray-500">Manage your registered clients and their details.</p>
            </div>

            <!-- Controls -->
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex w-full items-center gap-4 md:w-1/2">
                    <!-- Search -->
                    <div class="relative w-full">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search clients..."
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
                            v-model="rowsPerPage"
                            class="rounded-lg border border-gray-300 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        >
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>

                <!-- Add Client Button -->
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white shadow transition hover:bg-blue-700" @click="go">+ Add Client</button>
            </div>

            <!-- Clients Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <BaseTable
                    :columns="columns"
                    :data="paginatedClients"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :on-next-page="nextPage"
                    :on-prev-page="prevPage"
                    :per-page="pagination.per_page"
                >
                    <template #actions="{ row }">
                        <button
                            @click="editClient(row.client_id)"
                            class="rounded bg-yellow-500 px-2 py-1 text-xs font-semibold text-white shadow transition hover:bg-yellow-600"
                        >
                            Edit
                        </button>
                        <button
                            @click="deleteClient(row.client_id)"
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
                <h2 class="mb-4 text-xl font-semibold">Edit Client</h2>

                <form @submit.prevent="updateClient(editClientData.client_id)">
                    <!-- Client Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Client Name</label>
                        <input
                            type="text"
                            v-model="editClientData.client_name"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <!-- Contact Info -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Contact Information</label>
                        <input
                            type="text"
                            v-model="editClientData.contact_information"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <!-- Registration Date -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Registration Date</label>
                        <input
                            type="date"
                            v-model="editClientData.registration_date"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <!-- Client Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Client Type</label>
                        <input
                            type="text"
                            v-model="editClientData.client_type"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            required
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
    { key: 'client_name', label: 'Client Name' },
    { key: 'contact_information', label: 'Contact Info' },
    { key: 'registration_date', label: 'Registration Date' },
    { key: 'client_type', label: 'Client Type' },
];

const clients = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
const rowsPerPage = ref(10);
const currentPage = ref(1);

async function fetchClients() {
    const route_url = route('admin.clients.list'); // 👈 adjust route name
    try {
        const response = await axios.get(route_url, {
            params: {
                search: search.value,
                page: currentPage.value,
                per_page: rowsPerPage.value,
            },
        });
        if (response.data.result === true) {
            clients.value = response.data.data;
            pagination.value = response.data.pagination;
        } else {
            console.error('Failed to fetch clients:', response.data.message);
        }
    } catch (error) {
        console.error('Error fetching clients:', error);
    }
}

onMounted(fetchClients);
watch(search, () => {
    currentPage.value = 1;
    fetchClients();
});
watch(rowsPerPage, () => {
    currentPage.value = 1;
    fetchClients();
});

function nextPage() {
    if (currentPage.value < pagination.value.last_page) {
        currentPage.value++;
        fetchClients();
    }
}

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
        fetchClients();
    }
}

const paginatedClients = computed(() => clients.value);
const totalPages = computed(() => pagination.value.last_page);

function go() {
    router.get(route('admin.clients.create'));
}

// Edit / Update Modal Logic
const showEditModal = ref(false);
const editClientData = ref(null);

function handleOpenEditModal(client) {
    showEditModal.value = true;
    editClientData.value = { ...client };
}

function handleCloseEditModal() {
    showEditModal.value = false;
    editClientData.value = null;
}

async function editClient(client_id) {
    const route_url = route('admin.clients.edit', { client_id });
    try {
        const response = await axios.get(route_url);
        if (response.data.result === true) {
            handleOpenEditModal(response.data.data);
        } else {
            console.error('Failed to edit client:', response.data.message);
        }
    } catch (error) {
        console.error('Error editing client:', error);
    }
}

async function updateClient(client_id) {
    const route_url = route('admin.clients.update', { client_id });
    try {
        const response = await axios.post(route_url, editClientData.value);
        if (response.data.result === true) {
            handleCloseEditModal();
            toast.show('Client updated successfully!', 'success');
            fetchClients();
        } else {
            toast.show('Error updating client!', 'error');
        }
    } catch (error) {
        toast.show('Error updating client!', 'error');
    }
}

async function deleteClient(client_id) {
    if (confirm('Are you sure you want to delete this client?')) {
        const route_url = route('admin.clients.destroy', { client_id });
        try {
            const response = await axios.post(route_url);
            if (response.data.result === true) {
                fetchClients();
                toast.show('Client deleted successfully!', 'success');
            } else {
                toast.show('Error deleting client!', 'error');
            }
        } catch (error) {
            toast.show('Error deleting client!', 'error');
        }
    }
}
</script>

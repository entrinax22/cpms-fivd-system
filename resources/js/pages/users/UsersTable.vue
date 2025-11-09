<template>
    <Head title="Users Table" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Dashboard Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Users Table</h1>
                <p class="text-gray-500">Manage employees, their details, and actions.</p>
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
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white shadow transition hover:bg-blue-700" @click="go">+ Add User</button>
            </div>

            <!-- Users Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <BaseTable
                    :columns="columns"
                    :data="paginatedUsers"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :on-next-page="nextPage"
                    :on-prev-page="prevPage"
                    :per-page="pagination.per_page"
                >
                    <template #actions="{ row }">
                        <button
                            @click="editUser(row.id)"
                            class="rounded bg-yellow-500 px-2 py-1 text-xs font-semibold text-white shadow transition hover:bg-yellow-600"
                        >
                            Edit
                        </button>
                        <button
                            @click="deleteUser(row.id)"
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
                <h2 class="mb-4 text-xl font-semibold">Edit User</h2>

                <!-- Form -->
                <form @submit.prevent="updateUser(editUserData.id)">
                    <!-- Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input
                            type="text"
                            v-model="editUserData.name"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            type="email"
                            v-model="editUserData.email"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <!--Phone-->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input
                            type="text"
                            v-model="editUserData.phone"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <!-- Role -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <select
                            v-model="editUserData.role"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>

                    <!-- Development Teams -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Development Teams</label>
                        <Multiselect
                            v-model="selectedDevTeams"
                            :options="devTeams"
                            label="displayName"
                            track-by="team_id"
                            :multiple="true"
                            :searchable="true"
                            placeholder="Search and select development teams"
                            class="mt-1 block w-full"
                        />
                        <p class="mt-1 text-xs text-gray-400">(Use the dropdown to search & select multiple)</p>
                    </div>

                    <!-- Testing Teams -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Testing Teams</label>
                        <Multiselect
                            v-model="selectedTestTeams"
                            :options="testTeams"
                            label="displayName"
                            track-by="testing_team_id"
                            :multiple="true"
                            :searchable="true"
                            placeholder="Search and select testing teams"
                            class="mt-1 block w-full"
                        />
                        <p class="mt-1 text-xs text-gray-400">(Use the dropdown to search & select multiple)</p>
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
import { computed, nextTick, onMounted, ref, watch } from 'vue';

// vue-multiselect
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';

const search = ref('');
const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Phone' },
    { key: 'role', label: 'Role' },
    {
        key: 'development_teams',
        label: 'Development Teams',
        template: (row) => {
            if (!row.development_teams?.length) return '-';
            return row.development_teams
                .map(
                    (team) =>
                        `<span class="inline-block rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800 mr-1 mb-1">${team.team_name}</span>`,
                )
                .join('');
        },
    },
    {
        key: 'testing_teams',
        label: 'Testing Teams',
        template: (row) => {
            if (!row.testing_teams?.length) return '-';
            return row.testing_teams
                .map(
                    (team) =>
                        `<span class="inline-block rounded-full bg-green-100 px-2 py-1 text-xs text-green-800 mr-1 mb-1">${team.team_name}</span>`,
                )
                .join('');
        },
    },
];

const users = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
const rowsPerPage = ref(10);
const currentPage = ref(1);

async function fetch() {
    const route_url = route('admin.users.list');
    try {
        const response = await axios.get(route_url, {
            params: {
                search: search.value,
                page: currentPage.value,
                per_page: rowsPerPage.value,
            },
        });
        if (response.data.result === true) {
            users.value = response.data.data;
            pagination.value = response.data.pagination;
        } else {
            console.error('Failed to fetch users:', response.data.message);
        }
    } catch (error) {
        console.error('Error fetching users:', error);
    }
}

// Fetch users and team lists on mount
const devTeams = ref([]);
const testTeams = ref([]);
const selectedDevTeams = ref([]);
const selectedTestTeams = ref([]);

const fetchTeamLists = async () => {
    try {
        const [devResp, testResp] = await Promise.all([
            axios.get(route('admin.development-teams.selectList')),
            axios.get(route('admin.testing-teams.selectList')),
        ]);
        if (devResp.data.result === true) {
            devTeams.value = devResp.data.data.map((team) => ({
                ...team,
                displayName: `${team.team_name} (${team.current_members}/${team.team_size} members)`,
            }));
        }
        if (testResp.data.result === true) {
            testTeams.value = testResp.data.data.map((team) => ({
                ...team,
                displayName: `${team.team_name} (${team.current_members}/${team.team_size} members)`,
            }));
        }
        console.log('Fetched dev teams:', devTeams.value);
        console.log('Fetched test teams:', testTeams.value);
    } catch (e) {
        console.error('Error fetching team lists', e);
        toast.show('Error loading team lists', 'error');
    }
};

onMounted(() => {
    fetch();
    fetchTeamLists();
});

// when edit modal opens and team lists are available, pre-select matching options
watch([devTeams, testTeams], async () => {
    if (!editUserData.value) return;
    await nextTick();
    if (devTeams.value.length && editUserData.value.development_team_ids) {
        selectedDevTeams.value = devTeams.value.filter((t) => editUserData.value.development_team_ids.includes(t.team_id));
    }
    if (testTeams.value.length && editUserData.value.testing_team_ids) {
        selectedTestTeams.value = testTeams.value.filter((t) => editUserData.value.testing_team_ids.includes(t.testing_team_id));
    }
});

// keep selected objects in sync with encrypted id arrays for update payload
watch(selectedDevTeams, (val) => {
    if (!editUserData.value) return;
    editUserData.value.development_team_ids = val && val.length ? val.map((v) => v.team_id) : [];
    editUserData.value.development_teams =
        val && val.length
            ? val.map((v) => ({
                  team_id: v.team_id,
                  team_name: v.team_name,
              }))
            : [];
});

watch(selectedTestTeams, (val) => {
    if (!editUserData.value) return;
    editUserData.value.testing_team_ids = val && val.length ? val.map((v) => v.testing_team_id) : [];
    editUserData.value.testing_teams =
        val && val.length
            ? val.map((v) => ({
                  testing_team_id: v.testing_team_id,
                  team_name: v.team_name,
              }))
            : [];
});

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

const paginatedUsers = computed(() => users.value);
const totalPages = computed(() => pagination.value.last_page);
//ends here for templating

function go() {
    router.get(route('admin.users.create'));
}

const showEditModal = ref(false);
const editUserData = ref(null);
function handleOpenEditModal(user) {
    showEditModal.value = true;
    console.log('Opening edit modal for user:', user);

    // create a copy and normalize team arrays into the expected payload shape
    const copy = { ...user };

    // Handle development teams
    if (user.development_teams?.length) {
        copy.development_team_ids = user.development_teams.map((t) => t.team_id);
        console.log('Development team IDs from user:', copy.development_team_ids);
        console.log('Available dev teams:', devTeams.value);

        selectedDevTeams.value = devTeams.value.filter((dt) => {
            const matches = user.development_teams.some((ut) => {
                console.log('Comparing:', ut.team_id, 'with', dt.team_id);
                return ut.team_id === dt.team_id;
            });
            return matches;
        });
    } else {
        copy.development_team_ids = [];
        selectedDevTeams.value = [];
    }

    // Handle testing teams
    if (user.testing_teams?.length) {
        copy.testing_team_ids = user.testing_teams.map((t) => t.testing_team_id);
        console.log('Testing team IDs from user:', copy.testing_team_ids);
        console.log('Available test teams:', testTeams.value);

        selectedTestTeams.value = testTeams.value.filter((tt) => {
            const matches = user.testing_teams.some((ut) => {
                console.log('Comparing:', ut.testing_team_id, 'with', tt.testing_team_id);
                return ut.testing_team_id === tt.testing_team_id;
            });
            return matches;
        });
    } else {
        copy.testing_team_ids = [];
        selectedTestTeams.value = [];
    }

    editUserData.value = copy;

    console.log('Selected development teams:', selectedDevTeams.value);
    console.log('Selected testing teams:', selectedTestTeams.value);
}

function handleCloseEditModal() {
    showEditModal.value = false;
    editUserData.value = null;
}
async function editUser(id) {
    const route_url = route('admin.users.edit', { id });
    try {
        const response = await axios.get(route_url);
        if (response.data.result === true) {
            handleOpenEditModal(response.data.data);
        } else {
            console.error('Failed to edit user:', response.data.message);
        }
    } catch (error) {
        console.error('Error editing user:', error);
    }
}

async function updateUser(id) {
    const route_url = route('admin.users.update', { id });
    try {
        const response = await axios.post(route_url, editUserData.value);
        if (response.data.result === true) {
            handleCloseEditModal();
            toast.show('User updated successfully!', 'success');
            fetch(); // Refresh the user list after update
            fetchTeamLists(); // Refresh team lists to update member counts
        } else {
            toast.show(response.data.message || 'Error in updating the user!', 'error');
        }
    } catch (error) {
        // Handle validation errors from team size limits
        if (error.response?.status === 422) {
            toast.show(error.response.data.message, 'error');
        } else {
            toast.show(error?.message || String(error), 'error');
        }
    }
}

async function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        const route_url = route('admin.users.destroy', { id });
        try {
            const response = await axios.post(route_url);
            if (response.data.result === true) {
                fetch(); // Refresh the user list after deletion
                toast.show('User deleted successfully!', 'success');
            } else {
                toast.show('Error in deleting the user!', 'error');
            }
        } catch (err) {
            console.error(err);
            toast.show('Error in deleting the user!', 'error');
        }
    }
}
</script>

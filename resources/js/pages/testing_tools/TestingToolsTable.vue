<template>
    <Head title="Testing Tools" />

    <AdminLayout>
        <div class="p-6 bg-gray-50 min-h-screen">
            <!-- Dashboard Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Testing Tools Table</h1>
                <p class="text-gray-500">Manage testing tools and their details.</p>
            </div>

            <!-- Table Controls -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3">
                <div class="flex w-full md:w-1/2 gap-4 items-center">
                    <!-- Search -->
                    <div class="relative w-full">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search teams..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                        <span class="absolute right-3 top-2.5 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.65 6.65a7.5 7.5 0 016.5 9.5z" />
                            </svg>
                        </span>
                    </div>

                    <!-- Rows per page selector -->
                    <div class="flex items-center gap-2 min-w-max">
                        <select v-model="rowsPerPage" class="border border-gray-300 rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>

                <!-- Add Team Button -->
                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition"
                    @click="go"
                >
                    + Add Testing Tool
                </button>
            </div>

            <!-- Teams Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <BaseTable
                    :columns="columns"
                    :data="paginatedTeams"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :on-next-page="nextPage"
                    :on-prev-page="prevPage"
                    :per-page="pagination.per_page"
                >
                    <template #testing_team="{ row }">
                        <div>
                            <div v-if="row.testing_team">
                                <div class="font-semibold">{{ row.testing_team.team_name }}</div>
                                <div class="text-xs text-gray-500">Team Size: {{ row.testing_team.team_size }}</div>
                                <div class="text-xs text-gray-500">Specialization: {{ row.testing_team.specialization }}</div>
                                <div v-if="row.testing_team.manager" class="mt-1">
                                    <span class="font-medium">Manager:</span> {{ row.testing_team.manager.manager_name }}
                                    <span class="text-xs text-gray-400">({{ row.testing_team.manager.expertise_area }})</span>
                                </div>
                            </div>
                            <div v-else class="text-gray-400 italic">No Team</div>
                        </div>
                    </template>
                    <template #actions="{ row }">
                        <button @click="editTeam(row.testing_tool_id)" class="px-2 py-1 text-xs font-semibold bg-yellow-500 text-white rounded shadow hover:bg-yellow-600 transition">Edit</button>
                        <button @click="deleteTeam(row.testing_tool_id)" class="px-2 py-1 text-xs font-semibold bg-red-500 text-white rounded shadow hover:bg-red-600 transition">Delete</button>
                    </template>
                </BaseTable>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-gray-500/40 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
                <h2 class="text-xl font-semibold mb-4">Edit Testing Tool</h2>

                <form @submit.prevent="updateTeam(editToolData.testing_tool_id)">
                    <!-- Team Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Testing Tool Name</label>
                        <input 
                            type="text" 
                            v-model="editToolData.testing_tool_name" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                            required 
                        />
                    </div>

                    <!-- Team Size -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tool Version</label>
                        <input 
                            type="text" 
                            v-model="editToolData.license_key" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                        />
                    </div>

                    <!-- Testing Team -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Testing Team</label>
                        <Multiselect
                            v-model="editToolData.testing_team"
                            :options="testingTeams"
                            label="team_name"
                            track-by="testing_team_id"
                            placeholder="Select a testing team"
                        >
                            <template #option="{ option }">
                                {{ option.team_name }} - ({{ option.specialization }})
                            </template>
                        </Multiselect>

                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 transition" @click="handleCloseEditModal()">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { ref, computed, watch, onMounted } from "vue";
import BaseTable from "@/components/BaseTable.vue";
import axios from "axios";
import { toast } from "@/stores/ToastStore";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";

// Table & Pagination
const search = ref("");
const columns = [
  { key: 'testing_tool_name', label: 'Testing Tool Name' },
  { key: 'license_key', label: 'License Key' },
  { key: 'testing_team', label: 'Testing Team & Manager' },
];

const testingTools = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
const rowsPerPage = ref(10);
const currentPage = ref(1);

const fetchTools = async () => {
  try {
    const res = await axios.get(route('admin.testing-tools.list'), {
      params: { search: search.value, page: currentPage.value, per_page: rowsPerPage.value }
    });
    if (res.data.result) {
      testingTools.value = res.data.data;
      pagination.value = res.data.pagination;
    }
  } catch (err) { console.error(err); }
};

onMounted(fetchTools);

watch([search, rowsPerPage], () => {
  currentPage.value = 1;
  fetchTools();
});

const nextPage = () => { if (currentPage.value < pagination.value.last_page) { currentPage.value++; fetchTools(); }};
const prevPage = () => { if (currentPage.value > 1) { currentPage.value--; fetchTools(); }};

const paginatedTeams = computed(() => testingTools.value);
const totalPages = computed(() => pagination.value.last_page);

// Edit Modal
const testingTeams = ref([]);
const showEditModal = ref(false);
const editToolData = ref(null);

const handleCloseEditModal = () => { showEditModal.value = false; editToolData.value = null; };

const editTeam = async (testing_tool_id) => {
  try {
    const res = await axios.get(route('admin.testing-tools.edit', { testing_tool_id }));
    if (res.data.result === true) {
        const { testing_tool, testing_teams } = res.data.data;

        testingTeams.value = testing_teams; 

        editToolData.value = {
            testing_tool_id: testing_tool.testing_tool_id,
            testing_tool_name: testing_tool.testing_tool_name,
            license_key: testing_tool.license_key,
            testing_team: testing_teams.find(t => t.testing_team_id === testing_tool.testing_team_id) || null,
        };

        showEditModal.value = true;
    }
  } catch (err) {
    console.error(err);
  }
};


const updateTeam = async (testing_tool_id) => {
  try {
    const payload = {
        ...editToolData.value,
        testing_team_id: editToolData.value.testing_team.testing_team_id,
    };
    const res = await axios.post(route('admin.testing-tools.update', { testing_tool_id }), payload);
    if (res.data.result) {
      handleCloseEditModal();
      toast.show("Testing Tool updated successfully!", "success");
      fetchTools();
    } else {
      toast.show("Error updating tool!", "error");
    }
  } catch (err) {
    toast.show("Error updating tool!", "error");
    console.error(err);
  }
};

const deleteTeam = async (testing_tool_id) => {
  if (!confirm('Are you sure you want to delete this testing tool?')) return;

  try {
    const res = await axios.post(route('admin.testing-tools.destroy', { testing_tool_id }));
    if (res.data.result) {
      toast.show("Testing Tool deleted successfully!", "success");
      fetchTools();
    } else {
      toast.show("Error deleting testing tool!", "error");
    }
  } catch (err) {
    toast.show("Error deleting testing tool!", "error");
    console.error(err);
  }
};

const go = () => {
  router.get(route('admin.testing-tools.create'));
};
</script>

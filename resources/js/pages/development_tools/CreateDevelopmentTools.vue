<template>
    <Head title="Create Development Tool" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Development Tool</h1>
                    <p class="mt-1 text-gray-500">Fill out the form below to add a new development tool.</p>
                </div>
                <!-- Back Button -->
                <button
                    @click="goBack"
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-gray-700 shadow-sm transition hover:bg-gray-200"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <!-- Development Tool Creation Form -->
            <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-lg">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Tool Name -->
                        <div>
                            <label for="team_name" class="mb-1 block text-sm font-semibold text-gray-700">Team Name</label>
                            <input
                                v-model="newTool.tool_name"
                                type="text"
                                id="tool_name"
                                placeholder="Enter tool name"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- Tool Version -->
                        <div>
                            <label for="team_size" class="mb-1 block text-sm font-semibold text-gray-700">Team Size</label>
                            <input
                                v-model="newTool.tool_version"
                                type="text"
                                id="tool_version"
                                placeholder="Enter tool version"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- License Expiry Date -->
                        <div>
                            <label for="specialization" class="mb-1 block text-sm font-semibold text-gray-700">Specialization</label>
                            <input
                                v-model="newTool.license_expiry_date"
                                type="date"
                                id="license_expiry_date"
                                placeholder="Select license expiry date"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Assign Manager (Searchable) -->
                        <div>
                            <label for="manager_id" class="mb-1 block text-sm font-semibold text-gray-700">Assign Team</label>
                            <Multiselect
                                v-model="selectedTeam"
                                :options="development_teams"
                                label="team_name"
                                track-by="team_id"
                                :searchable="true"
                                placeholder="Search and select a team"
                                @search-change="fetchTeams"
                                class="w-full"
                                required
                            >
                                <template #option="{ option }"> {{ option.team_name }} - {{ option.manager.manager_name }} </template>
                            </Multiselect>
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
import 'vue-multiselect/dist/vue-multiselect.min.css';

const newTool = ref({
    tool_name: '',
    tool_version: '',
    team_id: '',
    license_expiry_date: '',
});

const development_teams = ref([]);
const selectedTeam = ref(null);

const fetchTeams = async (search = '') => {
    try {
        const response = await axios.get(route('admin.development-teams.selectList'), {
            params: { search },
        });
        development_teams.value = response.data.data;
    } catch (error) {
        console.error('Error fetching managers:', error);
    }
};

onMounted(() => {
    fetchTeams();
});

// keep manager_id synced with selected manager
watch(selectedTeam, (val) => {
    newTool.value.team_id = val ? val.team_id : '';
});

const submit = async () => {
    const route_url = route('admin.development-tools.store');
    try {
        const response = await axios.post(route_url, newTool.value);
        if (response.data.result === true) {
            console.log(response.data.message);
            newTool.value = {
                tool_name: '',
                tool_version: '',
                license_expiry_date: '',
            };
            toast.show('Development Tool added successfully!', 'success');
        } else {
            toast.show('Development Tool adding error!', 'error');
        }
    } catch (error) {
        console.log(error);
    }
};

function goBack() {
    window.history.back();
}
</script>

<template>
    <Head title="Create Testing Tool" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Testing Tool</h1>
                    <p class="mt-1 text-gray-500">Fill out the form below to add a new testing tool.</p>
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
                            <label for="testing_tool_name" class="mb-1 block text-sm font-semibold text-gray-700">Team Name</label>
                            <input
                                v-model="newTool.testing_tool_name"
                                type="text"
                                id="testing_tool_name"
                                placeholder="Enter tool name"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            <p v-if="errors.testing_tool_name" class="mt-1 text-sm text-red-600">{{ errors.testing_tool_name[0] }}</p>
                        </div>

                        <!-- License Key -->
                        <div>
                            <label for="license_key" class="mb-1 block text-sm font-semibold text-gray-700">Specialization</label>
                            <input
                                v-model="newTool.license_key"
                                type="text"
                                id="license_key"
                                placeholder="Enter license key"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            <p v-if="errors.license_key" class="mt-1 text-sm text-red-600">{{ errors.license_key[0] }}</p>
                        </div>

                        <!-- Assign Testing Team (Searchable) -->
                        <div>
                            <label for="testing_team_id" class="mb-1 block text-sm font-semibold text-gray-700">Assign Manager</label>
                            <Multiselect
                                v-model="selectedTestingTeam"
                                :options="testingTeam"
                                label="team_name"
                                track-by="testing_team_id"
                                :searchable="true"
                                placeholder="Search and select a testing team"
                                @search-change="fetchTestingTeam"
                                class="w-full"
                                required
                            >
                                <template #option="{ option }"> {{ option.team_name }} - ({{ option.manager.manager_name }}) </template>
                            </Multiselect>
                            <p v-if="errors.testing_team_id" class="mt-1 text-sm text-red-600">{{ errors.testing_team_id[0] }}</p>
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

// vue-multiselect
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';

const newTool = ref({
    testing_tool_name: '',
    testing_team_id: '',
    license_key: '',
});
const errors = ref({});
const testingTeam = ref([]);
const selectedTestingTeam = ref(null);

const fetchTestingTeam = async (search = '') => {
    try {
        const response = await axios.get(route('admin.testing-teams.selectList'), {
            params: { search },
        });
        testingTeam.value = response.data.data;
    } catch (error) {
        console.error('Error fetching testingTeam:', error);
    }
};
onMounted(() => {
    fetchTestingTeam();
});

// keep testing_team_id synced with selected manager
watch(selectedTestingTeam, (val) => {
    newTool.value.testing_team_id = val ? val.testing_team_id : '';
});

const submit = async () => {
    const route_url = route('admin.testing-tools.store');
    try {
        const response = await axios.post(route_url, newTool.value);
        if (response.data.result === true) {
            console.log(response.data.message);
            newTool.value = {
                testing_tool_name: '',
                testing_team_id: '',
                license_key: '',
            };
            toast.show('Testing Tool added successfully!', 'success');
        } else {
            toast.show('Testing Tool adding error!', 'error');
        }
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            console.error(error);
            toast.show('An unexpected error occurred.', 'error');
        }
    }
};

function goBack() {
    window.history.back();
}
</script>

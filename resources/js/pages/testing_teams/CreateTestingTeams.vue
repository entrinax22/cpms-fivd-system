<template>
    <Head title="Create Testing Team" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Testing Team</h1>
                    <p class="mt-1 text-gray-500">Fill out the form below to add a new testing team.</p>
                </div>
                <!-- Back Button -->
                <button
                    @click="goBack"
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-gray-700 shadow-sm transition hover:bg-gray-200"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <!-- Development Team Creation Form -->
            <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-lg">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Team Name -->
                        <div>
                            <label for="team_name" class="mb-1 block text-sm font-semibold text-gray-700">Team Name</label>
                            <input
                                v-model="newTestingTeam.team_name"
                                type="text"
                                id="team_name"
                                placeholder="Enter team name"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            <p v-if="errors.team_name" class="mt-1 text-sm text-red-600">{{ errors.team_name[0] }}</p>
                        </div>

                        <!-- Team Size -->
                        <div>
                            <label for="team_size" class="mb-1 block text-sm font-semibold text-gray-700">Team Size</label>
                            <input
                                v-model="newTestingTeam.team_size"
                                type="number"
                                id="team_size"
                                min="1"
                                placeholder="Enter number of members"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            <p v-if="errors.team_size" class="mt-1 text-sm text-red-600">{{ errors.team_size[0] }}</p>
                        </div>

                        <!-- Specialization -->
                        <div>
                            <label for="specialization" class="mb-1 block text-sm font-semibold text-gray-700">Specialization</label>
                            <input
                                v-model="newTestingTeam.specialization"
                                type="text"
                                id="specialization"
                                placeholder="e.g. Civil Works, Electrical, IT Systems"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            <p v-if="errors.specialization" class="mt-1 text-sm text-red-600">{{ errors.specialization[0] }}</p>
                        </div>

                        <!-- Assign Manager (Searchable) -->
                        <div>
                            <label for="manager_id" class="mb-1 block text-sm font-semibold text-gray-700">Assign Manager</label>
                            <Multiselect
                                v-model="selectedManager"
                                :options="managers"
                                label="manager_name"
                                track-by="manager_id"
                                :searchable="true"
                                placeholder="Search and select a manager"
                                @search-change="fetchManagers"
                                class="w-full"
                                required
                            >
                                <template #option="{ option }"> {{ option.manager_name }} - ({{ option.expertise_area }}) </template>
                            </Multiselect>
                            <p v-if="errors.manager_id" class="mt-1 text-sm text-red-600">{{ errors.manager_id[0] }}</p>
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

const newTestingTeam = ref({
    team_name: '',
    team_size: '',
    specialization: '',
    manager_id: '',
});
const errors = ref({});
const managers = ref([]);
const selectedManager = ref(null);

const fetchManagers = async (search = '') => {
    try {
        const response = await axios.get(route('admin.project-managers.selectList'), {
            params: { search },
        });
        managers.value = response.data.data;
    } catch (error) {
        console.error('Error fetching managers:', error);
    }
};
onMounted(() => {
    fetchManagers();
});

// keep manager_id synced with selected manager
watch(selectedManager, (val) => {
    newTestingTeam.value.manager_id = val ? val.manager_id : '';
});

const submit = async () => {
    const route_url = route('admin.testing-teams.store');
    try {
        const response = await axios.post(route_url, newTestingTeam.value);
        if (response.data.result === true) {
            console.log(response.data.message);
            newTestingTeam.value = {
                team_name: '',
                team_size: '',
                specialization: '',
                manager_id: '',
            };
            selectedManager.value = null;
            toast.show('Testing Team added successfully!', 'success');
        } else {
            toast.show('Testing Team adding error!', 'error');
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

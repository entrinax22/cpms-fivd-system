<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Dashboard Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create User</h1>
                    <p class="mt-1 text-gray-500">Fill out the form below to add a new user.</p>
                </div>
                <!-- Back Button -->
                <button
                    @click="goBack"
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-gray-700 shadow-sm transition hover:bg-gray-200"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <!-- User Creation Form -->
            <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-lg">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Name</label>
                            <input
                                v-model="newUser.name"
                                type="text"
                                id="name"
                                placeholder="Enter full name"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                        </div>
                        <!-- Email -->
                        <div>
                            <label for="email" class="mb-1 block text-sm font-semibold text-gray-700">Email</label>
                            <input
                                v-model="newUser.email"
                                type="email"
                                id="email"
                                placeholder="email@example.com"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
                        </div>
                        <!--Phone-->
                        <div>
                            <label for="phone" class="mb-1 block text-sm font-semibold text-gray-700">Phone</label>
                            <input
                                v-model="newUser.phone"
                                type="text"
                                id="phone"
                                placeholder="Enter phone number"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</p>
                        </div>
                        <!-- Role -->
                        <div>
                            <label for="role" class="mb-1 block text-sm font-semibold text-gray-700">Role</label>
                            <select
                                v-model="newUser.role"
                                id="role"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value="" disabled>Select role</option>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                                <option value="engineer">Engineer</option>
                            </select>
                            <p v-if="errors.role" class="mt-1 text-sm text-red-600">{{ errors.role[0] }}</p>
                        </div>
                        <!-- Development Teams -->
                        <div>
                            <label for="development_teams" class="mb-1 block text-sm font-semibold text-gray-700">Development Teams</label>
                            <Multiselect
                                v-model="selectedDevTeams"
                                :options="devTeams"
                                label="team_name"
                                track-by="team_id"
                                :multiple="true"
                                :searchable="true"
                                placeholder="Search and select development teams"
                                class="w-full"
                            />
                            <p class="mt-1 text-xs text-gray-400">(Use the dropdown to search & select multiple)</p>
                            <p v-if="errors.development_team_ids" class="mt-1 text-sm text-red-600">
                                {{ errors.development_team_ids[0] }}
                            </p>
                        </div>

                        <!-- Testing Teams -->
                        <div>
                            <label for="testing_teams" class="mb-1 block text-sm font-semibold text-gray-700">Testing Teams</label>
                            <Multiselect
                                v-model="selectedTestTeams"
                                :options="testTeams"
                                label="team_name"
                                track-by="testing_team_id"
                                :multiple="true"
                                :searchable="true"
                                placeholder="Search and select testing teams"
                                class="w-full"
                            />
                            <p class="mt-1 text-xs text-gray-400">(Use the dropdown to search & select multiple)</p>
                            <p v-if="errors.testing_team_ids" class="mt-1 text-sm text-red-600">
                                {{ errors.testing_team_ids[0] }}
                            </p>
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
import { ref, watch } from 'vue';

// vue-multiselect
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';

const newUser = ref({
    name: '',
    email: '',
    role: '',
    phone: '',
    development_team_ids: [],
    testing_team_ids: [],
});
const errors = ref({});
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
        if (devResp.data.result === true) devTeams.value = devResp.data.data;
        if (testResp.data.result === true) testTeams.value = testResp.data.data;
    } catch (e) {
        console.error('Error fetching team lists', e);
    }
};

fetchTeamLists();

// sync selected objects into encrypted id arrays for backend
watch(selectedDevTeams, (val) => {
    newUser.value.development_team_ids = val && val.length ? val.map((v) => v.team_id) : [];
});

watch(selectedTestTeams, (val) => {
    newUser.value.testing_team_ids = val && val.length ? val.map((v) => v.testing_team_id) : [];
});

const submit = async () => {
    const route_url = route('admin.users.store');
    try {
        const response = await axios.post(route_url, newUser.value);
        if (response.data.result === true) {
            console.log(response.data.message);
            newUser.value = {
                name: '',
                email: '',
                phone: '',
                role: '',
                development_team_ids: [],
                testing_team_ids: [],
            };
            toast.show('User added successfully!', 'success');
        } else {
            toast.show('User adding error!', 'error');
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

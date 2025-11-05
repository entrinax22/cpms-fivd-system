<template>
    <Head title="Create Requested Tool" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Requested Tool</h1>
                    <p class="mt-1 text-gray-500">Fill out the form below to add a new project.</p>
                </div>
                <button
                    @click="goBack"
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-gray-700 shadow-sm transition hover:bg-gray-200"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <!-- Project Creation Form -->
            <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-lg">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Project Name -->
                        <div>
                            <label for="project_name" class="mb-1 block text-sm font-semibold text-gray-700"> Project Name </label>
                            <input
                                v-model="project.project_name"
                                type="text"
                                id="project_name"
                                placeholder="e.g. Building Renovation"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- Client -->
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Client</label>
                            <Multiselect
                                v-model="selectedClient"
                                :options="clients"
                                label="client_name"
                                track-by="client_id"
                                placeholder="Select a client"
                                @search-change="fetchClients"
                                class="w-full"
                                :clear-on-select="true"
                                :allow-empty="true"
                            >
                                <template #option="{ option }">
                                    {{ option.client_name }}
                                </template>
                            </Multiselect>
                        </div>

                        <!-- Manager -->
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Project Manager</label>
                            <Multiselect
                                v-model="selectedManager"
                                :options="managers"
                                label="manager_name"
                                track-by="manager_id"
                                placeholder="Select a project manager"
                                @search-change="fetchManagers"
                                class="w-full"
                                :clear-on-select="true"
                                :allow-empty="true"
                            >
                                <template #option="{ option }"> {{ option.manager_name }} - {{ option.expertise_area }} </template>
                            </Multiselect>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="mb-1 block text-sm font-semibold text-gray-700"> Start Date </label>
                            <input
                                v-model="project.start_date"
                                type="date"
                                id="start_date"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- Estimated End Date -->
                        <div>
                            <label for="estimated_end_date" class="mb-1 block text-sm font-semibold text-gray-700"> Estimated End Date </label>
                            <input
                                v-model="project.estimated_end_date"
                                type="date"
                                id="estimated_end_date"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                            <select
                                v-model="project.status"
                                id="status"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
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

                    <!-- Project Description -->
                    <div>
                        <label for="project_description" class="mb-1 block text-sm font-semibold text-gray-700"> Project Description </label>
                        <textarea
                            v-model="project.project_description"
                            id="project_description"
                            rows="4"
                            placeholder="Enter project details..."
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        ></textarea>
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
import { ref } from 'process';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

const projects = ref([]);
const developmentTools = ref([]);
const testingTools = ref([]);

const fetchLists = async () => {
    try {
        const [projectRes, developmentToolRes, testingToolRes] = await Promise.all([
            axios.get(route('admin.projects.selectList')),
            axios.get('/api/development-tools'),
            axios.get(route('admin.testing-tools.selectList')),
        ]);

        if (projectRes.data.result === true && developmentToolRes.data.result === true && testingToolRes.data.result === true) {
            projects.value = projectRes.data.projects;
            developmentTools.value = developmentToolRes.data.development_tools;
            testingTools.value = testingToolRes.data.testing_tools;

            projects.value = projects.value.map((project) => ({
                ...project,
                display: `${project.project_name} (${project.client_name})`,
            }));
        } else {
            toast.show('Failed to load data. Please try again.', 'error');
        }
    } catch (error) {
        toast.show(`Failed to load data. Please try again. ${error.message}`, 'error');
    }
};

// back button
function goBack() {
    window.history.back();
}
</script>

<template>
    <Head title="Create Project" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Project</h1>
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
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

const project = ref({
    project_name: '',
    client_id: null,
    manager_id: null,
    start_date: '',
    estimated_end_date: '',
    project_description: '',
    status: '',
});

// dropdown data
const clients = ref([]);
const managers = ref([]);
const selectedClient = ref(null);
const selectedManager = ref(null);

// fetch client list
const fetchClients = async (search = '') => {
    const route_url = route('admin.clients.selectList');
    try {
        const response = await axios.get(route_url, { params: { search } });
        clients.value = response.data.data ?? [];
    } catch {
        clients.value = [];
    }
};

// fetch manager list
const fetchManagers = async (search = '') => {
    const route_url = route('admin.project-managers.selectList');
    try {
        const response = await axios.get(route_url, { params: { search } });
        managers.value = response.data.data ?? [];
    } catch {
        managers.value = [];
    }
};

// set encrypted IDs on select
watch(selectedClient, (val) => {
    project.value.client_id = val ? val.client_id : null;
});

watch(selectedManager, (val) => {
    project.value.manager_id = val ? val.manager_id : null;
});

// initial load
onMounted(() => {
    fetchClients();
    fetchManagers();
});

// submit form
const submit = async () => {
    const route_url = route('admin.projects.store');
    try {
        const response = await axios.post(route_url, project.value);
        if (response.data.result === true) {
            toast.show('Project created successfully!', 'success');
            project.value = {
                project_name: '',
                client_id: null,
                manager_id: null,
                start_date: '',
                estimated_end_date: '',
                project_description: '',
                status: '',
            };
            selectedClient.value = null;
            selectedManager.value = null;
        } else {
            toast.show(response.data.message || 'Failed to create project', 'error');
        }
    } catch (error) {
        console.error(error);
        toast.show('Error occurred while creating project', 'error');
    }
};

// back button
function goBack() {
    window.history.back();
}
</script>

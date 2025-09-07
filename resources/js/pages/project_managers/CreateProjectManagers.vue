<template>
    <Head title="Create Project Manager" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Dashboard Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Project Manager</h1>
                    <p class="mt-1 text-gray-500">Fill out the form below to add a new project manager.</p>
                </div>
                <!-- Back Button -->
                <button
                    @click="goBack"
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-gray-700 shadow-sm transition hover:bg-gray-200"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <!-- Project Manager Creation Form -->
            <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-lg">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Expertise Area -->
                        <div>
                            <label for="expertise_area" class="mb-1 block text-sm font-semibold text-gray-700">Expertise Area</label>
                            <input
                                v-model="newManager.expertise_area"
                                type="text"
                                id="expertise_area"
                                placeholder="e.g. Civil Engineering, IT Systems"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- Contact Information -->
                        <div>
                            <label for="contact_information" class="mb-1 block text-sm font-semibold text-gray-700">Contact Information</label>
                            <input
                                v-model="newManager.contact_information"
                                type="text"
                                id="contact_information"
                                placeholder="Phone or Email"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- Years of Experience -->
                        <div>
                            <label for="years_of_experience" class="mb-1 block text-sm font-semibold text-gray-700">Years of Experience</label>
                            <input
                                v-model="newManager.years_of_experience"
                                type="number"
                                id="years_of_experience"
                                min="0"
                                placeholder="Enter years"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- Related User -->
                        <div>
                            <label for="users_id" class="mb-1 block text-sm font-semibold text-gray-700">Assign User</label>
                            <Multiselect
                                v-model="selectedUser"
                                :options="users"
                                label="name"
                                track-by="id"
                                placeholder="Search and select a user"
                                @search-change="fetchUsers"
                                class="w-full"
                                :clear-on-select="true"
                                :allow-empty="true"
                            >
                                <template #option="{ option }"> {{ option.name }} - {{ option.role ? option.role.toUpperCase() : '' }} </template>
                                <template #singleLabel="{ option }"> {{ option.name }} - {{ option.role ? option.role.toUpperCase() : '' }}</template>
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

// ✅ use vue-multiselect instead of vue-select
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

const newManager = ref({
    manager_name: '',
    expertise_area: '',
    contact_information: '',
    years_of_experience: '',
    user_id: null,
});

const users = ref([]);
const selectedUser = ref(null);

const fetchUsers = async (search = '') => {
    const route_url = route('admin.users.select');
    try {
        const response = await axios.get(route_url, { params: { search } });
        users.value = response.data.data ?? response.data;
    } catch (error) {
        users.value = [];
    }
};

onMounted(() => {
    fetchUsers();
});

watch(selectedUser, (val) => {
    newManager.value.user_id = val ? val.id : null;
});

const submit = async () => {
    const route_url = route('admin.project-managers.store');
    try {
        const response = await axios.post(route_url, newManager.value);
        if (response.data.result === true) {
            console.log(response.data.message);
            // reset form
            newManager.value = {
                manager_name: '',
                expertise_area: '',
                contact_information: '',
                years_of_experience: '',
                user_id: null,
            };
            selectedUser.value = null;
            toast.show('Project Manager added successfully!', 'success');
        } else {
            toast.show('Project Manager adding error!', 'error');
        }
    } catch (error) {
        console.log(error);
    }
};

function goBack() {
    window.history.back();
}
</script>

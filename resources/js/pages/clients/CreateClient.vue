<template>
    <Head title="Create Client" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Dashboard Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Client</h1>
                    <p class="mt-1 text-gray-500">Fill out the form below to add a new client.</p>
                </div>
                <!-- Back Button -->
                <button
                    @click="goBack"
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-gray-700 shadow-sm transition hover:bg-gray-200"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <!-- Client Creation Form -->
            <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-lg">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Client Name -->
                        <div>
                            <label for="client_name" class="mb-1 block text-sm font-semibold text-gray-700">Client Name</label>
                            <input
                                v-model="newClient.client_name"
                                type="text"
                                id="client_name"
                                placeholder="Enter client name"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- Contact Information -->
                        <div>
                            <label for="contact_information" class="mb-1 block text-sm font-semibold text-gray-700">Contact Information</label>
                            <input
                                v-model="newClient.contact_information"
                                type="text"
                                id="contact_information"
                                placeholder="Phone"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!--Client Type-->
                        <div>
                            <label for="client_type" class="mb-1 block text-sm font-semibold text-gray-700">Contact Type</label>
                            <input
                                v-model="newClient.client_type"
                                type="text"
                                id="client_type"
                                placeholder="Enter client type"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <!-- Registration Date -->
                        <div>
                            <label for="registration_date" class="mb-1 block text-sm font-semibold text-gray-700">Registration Date</label>
                            <input
                                v-model="newClient.registration_date"
                                type="date"
                                id="registration_date"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
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
import { ref } from 'vue';

// ✅ use vue-multiselect instead of vue-select
import 'vue-multiselect/dist/vue-multiselect.css';

const newClient = ref({
    client_name: '',
    contact_information: '',
    registration_date: '',
    client_type: '',
});

const submit = async () => {
    const route_url = route('admin.clients.store');
    try {
        const response = await axios.post(route_url, newClient.value);
        if (response.data.result === true) {
            console.log(response.data.message);
            // reset form
            newClient.value = {
                client_name: '',
                contact_information: '',
                registration_date: '',
                client_type: '',
            };
            toast.show('Client added successfully!', 'success');
        } else {
            toast.show('Client adding error!', 'error');
        }
    } catch (error) {
        console.log(error);
    }
};

function goBack() {
    window.history.back();
}
</script>

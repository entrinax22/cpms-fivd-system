<template>
    <Head title="Create Project Manager" />

    <AdminLayout>
        <div class="p-6 bg-gray-50 min-h-screen">
            <!-- Dashboard Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Project Manager</h1>
                    <p class="text-gray-500 mt-1">Fill out the form below to add a new project manager.</p>
                </div>
                <!-- Back Button -->
                <button 
                    @click="goBack" 
                    class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow-sm hover:bg-gray-200 transition"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <!-- Project Manager Creation Form -->
            <div class="bg-white shadow-lg rounded-xl p-8 border border-gray-100">
                <form @submit.prevent="submitAddManager" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Manager Name -->
                        <div>
                            <label for="manager_name" class="block text-sm font-semibold text-gray-700 mb-1">Manager Name</label>
                            <input 
                                v-model="newManager.manager_name" 
                                type="text" 
                                id="manager_name" 
                                placeholder="Enter manager full name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required
                            />
                        </div>

                        <!-- Expertise Area -->
                        <div>
                            <label for="expertise_area" class="block text-sm font-semibold text-gray-700 mb-1">Expertise Area</label>
                            <input 
                                v-model="newManager.expertise_area" 
                                type="text" 
                                id="expertise_area" 
                                placeholder="e.g. Civil Engineering, IT Systems"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required
                            />
                        </div>

                        <!-- Contact Information -->
                        <div>
                            <label for="contact_information" class="block text-sm font-semibold text-gray-700 mb-1">Contact Information</label>
                            <input 
                                v-model="newManager.contact_information" 
                                type="text" 
                                id="contact_information" 
                                placeholder="Phone or Email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required
                            />
                        </div>

                        <!-- Years of Experience -->
                        <div>
                            <label for="years_of_experience" class="block text-sm font-semibold text-gray-700 mb-1">Years of Experience</label>
                            <input 
                                v-model="newManager.years_of_experience" 
                                type="number" 
                                id="years_of_experience" 
                                min="0"
                                placeholder="Enter years"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required
                            />
                        </div>

                        <!-- Related User -->
                        <!-- <div>
                            <label for="users_id" class="block text-sm font-semibold text-gray-700 mb-1">Assign User</label>
                            <Multiselect
                                v-model="selectedUser"
                                :options="users"
                                label="name"
                                track-by="id"
                                :searchable="true"
                                placeholder="Search and select a user"
                                @search-change="fetchUsers"
                                class="w-full"
                                required
                            >
                                <template #option="{ option }">
                                    {{ option.name }} ({{ option.role }})
                                </template>
                            </Multiselect>
                        </div> -->
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button 
                            type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 transition"
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
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { toast } from "@/stores/ToastStore";

// // import vue-multiselect
// import Multiselect from "vue-multiselect";
// import "vue-multiselect/dist/vue-multiselect.min.css";

const newManager = ref({
    manager_name: "",
    expertise_area: "",
    contact_information: "",
    years_of_experience: ""
});

const submitAddManager = async () => {
    const route_url = route('admin.project-managers.store');
    try {
        const response = await axios.post(route_url, newManager.value);
        if (response.data.result === true) {
            console.log(response.data.message);
            // reset form
            newManager.value = {
                manager_name: "",
                expertise_area: "",
                contact_information: "",
                years_of_experience: ""
            };
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

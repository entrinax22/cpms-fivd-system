<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="p-6 bg-gray-50 min-h-screen">
            <!-- Dashboard Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create User</h1>
                    <p class="text-gray-500 mt-1">Fill out the form below to add a new user.</p>
                </div>
                <!-- Back Button -->
                <button 
                    @click="goBack" 
                    class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow-sm hover:bg-gray-200 transition"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <!-- User Creation Form -->
            <div class="bg-white shadow-lg rounded-xl p-8 border border-gray-100">
                <form @submit.prevent="submitAddUser" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Name</label>
                            <input 
                                v-model="newUser.name" 
                                type="text" 
                                id="name" 
                                placeholder="Enter full name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required
                            />
                        </div>
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                            <input 
                                v-model="newUser.email" 
                                type="email" 
                                id="email" 
                                placeholder="email@example.com"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required
                            />
                        </div>
                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
                            <select 
                                v-model="newUser.role" 
                                id="role" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required
                            >
                                <option value="" disabled>Select role</option>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
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
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { ref } from "vue";
import axios from "axios";
import { toast } from "@/stores/ToastStore";

const newUser = ref({
    name: "",
    email: "",
    role: ""
});

const submitAddUser = async () => {
    const route_url = route('admin.users.store');
    try {
        const response = await axios.post(route_url, newUser.value);
        if (response.data.result === true) {
            console.log(response.data.message);
            newUser.value = {
                name: "",
                email: "",
                role: ""
            };
            toast.show('User added successfully!', 'success');
        } else {
            toast.show('User adding error!', 'error');
        }
    } catch (error) {
        console.log(error);
    }
};

function goBack() {
    window.history.back();
}
</script>

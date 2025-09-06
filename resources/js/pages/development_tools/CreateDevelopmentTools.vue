<template>
    <Head title="Create Development Tool" />

    <AdminLayout>
        <div class="p-6 bg-gray-50 min-h-screen">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Development Tool</h1>
                    <p class="text-gray-500 mt-1">Fill out the form below to add a new development tool.</p>
                </div>
                <!-- Back Button -->
                <button 
                    @click="goBack" 
                    class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow-sm hover:bg-gray-200 transition"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <!-- Development Tool Creation Form -->
            <div class="bg-white shadow-lg rounded-xl p-8 border border-gray-100">
                <form @submit.prevent="submitAddTeam" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tool Name -->
                        <div>
                            <label for="team_name" class="block text-sm font-semibold text-gray-700 mb-1">Team Name</label>
                            <input 
                                v-model="newTool.tool_name" 
                                type="text" 
                                id="tool_name" 
                                placeholder="Enter tool name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required
                            />
                        </div>

                        <!-- Tool Version -->
                        <div>
                            <label for="team_size" class="block text-sm font-semibold text-gray-700 mb-1">Team Size</label>
                            <input 
                                v-model="newTool.tool_version" 
                                type="text" 
                                id="tool_version" 
                                placeholder="Enter tool version"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required
                            />
                        </div>

                        <!-- License Expiry Date -->
                        <div>
                            <label for="specialization" class="block text-sm font-semibold text-gray-700 mb-1">Specialization</label>
                            <input 
                                v-model="newTool.license_expiry_date" 
                                type="date" 
                                id="license_expiry_date" 
                                placeholder="Select license expiry date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            />
                        </div>

                        <!-- Assign Manager (Searchable) -->
                        <!-- <div>
                            <label for="manager_id" class="block text-sm font-semibold text-gray-700 mb-1">Assign Manager</label>
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
                                <template #option="{ option }">
                                    {{ option.manager_name }} - ({{ option.expertise_area }})
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
import { onMounted, ref, watch } from "vue";
import axios from "axios";
import { toast } from "@/stores/ToastStore";

// vue-multiselect
// import Multiselect from "vue-multiselect";
// import "vue-multiselect/dist/vue-multiselect.min.css";

const newTool = ref({
    tool_name: "",
    tool_version: "",
    license_expiry_date: "",
});

// const managers = ref([]);
// const selectedManager = ref(null);

// const fetchManagers = async (search = "") => {
//     try {
//         const response = await axios.get(route("admin.project-managers.selectList"), {
//             params: { search }
//         });
//         managers.value = response.data.data; 
//     } catch (error) {
//         console.error("Error fetching managers:", error);
//     }
// };
// onMounted(()=>{
//     fetchManagers();
// })

// keep manager_id synced with selected manager
// watch(selectedManager, (val) => {
//     newTeam.value.manager_id = val ? val.manager_id : "";
// });

const submitAddTeam = async () => {
    const route_url = route('admin.development-tools.store');
    try {
        const response = await axios.post(route_url, newTool.value);
        if (response.data.result === true) {
            console.log(response.data.message);
            newTool.value = {
                tool_name: "",
                tool_version: "",
                license_expiry_date: "",
            };
            toast.show("Development Tool added successfully!", "success");
        } else {
            toast.show("Development Tool adding error!", "error");
        }
    } catch (error) {
        console.log(error);
    }
};

function goBack() {
    window.history.back();
}
</script>

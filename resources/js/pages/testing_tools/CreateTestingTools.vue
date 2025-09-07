<template>
    <Head title="Create Testing Tool" />

    <AdminLayout>
        <div class="p-6 bg-gray-50 min-h-screen">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Create Testing Tool</h1>
                    <p class="text-gray-500 mt-1">Fill out the form below to add a new testing tool.</p>
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
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tool Name -->
                        <div>
                            <label for="testing_tool_name" class="block text-sm font-semibold text-gray-700 mb-1">Team Name</label>
                            <input 
                                v-model="newTool.testing_tool_name" 
                                type="text" 
                                id="testing_tool_name" 
                                placeholder="Enter tool name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required
                            />
                        </div>

                        <!-- License Key -->
                        <div>
                            <label for="license_key" class="block text-sm font-semibold text-gray-700 mb-1">Specialization</label>
                            <input 
                                v-model="newTool.license_key" 
                                type="text" 
                                id="license_key" 
                                placeholder="Enter license key"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            />
                        </div>

                        <!-- Assign Testing Team (Searchable) -->
                        <div>
                            <label for="testing_team_id" class="block text-sm font-semibold text-gray-700 mb-1">Assign Manager</label>
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
                                <template #option="{ option }">
                                    {{ option.team_name }} - ({{ option.manager.manager_name }})
                                </template>
                            </Multiselect>
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
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { onMounted, ref, watch } from "vue";
import axios from "axios";
import { toast } from "@/stores/ToastStore";

// vue-multiselect
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";

const newTool = ref({
    testing_tool_name: "",
    testing_team_id: "",
    license_key: "",
});

const testingTeam = ref([]);
const selectedTestingTeam = ref(null);

const fetchTestingTeam = async (search = "") => {
    try {
        const response = await axios.get(route("admin.testing-teams.selectList"), {
            params: { search }
        });
        testingTeam.value = response.data.data; 
    } catch (error) {
        console.error("Error fetching testingTeam:", error);
    }
};
onMounted(()=>{
    fetchTestingTeam();
})

// keep testing_team_id synced with selected manager
watch(selectedTestingTeam, (val) => {
    newTool.value.testing_team_id = val ? val.testing_team_id : "";
});

const submit = async () => {
    const route_url = route('admin.testing-tools.store');
    try {
        const response = await axios.post(route_url, newTool.value);
        if (response.data.result === true) {
            console.log(response.data.message);
            newTool.value = {
                testing_tool_name: "",
                testing_team_id: "",
                license_key: "",
            };
            toast.show("Testing Tool added successfully!", "success");
        } else {
            toast.show("Testing Tool adding error!", "error");
        }
    } catch (error) {
        console.log(error);
    }
};

function goBack() {
    window.history.back();
}
</script>

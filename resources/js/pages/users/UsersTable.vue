<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="p-6 bg-gray-50 min-h-screen">
            <!-- Dashboard Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Users Table</h1>
                <p class="text-gray-500">Manage employees, their details, and actions.</p>
            </div>

            <!-- Table Controls -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3">
                <!-- Search & Rows per page grouped -->
                <div class="flex w-full md:w-1/2 gap-4 items-center">
                    <!-- Search -->
                    <div class="relative w-full">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search users..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                        <span class="absolute right-3 top-2.5 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.65 6.65a7.5 7.5 0 016.5 9.5z" />
                            </svg>
                        </span>
                    </div>
                    <!-- Rows per page selector -->
                    <div class="flex items-center gap-2 min-w-max">
                        <select id="rowsPerPage" v-model="rowsPerPage" class="border border-gray-300 rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>
                <!-- Add User Button -->
                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition"
                    @click="addUser"
                >
                    + Add User
                </button>
            </div>

            <!-- Users Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <BaseTable
                    :columns="columns"
                    :data="paginatedUsers"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :on-next-page="nextPage"
                    :on-prev-page="prevPage"
                    :per-page="pagination.per_page"
                >
                    <template #actions="{ row }">
                        <button @click="editUser(row.id)" class="px-2 py-1 text-xs font-semibold bg-yellow-500 text-white rounded shadow hover:bg-yellow-600 transition">Edit</button>
                        <button @click="deleteUser(row.id)" class="px-2 py-1 text-xs font-semibold bg-red-500 text-white rounded shadow hover:bg-red-600 transition">Delete</button>
                    </template>
                </BaseTable>
            </div>
        </div>

        <!-- Edit Modal -->
        <div 
            v-if="showEditModal" 
            class="fixed inset-0 bg-gray-500/40 backdrop-blur-sm flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
                <h2 class="text-xl font-semibold mb-4">Edit User</h2>

                <!-- Form -->
                <form @submit.prevent="updateUser(editUserData.id)">
                    <!-- Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input 
                            type="text" 
                            v-model="editUserData.name" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                            required 
                        />
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input 
                            type="email" 
                            v-model="editUserData.email" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                            required 
                        />
                    </div>

                    <!-- Role -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <select 
                            v-model="editUserData.role" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="admin">Admin</option>
                            <option value="employee">Employeessssssssssssss</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button 
                            type="button" 
                            class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 transition"
                            @click="handleCloseEditModal()"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition"
                        >
                            Save Changes
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
import { ref, computed, watch, onMounted } from "vue";
import BaseTable from "@/components/BaseTable.vue";
import axios from "axios";
import { toast } from "@/stores/ToastStore";

const search = ref("");
const columns = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role' },
];

const users = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
const rowsPerPage = ref(10);
const currentPage = ref(1);

async function fetch() {
  const route_url = route('admin.users.list');
  try {
    const response = await axios.get(route_url, {
      params: {
        search: search.value,
        page: currentPage.value,
        per_page: rowsPerPage.value,
      },
    });
    if (response.data.result === true) {
      users.value = response.data.data;
      pagination.value = response.data.pagination;
    } else {
      console.error('Failed to fetch users:', response.data.message);
    }
  } catch (error) {
    console.error('Error fetching users:', error);
  }
}

onMounted(fetch);

watch(search, () => {
  currentPage.value = 1;
  fetch();
});

watch(rowsPerPage, () => {
  currentPage.value = 1;
  fetch();
});

function nextPage() {
  if (currentPage.value < pagination.value.last_page) {
    currentPage.value++;
    fetch();
  }
}

function prevPage() {
  if (currentPage.value > 1) {
    currentPage.value--;
    fetch();
  }
}

const paginatedUsers = computed(() => users.value);
const totalPages = computed(() => pagination.value.last_page);

function addUser() {
  router.get(route('admin.users.create'));
}

const showEditModal = ref(false);
const editUserData = ref(null);
function handleOpenEditModal(user) {
    showEditModal.value = true;
    editUserData.value = { ...user };
}
function handleCloseEditModal() {
    showEditModal.value = false;
    editUserData.value = null; 
}
async function editUser(id) {
    const route_url = route('admin.users.edit', { id });
    try{
        const response = await axios.get(route_url);
        if (response.data.result === true) {
            handleOpenEditModal(response.data.data);
        } else {
            console.error('Failed to edit user:', response.data.message);
        }
    }catch(error) {
        console.error('Error editing user:', error);
    }
}

async function updateUser(id) {
    const route_url = route('admin.users.update', { id });
    try {
        const response = await axios.post(route_url, editUserData.value);
        if (response.data.result === true) {
            handleCloseEditModal();
            toast.show("User updated successfully!", "success");
            fetch(); // Refresh the user list after update
        } else {
            toast.show("Error in updating the user!", "error");
        }
    } catch (error) {
        toast.show(error, "error");
    }
}

async function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        const route_url = route('admin.users.destroy', { id });
        try {
            const response = await axios.post(route_url);
            if (response.data.result === true) {
                fetch(); // Refresh the user list after deletion
                toast.show("User deleted successfully!", "success");
            } else {
                toast.show("Error in deleting the user!", "error");
            }
        } catch (error) {
            toast.show("Error in deleting the user!", "error");
        }
    }
}

</script>

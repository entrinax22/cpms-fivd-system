<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="p-6 bg-gray-50 min-h-screen">
            <!-- Dashboard Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
                <p class="text-gray-500">Monitor projects, resources, and employee activities.</p>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 rounded-xl shadow">
                    <h2 class="text-gray-500 text-sm">Active Projects</h2>
                    <p class="text-3xl font-bold text-blue-600">12</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow">
                    <h2 class="text-gray-500 text-sm">Employees</h2>
                    <p class="text-3xl font-bold text-green-600">34</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow">
                    <h2 class="text-gray-500 text-sm">Resources in Use</h2>
                    <p class="text-3xl font-bold text-yellow-600">78%</p>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Project Progress -->
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Project Progress</h2>
                    <ul class="space-y-4">
                        <li class="flex items-center justify-between">
                            <span>Project A</span>
                            <div class="w-2/3 bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-600 h-3 rounded-full" style="width: 65%"></div>
                            </div>
                            <span class="ml-2 text-sm text-gray-500">65%</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Project B</span>
                            <div class="w-2/3 bg-gray-200 rounded-full h-3">
                                <div class="bg-green-600 h-3 rounded-full" style="width: 90%"></div>
                            </div>
                            <span class="ml-2 text-sm text-gray-500">90%</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Project C</span>
                            <div class="w-2/3 bg-gray-200 rounded-full h-3">
                                <div class="bg-yellow-500 h-3 rounded-full" style="width: 40%"></div>
                            </div>
                            <span class="ml-2 text-sm text-gray-500">40%</span>
                        </li>
                    </ul>
                </div>

                <!-- Employee Activity -->
                <div class="bg-white p-6 rounded-xl shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Employee Activity</h2>
                    <ul class="space-y-3">
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700">John Doe</span>
                            <span class="text-sm text-green-600">Online</span>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700">Jane Smith</span>
                            <span class="text-sm text-yellow-600">On Break</span>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700">Carlos Reyes</span>
                            <span class="text-sm text-red-600">Offline</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Resource Monitoring -->
            <div class="mt-6 bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Resource Monitoring</h2>
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Resource</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b">
                            <td class="px-4 py-2">Server A</td>
                            <td class="px-4 py-2 text-green-600">Active</td>
                            <td class="px-4 py-2">70%</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-2">Server B</td>
                            <td class="px-4 py-2 text-yellow-600">Maintenance</td>
                            <td class="px-4 py-2">40%</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2">Database</td>
                            <td class="px-4 py-2 text-green-600">Active</td>
                            <td class="px-4 py-2">82%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
</script>

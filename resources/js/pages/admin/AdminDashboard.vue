<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Dashboard Header -->
            <div class="mb-6">
                <h1 class="header text-3xl font-bold text-green-800">Admin Dashboard</h1>
                <p class="text-gray-500">Monitor projects, resources, and employee activities.</p>
            </div>

            <!-- Stats Overview -->
            <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="text-sm text-gray-500">Active Projects</h2>
                    <p class="text-3xl font-bold text-blue-600">12</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="text-sm text-gray-500">Employees</h2>
                    <p class="text-3xl font-bold text-green-600">34</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="text-sm text-gray-500">Resources in Use</h2>
                    <p class="text-3xl font-bold text-yellow-600">78%</p>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Project Progress -->
                <div class="rounded-xl bg-white p-6 shadow lg:col-span-2">
                    <h2 class="mb-4 text-xl font-semibold text-gray-800">Project Progress</h2>
                    <ul class="space-y-4">
                        <li class="flex items-center justify-between">
                            <span>Project A</span>
                            <div class="h-3 w-2/3 rounded-full bg-gray-200">
                                <div class="h-3 rounded-full bg-blue-600" style="width: 65%"></div>
                            </div>
                            <span class="ml-2 text-sm text-gray-500">65%</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Project B</span>
                            <div class="h-3 w-2/3 rounded-full bg-gray-200">
                                <div class="h-3 rounded-full bg-green-600" style="width: 90%"></div>
                            </div>
                            <span class="ml-2 text-sm text-gray-500">90%</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Project C</span>
                            <div class="h-3 w-2/3 rounded-full bg-gray-200">
                                <div class="h-3 rounded-full bg-yellow-500" style="width: 40%"></div>
                            </div>
                            <span class="ml-2 text-sm text-gray-500">40%</span>
                        </li>
                    </ul>
                </div>

                <!-- Employee Activity -->
                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="mb-4 text-xl font-semibold text-gray-800">Employee Activity</h2>
                    <ul class="space-y-3">
                        <li class="flex items-center justify-between">
                            <span class="text-gray-700">John Doe</span>
                            <span class="text-sm text-green-600">Online</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-gray-700">Jane Smith</span>
                            <span class="text-sm text-yellow-600">On Break</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-gray-700">Carlos Reyes</span>
                            <span class="text-sm text-red-600">Offline</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Resource Monitoring -->
            <div class="mt-6 rounded-xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Resource Monitoring</h2>
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="bg-gray-100 text-gray-700 uppercase">
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
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 p-6">
            <!-- Dashboard Header -->
            <div class="mb-6 flex flex-col justify-between sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-3xl font-extrabold text-green-800">Admin Dashboard</h1>
                    <p class="text-gray-600">Comprehensive overview of projects and team performance.</p>
                </div>
                <button @click="generateReport" class="mt-3 rounded-lg bg-green-700 px-4 py-2 text-white shadow hover:bg-green-800 sm:mt-0">
                    Generate Report
                </button>
            </div>

            <!-- Report Overlay -->
            <transition name="fade">
                <div v-if="showReport" class="bg-opacity-60 fixed inset-0 z-50 flex items-center justify-center bg-black backdrop-blur-sm">
                    <div class="relative w-11/12 max-w-6xl rounded-xl bg-white p-4 shadow-2xl">
                        <div class="absolute top-3 right-3 flex gap-2">
                            <button @click="downloadReport" class="rounded-md bg-green-600 px-3 py-1 text-sm text-white hover:bg-green-700">
                                ⬇ Download PDF
                            </button>
                            <button @click="showReport = false" class="rounded-md bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700">
                                ✕ Close
                            </button>
                        </div>

                        <h2 class="mb-2 text-center text-lg font-semibold text-green-800">Project Dashboard Report Preview</h2>
                        <div class="h-[80vh] overflow-hidden rounded-lg border">
                            <iframe id="reportFrame" class="h-full w-full border-none"></iframe>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Stats Overview -->
            <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Active Projects -->
                <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-md transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div class="absolute top-0 right-0 h-24 w-24 rounded-bl-full bg-blue-100 opacity-30"></div>
                    <h2 class="text-sm font-medium text-gray-500">Active Projects</h2>
                    <p class="mt-2 text-4xl font-extrabold text-blue-600 transition-all group-hover:scale-105">
                        {{ activeProjectsCount }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Currently in progress</p>
                </div>

                <!-- Employees -->
                <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-md transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div class="absolute top-0 right-0 h-24 w-24 rounded-bl-full bg-green-100 opacity-30"></div>
                    <h2 class="text-sm font-medium text-gray-500">Total Employees</h2>
                    <p class="mt-2 text-4xl font-extrabold text-green-600 transition-all group-hover:scale-105">
                        {{ employees.length }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Developers & testers included</p>
                </div>

                <!-- Completed Projects -->
                <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-md transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div class="absolute top-0 right-0 h-24 w-24 rounded-bl-full bg-yellow-100 opacity-30"></div>
                    <h2 class="text-sm font-medium text-gray-500">Completed Projects</h2>
                    <p class="mt-2 text-4xl font-extrabold text-yellow-600 transition-all group-hover:scale-105">
                        {{ completedProjectsCount }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Successfully delivered</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Monthly Projects Chart -->
                <div class="rounded-2xl bg-white p-6 shadow-md transition-all hover:-translate-y-1 hover:shadow-lg">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800">📆 Monthly Projects Overview</h2>
                    <div class="h-64">
                        <canvas id="monthlyProjectsChart"></canvas>
                    </div>
                </div>

                <!-- Project Status Distribution -->
                <div class="rounded-2xl bg-white p-6 shadow-md transition-all hover:-translate-y-1 hover:shadow-lg">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800">📊 Project Status Distribution</h2>
                    <div class="flex h-64 items-center justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Recent Projects -->
                <div class="rounded-2xl bg-white p-6 shadow-md transition-all hover:-translate-y-1 hover:shadow-lg lg:col-span-2">
                    <h2 class="mb-4 text-lg font-semibold text-gray-800">Recent Projects</h2>
                    <div class="overflow-x-auto rounded-lg">
                        <table class="w-full border-collapse text-left text-sm text-gray-700">
                            <thead class="sticky top-0 bg-gray-100 text-xs text-gray-600 uppercase">
                                <tr>
                                    <th class="px-4 py-3">Project Name</th>
                                    <th class="px-4 py-3">Client</th>
                                    <th class="px-4 py-3">Manager</th>
                                    <th class="px-4 py-3">Start Date</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="project in projects"
                                    :key="project.project_id"
                                    class="border-b transition odd:bg-white even:bg-gray-50 hover:bg-green-50"
                                >
                                    <td class="px-4 py-2 font-semibold text-gray-800">
                                        {{ project.project_name }}
                                    </td>
                                    <td class="px-4 py-2">{{ project.client_name }}</td>
                                    <td class="px-4 py-2">{{ project.manager_name || 'Unassigned' }}</td>
                                    <td class="px-4 py-2">{{ formatDate(project.start_date) }}</td>
                                    <td class="px-4 py-2">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                            :class="{
                                                'bg-blue-100 text-blue-800': project.status === 'in_progress',
                                                'bg-yellow-100 text-yellow-800': project.status === 'planning',
                                                'bg-green-100 text-green-800': project.status === 'completed',
                                                'bg-gray-100 text-gray-800': project.status === 'on_hold',
                                                'bg-red-100 text-red-800': project.status === 'cancelled',
                                            }"
                                        >
                                            {{ project.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Progress -->
                <div class="rounded-2xl bg-white p-6 shadow-md transition-all hover:-translate-y-1 hover:shadow-lg">
                    <h2 class="mb-4 text-lg font-semibold text-gray-800">Recent Progress Updates</h2>
                    <ul class="scrollbar-thin scrollbar-thumb-gray-300 max-h-80 space-y-3 overflow-y-auto">
                        <li v-for="progress in recentProgress" :key="progress.project_progress_id" class="border-b pb-2">
                            <p class="font-semibold text-gray-700">{{ progress.project_name }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(progress.progress_date) }} — {{ progress.progress_description }}</p>
                        </li>
                        <li v-if="recentProgress.length === 0" class="text-sm text-gray-400">No recent progress updates.</li>
                    </ul>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { Chart, registerables } from 'chart.js';
import { nextTick, onMounted, ref } from 'vue';
Chart.register(...registerables);

const showReport = ref(false);
const monthlyProjects = ref({});
const statusDistribution = ref({});
let monthlyChart = null;
let statusChart = null;

const activeProjectsCount = ref(0);
const completedProjectsCount = ref(0);
const projects = ref([]);
const recentProgress = ref([]);
const employees = ref([]);

const renderCharts = () => {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const monthlyData = Array(12).fill(0);
    Object.entries(monthlyProjects.value).forEach(([month, count]) => {
        monthlyData[month - 1] = count;
    });

    // Monthly Projects Chart
    const ctx1 = document.getElementById('monthlyProjectsChart');
    if (monthlyChart) monthlyChart.destroy();
    monthlyChart = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Projects Started',
                    data: monthlyData,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10B981',
                    borderWidth: 1,
                    borderRadius: 8,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: { beginAtZero: true, ticks: { color: '#374151' } },
                x: { ticks: { color: '#374151' } },
            },
        },
    });

    // Status Distribution Chart
    const ctx2 = document.getElementById('statusChart');
    if (statusChart) statusChart.destroy();
    statusChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusDistribution.value),
            datasets: [
                {
                    data: Object.values(statusDistribution.value),
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#6B7280'],
                    hoverOffset: 6,
                },
            ],
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#374151', font: { size: 12 } },
                },
            },
        },
    });
};

const fetchData = async () => {
    try {
        const { data } = await axios.get('/dashboard/data');
        if (data.result) {
            activeProjectsCount.value = data.activeProjectsCount;
            completedProjectsCount.value = data.completedProjectsCount;
            projects.value = data.projects;
            recentProgress.value = data.recentProgress;
            employees.value = data.employees;
            monthlyProjects.value = data.monthlyProjects;
            statusDistribution.value = data.statusDistribution;
            setTimeout(renderCharts, 300);
        }
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

onMounted(fetchData);
const generateReport = async () => {
    showReport.value = true;

    await nextTick();

    const iframe = document.getElementById('reportFrame');
    const doc = iframe.contentWindow.document;

    const monthlyData = Array(12).fill(0);
    Object.entries(monthlyProjects.value).forEach(([month, count]) => {
        monthlyData[month - 1] = count;
    });

    const statusLabels = Object.keys(statusDistribution.value);
    const statusData = Object.values(statusDistribution.value);

    doc.open();
    doc.write(`
    <html>
    <head>
        <title>Project Dashboard Report</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 30px; color: #111827; background: #f9fafb; }
            h1 { text-align: center; color: #065f46; margin-bottom: 30px; }
            .stats { display: flex; justify-content: space-around; margin-bottom: 30px; }
            .card { background: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; width: 30%; }
            .charts { display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; margin-top: 20px; }
            canvas { width: 400px !important; height: 250px !important; background: white; padding: 15px; border-radius: 8px; }
            table { width: 100%; border-collapse: collapse; margin-top: 40px; }
            th, td { border: 1px solid #d1d5db; padding: 8px 10px; font-size: 13px; }
            th { background-color: #f3f4f6; text-align: left; }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"><\/script>
    </head>
    <body>
        <h1>Project Dashboard Report</h1>

        <div class="stats">
            <div class="card">
                <h3>Active Projects</h3>
                <p style="font-size:24px;color:#3B82F6;">${activeProjectsCount.value}</p>
            </div>
            <div class="card">
                <h3>Completed Projects</h3>
                <p style="font-size:24px;color:#10B981;">${completedProjectsCount.value}</p>
            </div>
            <div class="card">
                <h3>Total Employees</h3>
                <p style="font-size:24px;color:#F59E0B;">${employees.value.length}</p>
            </div>
        </div>

        <div class="charts">
            <canvas id="monthlyChart"></canvas>
            <canvas id="statusChart"></canvas>
        </div>

        <h2>Recent Projects</h2>
        <table>
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Client</th>
                    <th>Manager</th>
                    <th>Start Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                ${projects.value
                    .map(
                        (p) => `
                    <tr>
                        <td>${p.project_name}</td>
                        <td>${p.client_name}</td>
                        <td>${p.manager_name || 'N/A'}</td>
                        <td>${p.start_date}</td>
                        <td>${p.status}</td>
                    </tr>`,
                    )
                    .join('')}
            </tbody>
        </table>

        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const monthlyData = ${JSON.stringify(monthlyData)};
                const statusLabels = ${JSON.stringify(statusLabels)};
                const statusData = ${JSON.stringify(statusData)};

                const ctx1 = document.getElementById('monthlyChart').getContext('2d');
                new Chart(ctx1, {
                    type: 'bar',
                    data: { labels: months, datasets: [{ label: 'Projects Started', data: monthlyData, backgroundColor: '#10B981' }] },
                    options: { responsive: true }
                });

                const ctx2 = document.getElementById('statusChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: ['#3B82F6','#10B981','#F59E0B','#EF4444','#6B7280'] }] },
                    options: { cutout: '70%' }
                });
            });
        <\/script>
    </body>
    </html>
    `);
    doc.close();
};

const downloadReport = () => {
    const iframe = document.getElementById('reportFrame');
    if (!iframe || !iframe.contentWindow) {
        alert('Please wait for the report to load.');
        return;
    }

    iframe.contentWindow.focus();
    iframe.contentWindow.print();
};
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

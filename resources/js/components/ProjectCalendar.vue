<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg">
        <div class="mb-6 flex items-center justify-between">
            <button
                @click="prevMonth"
                class="flex items-center gap-1 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                <span class="material-icons text-base">chevron_left</span>
                Prev
            </button>

            <h2 class="text-xl font-bold text-gray-800 sm:text-2xl">{{ monthName }} {{ currentYear }}</h2>

            <button
                @click="nextMonth"
                class="flex items-center gap-1 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Next
                <span class="material-icons text-base">chevron_right</span>
            </button>
        </div>

        <div class="mb-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
            <span v-for="s in statusLegend" :key="s.label" class="flex items-center gap-2 text-sm text-gray-600">
                <span :style="{ background: s.color }" class="inline-block h-3.5 w-3.5 rounded-full shadow-sm"></span>
                {{ s.label }}
            </span>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50">
            <div class="relative grid" :style="gridTemplateStyle">
                <div class="sticky left-0 z-20 border-r border-b border-gray-200 bg-gray-100 px-4 py-3">
                    <span class="text-sm font-semibold text-gray-600 uppercase">Project</span>
                </div>

                <div
                    v-for="day in daysInMonth"
                    :key="day"
                    class="border-r border-b border-gray-200 p-3 text-center"
                    :class="{
                        'bg-blue-50': isWeekend(day),
                        'bg-yellow-100': isToday(day),
                    }"
                >
                    <div class="text-xs font-bold text-gray-400 uppercase">
                        {{ getDayOfWeek(day) }}
                    </div>
                    <div class="mt-1 text-sm font-bold text-gray-600">
                        {{ day }}
                    </div>
                </div>

                <div
                    v-if="todayMarkerColumn"
                    class="absolute top-0 bottom-0 z-10 w-0.5 bg-yellow-500 opacity-70"
                    :style="{ 'grid-column': todayMarkerColumn, 'grid-row': '1 / -1' }"
                ></div>

                <template v-for="(p, index) in filteredProjects" :key="p.id">
                    <div
                        class="sticky left-0 z-10 flex items-center truncate border-r border-b border-gray-200 bg-white px-4"
                        :style="{ 'grid-row': index + 2, 'border-bottom-color': index === filteredProjects.length - 1 ? 'transparent' : '' }"
                    >
                        <span class="truncate font-medium text-gray-800">{{ p.name }}</span>
                    </div>

                    <div
                        class="group relative flex h-8 cursor-pointer items-center overflow-hidden rounded-lg px-3 text-white shadow-md transition-all duration-200 ease-in-out hover:opacity-80"
                        :style="getBarStyle(p, index)"
                    >
                        <span class="truncate text-xs font-semibold">{{ p.name }}</span>

                        <div
                            class="absolute -top-10 left-1/2 z-30 -translate-x-1/2 rounded-md bg-gray-800 px-2 py-1 text-xs whitespace-nowrap text-white opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            {{ formatDate(p.start) }} - {{ formatDate(p.end) }}
                        </div>
                    </div>
                </template>
            </div>

            <div v-if="!filteredProjects.length" class="p-10 text-center text-gray-500">No projects scheduled for this month.</div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

const today = new Date();
const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());
const projects = ref([]);

const monthName = computed(() =>
    new Date(currentYear.value, currentMonth.value).toLocaleString('default', {
        month: 'long',
    }),
);

const daysInMonth = computed(() => new Date(currentYear.value, currentMonth.value + 1, 0).getDate());

// Dynamically sets the CSS grid columns: 1 for project name, N for days
const gridTemplateStyle = computed(() => {
    return {
        'grid-template-columns': `200px repeat(${daysInMonth.value}, minmax(60px, 1fr))`,
        'grid-template-rows': `auto repeat(${filteredProjects.value.length}, 60px)`,
    };
});

// Filters projects to only those visible in the current month
const filteredProjects = computed(() => {
    const monthStart = new Date(currentYear.value, currentMonth.value, 1);
    const monthEnd = new Date(currentYear.value, currentMonth.value, daysInMonth.value, 23, 59, 59);

    return projects.value.filter((p) => {
        return p.start <= monthEnd && p.end >= monthStart;
    });
});

// --- Date Helper Functions ---

function getDayOfWeek(day) {
    const date = new Date(currentYear.value, currentMonth.value, day);
    return date.toLocaleDateString('en-US', { weekday: 'short' })[0]; // S, M, T...
}

function isWeekend(day) {
    const date = new Date(currentYear.value, currentMonth.value, day);
    const dayOfWeek = date.getDay();
    return dayOfWeek === 0 || dayOfWeek === 6; // 0 = Sunday, 6 = Saturday
}

const isToday = (day) => {
    return today.getFullYear() === currentYear.value && today.getMonth() === currentMonth.value && today.getDate() === day;
};

// Calculates the grid column for the "Today" marker
const todayMarkerColumn = computed(() => {
    if (today.getFullYear() === currentYear.value && today.getMonth() === currentMonth.value) {
        return today.getDate() + 1; // +1 because col 1 is the project name
    }
    return null; // No marker if not this month
});

const formatDate = (date) => {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

// --- Gantt Bar Style Calculation ---
// This is the core logic for the continuous bars
function getBarStyle(project, index) {
    const monthStart = new Date(currentYear.value, currentMonth.value, 1);
    const monthEnd = new Date(currentYear.value, currentMonth.value, daysInMonth.value);

    // Clamp project start/end dates to the visible month
    const barStartDate = project.start < monthStart ? monthStart : project.start;
    const barEndDate = project.end > monthEnd ? monthEnd : project.end;

    const startDay = barStartDate.getDate();
    const endDay = barEndDate.getDate();

    // Calculate grid columns
    // +1 because grid columns are 1-based
    // +1 again because our first column is for the project name
    const gridColStart = startDay + 1;
    // +1 (1-based) +1 (project col) +1 (grid-column-end is exclusive)
    const gridColEnd = endDay + 2;

    return {
        'grid-column': `${gridColStart} / ${gridColEnd}`,
        'grid-row': index + 2, // +2 because row 1 is the header
        'background-color': project.color,
        'margin-top': 'auto', // Vertically center the bar
        'margin-bottom': 'auto', // Vertically center the bar
    };
}

// --- Your Existing Functions (Unchanged) ---

const statusLegend = [
    { label: 'Completed', color: '#10b981' },
    { label: 'In_progress', color: '#3b82f6' },
    { label: 'Planning', color: '#f59e0b' },
    { label: 'On_hold / Cancelled', color: '#9ca3af' },
];

async function fetchProjects() {
    try {
        const response = await axios.get('/projects/calendar-data');
        if (response.data.result) {
            projects.value = response.data.data.map((p) => {
                return {
                    id: p.id,
                    name: p.name,
                    start: new Date(p.start_date),
                    end: new Date(p.end_date),
                    color: p.color,
                    status: p.status,
                };
            });
        }
    } catch (error) {
        console.error('Error fetching projects:', error);
    }
}

function prevMonth() {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else currentMonth.value--;
}
function nextMonth() {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else currentMonth.value++;
}

onMounted(fetchProjects);
</script>

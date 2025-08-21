<template>
    <div class="rounded-lg bg-white p-6 shadow">
        <!-- Header -->
        <div class="mb-4 flex items-center justify-between">
            <button @click="prevMonth" class="rounded bg-yellow-600 px-3 py-1 text-white hover:bg-yellow-700">Prev</button>
            <h2 class="text-xl font-bold text-yellow-700">{{ monthName }} {{ currentYear }}</h2>
            <button @click="nextMonth" class="rounded bg-yellow-600 px-3 py-1 text-white hover:bg-yellow-700">Next</button>
        </div>

        <!-- Legend -->
        <div class="mb-4 flex items-center gap-4">
            <span v-for="s in statusLegend" :key="s.label" class="flex items-center gap-2">
                <span :style="{ background: s.color }" class="inline-block h-4 w-4 rounded-full border"></span>
                <span class="text-sm text-gray-600">{{ s.label }}</span>
            </span>
        </div>

        <!-- Calendar (week by week) -->
        <div class="overflow-x-auto">
            <!-- Weekday headers -->
            <div class="mb-2 grid grid-cols-7 gap-1">
                <div v-for="d in daysOfWeek" :key="d" class="text-center text-xs font-semibold text-gray-600">{{ d }}</div>
            </div>

            <!-- Weeks -->
            <div v-for="(week, wi) in weeks" :key="wi" class="mb-3">
                <!-- Date cells -->
                <div class="grid grid-cols-7 gap-1">
                    <div v-for="(cell, ci) in week.cells" :key="ci" class="relative h-16 rounded-lg border bg-gray-50 p-1">
                        <div class="absolute top-1 left-1 text-xs font-bold" :class="cell.inMonth ? 'text-gray-700' : 'text-gray-400'">
                            {{ cell.day }}
                        </div>
                    </div>
                </div>

                <!-- Bars for this week (same 7-column grid) -->
                <div class="relative mt-2 grid grid-cols-7 gap-1" :style="{ minHeight: `${(week.lanes || 1) * 28}px` }">
                    <!-- placeholders to define the 7 columns -->
                    <div v-for="n in 7" :key="`ph-${wi}-${n}`"></div>

                    <!-- segments -->
                    <div
                        v-for="seg in week.segments"
                        :key="seg.key"
                        class="absolute flex items-center rounded px-2 py-0.5 text-xs font-semibold text-white shadow"
                        :style="{
                            gridColumn: `${seg.colStart} / span ${seg.span}`,
                            top: `${seg.lane * 28}px`,
                            height: '24px',
                            lineHeight: '24px',
                            background: seg.color,
                            left: '0',
                            right: '0',
                        }"
                        :title="`${seg.name} (${seg.status})`"
                    >
                        {{ seg.name }} <span class="ml-1 text-[10px]">({{ seg.status }})</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const today = new Date();
const currentMonth = ref(today.getMonth()); // 0-11
const currentYear = ref(today.getFullYear());

const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const monthName = computed(() => new Date(currentYear.value, currentMonth.value).toLocaleString('default', { month: 'long' }));

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

/** Example data (use your own) */
const projects = ref([
    { id: 1, name: 'Site Survey', start: 1, end: 4, color: '#f59e42', status: 'Completed' },
    { id: 2, name: 'Foundation Work', start: 5, end: 10, color: '#3b82f6', status: 'Ongoing' },
    { id: 3, name: 'Material Delivery', start: 12, end: 14, color: '#10b981', status: 'Pending' },
    { id: 4, name: 'Inspection', start: 20, end: 22, color: '#ef4444', status: 'Delayed' },
]);

const statusLegend = [
    { label: 'Completed', color: '#f59e42' },
    { label: 'Ongoing', color: '#3b82f6' },
    { label: 'Pending', color: '#10b981' },
    { label: 'Delayed', color: '#ef4444' },
];

const weeks = computed(() => {
    const year = currentYear.value;
    const month = currentMonth.value;

    const firstDayIdx = new Date(year, month, 1).getDay(); // 0=Sun
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const totalCells = firstDayIdx + daysInMonth;
    const weekCount = Math.ceil(totalCells / 7);

    const result = [];

    for (let w = 0; w < weekCount; w++) {
        const weekStartDay = 1 - firstDayIdx + w * 7; // may be <=0
        const weekEndDay = weekStartDay + 6;

        // Build the 7 visible cells
        const cells = Array.from({ length: 7 }, (_, i) => {
            const dayNum = weekStartDay + i;
            return {
                day: dayNum > 0 && dayNum <= daysInMonth ? dayNum : '', // show blanks for prev/next month
                inMonth: dayNum > 0 && dayNum <= daysInMonth,
            };
        });

        // Build project segments that intersect this week
        const rawSegs = [];
        for (const p of projects.value) {
            const overlapStart = Math.max(p.start, Math.max(weekStartDay, 1));
            const overlapEnd = Math.min(p.end, Math.min(weekEndDay, daysInMonth));
            if (overlapStart <= overlapEnd) {
                const colStart = overlapStart - weekStartDay + 1; // 1..7
                const span = overlapEnd - overlapStart + 1; // 1..7
                rawSegs.push({
                    key: `${p.id}-${w}`,
                    id: p.id,
                    name: p.name,
                    status: p.status,
                    color: p.color,
                    colStart,
                    span,
                    startColIndex: colStart,
                    endColIndex: colStart + span - 1,
                });
            }
        }

        // Assign lanes to avoid vertical overlap
        rawSegs.sort((a, b) => a.colStart - b.colStart || b.span - a.span);
        const lanes = []; // each lane holds endColIndex of last seg
        for (const s of rawSegs) {
            let placed = false;
            for (let li = 0; li < lanes.length; li++) {
                if (s.colStart > lanes[li]) {
                    // free (strictly after)
                    s.lane = li;
                    lanes[li] = s.endColIndex;
                    placed = true;
                    break;
                }
            }
            if (!placed) {
                s.lane = lanes.length;
                lanes.push(s.endColIndex);
            }
        }

        result.push({
            cells,
            segments: rawSegs,
            lanes: lanes.length,
        });
    }

    return result;
});
</script>

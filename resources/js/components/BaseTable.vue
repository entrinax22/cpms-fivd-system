<template>
  <div class="overflow-x-auto bg-white shadow rounded-lg">
    <table class="min-w-full border border-gray-200 rounded-lg">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 text-center text-gray-600 text-sm font-semibold">#</th>
          <th v-for="col in columns" :key="col.key" class="px-4 py-2 text-left text-gray-600 text-sm font-semibold">
            {{ col.label }}
          </th>
          <th class="px-4 py-2 text-center text-gray-600 text-sm font-semibold">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in data" :key="index" class="border-t hover:bg-gray-50">
          <td class="px-4 py-2 text-center text-gray-600">{{ (currentPage - 1) * perPage + index + 1 }}</td>
          <td v-for="col in columns" :key="col.key" class="px-4 py-2 text-sm text-gray-700">
            <slot :name="col.key" :row="row" :value="row[col.key]">
              {{ row[col.key] }}
            </slot>
          </td>
          <td class="px-4 py-2 flex justify-center gap-2">
            <slot name="actions" :row="row" />
          </td>
        </tr>
      </tbody>
    </table>
    <div class="flex justify-end items-center gap-2 p-4">
      <button @click="onPrevPage" :disabled="currentPage === 1" class="px-2 py-1 rounded bg-gray-200 hover:bg-gray-300">Prev</button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="onNextPage" :disabled="currentPage === totalPages" class="px-2 py-1 rounded bg-gray-200 hover:bg-gray-300">Next</button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  columns: {
    type: Array,
    required: true,
  },
  data: {
    type: Array,
    required: true,
  },
  currentPage: {
    type: Number,
    required: true,
  },
  totalPages: {
    type: Number,
    required: true,
  },
  perPage: {
    type: Number,
    required: true,
  },
  onNextPage: {
    type: Function,
    required: true,
  },
  onPrevPage: {
    type: Function,
    required: true,
  },
});
</script>

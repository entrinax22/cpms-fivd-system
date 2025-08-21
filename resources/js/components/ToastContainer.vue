<!-- resources/js/Components/ToastContainer.vue -->
<template>
  <div class="fixed top-5 right-5 flex flex-col gap-2 z-50">
    <transition-group name="toast" tag="div">
      <div
        v-for="toastItem in toasts"
        :key="toastItem.id"
        :class="[
          'px-4 py-2 rounded-lg shadow text-white font-medium text-sm',
          toastItem.type === 'success' ? 'bg-green-500' : 'bg-red-500'
        ]"
      >
        {{ toastItem.message }}
      </div>
    </transition-group>
  </div>
</template>

<script lang="ts" setup>
import { toast, Toast } from "@/stores/ToastStore";

const toasts: Toast[] = toast.state.toasts;
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>

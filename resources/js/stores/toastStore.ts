// resources/js/stores/toastStore.ts
import { reactive } from "vue";

export type ToastType = "success" | "error";

export interface Toast {
  id: number;
  message: string;
  type: ToastType;
  duration?: number;
}

const state = reactive<{ toasts: Toast[] }>({
  toasts: [],
});

let idCounter = 0;

export const toast = {
  state,
  show(message: string, type: ToastType = "success", duration: number = 3000) {
    const id = idCounter++;
    state.toasts.push({ id, message, type, duration });

    setTimeout(() => {
      const index = state.toasts.findIndex(t => t.id === id);
      if (index !== -1) state.toasts.splice(index, 1);
    }, duration);
  },
};

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 px-2">
    <!-- Card -->
    <div class="bg-white shadow-2xl rounded-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 w-full max-w-4xl">
      
      <!-- Left Column (Branding) -->
      <div class="hidden md:flex flex-col items-center justify-center bg-gradient-to-br from-yellow-700 via-yellow-600 to-yellow-500 p-10 text-white">
        <img src="/logo.png" alt="Logo" class="w-24 h-24 mb-6 rounded-full shadow-lg border-4 border-white" />
        <h2 class="text-3xl font-extrabold">CPMS - FIVD</h2>
        <p class="mt-4 text-center text-yellow-100">
          Internal system for project tracking & resource management.<br />
          Employees only.
        </p>
      </div>

      <!-- Right Column (Form) -->
      <div class="p-4 sm:p-8 md:p-12 flex flex-col justify-center">
        <Head title="Change Password" />

        <h1 class="text-2xl font-bold text-gray-900 mb-2 text-center md:text-left">Change Your Password</h1>
        <p class="text-gray-500 mb-6 text-center md:text-left">
          Enter your current password and set a new one to secure your account.
        </p>

        <Form
          method="post"
          :action="route('password.change.update')"
          v-slot="{ errors, processing }"
          class="flex flex-col gap-6"
        >
          <div class="grid gap-6">
            <!-- New Password -->
            <div class="grid gap-2">
              <Label for="password">New Password</Label>
              <Input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Enter new password"
              />
              <InputError :message="errors.password" />
            </div>

            <!-- Confirm Password -->
            <div class="grid gap-2">
              <Label for="password_confirmation">Confirm New Password</Label>
              <Input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Confirm new password"
              />
              <InputError :message="errors.password_confirmation" />
            </div>

            <!-- Submit -->
            <Button type="submit" class="w-full mt-4" :disabled="processing">
              <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin mr-2" />
              Save Changes
            </Button>
          </div>
        </Form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form, Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

</script>

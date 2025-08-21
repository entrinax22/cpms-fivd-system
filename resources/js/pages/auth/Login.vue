<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 px-2">
    <!-- Card -->
    <div class="bg-white shadow-2xl rounded-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 w-full max-w-4xl">
      
      <!-- Left Column (Logo / Branding) -->
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
        <Head title="Log in" />

        <h1 class="text-2xl font-bold text-gray-900 mb-2 text-center md:text-left">Log in to your account</h1>
        <p class="text-gray-500 mb-6 text-center md:text-left">Enter your email and password below to log in</p>

        <div v-if="status" class="mb-4 text-sm font-medium text-center text-green-600">
          {{ status }}
        </div>

        <Form
          method="post"
          :action="route('login')"
          :reset-on-success="['password']"
          v-slot="{ errors, processing }"
          class="flex flex-col gap-6"
        >
          <div class="grid gap-6">
            <!-- Email -->
            <div class="grid gap-2">
              <Label for="email">Email address</Label>
              <Input
                id="email"
                type="email"
                name="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
              />
              <InputError :message="errors.email" />
            </div>

            <!-- Password -->
            <div class="grid gap-2">
              <div class="flex items-center justify-between">
                <Label for="password">Password</Label>
                <TextLink
                  v-if="canResetPassword"
                  :href="route('password.request')"
                  class="text-sm"
                >
                  Forgot password?
                </TextLink>
              </div>
              <Input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Password"
              />
              <InputError :message="errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
              <Checkbox id="remember" name="remember" />
              <Label for="remember" class="ml-2">Remember me</Label>
            </div>

            <!-- Submit -->
            <Button type="submit" class="w-full mt-4" :disabled="processing">
              <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin mr-2" />
              Log in
            </Button>
          </div>
        </Form>
      </div>
    </div>
  </div>
</template>

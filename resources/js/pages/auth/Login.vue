<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { Clock, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

// Modal state
const showForgotModal = ref(false);
const step = ref(1); // 1 = input phone, 2 = input OTP + password
const phone = ref('');
const otp = ref('');
const newPassword = ref('');
const loading = ref(false);
const message = ref('');
const error = ref('');
const resendCooldown = ref(0);
let timer: number | null = null;

// Send OTP via backend
const sendOtp = async () => {
    if (loading.value) return;
    loading.value = true;
    error.value = '';
    message.value = '';
    try {
        const res = await axios.post('/forgot-password/send-otp', { phone: phone.value });
        message.value = res.data.message || 'OTP sent successfully.';
        step.value = 2;
        startCooldown();
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to send OTP';
    } finally {
        loading.value = false;
    }
};

// Verify OTP & Reset Password
const verifyOtp = async () => {
    if (loading.value) return;
    loading.value = true;
    error.value = '';
    message.value = '';
    try {
        const res = await axios.post('/forgot-password/verify-otp', {
            phone: phone.value,
            otp: otp.value,
            password: newPassword.value,
        });
        message.value = res.data.message || 'Password reset successfully.';
        showForgotModal.value = false;
        step.value = 1;
        phone.value = '';
        otp.value = '';
        newPassword.value = '';
    } catch (err) {
        error.value = err.response?.data?.message || 'Invalid OTP';
    } finally {
        loading.value = false;
    }
};

// Start 60s resend cooldown
const startCooldown = () => {
    resendCooldown.value = 60;
    if (timer) clearInterval(timer);
    timer = window.setInterval(() => {
        if (resendCooldown.value > 0) {
            resendCooldown.value--;
        } else {
            clearInterval(timer!);
        }
    }, 1000);
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100 px-2">
        <div class="grid w-full max-w-4xl grid-cols-1 overflow-hidden rounded-2xl bg-white shadow-2xl md:grid-cols-2">
            <!-- Left Section -->
            <div
                class="hidden flex-col items-center justify-center bg-gradient-to-br from-yellow-700 via-yellow-600 to-yellow-500 p-10 text-white md:flex"
            >
                <img src="/logo.png" alt="Logo" class="mb-6 h-24 w-24 rounded-full border-4 border-white shadow-lg" />
                <h2 class="text-3xl font-extrabold">CPMS - FIVD</h2>
                <p class="mt-4 text-center text-yellow-100">
                    Internal system for project tracking & resource management.<br />
                    Employees only.
                </p>
            </div>

            <!-- Right Section -->
            <div class="flex flex-col justify-center p-4 sm:p-8 md:p-12">
                <Head title="Log in" />
                <h1 class="mb-2 text-center text-2xl font-bold text-gray-900 md:text-left">Log in to your account</h1>
                <p class="mb-6 text-center text-gray-500 md:text-left">Enter your email and password below to log in</p>

                <Form method="post" :action="route('login')" v-slot="{ errors, processing }" class="flex flex-col gap-6">
                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <Label for="email">Email address</Label>
                            <Input id="email" type="email" name="email" required autofocus placeholder="email@example.com" />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password">Password</Label>
                                <button
                                    v-if="canResetPassword"
                                    type="button"
                                    @click="showForgotModal = true"
                                    class="text-sm text-blue-600 hover:underline"
                                >
                                    Forgot password?
                                </button>
                            </div>
                            <Input id="password" type="password" name="password" required placeholder="Password" />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="flex items-center">
                            <Checkbox id="remember" name="remember" />
                            <Label for="remember" class="ml-2">Remember me</Label>
                        </div>

                        <Button type="submit" class="mt-4 w-full" :disabled="processing">
                            <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                            Log in
                        </Button>
                    </div>
                </Form>
            </div>
        </div>

        <!-- Forgot Password Modal -->
        <transition name="fade">
            <div v-if="showForgotModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl transition-all">
                    <h2 class="mb-4 text-center text-xl font-bold text-gray-800">Forgot Password</h2>

                    <!-- Step 1: Input Phone -->
                    <div v-if="step === 1" class="space-y-4">
                        <Label for="phone">Enter your registered phone number</Label>
                        <Input id="phone" v-model="phone" placeholder="09xxxxxxxxx" />
                        <Button class="mt-2 w-full" @click="sendOtp" :disabled="loading">
                            <LoaderCircle v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                            Send OTP
                        </Button>
                    </div>

                    <!-- Step 2: Input OTP & Password -->
                    <div v-if="step === 2" class="space-y-4">
                        <Label for="otp">Enter the OTP sent to your phone</Label>
                        <Input id="otp" v-model="otp" placeholder="Enter OTP" />

                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>Didn’t receive code?</span>
                            <button
                                class="text-blue-600 hover:underline disabled:text-gray-400"
                                :disabled="resendCooldown > 0 || loading"
                                @click="sendOtp"
                            >
                                <template v-if="resendCooldown > 0"> <Clock class="mr-1 inline h-4 w-4" /> Resend in {{ resendCooldown }}s </template>
                                <template v-else>Resend OTP</template>
                            </button>
                        </div>

                        <Label for="newPassword">New Password</Label>
                        <Input id="newPassword" v-model="newPassword" type="password" placeholder="Enter new password" />

                        <Button class="mt-2 w-full" @click="verifyOtp" :disabled="loading">
                            <LoaderCircle v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                            Reset Password
                        </Button>
                    </div>

                    <!-- Messages -->
                    <p v-if="message" class="mt-3 text-center text-sm text-green-600">{{ message }}</p>
                    <p v-if="error" class="mt-3 text-center text-sm text-red-600">{{ error }}</p>

                    <!-- Close Button -->
                    <Button variant="ghost" class="mt-4 w-full text-gray-600 hover:text-black" @click="showForgotModal = false"> Cancel </Button>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

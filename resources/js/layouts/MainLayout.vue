<template>
    <nav class="flex items-center justify-between bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 px-4 py-3 shadow-lg flex-wrap">
        <!-- Branding -->
        <div class="flex items-center gap-3">
            <img src="/logo.png" alt="Logo" class="h-10 w-10 rounded-full border-2 border-white shadow" />
            <span class="text-2xl font-extrabold tracking-wide text-white">CPMS - FIVD</span>
        </div>

        <!-- Hamburger for mobile -->
        <button @click="showMobileMenu = !showMobileMenu" class="md:hidden flex items-center px-2 py-1 text-white focus:outline-none">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Desktop Nav -->
        <div class="hidden md:flex items-center gap-6">
            <Link :href="route('home')" class="font-medium text-white transition hover:text-gray-900">Home</Link>
            <Link :href="route('home')" class="font-medium text-white transition hover:text-gray-900">Resources</Link>
            <Link :href="route('home')" class="font-medium text-white transition hover:text-gray-900">Teams</Link>

            <!-- User Dropdown -->
            <div class="relative" v-if="user">
                <button
                    @click="showDropdown = !showDropdown"
                    class="flex items-center gap-2 rounded-lg bg-yellow-700 px-3 py-2 font-semibold text-white transition hover:bg-yellow-800 focus:outline-none"
                >
                    <span class="font-semibold">{{ user.name || 'Account' }}</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div v-if="showDropdown" class="absolute right-0 z-50 mt-2 w-48 rounded-lg border border-yellow-200 bg-white shadow-lg">
                    <Link v-if="user.role === 'admin'" :href="route('admin.dashboard')" class="block px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-100">Admin Dashboard</Link>
                    <Link :href="route('home')" class="block px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-100">Notifications</Link>
                    <Link :href="route('home')" class="block px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-100">My Bookings</Link>
                    <button @click="logout()" class="w-full px-4 py-2 text-left text-red-600 hover:bg-yellow-100">Logout</button>
                </div>
            </div>
        </div>

        <!-- Mobile Nav -->
        <div v-if="showMobileMenu" class="w-full md:hidden mt-3">
            <div class="flex flex-col gap-2 bg-yellow-500 rounded-lg p-3">
                <Link :href="route('home')" class="font-medium text-white transition hover:text-gray-900">Home</Link>
                <Link :href="route('home')" class="font-medium text-white transition hover:text-gray-900">Resources</Link>
                <Link :href="route('home')" class="font-medium text-white transition hover:text-gray-900">Teams</Link>
                <div v-if="user" class="mt-2">
                    <button @click="showDropdown = !showDropdown" class="flex items-center gap-2 rounded-lg bg-yellow-700 px-3 py-2 font-semibold text-white transition hover:bg-yellow-800 focus:outline-none w-full">
                        <span class="font-semibold">{{ user.name || 'Account' }}</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-if="showDropdown" class="mt-2 w-full rounded-lg border border-yellow-200 bg-white shadow-lg">
                        <Link v-if="user.role === 'admin'" :href="route('admin.dashboard')" class="block px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-100">Admin Dashboard</Link>
                        <Link :href="route('home')" class="block px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-100">Notifications</Link>
                        <Link :href="route('home')" class="block px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-100">My Bookings</Link>
                        <button @click="logout()" class="w-full px-4 py-2 text-left text-red-600 hover:bg-yellow-100">Logout</button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="min-h-screen bg-gray-50 p-3 md:p-6">
        <slot />
        <ToastContainer />
    </main>

    <footer class="mt-8 flex flex-col md:flex-row items-center justify-between bg-yellow-700 px-4 md:px-6 py-4 text-white gap-2 md:gap-0 text-center md:text-left">
        <span>&copy; 2025 BuildPro Construction. All rights reserved.</span>
        <span class="flex gap-2">
            <Link :href="route('home')" class="hover:text-yellow-300">Privacy Policy</Link>
            <Link :href="route('home')" class="hover:text-yellow-300">Terms</Link>
        </span>
    </footer>
</template>

<script setup>
import { usePage, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';

const page = usePage();
const user = ref(page.props.user || { name: 'Guest', role: 'guest' });
const showDropdown = ref(false);
const showMobileMenu = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

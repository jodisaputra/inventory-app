<script setup>
import { ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(true);
const page = usePage();

// Track active submenu
const activeSubmenu = ref(null);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
    // Save preference to localStorage
    localStorage.setItem('sidebarOpen', sidebarOpen.value);
};

const toggleSubmenu = (menu) => {
    activeSubmenu.value = activeSubmenu.value === menu ? null : menu;
};

// Load sidebar state from localStorage on mount
onMounted(() => {
    const savedState = localStorage.getItem('sidebarOpen');
    if (savedState !== null) {
        sidebarOpen.value = savedState === 'true';
    }

    // Handle smaller screens
    const handleResize = () => {
        if (window.innerWidth < 768) {
            sidebarOpen.value = false;
        }
    };

    // Initial check
    handleResize();

    // Add event listener
    window.addEventListener('resize', handleResize);
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <!-- Top Navbar -->
            <nav class="fixed top-0 z-30 w-full border-b border-gray-100 bg-white shadow-sm">
                <div class="px-4">
                    <div class="flex h-16 items-center justify-between">
                        <div class="flex items-center">
                            <!-- Toggle sidebar button -->
                            <button @click="toggleSidebar"
                                class="mr-3 flex h-10 w-10 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>

                            <!-- Logo for mobile -->
                            <div class="flex shrink-0 items-center sm:hidden">
                                <Link :href="route('dashboard')">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                                </Link>
                            </div>

                            <!-- Page Title -->
                            <h1 class="text-xl font-semibold text-gray-800">
                                <slot name="header">Dashboard</slot>
                            </h1>
                        </div>

                        <div class="flex items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger for mobile -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu (Mobile) -->
                <div :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }" class="absolute left-0 right-0 top-16 z-40 bg-white shadow-lg sm:hidden">
                    <!-- User Info at the top -->
                    <div class="border-b border-gray-200 px-4 py-4">
                        <div class="flex items-center">
                            <div
                                class="mr-3 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <div>
                                <div class="text-base font-medium text-gray-800">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick action links -->
                    <div class="px-2 py-3">
                        <ResponsiveNavLink :href="route('profile.edit')" class="flex items-center">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile
                        </ResponsiveNavLink>

                        <ResponsiveNavLink :href="route('logout')" method="post" as="button"
                            class="flex w-full items-center">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            Log Out
                        </ResponsiveNavLink>
                    </div>

                    <div class="mt-2 border-t border-gray-200">
                        <div class="px-4 py-2 text-xs font-bold text-gray-500 uppercase">
                            Toggle Menu
                        </div>

                        <!-- Toggle sidebar button on mobile -->
                        <button @click="toggleSidebar(); showingNavigationDropdown = false;"
                            class="flex w-full items-center px-4 py-2 text-base font-medium text-gray-700">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16">
                                </path>
                            </svg>
                            {{ sidebarOpen ? 'Hide Sidebar' : 'Show Sidebar' }}
                        </button>
                    </div>
                </div>
            </nav>

            <div class="flex pt-16">
                <!-- Sidebar -->
                <div :class="['fixed inset-y-0 left-0 z-20 w-64 bg-gray-900 pt-16 transition-all duration-300',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
                    <!-- Logo and App Name -->
                    <div class="flex items-center justify-center py-6 border-b border-gray-700">
                        <ApplicationLogo class="h-10 w-10 fill-current text-white" />
                        <span class="ml-3 text-xl font-bold text-white">Your App</span>
                    </div>

                    <!-- Main Menu Header -->
                    <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider mt-4">
                        MAIN MENU
                    </div>

                    <div class="py-2">
                        <nav>
                            <!-- Dashboard Link -->
                            <Link :href="route('dashboard')"
                                class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white"
                                :class="{ 'bg-blue-700 text-white': route().current('dashboard') }">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                            <span>Dashboard</span>
                            </Link>

                            <!-- Users Menu with Submenu -->
                            <div>
                                <button @click="toggleSubmenu('users')"
                                    class="flex items-center justify-between w-full px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                            </path>
                                        </svg>
                                        <span>Users</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200"
                                        :class="{ 'rotate-180': activeSubmenu === 'users' }" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7">
                                        </path>
                                    </svg>
                                </button>

                                <!-- Submenu Items -->
                                <div v-show="activeSubmenu === 'users'" class="pl-12 py-1 bg-gray-800">
                                    <Link href="#" class="block py-2 text-sm text-gray-400 hover:text-white">
                                    All Users
                                    </Link>
                                    <Link href="#" class="block py-2 text-sm text-gray-400 hover:text-white">
                                    Add New
                                    </Link>
                                    <Link href="#" class="block py-2 text-sm text-gray-400 hover:text-white">
                                    Roles
                                    </Link>
                                </div>
                            </div>

                            <!-- Reports Menu with Submenu -->
                            <div>
                                <button @click="toggleSubmenu('reports')"
                                    class="flex items-center justify-between w-full px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <span>Reports</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200"
                                        :class="{ 'rotate-180': activeSubmenu === 'reports' }" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7">
                                        </path>
                                    </svg>
                                </button>

                                <!-- Submenu Items -->
                                <div v-show="activeSubmenu === 'reports'" class="pl-12 py-1 bg-gray-800">
                                    <Link href="#" class="block py-2 text-sm text-gray-400 hover:text-white">
                                    Sales Report
                                    </Link>
                                    <Link href="#" class="block py-2 text-sm text-gray-400 hover:text-white">
                                    User Activity
                                    </Link>
                                    <Link href="#" class="block py-2 text-sm text-gray-400 hover:text-white">
                                    Analytics
                                    </Link>
                                </div>
                            </div>

                            <!-- Settings Link -->
                            <Link href="#"
                                class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Settings</span>
                            </Link>
                        </nav>
                    </div>
                </div>

                <!-- Overlay to close sidebar on mobile -->
                <div v-if="sidebarOpen" @click="toggleSidebar"
                    class="fixed inset-0 z-10 bg-black bg-opacity-50 md:hidden"></div>

                <!-- Main Content Area -->
                <div :class="['min-h-screen w-full transition-all duration-300',
                    sidebarOpen ? 'md:pl-64' : 'pl-0']">
                    <!-- Page Content -->
                    <main class="px-4 py-8 sm:px-6 lg:px-8">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Optional: Add smooth transitions for submenus */
[v-show] {
    transition: all 0.3s ease-in-out;
}
</style>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Get authenticated user from Inertia
const page = usePage();
const auth = computed(() => page.props.auth);

// Table state
const users = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const entriesPerPage = ref(10);

// Pagination
const currentPage = ref(1);
const totalPages = ref(1);
const totalUsers = ref(0);

// Delete modal state
const deleteModalOpen = ref(false);
const userToDelete = ref(null);

// Toast notification system
const toast = ref({
    visible: false,
    message: '',
    type: 'info' // info, success, error, warning
});

const showToast = (message, type = 'info') => {
    toast.value.message = message;
    toast.value.type = type;
    toast.value.visible = true;

    // Auto-hide after 5 seconds
    setTimeout(() => {
        toast.value.visible = false;
    }, 5000);
};

const hideToast = () => {
    toast.value.visible = false;
};

// Fetch users from server
const fetchUsers = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('users.data'), {
            params: {
                page: currentPage.value,
                per_page: entriesPerPage.value,
                search: searchQuery.value
            }
        });

        users.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
        totalUsers.value = response.data.total;
    } catch (error) {
        console.error('Error fetching users:', error);
        showToast('Error loading users data', 'error');
    } finally {
        loading.value = false;
    }
};

// Handle search input
const handleSearch = () => {
    currentPage.value = 1; // Reset to first page on search
    fetchUsers();
};

// Handle page change
const changePage = (page) => {
    currentPage.value = page;
    fetchUsers();
};

// Handle entries per page change
const changeEntriesPerPage = () => {
    currentPage.value = 1; // Reset to first page when changing entries per page
    fetchUsers();
};

// Delete user functions
const confirmDelete = (user) => {
    // Check if user is trying to delete their own account
    if (user.id === auth.value.user.id) {
        // Show alert notification
        showToast('You cannot delete your own account.', 'error');
        return;
    }

    userToDelete.value = user;
    deleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    deleteModalOpen.value = false;
    userToDelete.value = null;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(route('users.destroy', userToDelete.value.id), {
            onSuccess: () => {
                closeDeleteModal();
                showToast('User deleted successfully', 'success');
                fetchUsers(); // Refresh the user list
            },
            onError: (errors) => {
                showToast(errors.message || 'Failed to delete user', 'error');
            },
        });
    }
};

// Sort functions
const sortColumn = (column) => {
    // Implement sorting logic here if needed
    fetchUsers();
};

// Initialize on component mount
onMounted(() => {
    fetchUsers();
});
</script>

<template>

    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Users</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
                            <!-- Per Page Selector -->
                            <div class="mb-4 sm:mb-0 flex items-center">
                                <span class="mr-2">Show</span>
                                <select v-model="entriesPerPage" @change="changeEntriesPerPage"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="ml-2">entries</span>
                            </div>

                            <div class="flex items-center space-x-4">
                                <!-- Search Box -->
                                <div class="relative">
                                    <input v-model="searchQuery" @input="handleSearch" type="text"
                                        placeholder="Search..."
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                </div>

                                <!-- Add User Button -->
                                <Link :href="route('users.create')"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4">
                                    </path>
                                </svg>
                                Add User
                                </Link>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto mt-4">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th @click="sortColumn('id')"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                                            ID
                                            <!-- Sort indicators can be added here -->
                                        </th>
                                        <th @click="sortColumn('name')"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                                            Name
                                        </th>
                                        <th @click="sortColumn('email')"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                                            Email
                                        </th>
                                        <th @click="sortColumn('created_at')"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                                            Created At
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="loading">
                                        <td colspan="5" class="px-6 py-4 text-center">
                                            <div class="flex justify-center">
                                                <div
                                                    class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-500">
                                                </div>
                                            </div>
                                            <div class="mt-2">Loading...</div>
                                        </td>
                                    </tr>
                                    <tr v-else-if="users.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            No users found.
                                        </td>
                                    </tr>
                                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ user.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(user.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link :href="route('users.edit', user.id)"
                                                    class="text-indigo-600 hover:text-indigo-900">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                                </Link>
                                                <button @click="confirmDelete(user)"
                                                    class="text-red-600 hover:text-red-900">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="flex items-center justify-between mt-6">
                            <div class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ users.length ? (currentPage - 1) * entriesPerPage + 1 : 0
                                    }}</span>
                                to
                                <span class="font-medium">{{ Math.min(currentPage * entriesPerPage, totalUsers)
                                    }}</span>
                                of
                                <span class="font-medium">{{ totalUsers }}</span>
                                results
                            </div>

                            <div class="flex justify-end space-x-1">
                                <PrimaryButton :disabled="currentPage === 1" @click="changePage(currentPage - 1)"
                                    class="px-3">
                                    Previous
                                </PrimaryButton>

                                <PrimaryButton v-for="page in totalPages" :key="page" @click="changePage(page)"
                                    :class="{ 'bg-indigo-800': page === currentPage }" class="px-3">
                                    {{ page }}
                                </PrimaryButton>

                                <PrimaryButton :disabled="currentPage === totalPages"
                                    @click="changePage(currentPage + 1)" class="px-3">
                                    Next
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div v-if="toast.visible" :class="[
            'fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg max-w-md transition-all duration-300',
            toast.type === 'error' ? 'bg-red-500 text-white' :
                toast.type === 'success' ? 'bg-green-500 text-white' :
                    toast.type === 'warning' ? 'bg-yellow-500 text-white' :
                        'bg-blue-500 text-white'
        ]">
            <div class="flex items-center">
                <!-- Icon based on type -->
                <div v-if="toast.type === 'error'" class="mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div v-else-if="toast.type === 'success'" class="mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div v-else class="mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <!-- Message -->
                <div class="flex-1">{{ toast.message }}</div>

                <!-- Close button -->
                <button @click="hideToast" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Delete User Confirmation Modal -->
        <Modal :show="deleteModalOpen" @close="closeDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete User
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeDeleteModal">
                        Cancel
                    </SecondaryButton>

                    <DangerButton @click="deleteUser" class="ml-3">
                        Delete User
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

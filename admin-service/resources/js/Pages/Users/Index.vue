<template>
    <AppLayout title="Users">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Users Management</h2>
                </div>

                <div class="mb-6 flex gap-4 justify-between items-center">
                    <div class="flex gap-4 flex-1">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name or email..."
                            class="flex-1 px-4 py-2 border rounded-lg"
                            @keyup.enter="applyFilters"
                        />
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            Search
                        </button>
                    </div>
                    <button
                        @click="showCreateModal = true"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 whitespace-nowrap"
                    >
                        + Create User
                    </button>
                </div>

                <div v-if="usersList.length > 0" class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left">Name</th>
                                <th class="px-6 py-3 text-left">Email</th>
                                <th class="px-6 py-3 text-left">Phone</th>
                                <th class="px-6 py-3 text-left">Role</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in usersList" :key="user.id" class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">{{ user.first_name }} {{ user.last_name }}</td>
                                <td class="px-6 py-4">{{ user.email }}</td>
                                <td class="px-6 py-4">{{ user.phone }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                        {{ user.role?.label || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-sm',
                                            user.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="openModal(user)"
                                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-12 bg-white rounded-lg">
                    <p class="text-gray-500">No users found</p>
                </div>

                <!-- Pagination -->
                <div v-if="total > 0" class="mt-6 flex flex-col items-center justify-between sm:flex-row">
                    <div class="text-sm text-gray-600">
                        Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} users
                    </div>
                    
                    <div class="mt-4 flex gap-2 sm:mt-0">
                        <button
                            :disabled="currentPage <= 1"
                            @click="goToPage(currentPage - 1)"
                            class="px-3 py-1 text-sm border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                        >
                            ← Previous
                        </button>

                        <div class="flex gap-1">
                            <button
                                v-for="page in pageNumbers"
                                :key="page"
                                :class="[
                                    'px-3 py-1 text-sm border rounded-lg',
                                    currentPage === page
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'hover:bg-gray-50'
                                ]"
                                @click="goToPage(page)"
                            >
                                {{ page }}
                            </button>
                        </div>

                        <button
                            :disabled="currentPage >= lastPage"
                            @click="goToPage(currentPage + 1)"
                            class="px-3 py-1 text-sm border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                        >
                            Next →
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Edit User</h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form v-if="selectedUser" @submit.prevent="submitEditUser" class="space-y-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">First Name *</label>
                        <input
                            v-model="editForm.first_name"
                            type="text"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Last Name *</label>
                        <input
                            v-model="editForm.last_name"
                            type="text"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Email *</label>
                        <input
                            v-model="editForm.email"
                            type="email"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Phone</label>
                        <input
                            v-model="editForm.phone"
                            type="tel"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Role *</label>
                        <select
                            v-model.number="editForm.role_id"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                            <option value="1">Administrator</option>
                            <option value="2">Customer</option>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold disabled:opacity-50"
                        >
                            {{ isLoading ? 'Updating...' : 'Update' }}
                        </button>
                        <button
                            type="button"
                            @click="toggleUserStatus"
                            :class="[
                                'flex-1 px-4 py-2 rounded-lg text-white font-semibold',
                                selectedUser?.is_active
                                    ? 'bg-yellow-600 hover:bg-yellow-700'
                                    : 'bg-green-600 hover:bg-green-700'
                            ]"
                            :disabled="isLoading"
                        >
                            {{ selectedUser?.is_active ? 'Inative' : 'Active' }}
                        </button>
                        <button
                            type="button"
                            @click="deleteUser"
                            :disabled="isLoading"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold disabled:opacity-50"
                        >
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create User Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Create New User</h3>
                    <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form @submit.prevent="submitCreateUser" class="space-y-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">First Name *</label>
                        <input
                            v-model="formData.first_name"
                            type="text"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Last Name *</label>
                        <input
                            v-model="formData.last_name"
                            type="text"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Email *</label>
                        <input
                            v-model="formData.email"
                            type="email"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Phone</label>
                        <input
                            v-model="formData.phone"
                            type="tel"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Password *</label>
                        <input
                            v-model="formData.password"
                            type="password"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                            minlength="8"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Role *</label>
                        <select
                            v-model.number="formData.role_id"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                            <option value="">Select a role</option>
                            <option value="1">Administrator</option>
                            <option value="2">Customer</option>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="isCreating"
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold disabled:opacity-50"
                        >
                            {{ isCreating ? 'Creating...' : 'Create User' }}
                        </button>
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="flex-1 px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg font-semibold"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    users: {
        type: [Object, Array],
        default: () => ({ data: [], meta: {} }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters?.search ?? '');
const showModal = ref(false);
const selectedUser = ref(null);
const isLoading = ref(false);
const showCreateModal = ref(false);
const isCreating = ref(false);
const editForm = ref({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    role_id: '',
    is_active: true,
});
const formData = ref({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    role_id: '',
});

const usersList = computed(() => {
    if (Array.isArray(props.users)) return props.users;
    if (Array.isArray(props.users?.data)) return props.users.data;
    return [];
});

const currentPage = computed(() => props.users?.meta?.current_page ?? 1);
const total = computed(() => props.users?.meta?.total ?? usersList.value.length);
const perPage = computed(() => props.users?.meta?.per_page ?? 15);

const lastPage = computed(() => props.users?.meta?.last_page ?? 1);

const pageNumbers = computed(() => {
    const pages = [];
    const maxPagesToShow = 5;
    const halfWindow = Math.floor(maxPagesToShow / 2);
    let startPage = Math.max(1, currentPage.value - halfWindow);
    let endPage = Math.min(lastPage.value, startPage + maxPagesToShow - 1);

    if (endPage - startPage < maxPagesToShow - 1) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
    }
    return pages;
});

const goToPage = (page) => {
    if (page < 1 || page > lastPage.value) return;
    
    router.get(route('users.index'), {
        search: search.value || undefined,
        page: page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['users', 'filters'],
    });
};

const applyFilters = () => {
    router.get(route('users.index'), {
        search: search.value || undefined,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['users', 'filters'],
    });
};

const openModal = (user) => {
    selectedUser.value = { ...user };
    editForm.value = {
        first_name: user.first_name,
        last_name: user.last_name,
        email: user.email,
        phone: user.phone || '',
        role_id: user.role?.id || 2,
        is_active: user.is_active,
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedUser.value = null;
    editForm.value = {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        role_id: '',
        is_active: true,
    };
};

const submitEditUser = () => {
    if (!selectedUser.value || isLoading.value) return;
    
    isLoading.value = true;
    const userId = selectedUser.value.id;
    
    router.put(`/users/${userId}`, editForm.value, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            closeModal();

            router.get(route('users.index'));
        },
    });
};

const toggleUserStatus = () => {
    if (!selectedUser.value || isLoading.value) return;
    
    isLoading.value = true;
    const userId = selectedUser.value.id;
    
    router.put(`/users/toggle-status/${userId}`, {
        is_active: !selectedUser.value.is_active,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            closeModal();
            // Reload the users list
            router.get(route('users.index'));
        },
    });
};

const deleteUser = () => {
    if (!selectedUser.value || isLoading.value) return;
    
    if (!confirm(`Are you sure you want to delete ${selectedUser.value.first_name} ${selectedUser.value.last_name}?`)) {
        return;
    }
    
    isLoading.value = true;
    const userId = selectedUser.value.id;
    
    router.delete(`/users/${userId}`, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            closeModal();

            router.get(route('users.index'));
        },
    });
};

const submitCreateUser = () => {
    if (isCreating.value) return;
    
    isCreating.value = true;
    router.post('/users', formData.value, {
        preserveScroll: true,

        onFinish: () => {
            isCreating.value = false;
            showCreateModal.value = false;

            formData.value = {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                password: '',
                role_id: '',
            };

            router.get(route('users.index'));
        },
        onError: (errors) => {
            isCreating.value = false;
            console.error(errors);
            alert('Failed to create user. Please check the form and try again.');
        },
    });
};
</script>

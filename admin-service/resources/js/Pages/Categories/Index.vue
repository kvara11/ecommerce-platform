<template>
    <AppLayout title="Categories">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Categories Management</h2>
                </div>

                <div class="mb-6 flex gap-4 justify-between items-center">
                    <div class="flex gap-4 flex-1">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name or description..."
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
                        + Create Category
                    </button>
                </div>

                <div v-if="categoriesList.length > 0" class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left">Image</th>
                                <th class="px-6 py-3 text-left">Category</th>
                                <th class="px-6 py-3 text-left">Products</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="category in categoryTree" :key="category.id">
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <img
                                            v-if="category.image_url"
                                            :src="category.image_url"
                                            :alt="category.name"
                                            class="w-12 h-12 object-cover rounded"
                                        />
                                        <div v-else class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">No image</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <button
                                                v-if="category.children && category.children.length > 0"
                                                @click="toggleExpand(category.id)"
                                                :class="[
                                                    'w-6 h-6 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded transition-all',
                                                    expandedCategories.includes(category.id) ? 'rotate-90' : ''
                                                ]"
                                            >
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <span v-else class="w-6"></span>
                                            <span class="font-semibold" :style="{ paddingLeft: category.level * 20 + 'px' }">
                                                {{ category.name }}
                                            </span>
                                            <span v-if="category.children && category.children.length > 0" class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">
                                                {{ category.children.length }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                            {{ category.products_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-sm',
                                                category.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ category.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button
                                            @click="openModal(category)"
                                            class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>

                                <!-- Render children recursively -->
                                <template v-if="expandedCategories.includes(category.id) && category.children">
                                    <CategoryRow
                                        v-for="child in category.children"
                                        :key="child.id"
                                        :category="child"
                                        :expanded="expandedCategories"
                                        @toggle="toggleExpand"
                                        @open-modal="openModal"
                                    />
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-12 bg-white rounded-lg">
                    <p class="text-gray-500">No categories found</p>
                </div>

                <!-- Pagination -->
                <div v-if="total > 0" class="mt-6 flex flex-col items-center justify-between sm:flex-row">
                    <div class="text-sm text-gray-600">
                        Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} categories
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

        <!-- Category Details Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Edit Category</h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form v-if="selectedCategory" @submit.prevent="submitEditCategory" class="space-y-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Category Name *</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="editForm.description"
                            rows="3"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Parent Category</label>
                        <select
                            v-model.number="editForm.parent_id"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option :value="null">No Parent (Root Category)</option>
                            <option v-for="parent in parentCategories" :key="parent.id" :value="parent.id">
                                {{ parent.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Sort Order</label>
                        <input
                            v-model.number="editForm.sort_order"
                            type="number"
                            min="0"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Upload Image</label>
                        <input
                            @change="handleEditImageUpload"
                            type="file"
                            accept="image/*"
                            class="w-full px-3 py-2 border rounded-lg"
                        />
                        <div v-if="selectedCategory?.image_url" class="mt-2">
                            <img
                                :src="selectedCategory.image_url"
                                :alt="selectedCategory.name"
                                class="w-32 h-32 object-cover rounded"
                            />
                        </div>
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
                            @click="toggleCategoryStatus"
                            :class="[
                                'flex-1 px-4 py-2 rounded-lg text-white font-semibold',
                                selectedCategory?.is_active
                                    ? 'bg-yellow-600 hover:bg-yellow-700'
                                    : 'bg-green-600 hover:bg-green-700'
                            ]"
                            :disabled="isLoading"
                        >
                            {{ selectedCategory?.is_active ? 'Inactive' : 'Active' }}
                        </button>
                        <button
                            type="button"
                            @click="deleteCategory"
                            :disabled="isLoading"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold disabled:opacity-50"
                        >
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Category Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Create New Category</h3>
                    <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form @submit.prevent="submitCreateCategory" class="space-y-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Category Name *</label>
                        <input
                            v-model="formData.name"
                            type="text"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="formData.description"
                            rows="3"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Parent Category</label>
                        <select
                            v-model.number="formData.parent_id"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option :value="null">No Parent (Root Category)</option>
                            <option v-for="parent in parentCategories" :key="parent.id" :value="parent.id">
                                {{ parent.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Sort Order</label>
                        <input
                            v-model.number="formData.sort_order"
                            type="number"
                            min="0"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Upload Image</label>
                        <input
                            @change="handleImageUpload"
                            type="file"
                            accept="image/*"
                            class="w-full px-3 py-2 border rounded-lg"
                        />
                        <div v-if="imagePreview" class="mt-2">
                            <img :src="imagePreview" alt="preview" class="w-32 h-32 object-cover rounded" />
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="isCreating"
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold disabled:opacity-50"
                        >
                            {{ isCreating ? 'Creating...' : 'Create Category' }}
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
import CategoryRow from '@/Components/CategoryRow.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    categories: {
        type: [Object, Array],
        default: () => ({ data: [], meta: {} }),
    },
    parentCategories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters?.search ?? '');
const showModal = ref(false);
const selectedCategory = ref(null);
const isLoading = ref(false);
const showCreateModal = ref(false);
const isCreating = ref(false);
const imagePreview = ref('');
const editImageFile = ref(null);
const expandedCategories = ref([]);

const editForm = ref({
    name: '',
    description: '',
    parent_id: null,
    sort_order: 0,
});

const formData = ref({
    name: '',
    description: '',
    parent_id: null,
    sort_order: 0,
});

const parentCategories = ref(props.parentCategories);

const categoriesList = computed(() => {
    if (Array.isArray(props.categories)) return props.categories;
    if (Array.isArray(props.categories?.data)) return props.categories.data;
    return [];
});

const currentPage = computed(() => props.categories?.meta?.current_page ?? 1);
const total = computed(() => props.categories?.meta?.total ?? categoriesList.value.length);
const perPage = computed(() => props.categories?.meta?.per_page ?? 15);

const lastPage = computed(() => props.categories?.meta?.last_page ?? 1);

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

// Build tree structure from flat list
const categoryTree = computed(() => {
    const categories = categoriesList.value || [];
    const categoryMap = new Map();
    
    // First pass: create map with level info
    categories.forEach(cat => {
        categoryMap.set(cat.id, {
            ...cat,
            level: 0,
            children: [],
        });
    });
    
    // Second pass: build parent-child relationships
    const roots = [];
    categories.forEach(cat => {
        const mapCat = categoryMap.get(cat.id);
        if (cat.parent_id) {
            const parent = categoryMap.get(cat.parent_id);
            if (parent) {
                mapCat.level = parent.level + 1;
                parent.children.push(mapCat);
            } else {
                roots.push(mapCat);
            }
        } else {
            roots.push(mapCat);
        }
    });
    
    // Sort by sort_order
    const sortByOrder = (cats) => {
        return cats.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
    };
    
    roots.forEach(root => {
        const sortChildren = (children) => {
            sortByOrder(children).forEach(child => {
                if (child.children.length > 0) {
                    sortChildren(child.children);
                }
            });
        };
        if (root.children.length > 0) {
            sortChildren(root.children);
        }
    });
    
    const result = sortByOrder(roots);
    console.log('Category Tree:', result);
    return result;
});

const toggleExpand = (categoryId) => {
    const index = expandedCategories.value.indexOf(categoryId);
    if (index > -1) {
        expandedCategories.value.splice(index, 1);
    } else {
        expandedCategories.value.push(categoryId);
    }
};

const goToPage = (page) => {
    if (page < 1 || page > lastPage.value) return;
    
    router.get(route('categories.index'), {
        search: search.value || undefined,
        page: page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['categories', 'filters'],
    });
};

const applyFilters = () => {
    router.get(route('categories.index'), {
        search: search.value || undefined,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['categories', 'filters'],
    });
};

const openModal = (category) => {
    selectedCategory.value = { ...category };
    editForm.value = {
        name: category.name,
        description: category.description || '',
        parent_id: category.parent?.id || null,
        sort_order: category.sort_order || 0,
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedCategory.value = null;
    editImageFile.value = null;
    editForm.value = {
        name: '',
        description: '',
        parent_id: null,
        sort_order: 0,
    };
};

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
        formData.value.image = file;
    }
};

const handleEditImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            if (selectedCategory.value) {
                selectedCategory.value.image_url = e.target.result;
            }
        };
        reader.readAsDataURL(file);
        editImageFile.value = file;
    }
};

const submitEditCategory = () => {
    if (!selectedCategory.value || isLoading.value) return;
    
    isLoading.value = true;
    const categoryId = selectedCategory.value.id;
    
    const formDataToSend = new FormData();
    formDataToSend.append('name', editForm.value.name);
    formDataToSend.append('description', editForm.value.description);
    formDataToSend.append('parent_id', editForm.value.parent_id);
    formDataToSend.append('sort_order', editForm.value.sort_order);
    formDataToSend.append('_method', 'PUT');
    
    if (editImageFile.value) {
        formDataToSend.append('image', editImageFile.value);
    }
    
    router.post(`/categories/${categoryId}`, formDataToSend, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            closeModal();
            router.get(route('categories.index'));
        },
        onError: (errors) => {
            isLoading.value = false;
            console.error(errors);
            alert('Failed to update category. Please check the form and try again.');
        },
    });
};

const toggleCategoryStatus = () => {
    if (!selectedCategory.value || isLoading.value) return;
    
    isLoading.value = true;
    const categoryId = selectedCategory.value.id;
    
    router.put(`/categories/toggle-status/${categoryId}`, {
        is_active: !selectedCategory.value.is_active,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            closeModal();
            router.get(route('categories.index'));
        },
        onError: (errors) => {
            isLoading.value = false;
            console.error(errors);
            alert('Failed to update category status. Please try again.');
        },
    });
};

const deleteCategory = () => {
    if (!selectedCategory.value || isLoading.value) return;
    
    if (!confirm(`Are you sure you want to delete ${selectedCategory.value.name}?`)) {
        return;
    }
    
    isLoading.value = true;
    const categoryId = selectedCategory.value.id;
    
    router.delete(`/categories/${categoryId}`, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            closeModal();
            router.get(route('categories.index'));
        },
        onError: (errors) => {
            isLoading.value = false;
            console.error(errors);
            alert('Failed to delete category. Please try again.');
        },
    });
};

const submitCreateCategory = () => {
    if (isCreating.value) return;
    
    isCreating.value = true;
    
    const formDataToSend = new FormData();
    formDataToSend.append('name', formData.value.name);
    formDataToSend.append('description', formData.value.description);
    formDataToSend.append('parent_id', formData.value.parent_id);
    formDataToSend.append('sort_order', formData.value.sort_order);
    
    if (formData.value.image) {
        formDataToSend.append('image', formData.value.image);
    }
    
    router.post('/categories', formDataToSend, {
        preserveScroll: true,
        onFinish: () => {
            isCreating.value = false;
            showCreateModal.value = false;
            
            imagePreview.value = '';
            formData.value = {
                name: '',
                description: '',
                parent_id: null,
                sort_order: 0,
            };
            
            router.get(route('categories.index'));
        },
        onError: (errors) => {
            isCreating.value = false;
            console.error(errors);
            alert('Failed to create category. Please check the form and try again.');
        },
    });
};
</script>

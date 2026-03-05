<template>
    <AppLayout title="Products">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Products Management</h2>
                </div>

                <div class="mb-6 flex gap-4 justify-between items-center">
                    <div class="flex gap-4 flex-1">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name or SKU..."
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
                        + Create Product
                    </button>
                </div>

                <div v-if="productsList.length > 0" class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left">Image</th>
                                <th class="px-6 py-3 text-left">Name</th>
                                <th class="px-6 py-3 text-left">Category</th>
                                <th class="px-6 py-3 text-left">Price</th>
                                <th class="px-6 py-3 text-left">Stock</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in productsList" :key="product.id" class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <img
                                        v-if="product.image_url"
                                        :src="product.image_url"
                                        :alt="product.name"
                                        class="w-12 h-12 object-cover rounded"
                                    />

                                    <div v-else class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                        <span class="text-gray-400 text-xs">No image</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ product.name }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm">
                                        {{ product.category?.name || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold">${{ parseFloat(product.price).toFixed(2) }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-sm',
                                            product.quantity > 10
                                                ? 'bg-green-100 text-green-800'
                                                : product.quantity > 0
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        {{ product.quantity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-sm',
                                            product.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        {{ product.status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="openModal(product)"
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
                    <p class="text-gray-500">No products found</p>
                </div>

                <!-- Pagination -->
                <div v-if="total > 0" class="mt-6 flex flex-col items-center justify-between sm:flex-row">
                    <div class="text-sm text-gray-600">
                        Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} products
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

        <!-- Product Details Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Edit Product</h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form v-if="selectedProduct" @submit.prevent="submitEditProduct" class="space-y-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Product Name *</label>
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
                        <label class="block font-semibold text-gray-700 mb-1">Category *</label>
                        <select
                            v-model.number="editForm.category_id"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                            <option value="">Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Price *</label>
                        <input
                            v-model.number="editForm.price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Stock Quantity *</label>
                        <input
                            v-model.number="editForm.quantity"
                            type="number"
                            min="0"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
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
                        <div v-if="selectedProduct?.image_url" class="mt-2">
                            <img
                                :src="selectedProduct.image_url"
                                :alt="selectedProduct.name"
                                class="w-32 h-32 object-cover rounded"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Status *</label>
                        <select
                            v-model="editForm.status"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
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
                            @click="deleteProduct"
                            :disabled="isLoading"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold disabled:opacity-50"
                        >
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Product Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Create New Product</h3>
                    <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form @submit.prevent="submitCreateProduct" class="space-y-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Product Name *</label>
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
                        <label class="block font-semibold text-gray-700 mb-1">Category *</label>
                        <select
                            v-model.number="formData.category_id"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                            <option value="">Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Price *</label>
                        <input
                            v-model.number="formData.price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Stock Quantity *</label>
                        <input
                            v-model.number="formData.quantity"
                            type="number"
                            min="0"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Upload Image *</label>
                        <input
                            @change="handleImageUpload"
                            type="file"
                            accept="image/*"
                            class="w-full px-3 py-2 border rounded-lg"
                            required
                        />
                        <div v-if="imagePreview" class="mt-2">
                            <img :src="imagePreview" alt="preview" class="w-32 h-32 object-cover rounded" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Status *</label>
                        <select
                            v-model="formData.status"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                            <option value="">Select status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="isCreating"
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold disabled:opacity-50"
                        >
                            {{ isCreating ? 'Creating...' : 'Create Product' }}
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
    products: {
        type: [Object, Array],
        default: () => ({ data: [], meta: {} }),
    },
    categories: {
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
const selectedProduct = ref(null);
const isLoading = ref(false);
const showCreateModal = ref(false);
const isCreating = ref(false);
const imagePreview = ref('');
const editImageFile = ref(null);

const editForm = ref({
    name: '',
    description: '',
    category_id: '',
    price: '',
    quantity: '',
    status: 'active',
});

const formData = ref({
    name: '',
    description: '',
    category_id: '',
    price: '',
    quantity: '',
    status: 'active',
});

const categories = ref(props.categories);

const productsList = computed(() => {
    if (Array.isArray(props.products)) return props.products;
    if (Array.isArray(props.products?.data)) return props.products.data;
    return [];
});

console.log(props.products);


const currentPage = computed(() => props.products?.meta?.current_page ?? 1);
const total = computed(() => props.products?.meta?.total ?? productsList.value.length);
const perPage = computed(() => props.products?.meta?.per_page ?? 15);

const lastPage = computed(() => props.products?.meta?.last_page ?? 1);

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
    
    router.get(route('products.index'), {
        search: search.value || undefined,
        page: page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['products', 'filters'],
    });
};

const applyFilters = () => {
    router.get(route('products.index'), {
        search: search.value || undefined,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['products', 'filters'],
    });
};

const openModal = (product) => {
    selectedProduct.value = { ...product };
    editForm.value = {
        name: product.name,
        description: product.description || '',
        category_id: product.category?.id || '',
        price: product.price,
        quantity: product.quantity,
        status: product.status || 'active',
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedProduct.value = null;
    editImageFile.value = null;
    editForm.value = {
        name: '',
        description: '',
        category_id: '',
        price: '',
        quantity: '',
        status: 'active',
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
            if (selectedProduct.value) {
                selectedProduct.value.image_url = e.target.result;
            }
        };
        reader.readAsDataURL(file);
        editImageFile.value = file;
    }
};

const submitEditProduct = () => {
    if (!selectedProduct.value || isLoading.value) return;
    
    isLoading.value = true;
    const productId = selectedProduct.value.id;
    
    const formDataToSend = new FormData();
    formDataToSend.append('name', editForm.value.name);
    formDataToSend.append('description', editForm.value.description);
    formDataToSend.append('category_id', editForm.value.category_id);
    formDataToSend.append('price', editForm.value.price);
    formDataToSend.append('quantity', editForm.value.quantity);
    formDataToSend.append('status', editForm.value.status);
    formDataToSend.append('_method', 'PUT');
    
    if (editImageFile.value) {
        formDataToSend.append('image', editImageFile.value);
    }
    
    router.post(`/products/${productId}`, formDataToSend, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            closeModal();
            router.get(route('products.index'));
        },
        onError: (errors) => {
            isLoading.value = false;
            console.error(errors);
            alert('Failed to update product. Please check the form and try again.');
        },
    });
};

const deleteProduct = () => {
    if (!selectedProduct.value || isLoading.value) return;
    
    if (!confirm(`Are you sure you want to delete ${selectedProduct.value.name}?`)) {
        return;
    }
    
    isLoading.value = true;
    const productId = selectedProduct.value.id;
    
    router.delete(`/products/${productId}`, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            closeModal();
            router.get(route('products.index'));
        },
        onError: (errors) => {
            isLoading.value = false;
            console.error(errors);
            alert('Failed to delete product. Please try again.');
        },
    });
};

const submitCreateProduct = () => {
    if (isCreating.value) return;
    
    isCreating.value = true;
    
    const formDataToSend = new FormData();
    formDataToSend.append('name', formData.value.name);
    formDataToSend.append('description', formData.value.description);
    formDataToSend.append('category_id', formData.value.category_id);
    formDataToSend.append('price', formData.value.price);
    formDataToSend.append('quantity', formData.value.quantity);
    formDataToSend.append('status', formData.value.status);
    
    if (formData.value.image) {
        formDataToSend.append('image', formData.value.image);
    }
    
    router.post('/products', formDataToSend, {
        preserveScroll: true,
        onFinish: () => {
            isCreating.value = false;
            showCreateModal.value = false;
            
            imagePreview.value = '';
            formData.value = {
                name: '',
                description: '',
                category_id: '',
                price: '',
                quantity: '',
                status: 'active',
            };
            
            router.get(route('products.index'));
        },
        onError: (errors) => {
            isCreating.value = false;
            console.error(errors);
            alert('Failed to create product. Please check the form and try again.');
        },
    });
};
</script>

<template>
    <AppLayout title="Orders">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Orders Management</h2>
                </div>

                <div class="mb-6 space-y-4">
                    <div class="flex gap-4 justify-between items-center">
                        <div class="flex gap-4 flex-1">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by order number, customer..."
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
                    </div>

                    <div class="flex gap-4 bg-white p-4 rounded-lg shadow">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                            <select
                                v-model="filterStatus"
                                class="w-full px-3 py-2 border rounded-lg"
                                @change="applyFilters"
                            >
                                <option value="">All Statuses</option>
                                <option v-for="status in orderStatuses" :key="status.id" :value="status.id">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Payment Status</label>
                            <select
                                v-model="filterPaymentStatus"
                                class="w-full px-3 py-2 border rounded-lg"
                                @change="applyFilters"
                            >
                                <option value="">All Payment Statuses</option>
                                <option v-for="status in paymentStatuses" :key="status.id" :value="status.id">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">From Date</label>
                            <input
                                v-model="filterDateFrom"
                                type="date"
                                class="w-full px-3 py-2 border rounded-lg"
                                @change="applyFilters"
                            />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">To Date</label>
                            <input
                                v-model="filterDateTo"
                                type="date"
                                class="w-full px-3 py-2 border rounded-lg"
                                @change="applyFilters"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="ordersList.length > 0" class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left">Order #</th>
                                <th class="px-6 py-3 text-left">Customer</th>
                                <th class="px-6 py-3 text-left">Total</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Payment</th>
                                <th class="px-6 py-3 text-left">Items</th>
                                <th class="px-6 py-3 text-left">Date</th>
                                <th class="px-6 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in ordersList" :key="order.id" class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold">{{ order.order_number }}</td>
                                <td class="px-6 py-4">
                                    <div v-if="order.customer" class="text-sm">
                                        <div class="font-semibold">{{ order.customer.first_name }} {{ order.customer.last_name }}</div>
                                        <div class="text-gray-500">{{ order.customer.email }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold">
                                    {{ order.currency }} {{ parseFloat(order.total_amount).toFixed(2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-sm font-semibold',
                                            getStatusColor(order.status?.name)
                                        ]"
                                    >
                                        {{ order.status?.label || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-sm',
                                            order.payment_status?.name === 'paid'
                                                ? 'bg-green-100 text-green-800'
                                                : order.payment_status?.name === 'pending'
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        {{ order.payment_status?.label || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                        {{ order.items_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ formatDate(order.created_at) }}</td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="openModal(order)"
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
                    <p class="text-gray-500">No orders found</p>
                </div>

                <!-- Pagination -->
                <div v-if="total > 0" class="mt-6 flex flex-col items-center justify-between sm:flex-row">
                    <div class="text-sm text-gray-600">
                        Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} orders
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

        <!-- Order Details Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Order #{{ selectedOrder?.order_number }}</h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form v-if="selectedOrder" @submit.prevent="submitUpdateOrder" class="space-y-6">
                    <!-- Order Summary -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold mb-3">Order Information</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Order Date:</span>
                                <p class="font-semibold">{{ formatDate(selectedOrder.created_at) }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Payment Method:</span>
                                <p class="font-semibold">{{ selectedOrder.payment_method?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Shipped:</span>
                                <p class="font-semibold">{{ selectedOrder.shipped_at ? formatDate(selectedOrder.shipped_at) : 'Not shipped' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Delivered:</span>
                                <p class="font-semibold">{{ selectedOrder.delivered_at ? formatDate(selectedOrder.delivered_at) : 'Not delivered' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold mb-3">Customer</h4>
                        <div v-if="selectedOrder.customer" class="text-sm">
                            <p><span class="text-gray-600">Name:</span> {{ selectedOrder.customer.first_name }} {{ selectedOrder.customer.last_name }}</p>
                            <p><span class="text-gray-600">Email:</span> {{ selectedOrder.customer.email }}</p>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div>
                        <h4 class="font-semibold mb-3">Order Items</h4>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Product</th>
                                        <th class="px-4 py-2 text-right">Qty</th>
                                        <th class="px-4 py-2 text-right">Unit Price</th>
                                        <th class="px-4 py-2 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in itemsForDisplay" :key="item.id" class="border-t">
                                        <td class="px-4 py-2">{{ item.product_name }}</td>
                                        <td class="px-4 py-2 text-right">{{ item.quantity }}</td>
                                        <td class="px-4 py-2 text-right">{{ selectedOrder.currency }} {{ parseFloat(item.unit_price).toFixed(2) }}</td>
                                        <td class="px-4 py-2 text-right font-semibold">{{ selectedOrder.currency }} {{ parseFloat(item.total_amount).toFixed(2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Order Total -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span>{{ selectedOrder.currency }} {{ parseFloat(selectedOrder.subtotal).toFixed(2) }}</span>
                            </div>
                            <div v-if="selectedOrder.tax_amount > 0" class="flex justify-between">
                                <span class="text-gray-600">Tax:</span>
                                <span>{{ selectedOrder.currency }} {{ parseFloat(selectedOrder.tax_amount).toFixed(2) }}</span>
                            </div>
                            <div v-if="selectedOrder.shipping_amount > 0" class="flex justify-between">
                                <span class="text-gray-600">Shipping:</span>
                                <span>{{ selectedOrder.currency }} {{ parseFloat(selectedOrder.shipping_amount).toFixed(2) }}</span>
                            </div>
                            <div v-if="selectedOrder.discount_amount > 0" class="flex justify-between text-red-600">
                                <span>Discount:</span>
                                <span>-{{ selectedOrder.currency }} {{ parseFloat(selectedOrder.discount_amount).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2 font-bold">
                                <span>Total:</span>
                                <span>{{ selectedOrder.currency }} {{ parseFloat(selectedOrder.total_amount).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Updates -->
                    <div class="space-y-4">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Order Status</label>
                            <select
                                v-model.number="editForm.status_id"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option v-for="status in orderStatuses" :key="status.id" :value="status.id">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Payment Status</label>
                            <select
                                v-model.number="editForm.payment_status_id"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option :value="null">Select Payment Status</option>
                                <option v-for="status in paymentStatuses" :key="status.id" :value="status.id">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Notes</label>
                            <textarea
                                v-model="editForm.notes"
                                rows="3"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold disabled:opacity-50"
                        >
                            {{ isLoading ? 'Updating...' : 'Update Order' }}
                        </button>
                        <button
                            type="button"
                            @click="closeModal"
                            class="flex-1 px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg font-semibold"
                        >
                            Close
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
    orders: {
        type: [Object, Array],
        default: () => ({ data: [], meta: {} }),
    },
    orderStatuses: {
        type: Array,
        default: () => [],
    },
    paymentStatuses: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters?.search ?? '');
const filterStatus = ref(props.filters?.status_id ?? '');
const filterPaymentStatus = ref(props.filters?.payment_status_id ?? '');
const filterDateFrom = ref(props.filters?.date_from ?? '');
const filterDateTo = ref(props.filters?.date_to ?? '');

const showModal = ref(false);
const selectedOrder = ref(null);
const isLoading = ref(false);

const editForm = ref({
    status_id: '',
    payment_status_id: null,
    notes: '',
});

const orderStatuses = ref(props.orderStatuses);
const paymentStatuses = ref(props.paymentStatuses);

const ordersList = computed(() => {
    if (Array.isArray(props.orders)) return props.orders;
    if (Array.isArray(props.orders?.data)) return props.orders.data;
    return [];
});

const itemsForDisplay = computed(() => {
    return selectedOrder.value?.items || [];
});

const currentPage = computed(() => props.orders?.meta?.current_page ?? 1);
const total = computed(() => props.orders?.meta?.total ?? ordersList.value.length);
const perPage = computed(() => props.orders?.meta?.per_page ?? 15);
const lastPage = computed(() => props.orders?.meta?.last_page ?? 1);

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
    
    router.get(route('orders.index'), {
        search: search.value || undefined,
        status_id: filterStatus.value || undefined,
        payment_status_id: filterPaymentStatus.value || undefined,
        date_from: filterDateFrom.value || undefined,
        date_to: filterDateTo.value || undefined,
        page: page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['orders', 'filters'],
    });
};

const applyFilters = () => {
    router.get(route('orders.index'), {
        search: search.value || undefined,
        status_id: filterStatus.value || undefined,
        payment_status_id: filterPaymentStatus.value || undefined,
        date_from: filterDateFrom.value || undefined,
        date_to: filterDateTo.value || undefined,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['orders', 'filters'],
    });
};

const openModal = (order) => {
    selectedOrder.value = { ...order };
    editForm.value = {
        status_id: order.status?.id,
        payment_status_id: order.payment_status?.id || null,
        notes: order.notes || '',
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedOrder.value = null;
};

const submitUpdateOrder = () => {
    if (!selectedOrder.value || isLoading.value) return;
    
    isLoading.value = true;
    const orderId = selectedOrder.value.id;
    
    router.put(`/orders/${orderId}`, editForm.value, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            closeModal();
            router.get(route('orders.index'));
        },
        onError: (errors) => {
            isLoading.value = false;
            console.error(errors);
            alert('Failed to update order. Please check the form and try again.');
        },
    });
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        confirmed: 'bg-cyan-100 text-cyan-800',
        shipped: 'bg-purple-100 text-purple-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        refunded: 'bg-orange-100 text-orange-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

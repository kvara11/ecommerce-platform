<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({
            total_orders: 0,
            pending_orders: 0,
            total_revenue: 0,
            total_products: 0,
            active_products: 0,
            total_customers: 0,
            low_stock_products: 0,
        })
    },
    recentOrders: {
        type: Array,
        default: () => []
    },
    salesData: {
        type: Array,
        default: () => []
    },
    topProducts: {
        type: Array,
        default: () => []
    }
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('ka-GE', {
        style: 'currency',
        currency: 'GEL',
    }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const maxRevenue = computed(() => {
    if (props.salesData.length === 0) return 1;
    return Math.max(...props.salesData.map(s => s.revenue));
});
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Dashboard Overivew</h2>
                </div>

                <!-- Metrics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Orders -->
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Total Orders</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ metrics.total_orders }}</p>
                            </div>
                            <div class="bg-blue-100 rounded-lg p-3">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Orders -->
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Pending Orders</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ metrics.pending_orders }}</p>
                            </div>
                            <div class="bg-yellow-100 rounded-lg p-3">
                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ formatCurrency(metrics.total_revenue) }}</p>
                            </div>
                            <div class="bg-green-100 rounded-lg p-3">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Products -->
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Total Products</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ metrics.total_products }}</p>
                            </div>
                            <div class="bg-purple-100 rounded-lg p-3">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Active Products -->
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Active Products</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ metrics.active_products }}</p>
                            </div>
                            <div class="bg-indigo-100 rounded-lg p-3">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Customers -->
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-pink-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Total Customers</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ metrics.total_customers }}</p>
                            </div>
                            <div class="bg-pink-100 rounded-lg p-3">
                                <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM15 20H9m6-20H9m6 20v-2a6 6 0 00-9-5.497V13a2 2 0 002 2h10a2 2 0 002-2v-.5"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Products -->
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Low Stock Products</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ metrics.low_stock_products }}</p>
                            </div>
                            <div class="bg-red-100 rounded-lg p-3">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">Order ID</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in recentOrders" :key="order.id" class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#{{ order.id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ order.user?.full_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ formatCurrency(order.total_amount) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span :class="[
                                            'inline-block px-3 py-1 rounded-full text-xs font-semibold',
                                                order.status_id === 3 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                            ]">

                                            {{ order.status_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span :class="[
                                            'inline-block px-3 py-1 rounded-full text-xs font-semibold',
                                            order.payment_status_id === 2 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        ]">
                                            {{ order.payment_status_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(order.created_at) }}</td>
                                </tr>
                                <tr v-if="recentOrders.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">No recent orders found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sales Chart Data & Top Products -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Sales Data -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Sales Data (Last 30 Days)</h3>
                        </div>
                        <div class="px-6 py-4">
                            <div class="space-y-4">
                                <div v-for="sale in salesData" :key="sale.date" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 min-w-[100px]">{{ formatDate(sale.date) }}</span>
                                    <div class="flex-1 mx-4 bg-gray-100 rounded-full h-3 overflow-hidden">
                                        <div class="bg-blue-600 h-full rounded-full transition-all duration-500" 
                                             :style="{ width: `${(sale.revenue / maxRevenue * 100)}%` }"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 min-w-[80px] text-right">{{ formatCurrency(sale.revenue) }}</span>
                                </div>
                                <div v-if="salesData.length === 0" class="text-center text-gray-500 py-8">No sales data available</div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Top Selling Products</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">Product Name</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">Total Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in topProducts" :key="product.id" class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ product.name }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-lg text-xs font-bold ring-1 ring-blue-200">
                                                {{ product.total_sold }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="topProducts.length === 0">
                                        <td colspan="2" class="px-6 py-8 text-center text-gray-500 italic">No product sales data found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<template>
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
                    @click="$emit('toggle', category.id)"
                    :class="[
                        'w-6 h-6 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded transition-all',
                        expanded.includes(category.id) ? 'rotate-90' : ''
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
                @click="$emit('open-modal', category)"
                class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                View
            </button>
        </td>
    </tr>

    <!-- Render children recursively -->
    <template v-if="expanded.includes(category.id) && category.children">
        <CategoryRow
            v-for="child in category.children"
            :key="child.id"
            :category="child"
            :expanded="expanded"
            @toggle="$emit('toggle', $event)"
            @open-modal="$emit('open-modal', $event)"
        />
    </template>
</template>

<script setup>
defineProps({
    category: {
        type: Object,
        required: true,
    },
    expanded: {
        type: Array,
        required: true,
    },
});

defineEmits(['toggle', 'open-modal']);
</script>

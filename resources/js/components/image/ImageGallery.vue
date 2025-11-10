<template>
  <div class="space-y-4">
    <h3 class="text-lg font-bold">画像ギャラリー ({{ images.length }}枚)</h3>
    
    <div v-if="images.length === 0" class="text-gray-500 text-center py-8 bg-gray-50 rounded-lg">
      画像がまだアップロードされていません
    </div>
    
    <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div v-for="image in images" :key="image.id" class="relative group">
        <img 
          :src="getImageUrl(image)" 
          :alt="image.original_name || 'Image'"
          class="w-full h-32 object-cover rounded-lg cursor-pointer shadow hover:shadow-lg transition-shadow"
          @click="handleView(image)"
        >
        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
          <button
            type="button"
            @click="handleDelete(image.id)"
            class="p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 shadow-lg"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2 rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity">
          <p class="text-xs text-white truncate">{{ image.original_name || 'Image' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  images: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['view', 'delete']);

const getImageUrl = (image) => {
  // Use thumbnail if available, otherwise use original
  if (image.thumbnail_path) {
    return `/storage/${image.thumbnail_path}`;
  }
  if (image.image_path) {
    return `/storage/${image.image_path}`;
  }
  // Fallback
  return image.file_path ? `/storage/${image.file_path}` : '';
};

const handleView = (image) => {
  const url = image.image_path ? `/storage/${image.image_path}` : `/storage/${image.file_path}`;
  window.open(url, '_blank');
};

const handleDelete = (imageId) => {
  if (confirm('この画像を削除しますか？')) {
    emit('delete', imageId);
  }
};
</script>

<template>
  <div class="space-y-4">
    <!-- Upload Area -->
    <div 
      class="border-2 border-dashed border-blue-400 rounded-lg p-8 text-center bg-blue-50 hover:bg-blue-100 hover:border-blue-500 transition-all cursor-pointer"
      @click="$refs.fileInput.click()"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      :class="{ 'border-blue-600 bg-blue-100': isDragging }"
    >
      <input
        ref="fileInput"
        type="file"
        multiple
        accept="image/*"
        @change="handleFileSelect"
        class="hidden"
      >
      
      <div>
        <!-- Icon -->
        <div class="mb-4">
          <svg class="mx-auto h-16 w-16 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
        </div>
        
        <!-- Text -->
        <p class="text-lg font-bold text-blue-700 mb-2">
          📷 画像をアップロード
        </p>
        <p class="text-sm text-gray-600 mb-1">
          <strong class="text-blue-600">クリック</strong>して画像を選択、または<strong class="text-blue-600">ドラッグ&ドロップ</strong>
        </p>
        <p class="text-xs text-gray-500">
          PNG, JPG, GIF対応（1枚あたり最大10MB）
        </p>
        <p class="text-xs text-blue-600 mt-2 font-semibold">
          ✨ 複数枚まとめて選択できます
        </p>
      </div>
    </div>

    <!-- Preview -->
    <div v-if="previews.length > 0" class="space-y-3">
      <div class="flex items-center justify-between">
        <p class="text-sm font-bold text-gray-700">
          📸 選択中の画像 ({{ previews.length }}枚)
        </p>
        <button
          type="button"
          @click="clearAll"
          class="text-sm text-red-600 hover:text-red-700 font-semibold"
        >
          すべてクリア
        </button>
      </div>
      
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="(preview, index) in previews" :key="index" class="relative group">
          <img :src="preview.url" :alt="preview.name" class="w-full h-32 object-cover rounded-lg border-2 border-gray-200">
          <div class="absolute inset-0 bg-black bg-opacity-60 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
            <button
              type="button"
              @click="removePreview(index)"
              class="p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transform hover:scale-110 transition-transform"
              title="削除"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <p class="text-xs text-gray-600 mt-1 truncate">{{ preview.name }}</p>
        </div>
      </div>
    </div>

    <!-- Upload Button -->
    <button
      v-if="previews.length > 0"
      type="button"
      @click="uploadImages"
      :disabled="uploading"
      class="w-full px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-lg hover:from-blue-700 hover:to-blue-800 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transition-all text-lg"
    >
      <span v-if="uploading" class="flex items-center justify-center gap-2">
        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        アップロード中...
      </span>
      <span v-else class="flex items-center justify-center gap-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        {{ previews.length }}枚の画像をアップロード
      </span>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  imageableType: {
    type: String,
    required: true, // 'plan' or 'schedule_item'
  },
  imageableId: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(['uploaded']);

const fileInput = ref(null);
const previews = ref([]);
const uploading = ref(false);
const isDragging = ref(false);

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  processFiles(files);
  event.target.value = '';
};

const handleDrop = (event) => {
  isDragging.value = false;
  const files = Array.from(event.dataTransfer.files);
  processFiles(files);
};

const processFiles = (files) => {
  files.forEach(file => {
    if (!file.type.startsWith('image/')) {
      alert(`${file.name} は画像ファイルではありません`);
      return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
      alert(`${file.name} は10MBを超えています`);
      return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
      previews.value.push({
        file,
        url: e.target.result,
        name: file.name,
      });
    };
    reader.readAsDataURL(file);
  });
};

const removePreview = (index) => {
  previews.value.splice(index, 1);
};

const clearAll = () => {
  if (confirm('選択中の画像をすべてクリアしますか？')) {
    previews.value = [];
  }
};

const uploadImages = async () => {
  uploading.value = true;
  
  try {
    // Upload each image separately
    for (const preview of previews.value) {
      const formData = new FormData();
      formData.append('image', preview.file);
      formData.append('imageable_type', props.imageableType);
      formData.append('imageable_id', props.imageableId);
      
      await axios.post('/api/images', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
    }
    
    emit('uploaded');
    previews.value = [];
  } catch (error) {
    console.error('Upload failed:', error);
    alert('画像のアップロードに失敗しました: ' + (error.response?.data?.message || error.message));
  } finally {
    uploading.value = false;
  }
};
</script>

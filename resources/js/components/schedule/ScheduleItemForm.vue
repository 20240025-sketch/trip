<template>
  <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
    <h4 class="font-bold mb-4">{{ isEdit ? 'スケジュール編集' : '新しいスケジュール' }}</h4>
    
    <form @submit.prevent="handleSubmit">
      <!-- Time Input -->
      <div class="mb-4">
        <label class="block text-sm font-bold text-gray-700 mb-2">時刻 *</label>
        <input 
          v-model="form.time" 
          type="time" 
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
      </div>

      <!-- Title -->
      <div class="mb-4">
        <label class="block text-sm font-bold text-gray-700 mb-2">タイトル *</label>
        <input 
          v-model="form.title" 
          type="text" 
          required
          placeholder="例: 鶴岡八幡宮参拝"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
      </div>

      <!-- Description -->
      <div class="mb-4">
        <label class="block text-sm font-bold text-gray-700 mb-2">詳細</label>
        <textarea 
          v-model="form.description" 
          rows="2"
          placeholder="詳しい内容や注意事項"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        ></textarea>
      </div>

      <!-- Location -->
      <div class="mb-4">
        <label class="block text-sm font-bold text-gray-700 mb-2">場所</label>
        <input 
          v-model="form.location" 
          type="text" 
          placeholder="例: 鶴岡八幡宮"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
      </div>

      <!-- Images -->
      <div class="mb-4">
        <label class="block text-sm font-bold text-gray-700 mb-2">画像</label>
        
        <!-- Image Uploader (only shown if item exists) -->
        <div v-if="isEdit && props.item?.id" class="mb-3">
          <ImageUploader
            imageable-type="schedule_item"
            :imageable-id="props.item.id"
            @uploaded="$emit('images-uploaded')"
          />
        </div>
        
        <!-- Existing Images Gallery -->
        <div v-if="props.item?.images && props.item.images.length > 0" class="mt-3">
          <ImageGallery
            :images="props.item.images"
            @delete="$emit('image-deleted', $event)"
          />
        </div>
        
        <!-- Note for new items -->
        <div v-if="!isEdit" class="text-xs text-gray-500 bg-blue-50 border border-blue-200 rounded p-2">
          💡 スケジュールを保存した後に画像を追加できます
        </div>
      </div>

      <!-- Transport Section (Toggle) -->
      <div class="mb-4">
        <button 
          type="button"
          @click="showTransport = !showTransport"
          class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1"
        >
          <span>{{ showTransport ? '▼' : '▶' }}</span>
          移動情報を追加
        </button>
        
        <div v-if="showTransport" class="mt-3 space-y-3 pl-4 border-l-2 border-blue-200">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">移動手段</label>
              <select 
                v-model="form.transport_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              >
                <option value="">選択してください</option>
                <option value="train">電車</option>
                <option value="bus">バス</option>
                <option value="car">車</option>
                <option value="walk">徒歩</option>
                <option value="taxi">タクシー</option>
                <option value="other">その他</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">所要時間(分)</label>
              <input 
                v-model.number="form.transport_duration" 
                type="number" 
                min="0"
                placeholder="30"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              >
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">出発地</label>
              <input 
                v-model="form.transport_from" 
                type="text" 
                placeholder="鎌倉駅"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              >
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">到着地</label>
              <input 
                v-model="form.transport_to" 
                type="text" 
                placeholder="長谷駅"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              >
            </div>
          </div>
          
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">料金(円)</label>
            <input 
              v-model.number="form.transport_cost" 
              type="number" 
              min="0"
              placeholder="260"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
            >
          </div>
        </div>
      </div>

      <!-- Buttons -->
      <div class="flex gap-2">
        <button 
          type="submit"
          class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold"
        >
          {{ isEdit ? '更新' : '追加' }}
        </button>
        <button 
          type="button"
          @click="$emit('cancel')"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm"
        >
          キャンセル
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import ImageUploader from '../image/ImageUploader.vue';
import ImageGallery from '../image/ImageGallery.vue';

const props = defineProps({
  item: {
    type: Object,
    default: null,
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['submit', 'cancel', 'images-uploaded', 'image-deleted']);

const showTransport = ref(false);

const form = ref({
  time: '',
  title: '',
  description: '',
  location: '',
  transport_type: '',
  transport_from: '',
  transport_to: '',
  transport_duration: null,
  transport_cost: null,
});

// Initialize form with existing data
watch(() => props.item, (newItem) => {
  if (newItem) {
    form.value = {
      time: newItem.time || '',
      title: newItem.title || '',
      description: newItem.description || '',
      location: newItem.location || '',
      transport_type: newItem.transport_type || '',
      transport_from: newItem.transport_from || '',
      transport_to: newItem.transport_to || '',
      transport_duration: newItem.transport_duration || null,
      transport_cost: newItem.transport_cost || null,
    };
    
    if (newItem.transport_type) {
      showTransport.value = true;
    }
  }
}, { immediate: true });

const handleSubmit = () => {
  const data = { ...form.value };
  
  // Clean up transport data if not needed
  if (!showTransport.value || !data.transport_type) {
    data.transport_type = null;
    data.transport_from = null;
    data.transport_to = null;
    data.transport_duration = null;
    data.transport_cost = null;
  }
  
  emit('submit', data);
  
  // Reset form if adding new item
  if (!props.isEdit) {
    form.value = {
      time: '',
      title: '',
      description: '',
      location: '',
      transport_type: '',
      transport_from: '',
      transport_to: '',
      transport_duration: null,
      transport_cost: null,
    };
    showTransport.value = false;
  }
};
</script>

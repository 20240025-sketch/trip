<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <!-- Day Header -->
    <div class="flex justify-between items-center mb-4">
      <div>
        <h3 class="text-2xl font-bold">
          Day {{ dayNumber }}: {{ formatDate(date) }}
        </h3>
        <input 
          v-model="dayTitle"
          type="text"
          placeholder="この日のタイトル (例: 鎌倉観光)"
          class="mt-2 px-3 py-1 border border-gray-300 rounded text-sm w-full max-w-md"
          @blur="updateDayTitle"
        >
      </div>
      <button 
        @click="showAddForm = !showAddForm"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold"
      >
        {{ showAddForm ? 'フォームを閉じる' : '+ スケジュール追加' }}
      </button>
    </div>

    <!-- Add Form -->
    <div v-if="showAddForm" class="mb-6">
      <ScheduleItemForm 
        @submit="handleAdd"
        @cancel="showAddForm = false"
      />
    </div>

    <!-- Edit Form -->
    <div v-if="editingItem" class="mb-6">
      <ScheduleItemForm 
        :item="editingItem"
        :is-edit="true"
        @submit="handleUpdate"
        @cancel="editingItem = null"
        @images-uploaded="handleImagesUpdated"
        @image-deleted="handleImageDeleted"
      />
    </div>

    <!-- Schedule Items List -->
    <div v-if="sortedItems.length > 0" class="space-y-3">
      <ScheduleItemCard 
        v-for="item in sortedItems"
        :key="item.id || item.tempId"
        :item="item"
        @edit="startEdit"
        @delete="handleDelete"
      />
    </div>
    
    <div v-else class="text-center py-8 text-gray-500">
      <p>まだスケジュールが登録されていません</p>
      <p class="text-sm mt-2">「+ スケジュール追加」ボタンから追加してください</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ScheduleItemForm from './ScheduleItemForm.vue';
import ScheduleItemCard from './ScheduleItemCard.vue';

const props = defineProps({
  dayId: {
    type: Number,
    default: null,
  },
  dayNumber: {
    type: Number,
    required: true,
  },
  date: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    default: '',
  },
  items: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['add-item', 'update-item', 'delete-item', 'update-title', 'refresh']);

const showAddForm = ref(false);
const editingItem = ref(null);
const dayTitle = ref(props.title);

// Sort items by time
const sortedItems = computed(() => {
  return [...props.items].sort((a, b) => {
    if (a.time < b.time) return -1;
    if (a.time > b.time) return 1;
    return 0;
  });
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ja-JP', { 
    month: 'long', 
    day: 'numeric',
    weekday: 'short'
  });
};

const handleAdd = (data) => {
  // Calculate order based on existing items
  const maxOrder = props.items.length > 0 
    ? Math.max(...props.items.map(item => item.order || 0)) 
    : 0;
  
  emit('add-item', {
    dayId: props.dayId,
    dayNumber: props.dayNumber,
    order: maxOrder + 1,
    ...data,
  });
  showAddForm.value = false;
};

const startEdit = (item) => {
  editingItem.value = item;
  showAddForm.value = false;
};

const handleUpdate = (data) => {
  emit('update-item', {
    ...editingItem.value,
    ...data,
  });
  editingItem.value = null;
};

const handleDelete = (item) => {
  emit('delete-item', item);
};

const updateDayTitle = () => {
  if (dayTitle.value !== props.title) {
    emit('update-title', {
      dayId: props.dayId,
      dayNumber: props.dayNumber,
      title: dayTitle.value,
    });
  }
};

const handleImagesUpdated = () => {
  emit('refresh');
};

const handleImageDeleted = (imageId) => {
  emit('refresh');
};
</script>

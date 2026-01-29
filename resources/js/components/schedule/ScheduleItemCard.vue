<template>
  <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
    <div class="flex gap-4">
      <!-- Time -->
      <div class="flex-shrink-0 w-20">
        <div class="text-2xl font-bold text-blue-600">{{ item.time }}</div>
      </div>

      <!-- Content -->
      <div class="flex-1">
        <h4 class="text-lg font-bold mb-1">{{ item.title }}</h4>
        
        <div v-if="item.description" class="text-gray-600 text-sm mb-2" v-html="linkifyDescription(item.description)"></div>
        
        <div v-if="item.location" class="text-sm text-gray-500 mb-2">
          <span class="inline-flex items-center gap-1">
            📍 {{ item.location }}
          </span>
        </div>

        <!-- Transport Info -->
        <div v-if="item.transport_type" class="mt-2 p-2 bg-blue-50 rounded text-sm">
          <div class="flex items-center gap-2 text-gray-700">
            <span class="font-bold">
              {{ transportLabel(item.transport_type) }}
            </span>
            <span v-if="item.transport_from && item.transport_to">
              {{ item.transport_from }} → {{ item.transport_to }}
            </span>
            <span v-if="item.transport_duration" class="text-gray-600">
              ({{ item.transport_duration }}分)
            </span>
            <span v-if="item.transport_cost" class="text-gray-600 ml-auto">
              ¥{{ item.transport_cost.toLocaleString() }}
            </span>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex-shrink-0 flex flex-col gap-2">
        <button 
          @click="$emit('edit', item)"
          class="p-2 text-blue-600 hover:bg-blue-50 rounded"
          title="編集"
        >
          ✏️
        </button>
        <button 
          @click="handleDelete"
          class="p-2 text-red-600 hover:bg-red-50 rounded"
          title="削除"
        >
          🗑️
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['edit', 'delete']);

const linkifyDescription = (text) => {
  if (!text) return '';
  
  // URLパターンにマッチする正規表現
  const urlPattern = /(https?:\/\/[^\s<]+[^<.,:;"'\]\s])/gi;
  
  // HTMLエスケープしてからリンクに変換
  const escaped = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
  
  // URLをリンクに変換
  return escaped.replace(urlPattern, (url) => {
    return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline">${url}</a>`;
  }).replace(/\n/g, '<br>');
};

const transportLabel = (type) => {
  const labels = {
    train: '🚃 電車',
    bus: '🚌 バス',
    car: '🚗 車',
    walk: '🚶 徒歩',
    taxi: '🚕 タクシー',
    other: '🚏 その他',
  };
  return labels[type] || type;
};

const handleDelete = () => {
  if (confirm('このスケジュールを削除しますか?')) {
    emit('delete', props.item);
  }
};
</script>

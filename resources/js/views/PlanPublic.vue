<template>
  <div v-if="planStore.loading" class="min-h-screen bg-gradient-to-br from-white via-cyan-50 to-blue-50 flex items-center justify-center py-12">
    <div class="text-center">
      <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-cyan-400 border-t-transparent mb-4"></div>
      <div class="text-gray-600 text-lg">読み込み中...</div>
    </div>
  </div>

  <div v-else-if="planStore.error" class="min-h-screen bg-gradient-to-br from-white via-cyan-50 to-blue-50 flex items-center justify-center px-4">
    <div class="max-w-2xl mx-auto">
      <div class="bg-white border-2 border-red-200 rounded-2xl sm:rounded-3xl p-6 sm:p-8 text-center shadow-lg">
        <div class="text-5xl sm:text-6xl mb-4">🔒</div>
        <h2 class="text-xl sm:text-2xl font-bold text-red-800 mb-2">アクセスできません</h2>
        <p class="text-red-600 mb-6">{{ planStore.error }}</p>
        <router-link 
          to="/" 
          class="inline-block px-6 py-3 bg-gradient-to-r from-cyan-400 to-blue-400 text-white rounded-full font-bold hover:scale-105 hover:shadow-lg transition-all duration-300"
        >
          ホームに戻る
        </router-link>
      </div>
    </div>
  </div>

  <div v-else-if="plan" class="min-h-screen bg-gradient-to-br from-white via-cyan-50 to-blue-50 py-6 sm:py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <!-- Header -->
      <div class="mb-6 sm:mb-8 bg-white rounded-2xl sm:rounded-3xl shadow-lg p-6 sm:p-8 border-2 border-cyan-100">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-3 sm:mb-4 bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent">
          {{ plan.title }}
        </h1>
        <p class="text-gray-600 mb-4 text-base sm:text-lg leading-relaxed">{{ plan.description }}</p>
        
        <div class="flex gap-4 text-sm sm:text-base text-gray-600">
          <span class="flex items-center gap-1">📅 {{ formatDateRange(plan.start_date, plan.end_date) }}</span>
        </div>
      </div>

      <!-- Days Timeline -->
      <div class="space-y-6 sm:space-y-8">
        <div v-for="day in plan.days" :key="day.id" class="bg-white rounded-2xl sm:rounded-3xl shadow-lg p-5 sm:p-6 border-2 border-cyan-100">
          <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-4 sm:mb-5 text-gray-800">
            <span class="text-cyan-500">Day {{ day.day_number }}:</span> {{ formatDate(day.date) }}
            <span v-if="day.title" class="text-base sm:text-lg font-normal text-gray-600 ml-2 block sm:inline mt-1 sm:mt-0">
              - {{ day.title }}
            </span>
          </h2>

          <!-- Add Schedule Button (for authenticated users) -->
          <div v-if="authStore.isAuthenticated && !isAddingSchedule[day.id]" class="mb-4">
            <button
              @click="startAddingSchedule(day.id)"
              class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm sm:text-base font-semibold"
            >
              ➕ 自分用スケジュールを追加
            </button>
          </div>

          <!-- Add Schedule Form -->
          <div v-if="isAddingSchedule[day.id]" class="mb-4 p-4 bg-green-50 border-2 border-green-200 rounded-xl">
            <h3 class="font-bold text-lg mb-3 text-green-700">スケジュールを追加</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">時間</label>
                <input
                  v-model="newSchedule[day.id].time"
                  type="time"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">タイトル *</label>
                <input
                  v-model="newSchedule[day.id].title"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  placeholder="スケジュールのタイトル"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
                <textarea
                  v-model="newSchedule[day.id].description"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  rows="3"
                  placeholder="詳細な説明"
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">場所</label>
                <input
                  v-model="newSchedule[day.id].location"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  placeholder="場所"
                >
              </div>
              <div class="flex gap-2">
                <button
                  @click="saveSchedule(day.id)"
                  :disabled="!newSchedule[day.id].title"
                  class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed font-semibold"
                >
                  💾 保存
                </button>
                <button
                  @click="cancelAddingSchedule(day.id)"
                  class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors font-semibold"
                >
                  ✕ キャンセル
                </button>
              </div>
            </div>
          </div>

          <div v-if="day.schedule_items && day.schedule_items.length > 0" class="space-y-4 sm:space-y-5">
            <div 
              v-for="item in day.schedule_items" 
              :key="item.id"
              :class="[
                'flex flex-col sm:flex-row gap-3 sm:gap-4 pl-4 sm:pl-5 py-3',
                item.is_personal ? 'border-l-4 border-green-400 bg-green-50' : 'border-l-4 border-cyan-400'
              ]"
            >
              <div class="flex-shrink-0 sm:w-20 font-bold text-base sm:text-lg" :class="item.is_personal ? 'text-green-600' : 'text-cyan-600'">
                {{ item.time }}
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <h3 class="font-bold text-lg sm:text-xl text-gray-800">{{ item.title }}</h3>
                  <span v-if="item.is_personal && authStore.isAdmin && item.user_name" class="text-xs bg-purple-500 text-white px-2 py-1 rounded-full">👤 {{ item.user_name }}</span>
                  <span v-else-if="item.is_own" class="text-xs bg-green-500 text-white px-2 py-1 rounded-full">自分用</span>
                </div>
                <div v-if="item.description" v-html="linkifyDescription(item.description)" class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base leading-relaxed"></div>
                <p v-if="item.location" class="text-gray-500 text-sm sm:text-base mt-1 sm:mt-2">
                  📍 {{ item.location }}
                </p>
                
                <!-- Transport Info -->
                <div v-if="item.transport_type" class="mt-2 sm:mt-3 text-sm sm:text-base text-gray-600 bg-blue-50 rounded-lg p-3">
                  🚃 {{ item.transport_from }} → {{ item.transport_to }}
                  <span v-if="item.transport_duration">({{ item.transport_duration }}分)</span>
                  <span v-if="item.transport_cost" class="ml-2">¥{{ item.transport_cost }}</span>
                </div>

                <!-- Images and PDFs -->
                <div v-if="item.images && item.images.length > 0" class="mt-3 sm:mt-4 flex gap-2 flex-wrap">
                  <!-- Image files -->
                  <img 
                    v-for="image in item.images.filter(img => !isPdf(img))" 
                    :key="image.id"
                    :src="getImageUrl(image)" 
                    :alt="item.title"
                    class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl border-2 border-cyan-100 cursor-pointer hover:opacity-80"
                    @click="viewImage(image)"
                  >
                  <!-- PDF files -->
                  <div
                    v-for="pdf in item.images.filter(img => isPdf(img))"
                    :key="pdf.id"
                    @click="viewImage(pdf)"
                    class="w-20 h-20 sm:w-24 sm:h-24 flex flex-col items-center justify-center rounded-xl border-2 border-red-200 bg-red-50 cursor-pointer hover:bg-red-100 transition-colors"
                  >
                    <span class="text-3xl">📄</span>
                    <span class="text-xs text-red-600 font-semibold mt-1">PDF</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm sm:text-base">スケジュールが登録されていません</p>
        </div>
      </div>

      <!-- Participants -->
      <div v-if="plan.participants && plan.participants.length > 0" class="mt-6 sm:mt-8 bg-white rounded-2xl sm:rounded-3xl shadow-lg p-5 sm:p-6 border-2 border-cyan-100">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-4 sm:mb-5 text-gray-800">👥 参加者</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
          <div 
            v-for="participant in plan.participants" 
            :key="participant.id"
            class="flex items-center gap-3 p-3 sm:p-4 bg-gradient-to-r from-cyan-50 to-blue-50 rounded-xl border-2 border-cyan-100"
          >
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-cyan-400 to-blue-400 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl">
              {{ participant.name.charAt(0) }}
            </div>
            <div>
              <div class="font-bold text-sm sm:text-base">{{ participant.name }}</div>
              <div v-if="participant.contact" class="text-xs sm:text-sm text-gray-600">
                {{ participant.contact }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Checklist -->
      <div v-if="plan.checklist_items && plan.checklist_items.length > 0" class="mt-6 sm:mt-8 bg-white rounded-2xl sm:rounded-3xl shadow-lg p-5 sm:p-6 border-2 border-cyan-100">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-4 sm:mb-5 text-gray-800">✓ チェックリスト</h2>
        <div class="space-y-4 sm:space-y-5">
          <div v-for="(items, category) in groupedChecklist" :key="category">
            <h3 class="font-bold text-base sm:text-lg mb-2 sm:mb-3 text-cyan-600">{{ category }}</h3>
            <div class="space-y-2">
              <label 
                v-for="item in items" 
                :key="item.id"
                class="flex items-center gap-2 sm:gap-3 cursor-default text-sm sm:text-base"
              >
                <input 
                  type="checkbox" 
                  :checked="item.is_checked"
                  class="w-4 h-4 sm:w-5 sm:h-5 text-cyan-600"
                  disabled
                >
                <span :class="{ 'line-through text-gray-400': item.is_checked }">
                  {{ item.item }}
                </span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Memo -->
      <div v-if="plan.memo" class="mt-6 sm:mt-8 bg-white rounded-2xl sm:rounded-3xl shadow-lg p-5 sm:p-6 border-2 border-cyan-100">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-4 sm:mb-5 text-gray-800">📝 メモ</h2>
        <p class="whitespace-pre-wrap text-gray-700 text-sm sm:text-base leading-relaxed">{{ plan.memo }}</p>
      </div>
    </div>

    <!-- Image Modal -->
    <div v-if="showImageModal" @click="closeImageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4">
      <button 
        @click="closeImageModal"
        class="fixed top-4 right-4 text-white text-4xl font-bold hover:text-gray-300 z-[60] bg-black bg-opacity-70 rounded-full w-14 h-14 flex items-center justify-center shadow-lg hover:bg-opacity-90 transition-all"
      >
        ×
      </button>
      <div class="relative max-w-7xl max-h-full">
        <img 
          v-if="modalImageUrl && !isPdfUrl(modalImageUrl)"
          :src="modalImageUrl" 
          class="max-w-full max-h-[90vh] object-contain rounded-lg"
          @click.stop
        >
        <iframe
          v-else-if="modalImageUrl && isPdfUrl(modalImageUrl)"
          :src="modalImageUrl"
          class="w-full h-[90vh] rounded-lg"
          @click.stop
        ></iframe>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { usePlanStore } from '@/stores/planStore';
import { useAuthStore } from '@/stores/authStore';
import { useUiStore } from '@/stores/uiStore';
import axios from 'axios';

const route = useRoute();
const planStore = usePlanStore();
const authStore = useAuthStore();
const uiStore = useUiStore();

const plan = computed(() => planStore.currentPlan);

const isAddingSchedule = ref({});
const newSchedule = ref({});
const showImageModal = ref(false);
const modalImageUrl = ref('');

const startAddingSchedule = (dayId) => {
  isAddingSchedule.value[dayId] = true;
  newSchedule.value[dayId] = {
    time: '',
    title: '',
    description: '',
    location: ''
  };
};

const cancelAddingSchedule = (dayId) => {
  isAddingSchedule.value[dayId] = false;
  newSchedule.value[dayId] = null;
};

const saveSchedule = async (dayId) => {
  try {
    const scheduleData = newSchedule.value[dayId];
    
    if (!scheduleData.title) {
      uiStore.showError('タイトルを入力してください');
      return;
    }

    await axios.post(`/api/days/${dayId}/schedule-items`, {
      time: scheduleData.time || null,
      title: scheduleData.title,
      description: scheduleData.description || null,
      location: scheduleData.location || null,
      order: 999 // Will be sorted by time anyway
    });

    uiStore.showSuccess('スケジュールを追加しました');
    
    // Reload plan to show new schedule
    await planStore.fetchPlanBySlug(route.params.slug);
    
    // Reset form
    cancelAddingSchedule(dayId);
  } catch (error) {
    console.error('Failed to add schedule:', error);
    uiStore.showError('スケジュールの追加に失敗しました');
  }
};

const groupedChecklist = computed(() => {
  if (!plan.value?.checklist_items) return {};
  
  return plan.value.checklist_items.reduce((acc, item) => {
    const category = item.category || 'その他';
    if (!acc[category]) acc[category] = [];
    acc[category].push(item);
    return acc;
  }, {});
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatDateRange = (startDate, endDate) => {
  const start = new Date(startDate);
  const end = new Date(endDate);
  
  if (startDate === endDate) {
    return start.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric' });
  }
  
  return `${start.toLocaleDateString('ja-JP', { month: 'long', day: 'numeric' })} - ${end.toLocaleDateString('ja-JP', { month: 'long', day: 'numeric' })}`;
};

const getImageUrl = (image) => {
  // Use thumbnail if available, otherwise use original path
  if (image.thumbnail_path) {
    return image.thumbnail_path.startsWith('/storage/') ? image.thumbnail_path : `/storage/${image.thumbnail_path}`;
  }
  if (image.path) {
    return image.path.startsWith('/storage/') ? image.path : `/storage/${image.path}`;
  }
  if (image.image_path) {
    return image.image_path.startsWith('/storage/') ? image.image_path : `/storage/${image.image_path}`;
  }
  return image.file_path ? (image.file_path.startsWith('/storage/') ? image.file_path : `/storage/${image.file_path}`) : '';
};

const isPdf = (image) => {
  return image.mime_type === 'application/pdf' || 
         (image.filename && image.filename.toLowerCase().endsWith('.pdf')) ||
         (image.original_name && image.original_name.toLowerCase().endsWith('.pdf'));
};

const linkifyDescription = (text) => {
  if (!text) return '';
  
  // URLパターンにマッチする正規表現
  const urlPattern = /(https?:\/\/[^\s<]+[^<.,:;"'\]\s])/gi;
  
  // HTMLエスケープ
  const escapeHtml = (str) => {
    return str.replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#039;');
  };
  
  // URLをリンクに変換
  const escaped = escapeHtml(text);
  const linked = escaped.replace(urlPattern, (url) => {
    return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline">${url}</a>`;
  });
  
  // 改行をbrタグに変換
  return linked.replace(/\n/g, '<br>');
};

const viewImage = (image) => {
  const url = getImageUrl(image);
  
  // PDF files open in new tab, images open in modal
  if (isPdf(image)) {
    window.open(url, '_blank');
  } else {
    modalImageUrl.value = url;
    showImageModal.value = true;
  }
};

const closeImageModal = () => {
  showImageModal.value = false;
  modalImageUrl.value = '';
};

const isPdfUrl = (url) => {
  return url && url.toLowerCase().includes('.pdf');
};

onMounted(() => {
  planStore.fetchPlanBySlug(route.params.slug);
});
</script>

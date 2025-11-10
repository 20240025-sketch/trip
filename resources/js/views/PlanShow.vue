<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 py-8">
    <div v-if="planStore.loading && !plan" class="text-center py-20">
      <div class="inline-block animate-spin rounded-full h-20 w-20 border-4 border-purple-500 border-t-transparent mb-4"></div>
      <p class="text-gray-500 text-xl">読み込み中...</p>
    </div>

    <div v-else-if="planStore.error && !plan" class="text-center py-20">
      <div class="text-6xl mb-4">😢</div>
      <div class="text-red-600 text-xl">{{ planStore.error }}</div>
    </div>

    <div v-else-if="plan" class="max-w-6xl mx-auto px-4">
      <!-- Header -->
      <div class="bg-gradient-to-br from-white via-pink-50 to-purple-50 rounded-3xl shadow-2xl p-8 mb-8 border-2 border-pink-100">
        <div class="flex justify-between items-start mb-6">
          <div class="flex-1">
            <h1 class="text-5xl font-black mb-4 bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 bg-clip-text text-transparent">
              {{ plan.title }} ✨
            </h1>
            <p class="text-gray-700 text-lg leading-relaxed">{{ plan.description }}</p>
          </div>
          <div class="flex gap-3">
            <router-link 
              v-if="canEdit"
              :to="`/plans/${plan.id}/edit`" 
              class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300"
            >
              ✏️ 編集
            </router-link>
            <button 
              @click="downloadPdf"
              class="px-6 py-3 bg-gradient-to-r from-green-500 to-teal-500 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300"
            >
              📄 PDF
            </button>
            <button 
              v-if="canEdit"
              @click="handleDelete"
              class="px-6 py-3 bg-gradient-to-r from-red-500 to-pink-500 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300"
            >
              🗑️ 削除
            </button>
          </div>
        </div>
        
        <div class="flex gap-6 items-center flex-wrap">
          <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm">
            <span class="text-2xl">📅</span>
            <span class="font-semibold text-gray-700">{{ formatDateRange(plan.start_date, plan.end_date) }}</span>
          </div>
          <div v-if="plan.is_public" class="flex items-center gap-2 px-4 py-2 bg-green-100 rounded-full shadow-sm">
            <span class="text-2xl">🌐</span>
            <span class="font-bold text-green-700">公開中</span>
          </div>
        </div>

        <div v-if="plan.is_public" class="mt-6 p-5 bg-gradient-to-r from-blue-100 to-purple-100 border-2 border-blue-200 rounded-2xl">
          <p class="text-sm font-bold text-gray-800 mb-2">🔗 公開URL</p>
          <a :href="`/p/${plan.slug}`" target="_blank" class="text-blue-600 hover:text-blue-700 underline font-medium break-all">
            {{ publicUrl }}
          </a>
        </div>
      </div>

      <!-- Days Timeline -->
      <div class="space-y-6">
        <div v-for="day in plan.days" :key="day.id" class="bg-white rounded-3xl shadow-lg p-8 border-2 border-gray-100 hover:shadow-xl transition-shadow duration-300">
          <div class="flex justify-between items-start mb-6">
            <div>
              <h2 class="text-3xl font-bold text-gray-800 mb-2">
                <span class="bg-gradient-to-r from-pink-500 to-purple-500 text-white px-4 py-1 rounded-full text-lg inline-block">
                  Day {{ day.day_number }}
                </span>
              </h2>
              <p class="text-xl text-gray-600 font-semibold">
                {{ formatDate(day.date) }}
                <span v-if="day.title" class="text-purple-600 ml-2">
                  - {{ day.title }}
                </span>
              </p>
            </div>
            <router-link 
              :to="`/plans/${plan.id}/edit`"
              class="px-5 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 hover:scale-105 transition-all duration-300 font-bold text-sm"
            >
              ✏️ 編集
            </router-link>
          </div>

          <div v-if="day.schedule_items && day.schedule_items.length > 0" class="space-y-4">
            <div 
              v-for="item in day.schedule_items" 
              :key="item.id"
              class="flex gap-4 border-l-4 border-purple-400 pl-6 py-4 hover:bg-gradient-to-r hover:from-pink-50 hover:to-purple-50 transition-all duration-300 rounded-r-2xl"
            >
              <div class="flex-shrink-0 w-24">
                <div class="text-2xl font-black text-transparent bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text">
                  {{ item.time }}
                </div>
              </div>
              <div class="flex-1">
                <h3 class="font-bold text-xl mb-2 text-gray-800">{{ item.title }}</h3>
                <p v-if="item.description" class="text-gray-600 mb-3 leading-relaxed">
                  {{ item.description }}
                </p>
                <p v-if="item.location" class="text-gray-500 mb-3 flex items-center gap-2">
                  <span class="text-lg">📍</span>
                  <span class="font-medium">{{ item.location }}</span>
                </p>
              
              <!-- Transport Info -->
              <div v-if="item.transport_type" class="mt-3 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl border-2 border-blue-100">
                <div class="flex items-center gap-2 text-gray-700">
                  <span class="font-bold">
                    {{ transportLabel(item.transport_type) }}
                  </span>
                  <span v-if="item.transport_from && item.transport_to" class="flex-1">
                    {{ item.transport_from }} → {{ item.transport_to }}
                  </span>
                  <div class="flex gap-3 text-gray-600">
                    <span v-if="item.transport_duration">
                      ⏱️ {{ item.transport_duration }}分
                    </span>
                    <span v-if="item.transport_cost" class="font-semibold">
                      ¥{{ item.transport_cost.toLocaleString() }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Images -->
              <div v-if="item.images && item.images.length > 0" class="mt-3 flex gap-2">
                <img 
                  v-for="image in item.images" 
                  :key="image.id"
                  :src="image.thumbnail_path || image.image_path" 
                  :alt="item.title"
                  class="w-24 h-24 object-cover rounded-lg cursor-pointer hover:opacity-80 shadow"
                >
              </div>
            </div>
            </div>
          </div>
          <p v-else class="text-gray-500 text-center py-6">
            スケジュールが登録されていません。
            <router-link 
              :to="`/plans/${plan.id}/edit`"
              class="text-blue-600 hover:underline ml-1"
            >
              編集画面
            </router-link>
            から追加できます。
          </p>
        </div>
      </div>

    <!-- Participants -->
    <div v-if="plan.participants && plan.participants.length > 0" class="mt-8 bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-4">参加者</h2>
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="participant in plan.participants" 
          :key="participant.id"
          class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
        >
          <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
            {{ participant.name.charAt(0) }}
          </div>
          <div>
            <div class="font-bold">{{ participant.name }}</div>
            <div v-if="participant.contact" class="text-sm text-gray-600">
              {{ participant.contact }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Checklist -->
    <div v-if="plan.checklist_items && plan.checklist_items.length > 0" class="mt-8 bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-4">チェックリスト</h2>
      <div class="space-y-4">
        <div v-for="(items, category) in groupedChecklist" :key="category">
          <h3 class="font-bold text-lg mb-2">{{ category }}</h3>
          <div class="space-y-2">
            <label 
              v-for="item in items" 
              :key="item.id"
              class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors"
            >
              <input 
                type="checkbox" 
                :checked="item.is_checked"
                @change="toggleChecklistItem(item.id)"
                class="w-5 h-5 text-blue-600 cursor-pointer"
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
    <div v-if="plan.memo" class="mt-8 bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-4">メモ</h2>
      <p class="whitespace-pre-wrap text-gray-700">{{ plan.memo }}</p>
    </div>

    <!-- Images -->
    <div v-if="plan.images && plan.images.length > 0" class="mt-8 bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-4">画像 ({{ plan.images.length }}枚)</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="image in plan.images" :key="image.id" class="relative group">
          <img 
            :src="`/storage/${image.thumbnail_path || image.image_path}`"
            :alt="image.original_name || 'Image'"
            class="w-full h-32 object-cover rounded-lg cursor-pointer shadow hover:shadow-lg transition-shadow"
            @click="viewImage(image)"
          >
        </div>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePlanStore } from '@/stores/planStore';
import { useAuthStore } from '@/stores/authStore';
import { useUiStore } from '@/stores/uiStore';

const route = useRoute();
const router = useRouter();
const planStore = usePlanStore();
const authStore = useAuthStore();
const uiStore = useUiStore();

const plan = computed(() => planStore.currentPlan);

const canEdit = computed(() => {
  if (!authStore.isAuthenticated || !plan.value) return false;
  // Admin can edit any plan, user can only edit their own
  return authStore.isAdmin || plan.value.user_id === authStore.user?.id;
});

const publicUrl = computed(() => {
  if (!plan.value?.slug) return '';
  return `${window.location.origin}/p/${plan.value.slug}`;
});

const groupedChecklist = computed(() => {
  if (!plan.value?.checklist_items) return {};
  
  return plan.value.checklist_items.reduce((acc, item) => {
    const category = item.category || 'その他';
    if (!acc[category]) acc[category] = [];
    acc[category].push(item);
    return acc;
  }, {});
});

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

const downloadPdf = async () => {
  try {
    // Use axios to download PDF with proper base URL
    const response = await axios.get(`/api/plans/${plan.value.id}/pdf`, {
      responseType: 'blob',
    });
    
    // Create a blob URL and trigger download
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${plan.value.title}_${new Date().toISOString().split('T')[0]}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    
    uiStore.showSuccess('PDFのダウンロードを開始しました');
  } catch (error) {
    console.error('PDF download error:', error);
    uiStore.showError('PDFのダウンロードに失敗しました');
  }
};

const viewImage = (image) => {
  const url = image.image_path ? `/storage/${image.image_path}` : `/storage/${image.file_path}`;
  window.open(url, '_blank');
};

const handleDelete = async () => {
  if (!confirm('本当に削除しますか?')) return;
  
  try {
    await planStore.deletePlan(plan.value.id);
    uiStore.showSuccess('プランを削除しました');
    router.push('/plans');
  } catch (error) {
    uiStore.showError('プランの削除に失敗しました');
  }
};

const toggleChecklistItem = async (itemId) => {
  try {
    await axios.put(`/api/checklist-items/${itemId}/toggle`);
    // Reload the plan to get updated checklist state
    await planStore.fetchPlan(plan.value.id);
    uiStore.showSuccess('チェック状態を更新しました');
  } catch (error) {
    console.error('Toggle checklist error:', error);
    uiStore.showError('チェック状態の更新に失敗しました');
  }
};

onMounted(async () => {
  const planId = route.params.id;
  
  // If plan is already loaded and matches the route, skip fetching
  if (plan.value && plan.value.id == planId) {
    console.log('Plan already loaded, skipping fetch');
    return;
  }
  
  // Otherwise fetch the plan
  await planStore.fetchPlan(planId);
});
</script>

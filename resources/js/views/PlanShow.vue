<template>
  <div class="min-h-screen py-8">
    <div v-if="planStore.loading && !plan" class="text-center py-20">
      <div class="inline-block animate-spin rounded-full h-20 w-20 border-4 border-cyan-400 border-t-transparent mb-4"></div>
      <p class="text-gray-500 text-xl">読み込み中...</p>
    </div>

    <div v-else-if="planStore.error && !plan" class="text-center py-20">
      <div class="text-6xl mb-4">😢</div>
      <div class="text-red-600 text-xl">{{ planStore.error }}</div>
    </div>

    <div v-else-if="plan" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-gradient-to-br from-white via-cyan-50 to-blue-50 rounded-2xl sm:rounded-3xl shadow-2xl p-4 sm:p-6 lg:p-8 mb-6 sm:mb-8 border-2 border-cyan-200">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
          <div class="flex-1">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black mb-3 sm:mb-4 bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent break-words">
              {{ plan.title }} ✨
            </h1>
            <p class="text-gray-700 text-base sm:text-lg leading-relaxed">{{ plan.description }}</p>
          </div>
          <div class="flex gap-2 sm:gap-3 flex-wrap justify-start sm:justify-end">
            <button 
              @click="downloadPdf"
              class="px-4 py-2 sm:px-6 sm:py-3 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300 whitespace-nowrap text-sm sm:text-base"
            >
              📄 PDF
            </button>
            <router-link 
              v-if="canEdit"
              :to="`/plans/${plan.id}/participants`" 
              class="px-4 py-2 sm:px-6 sm:py-3 bg-gradient-to-r from-purple-400 to-pink-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300 whitespace-nowrap text-sm sm:text-base"
            >
              👥 参加者管理
            </router-link>
            <router-link 
              v-if="canEdit"
              :to="`/plans/${plan.id}/edit`" 
              class="px-4 py-2 sm:px-6 sm:py-3 bg-gradient-to-r from-blue-400 to-cyan-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300 whitespace-nowrap text-sm sm:text-base"
            >
              ✏️ 編集
            </router-link>
            <button 
              v-if="canEdit"
              @click="handleDelete"
              class="px-4 py-2 sm:px-6 sm:py-3 bg-gradient-to-r from-red-400 to-pink-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300 whitespace-nowrap text-sm sm:text-base"
            >
              🗑️ 削除
            </button>
          </div>
        </div>
        
        <div class="flex gap-3 sm:gap-6 items-center flex-wrap">
          <div class="flex items-center gap-2 px-3 py-2 sm:px-4 sm:py-2 bg-white rounded-full shadow-sm">
            <span class="text-xl sm:text-2xl">📅</span>
            <span class="font-semibold text-gray-700 text-sm sm:text-base">{{ formatDateRange(plan.start_date, plan.end_date) }}</span>
          </div>
          <div v-if="plan.is_public" class="flex items-center gap-2 px-3 py-2 sm:px-4 sm:py-2 bg-green-100 rounded-full shadow-sm">
            <span class="text-xl sm:text-2xl">🌐</span>
            <span class="font-bold text-green-700 text-sm sm:text-base">公開中</span>
          </div>
        </div>

        <div v-if="plan.is_public" class="mt-4 sm:mt-6 p-4 sm:p-5 bg-gradient-to-r from-blue-100 to-purple-100 border-2 border-blue-200 rounded-xl sm:rounded-2xl">
          <p class="text-xs sm:text-sm font-bold text-gray-800 mb-2">🔗 公開URL</p>
          <a :href="`/p/${plan.slug}`" target="_blank" class="text-blue-600 hover:text-blue-700 underline font-medium break-all text-xs sm:text-base">
            {{ publicUrl }}
          </a>
        </div>
      </div>

      <!-- Days Timeline -->
      <div class="space-y-6 sm:space-y-8">
        <div v-for="day in plan.days" :key="day.id" class="bg-white rounded-2xl sm:rounded-3xl shadow-lg p-5 sm:p-6 border-2 border-blue-100">
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-4 sm:mb-5">
            <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">
              <span class="text-blue-500">Day {{ day.day_number }}:</span> {{ formatDate(day.date) }}
              <span v-if="day.title" class="text-base sm:text-lg font-normal text-gray-600 ml-2 block sm:inline mt-1 sm:mt-0">
                - {{ day.title }}
              </span>
            </h2>
            <router-link 
              :to="`/plans/${plan.id}/edit`"
              class="px-4 py-2 sm:px-5 bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 hover:scale-105 transition-all duration-300 font-bold text-xs sm:text-sm text-center"
            >
              ✏️ 編集
            </router-link>
          </div>

          <div v-if="day.schedule_items && day.schedule_items.length > 0" class="space-y-4 sm:space-y-5">
            <div 
              v-for="item in day.schedule_items" 
              :key="item.id"
              class="flex flex-col sm:flex-row gap-3 sm:gap-4 border-l-4 border-blue-400 pl-4 sm:pl-5 py-3"
            >
              <div class="flex-shrink-0 sm:w-20 font-bold text-blue-600 text-base sm:text-lg">
                {{ item.time }}
              </div>
              <div class="flex-1">
                <h3 class="font-bold text-lg sm:text-xl text-gray-800">{{ item.title }}</h3>
                <div v-if="item.description" class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base leading-relaxed" v-html="linkifyDescription(item.description)"></div>
                <p v-if="item.location" class="text-gray-500 text-sm sm:text-base mt-1 sm:mt-2">
                  📍 {{ item.location }}
                </p>
              
              <!-- Transport Info -->
              <div v-if="item.transport_type" class="mt-2 sm:mt-3 text-sm sm:text-base text-gray-600 bg-blue-50 rounded-lg p-3">
                {{ transportLabel(item.transport_type) }} {{ item.transport_from }} → {{ item.transport_to }}
                <span v-if="item.transport_duration" class="ml-2">⏱️ {{ item.transport_duration }}分</span>
                <span v-if="item.transport_cost" class="ml-2 font-semibold">¥{{ item.transport_cost.toLocaleString() }}</span>
              </div>

              <!-- Images and PDFs -->
              <div v-if="item.images && item.images.length > 0" class="mt-3 sm:mt-4 flex gap-2 flex-wrap">
                <!-- Image files -->
                <img 
                  v-for="image in item.images.filter(img => !isPdf(img))" 
                  :key="image.id"
                  :src="getImageUrl(image)" 
                  :alt="item.title"
                  class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl border-2 border-blue-100 cursor-pointer hover:opacity-80"
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
          <p v-else class="text-gray-500 text-sm sm:text-base">
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
    <div v-if="plan.participants && plan.participants.length > 0" class="mt-6 sm:mt-8 bg-white rounded-xl sm:rounded-lg shadow-md p-4 sm:p-6">
      <h2 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4">参加者</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <div 
          v-for="participant in plan.participants" 
          :key="participant.id"
          class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
        >
          <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-sm sm:text-base">
            {{ participant.name.charAt(0) }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="font-bold text-sm sm:text-base truncate">{{ participant.name }}</div>
            <div v-if="participant.contact" class="text-xs sm:text-sm text-gray-600 truncate">
              {{ participant.contact }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Checklist -->
    <div class="mt-6 sm:mt-8 bg-white rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 lg:p-8 border-2 border-cyan-100">
      <div class="flex items-center gap-2 mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent">
          <span>✅</span>
          <span>持ち物リスト</span>
        </h2>
      </div>
      
      <div v-if="belongings.length > 0" class="space-y-3 sm:space-y-4">
        <div v-for="(items, category) in groupedChecklist" :key="category" class="bg-gradient-to-br from-cyan-50 to-blue-50 rounded-xl p-4 border border-cyan-200">
          <h3 class="font-bold text-base sm:text-lg mb-3 text-cyan-700">{{ category }}</h3>
          <div class="space-y-2">
            <label 
              v-for="item in items" 
              :key="item.id"
              class="flex items-center gap-3 cursor-pointer hover:bg-white hover:shadow-sm p-3 rounded-lg transition-all text-sm sm:text-base"
            >
              <input 
                type="checkbox" 
                :checked="item.is_checked"
                @change="toggleBelonging(item)"
                class="w-5 h-5 text-cyan-600 cursor-pointer flex-shrink-0 rounded focus:ring-cyan-500"
              >
              <span :class="{ 'line-through text-gray-400': item.is_checked, 'text-gray-700': !item.is_checked }" class="break-words font-medium">
                {{ item.name }}
              </span>
            </label>
          </div>
        </div>
      </div>
      
      <div v-else class="text-center py-12 bg-gradient-to-br from-cyan-50 to-blue-50 rounded-xl border-2 border-dashed border-cyan-200">
        <div class="text-5xl sm:text-6xl mb-4">📋</div>
        <p class="text-gray-600 font-medium text-base sm:text-lg mb-2">持ち物リストがまだ追加されていません</p>
        <p class="text-gray-500 text-sm sm:text-base">編集画面から持ち物を追加できます</p>
      </div>
    </div>

    <!-- Memo -->
    <div v-if="plan.memo" class="mt-6 sm:mt-8 bg-white rounded-xl sm:rounded-lg shadow-md p-4 sm:p-6">
      <h2 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4">メモ</h2>
      <p class="whitespace-pre-wrap text-gray-700 text-sm sm:text-base leading-relaxed">{{ plan.memo }}</p>
    </div>

    <!-- Images (Hidden) -->
    <!-- <div class="mt-6 sm:mt-8 bg-white rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 lg:p-8 border-2 border-purple-100" style="display: none;">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl sm:text-2xl font-bold flex items-center gap-2">
          <span>📸</span>
          <span>画像</span>
          <span v-if="plan.images && plan.images.length > 0" class="text-base text-gray-500">({{ plan.images.length }}枚)</span>
        </h2>
      </div>

      <div v-if="plan.images && plan.images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
        <div v-for="image in plan.images" :key="image.id" class="relative group">
          <img 
            :src="getImageUrl(image)"
            :alt="image.original_name || 'Image'"
            class="w-full h-28 sm:h-36 object-cover rounded-xl cursor-pointer shadow-md hover:shadow-xl transition-all hover:scale-105"
            @click="viewImage(image)"
          >
          <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity rounded-xl"></div>
        </div>
      </div>
      
      <div v-else class="text-center py-12 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border-2 border-dashed border-purple-200">
        <div class="text-5xl sm:text-6xl mb-4">📷</div>
        <p class="text-gray-600 font-medium text-base sm:text-lg mb-2">画像がまだ追加されていません</p>
        <p class="text-gray-500 text-sm sm:text-base">編集画面から画像をアップロードできます</p>
      </div>
    </div> -->

    <!-- Attachments -->
    <div class="mt-6 sm:mt-8 bg-white rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 lg:p-8 border-2 border-cyan-100">
      <AttachmentManager
        :plan-id="plan.id"
        :can-edit="canEdit"
      />
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
import AttachmentManager from '@/components/AttachmentManager.vue';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const planStore = usePlanStore();
const authStore = useAuthStore();
const uiStore = useUiStore();

const plan = computed(() => planStore.currentPlan);
const belongings = ref([]);

const canEdit = computed(() => {
  if (!plan.value) return false;
  
  // Debug: Log permission info
  console.log('Plan permissions:', {
    can_edit: plan.value.can_edit,
    can_delete: plan.value.can_delete,
    user_id: plan.value.user_id,
    current_user: authStore.user,
    is_authenticated: authStore.isAuthenticated,
    user_is_admin: authStore.user?.is_admin
  });
  
  // If user is admin, always allow edit
  if (authStore.user?.is_admin === true) {
    return true;
  }
  
  // Otherwise use the can_edit flag from the API response
  return plan.value.can_edit === true;
});

const publicUrl = computed(() => {
  if (!plan.value?.slug) return '';
  return `${window.location.origin}/p/${plan.value.slug}`;
});

const groupedChecklist = computed(() => {
  console.log('Belongings:', belongings.value);
  
  const carry = belongings.value.filter(b => b.type === 'carry');
  const send = belongings.value.filter(b => b.type === 'send');
  
  const result = {};
  if (carry.length > 0) result['所持するもの'] = carry;
  if (send.length > 0) result['送るもの'] = send;
  
  console.log('Grouped checklist:', result);
  return result;
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

const getImageUrl = (image) => {
  console.log('getImageUrl called with:', image);
  console.log('image.path:', image.path);
  console.log('image.thumbnail_path:', image.thumbnail_path);
  console.log('image.image_path:', image.image_path);
  console.log('image.file_path:', image.file_path);
  
  // Use thumbnail if available, otherwise use original path
  if (image.thumbnail_path) {
    const url = image.thumbnail_path.startsWith('/storage/') ? image.thumbnail_path : `/storage/${image.thumbnail_path}`;
    console.log('Using thumbnail_path:', url);
    return url;
  }
  if (image.path) {
    const url = image.path.startsWith('/storage/') ? image.path : `/storage/${image.path}`;
    console.log('Using path:', url);
    return url;
  }
  if (image.image_path) {
    const url = image.image_path.startsWith('/storage/') ? image.image_path : `/storage/${image.image_path}`;
    console.log('Using image_path:', url);
    return url;
  }
  const url = image.file_path ? (image.file_path.startsWith('/storage/') ? image.file_path : `/storage/${image.file_path}`) : '';
  console.log('Using file_path or empty:', url);
  return url;
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

const viewImage = (image) => {
  // Use original image path for viewing
  let url = '';
  if (image.path) {
    url = image.path.startsWith('/storage/') ? image.path : `/storage/${image.path}`;
  } else if (image.image_path) {
    url = image.image_path.startsWith('/storage/') ? image.image_path : `/storage/${image.image_path}`;
  } else if (image.file_path) {
    url = image.file_path.startsWith('/storage/') ? image.file_path : `/storage/${image.file_path}`;
  }
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

const toggleBelonging = async (item) => {
  try {
    await axios.put(`/api/plans/${plan.value.id}/belongings/${item.id}`, {
      is_checked: !item.is_checked
    });
    // Update local state
    item.is_checked = !item.is_checked;
    uiStore.showSuccess('チェック状態を更新しました');
  } catch (error) {
    console.error('Toggle belonging error:', error);
    uiStore.showError('チェック状態の更新に失敗しました');
  }
};

const fetchBelongings = async () => {
  if (!plan.value?.id) return;
  
  try {
    const response = await axios.get(`/api/plans/${plan.value.id}/belongings`);
    belongings.value = response.data.data || [];
    console.log('Belongings fetched:', belongings.value);
  } catch (error) {
    console.error('Failed to fetch belongings:', error);
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
  
  console.log('PlanShow mounted - route.params:', route.params);
  console.log('PlanShow mounted - planId:', planId);
  
  if (!planId) {
    console.error('No plan ID in route params');
    uiStore.showError('プランIDが見つかりません');
    router.push({ name: 'plans' });
    return;
  }
  
  // If plan is already loaded and matches the route, skip fetching
  if (plan.value && plan.value.id == planId) {
    console.log('Plan already loaded, skipping fetch');
    await fetchBelongings();
    return;
  }
  
  // Otherwise fetch the plan
  await planStore.fetchPlan(planId);
  await fetchBelongings();
});
</script>

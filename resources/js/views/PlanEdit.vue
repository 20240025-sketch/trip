<template>
  <div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold mb-8">プランを編集</h1>
    
    <div v-if="planStore.loading && !plan" class="text-center py-12">
      <div class="text-gray-500">読み込み中...</div>
    </div>

    <div v-else-if="plan">
      <!-- Basic Info Form -->
      <div class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h2 class="text-xl font-bold mb-6">基本情報</h2>
        
        <form @submit.prevent="handleSubmit">
          <!-- Title -->
          <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">タイトル *</label>
            <input 
              v-model="form.title" 
              type="text" 
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>

          <!-- Description -->
          <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">説明</label>
            <textarea 
              v-model="form.description" 
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <!-- Dates -->
          <div class="grid md:grid-cols-2 gap-6 mb-6">
            <div>
              <label class="block text-gray-700 font-bold mb-2">開始日 *</label>
              <input 
                v-model="form.start_date" 
                type="date" 
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-gray-700 font-bold mb-2">終了日 *</label>
              <input 
                v-model="form.end_date" 
                type="date" 
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <!-- Memo -->
          <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">メモ</label>
            <textarea 
              v-model="form.memo" 
              rows="4"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <!-- Public -->
          <div class="mb-8">
            <label class="flex items-center">
              <input 
                v-model="form.is_public" 
                type="checkbox"
                class="w-5 h-5 text-blue-600"
              >
              <span class="ml-2 text-gray-700">このプランを公開する</span>
            </label>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-600">{{ error }}</p>
          </div>

          <!-- Buttons -->
          <div class="flex gap-4">
            <button 
              type="submit" 
              :disabled="planStore.loading"
              class="px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {{ planStore.loading ? '更新中...' : '基本情報を更新' }}
            </button>
            <router-link 
              :to="`/plans/${plan.id}`" 
              class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300"
            >
              キャンセル
            </router-link>
          </div>
        </form>
      </div>

      <!-- Schedule Management -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6">日程別スケジュール</h2>
        
        <div v-if="plan.days && plan.days.length > 0" class="space-y-6">
          <DaySchedule 
            v-for="day in plan.days"
            :key="day.id"
            :day-id="day.id"
            :day-number="day.day_number"
            :date="day.date"
            :title="day.title"
            :items="day.schedule_items || []"
            @add-item="handleAddScheduleItem"
            @update-item="handleUpdateScheduleItem"
            @delete-item="handleDeleteScheduleItem"
            @update-title="handleUpdateDayTitle"
            @refresh="refreshPlan"
          />
        </div>
        
        <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
          <p class="text-yellow-800">日程がまだ作成されていません。基本情報を保存すると、日程が自動作成されます。</p>
        </div>
      </div>

      <!-- Image Management -->
            <!-- Image Management -->
      <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg shadow-lg p-8 border-2 border-blue-200">
        <div class="flex items-center gap-3 mb-6">
          <div class="p-3 bg-blue-600 rounded-full">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-800">画像管理</h2>
            <p class="text-sm text-gray-600">旅の思い出の写真や参考画像を追加できます</p>
          </div>
        </div>
        
        <!-- Upload Section -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <span class="text-2xl">⬆️</span>
            画像を追加
          </h3>
          <ImageUploader
            imageable-type="plan"
            :imageable-id="plan.id"
            @uploaded="handleImagesUploaded"
          />
        </div>
        
        <!-- Gallery Section -->
        <div v-if="plan.images && plan.images.length > 0">
          <h3 class="text-lg font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <span class="text-2xl">🖼️</span>
            アップロード済み画像 ({{ plan.images.length }}枚)
          </h3>
          <ImageGallery
            :images="plan.images"
            @delete="handleDeleteImage"
          />
        </div>
        
        <div v-else class="text-center py-8 bg-white rounded-lg border-2 border-dashed border-gray-300">
          <p class="text-gray-500">まだ画像が追加されていません</p>
          <p class="text-sm text-gray-400 mt-1">上のエリアから画像をアップロードしてください</p>
        </div>
      </div>

      <!-- Attachments Section -->
      <div class="bg-white rounded-2xl shadow-xl p-8 border-2 border-cyan-100">
        <AttachmentManager
          :plan-id="plan.id"
          :can-edit="true"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePlanStore } from '@/stores/planStore';
import { useScheduleStore } from '@/stores/scheduleStore';
import { useUiStore } from '@/stores/uiStore';
import DaySchedule from '@/components/schedule/DaySchedule.vue';
import ImageUploader from '@/components/image/ImageUploader.vue';
import ImageGallery from '@/components/image/ImageGallery.vue';
import AttachmentManager from '@/components/AttachmentManager.vue';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const planStore = usePlanStore();
const scheduleStore = useScheduleStore();
const uiStore = useUiStore();

const plan = computed(() => planStore.currentPlan);

const form = ref({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  memo: '',
  is_public: false,
});

const error = ref('');

watch(plan, (newPlan) => {
  if (newPlan) {
    form.value = {
      title: newPlan.title,
      description: newPlan.description || '',
      start_date: newPlan.start_date,
      end_date: newPlan.end_date,
      memo: newPlan.memo || '',
      is_public: newPlan.is_public,
    };
  }
});

const handleSubmit = async () => {
  error.value = '';
  
  try {
    await planStore.updatePlan(plan.value.id, form.value);
    uiStore.showSuccess('基本情報を更新しました');
    // Reload to get updated days
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    error.value = planStore.error || 'プランの更新に失敗しました';
  }
};

const handleAddScheduleItem = async (data) => {
  console.log('Adding schedule item:', data); // デバッグログ
  
  try {
    const result = await scheduleStore.createScheduleItem(data.dayId, data);
    console.log('Schedule item created:', result); // デバッグログ
    uiStore.showSuccess('スケジュールを追加しました');
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    console.error('Failed to add schedule item:', err); // デバッグログ
    uiStore.showError(scheduleStore.error || 'スケジュールの追加に失敗しました');
  }
};

const handleUpdateScheduleItem = async (item) => {
  console.log('Updating schedule item:', item); // デバッグログ
  
  try {
    await scheduleStore.updateScheduleItem(item.day_id, item.id, item);
    uiStore.showSuccess('スケジュールを更新しました');
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    console.error('Failed to update schedule item:', err); // デバッグログ
    uiStore.showError(scheduleStore.error || 'スケジュールの更新に失敗しました');
  }
};

const handleDeleteScheduleItem = async (item) => {
  console.log('Deleting schedule item:', item); // デバッグログ
  
  try {
    await scheduleStore.deleteScheduleItem(item.day_id, item.id);
    uiStore.showSuccess('スケジュールを削除しました');
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    console.error('Failed to delete schedule item:', err); // デバッグログ
    uiStore.showError(scheduleStore.error || 'スケジュールの削除に失敗しました');
  }
};

const handleUpdateDayTitle = async (data) => {
  console.log('Updating day title:', data); // デバッグログ
  
  try {
    await scheduleStore.updateDay(plan.value.id, data.dayId, { title: data.title });
    uiStore.showSuccess('日程のタイトルを更新しました');
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    console.error('Failed to update day title:', err); // デバッグログ
    uiStore.showError(scheduleStore.error || '日程の更新に失敗しました');
  }
};

const handleImagesUploaded = async () => {
  uiStore.showSuccess('画像をアップロードしました');
  await planStore.fetchPlan(plan.value.id);
};

const handleDeleteImage = async (imageId) => {
  try {
    await axios.delete(`/api/images/${imageId}`);
    uiStore.showSuccess('画像を削除しました');
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    console.error('Failed to delete image:', err);
    uiStore.showError('画像の削除に失敗しました');
  }
};

const refreshPlan = async () => {
  await planStore.fetchPlan(plan.value.id);
};

onMounted(() => {
  planStore.fetchPlan(route.params.id);
});
</script>

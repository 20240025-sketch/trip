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

          <div v-if="day.schedule_items && day.schedule_items.length > 0" class="space-y-4 sm:space-y-5">
            <div 
              v-for="item in day.schedule_items" 
              :key="item.id"
              class="flex flex-col sm:flex-row gap-3 sm:gap-4 border-l-4 border-cyan-400 pl-4 sm:pl-5 py-3"
            >
              <div class="flex-shrink-0 sm:w-20 font-bold text-cyan-600 text-base sm:text-lg">
                {{ item.time }}
              </div>
              <div class="flex-1">
                <h3 class="font-bold text-lg sm:text-xl text-gray-800">{{ item.title }}</h3>
                <p v-if="item.description" class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base leading-relaxed">
                  {{ item.description }}
                </p>
                <p v-if="item.location" class="text-gray-500 text-sm sm:text-base mt-1 sm:mt-2">
                  📍 {{ item.location }}
                </p>
                
                <!-- Transport Info -->
                <div v-if="item.transport_type" class="mt-2 sm:mt-3 text-sm sm:text-base text-gray-600 bg-blue-50 rounded-lg p-3">
                  🚃 {{ item.transport_from }} → {{ item.transport_to }}
                  <span v-if="item.transport_duration">({{ item.transport_duration }}分)</span>
                  <span v-if="item.transport_cost" class="ml-2">¥{{ item.transport_cost }}</span>
                </div>

                <!-- Images -->
                <div v-if="item.images && item.images.length > 0" class="mt-3 sm:mt-4 flex gap-2 flex-wrap">
                  <img 
                    v-for="image in item.images" 
                    :key="image.id"
                    :src="image.thumbnail_path || image.image_path" 
                    :alt="item.title"
                    class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl border-2 border-cyan-100"
                  >
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { usePlanStore } from '@/stores/planStore';

const route = useRoute();
const planStore = usePlanStore();

const plan = computed(() => planStore.currentPlan);

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

onMounted(() => {
  planStore.fetchPlanBySlug(route.params.slug);
});
</script>

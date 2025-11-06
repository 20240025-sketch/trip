<template>
  <div v-if="planStore.loading" class="text-center py-12">
    <div class="text-gray-500">読み込み中...</div>
  </div>

  <div v-else-if="planStore.error" class="text-center py-12">
    <div class="text-red-600">{{ planStore.error }}</div>
  </div>

  <div v-else-if="plan" class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold mb-2">{{ plan.title }}</h1>
      <p class="text-gray-600 mb-4">{{ plan.description }}</p>
      
      <div class="flex gap-4 text-sm text-gray-600">
        <span>📅 {{ formatDateRange(plan.start_date, plan.end_date) }}</span>
      </div>
    </div>

    <!-- Days Timeline -->
    <div class="space-y-8">
      <div v-for="day in plan.days" :key="day.id" class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">
          Day {{ day.day_number }}: {{ formatDate(day.date) }}
          <span v-if="day.title" class="text-lg font-normal text-gray-600 ml-2">
            - {{ day.title }}
          </span>
        </h2>

        <div v-if="day.schedule_items && day.schedule_items.length > 0" class="space-y-4">
          <div 
            v-for="item in day.schedule_items" 
            :key="item.id"
            class="flex gap-4 border-l-4 border-blue-500 pl-4 py-2"
          >
            <div class="flex-shrink-0 w-16 font-bold text-blue-600">
              {{ item.time }}
            </div>
            <div class="flex-1">
              <h3 class="font-bold text-lg">{{ item.title }}</h3>
              <p v-if="item.description" class="text-gray-600 mt-1">
                {{ item.description }}
              </p>
              <p v-if="item.location" class="text-gray-500 text-sm mt-1">
                📍 {{ item.location }}
              </p>
              
              <!-- Transport Info -->
              <div v-if="item.transport_type" class="mt-2 text-sm text-gray-600">
                🚃 {{ item.transport_from }} → {{ item.transport_to }}
                <span v-if="item.transport_duration">({{ item.transport_duration }}分)</span>
                <span v-if="item.transport_cost" class="ml-2">¥{{ item.transport_cost }}</span>
              </div>

              <!-- Images -->
              <div v-if="item.images && item.images.length > 0" class="mt-3 flex gap-2">
                <img 
                  v-for="image in item.images" 
                  :key="image.id"
                  :src="image.thumbnail_path || image.image_path" 
                  :alt="item.title"
                  class="w-24 h-24 object-cover rounded-lg"
                >
              </div>
            </div>
          </div>
        </div>
        <p v-else class="text-gray-500">スケジュールが登録されていません</p>
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
              class="flex items-center gap-2 cursor-pointer"
            >
              <input 
                type="checkbox" 
                :checked="item.is_checked"
                class="w-5 h-5 text-blue-600"
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
    <div v-if="plan.memo" class="mt-8 bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-4">メモ</h2>
      <p class="whitespace-pre-wrap text-gray-700">{{ plan.memo }}</p>
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

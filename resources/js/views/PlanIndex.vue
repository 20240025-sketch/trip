<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-4">プラン一覧</h1>
      
      <!-- Search -->
      <div class="flex gap-4 mb-6">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="プランを検索..." 
          class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          @keyup.enter="handleSearch"
        >
        <button 
          @click="handleSearch"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          検索
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="planStore.loading" class="text-center py-12">
      <div class="text-gray-500">読み込み中...</div>
    </div>

    <!-- Error -->
    <div v-else-if="planStore.error" class="text-center py-12">
      <div class="text-red-600">{{ planStore.error }}</div>
    </div>

    <!-- Plans Grid -->
    <div v-else>
      <div v-if="planStore.plans.length === 0" class="text-center py-12">
        <p class="text-gray-500 mb-4">プランがありません</p>
        <router-link 
          to="/plans/create" 
          class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          新規作成
        </router-link>
      </div>

      <div v-else>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          <div 
            v-for="plan in planStore.plans" 
            :key="plan.id"
            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
          >
            <div 
              class="h-48 bg-gradient-to-r from-blue-400 to-blue-600"
              :style="plan.cover_image ? `background-image: url(${plan.cover_image}); background-size: cover; background-position: center;` : ''"
            ></div>
            <div class="p-6">
              <h3 class="text-xl font-bold mb-2">{{ plan.title }}</h3>
              <p class="text-gray-600 mb-4 line-clamp-2">{{ plan.description }}</p>
              <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                <span>{{ formatDateRange(plan.start_date, plan.end_date) }}</span>
                <span v-if="plan.is_public" class="text-green-600">公開中</span>
              </div>
              <div class="flex gap-2">
                <router-link 
                  :to="`/plans/${plan.id}`" 
                  class="flex-1 text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                  詳細
                </router-link>
                <router-link 
                  :to="`/plans/${plan.id}/edit`" 
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                >
                  編集
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="planStore.pagination.lastPage > 1" class="flex justify-center gap-2">
          <button 
            v-for="page in planStore.pagination.lastPage" 
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-4 py-2 rounded-lg',
              page === planStore.pagination.currentPage 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePlanStore } from '@/stores/planStore';

const planStore = usePlanStore();
const searchQuery = ref('');

const formatDateRange = (startDate, endDate) => {
  const start = new Date(startDate);
  const end = new Date(endDate);
  
  if (startDate === endDate) {
    return start.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric' });
  }
  
  return `${start.toLocaleDateString('ja-JP', { month: 'long', day: 'numeric' })} - ${end.toLocaleDateString('ja-JP', { month: 'long', day: 'numeric' })}`;
};

const handleSearch = () => {
  planStore.fetchPlans(1, searchQuery.value);
};

const changePage = (page) => {
  planStore.fetchPlans(page, searchQuery.value);
};

onMounted(() => {
  planStore.fetchPlans();
});
</script>

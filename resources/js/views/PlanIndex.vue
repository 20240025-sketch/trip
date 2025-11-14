<template>
  <div class="min-h-screen bg-gradient-to-br from-white via-cyan-50 to-blue-50 py-6 sm:py-8">
    <div class="container mx-auto px-4 sm:px-6">
      <div class="mb-8 sm:mb-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0 mb-6">
          <div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent mb-2">
              プラン一覧 📋
            </h1>
            <p class="text-gray-600 text-sm sm:text-base">あなたの素敵な旅行プランを管理しましょう✨</p>
          </div>
          <router-link 
            to="/plans/create" 
            class="px-6 py-3 sm:px-8 sm:py-4 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-xl transition-all duration-300 flex items-center gap-2 justify-center whitespace-nowrap"
          >
            <span class="text-xl sm:text-2xl">✨</span>
            <span class="text-sm sm:text-base">新しい旅を計画</span>
          </router-link>
        </div>
        
        <!-- Search -->
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
          <div class="flex-1 relative">
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="🔍 プランを検索..." 
              class="w-full px-4 sm:px-6 py-3 sm:py-4 border-2 border-cyan-200 rounded-full focus:outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-base sm:text-lg shadow-sm"
              @keyup.enter="handleSearch"
            >
          </div>
          <button 
            @click="handleSearch"
            class="px-6 py-3 sm:px-8 sm:py-4 bg-gradient-to-r from-blue-400 to-cyan-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300 text-sm sm:text-base"
          >
            検索
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="planStore.loading" class="text-center py-16 sm:py-20">
        <div class="inline-block animate-spin rounded-full h-16 w-16 sm:h-20 sm:w-20 border-4 border-cyan-400 border-t-transparent mb-4"></div>
        <p class="text-gray-500 text-lg sm:text-xl">読み込み中...</p>
      </div>

      <!-- Error -->
      <div v-else-if="planStore.error" class="text-center py-16 sm:py-20">
        <div class="text-5xl sm:text-6xl mb-4">😢</div>
        <div class="text-red-600 text-lg sm:text-xl">{{ planStore.error }}</div>
      </div>

      <!-- Plans Grid -->
      <div v-else>
        <div v-if="planStore.plans.length === 0" class="text-center py-16 sm:py-20 bg-white rounded-2xl sm:rounded-3xl shadow-lg border-2 border-cyan-100">
          <div class="text-6xl sm:text-8xl mb-6">📝</div>
          <p class="text-gray-500 text-xl sm:text-2xl mb-6 sm:mb-8">まだプランがありません</p>
          <router-link 
            to="/plans/create" 
            class="inline-block px-8 py-4 sm:px-10 sm:py-5 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-xl transition-all duration-300 text-base sm:text-xl"
          >
            最初のプランを作成する
          </router-link>
        </div>

        <div v-else>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 mb-8 sm:mb-12">
            <div 
              v-for="plan in planStore.plans" 
              :key="plan.id"
              class="group bg-white rounded-2xl sm:rounded-3xl shadow-lg overflow-hidden hover:shadow-2xl hover:scale-105 transition-all duration-300 border-2 border-cyan-100"
            >
              <!-- Image -->
              <div 
                class="h-40 sm:h-48 lg:h-56 bg-gradient-to-r from-cyan-200 via-blue-200 to-sky-200 relative overflow-hidden"
                :style="plan.cover_image ? `background-image: url(${plan.cover_image}); background-size: cover; background-position: center;` : ''"
              >
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent group-hover:from-black/50 transition-all duration-300"></div>
                <div v-if="plan.is_public" class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-green-500 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-full text-xs sm:text-sm font-bold flex items-center gap-1 shadow-lg">
                  <span>✓</span>
                  <span>公開中</span>
                </div>
                <div v-else class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-gray-500 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-full text-xs sm:text-sm font-bold flex items-center gap-1 shadow-lg">
                  <span>🔒</span>
                  <span>非公開</span>
                </div>
              </div>
              
              <!-- Content -->
              <div class="p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl lg:text-2xl font-bold mb-2 sm:mb-3 text-gray-800 group-hover:text-cyan-600 transition-colors line-clamp-1">
                  {{ plan.title }}
                </h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-5 line-clamp-2 leading-relaxed">
                  {{ plan.description || '楽しい旅行の計画です✨' }}
                </p>
                <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 mb-4 sm:mb-6">
                  <span class="text-lg sm:text-xl">📅</span>
                  <span>{{ formatDateRange(plan.start_date, plan.end_date) }}</span>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-2 sm:gap-3">
                  <router-link 
                    :to="`/plans/${plan.id}`"
                    @click="handlePlanClick(plan)"
                    class="flex-1 text-center px-3 py-2 sm:px-4 sm:py-3 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300 text-sm sm:text-base"
                  >
                    詳細を見る
                  </router-link>
                  <router-link 
                    v-if="plan.can_edit"
                    :to="`/plans/${plan.id}/edit`"
                    @click="handlePlanClick(plan)"
                    class="px-3 py-2 sm:px-4 sm:py-3 bg-cyan-50 text-cyan-700 font-bold rounded-full hover:bg-cyan-100 hover:scale-105 transition-all duration-300"
                  >
                    ✏️
                  </router-link>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="planStore.pagination.lastPage > 1" class="flex justify-center gap-2 sm:gap-3 flex-wrap">
            <button 
              v-for="page in planStore.pagination.lastPage" 
              :key="page"
              @click="changePage(page)"
              :class="[
                'px-4 py-2 sm:px-6 sm:py-3 rounded-full font-bold transition-all duration-300 text-sm sm:text-base',
                page === planStore.pagination.currentPage 
                  ? 'bg-gradient-to-r from-cyan-400 to-blue-400 text-white shadow-lg scale-110' 
                  : 'bg-white text-gray-700 hover:bg-cyan-50 hover:scale-105 shadow border-2 border-cyan-100'
              ]"
            >
              {{ page }}
            </button>
          </div>
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

const handlePlanClick = (plan) => {
  // Pre-load the plan data for instant display
  planStore.setCurrentPlan(plan);
};

onMounted(() => {
  planStore.fetchPlans();
});
</script>

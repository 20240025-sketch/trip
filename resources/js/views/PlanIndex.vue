<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 py-8">
    <div class="container mx-auto px-4">
      <div class="mb-10">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-5xl font-black bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent mb-2">
              プラン一覧 📋
            </h1>
            <p class="text-gray-600">あなたの素敵な旅行プランを管理しましょう✨</p>
          </div>
          <router-link 
            to="/plans/create" 
            class="px-8 py-4 bg-gradient-to-r from-pink-500 to-purple-500 text-white font-bold rounded-full hover:scale-105 hover:shadow-xl transition-all duration-300 flex items-center gap-2"
          >
            <span class="text-2xl">✨</span>
            <span>新しい旅を計画</span>
          </router-link>
        </div>
        
        <!-- Search -->
        <div class="flex gap-4">
          <div class="flex-1 relative">
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="🔍 プランを検索..." 
              class="w-full px-6 py-4 border-2 border-purple-200 rounded-full focus:outline-none focus:ring-4 focus:ring-purple-200 focus:border-purple-400 transition-all text-lg shadow-sm"
              @keyup.enter="handleSearch"
            >
          </div>
          <button 
            @click="handleSearch"
            class="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300"
          >
            検索
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="planStore.loading" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-20 w-20 border-4 border-purple-500 border-t-transparent mb-4"></div>
        <p class="text-gray-500 text-xl">読み込み中...</p>
      </div>

      <!-- Error -->
      <div v-else-if="planStore.error" class="text-center py-20">
        <div class="text-6xl mb-4">😢</div>
        <div class="text-red-600 text-xl">{{ planStore.error }}</div>
      </div>

      <!-- Plans Grid -->
      <div v-else>
        <div v-if="planStore.plans.length === 0" class="text-center py-20 bg-white rounded-3xl shadow-lg">
          <div class="text-8xl mb-6">📝</div>
          <p class="text-gray-500 text-2xl mb-8">まだプランがありません</p>
          <router-link 
            to="/plans/create" 
            class="inline-block px-10 py-5 bg-gradient-to-r from-pink-500 to-purple-500 text-white font-bold rounded-full hover:scale-105 hover:shadow-xl transition-all duration-300 text-xl"
          >
            最初のプランを作成する
          </router-link>
        </div>

        <div v-else>
          <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <div 
              v-for="plan in planStore.plans" 
              :key="plan.id"
              class="group bg-white rounded-3xl shadow-lg overflow-hidden hover:shadow-2xl hover:scale-105 transition-all duration-300 border-2 border-gray-100"
            >
              <!-- Image -->
              <div 
                class="h-56 bg-gradient-to-r from-pink-300 via-purple-300 to-blue-300 relative overflow-hidden"
                :style="plan.cover_image ? `background-image: url(${plan.cover_image}); background-size: cover; background-position: center;` : ''"
              >
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent group-hover:from-black/50 transition-all duration-300"></div>
                <div v-if="plan.is_public" class="absolute top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-full text-sm font-bold flex items-center gap-1 shadow-lg">
                  <span>✓</span>
                  <span>公開中</span>
                </div>
                <div v-else class="absolute top-4 right-4 bg-gray-500 text-white px-4 py-2 rounded-full text-sm font-bold flex items-center gap-1 shadow-lg">
                  <span>🔒</span>
                  <span>非公開</span>
                </div>
              </div>
              
              <!-- Content -->
              <div class="p-6">
                <h3 class="text-2xl font-bold mb-3 text-gray-800 group-hover:text-purple-600 transition-colors line-clamp-1">
                  {{ plan.title }}
                </h3>
                <p class="text-gray-600 mb-5 line-clamp-2 leading-relaxed">
                  {{ plan.description || '楽しい旅行の計画です✨' }}
                </p>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                  <span class="text-xl">📅</span>
                  <span>{{ formatDateRange(plan.start_date, plan.end_date) }}</span>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-3">
                  <router-link 
                    :to="`/plans/${plan.id}`"
                    @click="handlePlanClick(plan)"
                    class="flex-1 text-center px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300"
                  >
                    詳細を見る
                  </router-link>
                  <router-link 
                    :to="`/plans/${plan.id}/edit`"
                    @click="handlePlanClick(plan)"
                    class="px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-full hover:bg-gray-200 hover:scale-105 transition-all duration-300"
                  >
                    ✏️
                  </router-link>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="planStore.pagination.lastPage > 1" class="flex justify-center gap-3">
            <button 
              v-for="page in planStore.pagination.lastPage" 
              :key="page"
              @click="changePage(page)"
              :class="[
                'px-6 py-3 rounded-full font-bold transition-all duration-300',
                page === planStore.pagination.currentPage 
                  ? 'bg-gradient-to-r from-pink-500 to-purple-500 text-white shadow-lg scale-110' 
                  : 'bg-white text-gray-700 hover:bg-gray-100 hover:scale-105 shadow'
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

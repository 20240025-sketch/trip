<template>
  <div class="min-h-screen flex flex-col">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-500 to-blue-700 text-white py-20">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">旅の計画をもっと楽しく</h1>
        <p class="text-xl mb-8">
          画像付きで旅行計画を作成し、PDF出力で共有できます
        </p>
        <router-link 
          to="/plans/create" 
          class="inline-block px-8 py-4 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition-colors text-lg"
        >
          今すぐ作成する
        </router-link>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-16">
      <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">主な機能</h2>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="text-5xl mb-4">📅</div>
            <h3 class="text-xl font-bold mb-2">日程管理</h3>
            <p class="text-gray-600">
              旅行の日程ごとにスケジュールを管理できます
            </p>
          </div>
          <div class="text-center">
            <div class="text-5xl mb-4">📸</div>
            <h3 class="text-xl font-bold mb-2">画像アップロード</h3>
            <p class="text-gray-600">
              思い出の写真や場所の画像を追加できます
            </p>
          </div>
          <div class="text-center">
            <div class="text-5xl mb-4">📄</div>
            <h3 class="text-xl font-bold mb-2">PDF出力</h3>
            <p class="text-gray-600">
              計画をPDFで出力して印刷・共有できます
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent Plans Section -->
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
          <h2 class="text-3xl font-bold">最近の公開プラン</h2>
          <router-link 
            to="/plans" 
            class="text-blue-600 hover:text-blue-700"
          >
            すべて見る →
          </router-link>
        </div>
        
        <div v-if="loading" class="text-center py-8">
          <div class="text-gray-500">読み込み中...</div>
        </div>
        
        <div v-else class="grid md:grid-cols-3 gap-6">
          <div 
            v-for="plan in recentPlans" 
            :key="plan.id"
            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
          >
            <div 
              class="h-48 bg-gradient-to-r from-blue-400 to-blue-600"
              :style="plan.cover_image ? `background-image: url(${plan.cover_image})` : ''"
            ></div>
            <div class="p-6">
              <h3 class="text-xl font-bold mb-2">{{ plan.title }}</h3>
              <p class="text-gray-600 mb-4 line-clamp-2">{{ plan.description }}</p>
              <div class="flex justify-between items-center text-sm text-gray-500">
                <span>{{ formatDate(plan.start_date) }}</span>
                <router-link 
                  :to="`/p/${plan.slug}`" 
                  class="text-blue-600 hover:text-blue-700"
                >
                  詳細 →
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePlanStore } from '@/stores/planStore';

const planStore = usePlanStore();
const recentPlans = ref([]);
const loading = ref(true);

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric' });
};

onMounted(async () => {
  try {
    await planStore.fetchPlans(1);
    recentPlans.value = planStore.plans.slice(0, 3);
  } catch (error) {
    console.error('Failed to load plans', error);
  } finally {
    loading.value = false;
  }
});
</script>

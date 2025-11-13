<template>
  <div class="min-h-screen flex flex-col">
    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20 bg-gradient-to-br from-cyan-50 via-blue-50 to-white">
      <!-- Decorative Elements -->
      <div class="absolute top-0 left-0 w-64 h-64 bg-cyan-200 rounded-full mix-blend-multiply filter blur-xl opacity-50 animate-blob"></div>
      <div class="absolute top-0 right-0 w-64 h-64 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-50 animate-blob animation-delay-2000"></div>
      <div class="absolute bottom-0 left-1/2 w-64 h-64 bg-cyan-300 rounded-full mix-blend-multiply filter blur-xl opacity-40 animate-blob animation-delay-4000"></div>
      
      <div class="container mx-auto px-4 text-center relative z-10">
        <div class="mb-6">
          <span class="text-7xl inline-block animate-bounce">✈️</span>
        </div>
        <h1 class="text-6xl font-black mb-6 bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent">
          旅の計画をもっと楽しく 🌈
        </h1>
        <p class="text-2xl mb-10 text-gray-700 font-medium">
          ✨ 画像付きで旅行計画を作成し、PDF出力で共有できます 💫
        </p>
        <router-link 
          to="/plans/create" 
          class="inline-block px-10 py-5 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-2xl transition-all duration-300 text-xl shadow-lg"
        >
          🎨 今すぐ作成する
        </router-link>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
      <div class="container mx-auto px-4">
        <h2 class="text-5xl font-black text-center mb-4 bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent">
          できること 💡
        </h2>
        <p class="text-center text-gray-600 mb-16 text-lg">
          旅行計画がもっと楽しくなる機能がいっぱい！
        </p>
        <div class="grid md:grid-cols-3 gap-10">
          <!-- Feature 1 -->
          <div class="bg-gradient-to-br from-white to-cyan-50 rounded-3xl p-8 shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 border-2 border-cyan-200">
            <div class="bg-gradient-to-br from-cyan-400 to-blue-400 w-20 h-20 rounded-full flex items-center justify-center text-4xl mb-6 mx-auto shadow-lg">
              📅
            </div>
            <h3 class="text-2xl font-bold mb-4 text-center text-gray-800">日程管理</h3>
            <p class="text-gray-600 text-center leading-relaxed">
              旅行の日程ごとにスケジュールを細かく管理できます。時間や場所、移動情報も記録可能！
            </p>
          </div>
          
          <!-- Feature 2 -->
          <div class="bg-gradient-to-br from-white to-blue-50 rounded-3xl p-8 shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 border-2 border-blue-200">
            <div class="bg-gradient-to-br from-blue-400 to-cyan-400 w-20 h-20 rounded-full flex items-center justify-center text-4xl mb-6 mx-auto shadow-lg">
              📸
            </div>
            <h3 class="text-2xl font-bold mb-4 text-center text-gray-800">画像アップロード</h3>
            <p class="text-gray-600 text-center leading-relaxed">
              思い出の写真や訪れる場所の画像を追加できます。視覚的に楽しい計画表が作れます！
            </p>
          </div>
          
          <!-- Feature 3 -->
          <div class="bg-gradient-to-br from-white to-cyan-50 rounded-3xl p-8 shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 border-2 border-cyan-200">
            <div class="bg-gradient-to-br from-cyan-400 to-blue-500 w-20 h-20 rounded-full flex items-center justify-center text-4xl mb-6 mx-auto shadow-lg">
              📄
            </div>
            <h3 class="text-2xl font-bold mb-4 text-center text-gray-800">PDF出力</h3>
            <p class="text-gray-600 text-center leading-relaxed">
              作成した計画をPDFで出力して印刷・共有できます。オフラインでも確認できて便利！
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent Plans Section -->
    <section class="py-20 bg-gradient-to-br from-cyan-50 to-white">
      <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-12">
          <div>
            <h2 class="text-4xl font-black bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent">
              みんなの旅行プラン ✨
            </h2>
            <p class="text-gray-600 mt-2">公開されているプランをチェック！</p>
          </div>
          <router-link 
            to="/plans" 
            class="px-6 py-3 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300"
          >
            すべて見る →
          </router-link>
        </div>
        
        <div v-if="loading" class="text-center py-16">
          <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-cyan-400 border-t-transparent"></div>
          <p class="text-gray-500 mt-4 text-lg">読み込み中...</p>
        </div>
        
        <div v-else class="grid md:grid-cols-3 gap-8">
          <div 
            v-for="plan in recentPlans" 
            :key="plan.id"
            class="group bg-white rounded-3xl shadow-xl overflow-hidden hover:shadow-2xl hover:scale-105 transition-all duration-300 border-2 border-cyan-100"
          >
            <div 
              class="h-56 bg-gradient-to-r from-cyan-300 via-blue-300 to-cyan-200 relative overflow-hidden"
              :style="plan.cover_image ? `background-image: url(${plan.cover_image}); background-size: cover; background-position: center;` : ''"
            >
              <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent group-hover:from-black/50 transition-all duration-300"></div>
              <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-bold text-cyan-600 shadow-lg">
                公開中
              </div>
            </div>
            <div class="p-6">
              <h3 class="text-2xl font-bold mb-3 text-gray-800 group-hover:text-cyan-600 transition-colors">{{ plan.title }}</h3>
              <p class="text-gray-600 mb-5 line-clamp-2 leading-relaxed">{{ plan.description || '楽しい旅行の計画です✨' }}</p>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500 flex items-center gap-2">
                  <span>📅</span>
                  <span>{{ formatDate(plan.start_date) }}</span>
                </span>
                <router-link 
                  :to="`/p/${plan.slug}`" 
                  class="px-5 py-2 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:scale-110 hover:shadow-lg transition-all duration-300 text-sm"
                >
                  詳細を見る →
                </router-link>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="!loading && recentPlans.length === 0" class="text-center py-16">
          <p class="text-gray-500 text-xl">まだ公開プランがありません 📝</p>
          <p class="text-gray-400 mt-2">最初のプランを作成してみましょう！</p>
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

<style scoped>
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}
</style>

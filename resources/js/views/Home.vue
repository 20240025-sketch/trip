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

    <!-- Bus & Room Assignments Section -->
    <section class="py-20 bg-white">
      <div class="container mx-auto px-4">
        <h2 class="text-4xl font-black text-center mb-4 bg-gradient-to-r from-blue-500 to-cyan-500 bg-clip-text text-transparent">
          🚌🏠 バス・部屋割
        </h2>
        <p class="text-center text-gray-600 mb-12 text-lg">
          計画されたバス座席と部屋割の情報
        </p>

        <!-- データがない場合のメッセージ -->
        <div v-if="!hasAssignments" class="text-center py-12">
          <p class="text-gray-500 text-xl">まだバス・部屋割が登録されていません 🚌🏠</p>
          <p class="text-gray-400 mt-2">旅行プランでバス座席と部屋割を設定しましょう！</p>
        </div>

        <!-- Room Assignments Slider -->
        <div v-if="roomAssignments.length > 0" class="mb-12">
          <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
            <span>🏠</span>
            <span>部屋割</span>
          </h3>
          <div class="relative">
            <!-- Slider Container -->
            <div class="overflow-hidden">
              <div 
                ref="roomSlider"
                class="flex transition-transform duration-500 ease-out"
                :style="{ transform: `translateX(-${roomCurrentIndex * 100}%)` }"
              >
                <div 
                  v-for="(room, index) in roomAssignments" 
                  :key="index"
                  class="w-full flex-shrink-0 px-2"
                >
                  <div class="max-w-md mx-auto bg-white rounded-2xl p-6 shadow-lg border-2 border-blue-100">
                    <div class="bg-gradient-to-br from-blue-400 to-cyan-400 text-white rounded-xl p-4 mb-4">
                      <div class="text-sm font-medium mb-1">{{ room.day_number }}日目</div>
                      <div class="text-2xl font-bold">{{ room.floor }} / {{ room.room_number }}</div>
                    </div>
                    <div v-if="room.notes" class="mt-3 p-3 bg-gray-50 rounded-lg">
                      <div class="text-xs text-gray-500 mb-1">備考</div>
                      <div class="text-sm text-gray-700">{{ room.notes }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Navigation Buttons -->
            <button 
              v-if="roomAssignments.length > 1"
              @click="prevRoom"
              class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 bg-white/90 backdrop-blur-sm text-blue-600 w-12 h-12 rounded-full shadow-lg hover:bg-blue-50 hover:scale-110 transition-all duration-300 flex items-center justify-center"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <button 
              v-if="roomAssignments.length > 1"
              @click="nextRoom"
              class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 bg-white/90 backdrop-blur-sm text-blue-600 w-12 h-12 rounded-full shadow-lg hover:bg-blue-50 hover:scale-110 transition-all duration-300 flex items-center justify-center"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
            
            <!-- Indicators -->
            <div v-if="roomAssignments.length > 1" class="flex justify-center gap-2 mt-6">
              <button
                v-for="(room, index) in roomAssignments"
                :key="index"
                @click="goToRoom(index)"
                class="transition-all duration-300"
                :class="index === roomCurrentIndex ? 'w-8 h-2 bg-blue-500 rounded-full' : 'w-2 h-2 bg-gray-300 rounded-full hover:bg-blue-300'"
              ></button>
            </div>
          </div>
        </div>

        <!-- Bus Assignments Slider -->
        <div v-if="busAssignments.length > 0">
          <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
            <span>🚌</span>
            <span>バス座席</span>
          </h3>
          <div class="relative">
            <!-- Slider Container -->
            <div class="overflow-hidden">
              <div 
                ref="busSlider"
                class="flex transition-transform duration-500 ease-out"
                :style="{ transform: `translateX(-${busCurrentIndex * 100}%)` }"
              >
                <div 
                  v-for="(bus, index) in busAssignments" 
                  :key="index"
                  class="w-full flex-shrink-0 px-2"
                >
                  <div class="max-w-md mx-auto bg-white rounded-2xl p-6 shadow-lg border-2 border-green-100">
                    <div class="bg-gradient-to-br from-green-400 to-emerald-400 text-white rounded-xl p-4 mb-4">
                      <div class="text-sm font-medium mb-1">{{ bus.day_number }}日目</div>
                      <div class="text-2xl font-bold">{{ bus.bus_number }}号車 / {{ bus.row_number }}列目</div>
                      <div class="text-sm mt-2">{{ bus.side === 'left' ? '左側' : '右側' }}</div>
                    </div>
                    <div v-if="bus.notes" class="mt-3 p-3 bg-gray-50 rounded-lg">
                      <div class="text-xs text-gray-500 mb-1">備考</div>
                      <div class="text-sm text-gray-700">{{ bus.notes }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Navigation Buttons -->
            <button 
              v-if="busAssignments.length > 1"
              @click="prevBus"
              class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 bg-white/90 backdrop-blur-sm text-green-600 w-12 h-12 rounded-full shadow-lg hover:bg-green-50 hover:scale-110 transition-all duration-300 flex items-center justify-center"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <button 
              v-if="busAssignments.length > 1"
              @click="nextBus"
              class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 bg-white/90 backdrop-blur-sm text-green-600 w-12 h-12 rounded-full shadow-lg hover:bg-green-50 hover:scale-110 transition-all duration-300 flex items-center justify-center"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
            
            <!-- Indicators -->
            <div v-if="busAssignments.length > 1" class="flex justify-center gap-2 mt-6">
              <button
                v-for="(bus, index) in busAssignments"
                :key="index"
                @click="goToBus(index)"
                class="transition-all duration-300"
                :class="index === busCurrentIndex ? 'w-8 h-2 bg-green-500 rounded-full' : 'w-2 h-2 bg-gray-300 rounded-full hover:bg-green-300'"
              ></button>
            </div>
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
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { usePlanStore } from '@/stores/planStore';
import axios from 'axios';

const planStore = usePlanStore();
const recentPlans = ref([]);
const loading = ref(true);
const roomAssignments = ref([]);
const busAssignments = ref([]);
const hasAssignments = ref(false);

// Carousel state
const roomCurrentIndex = ref(0);
const busCurrentIndex = ref(0);
const roomSlider = ref(null);
const busSlider = ref(null);
let roomInterval = null;
let busInterval = null;

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric' });
};

// Room carousel functions
const nextRoom = () => {
  if (roomAssignments.value.length > 0) {
    roomCurrentIndex.value = (roomCurrentIndex.value + 1) % roomAssignments.value.length;
  }
};

const prevRoom = () => {
  if (roomAssignments.value.length > 0) {
    roomCurrentIndex.value = roomCurrentIndex.value === 0 
      ? roomAssignments.value.length - 1 
      : roomCurrentIndex.value - 1;
  }
};

const goToRoom = (index) => {
  roomCurrentIndex.value = index;
  resetRoomAutoPlay();
};

const startRoomAutoPlay = () => {
  if (roomAssignments.value.length > 1) {
    roomInterval = setInterval(() => {
      nextRoom();
    }, 5000); // 5秒ごとに自動切り替え
  }
};

const resetRoomAutoPlay = () => {
  if (roomInterval) {
    clearInterval(roomInterval);
  }
  startRoomAutoPlay();
};

// Bus carousel functions
const nextBus = () => {
  if (busAssignments.value.length > 0) {
    busCurrentIndex.value = (busCurrentIndex.value + 1) % busAssignments.value.length;
  }
};

const prevBus = () => {
  if (busAssignments.value.length > 0) {
    busCurrentIndex.value = busCurrentIndex.value === 0 
      ? busAssignments.value.length - 1 
      : busCurrentIndex.value - 1;
  }
};

const goToBus = (index) => {
  busCurrentIndex.value = index;
  resetBusAutoPlay();
};

const startBusAutoPlay = () => {
  if (busAssignments.value.length > 1) {
    busInterval = setInterval(() => {
      nextBus();
    }, 5000); // 5秒ごとに自動切り替え
  }
};

const resetBusAutoPlay = () => {
  if (busInterval) {
    clearInterval(busInterval);
  }
  startBusAutoPlay();
};

const fetchAssignments = async () => {
  try {
    console.log('Fetching assignments for home page...');
    
    // Fetch all plans
    const plansResponse = await axios.get('/api/plans');
    const allPlans = plansResponse.data.data || plansResponse.data;
    console.log('Total plans:', allPlans.length);

    const allRoomAssignments = [];
    const allBusAssignments = [];

    // Fetch each plan with its days
    for (const plan of allPlans) {
      try {
        const planResponse = await axios.get(`/api/plans/${plan.id}`);
        const planData = planResponse.data.data || planResponse.data;
        console.log(`📦 Plan ${plan.id}:`, planData.title, '- Days:', planData.days?.length || 0);
        
        if (!planData.days || planData.days.length === 0) {
          console.log(`⚠️ Plan ${plan.id} has no days, skipping`);
          continue;
        }

        for (const day of planData.days) {
          // Fetch room assignments
          try {
            const roomResponse = await axios.get(`/api/days/${day.id}/room-assignments`);
            console.log(`Day ${day.id} room assignments:`, roomResponse.data);
            const rooms = roomResponse.data
              .filter(room => room.floor || room.room_number) // 空でないデータのみ
              .map(room => ({
                day_number: day.day_number,
                floor: room.floor || '',
                room_number: room.room_number || '',
                notes: room.notes || ''
              }));
            allRoomAssignments.push(...rooms);
          } catch (error) {
            console.error(`Failed to fetch room assignments for day ${day.id}:`, error);
          }

          // Fetch bus assignments
          try {
            const busResponse = await axios.get(`/api/days/${day.id}/bus-assignments`);
            console.log(`Day ${day.id} bus assignments:`, busResponse.data);
            const buses = busResponse.data
              .filter(bus => bus.bus_number || bus.row_number) // 空でないデータのみ
              .map(bus => ({
                day_number: day.day_number,
                bus_number: bus.bus_number || '',
                row_number: bus.row_number || '',
                side: bus.side || '',
                notes: bus.notes || ''
              }));
            allBusAssignments.push(...buses);
          } catch (error) {
            console.error(`Failed to fetch bus assignments for day ${day.id}:`, error);
          }
        }
      } catch (error) {
        console.error(`Failed to fetch plan ${plan.id}:`, error);
      }
    }

    console.log('Total room assignments:', allRoomAssignments.length);
    console.log('Total bus assignments:', allBusAssignments.length);

    roomAssignments.value = allRoomAssignments;
    busAssignments.value = allBusAssignments;
    hasAssignments.value = allRoomAssignments.length > 0 || allBusAssignments.length > 0;

    // Start auto-play after data is loaded
    if (allRoomAssignments.length > 0) {
      startRoomAutoPlay();
    }
    if (allBusAssignments.length > 0) {
      startBusAutoPlay();
    }
  } catch (error) {
    console.error('Failed to load assignments', error);
  }
};

onMounted(async () => {
  try {
    await planStore.fetchPlans(1);
    recentPlans.value = planStore.plans.slice(0, 3);
    await fetchAssignments();
  } catch (error) {
    console.error('Failed to load plans', error);
  } finally {
    loading.value = false;
  }
});

onBeforeUnmount(() => {
  if (roomInterval) {
    clearInterval(roomInterval);
  }
  if (busInterval) {
    clearInterval(busInterval);
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

/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>

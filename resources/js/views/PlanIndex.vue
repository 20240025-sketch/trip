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

        <!-- Folder Management -->
        <div class="mb-4 flex gap-3">
          <button 
            @click="showFolderModal = true"
            class="px-4 py-2 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition-colors flex items-center gap-2"
          >
            <span>📁</span>
            <span>フォルダを作成</span>
          </button>
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
        <!-- Folders Section -->
        <div v-if="folders.length > 0" class="mb-8">
          <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span>📁</span>
            <span>フォルダ</span>
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
            <div 
              v-for="folder in folders" 
              :key="folder.id"
              @click="toggleFolder(folder.id)"
              class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-6 border-2 border-amber-200 hover:border-amber-400 cursor-pointer hover:shadow-lg transition-all group"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                  <span class="text-4xl">{{ folder.expanded ? '📂' : '📁' }}</span>
                  <div>
                    <h3 class="font-bold text-gray-800 group-hover:text-amber-600 text-lg">
                      {{ folder.name }}
                      <span v-if="folder.is_private" class="ml-2 text-sm bg-gray-600 text-white px-2 py-0.5 rounded-full">🔒 作成者のみ</span>
                    </h3>
                    <p class="text-sm text-gray-600">{{ folder.plan_count }}件のプラン</p>
                  </div>
                </div>
                <div class="flex gap-2">
                  <button 
                    v-if="folder.is_owner"
                    @click.stop="toggleFolderPrivacy(folder)"
                    class="text-gray-400 hover:text-blue-600 transition-colors"
                    :title="folder.is_private ? '公開フォルダにする' : 'プライベートフォルダにする'"
                  >
                    {{ folder.is_private ? '🔒' : '🌐' }}
                  </button>
                  <button 
                    v-if="folder.is_owner"
                    @click.stop="deleteFolder(folder.id)"
                    class="text-gray-400 hover:text-red-600 transition-colors"
                    title="フォルダを削除"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Expanded Folder Plans -->
          <div v-for="folder in folders.filter(f => f.expanded)" :key="'expanded-' + folder.id" class="mb-8">
            <h3 class="text-xl font-bold text-gray-700 mb-4 flex items-center gap-2">
              <span>📂</span>
              <span>{{ folder.name }} のプラン</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
              <div 
                v-for="plan in getPlansInFolder(folder.id)" 
                :key="plan.id"
                class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl hover:scale-105 transition-all duration-300 border-2 border-amber-200"
              >
                <!-- Plan Card Content (same as before) -->
                <div 
                  class="h-40 sm:h-48 bg-gradient-to-r from-amber-200 via-orange-200 to-yellow-200 relative overflow-hidden"
                  :style="getPlanCoverImage(plan) ? `background-image: url(${getPlanCoverImage(plan)}); background-size: cover; background-position: center;` : ''"
                >
                  <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent group-hover:from-black/50 transition-all duration-300"></div>
                  <button 
                    @click.stop="removePlanFromFolder(plan.id, folder.id)"
                    class="absolute top-3 left-3 bg-red-500 text-white px-3 py-1.5 rounded-full text-xs font-bold hover:bg-red-600 transition-colors"
                  >
                    フォルダから削除
                  </button>
                  <div v-if="plan.is_public" class="absolute top-3 right-3 bg-green-500 text-white px-3 py-1.5 rounded-full text-xs font-bold flex items-center gap-1 shadow-lg">
                    <span>✓</span>
                    <span>公開中</span>
                  </div>
                  <div v-else class="absolute top-3 right-3 bg-gray-500 text-white px-3 py-1.5 rounded-full text-xs font-bold flex items-center gap-1 shadow-lg">
                    <span>🔒</span>
                    <span>非公開</span>
                  </div>
                </div>
                
                <div class="p-4 sm:p-6">
                  <h3 class="text-lg sm:text-xl font-bold mb-2 text-gray-800 group-hover:text-amber-600 transition-colors line-clamp-1">
                    {{ plan.title }}
                  </h3>
                  <p class="text-sm text-gray-600 mb-4 line-clamp-2 leading-relaxed">
                    {{ plan.description || '楽しい旅行の計画です✨' }}
                  </p>
                  <div class="flex items-center gap-2 text-xs text-gray-500 mb-4">
                    <span class="text-lg">📅</span>
                    <span>{{ formatDateRange(plan.start_date, plan.end_date) }}</span>
                  </div>
                  
                  <div class="flex gap-2">
                    <router-link 
                      :to="`/plans/${plan.id}`"
                      @click="handlePlanClick(plan)"
                      class="flex-1 text-center px-3 py-2 bg-gradient-to-r from-amber-400 to-orange-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-lg transition-all duration-300 text-sm"
                    >
                      詳細を見る
                    </router-link>
                    <router-link 
                      v-if="plan.can_edit"
                      :to="`/plans/${plan.id}/edit`"
                      @click="handlePlanClick(plan)"
                      class="px-3 py-2 bg-amber-50 text-amber-700 font-bold rounded-full hover:bg-amber-100 hover:scale-105 transition-all duration-300"
                    >
                      ✏️
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Uncategorized Plans -->
        <div v-if="getUncategorizedPlans().length > 0">
          <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span>📋</span>
            <span>{{ folders.length > 0 ? '未分類のプラン' : 'すべてのプラン' }}</span>
          </h2>
        </div>

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
              v-for="plan in getUncategorizedPlans()" 
              :key="plan.id"
              class="group bg-white rounded-2xl sm:rounded-3xl shadow-lg overflow-hidden hover:shadow-2xl hover:scale-105 transition-all duration-300 border-2 border-cyan-100"
            >
              <!-- Image -->
              <div 
                class="h-40 sm:h-48 lg:h-56 bg-gradient-to-r from-cyan-200 via-blue-200 to-sky-200 relative overflow-hidden"
                :style="getPlanCoverImage(plan) ? `background-image: url(${getPlanCoverImage(plan)}); background-size: cover; background-position: center;` : ''"
              >
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent group-hover:from-black/50 transition-all duration-300"></div>
                
                <!-- Add to Folder Button -->
                <div v-if="folders.length > 0" class="absolute top-3 left-3">
                  <select 
                    @change="addPlanToFolder(plan.id, $event.target.value); $event.target.value = ''"
                    class="bg-white/90 backdrop-blur-sm text-gray-700 px-3 py-1.5 rounded-full text-xs font-bold cursor-pointer hover:bg-white transition-colors"
                    @click.stop
                  >
                    <option value="">フォルダに追加</option>
                    <option v-for="folder in folders" :key="folder.id" :value="folder.id">
                      📁 {{ folder.name }}
                    </option>
                  </select>
                </div>

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

    <!-- Folder Creation Modal -->
    <div v-if="showFolderModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="showFolderModal = false">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" @click.stop>
        <h3 class="text-2xl font-bold mb-4 text-gray-800">📁 新しいフォルダ</h3>
        <input 
          v-model="newFolderName"
          type="text"
          placeholder="フォルダ名を入力"
          class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent mb-4"
          @keyup.enter="createFolder"
        />
        <div class="mb-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input 
              v-model="newFolderIsPrivate"
              type="checkbox"
              class="w-5 h-5 text-amber-500 rounded focus:ring-2 focus:ring-amber-500"
            />
            <span class="text-gray-700">🔒 作成者のみに表示（プライベートフォルダ）</span>
          </label>
          <p class="text-sm text-gray-500 mt-1 ml-7">チェックを入れると、フォルダの作成者のみがこのフォルダを閲覧できます</p>
        </div>
        <div class="flex gap-3">
          <button 
            @click="showFolderModal = false"
            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
          >
            キャンセル
          </button>
          <button 
            @click="createFolder"
            class="flex-1 px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors font-semibold"
          >
            作成
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePlanStore } from '@/stores/planStore';
import axios from 'axios';

const planStore = usePlanStore();
const searchQuery = ref('');
const folders = ref([]);
const showFolderModal = ref(false);
const newFolderName = ref('');
const newFolderIsPrivate = ref(false);

// Load folders from API
const loadFolders = async () => {
  try {
    const response = await axios.get('/api/folders');
    folders.value = response.data.map(folder => ({
      ...folder,
      expanded: false
    }));
  } catch (error) {
    console.error('Failed to load folders:', error);
  }
};

// Create new folder
const createFolder = async () => {
  if (!newFolderName.value.trim()) {
    alert('フォルダ名を入力してください');
    return;
  }
  
  try {
    const response = await axios.post('/api/folders', {
      name: newFolderName.value.trim(),
      is_private: newFolderIsPrivate.value
    });
    
    folders.value.push({
      ...response.data,
      expanded: false
    });
    
    newFolderName.value = '';
    newFolderIsPrivate.value = false;
    showFolderModal.value = false;
  } catch (error) {
    console.error('Failed to create folder:', error);
    alert('フォルダの作成に失敗しました');
  }
};

// Toggle folder privacy
const toggleFolderPrivacy = async (folder) => {
  try {
    const response = await axios.put(`/api/folders/${folder.id}`, {
      is_private: !folder.is_private
    });
    
    folder.is_private = response.data.is_private;
  } catch (error) {
    console.error('Failed to update folder:', error);
    alert('フォルダの更新に失敗しました');
  }
};

// Delete folder
const deleteFolder = async (folderId) => {
  if (!confirm('このフォルダを削除しますか？（プランは削除されません）')) {
    return;
  }
  
  try {
    await axios.delete(`/api/folders/${folderId}`);
    folders.value = folders.value.filter(f => f.id !== folderId);
  } catch (error) {
    console.error('Failed to delete folder:', error);
    alert('フォルダの削除に失敗しました');
  }
};

// Toggle folder expansion
const toggleFolder = async (folderId) => {
  const folder = folders.value.find(f => f.id === folderId);
  if (folder) {
    folder.expanded = !folder.expanded;
    
    // Load plans if expanding
    if (folder.expanded && !folder.plans) {
      try {
        const response = await axios.get(`/api/folders/${folderId}`);
        folder.plans = response.data.plans;
      } catch (error) {
        console.error('Failed to load folder plans:', error);
      }
    }
  }
};

// Add plan to folder
const addPlanToFolder = async (planId, folderId) => {
  if (!folderId) return;
  
  try {
    await axios.post(`/api/folders/${folderId}/plans`, {
      plan_id: planId
    });
    
    // Reload folders
    await loadFolders();
  } catch (error) {
    console.error('Failed to add plan to folder:', error);
    alert('プランをフォルダに追加できませんでした');
  }
};

// Remove plan from folder
const removePlanFromFolder = async (planId, folderId) => {
  try {
    await axios.delete(`/api/folders/${folderId}/plans/${planId}`);
    
    // Reload folders
    await loadFolders();
  } catch (error) {
    console.error('Failed to remove plan from folder:', error);
    alert('フォルダからプランを削除できませんでした');
  }
};

// Get plans in a folder
const getPlansInFolder = (folderId) => {
  const folder = folders.value.find(f => f.id === folderId);
  return folder?.plans || [];
};

// Get uncategorized plans
const getUncategorizedPlans = () => {
  const plansInFolders = new Set();
  folders.value.forEach(folder => {
    if (folder.plans) {
      folder.plans.forEach(plan => plansInFolders.add(plan.id));
    }
  });
  
  return planStore.plans.filter(plan => !plansInFolders.has(plan.id));
};

// Get plan cover image
const getPlanCoverImage = (plan) => {
  // プランに画像がある場合、最初の画像を使用
  if (plan.images && plan.images.length > 0) {
    const firstImage = plan.images[0];
    // ストレージパスを生成
    return `/storage/${firstImage.path}`;
  }
  return null;
};

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
  loadFolders();
});
</script>

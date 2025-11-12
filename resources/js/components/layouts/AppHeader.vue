<template>
  <header class="bg-gradient-to-r from-pink-50 via-purple-50 to-blue-50 shadow-lg border-b-4 border-pink-200">
    <nav class="container mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-3 group">
          <div class="bg-gradient-to-br from-pink-400 to-purple-500 p-3 rounded-2xl shadow-md group-hover:scale-110 transition-transform duration-300">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <span class="text-2xl font-black bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 bg-clip-text text-transparent">
            旅の計画帳 ✈️
          </span>
        </router-link>
        
        <!-- Navigation -->
        <div class="flex items-center gap-3">
          <router-link 
            to="/" 
            class="px-5 py-2.5 text-gray-700 hover:text-pink-600 font-semibold rounded-full hover:bg-pink-100 transition-all duration-300 flex items-center gap-2"
          >
            <span class="text-xl">🏠</span>
            <span>ホーム</span>
          </router-link>
          <router-link 
            to="/qa" 
            class="px-5 py-2.5 text-gray-700 hover:text-green-600 font-semibold rounded-full hover:bg-green-100 transition-all duration-300 flex items-center gap-2"
          >
            <span class="text-xl">💬</span>
            <span>Q&A</span>
          </router-link>
          <router-link 
            to="/notifications" 
            class="px-5 py-2.5 text-gray-700 hover:text-orange-600 font-semibold rounded-full hover:bg-orange-100 transition-all duration-300 flex items-center gap-2 relative"
          >
            <span class="text-xl">📢</span>
            <span>お知らせ</span>
            <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </router-link>
          <router-link 
            to="/plans" 
            class="px-5 py-2.5 text-gray-700 hover:text-purple-600 font-semibold rounded-full hover:bg-purple-100 transition-all duration-300 flex items-center gap-2"
          >
            <span class="text-xl">📋</span>
            <span>プラン一覧</span>
          </router-link>
          
          <template v-if="authStore.isAuthenticated">
            <router-link 
              to="/plans/create" 
              class="px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-500 text-white font-bold rounded-full hover:from-pink-600 hover:to-purple-600 shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 flex items-center gap-2"
            >
              <span class="text-xl">✨</span>
              <span>新しい旅を計画</span>
            </router-link>
            
            <div class="flex items-center gap-2 ml-2">
              <!-- User Info -->
              <div class="px-4 py-3 bg-white rounded-full shadow-sm border border-purple-200">
                <div class="flex items-center gap-2">
                  <span class="text-sm text-gray-600">👤</span>
                  <div class="flex flex-col">
                    <span class="font-semibold text-gray-800">{{ authStore.user?.name }}</span>
                    <span v-if="isAdmin" class="text-xs text-red-600 font-bold">
                      管理者
                    </span>
                    <span v-else class="text-xs text-blue-600 font-bold">
                      利用者
                    </span>
                  </div>
                </div>
              </div>
              
              <!-- Dropdown Menu -->
              <div class="relative" ref="dropdownContainer">
                <button
                  @click="toggleDropdown"
                  class="px-4 py-2 text-gray-700 hover:text-purple-600 font-semibold rounded-full hover:bg-purple-100 transition-all duration-300 flex items-center gap-1"
                >
                  <span class="text-xl">⚙️</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                
                <!-- Dropdown Content -->
                <div
                  v-show="showDropdown"
                  class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border-2 border-purple-100 overflow-hidden z-50"
                >
                  <router-link
                    to="/change-password"
                    @click="closeDropdown"
                    class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition-all duration-200"
                  >
                    <span class="text-xl">🔐</span>
                    <span class="font-semibold">パスワード変更</span>
                  </router-link>
                  <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 transition-all duration-200 border-t border-gray-100"
                  >
                    <span class="text-xl">🚪</span>
                    <span class="font-semibold">ログアウト</span>
                  </button>
                </div>
              </div>
            </div>
          </template>
          
          <template v-else>
            <router-link 
              to="/login" 
              class="px-5 py-2.5 text-gray-700 hover:text-blue-600 font-semibold rounded-full hover:bg-blue-100 transition-all duration-300 flex items-center gap-2"
            >
              <span class="text-xl">🔑</span>
              <span>ログイン</span>
            </router-link>
            <router-link 
              to="/register" 
              class="px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-500 text-white font-bold rounded-full hover:from-pink-600 hover:to-purple-600 shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 flex items-center gap-2"
            >
              <span class="text-xl">✨</span>
              <span>新規登録</span>
            </router-link>
          </template>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useUiStore } from '@/stores/uiStore';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();
const uiStore = useUiStore();
const showDropdown = ref(false);
const dropdownContainer = ref(null);
const unreadCount = ref(0);

// Check if user is admin (email doesn't start with a digit)
const isAdmin = computed(() => {
  if (!authStore.user?.email) return false;
  return !/^\d/.test(authStore.user.email);
});

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
};

const closeDropdown = () => {
  showDropdown.value = false;
};

const handleClickOutside = (event) => {
  if (dropdownContainer.value && !dropdownContainer.value.contains(event.target)) {
    closeDropdown();
  }
};

const handleLogout = async () => {
  closeDropdown();
  try {
    await authStore.logout();
    uiStore.showSuccess('ログアウトしました');
    router.push('/');
  } catch (error) {
    console.error('Logout error:', error);
  }
};

// Fetch unread notification count
const fetchUnreadCount = async () => {
  if (!authStore.isAuthenticated) {
    unreadCount.value = 0;
    return;
  }
  
  try {
    const response = await axios.get('/api/notifications/unread-count');
    unreadCount.value = response.data.count || 0;
  } catch (error) {
    console.error('未読通知の取得に失敗しました:', error);
  }
};

// Polling for unread count
let pollingInterval = null;

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  
  // Initial fetch
  fetchUnreadCount();
  
  // Poll every 30 seconds
  pollingInterval = setInterval(fetchUnreadCount, 30000);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});
</script>

<template>
  <header class="bg-white shadow-md border-b-4 border-cyan-300">
    <nav class="container mx-auto px-4 sm:px-6 py-4">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2 sm:gap-3 group">
          <div class="bg-gradient-to-br from-cyan-400 to-blue-400 p-2 sm:p-3 rounded-2xl shadow-md group-hover:scale-110 transition-transform duration-300">
            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <span class="text-lg sm:text-2xl font-black bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent">
            旅の計画帳 ✈️
          </span>
        </router-link>
        
        <!-- Mobile Menu Button -->
        <button
          @click="toggleMobileMenu"
          class="lg:hidden p-2 text-gray-700 hover:text-cyan-600 hover:bg-cyan-50 rounded-lg transition-all"
        >
          <svg v-if="!showMobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        
        <!-- Desktop Navigation -->
        <div class="hidden lg:flex items-center gap-2">
          <router-link 
            to="/" 
            class="px-4 py-2.5 text-gray-700 hover:text-cyan-600 font-semibold rounded-full hover:bg-cyan-50 transition-all duration-300 flex items-center gap-2 whitespace-nowrap"
          >
            <span class="text-xl">🏠</span>
            <span>ホーム</span>
          </router-link>
          <router-link 
            to="/attachments" 
            class="px-4 py-2.5 text-gray-700 hover:text-blue-600 font-semibold rounded-full hover:bg-blue-50 transition-all duration-300 flex items-center gap-2 whitespace-nowrap"
          >
            <span class="text-xl">📎</span>
            <span>添付ファイル</span>
          </router-link>
          <router-link 
            to="/qa" 
            class="px-4 py-2.5 text-gray-700 hover:text-blue-600 font-semibold rounded-full hover:bg-blue-50 transition-all duration-300 flex items-center gap-2 whitespace-nowrap"
          >
            <span class="text-xl">💬</span>
            <span>Q&A</span>
          </router-link>
          <router-link 
            to="/notifications" 
            class="px-4 py-2.5 text-gray-700 hover:text-cyan-600 font-semibold rounded-full hover:bg-cyan-50 transition-all duration-300 flex items-center gap-2 relative whitespace-nowrap"
          >
            <span class="text-xl">📢</span>
            <span>お知らせ</span>
            <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-gradient-to-r from-cyan-400 to-blue-400 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center shadow-lg">
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </router-link>
          <router-link 
            to="/plans" 
            class="px-4 py-2.5 text-gray-700 hover:text-blue-600 font-semibold rounded-full hover:bg-blue-50 transition-all duration-300 flex items-center gap-2 whitespace-nowrap"
          >
            <span class="text-xl">📋</span>
            <span>プラン一覧</span>
          </router-link>
          
          <template v-if="authStore.isAuthenticated">
            <router-link 
              to="/plans/create" 
              class="px-5 py-3 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:from-cyan-500 hover:to-blue-500 shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 flex items-center gap-2 whitespace-nowrap"
            >
              <span class="text-xl">✨</span>
              <span>新しい旅を計画</span>
            </router-link>
            
            <div class="flex items-center gap-2 ml-2">
              <!-- User Info -->
              <div class="px-4 py-3 bg-gradient-to-br from-cyan-50 to-blue-50 rounded-full shadow-sm border-2 border-cyan-200">
                <div class="flex items-center gap-2">
                  <span class="text-sm text-gray-600">👤</span>
                  <div class="flex flex-col">
                    <span class="font-semibold text-gray-800">{{ authStore.user?.name }}</span>
                    <span v-if="isAdmin" class="text-xs text-cyan-600 font-bold">
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
                  class="px-4 py-2 text-gray-700 hover:text-cyan-600 font-semibold rounded-full hover:bg-cyan-50 transition-all duration-300 flex items-center gap-1"
                >
                  <span class="text-xl">⚙️</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                
                <!-- Dropdown Content -->
                <div
                  v-show="showDropdown"
                  class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border-2 border-cyan-200 overflow-hidden z-50"
                >
                  <router-link
                    to="/change-password"
                    @click="closeDropdown"
                    class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-cyan-50 hover:text-cyan-600 transition-all duration-200"
                  >
                    <span class="text-xl">🔐</span>
                    <span class="font-semibold">パスワード変更</span>
                  </router-link>
                  <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 transition-all duration-200 border-t border-cyan-100"
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
              class="px-4 py-2.5 text-gray-700 hover:text-cyan-600 font-semibold rounded-full hover:bg-cyan-50 transition-all duration-300 flex items-center gap-2 whitespace-nowrap"
            >
              <span class="text-xl">🔑</span>
              <span>ログイン</span>
            </router-link>
            <router-link 
              to="/register" 
              class="px-5 py-3 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:from-cyan-500 hover:to-blue-500 shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 flex items-center gap-2 whitespace-nowrap"
            >
              <span class="text-xl">✨</span>
              <span>新規登録</span>
            </router-link>
          </template>
        </div>
      </div>
      
      <!-- Mobile Navigation Menu -->
      <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-show="showMobileMenu" class="lg:hidden mt-4 pb-4 border-t border-cyan-100 pt-4">
          <div class="flex flex-col space-y-2">
            <!-- Main Navigation Links -->
            <router-link 
              @click="closeMobileMenu"
              to="/" 
              class="px-4 py-3 text-gray-700 hover:text-cyan-600 hover:bg-cyan-50 font-semibold rounded-lg transition-all flex items-center gap-3"
            >
              <span class="text-xl">🏠</span>
              <span>ホーム</span>
            </router-link>
            <router-link 
              @click="closeMobileMenu"
              to="/attachments" 
              class="px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 font-semibold rounded-lg transition-all flex items-center gap-3"
            >
              <span class="text-xl">📎</span>
              <span>添付ファイル</span>
            </router-link>
            <router-link 
              @click="closeMobileMenu"
              to="/qa" 
              class="px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 font-semibold rounded-lg transition-all flex items-center gap-3"
            >
              <span class="text-xl">💬</span>
              <span>Q&A</span>
            </router-link>
            <router-link 
              @click="closeMobileMenu"
              to="/notifications" 
              class="px-4 py-3 text-gray-700 hover:text-cyan-600 hover:bg-cyan-50 font-semibold rounded-lg transition-all flex items-center gap-3 relative"
            >
              <span class="text-xl">📢</span>
              <span>お知らせ</span>
              <span v-if="unreadCount > 0" class="ml-auto bg-gradient-to-r from-cyan-400 to-blue-400 text-white text-xs font-bold rounded-full px-2 py-1">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </span>
            </router-link>
            <router-link 
              @click="closeMobileMenu"
              to="/plans" 
              class="px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 font-semibold rounded-lg transition-all flex items-center gap-3"
            >
              <span class="text-xl">📋</span>
              <span>プラン一覧</span>
            </router-link>
            
            <!-- Authenticated User Actions -->
            <template v-if="authStore.isAuthenticated">
              <div class="pt-2 border-t border-cyan-100 mt-2">
                <div class="px-4 py-3 bg-gradient-to-br from-cyan-50 to-blue-50 rounded-lg mb-2">
                  <div class="flex items-center gap-2">
                    <span class="text-lg">👤</span>
                    <div>
                      <div class="font-semibold text-gray-800">{{ authStore.user?.name }}</div>
                      <div v-if="isAdmin" class="text-xs text-cyan-600 font-bold">管理者</div>
                      <div v-else class="text-xs text-blue-600 font-bold">利用者</div>
                    </div>
                  </div>
                </div>
                
                <router-link 
                  @click="closeMobileMenu"
                  to="/plans/create" 
                  class="px-4 py-3 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-lg shadow-lg flex items-center gap-3 justify-center mb-2"
                >
                  <span class="text-xl">✨</span>
                  <span>新しい旅を計画</span>
                </router-link>
                
                <router-link
                  @click="closeMobileMenu"
                  to="/change-password"
                  class="px-4 py-3 text-gray-700 hover:bg-cyan-50 hover:text-cyan-600 font-semibold rounded-lg transition-all flex items-center gap-3"
                >
                  <span class="text-xl">🔐</span>
                  <span>パスワード変更</span>
                </router-link>
                
                <button
                  @click="handleMobileLogout"
                  class="w-full px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 font-semibold rounded-lg transition-all flex items-center gap-3"
                >
                  <span class="text-xl">🚪</span>
                  <span>ログアウト</span>
                </button>
              </div>
            </template>
            
            <!-- Guest Actions -->
            <template v-else>
              <div class="pt-2 border-t border-cyan-100 mt-2 space-y-2">
                <router-link 
                  @click="closeMobileMenu"
                  to="/login" 
                  class="px-4 py-3 text-gray-700 hover:text-cyan-600 hover:bg-cyan-50 font-semibold rounded-lg transition-all flex items-center gap-3"
                >
                  <span class="text-xl">🔑</span>
                  <span>ログイン</span>
                </router-link>
                <router-link 
                  @click="closeMobileMenu"
                  to="/register" 
                  class="px-4 py-3 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-lg shadow-lg flex items-center gap-3 justify-center"
                >
                  <span class="text-xl">✨</span>
                  <span>新規登録</span>
                </router-link>
              </div>
            </template>
          </div>
        </div>
      </transition>
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
const showMobileMenu = ref(false);
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

const toggleMobileMenu = () => {
  showMobileMenu.value = !showMobileMenu.value;
};

const closeMobileMenu = () => {
  showMobileMenu.value = false;
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

const handleMobileLogout = async () => {
  closeMobileMenu();
  await handleLogout();
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

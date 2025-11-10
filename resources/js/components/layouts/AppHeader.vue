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
            
            <div class="flex items-center gap-3 ml-2">
              <div class="px-4 py-2 bg-white rounded-full shadow-sm border border-purple-200">
                <span class="text-sm text-gray-600">👤</span>
                <span class="ml-2 font-semibold text-gray-800">{{ authStore.user?.name }}</span>
                <span v-if="authStore.isAdmin" class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                  管理者
                </span>
              </div>
              
              <button
                @click="handleLogout"
                class="px-5 py-2.5 text-gray-700 hover:text-red-600 font-semibold rounded-full hover:bg-red-100 transition-all duration-300 flex items-center gap-2"
              >
                <span class="text-xl">🚪</span>
                <span>ログアウト</span>
              </button>
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
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useUiStore } from '@/stores/uiStore';

const router = useRouter();
const authStore = useAuthStore();
const uiStore = useUiStore();

const handleLogout = async () => {
  try {
    await authStore.logout();
    uiStore.showSuccess('ログアウトしました');
    router.push('/');
  } catch (error) {
    console.error('Logout error:', error);
  }
};
</script>

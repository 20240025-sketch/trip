<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-4xl font-extrabold bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent">
          ログイン
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          または
          <router-link to="/register" class="font-medium text-cyan-600 hover:text-cyan-500">
            新規登録
          </router-link>
        </p>
      </div>
      
      <form class="mt-8 space-y-6 bg-white rounded-2xl shadow-xl p-8 border-2 border-cyan-100" @submit.prevent="handleLogin">
        <div v-if="authStore.error" class="rounded-xl bg-red-50 p-4 border-2 border-red-200">
          <div class="flex">
            <div class="flex-shrink-0">
              <span class="text-red-400">⚠️</span>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                {{ authStore.error }}
              </h3>
            </div>
          </div>
        </div>

        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label for="email" class="block text-sm font-semibold text-cyan-700 mb-1">
              メールアドレス
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="appearance-none relative block w-full px-4 py-3 border-2 border-cyan-200 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 bg-cyan-50 transition-all"
              placeholder="email@example.com"
            />
          </div>
          
          <div>
            <label for="password" class="block text-sm font-semibold text-cyan-700 mb-1">
              パスワード
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="appearance-none relative block w-full px-4 py-3 border-2 border-cyan-200 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 bg-cyan-50 transition-all"
              placeholder="パスワード"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="authStore.loading"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-gradient-to-r from-cyan-400 to-blue-400 hover:from-cyan-500 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl"
          >
            <span v-if="authStore.loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
            </span>
            {{ authStore.loading ? 'ログイン中...' : 'ログイン' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useUiStore } from '@/stores/uiStore';

const router = useRouter();
const authStore = useAuthStore();
const uiStore = useUiStore();

const form = ref({
  email: '',
  password: '',
});

const handleLogin = async () => {
  try {
    await authStore.login(form.value);
    uiStore.showSuccess('ログインしました');
    router.push('/plans');
  } catch (error) {
    console.error('Login error:', error);
    // Error is already set in authStore
  }
};
</script>

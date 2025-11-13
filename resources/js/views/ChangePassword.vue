<template>
  <div class="py-8">
    <div class="max-w-2xl mx-auto px-4">
      <div class="bg-white rounded-3xl shadow-2xl p-8 border-2 border-cyan-200">
        <h1 class="text-4xl font-black mb-6 bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent">
          🔐 パスワード変更
        </h1>
        
        <p class="text-gray-600 mb-8">
          セキュリティのため、定期的にパスワードを変更することをおすすめします。
        </p>

        <form @submit.prevent="handleSubmit">
          <!-- Current Password -->
          <div class="mb-6">
            <label for="current_password" class="block text-sm font-bold text-cyan-700 mb-2">
              現在のパスワード <span class="text-red-500">*</span>
            </label>
            <input
              id="current_password"
              v-model="form.current_password"
              type="password"
              required
              class="w-full px-4 py-3 border-2 border-cyan-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-400 transition-all bg-cyan-50"
              :class="{ 'border-red-500': errors.current_password }"
              placeholder="現在のパスワードを入力"
            />
            <p v-if="errors.current_password" class="text-red-500 text-sm mt-1">
              {{ errors.current_password[0] }}
            </p>
          </div>

          <!-- New Password -->
          <div class="mb-6">
            <label for="password" class="block text-sm font-bold text-cyan-700 mb-2">
              新しいパスワード <span class="text-red-500">*</span>
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-full px-4 py-3 border-2 border-cyan-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-400 transition-all bg-cyan-50"
              :class="{ 'border-red-500': errors.password }"
              placeholder="新しいパスワードを入力（8文字以上）"
            />
            <p v-if="errors.password" class="text-red-500 text-sm mt-1">
              {{ errors.password[0] }}
            </p>
            <p class="text-gray-500 text-sm mt-1">
              8文字以上のパスワードを設定してください
            </p>
          </div>

          <!-- Confirm Password -->
          <div class="mb-8">
            <label for="password_confirmation" class="block text-sm font-bold text-cyan-700 mb-2">
              新しいパスワード（確認） <span class="text-red-500">*</span>
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="w-full px-4 py-3 border-2 border-cyan-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-400 transition-all bg-cyan-50"
              placeholder="もう一度新しいパスワードを入力"
            />
          </div>

          <!-- Buttons -->
          <div class="flex gap-4">
            <button
              type="submit"
              :disabled="submitting"
              class="flex-1 px-6 py-3 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="submitting">変更中...</span>
              <span v-else>✓ パスワードを変更</span>
            </button>
            <router-link
              to="/"
              class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-full hover:bg-gray-300 hover:scale-105 transition-all duration-300 text-center"
            >
              キャンセル
            </router-link>
          </div>
        </form>

        <!-- Password Tips -->
        <div class="mt-8 p-4 bg-cyan-50 border-2 border-cyan-200 rounded-xl">
          <h3 class="font-bold text-cyan-800 mb-2">💡 安全なパスワードのヒント</h3>
          <ul class="text-sm text-cyan-700 space-y-1">
            <li>• 8文字以上にする</li>
            <li>• 大文字・小文字・数字を組み合わせる</li>
            <li>• 推測されやすい単語は避ける</li>
            <li>• 定期的に変更する</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUiStore } from '@/stores/uiStore';
import axios from 'axios';

const router = useRouter();
const uiStore = useUiStore();

const form = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const errors = ref({});
const submitting = ref(false);

const handleSubmit = async () => {
  if (submitting.value) return;

  // Clear previous errors
  errors.value = {};
  
  // Validate
  if (!form.value.current_password) {
    errors.value.current_password = ['現在のパスワードを入力してください。'];
    return;
  }
  
  if (!form.value.password) {
    errors.value.password = ['新しいパスワードを入力してください。'];
    return;
  }
  
  if (form.value.password.length < 8) {
    errors.value.password = ['パスワードは8文字以上にしてください。'];
    return;
  }
  
  if (form.value.password !== form.value.password_confirmation) {
    errors.value.password = ['新しいパスワードが一致しません。'];
    return;
  }

  submitting.value = true;

  try {
    await axios.put('/api/password', form.value);
    
    uiStore.showSuccess('パスワードが正常に変更されました。');
    
    // Reset form
    form.value = {
      current_password: '',
      password: '',
      password_confirmation: '',
    };
    
    // Redirect to home after a short delay
    setTimeout(() => {
      router.push('/');
    }, 1500);
  } catch (error) {
    console.error('パスワード変更エラー:', error);
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else if (error.response?.data?.message) {
      uiStore.showError(error.response.data.message);
    } else {
      uiStore.showError('パスワードの変更に失敗しました。');
    }
  } finally {
    submitting.value = false;
  }
};
</script>

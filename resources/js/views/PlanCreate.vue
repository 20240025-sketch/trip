<template>
  <div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-8">新しい旅行プランを作成</h1>
    
    <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-md p-8">
      <!-- Title -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">タイトル *</label>
        <input 
          v-model="form.title" 
          type="text" 
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="例: 鎌倉日帰りの旅"
        >
      </div>

      <!-- Description -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">説明</label>
        <textarea 
          v-model="form.description" 
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="旅行の概要や目的を入力してください"
        ></textarea>
      </div>

      <!-- Dates -->
      <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div>
          <label class="block text-gray-700 font-bold mb-2">開始日 *</label>
          <input 
            v-model="form.start_date" 
            type="date" 
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-gray-700 font-bold mb-2">終了日 *</label>
          <input 
            v-model="form.end_date" 
            type="date" 
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
      </div>

      <!-- Memo -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">メモ</label>
        <textarea 
          v-model="form.memo" 
          rows="4"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="持ち物リストや注意事項など"
        ></textarea>
      </div>

      <!-- Public -->
      <div class="mb-8">
        <label class="flex items-center">
          <input 
            v-model="form.is_public" 
            type="checkbox"
            class="w-5 h-5 text-blue-600"
          >
          <span class="ml-2 text-gray-700">このプランを公開する</span>
        </label>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-600">{{ error }}</p>
      </div>

      <!-- Buttons -->
      <div class="flex gap-4">
        <button 
          type="submit" 
          :disabled="planStore.loading"
          class="px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
          {{ planStore.loading ? '作成中...' : '作成する' }}
        </button>
        <router-link 
          to="/plans" 
          class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300"
        >
          キャンセル
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { usePlanStore } from '@/stores/planStore';
import { useUiStore } from '@/stores/uiStore';

const router = useRouter();
const planStore = usePlanStore();
const uiStore = useUiStore();

const form = ref({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  memo: '',
  is_public: false,
});

const error = ref('');

const handleSubmit = async () => {
  error.value = '';
  
  try {
    const plan = await planStore.createPlan(form.value);
    uiStore.showSuccess('プランを作成しました');
    router.push(`/plans/${plan.id}`);
  } catch (err) {
    error.value = planStore.error || 'プランの作成に失敗しました';
  }
};
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-white via-cyan-50 to-blue-50 py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
      <div class="text-center mb-8 sm:mb-10">
        <div class="text-5xl sm:text-6xl mb-4">✈️</div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black mb-4 bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent px-4">
          新しい旅行プランを作成
        </h1>
        <p class="text-gray-600 text-base sm:text-lg px-4">
          まずは基本情報を入力しましょう！プラン作成後、詳細なスケジュールを追加できます ✨
        </p>
      </div>
      
      <form @submit.prevent="handleSubmit" class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl p-6 sm:p-10 border-2 border-cyan-100">
        <!-- Title -->
        <div class="mb-6 sm:mb-8">
          <label class="block text-gray-800 font-bold mb-2 sm:mb-3 text-base sm:text-lg flex items-center gap-2">
            <span class="text-xl sm:text-2xl">📝</span>
            <span>タイトル *</span>
          </label>
          <input 
            v-model="form.title" 
            type="text" 
            required
            class="w-full px-4 sm:px-6 py-3 sm:py-4 border-2 border-cyan-200 rounded-xl sm:rounded-2xl focus:outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-base sm:text-lg"
            placeholder="例: 鎌倉日帰りの旅 🌸"
          >
        </div>

        <!-- Description -->
        <div class="mb-6 sm:mb-8">
          <label class="block text-gray-800 font-bold mb-2 sm:mb-3 text-base sm:text-lg flex items-center gap-2">
            <span class="text-xl sm:text-2xl">💭</span>
            <span>説明</span>
          </label>
          <textarea 
            v-model="form.description" 
            rows="3"
            class="w-full px-4 sm:px-6 py-3 sm:py-4 border-2 border-cyan-200 rounded-xl sm:rounded-2xl focus:outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-base sm:text-lg resize-none"
            placeholder="旅行の概要や目的を入力してください"
          ></textarea>
        </div>

        <!-- Dates -->
        <div class="grid sm:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
          <div>
            <label class="block text-gray-800 font-bold mb-2 sm:mb-3 text-base sm:text-lg flex items-center gap-2">
              <span class="text-xl sm:text-2xl">📅</span>
              <span>開始日 *</span>
            </label>
            <input 
              v-model="form.start_date" 
              type="date" 
              required
              class="w-full px-4 sm:px-6 py-3 sm:py-4 border-2 border-cyan-200 rounded-xl sm:rounded-2xl focus:outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-base sm:text-lg"
            >
          </div>
          <div>
            <label class="block text-gray-800 font-bold mb-2 sm:mb-3 text-base sm:text-lg flex items-center gap-2">
              <span class="text-xl sm:text-2xl">📅</span>
              <span>終了日 *</span>
            </label>
            <input 
              v-model="form.end_date" 
              type="date" 
              required
              class="w-full px-4 sm:px-6 py-3 sm:py-4 border-2 border-cyan-200 rounded-xl sm:rounded-2xl focus:outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-base sm:text-lg"
            >
          </div>
        </div>

        <!-- Memo -->
        <div class="mb-6 sm:mb-8">
          <label class="block text-gray-800 font-bold mb-2 sm:mb-3 text-base sm:text-lg flex items-center gap-2">
            <span class="text-xl sm:text-2xl">📌</span>
            <span>メモ</span>
          </label>
          <textarea 
            v-model="form.memo" 
            rows="4"
            class="w-full px-4 sm:px-6 py-3 sm:py-4 border-2 border-cyan-200 rounded-xl sm:rounded-2xl focus:outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-base sm:text-lg resize-none"
            placeholder="持ち物リストや注意事項など"
          ></textarea>
        </div>

        <!-- Public -->
        <div class="mb-8 sm:mb-10 p-4 sm:p-6 bg-gradient-to-r from-cyan-50 to-blue-50 rounded-xl sm:rounded-2xl border-2 border-cyan-100">
          <label class="flex items-center cursor-pointer">
            <input 
              v-model="form.is_public" 
              type="checkbox"
              class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-600 rounded focus:ring-cyan-500"
            >
            <span class="ml-3 sm:ml-4 text-gray-800 font-bold text-base sm:text-lg flex items-center gap-2">
              <span class="text-xl sm:text-2xl">🌐</span>
              <span>このプランを公開する</span>
            </span>
          </label>
          <p class="text-xs sm:text-sm text-gray-600 ml-8 sm:ml-10 mt-2">
            公開すると、他のユーザーがあなたのプランを見ることができます
          </p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mb-6 p-5 bg-red-50 border-2 border-red-200 rounded-2xl">
          <p class="text-red-600 font-semibold flex items-center gap-2">
            <span class="text-2xl">⚠️</span>
            <span>{{ error }}</span>
          </p>
        </div>

        <!-- Info Box -->
        <div class="mb-6 sm:mb-8 p-4 sm:p-6 bg-gradient-to-r from-cyan-50 to-blue-50 border-2 border-cyan-200 rounded-xl sm:rounded-2xl">
          <p class="text-cyan-800 font-semibold flex items-start gap-2 sm:gap-3 text-sm sm:text-base">
            <span class="text-xl sm:text-2xl">💡</span>
            <span>
              <strong>ヒント:</strong> プラン作成後、各日の詳細なスケジュール（09:00 鶴岡八幡宮参拝など）を追加できます。
            </span>
          </p>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
          <button 
            type="submit" 
            :disabled="planStore.loading"
            class="flex-1 px-6 sm:px-10 py-4 sm:py-5 bg-gradient-to-r from-cyan-400 to-blue-400 text-white font-bold rounded-full hover:scale-105 hover:shadow-2xl disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 transition-all duration-300 text-base sm:text-xl flex items-center justify-center gap-2"
          >
            <span v-if="planStore.loading" class="flex items-center gap-2">
              <svg class="animate-spin h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>作成中...</span>
            </span>
            <span v-else class="flex items-center gap-2">
              <span class="text-xl sm:text-2xl">✨</span>
              <span class="hidden sm:inline">作成してスケジュールを追加</span>
              <span class="sm:hidden">作成する</span>
            </span>
          </button>
          <router-link 
            to="/plans" 
            class="px-6 sm:px-10 py-4 sm:py-5 bg-gray-200 text-gray-700 font-bold rounded-full hover:bg-gray-300 hover:scale-105 transition-all duration-300 text-base sm:text-xl flex items-center justify-center"
          >
            キャンセル
          </router-link>
        </div>
      </form>
    </div>
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
    uiStore.showSuccess('プランを作成しました。続けてスケジュールを追加しましょう！');
    // 作成後すぐに編集画面へ遷移してスケジュールを追加できるようにする
    router.push(`/plans/${plan.id}/edit`);
  } catch (err) {
    error.value = planStore.error || 'プランの作成に失敗しました';
  }
};
</script>

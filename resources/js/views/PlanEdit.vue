<template>
  <div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-8">プランを編集</h1>
    
    <div v-if="planStore.loading && !plan" class="text-center py-12">
      <div class="text-gray-500">読み込み中...</div>
    </div>

    <form v-else-if="plan" @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-md p-8">
      <!-- Title -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">タイトル *</label>
        <input 
          v-model="form.title" 
          type="text" 
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
      </div>

      <!-- Description -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">説明</label>
        <textarea 
          v-model="form.description" 
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
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
          {{ planStore.loading ? '更新中...' : '更新する' }}
        </button>
        <router-link 
          :to="`/plans/${plan.id}`" 
          class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300"
        >
          キャンセル
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePlanStore } from '@/stores/planStore';
import { useUiStore } from '@/stores/uiStore';

const route = useRoute();
const router = useRouter();
const planStore = usePlanStore();
const uiStore = useUiStore();

const plan = computed(() => planStore.currentPlan);

const form = ref({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  memo: '',
  is_public: false,
});

const error = ref('');

watch(plan, (newPlan) => {
  if (newPlan) {
    form.value = {
      title: newPlan.title,
      description: newPlan.description || '',
      start_date: newPlan.start_date,
      end_date: newPlan.end_date,
      memo: newPlan.memo || '',
      is_public: newPlan.is_public,
    };
  }
});

const handleSubmit = async () => {
  error.value = '';
  
  try {
    await planStore.updatePlan(plan.value.id, form.value);
    uiStore.showSuccess('プランを更新しました');
    router.push(`/plans/${plan.value.id}`);
  } catch (err) {
    error.value = planStore.error || 'プランの更新に失敗しました';
  }
};

onMounted(() => {
  planStore.fetchPlan(route.params.id);
});
</script>

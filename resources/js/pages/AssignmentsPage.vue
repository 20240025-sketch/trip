<template>
  <div class="min-h-screen bg-gradient-to-br from-white via-cyan-50 to-blue-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
      <!-- Header -->
      <div class="mb-8 bg-white rounded-2xl shadow-lg p-6 border-2 border-cyan-100">
        <h1 class="text-4xl font-bold mb-3 bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent">
          🚌🏠 バス・部屋割管理
        </h1>
        <p class="text-gray-600">旅行の日を選択してバス座席と部屋割を管理できます</p>
      </div>

      <!-- Loading State -->
      <div v-if="loadingDays" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-cyan-400 border-t-transparent"></div>
        <p class="text-gray-500 mt-4">読み込み中...</p>
      </div>

      <!-- Assignments Management -->
      <div v-if="!loadingDays" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <RoomAssignmentManager 
            :plans="plans"
          />
          <BusAssignmentManager 
            :plans="plans"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUiStore } from '@/stores/uiStore';
import RoomAssignmentManager from '@/components/RoomAssignmentManager.vue';
import BusAssignmentManager from '@/components/BusAssignmentManager.vue';
import axios from 'axios';

const router = useRouter();
const uiStore = useUiStore();

const plans = ref([]);
const loadingDays = ref(true);

const fetchPlans = async () => {
  loadingDays.value = true;
  try {
    // Get all plans that user can edit with days and participants
    const plansResponse = await axios.get('/api/plans');
    const editablePlans = plansResponse.data.data.filter(plan => plan.can_edit);
    
    // Fetch all plans with their days and participants
    const planPromises = editablePlans.map(async plan => {
      const planResponse = await axios.get(`/api/plans/${plan.id}`);
      const planData = planResponse.data;
      
      // Fetch participants for this plan
      try {
        const participantsResponse = await axios.get(`/api/plans/${plan.id}/participants`);
        planData.participants = participantsResponse.data;
      } catch (error) {
        console.error('Failed to fetch participants:', error);
        planData.participants = [];
      }
      
      return planData;
    });
    
    plans.value = await Promise.all(planPromises);
    console.log('Plans loaded with days and participants:', plans.value);
    
  } catch (error) {
    console.error('プランの取得に失敗しました:', error);
    uiStore.showError('プランの取得に失敗しました');
  } finally {
    loadingDays.value = false;
  }
};

onMounted(() => {
  fetchPlans();
});
</script>

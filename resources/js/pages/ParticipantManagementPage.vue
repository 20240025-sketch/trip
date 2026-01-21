<template>
  <div class="participant-management-page">
    <div class="container">
      <div class="page-header">
        <h1><i class="fas fa-users"></i> 参加者管理</h1>
        <router-link :to="`/plans/${planId}`" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> プランに戻る
        </router-link>
      </div>

      <div v-if="loading" class="loading">
        <i class="fas fa-spinner fa-spin"></i> 読み込み中...
      </div>

      <div v-else-if="plan">
        <div class="plan-info">
          <h2>{{ plan.title }}</h2>
          <p v-if="plan.description">{{ plan.description }}</p>
        </div>

        <ParticipantAssignmentManager 
          :plan-id="planId" 
          @saved="handleSaved"
        />
      </div>

      <div v-else class="error">
        プランが見つかりませんでした。
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import ParticipantAssignmentManager from '../components/ParticipantAssignmentManager.vue';

const route = useRoute();
const router = useRouter();
const planId = ref(route.params.id);
const plan = ref(null);
const loading = ref(true);

const loadPlan = async () => {
  try {
    const response = await axios.get(`/api/plans/${planId.value}`);
    plan.value = response.data.data || response.data;
  } catch (error) {
    console.error('プランの読み込みに失敗:', error);
  } finally {
    loading.value = false;
  }
};

const handleSaved = () => {
  // 保存後の処理（必要に応じて）
};

onMounted(() => {
  loadPlan();
});
</script>

<style scoped>
.participant-management-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding: 20px 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.page-header h1 {
  margin: 0;
  font-size: 28px;
  color: #333;
  display: flex;
  align-items: center;
  gap: 12px;
}

.plan-info {
  background: white;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.plan-info h2 {
  margin: 0 0 10px 0;
  font-size: 20px;
  color: #333;
}

.plan-info p {
  margin: 0;
  color: #666;
}

.loading,
.error {
  text-align: center;
  padding: 60px 20px;
  font-size: 18px;
  color: #666;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.error {
  color: #dc3545;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
}

.btn:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }

  .page-header h1 {
    font-size: 22px;
  }
}
</style>

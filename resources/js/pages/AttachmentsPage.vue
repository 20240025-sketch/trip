<template>
  <div class="attachments-page">
    <div class="page-header">
      <h1 class="page-title">📎 添付ファイル管理</h1>
      <p class="page-description">すべてのプランの添付ファイルを確認できます</p>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>読み込み中...</p>
    </div>

    <div v-else-if="plans.length === 0" class="no-data">
      <div class="icon">📂</div>
      <p class="message">プランがまだありません</p>
      <router-link to="/plans/create" class="btn btn-primary">
        最初のプランを作成
      </router-link>
    </div>

    <div v-else class="plans-list">
      <div
        v-for="plan in plans"
        :key="plan.id"
        class="plan-card"
      >
        <div class="plan-header">
          <div class="plan-info">
            <h2 class="plan-title">{{ plan.title }}</h2>
            <div class="plan-meta">
              <span class="date">📅 {{ formatDate(plan.start_date) }}</span>
              <span class="attachment-count">
                📎 {{ plan.attachment_count || 0 }} ファイル
              </span>
            </div>
          </div>
          <div class="plan-actions">
            <router-link
              :to="`/plans/${plan.id}`"
              class="btn btn-view"
            >
              詳細を見る
            </router-link>
          </div>
        </div>

        <div v-if="plan.attachments && plan.attachments.length > 0" class="attachments-preview">
          <div
            v-for="attachment in plan.attachments.slice(0, 3)"
            :key="attachment.id"
            class="attachment-item"
          >
            <div class="attachment-icon">
              <span v-if="attachment.is_image">🖼️</span>
              <span v-else-if="attachment.is_pdf">📄</span>
              <span v-else>📎</span>
            </div>
            <div class="attachment-info">
              <div class="attachment-name">{{ attachment.original_name }}</div>
              <div class="attachment-size">{{ attachment.formatted_size }}</div>
            </div>
            <div class="attachment-actions">
              <button
                @click="downloadAttachment(plan.id, attachment)"
                class="btn-icon"
                title="ダウンロード"
              >
                📥
              </button>
            </div>
          </div>
          <div v-if="plan.attachments.length > 3" class="more-attachments">
            他 {{ plan.attachments.length - 3 }} ファイル
          </div>
        </div>

        <div v-else class="no-attachments">
          📂 このプランにはまだ添付ファイルがありません
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useUiStore } from '@/stores/uiStore';

const uiStore = useUiStore();

const plans = ref([]);
const loading = ref(true);

const fetchPlansWithAttachments = async () => {
  loading.value = true;
  try {
    // プラン一覧を取得
    const plansResponse = await axios.get('/api/plans');
    const allPlans = plansResponse.data.data || plansResponse.data;

    // 各プランの添付ファイルを取得
    const plansWithAttachments = await Promise.all(
      allPlans.map(async (plan) => {
        try {
          const attachmentsResponse = await axios.get(`/api/plans/${plan.id}/attachments`);
          return {
            ...plan,
            attachments: attachmentsResponse.data,
            attachment_count: attachmentsResponse.data.length,
          };
        } catch (error) {
          // 添付ファイル取得に失敗した場合は空配列
          return {
            ...plan,
            attachments: [],
            attachment_count: 0,
          };
        }
      })
    );

    plans.value = plansWithAttachments;
  } catch (error) {
    console.error('プランの取得に失敗しました:', error);
    uiStore.showError('プランの読み込みに失敗しました');
  } finally {
    loading.value = false;
  }
};

const downloadAttachment = async (planId, attachment) => {
  try {
    const response = await axios.get(
      `/api/plans/${planId}/attachments/${attachment.id}/download`,
      {
        responseType: 'blob',
      }
    );

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', attachment.original_name);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('ファイルのダウンロードに失敗しました:', error);
    uiStore.showError('ファイルのダウンロードに失敗しました');
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ja-JP', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

onMounted(() => {
  fetchPlansWithAttachments();
});
</script>

<style scoped>
.attachments-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.page-header {
  margin-bottom: 32px;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 800;
  color: #0891b2;
  margin-bottom: 8px;
  text-shadow: 0 2px 4px rgba(8, 145, 178, 0.1);
}

.page-description {
  font-size: 1.1rem;
  color: #64748b;
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: #67e8f9;
}

.spinner {
  display: inline-block;
  width: 50px;
  height: 50px;
  border: 4px solid #a5f3fc;
  border-top-color: #06b6d4;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.no-data {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 16px;
  border: 2px dashed #a5f3fc;
}

.no-data .icon {
  font-size: 5rem;
  margin-bottom: 16px;
}

.no-data .message {
  font-size: 1.2rem;
  color: #64748b;
  margin-bottom: 24px;
}

.plans-list {
  display: grid;
  gap: 24px;
}

.plan-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(8, 145, 178, 0.1);
  border: 2px solid #a5f3fc;
  transition: all 0.3s;
}

.plan-card:hover {
  box-shadow: 0 6px 20px rgba(8, 145, 178, 0.2);
  transform: translateY(-2px);
}

.plan-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 2px solid #cffafe;
}

.plan-info {
  flex: 1;
}

.plan-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0e7490;
  margin-bottom: 8px;
}

.plan-meta {
  display: flex;
  gap: 16px;
  font-size: 0.9rem;
  color: #67e8f9;
}

.plan-actions {
  display: flex;
  gap: 8px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-block;
}

.btn-primary {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
  box-shadow: 0 6px 16px rgba(6, 182, 212, 0.4);
  transform: translateY(-2px);
}

.btn-view {
  background: linear-gradient(135deg, #67e8f9 0%, #06b6d4 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(6, 182, 212, 0.2);
}

.btn-view:hover {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
  transform: translateY(-2px);
}

.attachments-preview {
  display: grid;
  gap: 12px;
}

.attachment-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);
  border-radius: 10px;
  border: 1px solid #a5f3fc;
}

.attachment-icon {
  font-size: 1.8rem;
  flex-shrink: 0;
}

.attachment-info {
  flex: 1;
  min-width: 0;
}

.attachment-name {
  font-weight: 600;
  color: #0e7490;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-bottom: 2px;
}

.attachment-size {
  font-size: 0.85rem;
  color: #67e8f9;
}

.attachment-actions {
  display: flex;
  gap: 8px;
}

.btn-icon {
  background: white;
  border: 2px solid #a5f3fc;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 8px;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: #06b6d4;
  border-color: #06b6d4;
  transform: scale(1.1);
}

.more-attachments {
  text-align: center;
  padding: 12px;
  color: #0891b2;
  font-weight: 600;
  background: #ecfeff;
  border-radius: 8px;
  border: 1px dashed #a5f3fc;
}

.no-attachments {
  text-align: center;
  padding: 32px;
  color: #94a3b8;
  font-style: italic;
  background: #f8fafc;
  border-radius: 12px;
  border: 2px dashed #cbd5e1;
}

@media (max-width: 768px) {
  .attachments-page {
    padding: 15px;
  }

  .page-title {
    font-size: 2rem;
  }

  .plan-header {
    flex-direction: column;
    gap: 12px;
  }

  .plan-actions {
    width: 100%;
  }

  .btn-view {
    width: 100%;
    text-align: center;
  }

  .attachment-item {
    flex-wrap: wrap;
  }
}
</style>

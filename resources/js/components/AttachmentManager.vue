<template>
  <div class="attachment-manager">
    <div class="section-header">
      <h3 class="section-title">📎 添付ファイル</h3>
      <div class="upload-area">
        <input
          ref="fileInput"
          type="file"
          @change="handleFileSelect"
          accept="*/*"
          style="display: none"
        />
        <button
          @click="triggerFileInput"
          class="btn btn-upload"
          :disabled="uploading"
        >
          <span v-if="!uploading">📁 ファイルを添付</span>
          <span v-else>アップロード中...</span>
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading">読み込み中...</div>

    <div v-else-if="attachments.length === 0" class="no-attachments">
      <p>📂 添付ファイルはありません</p>
      <p class="hint">ファイルを添付して、PDF出力時に一覧表示できます</p>
    </div>

    <div v-else class="attachments-list">
      <div
        v-for="attachment in attachments"
        :key="attachment.id"
        class="attachment-card"
      >
        <div class="attachment-icon">
          <span v-if="attachment.is_image">🖼️</span>
          <span v-else-if="attachment.is_pdf">📄</span>
          <span v-else>📎</span>
        </div>
        <div class="attachment-info">
          <div class="attachment-name">{{ attachment.original_name }}</div>
          <div class="attachment-meta">
            <span class="file-size">{{ attachment.formatted_size }}</span>
            <span class="file-date">{{ formatDate(attachment.created_at) }}</span>
          </div>
        </div>
        <div class="attachment-actions">
          <a
            :href="attachment.url"
            target="_blank"
            class="btn-icon btn-preview"
            title="プレビュー"
          >
            👁️
          </a>
          <button
            @click="downloadAttachment(attachment)"
            class="btn-icon btn-download"
            title="ダウンロード"
          >
            📥
          </button>
          <button
            v-if="canEdit"
            @click="deleteAttachment(attachment)"
            class="btn-icon btn-delete"
            title="削除"
          >
            🗑️
          </button>
        </div>
      </div>
    </div>

    <!-- Upload Progress -->
    <div v-if="uploading" class="upload-progress">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: uploadProgress + '%' }"></div>
      </div>
      <p class="progress-text">{{ uploadProgress }}% - {{ uploadingFileName }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useUiStore } from '@/stores/uiStore';

const props = defineProps({
  planId: {
    type: [String, Number],
    required: true,
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
});

const uiStore = useUiStore();

const attachments = ref([]);
const loading = ref(true);
const uploading = ref(false);
const uploadProgress = ref(0);
const uploadingFileName = ref('');
const fileInput = ref(null);

const fetchAttachments = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/plans/${props.planId}/attachments`);
    attachments.value = response.data;
  } catch (error) {
    console.error('添付ファイルの取得に失敗しました:', error);
    uiStore.showError('添付ファイルの読み込みに失敗しました');
  } finally {
    loading.value = false;
  }
};

const triggerFileInput = () => {
  if (fileInput.value) {
    fileInput.value.click();
  }
};

const handleFileSelect = async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  // ファイルサイズチェック（10MB）
  if (file.size > 10 * 1024 * 1024) {
    uiStore.showError('ファイルサイズは10MB以下にしてください');
    return;
  }

  uploadingFileName.value = file.name;
  uploading.value = true;
  uploadProgress.value = 0;

  const formData = new FormData();
  formData.append('file', file);

  try {
    const response = await axios.post(
      `/api/plans/${props.planId}/attachments`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
          uploadProgress.value = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          );
        },
      }
    );

    attachments.value.push(response.data);
    uiStore.showSuccess('ファイルを添付しました');

    // Reset file input
    if (fileInput.value) {
      fileInput.value.value = '';
    }
  } catch (error) {
    console.error('ファイルのアップロードに失敗しました:', error);
    uiStore.showError('ファイルのアップロードに失敗しました');
  } finally {
    uploading.value = false;
    uploadProgress.value = 0;
    uploadingFileName.value = '';
  }
};

const downloadAttachment = async (attachment) => {
  try {
    const response = await axios.get(
      `/api/plans/${props.planId}/attachments/${attachment.id}/download`,
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

const deleteAttachment = async (attachment) => {
  if (!confirm(`「${attachment.original_name}」を削除しますか？`)) {
    return;
  }

  try {
    await axios.delete(
      `/api/plans/${props.planId}/attachments/${attachment.id}`
    );

    attachments.value = attachments.value.filter((a) => a.id !== attachment.id);
    uiStore.showSuccess('ファイルを削除しました');
  } catch (error) {
    console.error('ファイルの削除に失敗しました:', error);
    uiStore.showError('ファイルの削除に失敗しました');
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ja-JP', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  });
};

onMounted(() => {
  fetchAttachments();
});
</script>

<style scoped>
.attachment-manager {
  margin-top: 24px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.section-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #0891b2;
  margin: 0;
}

.upload-area {
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
}

.btn-upload {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
}

.btn-upload:hover:not(:disabled) {
  background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
  box-shadow: 0 6px 16px rgba(6, 182, 212, 0.4);
  transform: translateY(-2px);
}

.btn-upload:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading,
.no-attachments {
  text-align: center;
  padding: 40px;
  color: #67e8f9;
  font-size: 1.1rem;
  background: white;
  border-radius: 16px;
  border: 2px dashed #a5f3fc;
}

.no-attachments p {
  margin: 8px 0;
}

.no-attachments .hint {
  font-size: 0.9rem;
  color: #94a3b8;
}

.attachments-list {
  display: grid;
  gap: 12px;
}

.attachment-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: white;
  border: 2px solid #a5f3fc;
  border-radius: 12px;
  transition: all 0.3s;
}

.attachment-card:hover {
  box-shadow: 0 4px 12px rgba(8, 145, 178, 0.15);
  transform: translateY(-2px);
}

.attachment-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.attachment-info {
  flex: 1;
  min-width: 0;
}

.attachment-name {
  font-weight: 600;
  color: #0e7490;
  margin-bottom: 4px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.attachment-meta {
  display: flex;
  gap: 16px;
  font-size: 0.85rem;
  color: #67e8f9;
}

.attachment-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 1.3rem;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon:hover {
  background: #ecfeff;
  transform: scale(1.1);
}

.btn-delete:hover {
  background: #fee2e2;
}

.upload-progress {
  margin-top: 16px;
  padding: 16px;
  background: #ecfeff;
  border-radius: 12px;
  border: 2px solid #a5f3fc;
}

.progress-bar {
  height: 8px;
  background: #cffafe;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  transition: width 0.3s;
}

.progress-text {
  text-align: center;
  font-size: 0.9rem;
  color: #0891b2;
  font-weight: 600;
}

@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .attachment-card {
    flex-wrap: wrap;
  }

  .attachment-actions {
    width: 100%;
    justify-content: flex-end;
  }
}
</style>

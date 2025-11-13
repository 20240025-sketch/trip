<template>
  <div class="notifications-page">
    <div class="page-header">
      <h1 class="page-title">📢 お知らせ</h1>
      <div class="header-actions">
        <button
          v-if="hasUnread"
          @click="markAllAsRead"
          class="btn btn-secondary"
        >
          すべて既読にする
        </button>
        <button
          v-if="isAdmin"
          @click="showCreateForm = true"
          class="btn btn-primary"
        >
          ✏️ お知らせを投稿
        </button>
      </div>
    </div>

    <!-- Create Form (Admin Only) -->
    <div v-if="showCreateForm && isAdmin" class="create-form-card">
      <h2>新しいお知らせを投稿</h2>
      <form @submit.prevent="submitNotification">
        <div class="form-group">
          <label for="title">タイトル</label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            placeholder="お知らせのタイトル"
            required
          />
        </div>
        
        <div class="form-group">
          <label for="content">内容</label>
          <textarea
            id="content"
            v-model="form.content"
            placeholder="お知らせの内容を入力..."
            rows="5"
            required
          ></textarea>
        </div>
        
        <div class="form-group">
          <label for="type">種類</label>
          <select id="type" v-model="form.type">
            <option value="info">お知らせ</option>
            <option value="warning">注意</option>
            <option value="success">成功</option>
            <option value="error">重要</option>
          </select>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            {{ submitting ? '投稿中...' : '投稿する' }}
          </button>
          <button type="button" class="btn btn-secondary" @click="cancelCreate">
            キャンセル
          </button>
        </div>
      </form>
    </div>

    <!-- Notifications List -->
    <div class="notifications-list">
      <div v-if="loading" class="loading">読み込み中...</div>
      
      <div v-else-if="notifications.length === 0" class="no-notifications">
        まだお知らせがありません
      </div>
      
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="['notification-card', `type-${notification.type}`, { unread: !notification.is_read }]"
        @click="markAsRead(notification)"
      >
        <div class="notification-header">
          <div class="notification-badge" :class="`badge-${notification.type}`">
            {{ getTypeLabel(notification.type) }}
          </div>
          <span v-if="!notification.is_read" class="unread-badge">未読</span>
          <span class="notification-date">{{ formatDate(notification.created_at) }}</span>
          <button
            v-if="authStore.user?.id === notification.user_id"
            @click.stop="deleteNotification(notification.id)"
            class="btn-delete"
            title="削除"
          >
            ×
          </button>
        </div>
        
        <h3 class="notification-title">{{ notification.title }}</h3>
        <p class="notification-content">{{ notification.content }}</p>
        
        <div class="notification-footer">
          <span class="notification-author">投稿者: {{ notification.author }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/authStore';
import axios from 'axios';

const authStore = useAuthStore();
const notifications = ref([]);
const loading = ref(true);
const submitting = ref(false);
const showCreateForm = ref(false);
const form = ref({
  title: '',
  content: '',
  type: 'info',
});

const isAdmin = computed(() => authStore.user?.is_admin === true || authStore.user?.role === 'admin');
const hasUnread = computed(() => notifications.value.some(n => !n.is_read));

// Fetch notifications
const fetchNotifications = async () => {
  try {
    const response = await axios.get('/api/notifications');
    notifications.value = response.data;
    loading.value = false;
  } catch (error) {
    console.error('お知らせの取得に失敗しました:', error);
    loading.value = false;
  }
};

// Submit new notification
const submitNotification = async () => {
  if (!form.value.title || !form.value.content) return;
  
  submitting.value = true;
  try {
    await axios.post('/api/notifications', form.value);
    
    // Reset form
    form.value = {
      title: '',
      content: '',
      type: 'info',
    };
    showCreateForm.value = false;
    
    // Refresh notifications
    await fetchNotifications();
  } catch (error) {
    console.error('お知らせの投稿に失敗しました:', error);
    alert('お知らせの投稿に失敗しました');
  } finally {
    submitting.value = false;
  }
};

// Cancel create
const cancelCreate = () => {
  showCreateForm.value = false;
  form.value = {
    title: '',
    content: '',
    type: 'info',
  };
};

// Mark as read
const markAsRead = async (notification) => {
  if (notification.is_read || !authStore.isAuthenticated) return;
  
  try {
    await axios.post(`/api/notifications/${notification.id}/read`);
    notification.is_read = true;
  } catch (error) {
    console.error('既読処理に失敗しました:', error);
  }
};

// Mark all as read
const markAllAsRead = async () => {
  try {
    await axios.post('/api/notifications/read-all');
    notifications.value.forEach(n => n.is_read = true);
  } catch (error) {
    console.error('一括既読処理に失敗しました:', error);
  }
};

// Delete notification
const deleteNotification = async (id) => {
  if (!confirm('このお知らせを削除しますか?')) return;
  
  try {
    await axios.delete(`/api/notifications/${id}`);
    await fetchNotifications();
  } catch (error) {
    console.error('お知らせの削除に失敗しました:', error);
    alert('お知らせの削除に失敗しました');
  }
};

// Get type label
const getTypeLabel = (type) => {
  const labels = {
    info: 'お知らせ',
    warning: '注意',
    success: '成功',
    error: '重要',
  };
  return labels[type] || 'お知らせ';
};

// Format date
const formatDate = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  
  if (diffMins < 1) return 'たった今';
  if (diffMins < 60) return `${diffMins}分前`;
  
  const diffHours = Math.floor(diffMins / 60);
  if (diffHours < 24) return `${diffHours}時間前`;
  
  const month = date.getMonth() + 1;
  const day = date.getDate();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  
  return `${month}/${day} ${hours}:${minutes}`;
};

// Polling for real-time updates
let pollingInterval = null;

onMounted(() => {
  fetchNotifications();
  // Poll every 10 seconds
  pollingInterval = setInterval(fetchNotifications, 10000);
});

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});
</script>

<style scoped>
.notifications-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-title {
  font-size: 2rem;
  margin: 0;
  color: #0891b2;
  text-shadow: 0 2px 4px rgba(8, 145, 178, 0.1);
}

.header-actions {
  display: flex;
  gap: 10px;
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

.btn-primary {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
  box-shadow: 0 6px 16px rgba(6, 182, 212, 0.4);
  transform: translateY(-2px);
}

.btn-secondary {
  background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
}

.btn-secondary:hover {
  background: linear-gradient(135deg, #64748b 0%, #475569 100%);
  box-shadow: 0 6px 16px rgba(100, 116, 139, 0.4);
  transform: translateY(-2px);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.create-form-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(8, 145, 178, 0.1);
  margin-bottom: 30px;
  border: 2px solid #a5f3fc;
}

.create-form-card h2 {
  font-size: 1.3rem;
  margin-bottom: 20px;
  color: #0891b2;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #0e7490;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 12px;
  border: 2px solid #a5f3fc;
  border-radius: 12px;
  font-size: 1rem;
  font-family: inherit;
  transition: all 0.3s;
  background: #ecfeff;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #06b6d4;
  background: white;
  box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
}

.form-group textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 10px;
}

.notifications-list {
  margin-top: 30px;
}

.loading,
.no-notifications {
  text-align: center;
  padding: 40px;
  color: #67e8f9;
  font-size: 1.1rem;
  background: white;
  border-radius: 16px;
  border: 2px dashed #a5f3fc;
}

.notification-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(8, 145, 178, 0.1);
  margin-bottom: 16px;
  cursor: pointer;
  transition: all 0.3s;
  border-left: 4px solid #a5f3fc;
}

.notification-card.unread {
  background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);
  border-left-color: #06b6d4;
  box-shadow: 0 4px 16px rgba(6, 182, 212, 0.2);
}

.notification-card:hover {
  box-shadow: 0 6px 20px rgba(8, 145, 178, 0.2);
  transform: translateY(-2px);
}

.notification-card.type-info {
  border-left-color: #06b6d4;
}

.notification-card.type-warning {
  border-left-color: #f59e0b;
}

.notification-card.type-success {
  border-left-color: #10b981;
}

.notification-card.type-error {
  border-left-color: #ef4444;
}

.notification-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.notification-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.badge-info {
  background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%);
  color: #0e7490;
}

.badge-warning {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #92400e;
}

.badge-success {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #065f46;
}

.badge-error {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #991b1b;
}

.unread-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(6, 182, 212, 0.3);
}

.notification-date {
  font-size: 0.85rem;
  color: #67e8f9;
  margin-left: auto;
}

.btn-delete {
  background: linear-gradient(135deg, #f43f5e 0%, #dc2626 100%);
  color: white;
  border: none;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  font-size: 1.3rem;
  line-height: 1;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 2px 8px rgba(244, 63, 94, 0.3);
}

.btn-delete:hover {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  box-shadow: 0 4px 12px rgba(244, 63, 94, 0.4);
  transform: scale(1.1);
}

.notification-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #0e7490;
  margin-bottom: 8px;
}

.notification-content {
  font-size: 1rem;
  line-height: 1.6;
  color: #164e63;
  white-space: pre-wrap;
  word-wrap: break-word;
  margin-bottom: 12px;
}

.notification-footer {
  font-size: 0.85rem;
  color: #67e8f9;
}

.notification-author {
  font-style: italic;
}

@media (max-width: 768px) {
  .notifications-page {
    padding: 15px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions .btn {
    flex: 1;
  }
}
</style>

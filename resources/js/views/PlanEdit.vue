<template>
  <div class="plan-edit-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">✏️ プランを編集</h1>
        <div class="header-actions">
          <router-link 
            :to="`/plans/${plan?.id}`" 
            class="btn btn-secondary"
          >
            ← プレビュー
          </router-link>
        </div>
      </div>
    </div>
    
    <div v-if="planStore.loading && !plan" class="loading-state">
      <div class="spinner"></div>
      <p>読み込み中...</p>
    </div>

    <div v-else-if="plan" class="content-wrapper">
      <!-- Section Navigation -->
      <div class="section-nav">
        <a href="#basic-info" class="nav-item">📝 基本情報</a>
        <a href="#schedule" class="nav-item">📅 スケジュール</a>
        <a href="#images" class="nav-item">📸 画像</a>
        <a href="#attachments" class="nav-item">📎 添付ファイル</a>
      </div>

      <!-- Basic Info Section -->
      <section id="basic-info" class="content-section">
        <div class="section-card">
          <div class="section-header">
            <div class="section-icon">📝</div>
            <div class="section-info">
              <h2 class="section-title">基本情報</h2>
              <p class="section-description">旅行の基本的な情報を入力してください</p>
            </div>
          </div>
          
          <form @submit.prevent="handleSubmit" class="form-content">
            <!-- Title -->
            <div class="form-group">
              <label class="form-label required">タイトル</label>
              <input 
                v-model="form.title" 
                type="text" 
                required
                class="form-input"
                placeholder="例: 箱根温泉旅行 1泊2日"
              >
            </div>

            <!-- Description -->
            <div class="form-group">
              <label class="form-label">説明</label>
              <textarea 
                v-model="form.description" 
                rows="3"
                class="form-input"
                placeholder="旅行の概要や目的を記入してください"
              ></textarea>
            </div>

            <!-- Dates -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label required">開始日</label>
                <input 
                  v-model="form.start_date" 
                  type="date" 
                  required
                  class="form-input"
                >
              </div>
              <div class="form-group">
                <label class="form-label required">終了日</label>
                <input 
                  v-model="form.end_date" 
                  type="date" 
                  required
                  class="form-input"
                >
              </div>
            </div>

            <!-- Memo -->
            <div class="form-group">
              <label class="form-label">メモ</label>
              <textarea 
                v-model="form.memo" 
                rows="4"
                class="form-input"
                placeholder="その他のメモや注意事項を記入してください"
              ></textarea>
            </div>

            <!-- Public -->
            <div class="form-group">
              <label class="checkbox-label">
                <input 
                  v-model="form.is_public" 
                  type="checkbox"
                  class="checkbox-input"
                >
                <span class="checkbox-text">🌐 このプランを公開する</span>
              </label>
              <p class="form-hint">公開すると、他のユーザーがこのプランを閲覧できます</p>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="error-message">
              <span class="error-icon">⚠️</span>
              <p>{{ error }}</p>
            </div>

            <!-- Buttons -->
            <div class="form-actions">
              <button 
                type="submit" 
                :disabled="planStore.loading"
                class="btn btn-primary"
              >
                <span v-if="!planStore.loading">✓ 基本情報を更新</span>
                <span v-else>更新中...</span>
              </button>
              <router-link 
                :to="`/plans/${plan.id}`" 
                class="btn btn-secondary"
              >
                キャンセル
              </router-link>
            </div>
          </form>
        </div>
      </section>

      <!-- Schedule Section -->
      <section id="schedule" class="content-section">
        <div class="section-card">
          <div class="section-header">
            <div class="section-icon">📅</div>
            <div class="section-info">
              <h2 class="section-title">日程別スケジュール</h2>
              <p class="section-description">各日程のスケジュールを管理できます</p>
            </div>
          </div>
          
          <div v-if="plan.days && plan.days.length > 0" class="schedule-content">
            <DaySchedule 
              v-for="day in plan.days"
              :key="day.id"
              :day-id="day.id"
              :day-number="day.day_number"
              :date="day.date"
              :title="day.title"
              :items="day.schedule_items || []"
              @add-item="handleAddScheduleItem"
              @update-item="handleUpdateScheduleItem"
              @delete-item="handleDeleteScheduleItem"
              @update-title="handleUpdateDayTitle"
              @refresh="refreshPlan"
            />
          </div>
          
          <div v-else class="info-message">
            <span class="info-icon">ℹ️</span>
            <div>
              <p class="info-title">日程がまだ作成されていません</p>
              <p class="info-text">基本情報を保存すると、日程が自動作成されます</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Images Section -->
      <section id="images" class="content-section">
        <div class="section-card">
          <div class="section-header">
            <div class="section-icon">📸</div>
            <div class="section-info">
              <h2 class="section-title">画像管理</h2>
              <p class="section-description">旅の思い出の写真や参考画像を追加できます</p>
            </div>
          </div>
          
          <div class="image-content">
            <!-- Upload Section -->
            <div class="upload-section">
              <h3 class="subsection-title">⬆️ 画像を追加</h3>
              <ImageUploader
                imageable-type="plan"
                :imageable-id="plan.id"
                @uploaded="handleImagesUploaded"
              />
            </div>
            
            <!-- Gallery Section -->
            <div v-if="plan.images && plan.images.length > 0" class="gallery-section">
              <h3 class="subsection-title">🖼️ アップロード済み画像 ({{ plan.images.length }}枚)</h3>
              <ImageGallery
                :images="plan.images"
                @delete="handleDeleteImage"
              />
            </div>
            
            <div v-else class="empty-state">
              <p class="empty-title">まだ画像が追加されていません</p>
              <p class="empty-text">上のエリアから画像をアップロードしてください</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Attachments Section -->
      <section id="attachments" class="content-section">
        <div class="section-card">
          <AttachmentManager
            :plan-id="plan.id"
            :can-edit="true"
          />
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePlanStore } from '@/stores/planStore';
import { useScheduleStore } from '@/stores/scheduleStore';
import { useUiStore } from '@/stores/uiStore';
import DaySchedule from '@/components/schedule/DaySchedule.vue';
import ImageUploader from '@/components/image/ImageUploader.vue';
import ImageGallery from '@/components/image/ImageGallery.vue';
import AttachmentManager from '@/components/AttachmentManager.vue';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const planStore = usePlanStore();
const scheduleStore = useScheduleStore();
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
    uiStore.showSuccess('基本情報を更新しました');
    // Reload to get updated days
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    error.value = planStore.error || 'プランの更新に失敗しました';
  }
};

const handleAddScheduleItem = async (data) => {
  console.log('Adding schedule item:', data); // デバッグログ
  
  try {
    const result = await scheduleStore.createScheduleItem(data.dayId, data);
    console.log('Schedule item created:', result); // デバッグログ
    uiStore.showSuccess('スケジュールを追加しました');
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    console.error('Failed to add schedule item:', err); // デバッグログ
    uiStore.showError(scheduleStore.error || 'スケジュールの追加に失敗しました');
  }
};

const handleUpdateScheduleItem = async (item) => {
  console.log('Updating schedule item:', item); // デバッグログ
  
  try {
    await scheduleStore.updateScheduleItem(item.day_id, item.id, item);
    uiStore.showSuccess('スケジュールを更新しました');
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    console.error('Failed to update schedule item:', err); // デバッグログ
    uiStore.showError(scheduleStore.error || 'スケジュールの更新に失敗しました');
  }
};

const handleDeleteScheduleItem = async (item) => {
  console.log('Deleting schedule item:', item); // デバッグログ
  
  try {
    await scheduleStore.deleteScheduleItem(item.day_id, item.id);
    uiStore.showSuccess('スケジュールを削除しました');
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    console.error('Failed to delete schedule item:', err); // デバッグログ
    uiStore.showError(scheduleStore.error || 'スケジュールの削除に失敗しました');
  }
};

const handleUpdateDayTitle = async (data) => {
  console.log('Updating day title:', data); // デバッグログ
  
  try {
    await scheduleStore.updateDay(plan.value.id, data.dayId, { title: data.title });
    uiStore.showSuccess('日程のタイトルを更新しました');
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    console.error('Failed to update day title:', err); // デバッグログ
    uiStore.showError(scheduleStore.error || '日程の更新に失敗しました');
  }
};

const handleImagesUploaded = async () => {
  uiStore.showSuccess('画像をアップロードしました');
  await planStore.fetchPlan(plan.value.id);
};

const handleDeleteImage = async (imageId) => {
  try {
    await axios.delete(`/api/images/${imageId}`);
    uiStore.showSuccess('画像を削除しました');
    await planStore.fetchPlan(plan.value.id);
  } catch (err) {
    console.error('Failed to delete image:', err);
    uiStore.showError('画像の削除に失敗しました');
  }
};

const refreshPlan = async () => {
  await planStore.fetchPlan(plan.value.id);
};

onMounted(() => {
  planStore.fetchPlan(route.params.id);
});
</script>

<style scoped>
.plan-edit-page {
  max-width: 1600px;
  margin: 0 auto;
  padding: 1rem;
}

@media (min-width: 640px) {
  .plan-edit-page {
    padding: 2rem 1.5rem;
  }
}

@media (min-width: 1024px) {
  .plan-edit-page {
    padding: 2rem 3rem;
  }
}

/* Page Header */
.page-header {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  border-radius: 1rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 10px 30px rgba(6, 182, 212, 0.15);
}

@media (min-width: 640px) {
  .page-header {
    border-radius: 1.5rem;
    padding: 2rem;
    margin-bottom: 2rem;
  }
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 640px) {
  .header-content {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  margin: 0;
}

@media (min-width: 640px) {
  .page-title {
    font-size: 2rem;
  }
}

.header-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

/* Section Navigation */
.section-nav {
  background: white;
  border-radius: 0.75rem;
  padding: 0.75rem;
  margin-bottom: 1.5rem;
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  border: 2px solid #e0f2fe;
}

@media (min-width: 640px) {
  .section-nav {
    border-radius: 1rem;
    padding: 1rem;
    margin-bottom: 2rem;
  }
}

.nav-item {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  color: #0891b2;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
  border: 2px solid transparent;
  font-size: 0.875rem;
}

@media (min-width: 640px) {
  .nav-item {
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-size: 1rem;
  }
}

.nav-item:hover {
  background: linear-gradient(135deg, #e0f2fe 0%, #cffafe 100%);
  border-color: #06b6d4;
  color: #0e7490;
}

/* Content Section */
.content-section {
  margin-bottom: 1.5rem;
  scroll-margin-top: 6rem;
}

@media (min-width: 640px) {
  .content-section {
    margin-bottom: 2rem;
  }
}

.section-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 2px solid #e0f2fe;
}

@media (min-width: 640px) {
  .section-card {
    border-radius: 1.5rem;
    padding: 2rem;
  }
}

@media (min-width: 1024px) {
  .section-card {
    padding: 2.5rem 3rem;
  }
}

.section-header {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #e0f2fe;
}

@media (min-width: 640px) {
  .section-header {
    flex-direction: row;
    gap: 1.5rem;
    margin-bottom: 2rem;
  }
}

.section-icon {
  font-size: 2rem;
  width: 3rem;
  height: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  border-radius: 0.75rem;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2);
}

@media (min-width: 640px) {
  .section-icon {
    font-size: 2.5rem;
    width: 4rem;
    height: 4rem;
    border-radius: 1rem;
  }
}

.section-info {
  flex: 1;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.5rem 0;
}

@media (min-width: 640px) {
  .section-title {
    font-size: 1.75rem;
  }
}

.section-description {
  color: #64748b;
  margin: 0;
  font-size: 0.875rem;
}

@media (min-width: 640px) {
  .section-description {
    font-size: 0.95rem;
  }
}

/* Form Elements */
.form-content {
  margin-top: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

@media (min-width: 640px) {
  .form-group {
    margin-bottom: 2rem;
  }
}

.form-row {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 640px) {
  .form-row {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
  }
}

.form-label {
  display: block;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

@media (min-width: 640px) {
  .form-label {
    font-size: 0.95rem;
  }
}

.form-label.required::after {
  content: " *";
  color: #06b6d4;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e0f2fe;
  border-radius: 0.75rem;
  font-size: 0.875rem;
  line-height: 1.5;
  transition: all 0.2s;
  background: #f8fafc;
}

@media (min-width: 640px) {
  .form-input {
    padding: 0.875rem 1.25rem;
    font-size: 1rem;
  }
}

.form-input:focus {
  outline: none;
  border-color: #06b6d4;
  background: white;
  box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
}

.form-hint {
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 0.5rem;
}

@media (min-width: 640px) {
  .form-hint {
    font-size: 0.875rem;
  }
}

/* Checkbox */
.checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
  gap: 0.75rem;
}

.checkbox-input {
  width: 1.25rem;
  height: 1.25rem;
  accent-color: #06b6d4;
  cursor: pointer;
}

.checkbox-text {
  color: #1e293b;
  font-weight: 500;
  font-size: 0.875rem;
}

@media (min-width: 640px) {
  .checkbox-text {
    font-size: 1rem;
  }
}

/* Buttons */
.btn {
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  border-radius: 0.75rem;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  white-space: nowrap;
}

@media (min-width: 640px) {
  .btn {
    padding: 0.875rem 2rem;
    font-size: 1rem;
  }
}

.btn-primary {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(6, 182, 212, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #e2e8f0;
  border-color: #cbd5e1;
}

.form-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid #e0f2fe;
}

@media (min-width: 640px) {
  .form-actions {
    flex-direction: row;
    gap: 1rem;
    margin-top: 2rem;
  }
}

/* Messages */
.error-message {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
  background: #fef2f2;
  border: 2px solid #fecaca;
  border-radius: 0.75rem;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

@media (min-width: 640px) {
  .error-message {
    padding: 1rem 1.25rem;
    font-size: 1rem;
  }
}

.error-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.error-message p {
  color: #dc2626;
  margin: 0;
  font-weight: 500;
}

.info-message {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem;
  background: #f0f9ff;
  border: 2px solid #bae6fd;
  border-radius: 0.75rem;
  font-size: 0.875rem;
}

@media (min-width: 640px) {
  .info-message {
    padding: 1.5rem;
    font-size: 1rem;
  }
}

.info-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.info-title {
  font-weight: 600;
  color: #0c4a6e;
  margin: 0 0 0.25rem 0;
}

.info-text {
  color: #075985;
  margin: 0;
  font-size: 0.875rem;
}

@media (min-width: 640px) {
  .info-text {
    font-size: 0.9rem;
  }
}

/* Schedule Content */
.schedule-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Image Content */
.image-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

@media (min-width: 640px) {
  .image-content {
    gap: 2rem;
  }
}

.upload-section,
.gallery-section {
  padding: 1.5rem;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 1rem;
  border: 2px solid #bae6fd;
}

@media (min-width: 640px) {
  .upload-section,
  .gallery-section {
    padding: 2rem 2.5rem;
  }
}

.subsection-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #0c4a6e;
  margin: 0 0 1rem 0;
}

@media (min-width: 640px) {
  .subsection-title {
    font-size: 1.25rem;
  }
}

.empty-state {
  text-align: center;
  padding: 2rem 1rem;
  background: #f8fafc;
  border: 2px dashed #cbd5e1;
  border-radius: 1rem;
}

@media (min-width: 640px) {
  .empty-state {
    padding: 3rem 1.5rem;
  }
}

.empty-title {
  color: #64748b;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
  font-size: 0.875rem;
}

@media (min-width: 640px) {
  .empty-title {
    font-size: 1rem;
  }
}

.empty-text {
  color: #94a3b8;
  font-size: 0.8rem;
  margin: 0;
}

@media (min-width: 640px) {
  .empty-text {
    font-size: 0.9rem;
  }
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #64748b;
}

@media (min-width: 640px) {
  .loading-state {
    padding: 4rem 1rem;
  }
}

.spinner {
  width: 2.5rem;
  height: 2.5rem;
  margin: 0 auto 1rem;
  border: 4px solid #e0f2fe;
  border-top-color: #06b6d4;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@media (min-width: 640px) {
  .spinner {
    width: 3rem;
    height: 3rem;
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>

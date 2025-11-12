<template>
  <div class="questions-page">
    <h1 class="page-title">Q&A</h1>
    
    <!-- 質問投稿フォーム -->
    <div class="question-form-card">
      <h2>質問を投稿</h2>
      <form @submit.prevent="submitQuestion">
        <div class="form-group">
          <label for="questionContent">質問内容</label>
          <textarea
            id="questionContent"
            v-model="newQuestion.content"
            placeholder="質問を入力してください..."
            rows="4"
            required
          ></textarea>
        </div>
        <div class="form-group checkbox-group">
          <label>
            <input type="checkbox" v-model="newQuestion.is_anonymous" />
            匿名で投稿
          </label>
        </div>
        <button type="submit" class="btn btn-primary" :disabled="submitting">
          {{ submitting ? '投稿中...' : '質問を投稿' }}
        </button>
      </form>
    </div>

    <!-- 質問一覧 -->
    <div class="questions-list">
      <div v-if="loading" class="loading">読み込み中...</div>
      <div v-else-if="questions.length === 0" class="no-questions">
        まだ質問がありません
      </div>
      <div v-else>
        <div
          v-for="question in questions"
          :key="question.id"
          class="question-card"
        >
          <!-- 質問内容 -->
          <div class="question-header">
            <div class="question-meta">
              <span class="author">{{ question.author_name }}</span>
              <span class="date">{{ formatDate(question.created_at) }}</span>
            </div>
            <button
              v-if="canDeleteQuestion(question)"
              @click="deleteQuestion(question.id)"
              class="btn-delete"
              title="削除"
            >
              ×
            </button>
          </div>
          <div class="question-content">{{ question.content }}</div>

          <!-- 回答一覧 -->
          <div v-if="question.answers && question.answers.length > 0" class="answers-section">
            <h3>回答</h3>
            <div
              v-for="answer in question.answers"
              :key="answer.id"
              class="answer-card"
            >
              <div class="answer-meta">
                <span class="author admin">{{ answer.author_name }}</span>
                <span class="date">{{ formatDate(answer.created_at) }}</span>
              </div>
              <div class="answer-content">{{ answer.content }}</div>
            </div>
          </div>

          <!-- 回答フォーム (管理者のみ) -->
          <div v-if="isAdmin" class="answer-form">
            <h3>回答を投稿</h3>
            <form @submit.prevent="submitAnswer(question.id)">
              <div class="form-group">
                <textarea
                  v-model="answerForms[question.id]"
                  placeholder="回答を入力してください..."
                  rows="3"
                  required
                ></textarea>
              </div>
              <div class="form-group checkbox-group">
                <label>
                  <input
                    type="checkbox"
                    v-model="answerAnonymous[question.id]"
                  />
                  匿名で投稿
                </label>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">
                回答を投稿
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useAuthStore } from '../stores/authStore';
import axios from 'axios';

const authStore = useAuthStore();
const questions = ref([]);
const loading = ref(true);
const submitting = ref(false);
const newQuestion = ref({
  content: '',
  is_anonymous: false
});

const answerForms = ref({});
const answerAnonymous = ref({});

const isAdmin = computed(() => authStore.user?.is_admin === true || authStore.user?.role === 'admin');

// 質問一覧を取得
const fetchQuestions = async () => {
  try {
    const response = await axios.get('/api/questions');
    questions.value = response.data;
    loading.value = false;
  } catch (error) {
    console.error('質問の取得に失敗しました:', error);
    loading.value = false;
  }
};

// 質問を投稿
const submitQuestion = async () => {
  if (!newQuestion.value.content.trim()) return;
  
  submitting.value = true;
  try {
    await axios.post('/api/questions', {
      content: newQuestion.value.content,
      is_anonymous: newQuestion.value.is_anonymous
    });
    
    // フォームをリセット
    newQuestion.value = {
      content: '',
      is_anonymous: false
    };
    
    // 質問一覧を更新
    await fetchQuestions();
  } catch (error) {
    console.error('質問の投稿に失敗しました:', error);
    alert('質問の投稿に失敗しました');
  } finally {
    submitting.value = false;
  }
};

// 回答を投稿
const submitAnswer = async (questionId) => {
  const content = answerForms.value[questionId];
  if (!content || !content.trim()) return;
  
  try {
    await axios.post(`/api/questions/${questionId}/answers`, {
      content: content,
      is_anonymous: answerAnonymous.value[questionId] || false
    });
    
    // フォームをリセット
    answerForms.value[questionId] = '';
    answerAnonymous.value[questionId] = false;
    
    // 質問一覧を更新
    await fetchQuestions();
  } catch (error) {
    console.error('回答の投稿に失敗しました:', error);
    alert('回答の投稿に失敗しました');
  }
};

// 質問を削除
const deleteQuestion = async (questionId) => {
  if (!confirm('この質問を削除しますか?')) return;
  
  try {
    await axios.delete(`/api/questions/${questionId}`);
    await fetchQuestions();
  } catch (error) {
    console.error('質問の削除に失敗しました:', error);
    alert('質問の削除に失敗しました');
  }
};

// 質問を削除できるか判定
const canDeleteQuestion = (question) => {
  if (!authStore.isAuthenticated) return false;
  if (isAdmin.value) return true;
  return question.user_id === authStore.user?.id;
};

// 日付フォーマット
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

// ポーリング設定
let pollingInterval = null;

onMounted(() => {
  fetchQuestions();
  // 5秒ごとに質問一覧を更新
  pollingInterval = setInterval(fetchQuestions, 5000);
});

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});
</script>

<style scoped>
.questions-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
}

.page-title {
  font-size: 2rem;
  margin-bottom: 30px;
  color: #333;
}

.question-form-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.question-form-card h2 {
  font-size: 1.3rem;
  margin-bottom: 20px;
  color: #333;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #555;
}

.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
  transition: border-color 0.3s;
}

.form-group textarea:focus {
  outline: none;
  border-color: #4CAF50;
}

.checkbox-group label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: normal;
  cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background: #4CAF50;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #45a049;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 0.9rem;
}

.questions-list {
  margin-top: 30px;
}

.loading,
.no-questions {
  text-align: center;
  padding: 40px;
  color: #999;
  font-size: 1.1rem;
}

.question-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.question-meta,
.answer-meta {
  display: flex;
  gap: 12px;
  font-size: 0.9rem;
  color: #666;
}

.author {
  font-weight: 600;
  color: #333;
}

.author.admin {
  color: #4CAF50;
}

.date {
  color: #999;
}

.btn-delete {
  background: #f44336;
  color: white;
  border: none;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  font-size: 1.3rem;
  line-height: 1;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-delete:hover {
  background: #d32f2f;
}

.question-content {
  font-size: 1.05rem;
  line-height: 1.6;
  color: #333;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.answers-section {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.answers-section h3 {
  font-size: 1.1rem;
  margin-bottom: 16px;
  color: #4CAF50;
}

.answer-card {
  background: #f9f9f9;
  border-left: 3px solid #4CAF50;
  padding: 16px;
  border-radius: 6px;
  margin-bottom: 12px;
}

.answer-content {
  font-size: 1rem;
  line-height: 1.6;
  color: #333;
  white-space: pre-wrap;
  word-wrap: break-word;
  margin-top: 8px;
}

.answer-form {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.answer-form h3 {
  font-size: 1.1rem;
  margin-bottom: 16px;
  color: #333;
}

@media (max-width: 768px) {
  .questions-page {
    padding: 15px;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .question-form-card,
  .question-card {
    padding: 16px;
  }
}
</style>

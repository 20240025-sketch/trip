<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold mb-3 text-gray-900">
          🚌🏠 部屋割・バス
        </h1>
        <p class="text-gray-600">参加者の部屋割とバス座席の管理</p>
      </div>

      <!-- Add Button -->
      <div class="mb-6" v-if="isAdmin">
        <button 
          @click="addParticipant"
          class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2"
        >
          <span>➕</span>
          <span>参加者を追加</span>
        </button>
      </div>

      <!-- User Info Notice -->
      <div v-if="!isAdmin && participants.length > 0" class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <p class="text-blue-800 flex items-center gap-2">
          <span>ℹ️</span>
          <span>閲覧モード：情報の編集はできません</span>
        </p>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gradient-to-r from-blue-50 to-cyan-50 border-b-2 border-blue-200">
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">メールアドレス</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">名前</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">クラス</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">部屋割</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">座席</th>
                <th v-if="isAdmin" class="px-6 py-4 text-center text-sm font-semibold text-gray-700">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="(participant, index) in participants" 
                :key="index"
                class="border-b border-gray-100 hover:bg-gray-50 transition-colors"
                :class="{ 'bg-gray-50/50': index % 2 === 1 }"
              >
                <!-- Email -->
                <td class="px-6 py-4">
                  <input 
                    v-if="isAdmin"
                    v-model="participant.email"
                    type="email"
                    placeholder="メールアドレス"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <div v-else class="text-sm text-gray-600 font-medium">
                    {{ participant.email }}
                  </div>
                </td>
                
                <!-- Name -->
                <td class="px-6 py-4">
                  <input 
                    v-model="participant.name"
                    type="text"
                    placeholder="名前"
                    :disabled="!isAdmin"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-2"
                    :class="{ 'bg-gray-100 cursor-not-allowed': !isAdmin }"
                  />
                  <input 
                    v-model="participant.furigana"
                    type="text"
                    placeholder="ふりがな"
                    :disabled="!isAdmin"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs text-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :class="{ 'bg-gray-100 cursor-not-allowed': !isAdmin }"
                  />
                </td>
                
                <!-- Class -->
                <td class="px-6 py-4">
                  <input 
                    v-model="participant.class_name"
                    type="text"
                    placeholder="1年A組"
                    :disabled="!isAdmin"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :class="{ 'bg-gray-100 cursor-not-allowed': !isAdmin }"
                  />
                </td>
                
                <!-- Room Assignment -->
                <td class="px-6 py-4">
                  <div class="space-y-2">
                    <input 
                      v-model="participant.room_day1"
                      type="text"
                      placeholder="1日目: 201号室"
                      :disabled="!isAdmin"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      :class="{ 'bg-gray-100 cursor-not-allowed': !isAdmin }"
                    />
                    <input 
                      v-model="participant.room_day2"
                      type="text"
                      placeholder="2日目: 202号室"
                      :disabled="!isAdmin"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      :class="{ 'bg-gray-100 cursor-not-allowed': !isAdmin }"
                    />
                    <input 
                      v-model="participant.room_day3"
                      type="text"
                      placeholder="3日目: 203号室"
                      :disabled="!isAdmin"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      :class="{ 'bg-gray-100 cursor-not-allowed': !isAdmin }"
                    />
                  </div>
                </td>
                
                <!-- Bus Seat -->
                <td class="px-6 py-4">
                  <input 
                    v-model="participant.seat"
                    type="text"
                    placeholder="1号車 3列目"
                    :disabled="!isAdmin"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :class="{ 'bg-gray-100 cursor-not-allowed': !isAdmin }"
                  />
                </td>
                
                <!-- Actions -->
                <td v-if="isAdmin" class="px-6 py-4 text-center">
                  <button 
                    @click="removeParticipant(index)"
                    class="px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-md transition-colors text-sm font-medium"
                  >
                    削除
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="participants.length === 0" class="text-center py-16">
        <div class="text-6xl mb-4">📝</div>
        <p class="text-gray-500 text-xl">{{ isAdmin ? '参加者を追加してください' : 'あなたの情報が登録されていません' }}</p>
        <p class="text-gray-400 mt-2">{{ isAdmin ? '「参加者を追加」ボタンをクリックして開始' : '管理者による登録をお待ちください' }}</p>
      </div>

      <!-- Save Button -->
      <div v-if="participants.length > 0 && isAdmin" class="mt-6 flex justify-end">
        <button 
          @click="saveAll"
          class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors font-medium"
        >
          すべて保存
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/authStore';

const authStore = useAuthStore();
const allParticipants = ref([]);

// 管理者は全員、一般ユーザーは自分の情報のみ表示
const participants = computed(() => {
  // 管理者は全て表示
  if (authStore.user && authStore.user.is_admin) {
    return allParticipants.value;
  }
  
  // 一般ユーザーは自分のメールアドレスと一致するもののみ
  if (!authStore.user || !authStore.user.email) {
    return [];
  }
  return allParticipants.value.filter(
    p => p.email && p.email.toLowerCase() === authStore.user.email.toLowerCase()
  );
});

// 管理者かどうか
const isAdmin = computed(() => authStore.user && authStore.user.is_admin);

const addParticipant = () => {
  if (!authStore.user) {
    alert('ログインしてください');
    return;
  }
  
  // 管理者は自由に追加可能
  if (isAdmin.value) {
    allParticipants.value.push({
      email: '',
      name: '',
      furigana: '',
      class_name: '',
      room_day1: '',
      room_day2: '',
      room_day3: '',
      seat: ''
    });
    return;
  }
  
  // 一般ユーザーは自分の情報のみ追加可能
  const exists = allParticipants.value.some(
    p => p.email && p.email.toLowerCase() === authStore.user.email.toLowerCase()
  );
  
  if (exists) {
    alert('既に登録されています');
    return;
  }
  
  allParticipants.value.push({
    email: authStore.user.email,
    name: authStore.user.name || '',
    furigana: '',
    class_name: '',
    room_day1: '',
    room_day2: '',
    room_day3: '',
    seat: ''
  });
};

const removeParticipant = (index) => {
  const participant = participants.value[index];
  const allIndex = allParticipants.value.findIndex(p => p === participant);
  if (allIndex !== -1) {
    allParticipants.value.splice(allIndex, 1);
  }
};

const saveAll = () => {
  // ローカルストレージに保存
  localStorage.setItem('assignmentTableData', JSON.stringify(allParticipants.value));
  alert('保存しました！');
};

// ページ読み込み時にローカルストレージからデータを復元
const loadData = () => {
  const saved = localStorage.getItem('assignmentTableData');
  if (saved) {
    try {
      allParticipants.value = JSON.parse(saved);
    } catch (error) {
      console.error('Failed to load data:', error);
    }
  }
};

// 初期化
loadData();
</script>

<style scoped>
table {
  border-collapse: separate;
  border-spacing: 0;
}

thead th:first-child {
  border-top-left-radius: 0;
}

thead th:last-child {
  border-top-right-radius: 0;
}

tbody tr:last-child td:first-child {
  border-bottom-left-radius: 0.75rem;
}

tbody tr:last-child td:last-child {
  border-bottom-right-radius: 0.75rem;
}

/* Canva風のクリーンなデザイン */
th {
  font-weight: 600;
  letter-spacing: 0.025em;
  text-transform: none;
}

td {
  vertical-align: top;
}

/* ホバー効果 */
tbody tr {
  transition: all 0.2s ease;
}

tbody tr:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}
</style>

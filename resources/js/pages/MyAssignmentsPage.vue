<template>
  <div class="min-h-screen bg-gradient-to-br from-white via-cyan-50 to-blue-50 py-6 sm:py-8">
    <div class="container mx-auto px-4 sm:px-6">
      <div class="mb-8">
        <h1 class="text-3xl sm:text-4xl font-black bg-gradient-to-r from-cyan-500 to-blue-500 bg-clip-text text-transparent mb-2">
          {{ isAdmin ? '部屋割・バス座席管理' : '部屋割・バス座席' }} 🏨🚌
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">
          {{ isAdmin ? '全ユーザーの部屋割とバス座席を管理' : 'あなたの部屋割とバス座席情報' }}
        </p>
      </div>

      <!-- Admin: Import Section -->
      <div v-if="isAdmin" class="mb-8 space-y-4">
        <div class="bg-white rounded-xl p-6 shadow-lg border-2 border-cyan-100">
          <h2 class="text-xl font-bold text-gray-800 mb-4">📥 CSVインポート</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- User Import -->
            <div class="border-2 border-gray-200 rounded-lg p-4">
              <h3 class="font-bold text-gray-700 mb-3">ユーザー一括インポート</h3>
              <p class="text-xs text-gray-600 mb-3">
                ユーザー情報と部屋割・バス座席を同時にインポート可能<br>
                <span class="font-mono text-xs">name, email, password, role, room_day1-3, bus_number</span>
              </p>
              <div class="space-y-2">
                <button 
                  @click="downloadUserTemplate"
                  class="block w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-center text-sm"
                >
                  📄 テンプレートダウンロード
                </button>
                <input 
                  type="file" 
                  @change="handleUserImport" 
                  accept=".csv"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100"
                />
              </div>
            </div>

            <!-- Assignment Import -->
            <div class="border-2 border-gray-200 rounded-lg p-4">
              <h3 class="font-bold text-gray-700 mb-3">部屋割・バス座席更新</h3>
              <p class="text-xs text-gray-600 mb-3">
                既存ユーザーの部屋割・バス座席のみを更新<br>
                <span class="font-mono text-xs">email, room_day1, room_day2, room_day3, bus_number</span>
              </p>
              <div class="space-y-2">
                <button 
                  @click="downloadAssignmentTemplate"
                  class="block w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-center text-sm"
                >
                  📄 テンプレートダウンロード
                </button>
                <input 
                  type="file" 
                  @change="handleAssignmentImport" 
                  accept=".csv"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Current User Info -->
      <div class="bg-gradient-to-r from-cyan-400 to-blue-400 rounded-xl p-6 shadow-xl mb-8 text-white">
        <h2 class="text-2xl font-bold mb-4">
          {{ isAdmin ? '🔑 あなたの情報（管理者）' : '👤 あなたの情報' }}
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="bg-white/20 rounded-lg p-4 backdrop-blur-sm">
            <div class="text-sm opacity-90 mb-1">📧 メール</div>
            <div class="font-bold">{{ currentUser?.email }}</div>
          </div>
          <div class="bg-white/20 rounded-lg p-4 backdrop-blur-sm">
            <div class="text-sm opacity-90 mb-1">👤 名前</div>
            <div class="font-bold">{{ currentUser?.name }}</div>
          </div>
          <div class="bg-white/20 rounded-lg p-4 backdrop-blur-sm">
            <div class="text-sm opacity-90 mb-1">🏠 1泊目</div>
            <div class="font-bold">{{ formatAsInteger(currentUser?.room_day1) || '未設定' }}</div>
          </div>
          <div class="bg-white/20 rounded-lg p-4 backdrop-blur-sm">
            <div class="text-sm opacity-90 mb-1">🏠 2泊目</div>
            <div class="font-bold">{{ formatAsInteger(currentUser?.room_day2) || '未設定' }}</div>
          </div>
          <div class="bg-white/20 rounded-lg p-4 backdrop-blur-sm">
            <div class="text-sm opacity-90 mb-1">🏠 3泊目</div>
            <div class="font-bold">{{ formatAsInteger(currentUser?.room_day3) || '未設定' }}</div>
          </div>
          <div class="bg-white/20 rounded-lg p-4 backdrop-blur-sm">
            <div class="text-sm opacity-90 mb-1">🚌 バス</div>
            <div class="font-bold">{{ currentUser?.bus_number || '未設定' }}</div>
          </div>
        </div>
      </div>

      <!-- Admin: All Users List -->
      <div v-if="isAdmin && allUsers.length > 0" class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-cyan-100">
        <div class="px-6 py-4 bg-gradient-to-r from-cyan-50 to-blue-50 border-b-2 border-cyan-100">
          <h2 class="text-xl font-bold text-gray-800">👥 全ユーザー一覧</h2>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">メール</th>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">名前</th>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">1泊目</th>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">2泊目</th>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">3泊目</th>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">バス</th>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="user in allUsers" 
                :key="user.id"
                :class="user.id === currentUser?.id ? 'bg-cyan-50' : editingUser?.id === user.id ? 'bg-yellow-50' : 'hover:bg-gray-50'"
                class="border-b border-gray-100"
              >
                <td class="px-4 py-3 text-sm">
                  <span class="flex items-center gap-1">
                    {{ user.email }}
                    <span v-if="user.role === 'admin'" class="text-xs bg-red-500 text-white px-2 py-0.5 rounded-full">管理者</span>
                  </span>
                </td>
                <td class="px-4 py-3 text-sm font-medium">{{ user.name }}</td>
                <td class="px-4 py-3">
                  <input 
                    v-if="editingUser?.id === user.id"
                    v-model="editingUser.room_day1"
                    type="text"
                    class="w-full px-2 py-1 border rounded text-sm"
                    placeholder="101"
                  />
                  <span v-else class="text-sm">{{ formatAsInteger(user.room_day1) || '-' }}</span>
                </td>
                <td class="px-4 py-3">
                  <input 
                    v-if="editingUser?.id === user.id"
                    v-model="editingUser.room_day2"
                    type="text"
                    class="w-full px-2 py-1 border rounded text-sm"
                    placeholder="202"
                  />
                  <span v-else class="text-sm">{{ formatAsInteger(user.room_day2) || '-' }}</span>
                </td>
                <td class="px-4 py-3">
                  <input 
                    v-if="editingUser?.id === user.id"
                    v-model="editingUser.room_day3"
                    type="text"
                    class="w-full px-2 py-1 border rounded text-sm"
                    placeholder="303"
                  />
                  <span v-else class="text-sm">{{ formatAsInteger(user.room_day3) || '-' }}</span>
                </td>
                <td class="px-4 py-3">
                  <input 
                    v-if="editingUser?.id === user.id"
                    v-model="editingUser.bus_number"
                    type="text"
                    class="w-full px-2 py-1 border rounded text-sm"
                    placeholder="1号車"
                  />
                  <span v-else class="text-sm">{{ user.bus_number || '-' }}</span>
                </td>
                <td class="px-4 py-3">
                  <div v-if="editingUser?.id === user.id" class="flex gap-1">
                    <button 
                      @click="saveUser"
                      class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600"
                    >
                      保存
                    </button>
                    <button 
                      @click="cancelEdit"
                      class="px-3 py-1 bg-gray-500 text-white rounded text-sm hover:bg-gray-600"
                    >
                      キャンセル
                    </button>
                  </div>
                  <button 
                    v-else
                    @click="editUser(user)"
                    class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600"
                  >
                    編集
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Non-admin: No assignments message -->
      <div v-if="!isAdmin && !hasAssignments" class="bg-white rounded-xl p-8 shadow-lg text-center border-2 border-cyan-100">
        <div class="text-6xl mb-4">📋</div>
        <p class="text-gray-600 text-lg">まだ部屋割・バス座席が設定されていません</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/authStore';

const authStore = useAuthStore();
const currentUser = ref(null);
const allUsers = ref([]);
const editingUser = ref(null);

const isAdmin = computed(() => authStore.user?.role === 'admin');
const hasAssignments = computed(() => {
  return currentUser.value?.room_day1 || currentUser.value?.room_day2 || 
         currentUser.value?.room_day3 || currentUser.value?.bus_number;
});

// Format value as integer (remove decimal points)
const formatAsInteger = (value) => {
  if (!value) return null;
  const parsed = parseFloat(value);
  return isNaN(parsed) ? value : Math.floor(parsed).toString();
};

const loadCurrentUser = async () => {
  try {
    const response = await axios.get('/api/assignments');
    currentUser.value = response.data;
  } catch (error) {
    console.error('Failed to load user assignments:', error);
  }
};

const loadAllUsers = async () => {
  if (!isAdmin.value) return;
  
  try {
    const response = await axios.get('/api/assignments/all');
    allUsers.value = response.data;
  } catch (error) {
    console.error('Failed to load all users:', error);
  }
};

const downloadUserTemplate = async () => {
  try {
    const response = await axios.get('/api/admin/users/import/template', {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'user_import_template.csv');
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Failed to download template:', error);
    alert('テンプレートのダウンロードに失敗しました');
  }
};

const downloadAssignmentTemplate = async () => {
  try {
    const response = await axios.get('/api/admin/assignments/import/template', {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'assignment_import_template.csv');
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Failed to download template:', error);
    alert('テンプレートのダウンロードに失敗しました');
  }
};

const handleUserImport = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);

  try {
    const response = await axios.post('/api/admin/users/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    alert(response.data.message);
    if (response.data.errors.length > 0) {
      console.error('Import errors:', response.data.errors);
      alert('エラー:\n' + response.data.errors.join('\n'));
    }
    
    await loadAllUsers();
  } catch (error) {
    console.error('User import failed:', error);
    alert('インポートに失敗しました');
  }
  
  event.target.value = '';
};

const handleAssignmentImport = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);

  try {
    const response = await axios.post('/api/admin/assignments/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    alert(response.data.message);
    if (response.data.errors.length > 0) {
      console.error('Import errors:', response.data.errors);
      alert('エラー:\n' + response.data.errors.join('\n'));
    }
    
    await loadCurrentUser();
    await loadAllUsers();
  } catch (error) {
    console.error('Assignment import failed:', error);
    alert('インポートに失敗しました');
  }
  
  event.target.value = '';
};

const editUser = (user) => {
  editingUser.value = { ...user };
};

const cancelEdit = () => {
  editingUser.value = null;
};

const saveUser = async () => {
  if (!editingUser.value) return;

  try {
    const response = await axios.put(`/api/assignments/${editingUser.value.id}`, {
      room_day1: editingUser.value.room_day1,
      room_day2: editingUser.value.room_day2,
      room_day3: editingUser.value.room_day3,
      bus_number: editingUser.value.bus_number,
    });

    // Update local data
    const index = allUsers.value.findIndex(u => u.id === editingUser.value.id);
    if (index !== -1) {
      allUsers.value[index] = response.data.user;
    }
    
    if (editingUser.value.id === currentUser.value?.id) {
      await loadCurrentUser();
    }

    editingUser.value = null;
    alert('更新しました');
  } catch (error) {
    console.error('Failed to update user:', error);
    alert('更新に失敗しました');
  }
};

onMounted(async () => {
  await loadCurrentUser();
  await loadAllUsers();
});
</script>

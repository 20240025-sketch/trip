<template>
  <div class="participant-assignment-manager">
    <div class="header">
      <h2>📋 参加者管理</h2>
      <button @click="addParticipant" class="btn btn-add">
        <i class="fas fa-plus"></i> 参加者を追加
      </button>
    </div>

    <div v-if="loading" class="loading">
      <i class="fas fa-spinner fa-spin"></i> 読み込み中...
    </div>

    <div v-else-if="participants.length > 0" class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th class="th-id">学籍番号</th>
            <th class="th-name">名前</th>
            <th class="th-class">学年・クラス</th>
            <th class="th-email">メールアドレス</th>
            <th class="th-bus">バス座席</th>
            <th class="th-room">部屋割</th>
            <th class="th-actions">操作</th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="(participant, index) in participants" 
            :key="participant.id || `new-${index}`"
          >
            <td class="td-id">
              <input 
                v-model="participant.contact" 
                type="text" 
                class="input-field input-id"
                placeholder="20230001"
              />
            </td>
            <td class="td-name">
              <div class="name-container">
                <input 
                  v-model="participant.name" 
                  type="text" 
                  class="input-field input-name"
                  placeholder="田中　太郎"
                  required
                />
                <input 
                  v-model="participant.name_furigana" 
                  type="text" 
                  class="input-field input-furigana"
                  placeholder="(たなか　たろう)"
                />
              </div>
            </td>
            <td class="td-class">
              <input 
                v-model="participant.class_name" 
                type="text" 
                class="input-field"
                placeholder="3年 3特進"
              />
            </td>
            <td class="td-email">
              <input 
                v-model="participant.email" 
                type="email" 
                class="input-field"
                placeholder="20230001@seiei.ac.jp"
              />
            </td>
            <td class="td-bus">
              <div class="assignment-inline">
                <div 
                  v-for="(bus, busIndex) in participant.bus_assignments" 
                  :key="busIndex"
                  class="assignment-inline-item"
                >
                  <div class="inline-row">
                    <select v-model="bus.day_id" class="input-mini" title="日程">
                      <option value="">日程</option>
                      <option v-for="day in days" :key="day.id" :value="day.id">
                        {{ day.day_number }}日目
                      </option>
                    </select>
                    <input 
                      v-model="bus.bus_number" 
                      type="text" 
                      class="input-mini"
                      placeholder="1号車"
                      title="何号車"
                    />
                    <input 
                      v-model="bus.row_number" 
                      type="text" 
                      class="input-mini"
                      placeholder="2列目"
                      title="何列目"
                    />
                    <button 
                      @click="removeBusAssignment(index, busIndex)"
                      class="btn-mini-delete"
                      title="削除"
                    >
                      ×
                    </button>
                  </div>
                </div>
                <button 
                  @click="addBusAssignment(index)" 
                  class="btn-add-inline"
                >
                  + 追加
                </button>
              </div>
            </td>
            <td class="td-room">
              <div class="assignment-inline">
                <div 
                  v-for="(room, roomIndex) in participant.room_assignments" 
                  :key="roomIndex"
                  class="assignment-inline-item"
                >
                  <div class="inline-row">
                    <select v-model="room.day_id" class="input-mini" title="日程">
                      <option value="">日程</option>
                      <option v-for="day in days" :key="day.id" :value="day.id">
                        {{ day.day_number }}日目
                      </option>
                    </select>
                    <input 
                      v-model="room.room_number" 
                      type="text" 
                      class="input-mini"
                      placeholder="101号室"
                      title="何号室"
                    />
                    <button 
                      @click="removeRoomAssignment(index, roomIndex)"
                      class="btn-mini-delete"
                      title="削除"
                    >
                      ×
                    </button>
                  </div>
                </div>
                <button 
                  @click="addRoomAssignment(index)" 
                  class="btn-add-inline"
                >
                  + 追加
                </button>
              </div>
            </td>
            <td class="td-actions text-center">
              <button 
                @click="removeParticipant(index)" 
                class="btn-delete"
                title="削除"
              >
                削除
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="participants.length === 0 && !loading" class="no-data">
      <div class="no-data-icon">📝</div>
      <p>参加者が登録されていません</p>
      <button @click="addParticipant" class="btn btn-add">
        <i class="fas fa-plus"></i> 最初の参加者を追加
      </button>
    </div>

    <div v-if="participants.length > 0" class="footer-actions">
      <button @click="saveAll" class="btn btn-save" :disabled="saving">
        <i class="fas fa-save"></i> {{ saving ? '保存中...' : 'すべて保存' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  planId: {
    type: [String, Number],
    required: true
  }
});

const emit = defineEmits(['saved']);

const participants = ref([]);
const days = ref([]);
const loading = ref(false);
const saving = ref(false);

const loadData = async () => {
  loading.value = true;
  try {
    const daysResponse = await axios.get(`/api/plans/${props.planId}/days`);
    days.value = daysResponse.data.data || daysResponse.data;

    const participantsResponse = await axios.get(`/api/plans/${props.planId}/participant-assignments`);
    participants.value = participantsResponse.data.data || participantsResponse.data;

    participants.value.forEach(p => {
      if (!p.bus_assignments) p.bus_assignments = [];
      if (!p.room_assignments) p.room_assignments = [];
    });
  } catch (error) {
    console.error('データの読み込みに失敗:', error);
    alert('データの読み込みに失敗しました。');
  } finally {
    loading.value = false;
  }
};

const addParticipant = () => {
  participants.value.push({
    name: '',
    name_furigana: '',
    email: '',
    class_name: '',
    contact: '',
    bus_assignments: [],
    room_assignments: []
  });
};

const removeParticipant = (index) => {
  if (confirm('この参加者を削除しますか?')) {
    participants.value.splice(index, 1);
  }
};

const addBusAssignment = (participantIndex) => {
  if (!participants.value[participantIndex].bus_assignments) {
    participants.value[participantIndex].bus_assignments = [];
  }
  participants.value[participantIndex].bus_assignments.push({
    day_id: '',
    bus_number: '',
    row_number: ''
  });
};

const removeBusAssignment = (participantIndex, busIndex) => {
  participants.value[participantIndex].bus_assignments.splice(busIndex, 1);
};

const addRoomAssignment = (participantIndex) => {
  if (!participants.value[participantIndex].room_assignments) {
    participants.value[participantIndex].room_assignments = [];
  }
  participants.value[participantIndex].room_assignments.push({
    day_id: '',
    room_number: ''
  });
};

const removeRoomAssignment = (participantIndex, roomIndex) => {
  participants.value[participantIndex].room_assignments.splice(roomIndex, 1);
};

const saveAll = async () => {
  saving.value = true;
  try {
    const newParticipants = participants.value.filter(p => !p.id);
    const existingParticipants = participants.value.filter(p => p.id);

    if (newParticipants.length > 0) {
      await axios.post(`/api/plans/${props.planId}/participant-assignments/bulk`, {
        participants: newParticipants
      });
    }

    for (const participant of existingParticipants) {
      await axios.put(`/api/participant-assignments/${participant.id}`, participant);
    }

    alert('保存しました。');
    emit('saved');
    await loadData();
  } catch (error) {
    console.error('保存に失敗:', error);
    alert('保存に失敗しました: ' + (error.response?.data?.message || error.message));
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.participant-assignment-manager {
  padding: 0;
  background: #f5f6fa;
  min-height: 100vh;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 32px;
  background: white;
  border-bottom: 1px solid #e1e4e8;
  margin-bottom: 24px;
}

.header h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #24292e;
}

.loading {
  text-align: center;
  padding: 80px 20px;
  font-size: 18px;
  color: #6a737d;
  background: white;
  margin: 0 32px;
  border-radius: 6px;
}

.loading i {
  font-size: 32px;
  display: block;
  margin-bottom: 16px;
}

.table-wrapper {
  margin: 0 32px 24px;
  background: white;
  border: 1px solid #e1e4e8;
  border-radius: 6px;
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  min-width: 1000px;
}

.data-table thead {
  background: #f6f8fa;
  border-bottom: 1px solid #e1e4e8;
}

.data-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #24292e;
  border-bottom: 1px solid #e1e4e8;
  white-space: nowrap;
}

.data-table tbody tr {
  border-bottom: 1px solid #e1e4e8;
  transition: background-color 0.1s;
}

.data-table tbody tr:hover {
  background-color: #f6f8fa;
}

.data-table tbody tr:last-child {
  border-bottom: none;
}

.data-table td {
  padding: 12px 16px;
  vertical-align: middle;
}

.th-id, .td-id { width: 130px; }
.th-name, .td-name { width: 220px; }
.th-class, .td-class { width: 140px; }
.th-email, .td-email { width: 220px; }
.th-bus, .td-bus { width: 280px; }
.th-room, .td-room { width: 220px; }
.th-actions, .td-actions { width: 90px; }

.text-center {
  text-align: center !important;
}

.input-field {
  width: 100%;
  padding: 7px 10px;
  border: 1px solid #e1e4e8;
  border-radius: 4px;
  font-size: 14px;
  color: #24292e;
  background: white;
  transition: border-color 0.2s;
}

.input-field:focus {
  outline: none;
  border-color: #0366d6;
  box-shadow: 0 0 0 3px rgba(3, 102, 214, 0.1);
}

.input-field::placeholder {
  color: #959da5;
}

.input-id {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.name-container {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.input-name {
  font-weight: 500;
  font-size: 14px;
}

.input-furigana {
  font-size: 12px;
  color: #6a737d;
  padding: 4px 10px;
}

/* インライン割り当て */
.assignment-inline {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 4px 0;
}

.assignment-inline-item {
  background: #f6f8fa;
  border: 1px solid #e1e4e8;
  border-radius: 4px;
  padding: 6px;
}

.inline-row {
  display: flex;
  gap: 4px;
  align-items: center;
}

.input-mini {
  padding: 4px 6px;
  border: 1px solid #e1e4e8;
  border-radius: 3px;
  font-size: 12px;
  color: #24292e;
  background: white;
  transition: border-color 0.2s;
  flex: 1;
  min-width: 0;
}

.input-mini:focus {
  outline: none;
  border-color: #0366d6;
  box-shadow: 0 0 0 2px rgba(3, 102, 214, 0.1);
}

.input-mini::placeholder {
  color: #959da5;
  font-size: 11px;
}

.btn-mini-delete {
  padding: 2px 6px;
  background: white;
  color: #cb2431;
  border: 1px solid #e1e4e8;
  border-radius: 3px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  line-height: 1;
  transition: all 0.2s;
  flex-shrink: 0;
}

.btn-mini-delete:hover {
  background: #cb2431;
  color: white;
  border-color: #cb2431;
}

.btn-add-inline {
  padding: 4px 8px;
  background: white;
  color: #28a745;
  border: 1px dashed #28a745;
  border-radius: 4px;
  cursor: pointer;
  font-size: 11px;
  font-weight: 500;
  transition: all 0.2s;
  text-align: center;
}

.btn-add-inline:hover {
  background: #f0fff4;
  border-style: solid;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.btn:active {
  transform: translateY(0);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-add {
  background: #28a745;
  color: white;
}

.btn-add:hover {
  background: #218838;
}

.btn-save {
  background: #0366d6;
  color: white;
  padding: 12px 32px;
  font-size: 16px;
}

.btn-save:hover {
  background: #0256c4;
}

.btn-primary {
  background: #0366d6;
  color: white;
}

.btn-primary:hover {
  background: #0256c4;
}

.btn-edit {
  padding: 5px 12px;
  font-size: 13px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid;
  background: white;
  font-weight: 500;
}

.btn-bus {
  color: #0366d6;
  border-color: #0366d6;
}

.btn-bus:hover {
  background: #0366d6;
  color: white;
}

.btn-room {
  color: #28a745;
  border-color: #28a745;
}

.btn-room:hover {
  background: #28a745;
  color: white;
}

.btn-delete {
  padding: 5px 12px;
  font-size: 13px;
  background: white;
  color: #cb2431;
  border: 1px solid #cb2431;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
}

.btn-delete:hover {
  background: #cb2431;
  color: white;
}

.btn-delete-small {
  padding: 8px 12px;
  background: white;
  color: #cb2431;
  border: 1px solid #cb2431;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.btn-delete-small:hover {
  background: #cb2431;
  color: white;
}

.btn-add-item {
  width: 100%;
  background: white;
  color: #28a745;
  border: 2px dashed #28a745;
  border-radius: 6px;
  padding: 12px;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
  margin-top: 10px;
}

.btn-add-item:hover {
  background: #f0fff4;
  border-style: solid;
}

.no-data {
  text-align: center;
  padding: 80px 32px;
  background: white;
  margin: 0 32px;
  border-radius: 6px;
  border: 1px solid #e1e4e8;
}

.no-data-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.no-data p {
  color: #6a737d;
  font-size: 16px;
  margin-bottom: 24px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(27, 31, 35, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 20px;
}

.modal-dialog {
  background: white;
  border-radius: 8px;
  width: 100%;
  max-width: 720px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e1e4e8;
  background: #f6f8fa;
  border-radius: 8px 8px 0 0;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #24292e;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-modal-close {
  background: none;
  border: none;
  color: #6a737d;
  font-size: 20px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-modal-close:hover {
  background: #e1e4e8;
  color: #24292e;
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.assignment-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.assignment-card {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: #f6f8fa;
  border: 1px solid #e1e4e8;
  border-radius: 6px;
}

.assignment-number {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: white;
  border: 1px solid #e1e4e8;
  border-radius: 50%;
  font-weight: 600;
  color: #6a737d;
  flex-shrink: 0;
  font-size: 14px;
}

.assignment-content {
  flex: 1;
}

.form-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.form-group {
  flex: 1;
  min-width: 150px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  font-size: 13px;
  color: #24292e;
}

.required {
  color: #cb2431;
}

.empty-state {
  text-align: center;
  padding: 48px 24px;
  color: #6a737d;
  background: #f6f8fa;
  border: 2px dashed #e1e4e8;
  border-radius: 6px;
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 12px;
  opacity: 0.5;
  display: block;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #e1e4e8;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  background: #f6f8fa;
  border-radius: 0 0 8px 8px;
}

.footer-actions {
  position: sticky;
  bottom: 0;
  padding: 20px 32px;
  background: white;
  border-top: 1px solid #e1e4e8;
  text-align: center;
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
    padding: 16px;
  }

  .table-wrapper {
    margin: 0 16px 16px;
  }

  .no-data {
    margin: 0 16px;
    padding: 60px 20px;
  }

  .footer-actions {
    padding: 16px;
  }

  .modal-dialog {
    max-width: 100%;
    margin: 0;
  }

  .form-row {
    flex-direction: column;
  }

  .form-group {
    min-width: 100%;
  }
}
</style>

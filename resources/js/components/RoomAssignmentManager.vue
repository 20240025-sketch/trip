<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4">🏠 部屋割</h3>

    <!-- 入力フォーム -->
    <div class="space-y-4 mb-6">
      <div v-for="(assignment, index) in assignments" :key="index" class="p-4 bg-gray-50 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold text-gray-700">部屋 {{ index + 1 }}</h4>
          <button 
            @click="removeAssignment(index)"
            class="text-red-500 hover:text-red-700 text-sm"
          >
            削除
          </button>
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">何日目か *</label>
          <input 
            type="number" 
            v-model.number="assignment.day_number"
            @input="onDayNumberChange(index)"
            min="1"
            placeholder="例: 1"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">参加者</label>
            <input 
              v-model="assignment.participant_name"
              type="text" 
              placeholder="参加者名を入力"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">階数</label>
            <input 
              v-model="assignment.floor" 
              type="text" 
              placeholder="例: 1階"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">部屋番号</label>
            <input 
              v-model="assignment.room_number" 
              type="text" 
              placeholder="例: 101号室"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>
        </div>
        <div class="mt-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">備考</label>
          <input 
            v-model="assignment.notes" 
            type="text" 
            placeholder="メモなど"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
      </div>

      <button 
        @click="addAssignment"
        class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition border-2 border-dashed border-gray-400"
      >
        + 部屋を追加
      </button>
    </div>

    <!-- 保存ボタン -->
    <button 
      @click="saveAllAssignments"
      :disabled="saving"
      class="w-full px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition disabled:bg-gray-300 font-semibold text-lg"
    >
      {{ saving ? '保存中...' : '保存' }}
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  plans: {
    type: Array,
    default: () => []
  }
});

const assignments = ref([]);
const saving = ref(false);

// Fetchで取得したplansを保持
const allPlansCache = ref([]);

const allDays = ref([]);

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric' });
};

const getAllDays = () => {
  const days = [];
  // キャッシュされたplansを使用、なければprops.plansを使用
  const plansToUse = allPlansCache.value.length > 0 ? allPlansCache.value : props.plans;
  plansToUse.forEach(plan => {
    if (plan.days) {
      plan.days.forEach(day => {
        days.push({
          ...day,
          plan_id: plan.id,
          plan_title: plan.title,
          participants: plan.participants
        });
      });
    }
  });
  return days;
};

const findDayByNumber = (dayNumber) => {
  const days = getAllDays();
  return days.find(d => d.day_number === dayNumber);
};

const getDayInfo = (dayId) => {
  const days = getAllDays();
  const day = days.find(d => d.id === dayId);
  if (!day) return '';
  return `${day.plan_title} - ${formatDate(day.date)}`;
};

const getAvailableParticipants = (dayId) => {
  if (!dayId) return [];
  const days = getAllDays();
  const day = days.find(d => d.id === dayId);
  return day?.participants || [];
};

const onDayNumberChange = (index) => {
  const dayNumber = assignments.value[index].day_number;
  if (!dayNumber || dayNumber < 1) {
    assignments.value[index].day_id = null;
    assignments.value[index].participant_id = null;
    return;
  }
  
  const day = findDayByNumber(parseInt(dayNumber));
  if (day) {
    assignments.value[index].day_id = day.id;
    assignments.value[index].plan_id = day.plan_id;
  } else {
    assignments.value[index].day_id = null;
    assignments.value[index].plan_id = null;
    assignments.value[index].participant_id = null;
  }
};

const addAssignment = () => {
  assignments.value.push({
    id: null,
    plan_id: null,
    day_id: null,
    day_number: null,
    participant_id: null,
    participant_name: '',
    floor: '',
    room_number: '',
    notes: ''
  });
};

const removeAssignment = async (index) => {
  const assignment = assignments.value[index];
  
  // 既存データの場合はデータベースからも削除
  if (assignment.id) {
    try {
      await axios.delete(`/api/room-assignments/${assignment.id}`);
    } catch (error) {
      console.error('削除に失敗しました:', error);
      alert('削除に失敗しました');
      return;
    }
  }
  
  assignments.value.splice(index, 1);
  
  // 削除後に空になった場合、空のフォームを追加
  if (assignments.value.length === 0) {
    addAssignment();
  }
};

const fetchAllRoomAssignments = async () => {
  try {
    const allAssignments = [];
    
    // Fetch all plans directly from API
    const plansResponse = await axios.get('/api/plans');
    const allPlans = plansResponse.data.data || plansResponse.data;
    console.log('Fetching room assignments for', allPlans.length, 'plans');
    
    // Fetch each plan with days and participants
    const detailedPlans = [];
    for (const plan of allPlans) {
      try {
        const planResponse = await axios.get(`/api/plans/${plan.id}`);
        const planData = planResponse.data.data || planResponse.data;
        console.log(`📦 Plan ${plan.id}:`, planData.title, '- Days:', planData.days?.length || 0);
        
        // Fetch participants
        try {
          const participantsResponse = await axios.get(`/api/plans/${plan.id}/participants`);
          planData.participants = participantsResponse.data;
        } catch (error) {
          console.error('Failed to fetch participants for plan', plan.id, error);
          planData.participants = [];
        }
        
        detailedPlans.push(planData);
        
        if (!planData.days || planData.days.length === 0) {
          console.log(`⚠️ Plan ${plan.id} has no days, skipping`);
          continue;
        }
        
        for (const day of planData.days) {
          const response = await axios.get(`/api/days/${day.id}/room-assignments`);
          console.log('Room assignments response for day', day.id, ':', response.data);
          const dayAssignments = response.data
            .filter(room => room.floor || room.room_number) // 空でないデータのみ
            .map(room => ({
              id: room.id,
              plan_id: planData.id,
              day_id: day.id,
              day_number: day.day_number,
              participant_id: room.participant_id,
              participant_name: room.participant?.name || '',
              floor: room.floor ? String(room.floor) : '',
              room_number: room.room_number ? String(room.room_number) : '',
              notes: room.notes || ''
            }));
          console.log('Mapped room assignments:', dayAssignments);
          allAssignments.push(...dayAssignments);
        }
      } catch (error) {
        console.error('Failed to fetch plan', plan.id, error);
      }
    }
    
    // 取得したplansをキャッシュに保存
    allPlansCache.value = detailedPlans;
    console.log('Cached plans:', allPlansCache.value.length);
    
    // 既存データがあればそれを表示、なければ空のフォームを1つ表示
    assignments.value = allAssignments.length > 0 ? allAssignments : [{
      id: null,
      plan_id: null,
      day_id: null,
      day_number: null,
      participant_id: null,
      participant_name: '',
      floor: '',
      room_number: '',
      notes: ''
    }];
    console.log('Final assignments.value:', assignments.value);
    console.log('Total room assignments:', assignments.value.length);
  } catch (error) {
    console.error('部屋割の取得に失敗しました:', error);
    assignments.value = [{
      id: null,
      plan_id: null,
      day_id: null,
      day_number: null,
      participant_id: null,
      participant_name: '',
      floor: '',
      room_number: '',
      notes: ''
    }];
  }
};

const saveAllAssignments = async () => {
  saving.value = true;
  try {
    // 各割り当てに対して、既存データがあれば更新、なければ新規作成
    const saveResults = await Promise.all(
      assignments.value
        .filter(assignment => assignment.day_id && (assignment.participant_name || assignment.floor || assignment.room_number))
        .map(async (assignment) => {
          // Find participant by name
          const days = getAllDays();
          const day = days.find(d => d.id === assignment.day_id);
          let participantId = null;
          
          if (assignment.participant_name && day?.participants) {
            const participant = day.participants.find(p => p.name === assignment.participant_name);
            participantId = participant?.id || null;
          }
          
          const data = {
            participant_id: participantId,
            floor: assignment.floor,
            room_number: assignment.room_number,
            notes: assignment.notes
          };
          
          // IDがある場合は更新、ない場合は新規作成
          let response;
          if (assignment.id) {
            response = await axios.put(`/api/room-assignments/${assignment.id}`, data);
          } else {
            response = await axios.post(`/api/days/${assignment.day_id}/room-assignments`, data);
          }
          
          return {
            originalAssignment: assignment,
            savedData: response.data
          };
        })
    );
    
    // 保存結果を使ってIDと参加者IDのみ更新（入力データは保持）
    saveResults.forEach(result => {
      result.originalAssignment.id = result.savedData.id;
      if (result.savedData.participant_id) {
        result.originalAssignment.participant_id = result.savedData.participant_id;
      }
    });
    
    console.log('Saved successfully. Current assignments:', assignments.value.length);
    alert('保存しました');
  } catch (error) {
    console.error('保存に失敗しました:', error);
    alert('保存に失敗しました');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  console.log('RoomAssignmentManager mounted');
  // マウント時に必ずデータを取得
  fetchAllRoomAssignments();
});
</script>

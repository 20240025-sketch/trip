<template>
  <div>
    <!-- Room Assignments Display -->
    <div v-if="roomAssignments.length > 0" class="mb-6">
      <h3 class="text-lg font-bold mb-3 text-gray-800">🏠 部屋割</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <div 
          v-for="room in roomAssignments" 
          :key="room.id"
          class="flex items-center gap-2 p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-200"
        >
          <div class="flex-1">
            <div class="font-semibold text-gray-800 text-sm">
              <span v-if="room.floor" class="text-blue-600">{{ room.floor }}</span>
              {{ room.room_number }}
            </div>
            <div v-if="room.participant" class="text-xs text-gray-600 mt-1">
              👤 {{ room.participant.name }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bus Assignments Display -->
    <div v-if="busAssignments.length > 0">
      <h3 class="text-lg font-bold mb-3 text-gray-800">🚌 バス座席</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <div 
          v-for="bus in busAssignments" 
          :key="bus.id"
          class="flex items-center gap-2 p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200"
        >
          <div class="flex-1">
            <div class="font-semibold text-gray-800 text-sm">
              <span class="text-green-600">{{ bus.bus_number }}</span>
              {{ bus.row_number }}
              <span class="ml-1 px-2 py-0.5 text-xs rounded-full" :class="bus.side === 'left' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700'">
                {{ bus.side === 'left' ? '左' : '右' }}
              </span>
            </div>
            <div v-if="bus.participant" class="text-xs text-gray-600 mt-1">
              👤 {{ bus.participant.name }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  planId: {
    type: Number,
    required: true
  }
});

const roomAssignments = ref([]);
const busAssignments = ref([]);

const fetchAssignments = async () => {
  try {
    const [roomRes, busRes] = await Promise.all([
      axios.get(`/api/plans/${props.planId}/room-assignments`),
      axios.get(`/api/plans/${props.planId}/bus-assignments`)
    ]);
    roomAssignments.value = roomRes.data;
    busAssignments.value = busRes.data;
  } catch (error) {
    console.error('割り当て情報の取得に失敗しました:', error);
  }
};

onMounted(() => {
  fetchAssignments();
});

watch(() => props.planId, () => {
  fetchAssignments();
});
</script>

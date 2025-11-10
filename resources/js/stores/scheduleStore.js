import { defineStore } from 'pinia';
import axios from 'axios';

export const useScheduleStore = defineStore('schedule', {
  state: () => ({
    loading: false,
    error: null,
  }),

  actions: {
    // Day operations
    async updateDay(planId, dayId, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.put(`/api/plans/${planId}/days/${dayId}`, data);
        return response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || '日程の更新に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Schedule Item operations
    async createScheduleItem(dayId, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post(`/api/days/${dayId}/schedule-items`, data);
        return response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'スケジュールの作成に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateScheduleItem(dayId, itemId, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.put(`/api/days/${dayId}/schedule-items/${itemId}`, data);
        return response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'スケジュールの更新に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteScheduleItem(dayId, itemId) {
      this.loading = true;
      this.error = null;
      try {
        await axios.delete(`/api/days/${dayId}/schedule-items/${itemId}`);
      } catch (error) {
        this.error = 'スケジュールの削除に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async reorderScheduleItems(dayId, itemIds) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post(`/api/days/${dayId}/schedule-items/reorder`, {
          item_ids: itemIds,
        });
        return response.data.data;
      } catch (error) {
        this.error = 'スケジュールの並び替えに失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Participant operations
    async createParticipant(planId, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post(`/api/plans/${planId}/participants`, data);
        return response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || '参加者の追加に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteParticipant(planId, participantId) {
      this.loading = true;
      this.error = null;
      try {
        await axios.delete(`/api/plans/${planId}/participants/${participantId}`);
      } catch (error) {
        this.error = '参加者の削除に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Checklist operations
    async createChecklistItem(planId, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post(`/api/plans/${planId}/checklist-items`, data);
        return response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'チェックリスト項目の追加に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async toggleChecklistItem(planId, itemId) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post(`/api/plans/${planId}/checklist-items/${itemId}/toggle`);
        return response.data.data;
      } catch (error) {
        this.error = 'チェックリスト項目の更新に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteChecklistItem(planId, itemId) {
      this.loading = true;
      this.error = null;
      try {
        await axios.delete(`/api/plans/${planId}/checklist-items/${itemId}`);
      } catch (error) {
        this.error = 'チェックリスト項目の削除に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});

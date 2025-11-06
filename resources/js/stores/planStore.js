import { defineStore } from 'pinia';
import axios from 'axios';

export const usePlanStore = defineStore('plan', {
  state: () => ({
    plans: [],
    currentPlan: null,
    loading: false,
    error: null,
    pagination: {
      currentPage: 1,
      lastPage: 1,
      perPage: 12,
      total: 0,
    },
  }),

  actions: {
    async fetchPlans(page = 1, search = '') {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/plans', {
          params: { page, search },
        });
        this.plans = response.data.data;
        this.pagination = response.data.meta;
      } catch (error) {
        this.error = 'プランの取得に失敗しました';
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    async fetchPlan(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get(`/api/plans/${id}`);
        this.currentPlan = response.data.data;
      } catch (error) {
        this.error = 'プランの取得に失敗しました';
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    async fetchPlanBySlug(slug) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get(`/api/plans/slug/${slug}`);
        this.currentPlan = response.data.data;
      } catch (error) {
        this.error = 'プランの取得に失敗しました';
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    async createPlan(planData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/plans', planData);
        this.currentPlan = response.data.data;
        return response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'プランの作成に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updatePlan(id, planData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.put(`/api/plans/${id}`, planData);
        this.currentPlan = response.data.data;
        return response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'プランの更新に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deletePlan(id) {
      this.loading = true;
      this.error = null;
      try {
        await axios.delete(`/api/plans/${id}`);
        this.plans = this.plans.filter(plan => plan.id !== id);
        if (this.currentPlan?.id === id) {
          this.currentPlan = null;
        }
      } catch (error) {
        this.error = 'プランの削除に失敗しました';
        console.error(error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async uploadImage(file, imageableType, imageableId) {
      const formData = new FormData();
      formData.append('image', file);
      formData.append('imageable_type', imageableType);
      formData.append('imageable_id', imageableId);

      try {
        const response = await axios.post('/api/images', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
        return response.data.data;
      } catch (error) {
        console.error('画像のアップロードに失敗しました', error);
        throw error;
      }
    },

    async deleteImage(imageId) {
      try {
        await axios.delete(`/api/images/${imageId}`);
      } catch (error) {
        console.error('画像の削除に失敗しました', error);
        throw error;
      }
    },
  },
});

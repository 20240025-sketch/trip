import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    isUser: (state) => state.user?.role === 'user',
  },

  actions: {
    async register(credentials) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post('/api/register', credentials);
        this.user = response.data.data.user;
        this.token = response.data.data.token;
        
        localStorage.setItem('token', this.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'ユーザー登録に失敗しました';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async login(credentials) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post('/api/login', credentials);
        this.user = response.data.data.user;
        this.token = response.data.data.token;
        
        localStorage.setItem('token', this.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'ログインに失敗しました';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async adminLogin(credentials) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post('/api/admin-login', {
          email: credentials.email,
          password: credentials.password,
          admin_password: credentials.admin_password,
        });
        this.user = response.data.data.user;
        this.token = response.data.data.token;
        
        localStorage.setItem('token', this.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || '管理者ログインに失敗しました';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      this.loading = true;
      this.error = null;

      try {
        if (this.token) {
          await axios.post('/api/logout');
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.user = null;
        this.token = null;
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
        this.loading = false;
      }
    },

    async fetchUser() {
      if (!this.token) return;

      this.loading = true;
      this.error = null;

      try {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        const response = await axios.get('/api/me');
        this.user = response.data.data;
      } catch (error) {
        console.error('Fetch user error:', error);
        // Token might be invalid, clear auth state
        this.user = null;
        this.token = null;
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
      } finally {
        this.loading = false;
      }
    },

    initAuth() {
      console.log('initAuth called, token:', this.token);
      if (this.token) {
        console.log('Setting Authorization header:', `Bearer ${this.token}`);
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        console.log('Current axios headers:', axios.defaults.headers.common);
        this.fetchUser();
      } else {
        console.log('No token found in localStorage');
      }
    },
  },
});

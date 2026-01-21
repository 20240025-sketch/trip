import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import { useAuthStore } from './stores/authStore';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);

// Initialize auth store BEFORE router to ensure tokens are set
const authStore = useAuthStore();
authStore.initAuth();

app.use(router);
app.mount('#app');

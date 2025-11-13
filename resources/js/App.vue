<template>
  <div id="app" class="min-h-screen" style="background: linear-gradient(135deg, #e0f7ff 0%, #ffffff 50%, #e0f7ff 100%);">
    <AppHeader />
    <main class="container mx-auto px-4 py-8">
      <RouterView />
    </main>
    <AppFooter />
    
    <!-- Toast Notification -->
    <div 
      v-if="uiStore.alert.show"
      class="fixed top-4 right-4 z-50 max-w-md animate-fade-in"
    >
      <div 
        :class="[
          'px-6 py-4 rounded-2xl shadow-lg border-2',
          {
            'bg-cyan-50 text-cyan-800 border-cyan-300': uiStore.alert.type === 'success',
            'bg-red-50 text-red-800 border-red-300': uiStore.alert.type === 'error',
            'bg-yellow-50 text-yellow-800 border-yellow-300': uiStore.alert.type === 'warning',
            'bg-blue-50 text-blue-800 border-blue-300': uiStore.alert.type === 'info',
          }
        ]"
      >
        <div class="flex items-center gap-3">
          <span class="text-2xl">
            {{ uiStore.alert.type === 'success' ? '✓' : uiStore.alert.type === 'error' ? '✕' : 'ℹ' }}
          </span>
          <p class="flex-1 font-semibold">{{ uiStore.alert.message }}</p>
          <button 
            @click="uiStore.hideAlert()"
            class="hover:opacity-70 transition-opacity"
          >
            ✕
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router';
import AppHeader from '@/components/layouts/AppHeader.vue';
import AppFooter from '@/components/layouts/AppFooter.vue';
import { useUiStore } from '@/stores/uiStore';

const uiStore = useUiStore();
</script>

<style>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-1rem);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>

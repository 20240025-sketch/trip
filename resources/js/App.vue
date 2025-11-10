<template>
  <div id="app" class="min-h-screen bg-gray-50">
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
          'px-6 py-4 rounded-lg shadow-lg',
          {
            'bg-green-500 text-white': uiStore.alert.type === 'success',
            'bg-red-500 text-white': uiStore.alert.type === 'error',
            'bg-yellow-500 text-white': uiStore.alert.type === 'warning',
            'bg-blue-500 text-white': uiStore.alert.type === 'info',
          }
        ]"
      >
        <div class="flex items-center gap-3">
          <span class="text-2xl">
            {{ uiStore.alert.type === 'success' ? '✓' : uiStore.alert.type === 'error' ? '✕' : 'ℹ' }}
          </span>
          <p class="flex-1">{{ uiStore.alert.message }}</p>
          <button 
            @click="uiStore.hideAlert()"
            class="text-white hover:text-gray-200"
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

<template>
  <header class="bg-white shadow-sm">
    <nav class="container mx-auto px-4 py-4">
      <div class="flex items-center justify-between">
        <router-link to="/" class="text-2xl font-bold text-blue-600">
          旅の計画
        </router-link>
        
        <div class="flex items-center space-x-6">
          <router-link 
            to="/plans" 
            class="text-gray-700 hover:text-blue-600 transition-colors"
          >
            プラン一覧
          </router-link>
          <router-link 
            to="/plans/create" 
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            新規作成
          </router-link>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
</script>

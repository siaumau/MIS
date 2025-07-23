<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 導航欄 -->
    <NavBar />

    <!-- 主要內容 -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">設備管理</h2>
          <p class="text-gray-600">管理公司內部的所有設備資訊與狀態</p>
        </div>

        <!-- 操作區塊 -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
              <div class="flex-1 min-w-0">
                <h3 class="text-lg leading-6 font-medium text-gray-900">設備清單</h3>
                <p class="mt-1 text-sm text-gray-500">查看和管理所有註冊的設備</p>
              </div>
              <div class="mt-4 sm:mt-0 sm:ml-4">
                <router-link 
                  to="/equipment/create" 
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                  <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                  </svg>
                  新增設備
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- 設備列表 -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">尚無設備資料</h3>
              <p class="mt-1 text-sm text-gray-500">開始新增第一個設備</p>
              <div class="mt-6">
                <router-link 
                  to="/equipment/create" 
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                  新增設備
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import NavBar from '@/components/NavBar.vue'
</script>
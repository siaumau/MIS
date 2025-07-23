<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 導航欄 -->
    <NavBar />

    <!-- 主要內容 -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">報修詳情</h2>
          <p class="text-gray-600">查看報修申請的詳細資訊</p>
        </div>

        <!-- 報修資訊 -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">電腦無法開機</h3>
                <p class="mt-1 text-sm text-gray-500">報修ID: {{ $route.params.id }}</p>
              </div>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                待處理
              </span>
            </div>

            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">申請人</dt>
                <dd class="mt-1 text-sm text-gray-900">張三</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">優先級</dt>
                <dd class="mt-1 text-sm text-gray-900">中</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">故障位置</dt>
                <dd class="mt-1 text-sm text-gray-900">3樓辦公室</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">建立時間</dt>
                <dd class="mt-1 text-sm text-gray-900">2024-12-01 10:30</dd>
              </div>
              <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500">問題描述</dt>
                <dd class="mt-1 text-sm text-gray-900">電腦今天早上突然無法開機，按電源鍵沒有反應，指示燈也沒有亮起。已經檢查過電源線連接，都正常。</dd>
              </div>
            </dl>

            <div class="mt-8 pt-6 border-t border-gray-200">
              <router-link 
                to="/repairs" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                返回報修列表
              </router-link>
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
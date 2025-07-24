<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 導航欄 -->
    <NavBar />

    <!-- 主要內容 -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">公告詳情</h2>
          <p class="text-gray-600">查看公告的詳細內容</p>
        </div>

        <!-- 公告內容 -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex items-start justify-between mb-6">
              <div class="flex-1">
                <div class="flex items-center space-x-2 mb-3">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    緊急
                  </span>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    需確認
                  </span>
                </div>
                <h3 class="text-xl leading-6 font-medium text-gray-900 mb-2">重要：系統維護通知</h3>
                <p class="text-sm text-gray-500">公告ID: {{ $route.params.id }} | 發布時間: 2024-12-01 10:00</p>
              </div>
              <div class="ml-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  已讀
                </span>
              </div>
            </div>

            <div class="prose max-w-none">
              <div class="bg-gray-50 rounded-lg p-6">
                <p class="text-gray-800 leading-relaxed">
                  本系統將於本週末（12月7日-8日）進行例行維護，期間可能會暫時中斷服務。請提前保存重要資料，避免資料遺失。
                </p>
                <br>
                <p class="text-gray-800 leading-relaxed">
                  維護時間：<br>
                  • 12月7日 22:00 - 12月8日 06:00<br>
                  • 預計影響時間：8小時
                </p>
                <br>
                <p class="text-gray-800 leading-relaxed">
                  維護期間如有緊急需求，請聯繫IT部門：<br>
                  • 電話：分機 123<br>
                  • 郵件：it@company.com
                </p>
                <br>
                <p class="text-gray-800 leading-relaxed font-medium">
                  請各位同事配合，提前完成重要工作並做好資料備份。感謝您的理解與配合！
                </p>
              </div>
            </div>

            <!-- 確認按鈕 -->
            <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-yellow-800">此公告需要確認</h4>
                  <p class="text-sm text-yellow-700">請確認您已閱讀並了解此公告內容</p>
                </div>
                <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                  確認已讀
                </button>
              </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200">
              <div class="flex items-center justify-between">
                <router-link 
                  to="/announcements" 
                  class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                  <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  返回公告列表
                </router-link>
                
                <div class="text-sm text-gray-500">
                  有效期限：2024-12-31
                </div>
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
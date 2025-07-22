<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-900">404</h1>
        <p class="mt-4 text-xl text-gray-600">頁面不存在</p>
        <p class="mt-2 text-base text-gray-500">
          抱歉，您訪問的頁面不存在或已被移除。
        </p>
        
        <div class="mt-8 flex justify-center space-x-4">
          <router-link
            to="/dashboard"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
          >
            回到首頁
          </router-link>
          <button
            @click="$router.go(-1)"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50"
          >
            返回上頁
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// 404 頁面組件
</script>
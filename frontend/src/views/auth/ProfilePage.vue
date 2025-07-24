<template>
  <div class="profile-page">
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">個人資料</h3>
        <div class="mt-2 max-w-xl text-sm text-gray-500">
          <p>管理您的個人資料和帳號設定</p>
        </div>
        <form class="mt-5 space-y-6">
          <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-3">
              <label for="name" class="block text-sm font-medium text-gray-700">姓名</label>
              <input 
                type="text" 
                id="name" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
            </div>
            <div class="sm:col-span-3">
              <label for="email" class="block text-sm font-medium text-gray-700">電子信箱</label>
              <input 
                type="email" 
                id="email" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
            </div>
          </div>
          <div class="flex justify-end">
            <button 
              type="submit" 
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
            >
              更新資料
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>

</script>

<style scoped>

</style>
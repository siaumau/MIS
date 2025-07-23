<template>
  <div class="equipment-form">
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">設備表單</h3>
        <div class="mt-2 max-w-xl text-sm text-gray-500">
          <p>新增或編輯設備資訊</p>
        </div>
        <form class="mt-5 space-y-6">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">設備名稱</label>
            <input 
              type="text" 
              id="name" 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>
          <div class="flex justify-end space-x-3">
            <router-link 
              to="/equipment" 
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              取消
            </router-link>
            <button 
              type="submit" 
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
            >
              儲存
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
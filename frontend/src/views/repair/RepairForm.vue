<template>
  <div class="repair-form">
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">報修表單</h3>
        <div class="mt-2 max-w-xl text-sm text-gray-500">
          <p>新增或編輯報修申請</p>
        </div>
        <form class="mt-5 space-y-6">
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700">報修標題</label>
            <input 
              type="text" 
              id="title" 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
            />
          </div>
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">問題描述</label>
            <textarea 
              id="description" 
              rows="4" 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
            ></textarea>
          </div>
          <div class="flex justify-end space-x-3">
            <router-link 
              to="/repairs" 
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              取消
            </router-link>
            <button 
              type="submit" 
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
            >
              提交報修
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
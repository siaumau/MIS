<template>
  <div class="equipment-list">
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">設備管理</h3>
        <div class="mt-2 max-w-xl text-sm text-gray-500">
          <p>管理公司內部的所有設備資訊</p>
        </div>
        <div class="mt-5">
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
</template>

<script setup>

</script>

<style scoped>

</style>
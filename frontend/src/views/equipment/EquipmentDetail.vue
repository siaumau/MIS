<template>
  <div class="equipment-detail">
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">設備詳情</h3>
        <div class="mt-2 max-w-xl text-sm text-gray-500">
          <p>查看設備詳細資訊</p>
        </div>
        <div class="mt-5">
          <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-gray-500">設備ID</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $route.params.id }}</dd>
            </div>
          </dl>
        </div>
        <div class="mt-5">
          <router-link 
            to="/equipment" 
            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            返回列表
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
<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 導航欄 -->
    <NavBar />

    <!-- 主要內容 -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">新增報修申請</h2>
          <p class="text-gray-600">填寫報修相關資訊，我們會盡快為您處理</p>
        </div>

        <!-- 報修表單 -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <label for="title" class="block text-sm font-medium text-gray-700">報修標題 *</label>
                  <input 
                    v-model="form.title"
                    type="text" 
                    id="title" 
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                    placeholder="請簡述問題"
                  />
                </div>
                
                <div>
                  <label for="priority" class="block text-sm font-medium text-gray-700">優先級</label>
                  <select 
                    v-model="form.priority"
                    id="priority" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                  >
                    <option value="low">低</option>
                    <option value="medium" selected>中</option>
                    <option value="high">高</option>
                    <option value="urgent">緊急</option>
                  </select>
                </div>

                <div>
                  <label for="location" class="block text-sm font-medium text-gray-700">故障位置</label>
                  <input 
                    v-model="form.location"
                    type="text" 
                    id="location" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                    placeholder="例: 3樓會議室"
                  />
                </div>

                <div>
                  <label for="equipment" class="block text-sm font-medium text-gray-700">相關設備</label>
                  <select 
                    v-model="form.equipment_id"
                    id="equipment" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                  >
                    <option value="">請選擇設備</option>
                    <!-- 這裡應該從API載入設備清單 -->
                  </select>
                </div>
              </div>

              <div>
                <label for="description" class="block text-sm font-medium text-gray-700">問題描述 *</label>
                <textarea 
                  v-model="form.description"
                  id="description" 
                  rows="6" 
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                  placeholder="請詳細描述遇到的問題，包括何時發生、具體症狀等"
                ></textarea>
              </div>

              <div>
                <label for="images" class="block text-sm font-medium text-gray-700">相關圖片</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                  <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                      <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                        <span>上傳圖片</span>
                        <input id="file-upload" name="file-upload" type="file" class="sr-only" multiple accept="image/*">
                      </label>
                      <p class="pl-1">或拖拽到這裡</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                  </div>
                </div>
              </div>

              <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <router-link 
                  to="/repairs" 
                  class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                  取消
                </router-link>
                <button 
                  type="submit" 
                  :disabled="loading"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                >
                  <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ loading ? '提交中...' : '提交報修' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import NavBar from '@/components/NavBar.vue'

const router = useRouter()
const loading = ref(false)

const form = ref({
  title: '',
  description: '',
  priority: 'medium',
  location: '',
  equipment_id: ''
})

const handleSubmit = async () => {
  loading.value = true
  try {
    // TODO: 實現提交邏輯
    console.log('提交報修表單:', form.value)
    // 模擬API請求
    await new Promise(resolve => setTimeout(resolve, 1000))
    router.push('/repairs')
  } catch (error) {
    console.error('提交失敗:', error)
  } finally {
    loading.value = false
  }
}

</script>
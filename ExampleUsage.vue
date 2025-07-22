<template>
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">圖片上傳範例</h1>
    
    <!-- 基本使用 -->
    <div class="mb-8">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">基本圖片上傳</h2>
      <ImagePasteUpload
        :api-endpoint="'/api/upload-image.php'"
        :max-file-size="5 * 1024 * 1024"
        :max-files="5"
        @upload-success="onUploadSuccess"
        @upload-error="onUploadError"
      />
    </div>

    <!-- 設備管理中的使用 -->
    <div class="mb-8">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">設備資料編輯</h2>
      <form @submit.prevent="saveEquipment" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">設備名稱</label>
            <input
              v-model="equipment.name"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="輸入設備名稱"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">IP 位址</label>
            <input
              v-model="equipment.ip"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="192.168.1.100"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">設備照片</label>
          <ImagePasteUpload
            :api-endpoint="'/api/upload-image.php'"
            :max-file-size="2 * 1024 * 1024"
            :max-files="3"
            @upload-success="onEquipmentImageUpload"
          />
        </div>

        <button
          type="submit"
          class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
        >
          保存設備資料
        </button>
      </form>
    </div>

    <!-- 報修單中的使用 -->
    <div class="mb-8">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">故障報修</h2>
      <form @submit.prevent="submitRepairRequest" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">故障描述</label>
          <textarea
            v-model="repairRequest.description"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="請詳細描述故障情況..."
          ></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">故障照片</label>
          <ImagePasteUpload
            :api-endpoint="'/api/upload-image.php'"
            :max-file-size="3 * 1024 * 1024"
            :max-files="5"
            @upload-success="onRepairImageUpload"
          />
        </div>

        <div class="flex space-x-4">
          <button
            type="submit"
            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
          >
            提交報修單
          </button>
          <button
            type="button"
            @click="resetForm"
            class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
          >
            重置表單
          </button>
        </div>
      </form>
    </div>

    <!-- 上傳結果顯示 -->
    <div v-if="uploadedImages.length > 0" class="mb-8">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">已上傳圖片</h2>
      <div class="grid grid-cols-3 gap-4">
        <div
          v-for="(image, index) in uploadedImages"
          :key="index"
          class="border border-gray-200 rounded-lg p-3"
        >
          <img
            :src="image.thumbnail_path || image.file_path"
            :alt="image.original_name"
            class="w-full h-32 object-cover rounded mb-2"
          />
          <p class="text-sm text-gray-600 truncate">{{ image.original_name }}</p>
          <p class="text-xs text-gray-400">{{ formatFileSize(image.file_size) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ImagePasteUpload from './ImagePasteUpload.vue'

// 響應式數據
const equipment = ref({
  name: '',
  ip: '',
  images: []
})

const repairRequest = ref({
  description: '',
  images: []
})

const uploadedImages = ref([])

// 事件處理函數
const onUploadSuccess = (results) => {
  console.log('上傳成功:', results)
  uploadedImages.value.push(...results)
}

const onUploadError = (error) => {
  console.error('上傳失敗:', error)
}

const onEquipmentImageUpload = (results) => {
  equipment.value.images.push(...results)
  console.log('設備圖片上傳成功:', results)
}

const onRepairImageUpload = (results) => {
  repairRequest.value.images.push(...results)
  console.log('報修圖片上傳成功:', results)
}

const saveEquipment = () => {
  // 保存設備資料的邏輯
  console.log('保存設備資料:', equipment.value)
  
  // 這裡可以發送到後端 API
  fetch('/api/equipment.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(equipment.value)
  })
  .then(response => response.json())
  .then(data => {
    console.log('設備保存成功:', data)
  })
  .catch(error => {
    console.error('設備保存失敗:', error)
  })
}

const submitRepairRequest = () => {
  // 提交報修單的邏輯
  console.log('提交報修單:', repairRequest.value)
  
  // 這裡可以發送到後端 API
  fetch('/api/repair-request.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(repairRequest.value)
  })
  .then(response => response.json())
  .then(data => {
    console.log('報修單提交成功:', data)
    resetForm()
  })
  .catch(error => {
    console.error('報修單提交失敗:', error)
  })
}

const resetForm = () => {
  repairRequest.value = {
    description: '',
    images: []
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>
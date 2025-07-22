<template>
  <div class="w-full">
    <!-- 上傳區域 -->
    <div
      ref="dropZone"
      @click="triggerFileInput"
      @drop="onDrop"
      @dragover="onDragOver"
      @dragenter="onDragEnter"
      @dragleave="onDragLeave"
      @paste="onPaste"
      :class="[
        'relative border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors duration-200',
        isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400',
        'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent'
      ]"
      tabindex="0"
    >
      <input
        ref="fileInput"
        type="file"
        accept="image/*"
        @change="onFileSelect"
        class="hidden"
        multiple
      />
      
      <div v-if="images.length === 0" class="py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
          <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <p class="mt-2 text-sm text-gray-600">
          點擊上傳圖片、拖曳圖片至此或按 <kbd class="px-2 py-1 text-xs bg-gray-100 border border-gray-300 rounded">Ctrl+V</kbd> 貼上圖片
        </p>
        <p class="text-xs text-gray-400 mt-1">支援 PNG, JPG, GIF 格式</p>
      </div>
    </div>

    <!-- 圖片預覽區域 -->
    <div v-if="images.length > 0" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div
        v-for="(image, index) in images"
        :key="index"
        class="relative group bg-gray-100 rounded-lg overflow-hidden aspect-square"
      >
        <img
          :src="image.dataUrl"
          :alt="`上傳圖片 ${index + 1}`"
          class="w-full h-full object-cover"
        />
        
        <!-- 刪除按鈕 -->
        <button
          @click="removeImage(index)"
          class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:bg-red-600"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>

        <!-- 上傳狀態 -->
        <div v-if="image.uploading" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
        </div>

        <!-- 檔案資訊 -->
        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
          <p class="truncate">{{ image.name }}</p>
          <p>{{ formatFileSize(image.size) }}</p>
        </div>
      </div>
    </div>

    <!-- 上傳按鈕 -->
    <div v-if="images.length > 0" class="mt-4 flex justify-between items-center">
      <button
        @click="clearAll"
        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors"
      >
        清除全部
      </button>
      
      <button
        @click="uploadImages"
        :disabled="uploading || images.length === 0"
        :class="[
          'px-6 py-2 rounded-lg text-sm font-medium transition-colors',
          uploading || images.length === 0
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-blue-500 text-white hover:bg-blue-600'
        ]"
      >
        <span v-if="uploading" class="flex items-center">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
          上傳中...
        </span>
        <span v-else>上傳圖片 ({{ images.length }})</span>
      </button>
    </div>

    <!-- 錯誤訊息 -->
    <div v-if="errorMessage" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
      {{ errorMessage }}
    </div>

    <!-- 成功訊息 -->
    <div v-if="successMessage" class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
      {{ successMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  maxFileSize: {
    type: Number,
    default: 5 * 1024 * 1024 // 5MB
  },
  maxFiles: {
    type: Number,
    default: 10
  },
  apiEndpoint: {
    type: String,
    default: '/api/upload-image.php'
  }
})

const emit = defineEmits(['upload-success', 'upload-error'])

const dropZone = ref(null)
const fileInput = ref(null)
const isDragging = ref(false)
const images = ref([])
const uploading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const triggerFileInput = () => {
  fileInput.value?.click()
}

const onFileSelect = (event) => {
  const files = Array.from(event.target.files)
  processFiles(files)
  event.target.value = ''
}

const onDrop = (event) => {
  event.preventDefault()
  isDragging.value = false
  
  const files = Array.from(event.dataTransfer.files)
  processFiles(files)
}

const onDragOver = (event) => {
  event.preventDefault()
}

const onDragEnter = (event) => {
  event.preventDefault()
  isDragging.value = true
}

const onDragLeave = (event) => {
  event.preventDefault()
  if (!dropZone.value?.contains(event.relatedTarget)) {
    isDragging.value = false
  }
}

const onPaste = async (event) => {
  const items = event.clipboardData?.items
  if (!items) return

  const files = []
  for (const item of items) {
    if (item.type.startsWith('image/')) {
      const file = item.getAsFile()
      if (file) files.push(file)
    }
  }

  if (files.length > 0) {
    processFiles(files)
  }
}

const processFiles = async (files) => {
  clearMessages()
  
  const imageFiles = files.filter(file => file.type.startsWith('image/'))
  
  if (imageFiles.length === 0) {
    showError('請選擇圖片檔案')
    return
  }

  if (images.value.length + imageFiles.length > props.maxFiles) {
    showError(`最多只能上傳 ${props.maxFiles} 張圖片`)
    return
  }

  for (const file of imageFiles) {
    if (file.size > props.maxFileSize) {
      showError(`檔案 "${file.name}" 超過大小限制 (${formatFileSize(props.maxFileSize)})`)
      continue
    }

    try {
      const dataUrl = await fileToBase64(file)
      images.value.push({
        file,
        dataUrl,
        name: file.name,
        size: file.size,
        uploading: false
      })
    } catch (error) {
      showError(`處理檔案 "${file.name}" 時發生錯誤`)
    }
  }
}

const fileToBase64 = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = () => reject(new Error('讀取檔案失敗'))
    reader.readAsDataURL(file)
  })
}

const removeImage = (index) => {
  images.value.splice(index, 1)
  clearMessages()
}

const clearAll = () => {
  images.value = []
  clearMessages()
}

const uploadImages = async () => {
  if (images.value.length === 0) return

  uploading.value = true
  clearMessages()

  try {
    const uploadPromises = images.value.map(async (image, index) => {
      image.uploading = true
      
      const formData = new FormData()
      formData.append('image', image.dataUrl)
      formData.append('filename', image.name)

      const response = await fetch(props.apiEndpoint, {
        method: 'POST',
        body: formData
      })

      const result = await response.json()
      
      if (!response.ok) {
        throw new Error(result.message || '上傳失敗')
      }

      image.uploading = false
      return result
    })

    const results = await Promise.all(uploadPromises)
    
    showSuccess(`成功上傳 ${results.length} 張圖片`)
    emit('upload-success', results)
    
    // 清除已上傳的圖片
    setTimeout(() => {
      images.value = []
    }, 2000)

  } catch (error) {
    showError(error.message || '上傳過程中發生錯誤')
    emit('upload-error', error)
    
    // 重置上傳狀態
    images.value.forEach(image => {
      image.uploading = false
    })
  } finally {
    uploading.value = false
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const showError = (message) => {
  errorMessage.value = message
  successMessage.value = ''
  setTimeout(() => {
    errorMessage.value = ''
  }, 5000)
}

const showSuccess = (message) => {
  successMessage.value = message
  errorMessage.value = ''
  setTimeout(() => {
    successMessage.value = ''
  }, 3000)
}

const clearMessages = () => {
  errorMessage.value = ''
  successMessage.value = ''
}

// 全域貼上事件監聽
const handleGlobalPaste = (event) => {
  if (dropZone.value && document.activeElement === dropZone.value) {
    onPaste(event)
  }
}

onMounted(() => {
  document.addEventListener('paste', handleGlobalPaste)
})

onUnmounted(() => {
  document.removeEventListener('paste', handleGlobalPaste)
})
</script>
<template>
  <div class="bg-white shadow rounded-lg p-4 mb-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div class="flex items-center">
          <div 
            :class="[
              'w-3 h-3 rounded-full mr-2',
              ipStatus === 'connected' ? 'bg-green-500' : 
              ipStatus === 'loading' ? 'bg-yellow-500' : 'bg-red-500'
            ]"
          ></div>
          <span class="text-sm font-medium text-gray-700">
            API 連接狀態: {{ getStatusText() }}
          </span>
        </div>
        
        <div v-if="currentIP" class="text-sm text-gray-500">
          IP: {{ currentIP }}
        </div>
      </div>
      
      <div class="flex items-center space-x-2">
        <button 
          @click="refreshIP"
          :disabled="isRefreshing"
          class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
        >
          <svg 
            :class="['w-3 h-3 mr-1', isRefreshing ? 'animate-spin' : '']" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          {{ isRefreshing ? '更新中...' : '刷新 IP' }}
        </button>
        
        <button 
          @click="testConnection"
          :disabled="isTesting"
          class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
        >
          <svg 
            :class="['w-3 h-3 mr-1', isTesting ? 'animate-spin' : '']" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          {{ isTesting ? '測試中...' : '測試連接' }}
        </button>
      </div>
    </div>
    
    <!-- 詳細信息（可展開） -->
    <div v-if="showDetails" class="mt-3 pt-3 border-t border-gray-200">
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <span class="font-medium text-gray-700">API Base URL:</span>
          <div class="text-gray-600 break-all">{{ apiBaseUrl }}</div>
        </div>
        <div>
          <span class="font-medium text-gray-700">檢測方法:</span>
          <div class="text-gray-600">{{ detectionMethod }}</div>
        </div>
        <div>
          <span class="font-medium text-gray-700">最後更新:</span>
          <div class="text-gray-600">{{ lastUpdated }}</div>
        </div>
        <div>
          <span class="font-medium text-gray-700">連接延遲:</span>
          <div class="text-gray-600">{{ latency }}ms</div>
        </div>
      </div>
    </div>
    
    <button 
      @click="showDetails = !showDetails"
      class="mt-3 text-xs text-indigo-600 hover:text-indigo-800"
    >
      {{ showDetails ? '隱藏詳細信息' : '顯示詳細信息' }}
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { IPHelper } from '@/utils/ipHelper'
import { api } from '@/utils/apiClient'

const currentIP = ref('')
const ipStatus = ref('loading') // loading, connected, error
const isRefreshing = ref(false)
const isTesting = ref(false)
const showDetails = ref(false)
const apiBaseUrl = ref('')
const detectionMethod = ref('')
const lastUpdated = ref('')
const latency = ref(0)

let statusCheckInterval = null

const getStatusText = () => {
  switch (ipStatus.value) {
    case 'connected': return '已連接'
    case 'loading': return '檢測中'
    case 'error': return '連接失敗'
    default: return '未知'
  }
}

const updateIPInfo = async () => {
  try {
    const ip = await IPHelper.getLocalIP()
    currentIP.value = ip
    apiBaseUrl.value = await IPHelper.buildApiUrl(8000, '/api')
    detectionMethod.value = 'WebRTC + 備用服務'
    lastUpdated.value = new Date().toLocaleTimeString()
  } catch (error) {
    console.error('Failed to get IP info:', error)
  }
}

const refreshIP = async () => {
  if (isRefreshing.value) return
  
  isRefreshing.value = true
  ipStatus.value = 'loading'
  
  try {
    await IPHelper.refreshIP()
    await updateIPInfo()
    await testConnection()
  } catch (error) {
    console.error('Failed to refresh IP:', error)
    ipStatus.value = 'error'
  } finally {
    isRefreshing.value = false
  }
}

const testConnection = async () => {
  if (isTesting.value) return
  
  isTesting.value = true
  const startTime = Date.now()
  
  try {
    // 嘗試發送一個簡單的健康檢查請求
    await api.get('/health')
    latency.value = Date.now() - startTime
    ipStatus.value = 'connected'
  } catch (error) {
    console.error('Connection test failed:', error)
    latency.value = Date.now() - startTime
    ipStatus.value = 'error'
  } finally {
    isTesting.value = false
  }
}

const startStatusCheck = () => {
  // 每30秒自動檢查一次連接狀態
  statusCheckInterval = setInterval(async () => {
    if (!isTesting.value && !isRefreshing.value) {
      await testConnection()
    }
  }, 30000)
}

const stopStatusCheck = () => {
  if (statusCheckInterval) {
    clearInterval(statusCheckInterval)
    statusCheckInterval = null
  }
}

onMounted(async () => {
  await updateIPInfo()
  await testConnection()
  startStatusCheck()
})

onUnmounted(() => {
  stopStatusCheck()
})
</script>
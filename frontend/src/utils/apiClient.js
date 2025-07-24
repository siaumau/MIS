import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import { buildApiUrl } from './ipHelper'

// 動態獲取 API 基礎 URL
let baseURL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:9000/api'

// 如果配置為使用動態 IP，則獲取本機 IP
if (import.meta.env.VITE_USE_DYNAMIC_IP === 'true') {
  buildApiUrl(8000, '/api').then(url => {
    baseURL = url
    apiClient.defaults.baseURL = url
    console.log('API Base URL updated to:', url)
  }).catch(error => {
    console.warn('Failed to get dynamic IP, using default:', error)
  })
}

// 創建 axios 實例
const apiClient = axios.create({
  baseURL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  }
})

// 請求攔截器
apiClient.interceptors.request.use(
  (config) => {
    // 添加認證 Token
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // 添加請求 ID 用於除錯
    config.requestId = Date.now().toString()
    
    console.log(`[API Request ${config.requestId}]`, {
      method: config.method?.toUpperCase(),
      url: config.url,
      params: config.params,
      data: config.data
    })
    
    return config
  },
  (error) => {
    console.error('[API Request Error]', error)
    return Promise.reject(error)
  }
)

// 回應攔截器
apiClient.interceptors.response.use(
  (response) => {
    console.log(`[API Response ${response.config.requestId}]`, {
      status: response.status,
      data: response.data
    })
    
    return response
  },
  async (error) => {
    const toast = useToast()
    
    console.error(`[API Error ${error.config?.requestId}]`, {
      status: error.response?.status,
      data: error.response?.data,
      message: error.message
    })
    
    // 處理特定錯誤狀態
    if (error.response) {
      const { status, data } = error.response
      
      switch (status) {
        case 401:
          // Token 過期或無效，清除認證狀態
          const authStore = useAuthStore()
          await authStore.logout()
          
          // 如果不是在登入頁面，則跳轉到登入頁
          if (window.location.pathname !== '/login') {
            window.location.href = '/login'
          }
          
          toast.error('登入已過期，請重新登入')
          break
          
        case 403:
          toast.error('權限不足')
          break
          
        case 404:
          toast.error('請求的資源不存在')
          break
          
        case 422:
          // 驗證錯誤，不顯示 toast，由組件處理
          break
          
        case 429:
          toast.error('請求過於頻繁，請稍後再試')
          break
          
        case 500:
        case 502:
        case 503:
        case 504:
          toast.error('伺服器錯誤，請稍後再試')
          break
          
        default:
          if (data?.message) {
            toast.error(data.message)
          } else {
            toast.error('請求失敗')
          }
      }
    } else if (error.request) {
      // 網路錯誤
      toast.error('網路連接失敗，請檢查網路設定')
    } else {
      // 其他錯誤
      toast.error('請求配置錯誤')
    }
    
    return Promise.reject(error)
  }
)

// 封裝常用的 HTTP 方法
export const api = {
  get: (url, config = {}) => apiClient.get(url, config),
  post: (url, data = {}, config = {}) => apiClient.post(url, data, config),
  put: (url, data = {}, config = {}) => apiClient.put(url, data, config),
  patch: (url, data = {}, config = {}) => apiClient.patch(url, data, config),
  delete: (url, config = {}) => apiClient.delete(url, config)
}

// 檔案上傳專用方法
export const uploadFile = (url, file, onProgress = null) => {
  const formData = new FormData()
  formData.append('file', file)
  
  return apiClient.post(url, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    },
    onUploadProgress: (progressEvent) => {
      if (onProgress && progressEvent.total) {
        const percentCompleted = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        )
        onProgress(percentCompleted)
      }
    }
  })
}

// 下載檔案
export const downloadFile = async (url, filename = null) => {
  try {
    const response = await apiClient.get(url, {
      responseType: 'blob'
    })
    
    // 創建下載連結
    const blob = new Blob([response.data])
    const downloadUrl = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = downloadUrl
    
    // 設置檔案名稱
    if (filename) {
      link.download = filename
    } else {
      // 嘗試從回應標頭獲取檔案名稱
      const contentDisposition = response.headers['content-disposition']
      if (contentDisposition) {
        const fileNameMatch = contentDisposition.match(/filename="(.+)"/)
        if (fileNameMatch) {
          link.download = fileNameMatch[1]
        }
      }
    }
    
    // 觸發下載
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(downloadUrl)
  } catch (error) {
    console.error('Download failed:', error)
    throw error
  }
}

export default apiClient
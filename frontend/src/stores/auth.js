import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  // 狀態
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const loading = ref(false)

  // 計算屬性
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  // 設置認證標頭
  const setAuthHeader = (authToken) => {
    if (authToken) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
      localStorage.setItem('token', authToken)
    } else {
      delete axios.defaults.headers.common['Authorization']
      localStorage.removeItem('token')
    }
  }

  // 初始化認證狀態
  const initAuth = () => {
    if (token.value) {
      setAuthHeader(token.value)
    }
  }

  // 登入
  const login = async (credentials) => {
    loading.value = true
    try {
      const response = await axios.post('/api/auth/login', credentials)
      const { user: userData, token: authToken } = response.data.data
      
      user.value = userData
      token.value = authToken
      setAuthHeader(authToken)
      
      return response.data
    } catch (error) {
      throw error.response?.data || error
    } finally {
      loading.value = false
    }
  }

  // 登出
  const logout = async () => {
    try {
      if (token.value) {
        await axios.post('/api/auth/logout')
      }
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      token.value = null
      setAuthHeader(null)
    }
  }

  // 獲取用戶資訊
  const fetchUser = async () => {
    if (!token.value) return null
    
    try {
      const response = await axios.get('/api/auth/me')
      user.value = response.data.data
      return user.value
    } catch (error) {
      // Token 可能已過期，清除認證狀態
      await logout()
      throw error
    }
  }

  // 更新個人資料
  const updateProfile = async (profileData) => {
    loading.value = true
    try {
      const response = await axios.put('/api/auth/profile', profileData)
      user.value = response.data.data
      return response.data
    } catch (error) {
      throw error.response?.data || error
    } finally {
      loading.value = false
    }
  }

  // 修改密碼
  const changePassword = async (passwordData) => {
    loading.value = true
    try {
      const response = await axios.post('/api/auth/change-password', passwordData)
      return response.data
    } catch (error) {
      throw error.response?.data || error
    } finally {
      loading.value = false
    }
  }

  return {
    // 狀態
    user,
    token,
    loading,
    
    // 計算屬性
    isAuthenticated,
    isAdmin,
    
    // 方法
    initAuth,
    login,
    logout,
    fetchUser,
    updateProfile,
    changePassword
  }
})
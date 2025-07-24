<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 導航欄 -->
    <NavBar />

    <!-- 主要內容 -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">人員管理</h2>
          <p class="text-gray-600">管理系統用戶資訊，包含辦公地點和分機設定</p>
        </div>

        <!-- 操作按鈕 -->
        <div class="mb-6 flex justify-between items-center">
          <div class="flex space-x-2">
            <select 
              v-model="filterLocation"
              class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">全部地點</option>
              <option value="taipei">台北</option>
              <option value="changhua">彰化</option>
            </select>
            <select 
              v-model="filterDepartment"
              class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">全部部門</option>
              <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
            </select>
          </div>
          <button 
            @click="showAddUserModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            新增人員
          </button>
        </div>

        <!-- 人員列表 -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li 
              v-for="user in filteredUsers" 
              :key="user.id"
              class="px-6 py-4 hover:bg-gray-50"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                      <span class="text-sm font-medium text-white">
                        {{ user.full_name?.charAt(0) || user.username?.charAt(0) }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="flex items-center">
                      <div class="text-sm font-medium text-gray-900">{{ user.full_name }}</div>
                      <span 
                        :class="[
                          'ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'
                        ]"
                      >
                        {{ user.role === 'admin' ? '管理員' : '用戶' }}
                      </span>
                      <span 
                        :class="[
                          'ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        ]"
                      >
                        {{ user.status === 'active' ? '啟用' : '停用' }}
                      </span>
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ user.department }} - {{ user.position }}
                    </div>
                    <div class="text-sm text-gray-500">
                      <span class="inline-flex items-center">
                        <svg class="mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        {{ user.office_location === 'taipei' ? '台北' : '彰化' }}
                      </span>
                      <span class="ml-4 inline-flex items-center">
                        <svg class="mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                        </svg>
                        分機: {{ user.extension || '無' }}
                      </span>
                      <span v-if="user.phone" class="ml-4">
                        電話: {{ user.phone }}
                      </span>
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button 
                    @click="editUser(user)"
                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                  >
                    編輯
                  </button>
                </div>
              </div>
            </li>
          </ul>
          
          <div v-if="filteredUsers.length === 0" class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M17 20H7m10 0v-2c0-5.523-4.477-10-10-10S-3 12.477-3 18v2m20 0H7m0 0H2v-2a3 3 0 015.196-2.121M7 20v-2m5-10a4 4 0 110-8 4 4 0 010 8z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">沒有找到人員</h3>
            <p class="mt-1 text-sm text-gray-500">請調整篩選條件或新增人員。</p>
          </div>
        </div>
      </div>
    </main>

    <!-- 新增/編輯用戶彈窗 -->
    <div v-if="showAddUserModal || editingUser" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ editingUser ? '編輯人員資訊' : '新增人員' }}
            </h3>
            <button 
              @click="closeModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="saveUser" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <label class="block text-sm font-medium text-gray-700">用戶名 *</label>
                <input 
                  v-model="userForm.username"
                  type="text" 
                  required
                  :disabled="!!editingUser"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">電子郵件 *</label>
                <input 
                  v-model="userForm.email"
                  type="email" 
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">姓名 *</label>
                <input 
                  v-model="userForm.full_name"
                  type="text" 
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">部門 *</label>
                <input 
                  v-model="userForm.department"
                  type="text" 
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">職位</label>
                <input 
                  v-model="userForm.position"
                  type="text" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">辦公地點</label>
                <select 
                  v-model="userForm.office_location"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="taipei">台北</option>
                  <option value="changhua">彰化</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">分機</label>
                <input 
                  v-model="userForm.extension"
                  type="text" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">電話</label>
                <input 
                  v-model="userForm.phone"
                  type="text" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">角色</label>
                <select 
                  v-model="userForm.role"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="user">用戶</option>
                  <option value="admin">管理員</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">狀態</label>
                <select 
                  v-model="userForm.status"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="active">啟用</option>
                  <option value="inactive">停用</option>
                </select>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
              <button 
                type="button"
                @click="closeModal"
                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                取消
              </button>
              <button 
                type="submit"
                :disabled="saving"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ saving ? '儲存中...' : '儲存' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import NavBar from '@/components/NavBar.vue'

const authStore = useAuthStore()

const users = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddUserModal = ref(false)
const editingUser = ref(null)
const filterLocation = ref('')
const filterDepartment = ref('')

const userForm = ref({
  username: '',
  email: '',
  full_name: '',
  department: '',
  position: '',
  office_location: 'taipei',
  extension: '',
  phone: '',
  role: 'user',
  status: 'active'
})

// 獲取唯一部門列表
const departments = computed(() => {
  const depts = [...new Set(users.value.map(user => user.department).filter(Boolean))]
  return depts.sort()
})

// 篩選用戶
const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const locationMatch = !filterLocation.value || user.office_location === filterLocation.value
    const departmentMatch = !filterDepartment.value || user.department === filterDepartment.value
    return locationMatch && departmentMatch
  })
})

// 載入用戶列表
const loadUsers = async () => {
  loading.value = true
  try {
    const response = await fetch('http://192.168.0.234:40001/api/users', {
      headers: {
        'Content-Type': 'application/json'
      }
    })
    if (response.ok) {
      const data = await response.json()
      users.value = data.data || []
    }
  } catch (error) {
    console.error('載入用戶失敗:', error)
  } finally {
    loading.value = false
  }
}

// 編輯用戶
const editUser = (user) => {
  editingUser.value = user
  userForm.value = { ...user }
}

// 關閉彈窗
const closeModal = () => {
  showAddUserModal.value = false
  editingUser.value = null
  userForm.value = {
    username: '',
    email: '',
    full_name: '',
    department: '',
    position: '',
    office_location: 'taipei',
    extension: '',
    phone: '',
    role: 'user',
    status: 'active'
  }
}

// 儲存用戶
const saveUser = async () => {
  saving.value = true
  try {
    const url = editingUser.value 
      ? `http://192.168.0.234:40001/api/users/${editingUser.value.id}`
      : 'http://192.168.0.234:40001/api/users'
    
    const method = editingUser.value ? 'PUT' : 'POST'
    
    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(userForm.value)
    })
    
    if (response.ok) {
      await loadUsers()
      closeModal()
    } else {
      const error = await response.json()
      console.error('儲存用戶失敗:', error)
      alert('儲存失敗: ' + (error.message || '未知錯誤'))
    }
  } catch (error) {
    console.error('儲存用戶失敗:', error)
    alert('儲存失敗: ' + error.message)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadUsers()
})
</script>
<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 導航欄 -->
    <NavBar />

    <!-- 主要內容 -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">設備分類管理</h2>
          <p class="text-gray-600">管理設備分類，包括新增、編輯和刪除分類</p>
        </div>

        <!-- 操作區塊 -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
              <div class="flex-1 min-w-0">
                <h3 class="text-lg leading-6 font-medium text-gray-900">分類清單</h3>
                <p class="mt-1 text-sm text-gray-500">目前共有 {{ categories.length }} 個分類</p>
              </div>
              <div class="mt-4 sm:mt-0 sm:ml-4">
                <button 
                  @click="showAddModal = true"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                  <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                  </svg>
                  新增分類
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 分類列表 -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div v-if="loading" class="text-center py-12">
              <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="mt-2 text-gray-500">載入中...</p>
            </div>
            <div v-else class="overflow-hidden">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      ID
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      分類名稱
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      設備數量
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      操作
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="category in categories" :key="category.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ category.id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ category.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ category.equipment_count || 0 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button 
                        @click="editCategory(category)"
                        class="text-indigo-600 hover:text-indigo-900 mr-4"
                      >
                        編輯
                      </button>
                      <button 
                        @click="deleteCategory(category)"
                        class="text-red-600 hover:text-red-900"
                        :disabled="category.equipment_count > 0"
                        :class="category.equipment_count > 0 ? 'opacity-50 cursor-not-allowed' : ''"
                      >
                        刪除
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="!loading" class="mt-8 pt-6 border-t border-gray-200">
              <router-link 
                to="/equipment/create" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                返回設備表單
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- 新增分類 Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">新增設備分類</h3>
          <form @submit.prevent="addCategory">
            <div class="mb-4">
              <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-2">分類名稱</label>
              <input 
                v-model="newCategoryName"
                type="text" 
                id="categoryName" 
                required
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="請輸入分類名稱"
              />
            </div>
            <div class="flex justify-end space-x-3">
              <button 
                type="button" 
                @click="showAddModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                取消
              </button>
              <button 
                type="submit"
                :disabled="!newCategoryName.trim() || saving"
                class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ saving ? '新增中...' : '新增' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- 編輯分類 Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">編輯設備分類</h3>
          <form @submit.prevent="updateCategory">
            <div class="mb-4">
              <label for="editCategoryName" class="block text-sm font-medium text-gray-700 mb-2">分類名稱</label>
              <input 
                v-model="editingCategory.name"
                type="text" 
                id="editCategoryName" 
                required
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="請輸入分類名稱"
              />
            </div>
            <div class="flex justify-end space-x-3">
              <button 
                type="button" 
                @click="showEditModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                取消
              </button>
              <button 
                type="submit"
                :disabled="!editingCategory.name.trim() || saving"
                class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ saving ? '更新中...' : '更新' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NavBar from '@/components/NavBar.vue'

const router = useRouter()
const authStore = useAuthStore()
const user = computed(() => authStore.user)

// Modal 狀態
const showAddModal = ref(false)
const showEditModal = ref(false)
const newCategoryName = ref('')
const editingCategory = ref({})

// 載入狀態
const loading = ref(false)
const saving = ref(false)

// 從API載入的設備分類
const categories = ref([])

// 載入分類清單
const loadCategories = async () => {
  loading.value = true
  try {
    const response = await fetch('http://192.168.0.234:40001/api/categories', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      categories.value = data.data || []
    } else {
      console.error('載入分類失敗')
    }
  } catch (error) {
    console.error('載入分類失敗:', error)
  } finally {
    loading.value = false
  }
}

const addCategory = async () => {
  if (!newCategoryName.value.trim()) return
  
  saving.value = true
  try {
    const response = await fetch('http://192.168.0.234:40001/api/categories', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: newCategoryName.value.trim()
      })
    })
    
    const data = await response.json()
    
    if (response.ok && data.success) {
      categories.value.push(data.data)
      newCategoryName.value = ''
      showAddModal.value = false
      alert('分類新增成功')
    } else {
      alert(data.message || '分類新增失敗')
    }
  } catch (error) {
    console.error('新增分類失敗:', error)
    alert('分類新增失敗')
  } finally {
    saving.value = false
  }
}

const editCategory = (category) => {
  editingCategory.value = { ...category }
  showEditModal.value = true
}

const updateCategory = async () => {
  if (!editingCategory.value.name.trim()) return
  
  saving.value = true
  try {
    const response = await fetch(`http://192.168.0.234:40001/api/categories/${editingCategory.value.id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: editingCategory.value.name.trim()
      })
    })
    
    const data = await response.json()
    
    if (response.ok && data.success) {
      const index = categories.value.findIndex(c => c.id === editingCategory.value.id)
      if (index !== -1) {
        categories.value[index] = data.data
      }
      showEditModal.value = false
      editingCategory.value = {}
      alert('分類更新成功')
    } else {
      alert(data.message || '分類更新失敗')
    }
  } catch (error) {
    console.error('更新分類失敗:', error)
    alert('分類更新失敗')
  } finally {
    saving.value = false
  }
}

const deleteCategory = async (category) => {
  if (category.equipment_count > 0) {
    alert('此分類下還有設備，無法刪除')
    return
  }
  
  if (!confirm(`確定要刪除分類「${category.name}」嗎？`)) {
    return
  }
  
  try {
    const response = await fetch(`http://192.168.0.234:40001/api/categories/${category.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })
    
    const data = await response.json()
    
    if (response.ok && data.success) {
      const index = categories.value.findIndex(c => c.id === category.id)
      if (index !== -1) {
        categories.value.splice(index, 1)
      }
      alert('分類刪除成功')
    } else {
      alert(data.message || '分類刪除失敗')
    }
  } catch (error) {
    console.error('刪除分類失敗:', error)
    alert('分類刪除失敗')
  }
}

// 頁面載入時取得分類清單
onMounted(() => {
  loadCategories()
})
</script>
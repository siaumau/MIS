<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 導航欄 -->
    <NavBar />

    <!-- 主要內容 -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">新增設備</h2>
          <p class="text-gray-600">新增設備資訊到系統中</p>
        </div>

        <!-- 設備表單 -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">設備名稱 *</label>
                  <input
                    v-model="form.name"
                    type="text"
                    id="name"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="請輸入設備名稱"
                  />
                </div>

                <div>
                  <label for="category" class="block text-sm font-medium text-gray-700">設備分類</label>
                  <div class="flex">
                    <select
                      v-model="form.category_id"
                      id="category"
                      class="mt-1 block w-full border-gray-300 rounded-l-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                      <option value="">請選擇分類</option>
                      <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                    <router-link
                      to="/equipment/categories"
                      class="mt-1 inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 hover:bg-gray-100"
                      title="管理分類"
                    >
                      <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                      </svg>
                    </router-link>
                  </div>
                </div>

                <div>
                  <label for="brand" class="block text-sm font-medium text-gray-700">品牌</label>
                  <input
                    v-model="form.brand"
                    type="text"
                    id="brand"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="例：Dell"
                  />
                </div>

                <div>
                  <label for="model" class="block text-sm font-medium text-gray-700">型號</label>
                  <input
                    v-model="form.model"
                    type="text"
                    id="model"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="例：OptiPlex 7090"
                  />
                </div>

                <div>
                  <label for="serial_number" class="block text-sm font-medium text-gray-700">序號</label>
                  <input
                    v-model="form.serial_number"
                    type="text"
                    id="serial_number"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>

                <div>
                  <label for="property_number" class="block text-sm font-medium text-gray-700">財產編號</label>
                  <input
                    v-model="form.property_number"
                    type="text"
                    id="property_number"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>

                <div>
                  <label for="ip_address" class="block text-sm font-medium text-gray-700">IP位址</label>
                  <input
                    v-model="form.ip_address"
                    type="text"
                    id="ip_address"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="例：192.168.1.100"
                  />
                </div>

                <div>
                  <label for="mac_address" class="block text-sm font-medium text-gray-700">MAC位址</label>
                  <input
                    v-model="form.mac_address"
                    type="text"
                    id="mac_address"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="例：00:11:22:33:44:55"
                  />
                </div>

                <div>
                  <label for="office_location" class="block text-sm font-medium text-gray-700">設備地點</label>
                  <select
                    v-model="form.office_location"
                    id="office_location"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  >
                    <option value="">請選擇設備地點</option>
                    <option value="taipei">台北</option>
                    <option value="changhua">彰化</option>
                  </select>
                </div>

                <div>
                  <label for="location" class="block text-sm font-medium text-gray-700">詳細位置</label>
                  <input
                    v-model="form.location"
                    type="text"
                    id="location"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="例：3樓辦公室A區"
                  />
                </div>

                <div>
                  <label for="responsible_user_id" class="block text-sm font-medium text-gray-700">負責人</label>
                  <div class="flex">
                    <select
                      v-model="form.responsible_user_id"
                      id="responsible_user_id"
                      class="mt-1 block w-full border-gray-300 rounded-l-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :disabled="usersLoading"
                    >
                      <option value="">請選擇負責人</option>
                      <optgroup v-for="location in groupedUsers" :key="location.name" :label="location.name">
                        <option v-for="user in location.users" :key="user.id" :value="user.id">
                          {{ user.display_name }} (分機: {{ user.extension || '無' }})
                        </option>
                      </optgroup>
                    </select>
                    <router-link
                      to="/users/manage"
                      class="mt-1 inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 hover:bg-gray-100"
                      title="管理人員"
                    >
                      <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </router-link>
                  </div>
                  <div v-if="selectedUserInfo" class="mt-2 text-sm text-gray-600">
                    {{ selectedUserInfo.full_name }} - {{ selectedUserInfo.department }} ({{ selectedUserInfo.office_location_text }})
                    <br>
                    電話: {{ selectedUserInfo.phone || '無' }} | 分機: {{ selectedUserInfo.extension || '無' }}
                  </div>
                </div>

                <div>
                  <label for="purchase_date" class="block text-sm font-medium text-gray-700">購買日期</label>
                  <input
                    v-model="form.purchase_date"
                    type="date"
                    id="purchase_date"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>

                <div>
                  <label for="price" class="block text-sm font-medium text-gray-700">價格</label>
                  <input
                    v-model="form.price"
                    type="number"
                    id="price"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>
              </div>

              <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">備註</label>
                <textarea
                  v-model="form.notes"
                  id="notes"
                  rows="4"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="其他相關資訊"
                ></textarea>
              </div>

              <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <router-link
                  to="/equipment"
                  class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  取消
                </router-link>
                <button
                  type="submit"
                  :disabled="loading"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                  <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ loading ? '儲存中...' : '儲存設備' }}
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { api } from '@/utils/apiClient'
import NavBar from '@/components/NavBar.vue'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const loading = ref(false)
const categoriesLoading = ref(false)
const usersLoading = ref(false)

// 從API載入的設備分類
const categories = ref([])

// 從API載入的用戶列表
const users = ref([])

// 載入設備分類
const loadCategories = async () => {
  categoriesLoading.value = true
  try {
    const response = await fetch('http://192.168.2.56:40001/api/categories', {
      headers: {
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      categories.value = data.data || []
    } else {
      // 如果API失敗，使用預設分類
      categories.value = [
        { id: 1, name: '桌上型電腦' },
        { id: 2, name: '筆記型電腦' },
        { id: 3, name: '伺服器' },
        { id: 4, name: '螢幕/顯示器' },
        { id: 5, name: '印表機' },
        { id: 6, name: '掃描器' },
        { id: 7, name: '網路設備' },
        { id: 8, name: '交換器(Switch)' },
        { id: 9, name: '路由器(Router)' },
        { id: 10, name: '無線基地台(AP)' },
        { id: 11, name: '防火牆' },
        { id: 12, name: 'UPS不斷電系統' },
        { id: 13, name: '儲存設備' },
        { id: 14, name: '投影機' },
        { id: 15, name: '電話設備' },
        { id: 16, name: '攝影機' },
        { id: 17, name: '麥克風/音響' },
        { id: 18, name: '其他週邊設備' }
      ]
    }
  } catch (error) {
    console.error('載入分類失敗:', error)
    // 載入失敗時使用預設分類
    categories.value = [
      { id: 1, name: '桌上型電腦' },
      { id: 2, name: '筆記型電腦' },
      { id: 3, name: '伺服器' },
      { id: 4, name: '螢幕/顯示器' },
      { id: 5, name: '印表機' },
      { id: 6, name: '掃描器' },
      { id: 7, name: '網路設備' },
      { id: 8, name: '交換器(Switch)' },
      { id: 9, name: '路由器(Router)' },
      { id: 10, name: '無線基地台(AP)' },
      { id: 11, name: '防火牆' },
      { id: 12, name: 'UPS不斷電系統' },
      { id: 13, name: '儲存設備' },
      { id: 14, name: '投影機' },
      { id: 15, name: '電話設備' },
      { id: 16, name: '攝影機' },
      { id: 17, name: '麥克風/音響' },
      { id: 18, name: '其他週邊設備' }
    ]
  } finally {
    categoriesLoading.value = false
  }
}

// 載入用戶列表
const loadUsers = async () => {
  usersLoading.value = true
  try {
    const response = await api.get('/users')

    if (response.ok) {
      const data = await response.json()
      users.value = data.data || []
    } else {
      console.error('載入用戶失敗:', response.statusText)
    }
  } catch (error) {
    console.error('載入用戶失敗:', error)
  } finally {
    usersLoading.value = false
  }
}

// 按辦公地點分組用戶
const groupedUsers = computed(() => {
  const grouped = users.value.reduce((acc, user) => {
    const locationKey = user.office_location
    const locationName = locationKey === 'taipei' ? '台北辦公室' : '彰化辦公室'

    if (!acc[locationKey]) {
      acc[locationKey] = {
        name: locationName,
        users: []
      }
    }
    acc[locationKey].users.push(user)
    return acc
  }, {})

  return Object.values(grouped)
})

// 選中的用戶詳細資訊
const selectedUserInfo = computed(() => {
  if (!form.value.responsible_user_id) return null
  return users.value.find(user => user.id == form.value.responsible_user_id)
})

const form = ref({
  name: '',
  category_id: '',
  brand: '',
  model: '',
  serial_number: '',
  property_number: '',
  ip_address: '',
  mac_address: '',
  office_location: '',
  location: '',
  responsible_user_id: '',
  purchase_date: '',
  warranty_expiry: '',
  status: 'active',
  notes: ''
})

const handleSubmit = async () => {
  loading.value = true
  try {
    // TODO: 實現提交邏輯
    console.log('提交設備表單:', form.value)
    // 模擬API請求
    await new Promise(resolve => setTimeout(resolve, 1000))
    router.push('/equipment')
  } catch (error) {
    console.error('提交失敗:', error)
  } finally {
    loading.value = false
  }
}

// 頁面載入時取得分類清單和用戶列表
onMounted(() => {
  loadCategories()
  loadUsers()
})
</script>

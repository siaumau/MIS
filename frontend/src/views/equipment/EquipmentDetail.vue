<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 導航欄 -->
    <NavBar />

    <!-- 主要內容 -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">設備詳情</h2>
          <p class="text-gray-600">查看設備的詳細資訊</p>
        </div>

        <!-- 設備資訊 -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div v-if="loading" class="text-center py-12">
              <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="mt-2 text-gray-500">載入中...</p>
            </div>

            <div v-else>
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h3 class="text-lg leading-6 font-medium text-gray-900">{{ equipment.name || '設備名稱' }}</h3>
                  <p class="mt-1 text-sm text-gray-500">設備 ID: {{ $route.params.id }}</p>
                </div>
                <div class="flex space-x-3">
                  <button class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    編輯
                  </button>
                  <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                    刪除
                  </button>
                </div>
              </div>

              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500">設備名稱</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.name || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">分類</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.category || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">品牌</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.brand || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">型號</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.model || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">序號</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.serial_number || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">財產編號</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.property_number || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">IP位址</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.ip_address || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">MAC位址</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.mac_address || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">位置</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.location || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">負責人</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.responsible_person || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">購買日期</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.purchase_date || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">價格</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.price ? `$${equipment.price}` : '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">狀態</dt>
                  <dd class="mt-1">
                    <span :class="getStatusClass(equipment.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                      {{ getStatusText(equipment.status) }}
                    </span>
                  </dd>
                </div>
                <div class="sm:col-span-2" v-if="equipment.notes">
                  <dt class="text-sm font-medium text-gray-500">備註</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ equipment.notes }}</dd>
                </div>
              </dl>

              <div class="mt-8 pt-6 border-t border-gray-200">
                <router-link 
                  to="/equipment" 
                  class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                  <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  返回設備列表
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import NavBar from '@/components/NavBar.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const loading = ref(false)

// 模擬設備資料
const equipment = ref({
  name: '辦公室電腦',
  category: '桌上型電腦', 
  brand: 'Dell',
  model: 'OptiPlex 7090',
  serial_number: 'ABC123456',
  property_number: 'IT001',
  ip_address: '192.168.1.100',
  mac_address: '00:11:22:33:44:55',
  location: '3樓辦公室',
  responsible_person: '張三',
  purchase_date: '2024-01-15',
  price: 25000,
  status: 'active',
  notes: '主要用於日常辦公作業'
})

const loadEquipment = async () => {
  loading.value = true
  try {
    // TODO: 實現API調用
    await new Promise(resolve => setTimeout(resolve, 500))
  } catch (error) {
    console.error('載入設備失敗:', error)
  } finally {
    loading.value = false
  }
}

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    maintenance: 'bg-yellow-100 text-yellow-800',
    decommissioned: 'bg-gray-100 text-gray-800',
    lost: 'bg-red-100 text-red-800'
  }
  return classes[status] || classes.active
}

const getStatusText = (status) => {
  const texts = {
    active: '正常',
    maintenance: '維護中',
    decommissioned: '已停用',
    lost: '遺失'
  }
  return texts[status] || '正常'
}

onMounted(() => {
  loadEquipment()
})
</script>
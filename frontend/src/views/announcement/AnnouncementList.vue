<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 導航欄 -->
    <NavBar />

    <!-- 主要內容 -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">資安公告</h2>
          <p class="text-gray-600">查看所有資訊安全相關公告與重要通知</p>
        </div>

        <!-- 篩選區塊 -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div>
                <label for="priority-filter" class="block text-sm font-medium text-gray-700">優先級</label>
                <select 
                  v-model="filters.priority"
                  id="priority-filter" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option value="">全部</option>
                  <option value="low">低</option>
                  <option value="medium">中</option>
                  <option value="high">高</option>
                  <option value="urgent">緊急</option>
                </select>
              </div>
              
              <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700">狀態</label>
                <select 
                  v-model="filters.status"
                  id="status-filter" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option value="">全部</option>
                  <option value="published">已發布</option>
                  <option value="archived">已封存</option>
                </select>
              </div>

              <div>
                <label for="search" class="block text-sm font-medium text-gray-700">搜尋</label>
                <input 
                  v-model="filters.search"
                  type="text" 
                  id="search" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  placeholder="搜尋標題或內容"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- 公告列表 -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div v-if="loading" class="text-center py-12">
              <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="mt-2 text-gray-500">載入中...</p>
            </div>

            <div v-else-if="announcements.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">目前沒有公告</h3>
              <p class="mt-1 text-sm text-gray-500">尚無符合條件的公告內容</p>
            </div>

            <div v-else class="space-y-4">
              <div 
                v-for="announcement in announcements" 
                :key="announcement.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                @click="viewAnnouncement(announcement.id)"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                      <span 
                        :class="getPriorityClass(announcement.priority)"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      >
                        {{ getPriorityText(announcement.priority) }}
                      </span>
                      <span 
                        v-if="announcement.requires_acknowledgment && !announcement.is_acknowledged"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                      >
                        需確認
                      </span>
                    </div>
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                      {{ announcement.title }}
                    </h3>
                    
                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                      {{ announcement.content }}
                    </p>
                    
                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                      <span>發布日期: {{ formatDate(announcement.publish_date) }}</span>
                      <span v-if="announcement.expire_date">
                        到期日期: {{ formatDate(announcement.expire_date) }}
                      </span>
                    </div>
                  </div>
                  
                  <div class="ml-4 flex-shrink-0">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- 分頁 -->
            <div v-if="announcements.length > 0" class="mt-6 flex items-center justify-between border-t border-gray-200 pt-6">
              <div class="flex flex-1 justify-between sm:hidden">
                <button 
                  @click="previousPage"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                >
                  上一頁
                </button>
                <button 
                  @click="nextPage"
                  :disabled="currentPage === totalPages"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                >
                  下一頁
                </button>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    顯示第 <span class="font-medium">{{ startIndex }}</span> 到 
                    <span class="font-medium">{{ endIndex }}</span> 項，
                    共 <span class="font-medium">{{ totalCount }}</span> 項
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button 
                      @click="previousPage"
                      :disabled="currentPage === 1"
                      class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                    >
                      上一頁
                    </button>
                    <button 
                      @click="nextPage"
                      :disabled="currentPage === totalPages"
                      class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                    >
                      下一頁
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import NavBar from '@/components/NavBar.vue'

const router = useRouter()
const loading = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const totalCount = ref(0)

const filters = ref({
  priority: '',
  status: '',
  search: ''
})

// 模擬公告資料
const announcements = ref([
  {
    id: 1,
    title: '重要：系統維護通知',
    content: '本系統將於本週末進行例行維護，期間可能會暫時中斷服務。請提前保存重要資料，避免資料遺失。維護期間如有緊急需求，請聯繫IT部門。',
    priority: 'high',
    status: 'published',
    requires_acknowledgment: true,
    is_acknowledged: false,
    publish_date: '2024-12-01T10:00:00Z',
    expire_date: '2024-12-31T23:59:59Z'
  },
  {
    id: 2,
    title: '資安政策更新',
    content: '公司資安政策已更新，新增密碼複雜度要求和多因子認證規範。所有員工請詳閱新政策並配合執行。',
    priority: 'medium',
    status: 'published',
    requires_acknowledgment: true,
    is_acknowledged: true,
    publish_date: '2024-11-28T09:00:00Z',
    expire_date: null
  },
  {
    id: 3,
    title: '新版本功能介紹',
    content: '系統新版本已上線，新增設備管理和報修流程優化功能。歡迎大家使用並提供意見回饋。',
    priority: 'low',
    status: 'published',
    requires_acknowledgment: false,
    is_acknowledged: false,
    publish_date: '2024-11-25T14:30:00Z',
    expire_date: null
  }
])

const totalPages = computed(() => Math.ceil(totalCount.value / perPage.value))
const startIndex = computed(() => (currentPage.value - 1) * perPage.value + 1)
const endIndex = computed(() => Math.min(currentPage.value * perPage.value, totalCount.value))

const loadAnnouncements = async () => {
  loading.value = true
  try {
    // TODO: 實現API調用
    await new Promise(resolve => setTimeout(resolve, 500))
    totalCount.value = announcements.value.length
  } catch (error) {
    console.error('載入公告失敗:', error)
  } finally {
    loading.value = false
  }
}

const viewAnnouncement = (id) => {
  router.push(`/announcements/${id}`)
}

const getPriorityClass = (priority) => {
  const classes = {
    low: 'bg-gray-100 text-gray-800',
    medium: 'bg-blue-100 text-blue-800',
    high: 'bg-yellow-100 text-yellow-800',
    urgent: 'bg-red-100 text-red-800'
  }
  return classes[priority] || classes.medium
}

const getPriorityText = (priority) => {
  const texts = {
    low: '低',
    medium: '中',
    high: '高',
    urgent: '緊急'
  }
  return texts[priority] || '中'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    loadAnnouncements()
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    loadAnnouncements()
  }
}


// 監聽篩選條件變化
watch(filters, () => {
  currentPage.value = 1
  loadAnnouncements()
}, { deep: true })

onMounted(() => {
  loadAnnouncements()
})
</script>
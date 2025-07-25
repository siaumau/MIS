<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 導航欄 -->
    <NavBar />

    <!-- 主要內容 -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">定期付款管理</h2>
          <p class="text-gray-600">管理服務費用和續費提醒，追蹤各種軟體、服務的訂閱狀態</p>
        </div>

        <!-- 付款提醒日曆 -->
        <div class="mb-8">
          <PaymentCalendar :payments="payments" />
        </div>

        <!-- 操作按鈕和篩選 -->
        <div class="mb-6 flex justify-between items-center">
          <div class="flex space-x-2">
            <select 
              v-model="filterPaymentMethod"
              class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">全部付款方式</option>
              <option value="個人先支出">個人先支出</option>
              <option value="我的Paypal(中信卡)">我的Paypal(中信卡)</option>
              <option value="我的中信卡">我的中信卡</option>
              <option value="新光商務卡 5304">新光商務卡 5304</option>
            </select>
            
            <select 
              v-model="filterCurrency"
              class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">全部幣別</option>
              <option value="USD">USD</option>
              <option value="TWD">TWD</option>
            </select>

            <select 
              v-model="filterStatus"
              class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">全部狀態</option>
              <option value="active">啟用中</option>
              <option value="expired">已過期</option>
              <option value="cancelled">已取消</option>
            </select>
          </div>
          
          <button 
            @click="showAddPaymentModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            新增付款項目
          </button>
        </div>

        <!-- 付款列表 -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">項次</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名稱</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">購買來源</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">帳密</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">週期</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">付款方式</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">金額</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">狀態</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr 
                  v-for="payment in filteredPayments" 
                  :key="payment.id"
                  :class="{'bg-red-50': isExpiringSoon(payment.end_date)}"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ payment.item_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ payment.name }}</div>
                    <div class="text-sm text-gray-500">{{ payment.source }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <a 
                      v-if="payment.purchase_url" 
                      :href="payment.purchase_url" 
                      target="_blank"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      {{ payment.vendor }}
                    </a>
                    <span v-else>{{ payment.vendor }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div v-if="payment.account_info">
                      <div class="truncate max-w-32" :title="payment.account_info">
                        {{ payment.account_info }}
                      </div>
                    </div>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <div class="flex flex-col">
                      <span>{{ formatDateRange(payment.start_date, payment.end_date) }}</span>
                      <span class="text-xs text-gray-500">{{ payment.billing_cycle }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span 
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        getPaymentMethodClass(payment.payment_method)
                      ]"
                    >
                      {{ payment.payment_method }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <div class="flex flex-col">
                      <span v-if="payment.amount_usd" class="text-green-600 font-medium">
                        ${{ payment.amount_usd }} USD
                      </span>
                      <span v-if="payment.amount_twd" class="text-blue-600 font-medium">
                        ${{ payment.amount_twd.toLocaleString() }} TWD
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        getStatusClass(payment.status)
                      ]"
                    >
                      {{ getStatusText(payment.status) }}
                    </span>
                    <div v-if="isExpiringSoon(payment.end_date)" class="mt-1">
                      <span class="text-xs text-red-600 font-medium">即將到期</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <button 
                      @click="editPayment(payment)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      編輯
                    </button>
                    <button 
                      @click="deletePayment(payment)"
                      class="text-red-600 hover:text-red-900"
                    >
                      刪除
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div v-if="filteredPayments.length === 0" class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">沒有找到付款記錄</h3>
            <p class="mt-1 text-sm text-gray-500">請調整篩選條件或新增付款項目。</p>
          </div>
        </div>
      </div>
    </main>

    <!-- 新增/編輯付款彈窗 -->
    <div v-if="showAddPaymentModal || editingPayment" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ editingPayment ? '編輯付款項目' : '新增付款項目' }}
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
          
          <form @submit.prevent="savePayment" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <label class="block text-sm font-medium text-gray-700">項次 *</label>
                <input 
                  v-model="paymentForm.item_number"
                  type="number" 
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">名稱 *</label>
                <input 
                  v-model="paymentForm.name"
                  type="text" 
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">購買來源</label>
                <input 
                  v-model="paymentForm.vendor"
                  type="text" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">購買網址</label>
                <input 
                  v-model="paymentForm.purchase_url"
                  type="url" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">帳密資訊</label>
                <textarea 
                  v-model="paymentForm.account_info"
                  rows="2"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="帳號、密碼或其他登入資訊"
                ></textarea>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">開始日期 *</label>
                <input 
                  v-model="paymentForm.start_date"
                  type="date" 
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">結束日期 *</label>
                <input 
                  v-model="paymentForm.end_date"
                  type="date" 
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">計費週期</label>
                <select 
                  v-model="paymentForm.billing_cycle"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="年">年</option>
                  <option value="月">月</option>
                  <option value="季">季</option>
                  <option value="半年">半年</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">付款方式 *</label>
                <select 
                  v-model="paymentForm.payment_method"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="">請選擇付款方式</option>
                  <option value="個人先支出">個人先支出</option>
                  <option value="我的Paypal(中信卡)">我的Paypal(中信卡)</option>
                  <option value="我的中信卡">我的中信卡</option>
                  <option value="新光商務卡 5304">新光商務卡 5304</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">USD 金額</label>
                <input 
                  v-model.number="paymentForm.amount_usd"
                  type="number" 
                  step="0.01"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">TWD 金額</label>
                <input 
                  v-model.number="paymentForm.amount_twd"
                  type="number" 
                  step="1"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">狀態</label>
                <select 
                  v-model="paymentForm.status"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="active">啟用中</option>
                  <option value="expired">已過期</option>
                  <option value="cancelled">已取消</option>
                </select>
              </div>
              
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">備註</label>
                <textarea 
                  v-model="paymentForm.notes"
                  rows="3"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                ></textarea>
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
import { api } from '@/utils/apiClient'
import NavBar from '@/components/NavBar.vue'
import PaymentCalendar from '@/components/PaymentCalendar.vue'

const authStore = useAuthStore()

const payments = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddPaymentModal = ref(false)
const editingPayment = ref(null)
const filterPaymentMethod = ref('')
const filterCurrency = ref('')
const filterStatus = ref('')

const paymentForm = ref({
  item_number: '',
  name: '',
  vendor: '',
  purchase_url: '',
  account_info: '',
  start_date: '',
  end_date: '',
  billing_cycle: '年',
  payment_method: '',
  amount_usd: null,
  amount_twd: null,
  status: 'active',
  notes: ''
})

// 篩選付款記錄
const filteredPayments = computed(() => {
  return payments.value.filter(payment => {
    const methodMatch = !filterPaymentMethod.value || payment.payment_method === filterPaymentMethod.value
    const currencyMatch = !filterCurrency.value || 
      (filterCurrency.value === 'USD' && payment.amount_usd) ||
      (filterCurrency.value === 'TWD' && payment.amount_twd)
    const statusMatch = !filterStatus.value || payment.status === filterStatus.value
    return methodMatch && currencyMatch && statusMatch
  })
})

// 檢查是否即將到期（30天內）
const isExpiringSoon = (endDate) => {
  if (!endDate) return false
  const end = new Date(endDate)
  const now = new Date()
  const thirtyDaysFromNow = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000)
  return end <= thirtyDaysFromNow && end >= now
}

// 格式化日期範圍
const formatDateRange = (startDate, endDate) => {
  const start = new Date(startDate).toLocaleDateString('zh-TW')
  const end = new Date(endDate).toLocaleDateString('zh-TW')
  return `${start} ~ ${end}`
}

// 獲取付款方式樣式
const getPaymentMethodClass = (method) => {
  const classes = {
    '個人先支出': 'bg-yellow-100 text-yellow-800',
    '我的Paypal(中信卡)': 'bg-blue-100 text-blue-800',
    '我的中信卡': 'bg-green-100 text-green-800',
    '新光商務卡 5304': 'bg-purple-100 text-purple-800'
  }
  return classes[method] || 'bg-gray-100 text-gray-800'
}

// 獲取狀態樣式
const getStatusClass = (status) => {
  const classes = {
    'active': 'bg-green-100 text-green-800',
    'expired': 'bg-red-100 text-red-800',
    'cancelled': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

// 獲取狀態文字
const getStatusText = (status) => {
  const texts = {
    'active': '啟用中',
    'expired': '已過期',
    'cancelled': '已取消'
  }
  return texts[status] || status
}

// 載入付款記錄
const loadPayments = async () => {
  loading.value = true
  try {
    const response = await api.get('/payments')
    const data = response.data
    payments.value = data.data || []
  } catch (error) {
    console.error('載入付款記錄失敗:', error)
  } finally {
    loading.value = false
  }
}

// 編輯付款記錄
const editPayment = (payment) => {
  editingPayment.value = payment
  paymentForm.value = { ...payment }
}

// 刪除付款記錄
const deletePayment = async (payment) => {
  if (!confirm(`確定要刪除「${payment.name}」嗎？`)) return
  
  try {
    await api.delete(`/payments/${payment.id}`)
    await loadPayments()
    alert('刪除成功！')
  } catch (error) {
    console.error('刪除失敗:', error)
    alert('刪除失敗: ' + error.message)
  }
}

// 關閉彈窗
const closeModal = () => {
  showAddPaymentModal.value = false
  editingPayment.value = null
  paymentForm.value = {
    item_number: '',
    name: '',
    vendor: '',
    purchase_url: '',
    account_info: '',
    start_date: '',
    end_date: '',
    billing_cycle: '年',
    payment_method: '',
    amount_usd: null,
    amount_twd: null,
    status: 'active',
    notes: ''
  }
}

// 儲存付款記錄
const savePayment = async () => {
  saving.value = true
  try {
    const isEdit = !!editingPayment.value
    
    if (editingPayment.value) {
      await api.put(`/payments/${editingPayment.value.id}`, paymentForm.value)
    } else {
      await api.post('/payments', paymentForm.value)
    }
    await loadPayments()
    closeModal()
    
    const action = isEdit ? '更新' : '新增'
    alert(`${action}付款記錄成功！`)
  } catch (error) {
    console.error('儲存付款記錄失敗:', error)
    
    if (error.response && error.response.data) {
      const errorData = error.response.data
      let errorMessage = '儲存失敗'
      
      if (errorData.errors && errorData.errors.length > 0) {
        errorMessage = errorData.errors[0]
      } else {
        errorMessage = errorData.message || '儲存失敗，請稍後再試'
      }
      
      alert(errorMessage)
    } else {
      alert('儲存失敗: ' + error.message)
    }
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadPayments()
})
</script>
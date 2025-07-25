<template>
  <div class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">付款提醒日曆</h3>
      <div class="flex items-center space-x-2">
        <button 
          @click="previousMonth"
          class="p-1 text-gray-400 hover:text-gray-600"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <span class="text-sm font-medium text-gray-900 min-w-32 text-center">
          {{ currentDate.getFullYear() }}年 {{ currentDate.getMonth() + 1 }}月
        </span>
        <button 
          @click="nextMonth"
          class="p-1 text-gray-400 hover:text-gray-600"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>

    <!-- 日曆格子 -->
    <div class="grid grid-cols-7 gap-1 text-center text-xs">
      <!-- 星期標題 -->
      <div v-for="day in weekDays" :key="day" class="py-2 font-medium text-gray-500">
        {{ day }}
      </div>
      
      <!-- 日期格子 -->
      <div 
        v-for="(date, index) in calendarDates" 
        :key="index" 
        :class="[
          'relative p-2 h-20 border border-gray-100',
          date.isCurrentMonth ? 'bg-white' : 'bg-gray-50 text-gray-400',
          date.isToday ? 'bg-blue-50 border-blue-200' : '',
          date.payments.length > 0 ? 'hover:bg-yellow-50' : ''
        ]"
      >
        <div class="text-sm font-medium">{{ date.date }}</div>
        
        <!-- 付款提醒點 -->
        <div v-if="date.payments.length > 0" class="absolute top-1 right-1">
          <div 
            :class="[
              'w-2 h-2 rounded-full',
              getPaymentDotColor(date.payments)
            ]"
            :title="`${date.payments.length} 項付款到期`"
          ></div>
        </div>
        
        <!-- 付款項目簡要 -->
        <div v-if="date.payments.length > 0" class="mt-1 space-y-1">
          <div 
            v-for="payment in date.payments.slice(0, 2)" 
            :key="payment.id"
            :class="[
              'text-xs px-1 py-0.5 rounded truncate',
              getPaymentItemClass(payment)
            ]"
            :title="payment.name"
          >
            {{ payment.name }}
          </div>
          <div 
            v-if="date.payments.length > 2" 
            class="text-xs text-gray-500"
          >
            +{{ date.payments.length - 2 }} 項
          </div>
        </div>
      </div>
    </div>

    <!-- 圖例 -->
    <div class="mt-4 pt-4 border-t border-gray-200">
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-1">
            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
            <span class="text-gray-600">即將到期 (7天內)</span>
          </div>
          <div class="flex items-center space-x-1">
            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
            <span class="text-gray-600">近期到期 (30天內)</span>
          </div>
          <div class="flex items-center space-x-1">
            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
            <span class="text-gray-600">正常</span>
          </div>
        </div>
        <div class="text-gray-500">
          本月到期: {{ monthlyExpiringCount }} 項
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  payments: {
    type: Array,
    default: () => []
  }
})

const currentDate = ref(new Date())
const weekDays = ['日', '一', '二', '三', '四', '五', '六']

// 切換月份
const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
}

// 生成日曆日期
const calendarDates = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const startDate = new Date(firstDay)
  
  // 調整到週日開始
  startDate.setDate(startDate.getDate() - startDate.getDay())
  
  const dates = []
  const currentDateObj = new Date(startDate)
  const today = new Date()
  
  // 生成6週的日期 (42天)
  for (let i = 0; i < 42; i++) {
    const dateStr = currentDateObj.toISOString().split('T')[0]
    const paymentsOnDate = getPaymentsOnDate(dateStr)
    
    dates.push({
      date: currentDateObj.getDate(),
      fullDate: new Date(currentDateObj),
      dateStr: dateStr,
      isCurrentMonth: currentDateObj.getMonth() === month,
      isToday: currentDateObj.toDateString() === today.toDateString(),
      payments: paymentsOnDate
    })
    
    currentDateObj.setDate(currentDateObj.getDate() + 1)
  }
  
  return dates
})

// 獲取指定日期的付款項目
const getPaymentsOnDate = (dateStr) => {
  return props.payments.filter(payment => {
    return payment.end_date === dateStr
  })
}

// 獲取付款點的顏色
const getPaymentDotColor = (payments) => {
  const now = new Date()
  const hasUrgent = payments.some(payment => {
    const endDate = new Date(payment.end_date)
    const diffDays = Math.ceil((endDate - now) / (1000 * 60 * 60 * 24))
    return diffDays <= 7 && diffDays >= 0
  })
  
  const hasWarning = payments.some(payment => {
    const endDate = new Date(payment.end_date)
    const diffDays = Math.ceil((endDate - now) / (1000 * 60 * 60 * 24))
    return diffDays <= 30 && diffDays > 7
  })
  
  if (hasUrgent) return 'bg-red-500'
  if (hasWarning) return 'bg-yellow-500'
  return 'bg-green-500'
}

// 獲取付款項目的樣式
const getPaymentItemClass = (payment) => {
  const now = new Date()
  const endDate = new Date(payment.end_date)
  const diffDays = Math.ceil((endDate - now) / (1000 * 60 * 60 * 24))
  
  if (diffDays <= 7 && diffDays >= 0) {
    return 'bg-red-100 text-red-800'
  } else if (diffDays <= 30 && diffDays > 7) {
    return 'bg-yellow-100 text-yellow-800'
  } else {
    return 'bg-green-100 text-green-800'
  }
}

// 計算本月到期項目數量
const monthlyExpiringCount = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  
  return props.payments.filter(payment => {
    const endDate = new Date(payment.end_date)
    return endDate.getFullYear() === year && endDate.getMonth() === month
  }).length
})
</script>
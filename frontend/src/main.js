import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import Toast from 'vue-toastification'
import FloatingVue from 'floating-vue'

import App from './App.vue'
import './style.css'
import 'vue-toastification/dist/index.css'
import 'floating-vue/dist/style.css'

// 創建應用實例
const app = createApp(App)

// 安裝插件
app.use(createPinia())
app.use(router)
app.use(Toast, {
  position: 'top-right',
  timeout: 5000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true,
  rtl: false,
  toastClassName: 'custom-toast',
  bodyClassName: 'custom-toast-body',
  transition: 'Vue-Toastification__slideBlurred',
  maxToasts: 5,
  newestOnTop: true
})
app.use(FloatingVue)

// 全域屬性
app.config.globalProperties.$filters = {
  // 格式化日期
  formatDate(date, format = 'yyyy-MM-dd') {
    if (!date) return ''
    const d = new Date(date)
    if (isNaN(d.getTime())) return ''
    
    const pad = (num) => String(num).padStart(2, '0')
    
    return format
      .replace('yyyy', d.getFullYear())
      .replace('MM', pad(d.getMonth() + 1))
      .replace('dd', pad(d.getDate()))
      .replace('HH', pad(d.getHours()))
      .replace('mm', pad(d.getMinutes()))
      .replace('ss', pad(d.getSeconds()))
  },
  
  // 格式化檔案大小
  formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
  },
  
  // 格式化貨幣
  formatCurrency(amount, currency = 'TWD') {
    if (!amount) return '0'
    return new Intl.NumberFormat('zh-TW', {
      style: 'currency',
      currency: currency
    }).format(amount)
  },
  
  // 截斷文字
  truncate(text, length = 50) {
    if (!text) return ''
    return text.length > length ? text.substring(0, length) + '...' : text
  },
  
  // 狀態文字轉換
  statusText(status, type = 'equipment') {
    const statusMap = {
      equipment: {
        active: '使用中',
        inactive: '未使用',
        maintenance: '維修中',
        retired: '已淘汰'
      },
      repair: {
        pending: '待處理',
        assigned: '已指派',
        in_progress: '處理中',
        completed: '已完成',
        cancelled: '已取消'
      },
      announcement: {
        draft: '草稿',
        sent: '已發送',
        cancelled: '已取消'
      }
    }
    
    return statusMap[type]?.[status] || status
  },
  
  // 優先級文字轉換
  priorityText(priority) {
    const priorityMap = {
      low: '低',
      medium: '中',
      high: '高',
      urgent: '緊急'
    }
    return priorityMap[priority] || priority
  }
}

// 全域指令
app.directive('loading', {
  mounted(el, binding) {
    if (binding.value) {
      el.classList.add('loading')
    }
  },
  updated(el, binding) {
    if (binding.value) {
      el.classList.add('loading')
    } else {
      el.classList.remove('loading')
    }
  }
})

app.directive('tooltip', {
  mounted(el, binding) {
    el.setAttribute('title', binding.value)
  },
  updated(el, binding) {
    el.setAttribute('title', binding.value)
  }
})

// 錯誤處理
app.config.errorHandler = (err, instance, info) => {
  console.error('Vue Error:', err)
  console.error('Component Info:', info)
  
  // 可以在這裡發送錯誤到錯誤追蹤服務
  // errorTracker.captureException(err, { extra: { info } })
}

// 掛載應用
app.mount('#app')
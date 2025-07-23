<template>
  <nav class="bg-white shadow-sm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 justify-between">
        <!-- 左側 - Logo 和主選單 -->
        <div class="flex">
          <div class="flex flex-shrink-0 items-center">
            <router-link to="/dashboard" class="text-xl font-bold text-gray-900 hover:text-indigo-600">
              MIS 管理系統
            </router-link>
          </div>

          <!-- 主選單 -->
          <div class="hidden xl:ml-8 xl:flex xl:space-x-6 xl:h-16">
            <!-- 儀表板 -->
            <router-link
              to="/dashboard"
              :class="isActiveRoute('/dashboard') ? activeClass : inactiveClass"
              class="flex items-center px-2 text-sm font-medium whitespace-nowrap h-16"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v4H8V5z"></path>
              </svg>
              儀表板
            </router-link>

            <!-- 資產設備管理 -->
            <div class="relative flex items-center h-16" @mouseenter="showDropdown = 'equipment'" @mouseleave="hideDropdown">
              <button
                :class="isActiveRoute('/equipment') ? activeClass : inactiveClass"
                class="flex items-center px-2 text-sm font-medium whitespace-nowrap h-16"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
                <span class="hidden lg:inline">資產設備</span>
                <span class="lg:hidden">設備</span>
                <svg class="w-3 h-3 ml-1 transition-transform duration-200" :class="showDropdown === 'equipment' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>

              <!-- 設備管理下拉選單 -->
              <div v-show="showDropdown === 'equipment'" class="absolute top-full left-0 z-10 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                <div class="py-1">
                  <router-link to="/equipment" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    設備清單
                  </router-link>
                  <router-link to="/equipment/create" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    新增設備
                  </router-link>
                  <router-link to="/equipment/categories" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    設備分類
                  </router-link>
                </div>
              </div>
            </div>

            <!-- 網路拓樸管理 -->
            <router-link
              to="/topology"
              :class="isActiveRoute('/topology') ? activeClass : inactiveClass"
              class="flex items-center px-2 text-sm font-medium whitespace-nowrap h-16"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
              </svg>
              <span class="hidden lg:inline">網路拓樸</span>
              <span class="lg:hidden">拓樸</span>
            </router-link>

            <!-- 報修管理 -->
            <div class="relative flex items-center h-16" @mouseenter="showDropdown = 'repairs'" @mouseleave="hideDropdown">
              <button
                :class="isActiveRoute('/repairs') ? activeClass : inactiveClass"
                class="flex items-center px-2 text-sm font-medium whitespace-nowrap h-16"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="hidden lg:inline">報修管理</span>
                <span class="lg:hidden">報修</span>
                <svg class="w-3 h-3 ml-1 transition-transform duration-200" :class="showDropdown === 'repairs' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>

              <!-- 報修管理下拉選單 -->
              <div v-show="showDropdown === 'repairs'" class="absolute top-full left-0 z-10 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                <div class="py-1">
                  <router-link to="/repairs" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    報修列表
                  </router-link>
                  <router-link to="/repairs/create" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    新增報修
                  </router-link>
                </div>
              </div>
            </div>

            <!-- 系統管理 -->
            <div class="relative flex items-center h-16" @mouseenter="showDropdown = 'system'" @mouseleave="hideDropdown">
              <button
                :class="isActiveRoute('/users/manage') || isActiveRoute('/vm-servers') || isActiveRoute('/payments') || isActiveRoute('/tracking') ? activeClass : inactiveClass"
                class="flex items-center px-2 text-sm font-medium whitespace-nowrap h-16"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
                <span class="hidden lg:inline">系統管理</span>
                <span class="lg:hidden">管理</span>
                <svg class="w-3 h-3 ml-1 transition-transform duration-200" :class="showDropdown === 'system' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>

              <!-- 系統管理下拉選單 -->
              <div v-show="showDropdown === 'system'" class="absolute top-full left-0 z-10 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                <div class="py-1">
                  <router-link to="/users/manage" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    帳號管理
                  </router-link>
                  <router-link to="/vm-servers" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path>
                    </svg>
                    VM伺服器
                  </router-link>
                  <router-link to="/payments" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    定期付費
                  </router-link>
                  <router-link to="/tracking" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    追蹤碼管理
                  </router-link>
                </div>
              </div>
            </div>

            <!-- 資訊安全佈達 -->
            <router-link
              to="/announcements"
              :class="isActiveRoute('/announcements') ? activeClass : inactiveClass"
              class="flex items-center px-2 text-sm font-medium whitespace-nowrap h-16"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
              </svg>
              <span class="hidden lg:inline">資安佈達</span>
              <span class="lg:hidden">佈達</span>
            </router-link>
          </div>
        </div>

        <!-- 右側 - 使用者資訊和登出 -->
        <div class="flex items-center space-x-4">
          <span class="text-gray-700 hidden sm:block">{{ user?.full_name || user?.username || '用戶' }}</span>
          <div class="relative" @mouseenter="showUserMenu = true" @mouseleave="showUserMenu = false">
            <button class="text-gray-500 hover:text-gray-700 p-1">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </button>

            <!-- 使用者下拉選單 -->
            <div v-show="showUserMenu" class="absolute right-0 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
              <div class="py-1">
                <router-link to="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  個人資料
                </router-link>
                <button @click="handleLogout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                  </svg>
                  登出
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 手機版選單按鈕 -->
        <div class="flex items-center xl:hidden">
          <button @click="showMobileMenu = !showMobileMenu" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- 手機版選單 -->
      <div v-show="showMobileMenu" class="xl:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <router-link to="/dashboard" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50">儀表板</router-link>
          <router-link to="/equipment" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50">資產設備</router-link>
          <router-link to="/topology" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50">網路拓樸</router-link>
          <router-link to="/repairs" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50">報修管理</router-link>
          <router-link to="/users/manage" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50">帳號管理</router-link>
          <router-link to="/vm-servers" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50">VM伺服器</router-link>
          <router-link to="/payments" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50">定期付費</router-link>
          <router-link to="/tracking" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50">追蹤碼管理</router-link>
          <router-link to="/announcements" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50">資安佈達</router-link>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200">
          <div class="px-4">
            <div class="text-base font-medium text-gray-800">{{ user?.full_name || user?.username }}</div>
          </div>
          <div class="mt-3 space-y-1">
            <router-link to="/profile" class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-50">個人資料</router-link>
            <button @click="handleLogout" class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-50">登出</button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const showDropdown = ref(null)
const showUserMenu = ref(false)
const showMobileMenu = ref(false)

// 樣式類別
const activeClass = 'border-indigo-500 text-indigo-600 border-b-2'
const inactiveClass = 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 border-b-2'

// 檢查當前路由是否為活動狀態
const isActiveRoute = (path) => {
  return route.path.startsWith(path)
}

// 隱藏下拉選單（延遲執行避免滑鼠快速移動時閃爍）
const hideDropdown = () => {
  setTimeout(() => {
    showDropdown.value = null
  }, 100)
}

// 登出處理
const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

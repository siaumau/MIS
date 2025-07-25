import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// 路由定義
const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/LoginPage.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/DashboardPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipment',
    name: 'Equipment',
    component: () => import('@/views/equipment/EquipmentList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipment/create',
    name: 'EquipmentCreate',
    component: () => import('@/views/equipment/EquipmentForm.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipment/categories',
    name: 'EquipmentCategories',
    component: () => import('@/views/equipment/EquipmentCategories.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipment/:id',
    name: 'EquipmentDetail',
    component: () => import('@/views/equipment/EquipmentDetail.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/repairs',
    name: 'Repairs',
    component: () => import('@/views/repair/RepairList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/repairs/create',
    name: 'RepairCreate',
    component: () => import('@/views/repair/RepairForm.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/repairs/:id',
    name: 'RepairDetail',
    component: () => import('@/views/repair/RepairDetail.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/announcements',
    name: 'Announcements',
    component: () => import('@/views/announcement/AnnouncementList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/announcements/:id',
    name: 'AnnouncementDetail',
    component: () => import('@/views/announcement/AnnouncementDetail.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/users/manage',
    name: 'UserManage',
    component: () => import('@/views/users/UserManage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/payments',
    name: 'Payments',
    component: () => import('@/views/payments/PaymentManage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('@/views/auth/ProfilePage.vue'),
    meta: { requiresAuth: true }
  },
  // 404 頁面
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/NotFoundPage.vue')
  }
]

// 創建路由器
const router = createRouter({
  history: createWebHistory(),
  routes
})

// 路由守衛
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // 需要認證的路由
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  }
  // 只允許訪客的路由 (如登入頁)
  else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/dashboard')
  }
  else {
    next()
  }
})

export default router
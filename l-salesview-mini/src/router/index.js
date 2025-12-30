import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Auth/Login.vue')
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: () => import('@/views/Auth/ResetPassword.vue')
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
 {
  path: '/inventory',
  name: 'InventoryList',
  component: () => import('@/views/Inventory/InventoryList.vue'),
  meta: { requiresAuth: true }
},
{
  path: '/inventory/:id',
  name: 'InventoryDetail',
  component: () => import('@/views/Inventory/InventoryDetail.vue'),
  meta: { requiresAuth: true }
},
{
  path: '/sales/create',
  name: 'CreateSalesOrder',
  component: () => import('@/views/Sales/CreateSalesOrder.vue'),
  meta: { requiresAuth: true }
},
{
  path: '/customer-map',
  name: 'CustomerMap',
  component: () => import('@/views/CustomerMap.vue'),
  meta: { requiresAuth: true }
},

  {
    path: '/:pathMatch(.*)*',
    redirect: '/login'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Global Navigation Guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Check persisted auth on every navigation
  if (!authStore.isAuthenticated) {
    authStore.checkAuth()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.name === 'Login' && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
<template>
  <aside 
    :class="[
      'fixed inset-y-0 left-0 bg-white dark:bg-gray-800 shadow-lg border-r border-gray-200 dark:border-gray-700 transition-all duration-300 z-40 pt-16',
      collapsed ? 'w-20' : 'w-64'
    ]"
  >
    <!-- Collapse Toggle -->
    <div class="absolute top-20 -right-3">
      <button 
        @click="collapsed = !collapsed"
        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full p-2 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 transition"
      >
        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="collapsed ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7'" />
        </svg>
      </button>
    </div>

    <!-- Menu Items -->
    <nav class="mt-8 px-4">
      <ul class="space-y-2">
        <li v-for="item in menuItems" :key="item.path">
          <RouterLink
            :to="item.path"
            class="flex items-center gap-4 px-4 py-3 rounded-lg transition-colors group"
            :class="[
              route.path === item.path || route.path.startsWith(item.path + '/')
                ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-medium'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
          >
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
            </svg>
            <span v-if="!collapsed" class="whitespace-nowrap">{{ item.name }}</span>
            <span v-else class="text-xs opacity-0 group-hover:opacity-100 absolute left-20 bg-gray-800 text-white px-2 py-1 rounded text-sm whitespace-nowrap z-50">
              {{ item.name }}
            </span>
          </RouterLink>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

/*** @component: L-SideNav
 * @description: Collapsible side navigation with proper router-link navigation
 */

const collapsed = ref(false)
const route = useRoute()

const menuItems = [
  { name: 'Dashboard', path: '/dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
  { name: 'Inventory', path: '/inventory', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8-5m8-9l-8-4m8 4v10l-8-5' },
  { name: 'Sales Orders', path: '/sales/create', icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z' },
  { name: 'Customer Map', path: '/customer-map', icon: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3v-6m6 3l5.447 2.724A1 1 0 0021 16.382V5.618a1 1 0 00-1.447-.894L15 7m6 13v-6m-6 3l-6-3' },
]
</script>
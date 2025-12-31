/*** @component: L-NavBar
 * @created-date: 30-12-2025
 * @leysco-version: 1.0.0
 * @description: Top navigation bar component featuring company branding, theme toggle, notification center with unread count, and user profile dropdown with logout functionality.
 */

<template>
  <nav class="fixed top-0 left-0 right-0 bg-white dark:bg-gray-800 shadow-md z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">L-SalesView Mini</h1>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <!-- Theme Toggle -->
          <button 
            @click="toggleTheme" 
            class="p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            aria-label="Toggle theme"
          >
            <!-- Moon icon (dark mode) -->
            <svg v-if="themeStore.isDark" class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
            </svg>
            <!-- Sun icon (light mode) -->
            <svg v-else class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
            </svg>
          </button>

          <!-- Notification Bell -->
          <div class="relative">
            <button 
              @click="showNotifications = !showNotifications" 
              class="p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 relative transition"
            >
              <!-- Bell icon -->
              <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
              </svg>
              <span 
                v-if="notificationsStore.unreadCount > 0" 
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"
              >
                {{ notificationsStore.unreadCount }}
              </span>
            </button>

            <!-- Notification Dropdown -->
            <div 
              v-if="showNotifications" 
              class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 shadow-lg rounded-md p-4 border border-gray-200 dark:border-gray-700"
            >
              <h3 class="font-medium mb-2 text-gray-900 dark:text-white">Notifications</h3>
              <div v-if="notificationsStore.notifications.length === 0" class="text-gray-500 text-sm">
                No notifications
              </div>
              <div v-else class="space-y-2 max-h-96 overflow-y-auto">
                <div 
                  v-for="notif in notificationsStore.notifications" 
                  :key="notif.id" 
                  class="text-sm p-2 rounded bg-gray-50 dark:bg-gray-700"
                >
                  {{ notif.message }}
                </div>
              </div>
            </div>
          </div>

          <!-- Profile Dropdown -->
          <div class="relative">
            <button 
              @click="showProfileDropdown = !showProfileDropdown" 
              class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            >
              <img 
                class="h-8 w-8 rounded-full border-2 border-gray-300 dark:border-gray-600" 
                src="https://placehold.co/32" 
                alt="User profile"
              >
            </button>

            <div 
              v-if="showProfileDropdown" 
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 shadow-lg rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
              <a href="#" class="block px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
                Profile Settings
              </a>
              <button 
                @click="authStore.logout(); router.push('/login'); showProfileDropdown = false" 
                class="w-full text-left px-4 py-3 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 transition"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useNotificationsStore } from '@/stores/notifications'

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const notificationsStore = useNotificationsStore()

const showProfileDropdown = ref(false)
const showNotifications = ref(false)

// Toggle theme 
const toggleTheme = () => {
  themeStore.toggle()
}
</script>
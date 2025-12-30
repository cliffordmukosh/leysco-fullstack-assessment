import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useThemeStore } from '@/stores/theme'
import { useNotificationsStore } from '@/stores/notifications'

import './style.css'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)

// Initialize theme and notifications
const themeStore = useThemeStore()
themeStore.initialize()

const notificationsStore = useNotificationsStore()
notificationsStore.loadFromStorage()

app.mount('#app')
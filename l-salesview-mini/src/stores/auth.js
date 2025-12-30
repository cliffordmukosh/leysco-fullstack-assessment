import { defineStore } from 'pinia'
import users from '@/assets/data/user_data.json'
import { useNotificationsStore } from '@/stores/notifications'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false,
    loading: false,
    error: null,
    loginAttempts: 0,
    lockoutUntil: null
  }),

  actions: {
    async login(username, password, rememberMe = false) {
      this.loading = true
      this.error = null

      const notificationsStore = useNotificationsStore()

      // Simulate lockout
      if (this.lockoutUntil && Date.now() < this.lockoutUntil) {
        this.error = 'Account locked due to too many failed attempts. Try again later.'
        this.loading = false
        return false
      }

      // Simulate API delay
      await new Promise(resolve => setTimeout(resolve, 1000))

      const foundUser = users.find(u => u.username === username)

      if (foundUser && password.length >= 8) { // Simulate correct password
        this.user = foundUser
        this.isAuthenticated = true
        this.loginAttempts = 0
        this.lockoutUntil = null

        // Store token/user based on rememberMe
        const token = 'leysco-fake-jwt-token-2025'
        if (rememberMe) {
          localStorage.setItem('authToken', token)
          localStorage.setItem('authUser', JSON.stringify(foundUser))
        } else {
          sessionStorage.setItem('authToken', token)
          sessionStorage.setItem('authUser', JSON.stringify(foundUser))
        }

        notificationsStore.addNotification('success', 'Login successful! Welcome back.')

        this.loading = false
        return true
      } else {
        this.loginAttempts += 1
        if (this.loginAttempts >= 3) {
          this.lockoutUntil = Date.now() + 5 * 60 * 1000 
          this.error = 'Too many failed attempts. Account locked for 5 minutes.'
        } else {
          this.error = 'Invalid username or password'
        }
        notificationsStore.addNotification('error', 'Login failed. Please check your credentials.')
        this.loading = false
        return false
      }
    },

    logout() {
      this.user = null
      this.isAuthenticated = false
      localStorage.removeItem('authToken')
      localStorage.removeItem('authUser')
      sessionStorage.removeItem('authToken')
      sessionStorage.removeItem('authUser')

      const notificationsStore = useNotificationsStore()
      notificationsStore.addNotification('info', 'You have been logged out.')
    },

    checkAuth() {
      const token = localStorage.getItem('authToken') || sessionStorage.getItem('authToken')
      const userData = localStorage.getItem('authUser') || sessionStorage.getItem('authUser')

      if (token && userData) {
        this.user = JSON.parse(userData)
        this.isAuthenticated = true
      }
    }
  }
})
import { defineStore } from 'pinia'

export const useNotificationsStore = defineStore('notifications', {
  state: () => ({
    notifications: []
  }),

  getters: {
    unreadCount: (state) => state.notifications.filter(n => !n.read).length
  },

  actions: {
    addNotification(type, message, autoDismiss = true) {
      const id = Date.now();
      const notification = {
        id,
        type,
        message,
        read: false,
        timestamp: new Date().toISOString(),
      };

      this.notifications.unshift(notification);

      // Auto dismiss after 7 seconds
      if (autoDismiss) {
        setTimeout(() => {
          this.removeNotification(id);
        }, 7000);
      }

      this.saveToStorage();
    },

    removeNotification(id) {
      this.notifications = this.notifications.filter(n => n.id !== id)
      this.saveToStorage()
    },

    markAsRead(id) {
      const notif = this.notifications.find(n => n.id === id)
      if (notif) notif.read = true
      this.saveToStorage()
    },

    markAllAsRead() {
      this.notifications.forEach(n => n.read = true)
      this.saveToStorage()
    },

    loadFromStorage() {
      const saved = localStorage.getItem('appNotifications')
      if (saved) {
        this.notifications = JSON.parse(saved)
      }
    },

    saveToStorage() {
      localStorage.setItem('appNotifications', JSON.stringify(this.notifications))
    }
  }
})
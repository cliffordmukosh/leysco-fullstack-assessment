/*** @component: L-NotificationToast
 * @created-date: 30-12-2025
 * @leysco-version: 1.0.0
 * @description: Reusable toast for notifications
 */

<template>
  <!-- Notification toast -->
  <div
    :class="[
      'p-4 rounded-md text-white',
      type === 'success'
        ? 'bg-green-500'
        : type === 'error'
        ? 'bg-red-500'
        : type === 'warning'
        ? 'bg-yellow-500'
        : 'bg-blue-500'
    ]"
  >
    {{ message }}
  </div>
</template>

<script setup>
defineProps({
  type: String,
  message: String
})
</script>
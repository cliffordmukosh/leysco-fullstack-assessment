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
/*** @component: L-NotificationToast
 * @description: Reusable toast for notifications
 */

defineProps({
  type: String,
  message: String
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2',
      variant === 'primary' ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
      loading || disabled ? 'opacity-50 cursor-not-allowed' : ''
    ]"
  >
    <span v-if="loading" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
    <slot v-else />
  </button>
</template>

<script setup>
defineProps({
  type: { type: String, default: 'button' },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  variant: { type: String, default: 'primary' } 
})
</script>
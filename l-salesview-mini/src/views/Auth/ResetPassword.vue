<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
        <h2 class="text-lg font-semibold text-center mb-6 text-gray-900 dark:text-white">
          Reset Your Password
        </h2>

        <div v-if="success" class="text-center py-8">
          <svg class="w-16 h-16 mx-auto text-green-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-base text-gray-900 dark:text-white mb-4">
            Password reset instructions have been sent to your email.
          </p>
          <button
            @click="router.push('/login')"
            class="text-sm text-blue-600 hover:underline"
          >
            ← Back to Login
          </button>
        </div>

        <form v-else @submit.prevent="handleReset" class="space-y-5">
          <p class="text-center text-xs text-gray-600 dark:text-gray-400 mb-5">
            Enter your username and registered email to request a password reset.
          </p>

          <LInput
            v-model="username"
            label="Username"
            placeholder="username"
          />

          <LInput
            v-model="email"
            type="email"
            label="Email Address"
            placeholder="your.email@leysco.co.ke"
          />

          <LButton
            :loading="loading"
            :disabled="!username || !email"
            class="w-full py-2 text-sm"
          >
            Send Reset Instructions
          </LButton>

          <div class="text-center">
            <router-link to="/login" class="text-xs text-blue-600 hover:underline">
              ← Back to Login
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import LInput from '@/components/L-Input.vue'
import LButton from '@/components/L-Button.vue'

const router = useRouter()
const username = ref('')
const email = ref('')
const loading = ref(false)
const success = ref(false)

async function handleReset() {
  if (!username.value || !email.value) return

  loading.value = true
  await new Promise(resolve => setTimeout(resolve, 1500))
  loading.value = false
  success.value = true
}
</script>
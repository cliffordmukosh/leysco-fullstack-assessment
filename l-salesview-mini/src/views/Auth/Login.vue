<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
      <!-- Branding -->
      <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">L-SalesView Mini</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Leysco Sales Automation Portal</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
        <h2 class="text-lg font-semibold text-center mb-6 text-gray-900 dark:text-white">
          Sign In to Your Account
        </h2>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Username -->
          <LInput
            v-model="username"
            label="Username"
            placeholder="username"
            autocomplete="username"
          />

          <!-- Password -->
          <div class="relative">
            <LInput
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              label="Password"
              placeholder="Enter your password"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-9 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
            >
              {{ showPassword ? 'Hide' : 'Show' }}
            </button>
          </div>

          <!-- Password Requirements -->
          <div v-if="password && !isPasswordValid" class="text-xs text-red-600 space-y-1">
            <p v-if="!passwordRules.minLength">• Minimum 8 characters</p>
            <p v-if="!passwordRules.uppercase">• At least 1 uppercase letter</p>
            <p v-if="!passwordRules.number">• At least 1 number</p>
            <p v-if="!passwordRules.special">• At least 1 special character (!@#$%^&*)</p>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between text-xs">
            <label class="flex items-center">
              <input type="checkbox" v-model="rememberMe" class="mr-2 rounded" />
              <span class="text-gray-700 dark:text-gray-300">Remember me</span>
            </label>
            <router-link
              to="/reset-password"
              class="text-blue-600 hover:underline"
            >
              Forgot Password?
            </router-link>
          </div>

          <!-- Submit Button -->
          <LButton
            type="submit"
            :loading="authStore.loading"
            :disabled="!isFormValid || authStore.loading"
            class="w-full py-2 text-sm"
          >
            Sign In
          </LButton>

          <!-- Error Message -->
          <p v-if="serverError" class="text-center text-xs text-red-600 font-medium mt-3">
            {{ serverError }}
          </p>
        </form>

        <!-- Loading Overlay -->
        <div v-if="authStore.loading" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 rounded-xl flex items-center justify-center">
          <LLoadingSpinner />
        </div>
      </div>

      <!-- Footer -->
      <p class="text-center text-xs text-gray-600 dark:text-gray-400 mt-6">
        © 2025 Leysco Limited. All rights reserved.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LInput from '@/components/L-Input.vue'
import LButton from '@/components/L-Button.vue'
import LLoadingSpinner from '@/components/L-LoadingSpinner.vue'

const router = useRouter()
const authStore = useAuthStore()

const username = ref('')
const password = ref('')
const rememberMe = ref(false)
const showPassword = ref(false)
const serverError = ref('')

// Password validation rules (shown for UX)
const passwordRules = computed(() => ({
  minLength: password.value.length >= 8,
  uppercase: /[A-Z]/.test(password.value),
  number: /\d/.test(password.value),
  special: /[!@#$%^&*]/.test(password.value)
}))

const isPasswordValid = computed(() => 
  passwordRules.value.minLength &&
  passwordRules.value.uppercase &&
  passwordRules.value.number &&
  passwordRules.value.special
)

const isFormValid = computed(() => 
  username.value.trim() !== '' && 
  password.value !== ''
)

async function handleLogin() {
  serverError.value = ''
  authStore.error = null

  const success = await authStore.login(username.value.trim(), password.value, rememberMe.value)

  if (success) {
    router.push('/dashboard')
  } else {
    serverError.value = authStore.error || 'Invalid username or password'
  }
}
</script>
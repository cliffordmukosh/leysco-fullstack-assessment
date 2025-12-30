<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-xs md:text-sm">
    <!-- Top Navbar -->
    <LNavBar />

    <!-- Sidebar -->
    <LSideNav />

    <!-- Main Content -->
    <main class="lg:pl-64 pt-16 transition-all duration-300">
      <div class="p-4 sm:p-6 lg:p-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
          <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li>
              <div class="flex items-center">
                <a href="/dashboard" class="text-gray-700 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">Home</a>
              </div>
            </li>
            <li>
              <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="ml-1 text-gray-500 md:ml-2 dark:text-gray-400" aria-current="page">
                  <slot name="breadcrumb">Dashboard</slot>
                </span>
              </div>
            </li>
          </ol>
        </nav>

        <!-- Page Content -->
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import LNavBar from '@/components/L-NavBar.vue'
import LSideNav from '@/components/L-SideNav.vue'
</script>
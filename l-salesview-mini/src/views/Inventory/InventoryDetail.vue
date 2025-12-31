/*** @component: L-ProductDetail
 * @created-date: 30-12-2025
 * @leysco-version: 1.0.0
 * @description: Detailed product view showing image carousel (placeholder), stock summary, warehouse breakdown table, specifications, batch/serial tracking status, and related products grid with navigation.
 */
 
<template>
  <LDefaultLayout>
    <template #breadcrumb>
      <router-link to="/inventory" class="text-blue-600 hover:underline mr-1">Inventory</router-link>
      <span>/ {{ product?.name || 'Loading...' }}</span>
    </template>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 space-y-4">
      <LLoadingSpinner />
      <p class="text-lg text-gray-600 dark:text-gray-400">Loading product details...</p>
    </div>

    <!-- Not Found -->
    <div v-else-if="!product" class="text-center py-20 space-y-4">
      <svg class="w-24 h-24 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Product Not Found</h2>
      <p class="text-gray-600 dark:text-gray-400">The product you're looking for doesn't exist.</p>
      <router-link to="/inventory" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Inventory
      </router-link>
    </div>

    <!-- Product Detail -->
    <div v-else class="space-y-8">
      <!-- Header with Back Button -->
      <div class="flex items-center justify-between">
        <button 
          @click="goBack" 
          class="inline-flex items-center px-4 py-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Inventory
        </button>
        
        <div class="text-right">
          <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ product.name }}</p>
          <p class="text-lg text-gray-500 dark:text-gray-400">{{ product.sku }}</p>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Product Image & Quick Stats -->
        <div class="lg:col-span-4 space-y-6">
          <!-- Product Image with Navigation -->
          <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 rounded-2xl p-8 text-center border-2 border-dashed border-gray-300 dark:border-gray-600 relative">
            <svg class="w-24 h-24 mx-auto text-gray-500 dark:text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8-5m8-9l-8-4m8 4v10l-8-5" />
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">Product Image</p>
            <p v-if="product.images?.length" class="text-xs text-blue-600 mt-2 font-medium">
              {{ product.images[currentImageIndex] }}
            </p>

            <!-- Left Arrow -->
            <button
              v-if="product.images?.length > 1"
              @click="prevImage"
              :disabled="currentImageIndex === 0"
              class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 dark:bg-gray-800/80 hover:bg-white dark:hover:bg-gray-800 p-2 rounded-full shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-5 h-5 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <!-- Right Arrow -->
            <button
              v-if="product.images?.length > 1"
              @click="nextImage"
              :disabled="currentImageIndex === product.images.length - 1"
              class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 dark:bg-gray-800/80 hover:bg-white dark:hover:bg-gray-800 p-2 rounded-full shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-5 h-5 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <!-- Stock Summary Card -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-lg mb-4 flex items-center">
              <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              Stock Summary
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Total Stock:</span>
                <span class="font-bold text-xl text-gray-900 dark:text-white">{{ totalStock }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Available:</span>
                <span class="font-bold text-2xl" :class="totalAvailable < product.reorder_level ? 'text-red-600' : 'text-green-600'">
                  {{ totalAvailable }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Reorder Level:</span>
                <span class="text-orange-600 font-semibold">{{ product.reorder_level }}</span>
              </div>
              <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                <span class="text-sm text-gray-500 dark:text-gray-400">Unit Price:</span>
                <span class="font-bold text-lg text-blue-600">{{ leysSalesFormatter(product.price) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Detailed Information -->
        <div class="lg:col-span-8 space-y-6">
          <!-- Warehouse Breakdown -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
              <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Warehouse Stock Breakdown
            </h3>
            <div class="overflow-x-auto">
              <table class="w-full min-w-[500px]">
                <thead>
                  <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left py-4 px-6 font-medium text-gray-900 dark:text-white">Warehouse</th>
                    <th class="text-center py-4 px-6 font-medium text-gray-900 dark:text-white">Total</th>
                    <th class="text-center py-4 px-6 font-medium text-gray-900 dark:text-white">Reserved</th>
                    <th class="text-center py-4 px-6 font-medium text-gray-900 dark:text-white">Available</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="stockItem in product.stock" :key="stockItem.warehouse_id" class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="py-4 px-6 font-medium text-gray-900 dark:text-white">
                      {{ getWarehouseName(stockItem.warehouse_id) }}
                    </td>
                    <td class="py-4 px-6 text-center font-semibold">{{ stockItem.quantity }}</td>
                    <td class="py-4 px-6 text-center text-orange-600">{{ stockItem.reserved }}</td>
                    <td class="py-4 px-6 text-center font-bold" :class="stockItem.quantity - stockItem.reserved < product.reorder_level ? 'text-red-600' : 'text-green-600'">
                      {{ stockItem.quantity - stockItem.reserved }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Product Specifications -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
              <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
              </svg>
              Specifications
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="(value, key) in product.specifications" :key="key" class="space-y-1">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">
                  {{ key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ value }}</dd>
              </div>
              <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Unit</dt>
                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ product.unit }}</dd>
              </div>
              <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Packaging</dt>
                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ product.packaging }}</dd>
              </div>
              <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Batch Tracking</dt>
                <dd class="text-lg font-semibold flex items-center gap-2" :class="product.batch_number ? 'text-green-600' : 'text-gray-500'">
                  <svg v-if="product.batch_number" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  {{ product.batch_number ? 'Enabled' : 'Disabled' }}
                </dd>
              </div>
              <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Serial Tracking</dt>
                <dd class="text-lg font-semibold flex items-center gap-2" :class="product.serial_number ? 'text-green-600' : 'text-gray-500'">
                  <svg v-if="product.serial_number" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  {{ product.serial_number ? 'Enabled' : 'Disabled' }}
                </dd>
              </div>
            </div>
          </div>

          <!-- Related Products -->
          <div v-if="relatedProducts.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
              <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
              </svg>
              Related Products ({{ relatedProducts.length }})
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="relProduct in relatedProducts"
                :key="relProduct.id"
                @click="router.push(`/inventory/${relProduct.id}`)"
                class="group border border-gray-200 dark:border-gray-700 rounded-xl p-6 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 cursor-pointer transition-all duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20"
              >
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8-5m8-9l-8-4m8 4v10l-8-5" />
                </svg>
                <h4 class="font-bold text-lg mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ relProduct.name }}</h4>
                <p class="text-sm text-gray-500 mb-2">{{ relProduct.sku }}</p>
                <p class="text-sm font-semibold text-blue-600">{{ leysSalesFormatter(relProduct.price) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LDefaultLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { leysSalesFormatter } from '@/utils/leysSalesFormatter'
import products from '@/assets/data/products.json'
import warehouses from '@/assets/data/warehouses.json'
import LDefaultLayout from '@/layouts/L-DefaultLayout.vue'
import LLoadingSpinner from '@/components/L-LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const productId = ref('')

// Image navigation
const currentImageIndex = ref(0)

const prevImage = () => {
  if (currentImageIndex.value > 0) currentImageIndex.value--
}

const nextImage = () => {
  if (product.value?.images && currentImageIndex.value < product.value.images.length - 1) {
    currentImageIndex.value++
  }
}

watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      productId.value = newId
      currentImageIndex.value = 0 // Reset to first image when changing product
    }
  },
  { immediate: true }
)

const product = computed(() => products.find(p => p.id === productId.value))

const totalStock = computed(() => {
  if (!product.value?.stock) return 0
  return product.value.stock.reduce((sum, s) => sum + s.quantity, 0)
})

const totalAvailable = computed(() => {
  if (!product.value?.stock) return 0
  return product.value.stock.reduce((sum, s) => sum + (s.quantity - s.reserved), 0)
})

const relatedProducts = computed(() => {
  if (!product.value?.related_products) return []
  return product.value.related_products
    .map(id => products.find(p => p.id === id))
    .filter(Boolean)
})

const warehouseNames = computed(() => {
  const names = {}
  warehouses.forEach(w => names[w.id] = w.name)
  return names
})

onMounted(async () => {
  await new Promise(resolve => setTimeout(resolve, 100))
  
  if (!productId.value || !product.value) {
    router.replace('/inventory')
    return
  }
  
  setTimeout(() => {
    loading.value = false
  }, 800)
})

function getWarehouseName(warehouseId) {
  return warehouseNames.value[warehouseId] || 'Unknown Warehouse'
}

function goBack() {
  router.push('/inventory')
}
</script>
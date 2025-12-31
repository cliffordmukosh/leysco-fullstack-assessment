/*** @component: L-InventoryList
 * @created-date: 30-12-2025
 * @leysco-version: 1.0.0
 * @description: Main inventory listing page with quick search, sortable table (SKU, name, total/available stock), status indicators (OK/Low/Critical), pagination, and clickable rows to product detail view.
 */
 
<template>
  <LDefaultLayout>
    <template #breadcrumb>Inventory</template>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="text-lg text-gray-600 dark:text-gray-400">Loading inventory...</div>
    </div>

    <div v-else>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Inventory Management</h1>

      <!-- Search Bar -->
      <div class="mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by name, SKU, or category..."
          class="w-full max-w-md px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th @click="sortBy('sku')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                  SKU <span v-if="sortKey==='sku'" class="ml-1">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                </th>
                <th @click="sortBy('name')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                  Product Name <span v-if="sortKey==='name'" class="ml-1">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th @click="sortBy('totalStock')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                  Total Stock
                </th>
                <th @click="sortBy('availableStock')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                  Available
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (KES)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr
                v-for="product in paginatedProducts"
                :key="product.id"
                @click="goToDetail(product.id)"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition"
              >
                <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-white">{{ product.sku }}</td>
                <td class="px-4 py-3 text-xs text-gray-900 dark:text-white">{{ product.name }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ product.category }} / {{ product.subcategory }}</td>
                <td class="px-4 py-3 text-xs text-gray-900 dark:text-white">{{ product.totalStock }}</td>
                <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-white">{{ product.availableStock }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ product.reorder_level }}</td>
                <td class="px-4 py-3">
                  <span class="px-2 py-1 text-xs font-medium rounded-full" :class="product.statusClass">
                    {{ product.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-900 dark:text-white">{{ product.price.toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex items-center justify-between text-xs">
          <div class="text-gray-700 dark:text-gray-300">
            Showing {{ (currentPage-1)*itemsPerPage + 1 }} to {{ Math.min(currentPage*itemsPerPage, filteredProducts.length) }} of {{ filteredProducts.length }} items
          </div>
          <div class="flex gap-2">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 disabled:opacity-50 text-xs"
            >Previous</button>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 disabled:opacity-50 text-xs"
            >Next</button>
          </div>
        </div>
      </div>
    </div>
  </LDefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import products from '@/assets/data/products.json'
import LDefaultLayout from '@/layouts/L-DefaultLayout.vue'

const router = useRouter()
const loading = ref(true)

// Table state
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const sortKey = ref('name')
const sortOrder = ref('asc') 

onMounted(() => {
  setTimeout(() => loading.value = false, 800)
})

// Computed: Processed products with stock calculations
const processedProducts = computed(() => {
  return products.map(product => {
    let totalQuantity = 0
    let totalReserved = 0

    product.stock.forEach(s => {
      totalQuantity += s.quantity
      totalReserved += s.reserved
    })

    const available = totalQuantity - totalReserved
    const status = available < product.reorder_level 
      ? (available === 0 ? 'Critical' : 'Low') 
      : 'OK'

    return {
      ...product,
      totalStock: totalQuantity,
      availableStock: available,
      status,
      statusClass: status === 'OK' ? 'text-green-600 bg-green-100' 
        : status === 'Low' ? 'text-yellow-600 bg-yellow-100' 
        : 'text-red-600 bg-red-100'
    }
  })
})

// Filtered & Searched
const filteredProducts = computed(() => {
  let items = processedProducts.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    items = items.filter(p => 
      p.name.toLowerCase().includes(query) ||
      p.sku.toLowerCase().includes(query) ||
      p.category.toLowerCase().includes(query)
    )
  }

  // Sorting
  items = [...items].sort((a, b) => {
    let aVal = a[sortKey.value]
    let bVal = b[sortKey.value]
    
    if (typeof aVal === 'string') {
      aVal = aVal.toLowerCase()
      bVal = bVal.toLowerCase()
    }

    if (sortOrder.value === 'asc') {
      return aVal > bVal ? 1 : -1
    } else {
      return aVal < bVal ? 1 : -1
    }
  })

  return items
})

// Pagination
const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredProducts.value.slice(start, end)
})

const totalPages = computed(() => 
  Math.ceil(filteredProducts.value.length / itemsPerPage.value)
)

function sortBy(key) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

function goToDetail(id) {
  router.push(`/inventory/${id}`)
}
</script>
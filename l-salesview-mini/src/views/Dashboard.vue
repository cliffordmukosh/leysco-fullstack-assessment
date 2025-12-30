<template>
  <LDefaultLayout>
    <template #breadcrumb>Dashboard</template>

    <div v-if="loading" class="flex flex-col items-center justify-center min-h-screen">
      <div class="text-lg font-medium text-gray-600 dark:text-gray-400">Loading dashboard...</div>
    </div>

    <div v-else class="space-y-8 pb-10">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Overview</h1>
        <div class="flex gap-2">
          <button @click="timePeriod = 'today'" :class="['px-4 py-2 rounded-lg text-sm font-medium transition', timePeriod === 'today' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
            Today
          </button>
          <button @click="timePeriod = 'week'" :class="['px-4 py-2 rounded-lg text-sm font-medium transition', timePeriod === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
            This Week
          </button>
          <button @click="timePeriod = 'month'" :class="['px-4 py-2 rounded-lg text-sm font-medium transition', timePeriod === 'month' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
            This Month
          </button>
        </div>
      </div>

      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow">
          <p class="text-xs text-gray-600 dark:text-gray-400">Total Sales</p>
          <p class="text-2xl font-bold mt-2">{{ leysSalesFormatter(totalSales) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow">
          <p class="text-xs text-gray-600 dark:text-gray-400">Orders</p>
          <p class="text-2xl font-bold mt-2">{{ totalOrders }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow">
          <p class="text-xs text-gray-600 dark:text-gray-400">Low Stock</p>
          <p class="text-2xl font-bold text-red-600 mt-2">{{ lowStockItems }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow">
          <p class="text-xs text-gray-600 dark:text-gray-400">Customers</p>
          <p class="text-2xl font-bold mt-2">{{ totalCustomers }}</p>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">
          <h3 class="text-lg font-semibold mb-4">Sales Performance</h3>
          <div class="h-80">
            <canvas ref="salesChartRef"></canvas>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">
          <h3 class="text-lg font-semibold mb-4">Inventory by Category</h3>
          <div class="h-80">
            <canvas ref="inventoryChartRef"></canvas>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">
        <h3 class="text-lg font-semibold mb-4">Top 5 Selling Products</h3>
        <div class="h-80">
          <canvas ref="topProductsChartRef"></canvas>
        </div>
      </div>
    </div>
  </LDefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import Chart from 'chart.js/auto'
import LDefaultLayout from '@/layouts/L-DefaultLayout.vue'
import salesData from '@/assets/data/sales_data.json'
import products from '@/assets/data/products.json'
import customers from '@/assets/data/customers.json'
import { leysSalesFormatter } from '@/utils/leysSalesFormatter'

const timePeriod = ref('month')
const loading = ref(true)

// Metrics
const totalSales = ref(0)
const totalOrders = ref(0)
const lowStockItems = ref(0)
const totalCustomers = ref(0)

// Chart refs
const salesChartRef = ref(null)
const inventoryChartRef = ref(null)
const topProductsChartRef = ref(null)

let salesChart = null
let inventoryChart = null
let topProductsChart = null

onMounted(async () => {
  calculateMetrics()

  loading.value = false
  await nextTick()

  renderCharts()
})

onUnmounted(() => {
  [salesChart, inventoryChart, topProductsChart].forEach(chart => chart?.destroy())
})

const calculateMetrics = () => {
  totalOrders.value = salesData.length
  totalSales.value = salesData.reduce((sum, order) => sum + order.net_amount, 0)
  totalCustomers.value = customers.length

  lowStockItems.value = products.filter(product => {
    const available = product.stock.reduce((sum, s) => sum + (s.quantity - s.reserved), 0)
    return available < product.reorder_level
  }).length
}

const salesByMonth = computed(() => ({
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  data: [0, 0, 0, totalSales.value, 0, 0]
}))

const inventoryByCategory = computed(() => {
  const map = {}
  products.forEach(p => {
    map[p.category] = (map[p.category] || 0) + p.stock.reduce((s, w) => s + w.quantity, 0)
  })
  return { labels: Object.keys(map), data: Object.values(map) }
})

const topProducts = computed(() => {
  const salesMap = {}
  salesData.forEach(order => {
    order.items.forEach(item => {
      const prod = products.find(p => p.id === item.product_id)
      if (prod) {
        salesMap[prod.id] = (salesMap[prod.id] || 0) + item.quantity
      }
    })
  })

  return Object.entries(salesMap)
    .map(([id, qty]) => ({
      name: products.find(p => p.id === id)?.name || id,
      quantity: qty
    }))
    .sort((a, b) => b.quantity - a.quantity)
    .slice(0, 5)
})

const renderCharts = () => {
  if (!salesChartRef.value || !inventoryChartRef.value || !topProductsChartRef.value) return

  // Sales Line Chart
  salesChart = new Chart(salesChartRef.value, {
    type: 'line',
    data: {
      labels: salesByMonth.value.labels,
      datasets: [{
        label: 'Sales (KES)',
        data: salesByMonth.value.data,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.2)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'top' } }
    }
  })

  // Inventory Doughnut
  inventoryChart = new Chart(inventoryChartRef.value, {
    type: 'doughnut',
    data: {
      labels: inventoryByCategory.value.labels,
      datasets: [{
        data: inventoryByCategory.value.data,
        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b']
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  })

  // Top Products Horizontal Bar
  topProductsChart = new Chart(topProductsChartRef.value, {
    type: 'bar',
    data: {
      labels: topProducts.value.map(p => p.name),
      datasets: [{
        label: 'Units Sold',
        data: topProducts.value.map(p => p.quantity),
        backgroundColor: '#3b82f6'
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } }
    }
  })
}
</script>

<style scoped>
canvas {
  max-height: 100%;
  width: 100% !important;
}
</style>
<template>
  <LDefaultLayout>
    <template #breadcrumb>Sales Order</template>

    <div class="max-w-6xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Create Sales Order</h1>

      <!-- Stepper -->
      <div class="flex items-center justify-center mb-12">
        <div class="flex items-center">
          <div :class="['w-12 h-12 rounded-full flex items-center justify-center font-bold', currentStep >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600']">1</div>
          <div class="w-32 h-1 bg-gray-300 mx-4" :class="currentStep >= 2 ? 'bg-blue-600' : ''"></div>
          <div :class="['w-12 h-12 rounded-full flex items-center justify-center font-bold', currentStep >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600']">2</div>
          <div class="w-32 h-1 bg-gray-300 mx-4" :class="currentStep >= 3 ? 'bg-blue-600' : ''"></div>
          <div :class="['w-12 h-12 rounded-full flex items-center justify-center font-bold', currentStep >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600']">3</div>
        </div>
      </div>

      <!-- Step 1: Select Customer -->
      <div v-if="currentStep === 1">
        <h2 class="text-2xl font-semibold mb-6">Select Customer</h2>

        <LInput
          v-model="customerSearch"
          label="Search Customer"
          placeholder="Name, contact, or phone..."
          class="max-w-lg"
        />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
          <div
            v-for="customer in filteredCustomers"
            :key="customer.id"
            @click="selectedCustomer = customer; currentStep = 2"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-2 cursor-pointer transition hover:border-blue-500"
            :class="selectedCustomer?.id === customer.id ? 'border-blue-500' : 'border-transparent'"
          >
            <h3 class="font-bold text-lg">{{ customer.name }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ customer.contact_person }}</p>
            <p class="text-sm mt-2">{{ customer.phone }}</p>
            <p class="text-xs mt-3 text-blue-600">Credit Limit: {{ leysSalesFormatter(customer.credit_limit) }}</p>
          </div>
        </div>
      </div>

      <!-- Step 2: Add Products -->
      <div v-if="currentStep === 2">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold">Add Products</h2>
          <button @click="currentStep = 3" :disabled="orderItems.length === 0" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
            Review Order →
          </button>
        </div>

        <LInput
          v-model="productSearch"
          label="Search Products"
          placeholder="Name or SKU..."
          class="max-w-lg mb-6"
        />

        <!-- Selected Items Summary -->
        <div v-if="orderItems.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow mb-8 overflow-hidden">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-4 text-left">Product</th>
                <th class="px-6 py-4 text-center">Qty</th>
                <th class="px-6 py-4 text-center">Avail</th>
                <th class="px-6 py-4 text-right">Price</th>
                <th class="px-6 py-4 text-right">Discount %</th>
                <th class="px-6 py-4 text-right">Line Total</th>
                <th class="px-6 py-4"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in orderItems" :key="item.product.id" class="border-t dark:border-gray-700">
                <td class="px-6 py-4">{{ item.product.name }}</td>
                <td class="px-6 py-4 text-center">
                  <input
                    type="number"
                    v-model.number="item.quantity"
                    min="1"
                    :max="getAvailableStock(item.product)"
                    class="w-20 px-2 py-1 border rounded text-center"
                  />
                </td>
                <td class="px-6 py-4 text-center text-green-600 font-medium">{{ getAvailableStock(item.product) }}</td>
                <td class="px-6 py-4 text-right">{{ leysSalesFormatter(item.product.price) }}</td>
                <td class="px-6 py-4 text-center">
                  <input
                    type="number"
                    v-model.number="item.discountPercent"
                    min="0"
                    max="100"
                    class="w-20 px-2 py-1 border rounded text-center"
                  />
                </td>
                <td class="px-6 py-4 text-right font-semibold">
                  {{ leysSalesFormatter(item.product.price * item.quantity * (1 - item.discountPercent/100)) }}
                </td>
                <td class="px-6 py-4 text-center">
                  <button @click="removeItem(index)" class="text-red-600 hover:text-red-800">✕</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border hover:border-blue-500 cursor-pointer transition"
            @click="addItem(product, 1)"
          >
            <div class="text-4xl mb-4 text-center">🛢️</div>
            <h3 class="font-bold text-lg mb-2">{{ product.name }}</h3>
            <p class="text-sm text-gray-500 mb-3">{{ product.sku }}</p>
            <p class="font-semibold text-blue-600 mb-2">{{ leysSalesFormatter(product.price) }}</p>
            <p class="text-sm">Available: <span class="font-bold text-green-600">{{ getAvailableStock(product) }}</span></p>
          </div>
        </div>
      </div>

      <!-- Step 3: Review & Submit -->
      <div v-if="currentStep === 3">
        <h2 class="text-2xl font-semibold mb-6">Order Review</h2>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 mb-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div>
              <h3 class="font-semibold text-lg mb-4">Customer</h3>
              <p class="font-bold">{{ selectedCustomer?.name }}</p>
              <p>{{ selectedCustomer?.contact_person }} • {{ selectedCustomer?.phone }}</p>
              <p class="text-sm text-gray-500 mt-2">{{ selectedCustomer?.physical_address.city }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-600 dark:text-gray-400">Order Date</p>
              <p class="font-bold text-xl">{{ new Date().toLocaleDateString() }}</p>
            </div>
          </div>

          <table class="w-full mb-8">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="text-left py-3 px-4">Item</th>
                <th class="text-center py-3 px-4">Qty</th>
                <th class="text-right py-3 px-4">Unit Price</th>
                <th class="text-right py-3 px-4">Discount</th>
                <th class="text-right py-3 px-4">Line Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in orderItems" :key="item.product.id" class="border-t dark:border-gray-700">
                <td class="py-4 px-4">{{ item.product.name }}</td>
                <td class="text-center py-4 px-4">{{ item.quantity }}</td>
                <td class="text-right py-4 px-4">{{ leysSalesFormatter(item.product.price) }}</td>
                <td class="text-right py-4 px-4">{{ item.discountPercent }}%</td>
                <td class="text-right py-4 px-4 font-semibold">
                  {{ leysSalesFormatter(item.product.price * item.quantity * (1 - item.discountPercent/100)) }}
                </td>
              </tr>
            </tbody>
          </table>

          <div class="border-t dark:border-gray-700 pt-6 space-y-3 text-right">
            <div class="flex justify-end gap-12">
              <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
              <span class="font-bold text-xl">{{ leysSalesFormatter(subtotal) }}</span>
            </div>
            <div class="flex justify-end gap-12">
              <span class="text-gray-600 dark:text-gray-400">Discount:</span>
              <span class="text-orange-600 font-bold">- {{ leysSalesFormatter(totalDiscount) }}</span>
            </div>
            <div class="flex justify-end gap-12">
              <span class="text-gray-600 dark:text-gray-400">Tax (16%):</span>
              <span class="font-bold">{{ leysSalesFormatter(taxAmount) }}</span>
            </div>
            <div class="flex justify-end gap-12 border-t dark:border-gray-700 pt-4">
              <span class="text-2xl font-bold text-gray-900 dark:text-white">Grand Total:</span>
              <span class="text-3xl font-bold text-blue-600">{{ leysSalesFormatter(grandTotal) }}</span>
            </div>
          </div>
        </div>

        <div class="flex justify-between">
          <LButton @click="currentStep = 2" variant="secondary">← Back</LButton>
          <LButton @click="submitOrder" :loading="loading">
            Submit Order
          </LButton>
        </div>
      </div>
    </div>
  </LDefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationsStore } from '@/stores/notifications'
import { leysSalesFormatter } from '@/utils/leysSalesFormatter'
import customers from '@/assets/data/customers.json'
import products from '@/assets/data/products.json'
import LDefaultLayout from '@/layouts/L-DefaultLayout.vue'
import LInput from '@/components/L-Input.vue'
import LButton from '@/components/L-Button.vue'

/*** @component: CreateSalesOrder
 * @description: Simulated sales order creation with customer/product selection, stock check, pricing, and totals
 */

const router = useRouter()
const notificationsStore = useNotificationsStore()

const loading = ref(false)

// Stepper
const currentStep = ref(1) // 1: Customer, 2: Products, 3: Review

// Customer Selection
const customerSearch = ref('')
const selectedCustomer = ref(null)

// Products
const productSearch = ref('')
const orderItems = ref([]) // { product, quantity, discountPercent }

// Filtered lists
const filteredCustomers = computed(() => {
  if (!customerSearch.value) return customers
  const query = customerSearch.value.toLowerCase()
  return customers.filter(c => 
    c.name.toLowerCase().includes(query) ||
    c.contact_person.toLowerCase().includes(query) ||
    c.phone.includes(query)
  )
})

const filteredProducts = computed(() => {
  if (!productSearch.value) return products
  const query = productSearch.value.toLowerCase()
  return products.filter(p => 
    p.name.toLowerCase().includes(query) ||
    p.sku.toLowerCase().includes(query)
  )
})

// Stock helpers
function getAvailableStock(product) {
  return product.stock.reduce((sum, s) => sum + (s.quantity - s.reserved), 0)
}

// Add item to order
function addItem(product, qty = 1) {
  const available = getAvailableStock(product)
  if (qty > available) {
    notificationsStore.addNotification('error', `Only ${available} units available for ${product.name}`)
    return
  }
  if (qty <= 0) return

  const existing = orderItems.value.find(i => i.product.id === product.id)
  if (existing) {
    const newQty = existing.quantity + qty
    if (newQty > available) {
      notificationsStore.addNotification('error', `Cannot exceed available stock (${available})`)
      return
    }
    existing.quantity = newQty
  } else {
    orderItems.value.push({
      product,
      quantity: qty,
      discountPercent: 0
    })
  }
  productSearch.value = ''
}

// Remove item
function removeItem(index) {
  orderItems.value.splice(index, 1)
}

// Calculations
const subtotal = computed(() => {
  return orderItems.value.reduce((sum, item) => {
    const lineTotal = item.product.price * item.quantity
    const discount = lineTotal * (item.discountPercent / 100)
    return sum + (lineTotal - discount)
  }, 0)
})

const totalDiscount = computed(() => {
  return orderItems.value.reduce((sum, item) => {
    return sum + (item.product.price * item.quantity * (item.discountPercent / 100))
  }, 0)
})

const taxAmount = computed(() => subtotal.value * 0.16) // 16% VAT

const grandTotal = computed(() => subtotal.value + taxAmount.value)

async function submitOrder() {
  loading.value = true
  await new Promise(resolve => setTimeout(resolve, 1500)) // Simulate API

  notificationsStore.addNotification('success', `Order created successfully! Total: ${leysSalesFormatter(grandTotal.value)}`)
  
  // Reset form
  selectedCustomer.value = null
  orderItems.value = []
  currentStep.value = 1
  
  loading.value = false
  router.push('/dashboard')
}
</script>

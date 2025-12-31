/*** @component: L-CreateSalesOrder
 * @created-date: 31-12-2025
 * @leysco-version: 1.0.0
 * @description: Multi-step sales order creation wizard with customer selection (search/filter), product addition (real-time stock check, quantity/discount controls), order review with calculations (subtotal, discount, 16% tax, grand total), and simulated submission with success notification.
 */

<template>
  <LDefaultLayout>
    <template #breadcrumb>Sales Order</template>

    <div class="max-w-5xl mx-auto px-4 sm:px-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Create Sales Order</h1>

      <!-- Stepper – clickable steps to go back -->
      <div class="flex items-center justify-center mb-8">
        <div class="flex items-center">
          <button
            @click="currentStep = 1"
            :class="[
              'w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition',
              currentStep >= 1 ? 'bg-blue-600 text-white cursor-pointer' : 'bg-gray-300 text-gray-600 cursor-not-allowed'
            ]"
          >
            1
          </button>
          <div class="w-16 h-0.5 bg-gray-300 mx-3" :class="currentStep >= 2 ? 'bg-blue-600' : ''"></div>
          <button
            @click="currentStep = 2"
            :class="[
              'w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition',
              currentStep >= 2 ? 'bg-blue-600 text-white cursor-pointer' : 'bg-gray-300 text-gray-600 cursor-not-allowed'
            ]"
            :disabled="currentStep < 2"
          >
            2
          </button>
          <div class="w-16 h-0.5 bg-gray-300 mx-3" :class="currentStep >= 3 ? 'bg-blue-600' : ''"></div>
          <button
            @click="currentStep = 3"
            :class="[
              'w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition',
              currentStep >= 3 ? 'bg-blue-600 text-white cursor-pointer' : 'bg-gray-300 text-gray-600 cursor-not-allowed'
            ]"
            :disabled="currentStep < 3"
          >
            3
          </button>
        </div>
      </div>

      <!-- Step 1: Select Customer -->
      <div v-if="currentStep === 1">
        <h2 class="text-xl font-semibold mb-4">Select Customer</h2>

        <LInput
          v-model="customerSearch"
          label="Search Customer"
          placeholder="Name, contact, phone..."
          class="max-w-md mb-4"
        />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="customer in filteredCustomers"
            :key="customer.id"
            @click="selectedCustomer = customer; currentStep = 2"
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-2 cursor-pointer transition hover:border-blue-500 text-sm"
            :class="selectedCustomer?.id === customer.id ? 'border-blue-500' : 'border-transparent'"
          >
            <h3 class="font-bold text-base mb-1">{{ customer.name }}</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400">{{ customer.contact_person }}</p>
            <p class="text-xs mt-1">{{ customer.phone }}</p>
            <p class="text-xs mt-2 text-blue-600">Credit: {{ leysSalesFormatter(customer.credit_limit) }}</p>
          </div>
        </div>
      </div>

      <!-- Step 2: Add Products -->
      <div v-if="currentStep === 2">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
          <h2 class="text-xl font-semibold">Add Products</h2>
          <button
            @click="currentStep = 3"
            :disabled="orderItems.length === 0"
            class="px-5 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            Review Order →
          </button>
        </div>

        <LInput
          v-model="productSearch"
          label="Search Products"
          placeholder="Name or SKU..."
          class="max-w-md mb-4"
        />

        <!-- Selected Items Summary -->
        <div v-if="orderItems.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-4 py-2 text-left">Product</th>
                  <th class="px-3 py-2 text-center">Qty</th>
                  <th class="px-3 py-2 text-center">Avail</th>
                  <th class="px-4 py-2 text-right">Price</th>
                  <th class="px-3 py-2 text-right">Disc %</th>
                  <th class="px-4 py-2 text-right">Total</th>
                  <th class="px-2"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in orderItems" :key="item.product.id" class="border-t dark:border-gray-700">
                  <td class="px-4 py-2">{{ item.product.name }}</td>
                  <td class="px-3 py-2 text-center">
                    <input
                      type="number"
                      v-model.number="item.quantity"
                      min="1"
                      :max="getAvailableStock(item.product)"
                      class="w-16 px-1 py-0.5 border rounded text-center text-xs"
                    />
                  </td>
                  <td class="px-3 py-2 text-center text-green-600 font-medium">{{ getAvailableStock(item.product) }}</td>
                  <td class="px-4 py-2 text-right">{{ leysSalesFormatter(item.product.price) }}</td>
                  <td class="px-3 py-2 text-center">
                    <input
                      type="number"
                      v-model.number="item.discountPercent"
                      min="0"
                      max="100"
                      class="w-16 px-1 py-0.5 border rounded text-center text-xs"
                    />
                  </td>
                  <td class="px-4 py-2 text-right font-semibold text-xs">
                    {{ leysSalesFormatter(item.product.price * item.quantity * (1 - item.discountPercent/100)) }}
                  </td>
                  <td class="px-2 text-center">
                    <button @click="removeItem(index)" class="text-red-600 hover:text-red-800">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Product Grid  -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border hover:border-blue-500 cursor-pointer transition text-sm"
            @click="addItem(product, 1)"
          >
            <h3 class="font-bold mb-1">{{ product.name }}</h3>
            <p class="text-xs text-gray-500">{{ product.sku }}</p>
            <p class="font-semibold text-blue-600 mt-1">{{ leysSalesFormatter(product.price) }}</p>
            <p class="text-xs mt-1">Avail: <span class="font-bold text-green-600">{{ getAvailableStock(product) }}</span></p>
          </div>
        </div>
      </div>

      <!-- Step 3: Review & Submit -->
      <div v-if="currentStep === 3">
        <h2 class="text-xl font-semibold mb-4">Order Review</h2>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <h3 class="font-semibold text-base mb-2">Customer</h3>
              <p class="font-bold">{{ selectedCustomer?.name }}</p>
              <p class="text-sm">{{ selectedCustomer?.contact_person }} • {{ selectedCustomer?.phone }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ selectedCustomer?.physical_address.city }}</p>
            </div>
            <div class="text-right">
              <p class="text-xs text-gray-600 dark:text-gray-400">Order Date</p>
              <p class="font-bold">{{ new Date().toLocaleDateString() }}</p>
            </div>
          </div>

          <table class="w-full mb-6 text-xs">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="text-left py-2 px-3">Item</th>
                <th class="text-center py-2 px-3">Qty</th>
                <th class="text-right py-2 px-3">Unit Price</th>
                <th class="text-right py-2 px-3">Disc</th>
                <th class="text-right py-2 px-3">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in orderItems" :key="item.product.id" class="border-t dark:border-gray-700">
                <td class="py-2 px-3">{{ item.product.name }}</td>
                <td class="text-center py-2 px-3">{{ item.quantity }}</td>
                <td class="text-right py-2 px-3">{{ leysSalesFormatter(item.product.price) }}</td>
                <td class="text-right py-2 px-3">{{ item.discountPercent }}%</td>
                <td class="text-right py-2 px-3 font-semibold">
                  {{ leysSalesFormatter(item.product.price * item.quantity * (1 - item.discountPercent/100)) }}
                </td>
              </tr>
            </tbody>
          </table>

          <div class="border-t dark:border-gray-700 pt-4 space-y-2 text-right text-sm">
            <div class="flex justify-end gap-8">
              <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
              <span class="font-bold">{{ leysSalesFormatter(subtotal) }}</span>
            </div>
            <div class="flex justify-end gap-8">
              <span class="text-gray-600 dark:text-gray-400">Discount:</span>
              <span class="text-orange-600 font-bold">- {{ leysSalesFormatter(totalDiscount) }}</span>
            </div>
            <div class="flex justify-end gap-8">
              <span class="text-gray-600 dark:text-gray-400">Tax (16%):</span>
              <span class="font-bold">{{ leysSalesFormatter(taxAmount) }}</span>
            </div>
            <div class="flex justify-end gap-8 border-t dark:border-gray-700 pt-3">
              <span class="text-lg font-bold text-gray-900 dark:text-white">Grand Total:</span>
              <span class="text-xl font-bold text-blue-600">{{ leysSalesFormatter(grandTotal) }}</span>
            </div>
          </div>
        </div>

        <div class="flex justify-between">
          <LButton @click="currentStep = 2" variant="secondary" class="text-sm px-4 py-2">← Back</LButton>
          <LButton @click="submitOrder" :loading="loading" class="text-sm px-4 py-2">
            Submit Order
          </LButton>
        </div>
      </div>
    </div>
  </LDefaultLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationsStore } from '@/stores/notifications'
import { leysSalesFormatter } from '@/utils/leysSalesFormatter'
import customers from '@/assets/data/customers.json'
import products from '@/assets/data/products.json'
import LDefaultLayout from '@/layouts/L-DefaultLayout.vue'
import LInput from '@/components/L-Input.vue'
import LButton from '@/components/L-Button.vue'

const router = useRouter()
const notificationsStore = useNotificationsStore()

const loading = ref(false)
const currentStep = ref(1)
const customerSearch = ref('')
const selectedCustomer = ref(null)
const productSearch = ref('')
const orderItems = ref([])

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

function getAvailableStock(product) {
  return product.stock.reduce((sum, s) => sum + (s.quantity - s.reserved), 0)
}

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

function removeItem(index) {
  orderItems.value.splice(index, 1)
}

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

const taxAmount = computed(() => subtotal.value * 0.16)
const grandTotal = computed(() => subtotal.value + taxAmount.value)

async function submitOrder() {
  loading.value = true
  await new Promise(resolve => setTimeout(resolve, 1500))

  notificationsStore.addNotification('success', `Order created! Total: ${leysSalesFormatter(grandTotal.value)}`)

  selectedCustomer.value = null
  orderItems.value = []
  currentStep.value = 1

  loading.value = false
  router.push('/dashboard')
}
</script>
/*** @component: L-CustomerMap
 * @created-date: 30-12-2025
 * @leysco-version: 1.0.0
 * @description: Interactive customer map view using Leaflet.js displaying customer locations from mock data with markers, rich popups (name, contact, address), and clickable sidebar list for quick navigation and marker focus.
 */
 
 <template>
  <LDefaultLayout>
    <template #breadcrumb>Customer Map</template>

    <div class="max-w-full mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Customer Locations</h1>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Map -->
        <div class="lg:col-span-3">
          <div
            ref="mapContainer"
            class="h-96 lg:h-screen lg:sticky lg:top-20 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700"
            style="min-height: 600px;"
          ></div>
        </div>

        <!-- Customer List Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold mb-6">Customers ({{ customers.length }})</h2>
            <div class="space-y-4 max-h-96 lg:max-h-full overflow-y-auto">
              <div
                v-for="customer in customers"
                :key="customer.id"
                class="p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer border border-gray-200 dark:border-gray-600"
                @click="() => {
                  const marker = markers.find(m => {
                    const latlng = m.getLatLng()
                    return Math.abs(latlng.lat - customer.location.latitude) < 0.001 &&
                           Math.abs(latlng.lng - customer.location.longitude) < 0.001
                  })
                  if (marker) {
                    map.setView(marker.getLatLng(), 12)
                    marker.openPopup()
                  }
                }"
              >
                <h3 class="font-bold text-lg">{{ customer.name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ customer.contact_person }}</p>
                <p class="text-xs mt-1 text-blue-600">{{ customer.phone }}</p>
                <p class="text-xs mt-2 text-gray-500">{{ customer.physical_address.city }}</p>
                <div class="mt-3 flex gap-2">
                  <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                    {{ customer.category }}
                  </span>
                  <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                    {{ customer.type }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LDefaultLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css' 
import customers from '@/assets/data/customers.json'
import LDefaultLayout from '@/layouts/L-DefaultLayout.vue'

const mapContainer = ref(null)
let map = null
let markers = []

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png'
})

onMounted(() => {
  // Initialize map centered on Kenya
  map = L.map(mapContainer.value).setView([-1.2921, 36.8219], 6)

  // OpenStreetMap tiles 
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map)

  // Add customer markers
  customers.forEach(customer => {
    if (customer.location?.latitude && customer.location?.longitude) {
      const marker = L.marker([
        customer.location.latitude,
        customer.location.longitude
      ]).addTo(map)

      // Custom popup
      const popupContent = `
        <div class="p-2">
          <h3 class="font-bold text-lg">${customer.name}</h3>
          <p class="text-sm"><strong>Contact:</strong> ${customer.contact_person}</p>
          <p class="text-sm"><strong>Phone:</strong> ${customer.phone}</p>
          <p class="text-sm"><strong>Email:</strong> ${customer.email}</p>
          <hr class="my-2 border-gray-300">
          <p class="text-xs text-gray-600">
            ${customer.physical_address.street}<br>
            ${customer.physical_address.city}, ${customer.physical_address.region}
          </p>
          <p class="text-xs mt-2"><strong>Category:</strong> ${customer.category} | <strong>Type:</strong> ${customer.type}</p>
        </div>
      `

      marker.bindPopup(popupContent, {
        maxWidth: 300
      })

      markers.push(marker)
    }
  })

  // Fit map to show all markers
  if (markers.length > 0) {
    const group = new L.featureGroup(markers)
    map.fitBounds(group.getBounds().pad(0.3))
  }
})

onUnmounted(() => {
  if (map) {
    map.remove()
  }
})
</script>

<style scoped>
/* Leaflet container */
:deep(.leaflet-container) {
  width: 100%;
  height: 100%;
}
</style>
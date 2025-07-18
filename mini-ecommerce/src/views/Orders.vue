<template>
  <div class="max-w-5xl mx-auto p-6 bg-white shadow-xl rounded-2xl mt-8 text-black">
    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <svg class="animate-spin h-14 w-14 text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <p class="mt-4 text-purple-600 font-semibold">Chargement de vos commandes...</p>
    </div>
    <div v-else>
      <h2 class="text-3xl font-bold mb-6 text-purple-700">Mes commandes</h2>
      <div v-if="orders.length === 0" class="text-gray-500 text-center py-8">
        Aucune commande trouvée.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
          <tr class="bg-purple-100 text-purple-700 uppercase">
            <th class="px-6 py-3 text-left">Commande</th>
            <th class="px-6 py-3 text-left">Date</th>
            <th class="px-6 py-3 text-left">Total</th>
            <th class="px-6 py-3 text-left">Articles</th>
            <th class="px-6 py-3 text-left">Actions</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="order in orders" :key="order.id" class="hover:bg-purple-50 transition">
            <td class="px-6 py-4 font-semibold">#{{ order.id }}</td>
            <td class="px-6 py-4">{{ formatDate(order.date) }}</td>
            <td class="px-6 py-4 font-bold text-purple-700">{{ formatPrice(order.total) }} €</td>
            <td class="px-6 py-4">{{ order.itemsCount }}</td>
            <td class="px-6 py-4 flex flex-col md:flex-row gap-2">
              <button
                  @click="openOrderDetail(order.id)"
                  class="bg-purple-600 text-white py-2 px-4 rounded-lg shadow hover:bg-purple-700 transition text-center"
              >
                Voir le détail
              </button>
              <button
                  v-if="order.invoiceId"
                  @click="downloadInvoice(order.invoiceId)"
                  class="bg-green-600 text-white py-2 px-4 rounded-lg shadow hover:bg-green-700 transition text-center"
              >
                Télécharger la facture
              </button>
              <span v-else class="text-gray-400 text-xs mt-1 md:mt-0">Facture non dispo</span>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
      <div v-if="error" class="text-red-600 bg-red-50 px-4 py-2 rounded shadow-lg mt-6 text-center">{{ error }}</div>
    </div>

    <!-- Modal détail commande -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-4xl w-full relative overflow-y-auto max-h-[90vh]">
        <button @click="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-purple-700 text-2xl">&times;</button>
        <div v-if="detailLoading" class="flex flex-col items-center justify-center py-16">
          <svg class="animate-spin h-14 w-14 text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="mt-4 text-purple-600 font-semibold">Chargement des détails de la commande...</p>
        </div>
        <div v-else-if="detailError" class="text-red-600 bg-red-50 px-4 py-2 rounded shadow-lg text-center">
          {{ detailError }}
        </div>
        <div v-else-if="orderDetail">
          <div class="flex items-center gap-4 mb-6">
            <div class="flex items-center gap-2">
              <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <h2 class="text-3xl font-bold">Commande #{{ orderDetail.id }}</h2>
            </div>
          </div>
          <div class="flex flex-col md:flex-row gap-6 mb-6">
            <div class="flex-1 bg-white rounded-xl shadow-lg p-5">
              <div class="flex items-center gap-2 mb-2">
                <span class="text-gray-500">Date :</span>
                <span class="font-medium">{{ formatDate(orderDetail.date) }}</span>
              </div>
              <div class="flex items-center gap-2 mb-2">
                <span class="text-gray-500">Total :</span>
                <span class="font-bold text-purple-700 text-lg">{{ formatPrice(orderDetail.total) }} €</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-gray-500">Produits :</span>
                <span class="font-medium">{{ totalQuantity }}</span>
              </div>
            </div>
            <div class="flex-1 flex flex-col gap-4">
              <div class="bg-white rounded-xl shadow-lg p-5">
                <h4 class="font-semibold mb-1 text-purple-700">Adresse de livraison</h4>
                <p class="text-gray-700 text-sm">
                  {{ orderDetail.shippingAddress?.street }}<br>
                  {{ orderDetail.shippingAddress?.postalCode }} {{ orderDetail.shippingAddress?.city }}
                </p>
              </div>
              <div class="bg-white rounded-xl shadow-lg p-5">
                <h4 class="font-semibold mb-1 text-purple-700">Adresse de facturation</h4>
                <p class="text-gray-700 text-sm">
                  {{ orderDetail.billingAddress?.street }}<br>
                  {{ orderDetail.billingAddress?.postalCode }} {{ orderDetail.billingAddress?.city }}
                </p>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-xl shadow-xl p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4 text-purple-700">Produits de la commande</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                <tr class="bg-purple-100 text-purple-700 uppercase">
                  <th class="px-6 py-3 text-left">Produit</th>
                  <th class="px-6 py-3 text-left">Type</th>
                  <th class="px-6 py-3 text-left">Quantité</th>
                  <th class="px-6 py-3 text-left">Prix Unitaire</th>
                  <th class="px-6 py-3 text-left">Total</th>
                  <th class="px-6 py-3 text-left">Statut</th>
                  <th class="px-6 py-3 text-left">Action</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in orderDetail.items" :key="item.id" class="hover:bg-purple-50 transition">
                  <td class="px-6 py-4 flex items-center gap-3">
                    <img v-if="item.product.image" :src="item.product.image" :alt="item.product.name" class="w-14 h-14 object-cover rounded shadow" />
                    <span>{{ item.product.name }}</span>
                  </td>
                  <td class="px-6 py-4">{{ getProductType(item.product) }}</td>
                  <td class="px-6 py-4">{{ item.quantity }}</td>
                  <td class="px-6 py-4">{{ formatPrice(item.product.price) }} €</td>
                  <td class="px-6 py-4">{{ formatPrice(item.product.price * item.quantity) }} €</td>
                  <td class="px-6 py-4">
                    <template v-if="getProductType(item.product) === 'Produit digital'">
                      <a v-if="item.product.downloadLink" :href="item.product.downloadLink" class="text-blue-600 hover:underline font-semibold">Télécharger</a>
                      <span v-else class="text-gray-400">Lien non dispo</span>
                    </template>
                    <template v-else>
                      <span class="inline-block bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">Préparation</span>
                    </template>
                  </td>
                  <td class="px-6 py-4">
                    <a
                        :href="`/product/${item.product.id}?category=${item.product.defaultCategory?.id || ''}`"
                        class="inline-block bg-purple-600 text-white py-2 px-4 rounded-lg shadow hover:bg-purple-700 transition"
                        target="_blank"
                    >
                      Voir l'article
                    </a>
                  </td>
                </tr>
                </tbody>
              </table>
            </div>
            <div v-if="!orderDetail.items || orderDetail.items.length === 0" class="text-gray-500 text-center py-8">
              Aucun produit trouvé pour cette commande.
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal -->
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const orders = ref([])
const loading = ref(true)
const error = ref('')

const showModal = ref(false)
const orderDetail = ref(null)
const detailLoading = ref(false)
const detailError = ref('')

function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleString('fr-FR')
}

function formatPrice(price) {
  return Number(price).toFixed(2).replace('.', ',')
}

async function downloadInvoice(invoiceId) {
  try {

    if (window._paq) {
      window._paq.push([
        'trackEvent',
        'Facture',
        'Téléchargement',
        invoiceId
      ]);
    }

    const token = localStorage.getItem('token')
    const res = await axios.get(`/api/invoices/${invoiceId}/download`, {
      headers: { Authorization: `Bearer ${token}` },
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `facture-${invoiceId}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    error.value = "Erreur lors du téléchargement de la facture."
  }
}

async function openOrderDetail(orderId) {
  showModal.value = true
  detailLoading.value = true
  detailError.value = ''
  orderDetail.value = null
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get(`/api/orders_details/${orderId}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    orderDetail.value = res.data.order
  } catch (e) {
    detailError.value = "Impossible de charger le détail de la commande."
  } finally {
    detailLoading.value = false
  }
}

function closeModal() {
  showModal.value = false
  orderDetail.value = null
}

const totalQuantity = computed(() =>
    orderDetail.value && orderDetail.value.items
        ? orderDetail.value.items.reduce((sum, item) => sum + (item.quantity || 0), 0)
        : 0
)

function getProductType(product) {
  if (!product) return ''
  if (product['@type'] && product['@type'].includes('PhysicalProduct')) return 'Produit physique'
  if (product['@type'] && product['@type'].includes('DigitalProduct')) return 'Produit digital'
  return product.productType || 'Type inconnu'
}

onMounted(async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('/api/my-orders', {
      headers: { Authorization: `Bearer ${token}` }
    })
    orders.value = res.data.orders || []
  } catch (e) {
    error.value = "Impossible de charger vos commandes."
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg);}
  to { transform: rotate(360deg);}
}
</style>
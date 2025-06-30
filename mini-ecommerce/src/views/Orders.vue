<template>
  <div class="min-h-screen flex items-center justify-center bg-white py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full space-y-8">
      <AlertMessage :message="success" type="success" />
      <AlertMessage :message="error" type="error" />
      <div v-if="isLoading" class="flex flex-col items-center justify-center w-full py-12">
        <svg class="animate-spin h-12 w-12 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-4 text-purple-600 font-semibold">Chargement des commandes...</p>
      </div>
      <div v-else>
        <h2 class="text-center text-3xl font-extrabold text-gray-900 drop-shadow">Mes commandes</h2>
        <div v-if="orders.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
          <p class="text-gray-500 mb-4 text-lg">Vous n'avez pas encore passé de commande.</p>
        </div>
        <div v-else class="space-y-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Historique de vos commandes</h3>
            <span class="bg-gray-100 text-black text-xs font-semibold px-3 py-1 rounded-full">{{ orders.length }} commande{{ orders.length > 1 ? 's' : '' }}</span>
          </div>
          <div v-for="order in orders" :key="order.id" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition-shadow border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="text-black font-bold text-lg">#{{ order.id }}</span>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded">Passée le {{ order.date }}</span>
              </div>
              <div>
                <span class="text-black font-medium">
                {{ order.itemsCount }} produit{{ order.itemsCount > 1 ? 's' : '' }}
                </span>
                <span class="text-black font-medium block mt-1">
                  Total : <span class="text-black">{{ order.total }} €</span>
              </span>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <div class="flex items-center gap-2">
                <router-link
                    :to="`/orders/${order.id}`"
                    class="inline-flex items-center px-5 py-2 bg-purple-600 text-white font-semibold rounded-lg shadow hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-400 transition"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 12H9m12 0A9 9 0 11 3 12a9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Détails
                </router-link>
                <button
                    v-if="order.invoiceId"
                    @click="downloadInvoice(order.id, order.invoiceId)"
                    class="inline-flex items-center px-5 py-2 bg-red-600 text-white font-semibold rounded-lg shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:white transition"
                    type="button"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Facture
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal détails commande (inchangé, mais couleurs neutres) -->
    <transition name="fade">
      <div v-if="showDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-lg relative animate-fadeIn">
          <button @click="showDetailsModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 transition">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <h3 class="text-xl font-bold text-gray-800 mb-2">Commande #{{ orderDetails.id }}</h3>
          <p class="text-gray-600 mb-2">Date : <span class="font-medium">{{ orderDetails.date }}</span></p>
          <p class="text-gray-600 mb-2">Total : <span class="font-medium text-gray-900">{{ orderDetails.total }} €</span></p>
          <div class="mb-4">
            <h4 class="font-semibold text-gray-800 mb-1">Adresse de livraison :</h4>
            <p class="text-gray-700 text-sm">
              {{ orderDetails.shippingAddress?.street }}, {{ orderDetails.shippingAddress?.postalCode }} {{ orderDetails.shippingAddress?.city }}
            </p>
          </div>
          <div>
            <h4 class="font-semibold text-gray-800 mb-1">Produits :</h4>
            <ul class="divide-y divide-gray-50">
              <li v-for="item in orderDetails.items" :key="item.product.id" class="py-2 flex justify-between items-center">
                <span class="text-gray-700">{{ item.product.name }}</span>
                <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-0.5 rounded">{{ item.quantity }}x</span>
              </li>
            </ul>
          </div>
          <button
              @click="showDetailsModal = false"
              class="mt-6 w-full py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition"
          >
            Fermer
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AlertMessage from '@/components/AlertMessage.vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const orders = ref([])
const success = ref('')
const error = ref('')
const isLoading = ref(true)
const showDetailsModal = ref(false)
const orderDetails = ref({})

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }
  try {
    const res = await axios.get('/api/my-orders', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    orders.value = res.data.orders
  } catch (err) {
    error.value = "Impossible de charger les commandes."
  } finally {
    isLoading.value = false
  }
})

const downloadInvoice = async (orderId, invoiceId) => {
  try {
    isLoading.value = true
    const token = localStorage.getItem('token')
    const response = await axios.get(`/api/invoices/${invoiceId}/download`, {
      headers: {Authorization: `Bearer ${token}`},
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `facture-${orderId}.pdf`)
    document.body.appendChild(link)
    link.click()

    window.URL.revokeObjectURL(url)
    document.body.removeChild(link)
  } catch (err) {
    error.value = "Erreur lors du téléchargement de la facture"
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const showOrderDetails = async (orderId) => {
  const token = localStorage.getItem('token')
  try {
    const res = await axios.get(`/api/orders/${orderId}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    orderDetails.value = res.data
    showDetailsModal.value = true
  } catch (err) {
    error.value = "Impossible de charger le détail de la commande."
  }
}
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg);}
  to { transform: rotate(360deg);}
}
.transition-shadow {
  transition: box-shadow 0.3s ease;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.animate-fadeIn {
  animation: fadeIn 0.3s;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(30px);}
  to { opacity: 1; transform: translateY(0);}
}
</style>
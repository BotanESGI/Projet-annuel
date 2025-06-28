<template>
  <div class="max-w-5xl mx-auto p-6 bg-white shadow-xl rounded-2xl mt-8 text-black">
    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <svg class="animate-spin h-14 w-14 text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <p class="mt-4 text-purple-600 font-semibold">Chargement des détails de la commande...</p>
    </div>
    <div v-else>
      <div class="flex items-center gap-4 mb-6">
        <div class="flex items-center gap-2">
          <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h2 class="text-3xl font-bold">Commande #{{ order.id }}</h2>
        </div>
      </div>
      <div class="flex flex-col md:flex-row gap-6 mb-6">
        <div class="flex-1 bg-white rounded-xl shadow-lg p-5">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-gray-500">Date :</span>
            <span class="font-medium">{{ formatDate(order.date) }}</span>
          </div>
          <div class="flex items-center gap-2 mb-2">
            <span class="text-gray-500">Total :</span>
            <span class="font-bold text-purple-700 text-lg">{{ formatPrice(order.total) }} €</span>
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
              {{ order.shippingAddress?.street }}<br>
              {{ order.shippingAddress?.postalCode }} {{ order.shippingAddress?.city }}
            </p>
          </div>
          <div class="bg-white rounded-xl shadow-lg p-5">
            <h4 class="font-semibold mb-1 text-purple-700">Adresse de facturation</h4>
            <p class="text-gray-700 text-sm">
              {{ order.billingAddress?.street }}<br>
              {{ order.billingAddress?.postalCode }} {{ order.billingAddress?.city }}
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
            <tr v-for="item in order.items" :key="item.id" class="hover:bg-purple-50 transition">
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
                <router-link
                    :to="`/product/${item.product.id}?category=${item.product.defaultCategory?.id || ''}`"
                    class="inline-block bg-purple-600 text-white py-2 px-4 rounded-lg shadow hover:bg-purple-700 transition"
                >
                  Voir l'article
                </router-link>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <div v-if="!order.items || order.items.length === 0" class="text-gray-500 text-center py-8">
          Aucun produit trouvé pour cette commande.
        </div>
      </div>
      <div class="flex justify-between items-center mt-8">
        <router-link to="/orders" class="text-purple-700 hover:underline font-semibold flex items-center">
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Retour à l'historique
        </router-link>
        <div v-if="error" class="text-red-600 bg-red-50 px-4 py-2 rounded shadow-lg">{{ error }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const order = ref({})
const error = ref('')
const loading = ref(true)

const totalQuantity = computed(() =>
    order.value.items
        ? order.value.items.reduce((sum, item) => sum + (item.quantity || 0), 0)
        : 0
)

function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleString('fr-FR')
}

function formatPrice(price) {
  return Number(price).toFixed(2).replace('.', ',')
}

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
    const res = await axios.get(`/api/orders_details/${route.params.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    order.value = res.data.order
  } catch (err) {
    error.value = "Impossible de charger le détail de la commande."
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
.bg-gradient-to-br {
  background: linear-gradient(135deg, #f3e8ff 0%, #e0f2fe 100%);
}
</style>
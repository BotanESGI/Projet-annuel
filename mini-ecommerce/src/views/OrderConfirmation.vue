<template>
  <div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-8 mt-10">
    <h2 class="text-2xl font-bold text-green-600 mb-4">Commande confirmée !</h2>
    <div v-if="loading" class="text-gray-500">Chargement...</div>
    <div v-else-if="error" class="text-red-500">{{ error }}</div>
    <div v-else>
      <div class="mb-4">
        <p class="mb-1 text-black">Numéro de commande : <b class="text-black">{{ order.id }}</b></p>
        <p class="text-black">
          Date : {{ new Date(order.date.replace(' ', 'T')).toLocaleString() }}
        </p>
      </div>
      <h3 class="mt-6 font-semibold text-lg text-black">Produits commandés</h3>
      <ul class="divide-y divide-gray-200 mb-6">
        <li v-for="item in order.items" :key="item.id" class="flex items-center gap-4 py-2">
          <img :src="item.product.image" alt="Image produit" class="w-16 h-16 object-cover rounded" />
          <div class="flex-1">
            <router-link
                :to="`/product/${item.product.id}`"
                class="text-blue-700 font-semibold hover:underline"
            >
              {{ item.product.name }}
            </router-link>
            <div class="text-gray-500 text-sm mt-1">
              Prix unitaire : <span class="text-black">{{ item.product.price.toFixed(2) }} €</span>
            </div>
            <div class="text-gray-500 text-sm">
              Quantité : <span class="text-black">{{ item.quantity }}</span>
            </div>
            <div class="text-gray-700 font-medium mt-1">
              Sous-total : <span class="text-black">{{ (item.product.price * item.quantity).toFixed(2) }} €</span>
            </div>
          </div>
        </li>
      </ul>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <h3 class="font-semibold mb-2 text-black">Adresse de livraison</h3>
          <div class="bg-gray-50 rounded p-3">
            <div class="text-black">{{ order.shippingAddress.street }}</div>
            <div class="text-black">{{ order.shippingAddress.postalCode }} {{ order.shippingAddress.city }}</div>
          </div>
        </div>
        <div>
          <h3 class="font-semibold mb-2 text-black">Adresse de facturation</h3>
          <div class="bg-gray-50 rounded p-3">
            <div class="text-black">{{ order.billingAddress.street }}</div>
            <div class="text-black">{{ order.billingAddress.postalCode }} {{ order.billingAddress.city }}</div>
          </div>
        </div>
      </div>
      <div class="mt-4 font-bold text-lg flex justify-between">
        <span class="text-black">Total :</span>
        <span class="text-black">{{ order.total.toFixed(2) }} €</span>
      </div>
      <div class="mt-8 flex flex-col gap-4">
        <router-link to="/orders" class="inline-block text-blue-600 underline">
          Voir mes commandes
        </router-link>
        <button
            v-if="order.invoiceId"
            @click="downloadInvoice"
            class="inline-block text-blue-600 underline text-left cursor-pointer"
            type="button"
        >
          Télécharger la facture
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

const order = ref({})
const loading = ref(true)
const error = ref('')

const downloadInvoice = async () => {
  try {
    loading.value = true
    const token = localStorage.getItem('token')
    const response = await axios.get(`/api/invoices/${order.value.invoiceId}/download`, {
      headers: {Authorization: `Bearer ${token}`},
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `facture-${order.value.id}.pdf`)
    document.body.appendChild(link)
    link.click()

    window.URL.revokeObjectURL(url)
    document.body.removeChild(link)
  } catch (e) {
    error.value = "Erreur lors du téléchargement de la facture"
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  const confirmedId = sessionStorage.getItem('orderConfirmedId')
  if (confirmedId !== route.params.id) {
    router.replace('/')
    return
  }
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get(`/api/orders/${route.params.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    order.value = res.data.order
    if (window._paq) {
      window._paq.push([
        'trackEvent',
        'Commande',
        'Finalisation',
        order.id,
        order.total
      ]);
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors du chargement de la commande.'
  } finally {
    loading.value = false
    sessionStorage.removeItem('orderConfirmedId')
  }
})
</script>
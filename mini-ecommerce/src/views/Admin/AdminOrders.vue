<template>
  <div class="p-8 min-h-screen text-black">
    <h1 class="text-3xl font-bold mb-6 text-black">Gestion des commandes (CRUD)</h1>
    <button @click="openForm()" class="mb-4 px-4 py-2 bg-blue-600 text-white rounded">Ajouter une commande</button>
    <div v-if="loading" class="flex justify-center items-center h-40">
      <span class="loader"></span>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white rounded shadow text-black">
        <thead>
        <tr>
          <th class="py-3 px-4 text-left">ID</th>
          <th class="py-3 px-4 text-left">Utilisateur</th>
          <th class="py-3 px-4 text-left">Articles</th>
          <th class="py-3 px-4 text-left">Total</th>
          <th class="py-3 px-4 text-left">Date</th>
          <th class="py-3 px-4 text-left">Adresse livraison</th>
          <th class="py-3 px-4 text-left">Adresse facturation</th>
          <th class="py-3 px-4 text-left">Facture</th>
          <th class="py-3 px-4 text-left">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="order in orders" :key="order.id" class="text-black">
          <td class="py-2 px-4">{{ order.id }}</td>
          <td class="py-2 px-4">
            <span v-if="order.user">{{ order.user.name }} {{ order.user.lastname }}<br>
              <span class="text-xs text-gray-500">{{ order.user.email }}</span>
            </span>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4">
            <ul>
              <li v-for="item in order.orderItems" :key="item.id" class="mb-2 flex items-center gap-3">
                <img
                    v-if="item.product?.image"
                    :src="item.product.image"
                    :alt="`Image de ${item.product.name}`"
                    class="w-12 h-12 object-cover rounded shadow"
                />
                <div>
                  <div class="font-semibold">{{ item.product?.name }}</div>
                  <div class="text-gray-500 text-sm">{{ item.product?.description }}</div>
                  <div class="text-blue-700 font-bold">{{ formatPrice(item.product?.price) }} €</div>
                  <div>Quantité : <span class="font-semibold">{{ item.quantity }}</span></div>
                </div>
              </li>
              <li v-if="order.orderItems.length === 0" class="text-gray-400 italic">Aucun article</li>
            </ul>
          </td>
          <td class="py-2 px-4">{{ formatPrice(order.total) }} €</td>
          <td class="py-2 px-4">{{ order.date }}</td>
          <td class="py-2 px-4">
            <div v-if="order.shippingAddress">
              {{ order.shippingAddress.shippingStreet }}, {{ order.shippingAddress.shippingCity }}<br>
              {{ order.shippingAddress.shippingPostalCode }}
            </div>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4">
            <div v-if="order.billingAddress">
              {{ order.billingAddress.billingStreet }}, {{ order.billingAddress.billingCity }}<br>
              {{ order.billingAddress.billingPostalCode }}
            </div>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4">
            <button
                v-if="order.invoice_pdf_path"
                @click="downloadInvoicePdf(order)"
                class="text-blue-600 underline hover:text-blue-800"
                type="button"
            >
              Télécharger la facture
            </button>
            <span v-else class="text-gray-400 italic">Aucune</span>
          </td>
          <td class="py-2 px-4 flex gap-2">
            <button
                @click="openForm(order)"
                class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-black rounded"
            >Modifier</button>
            <button
                @click="deleteOrder(order.id)"
                :disabled="deleteLoadingId === order.id"
                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded"
            >
              <span v-if="deleteLoadingId === order.id" class="loader-xs"></span>
              <span v-else>Supprimer</span>
            </button>
          </td>
        </tr>
        <tr v-if="orders.length === 0">
          <td colspan="8" class="text-center py-6 text-gray-400">Aucune commande trouvée.</td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Formulaire d'ajout/modification -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative text-black form-modal-scroll">
        <h2 class="text-2xl font-bold mb-4 text-black">{{ editingOrder ? 'Modifier' : 'Ajouter' }} une commande</h2>
        <form @submit="handleSubmit" class="text-black">
          <div class="mb-4">
            <label class="block mb-1">Utilisateur</label>
            <select v-model="form.user" required class="w-full border rounded px-3 py-2">
              <option disabled value="">-- Choisir --</option>
              <option v-for="user in users" :key="user.id" :value="user.id.toString()">
                {{ user.name }} {{ user.lastname }} ({{ user.email }})
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Articles de la commande</label>
            <div v-for="(item, idx) in form.orderItems" :key="idx" class="flex gap-2 mb-2 items-center">
              <img
                  v-if="getProductById(item.product)?.image"
                  :src="getProductById(item.product).image"
                  :alt="`Image de ${getProductById(item.product).name}`"
                  class="w-10 h-10 object-cover rounded shadow"
              />
              <select v-model="item.product" required class="border rounded px-2 py-1">
                <option value="" disabled>Produit</option>
                <option v-for="prod in products" :key="prod.id" :value="prod.id.toString()">
                  {{ prod.name }}
                </option>
              </select>
              <input type="number" v-model.number="item.quantity" min="1" required class="border rounded px-2 py-1 w-20" placeholder="Qté">
              <button type="button" @click="form.orderItems.splice(idx,1)" class="text-red-500 px-2 py-1">Suppr</button>
            </div>
            <button type="button" @click="form.orderItems.push({ product: '', quantity: 1 })" class="bg-blue-100 text-blue-700 px-3 py-1 rounded mt-2">
              + Ajouter un article
            </button>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Total</label>
            <input v-model="form.total" required type="number" step="0.01" class="w-full border rounded px-3 py-2" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">Adresse de livraison</label>
            <input v-model="form.shippingStreet" required type="text" class="w-full border rounded px-3 py-2" placeholder="Rue" />
            <input v-model="form.shippingCity" required type="text" class="w-full border rounded px-3 py-2 mt-2" placeholder="Ville" />
            <input v-model="form.shippingPostalCode" required type="text" class="w-full border rounded px-3 py-2 mt-2" placeholder="Code postal" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">Adresse de facturation</label>
            <input v-model="form.billingStreet" required type="text" class="w-full border rounded px-3 py-2" placeholder="Rue" />
            <input v-model="form.billingCity" required type="text" class="w-full border rounded px-3 py-2 mt-2" placeholder="Ville" />
            <input v-model="form.billingPostalCode" required type="text" class="w-full border rounded px-3 py-2 mt-2" placeholder="Code postal" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">Lien de la facture (PDF)</label>
            <input v-model="form.invoice_pdf_path" required type="text" class="w-full border rounded px-3 py-2" placeholder="URL ou chemin du PDF" />
          </div>
          <div v-if="formErrors.length" class="mb-4">
            <ul class="bg-red-100 text-red-700 rounded px-4 py-2">
              <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
            </ul>
          </div>
          <div class="flex justify-end gap-2 mt-6">
            <button type="button" @click="cancelForm" class="px-4 py-2 bg-gray-300 rounded">Annuler</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded" :disabled="formLoading">
              <span v-if="formLoading" class="loader-xs mr-2"></span>
              {{ editingOrder ? 'Enregistrer' : 'Ajouter' }}
            </button>
          </div>
        </form>
        <button
            @click="cancelForm"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl"
            title="Fermer"
        >×</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const orders = ref([])
const users = ref([])
const products = ref([])
const showForm = ref(false)
const editingOrder = ref(null)
const formLoading = ref(false)
const formErrors = ref([])
const loading = ref(false)
const deleteLoadingId = ref(null)

const form = ref({
  user: '',
  orderItems: [],
  total: '',
  shippingStreet: '',
  shippingCity: '',
  shippingPostalCode: '',
  billingStreet: '',
  billingCity: '',
  billingPostalCode: '',
  invoice_pdf_path: ''
})

const fetchOrders = async () => {
  loading.value = true
  const token = localStorage.getItem('token')
  try {
    const res = await axios.get('/api/admin_orders', {
      headers: { Authorization: `Bearer ${token}` }
    })
    orders.value = res.data['hydra:member'] || []
  } finally {
    loading.value = false
  }
}

const fetchUsers = async () => {
  const token = localStorage.getItem('token')
  const res = await axios.get('/api/users', {
    headers: { Authorization: `Bearer ${token}` }
  })
  users.value = res.data['hydra:member'] || []
}

const fetchProducts = async () => {
  const token = localStorage.getItem('token')
  const res = await axios.get('/api/products', {
    headers: { Authorization: `Bearer ${token}` }
  })
  products.value = res.data['hydra:member'] || []
}

const getProductById = (id) => {
  return products.value.find(p => p.id.toString() === id?.toString())
}

const downloadInvoicePdf = async (order) => {
  try {
    const token = localStorage.getItem('token')
    let url = order.invoice_pdf_path
    if (!/^https?:\/\//.test(url) && !url.startsWith('/api/')) {
      url = `/api/invoices/${order.id}/download`
    }
    const response = await axios.get(url, {
      headers: { Authorization: `Bearer ${token}` },
      responseType: 'blob'
    })
    const blobUrl = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = blobUrl
    link.setAttribute('download', `facture-${order.id}.pdf`)
    document.body.appendChild(link)
    link.click()
    window.URL.revokeObjectURL(blobUrl)
    document.body.removeChild(link)
  } catch (e) {
    alert('Erreur lors du téléchargement du PDF')
    console.error(e)
  }
}

const openForm = (order = null) => {
  showForm.value = true
  editingOrder.value = order
  if (order) {
    Object.assign(form.value, {
      user: order.user?.id?.toString() || '',
      orderItems: order.orderItems.map(item => ({
        product: item.product?.id?.toString() || '',
        quantity: item.quantity
      })),
      total: order.total,
      shippingStreet: order.shippingAddress?.shippingStreet || '',
      shippingCity: order.shippingAddress?.shippingCity || '',
      shippingPostalCode: order.shippingAddress?.shippingPostalCode || '',
      billingStreet: order.billingAddress?.billingStreet || '',
      billingCity: order.billingAddress?.billingCity || '',
      billingPostalCode: order.billingAddress?.billingPostalCode || '',
      invoice_pdf_path: order.invoice_pdf_path || ''
    })
  } else {
    Object.assign(form.value, {
      user: '',
      orderItems: [],
      total: '',
      shippingStreet: '',
      shippingCity: '',
      shippingPostalCode: '',
      billingStreet: '',
      billingCity: '',
      billingPostalCode: '',
      invoice_pdf_path: ''
    })
  }
  formErrors.value = []
}

const cancelForm = () => {
  showForm.value = false
  editingOrder.value = null
  formErrors.value = []
}

const handleSubmit = async (e) => {
  e.preventDefault()
  formLoading.value = true
  formErrors.value = []

  if (
      !form.value.user ||
      !form.value.total ||
      !form.value.shippingStreet ||
      !form.value.shippingCity ||
      !form.value.shippingPostalCode ||
      !form.value.billingStreet ||
      !form.value.billingCity ||
      !form.value.billingPostalCode ||
      !form.value.orderItems.length ||
      !form.value.invoice_pdf_path
  ) {
    formErrors.value = ['Tous les champs sont obligatoires.']
    formLoading.value = false
    return
  }

  const token = localStorage.getItem('token')
  let payload = {
    user: form.value.user.toString(),
    orderItems: form.value.orderItems.map(item => ({
      product: item.product.toString(),
      quantity: item.quantity
    })),
    total: form.value.total,
    shippingStreet: form.value.shippingStreet,
    shippingCity: form.value.shippingCity,
    shippingPostalCode: form.value.shippingPostalCode,
    billingStreet: form.value.billingStreet,
    billingCity: form.value.billingCity,
    billingPostalCode: form.value.billingPostalCode,
    invoice_pdf_path: form.value.invoice_pdf_path
  }
  try {
    if (editingOrder.value) {
      await axios.put(`/api/admin_orders/${editingOrder.value.id}`, payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    } else {
      await axios.post('/api/admin_orders', payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    }
    showForm.value = false
    editingOrder.value = null
    await fetchOrders()
  } catch (err) {
    if (err.response && err.response.data && err.response.data.violations) {
      formErrors.value = err.response.data.violations.map(v => v.message || v[0]?.message)
    } else if (err.response && err.response.data && err.response.data['hydra:description']) {
      formErrors.value = [err.response.data['hydra:description']]
    } else {
      formErrors.value = ['Erreur lors de la sauvegarde de la commande.']
    }
  } finally {
    formLoading.value = false
  }
}

const deleteOrder = async (id) => {
  if (confirm('Supprimer cette commande ?')) {
    deleteLoadingId.value = id
    const token = localStorage.getItem('token')
    try {
      await axios.delete(`/api/admin_orders/${id}`, {
        headers: { Authorization: `Bearer ${token}` }
      })
      await fetchOrders()
    } finally {
      deleteLoadingId.value = null
    }
  }
}

const formatPrice = (price) => {
  if (!price && price !== 0) return ''
  return parseFloat(price).toFixed(2).replace('.', ',')
}

onMounted(() => {
  fetchOrders()
  fetchUsers()
  fetchProducts()
})
</script>

<style scoped>
.loader {
  border: 4px solid #e5e7eb;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  animation: spin 1s linear infinite;
  display: inline-block;
}
.loader-xs {
  border: 2px solid #e5e7eb;
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  width: 16px;
  height: 16px;
  animation: spin 1s linear infinite;
  display: inline-block;
  vertical-align: middle;
}
@keyframes spin {
  to { transform: rotate(360deg);}
}
.form-modal-scroll {
  max-height: 80vh;
  overflow-y: auto;
}
</style>
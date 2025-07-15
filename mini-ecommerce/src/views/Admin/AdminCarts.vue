<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const carts = ref([])
const users = ref([])
const products = ref([])
const showAddForm = ref(false)
const editingCart = ref(null)
const loading = ref(false)
const deletingId = ref(null)
const formLoading = ref(false)
const formErrors = ref([])

const form = ref({
  user: '',
  items: []
})

const fetchCarts = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('/api/carts', {
      headers: { Authorization: `Bearer ${token}` }
    })
    carts.value = res.data['hydra:member'] || []
  } finally {
    loading.value = false
  }
}

const fetchUsers = async (onlyWithoutCart = false) => {
  const token = localStorage.getItem('token')
  const url = onlyWithoutCart ? '/api/users-without-cart' : '/api/users'
  const res = await axios.get(url, {
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

const openAddForm = async () => {
  showAddForm.value = true
  editingCart.value = null
  Object.assign(form.value, {
    user: '',
    items: []
  })
  formErrors.value = []
  await fetchUsers(true) // seulement les users sans panier
}

const editCart = async (cart) => {
  editingCart.value = { ...cart }
  showAddForm.value = true
  Object.assign(form.value, {
    user: cart.user?.id || '',
    items: cart.items.map(item => ({
      product: item.product?.id || '',
      quantity: item.quantity
    }))
  })
  formErrors.value = []
  await fetchUsers(false) // tous les users pour édition
}

const cancelEdit = () => {
  editingCart.value = null
  showAddForm.value = false
  formErrors.value = []
}

const handleSubmit = async (e) => {
  e.preventDefault()
  formLoading.value = true
  formErrors.value = []
  const token = localStorage.getItem('token')
  const payload = {
    user: form.value.user,
    items: form.value.items.map(item => ({
      product: item.product,
      quantity: item.quantity
    }))
  }
  try {
    if (editingCart.value) {
      await axios.put(`/api/carts/${editingCart.value.id}`, payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    } else {
      await axios.post('/api/carts', payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    }
    showAddForm.value = false
    editingCart.value = null
    await fetchCarts()
  } catch (err) {
    if (err.response && err.response.data && err.response.data.violations) {
      formErrors.value = err.response.data.violations.map(v => v.message || v[0]?.message)
    } else if (err.response && err.response.data && err.response.data['hydra:description']) {
      formErrors.value = [err.response.data['hydra:description']]
    } else {
      formErrors.value = ['Erreur lors de la sauvegarde du panier.']
    }
  } finally {
    formLoading.value = false
  }
}

const deleteCart = async (id) => {
  if (confirm('Supprimer ce panier ?')) {
    deletingId.value = id
    try {
      await axios.delete(`/api/carts/${id}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      })
      await fetchCarts()
    } finally {
      deletingId.value = null
    }
  }
}

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2).replace('.', ',')
}

// Fonction utilitaire pour retrouver un produit par son id
const getProductById = (id) => {
  return products.value.find(p => p.id === id)
}

onMounted(() => {
  fetchCarts()
  fetchProducts()
})
</script>

<template>
  <div class="p-8 min-h-screen text-black">
    <h1 class="text-3xl font-extrabold mb-6 text-black flex items-center gap-2">
      Gestion des paniers (CRUD)
    </h1>
    <button
        @click="openAddForm"
        class="mb-6 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow transition"
    >
      Ajouter un panier
    </button>
    <div v-if="loading" class="flex justify-center items-center h-40">
      <span class="loader"></span>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow-md overflow-hidden text-black">
        <thead>
        <tr class="bg-blue-50 text-black">
          <th class="py-3 px-4 text-left">ID</th>
          <th class="py-3 px-4 text-left">Utilisateur</th>
          <th class="py-3 px-4 text-left">Contenue du panier</th>
          <th class="py-3 px-4 text-left">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr
            v-for="cart in carts"
            :key="cart.id"
            class="hover:bg-blue-50 transition"
        >
          <td class="py-2 px-4">{{ cart.id }}</td>
          <td class="py-2 px-4">
            <span v-if="cart.user">
              {{ cart.user.name }} {{ cart.user.lastname }}<br>
              <span class="text-xs text-gray-500">{{ cart.user.email }}</span>
            </span>
          </td>
          <td class="py-2 px-4">
            <ul>
              <li
                  v-for="item in cart.items"
                  :key="item.id"
                  class="flex items-center gap-3 mb-2"
              >
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
              <li v-if="cart.items.length === 0" class="text-gray-400 italic">Aucun article</li>
            </ul>
          </td>
          <td class="py-2 px-4 flex gap-2">
            <button
                @click="editCart(cart)"
                class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-black rounded"
            >Modifier</button>
            <button
                @click="deleteCart(cart.id)"
                :disabled="deletingId === cart.id"
                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded"
            >
              <span v-if="deletingId === cart.id" class="loader-xs"></span>
              <span v-else>Supprimer</span>
            </button>
          </td>
        </tr>
        <tr v-if="carts.length === 0">
          <td colspan="4" class="text-center py-6 text-gray-400">Aucun panier trouvé.</td>
        </tr>
        </tbody>
      </table>
    </div>

    <div
        v-if="showAddForm || editingCart"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative form-modal-scroll text-black">
        <h2 class="text-2xl font-bold mb-4 text-black">
          {{ editingCart ? 'Modifier le panier' : 'Ajouter un panier' }}
        </h2>
        <form @submit="handleSubmit" class="text-black">
          <div class="mb-4">
            <label class="block mb-1">Utilisateur</label>
            <select v-model="form.user" required class="w-full border rounded px-3 py-2">
              <option value="" disabled>Choisir un utilisateur</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }} {{ user.lastname }} ({{ user.email }})
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Articles du panier</label>
            <div v-for="(item, idx) in form.items" :key="idx" class="flex gap-2 mb-2 items-center">
              <!-- Affichage de l'image du produit sélectionné -->
              <img
                  v-if="getProductById(item.product)?.image"
                  :src="getProductById(item.product).image"
                  :alt="`Image de ${getProductById(item.product).name}`"
                  class="w-10 h-10 object-cover rounded shadow"
              />
              <select v-model="item.product" required class="border rounded px-2 py-1">
                <option value="" disabled>Produit</option>
                <option v-for="prod in products" :key="prod.id" :value="prod.id">
                  {{ prod.name }}
                </option>
              </select>
              <input type="number" v-model.number="item.quantity" min="1" required class="border rounded px-2 py-1 w-20" placeholder="Qté">
              <button type="button" @click="form.items.splice(idx,1)" class="text-red-500 px-2 py-1">Suppr</button>
            </div>
            <button type="button" @click="form.items.push({ product: '', quantity: 1 })" class="bg-blue-100 text-blue-700 px-3 py-1 rounded mt-2">
              + Ajouter un article
            </button>
          </div>
          <div v-if="formErrors.length" class="mb-4">
            <ul class="bg-red-100 text-red-700 rounded px-4 py-2">
              <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
            </ul>
          </div>
          <div class="flex justify-end gap-2 mt-6">
            <button type="button" @click="cancelEdit" class="px-4 py-2 bg-gray-300 rounded">Annuler</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded" :disabled="formLoading">
              <span v-if="formLoading" class="loader-xs mr-2"></span>
              {{ editingCart ? 'Enregistrer' : 'Ajouter' }}
            </button>
          </div>
        </form>
        <button
            @click="cancelEdit"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl"
            title="Fermer"
        >×</button>
      </div>
    </div>
  </div>
</template>

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
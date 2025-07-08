<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const reviews = ref([])
const products = ref([])
const users = ref([])
const showAddForm = ref(false)
const editingReview = ref(null)
const loading = ref(false)
const deletingId = ref(null)
const formLoading = ref(false)
const formErrors = ref([])

const statusOptions = [
  { value: 'PENDING', label: 'En attente' },
  { value: 'VALIDATED', label: 'Validé' },
  { value: 'REJECTED', label: 'Rejeté' }
]

const form = ref({
  content: '',
  rating: 0,
  product: null,
  user: null,
  status: 'PENDING'
})

const fetchReviews = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('/api/reviews', {
      headers: { Authorization: `Bearer ${token}` }
    })
    reviews.value = res.data['hydra:member'] || []
  } finally {
    loading.value = false
  }
}

const fetchProducts = async () => {
  const token = localStorage.getItem('token')
  const res = await axios.get('/api/products', {
    headers: { Authorization: `Bearer ${token}` }
  })
  products.value = res.data['hydra:member'] || []
}

const fetchUsers = async () => {
  const token = localStorage.getItem('token')
  const res = await axios.get('/api/users', {
    headers: { Authorization: `Bearer ${token}` }
  })
  users.value = res.data['hydra:member'] || []
}

const openAddForm = () => {
  showAddForm.value = true
  editingReview.value = null
  Object.assign(form.value, {
    content: '',
    rating: 0,
    product: null,
    user: null,
    status: 'PENDING'
  })
  formErrors.value = []
}

const editReview = (review) => {
  editingReview.value = { ...review }
  showAddForm.value = true
  Object.assign(form.value, {
    content: review.content,
    rating: review.rating,
    product: review.product?.id || (typeof review.product === 'number' ? review.product : null),
    user: review.user?.id || (typeof review.user === 'number' ? review.user : null),
    status: review.status
  })
  formErrors.value = []
}

const cancelEdit = () => {
  editingReview.value = null
  showAddForm.value = false
  formErrors.value = []
}

const handleSubmit = async (e) => {
  e.preventDefault()
  formLoading.value = true
  formErrors.value = []
  const token = localStorage.getItem('token')
  const payload = {
    content: form.value.content,
    rating: form.value.rating,
    product: `/api/products/${form.value.product}`,
    user: `/api/users/${form.value.user}`,
    status: form.value.status
  }
  try {
    if (editingReview.value) {
      await axios.put(`/api/reviews/${editingReview.value.id}`, payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    } else {
      await axios.post('/api/reviews', payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    }
    showAddForm.value = false
    editingReview.value = null
    await fetchReviews()
  } catch (err) {
    if (err.response && err.response.data && err.response.data.violations) {
      formErrors.value = err.response.data.violations.map(v => v.message)
    } else if (err.response && err.response.data && err.response.data['hydra:description']) {
      formErrors.value = [err.response.data['hydra:description']]
    } else {
      formErrors.value = ['Erreur lors de la sauvegarde de l\'avis.']
    }
  } finally {
    formLoading.value = false
  }
}

const deleteReview = async (id) => {
  if (confirm('Supprimer cet avis ?')) {
    deletingId.value = id
    try {
      await axios.delete(`/api/reviews/${id}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      })
      await fetchReviews()
    } finally {
      deletingId.value = null
    }
  }
}

onMounted(() => {
  fetchReviews()
  fetchProducts()
  fetchUsers()
})

// Pour l'affichage des étoiles
const setRating = (val) => {
  form.value.rating = val
}
</script>

<template>
  <div class="p-8 min-h-screen text-black">
    <h1 class="text-3xl font-extrabold mb-6 text-black flex items-center gap-2">
      Gestion des avis (CRUD)
    </h1>
    <button
        @click="openAddForm"
        class="mb-6 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow transition"
    >
      Ajouter un avis
    </button>
    <div v-if="loading" class="flex justify-center items-center h-40">
      <span class="loader"></span>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow-md overflow-hidden text-black">
        <thead>
        <tr class="bg-blue-50 text-black">
          <th class="py-3 px-4 text-left">ID</th>
          <th class="py-3 px-4 text-left">Produit</th>
          <th class="py-3 px-4 text-left">Utilisateur</th>
          <th class="py-3 px-4 text-left">Note</th>
          <th class="py-3 px-4 text-left">Contenu</th>
          <th class="py-3 px-4 text-left">Statut</th>
          <th class="py-3 px-4 text-left">Date</th>
          <th class="py-3 px-4 text-left">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr
            v-for="review in reviews"
            :key="review.id"
            class="hover:bg-blue-50 transition"
        >
          <td class="py-2 px-4">{{ review.id }}</td>
          <td class="py-2 px-4">
            <span v-if="review.product">
              <router-link :to="`/product/${review.product.id}`" class="text-blue-600 underline hover:text-blue-800">
                #{{ review.product.id }} - {{ review.product.name }}
              </router-link>
            </span>
          </td>
          <td class="py-2 px-4">
            <span v-if="review.user">{{ review.user.name }} {{ review.user.lastname }}</span>
          </td>
          <td class="py-2 px-4">
            <span class="stars-col">
              <font-awesome-icon
                v-for="i in 5"
                :key="i"
                icon="star"
                :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
              />
            </span>
            <span class="ml-2 text-xs">{{ review.rating }}/5</span>
          </td>
          <td class="py-2 px-4">{{ review.content }}</td>
          <td class="py-2 px-4">
            <span
              :class="{
                'bg-yellow-200 text-yellow-800 px-2 py-1 rounded': review.status === 'PENDING',
                'bg-green-200 text-green-800 px-2 py-1 rounded': review.status === 'VALIDATED',
                'bg-red-200 text-red-800 px-2 py-1 rounded': review.status === 'REJECTED'
              }"
            >{{ review.status }}</span>
          </td>
          <td class="py-2 px-4">{{ review.datePublication?.slice(0, 10) }}</td>
          <td class="py-2 px-4 flex gap-2">
            <button
                @click="editReview(review)"
                class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-black rounded"
            >Modifier</button>
            <button
                @click="deleteReview(review.id)"
                :disabled="deletingId === review.id"
                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded"
            >
              <span v-if="deletingId === review.id" class="loader-xs"></span>
              <span v-else>Supprimer</span>
            </button>
          </td>
        </tr>
        <tr v-if="reviews.length === 0">
          <td colspan="8" class="text-center py-6 text-gray-400">Aucun avis trouvé.</td>
        </tr>
        </tbody>
      </table>
    </div>

    <div
        v-if="showAddForm || editingReview"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative form-modal-scroll text-black">
        <h2 class="text-2xl font-bold mb-4 text-black">
          {{ editingReview ? 'Modifier l\'avis' : 'Ajouter un avis' }}
        </h2>
        <form @submit="handleSubmit" class="text-black">
          <div class="mb-4">
            <label class="block mb-1">Produit</label>
            <select v-model="form.product" required class="w-full border rounded px-3 py-2">
              <option value="" disabled>Choisir un produit</option>
              <option v-for="prod in products" :key="prod.id" :value="prod.id">
                #{{ prod.id }} - {{ prod.name }}
              </option>
            </select>
          </div>
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
            <label class="block mb-1">Statut</label>
            <select v-model="form.status" required class="w-full border rounded px-3 py-2">
              <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Note</label>
            <div class="flex items-center gap-2 stars-col">
              <font-awesome-icon
                v-for="i in 5"
                :key="i"
                icon="star"
                :class="i <= form.rating ? 'text-yellow-400 cursor-pointer' : 'text-gray-300 cursor-pointer'"
                @click="setRating(i)"
              />
              <span class="ml-2 text-xs">{{ form.rating }}/5</span>
            </div>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Contenu</label>
            <textarea v-model="form.content" required class="w-full border rounded px-3 py-2" rows="4"></textarea>
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
              {{ editingReview ? 'Enregistrer' : 'Ajouter' }}
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
.stars-col {
  display: flex;
  align-items: center;
  gap: 2px;
  font-size: 1.3em;
}
</style>
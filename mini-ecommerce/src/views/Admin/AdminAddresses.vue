<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const addresses = ref([])
const users = ref([])
const showAddForm = ref(false)
const editingAddress = ref(null)
const loading = ref(false)
const deletingId = ref(null)
const formLoading = ref(false)
const formErrors = ref([])

const form = ref({
  street: '',
  city: '',
  postalCode: '',
  user: null,
  type: 'shipping',
  isDefault: false,
  isDefaultBilling: false
})

const fetchAddresses = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('/api/addresses', {
      headers: { Authorization: `Bearer ${token}` }
    })
    addresses.value = res.data['hydra:member'] || []
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

const openAddForm = () => {
  showAddForm.value = true
  editingAddress.value = null
  Object.assign(form.value, {
    street: '', city: '', postalCode: '', user: null, type: 'shipping', isDefault: false, isDefaultBilling: false
  })
  formErrors.value = []
}

const editAddress = (address) => {
  editingAddress.value = { ...address }
  showAddForm.value = false
  Object.assign(form.value, {
    street: address.street || '',
    city: address.city || '',
    postalCode: address.postalCode || '',
    user: address.user?.['@id'] || null,
    type: address.type || 'shipping',
    isDefault: !!address.isDefault,
    isDefaultBilling: !!address.isDefaultBilling
  })
  formErrors.value = []
}

const cancelEdit = () => {
  editingAddress.value = null
  showAddForm.value = false
  formErrors.value = []
}

const handleSubmit = async (e) => {
  e.preventDefault()
  formLoading.value = true
  formErrors.value = []
  if (!form.value.user) {
    formErrors.value = ['Veuillez sélectionner un utilisateur.']
    formLoading.value = false
    return
  }
  const token = localStorage.getItem('token')
  const payload = {
    street: form.value.street,
    city: form.value.city,
    postalCode: form.value.postalCode,
    user: form.value.user,
    type: form.value.type,
    isDefault: form.value.isDefault,
    isDefaultBilling: form.value.isDefaultBilling
  }
  try {
    let endpoint = ''
    if (editingAddress.value) {
      endpoint = `/api/addresses/${editingAddress.value.id}`
      await axios.put(endpoint, payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    } else {
      endpoint = '/api/addresses'
      await axios.post(endpoint, payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    }
    showAddForm.value = false
    editingAddress.value = null
    await fetchAddresses()
  } catch (err) {
    if (err.response && err.response.data && err.response.data.violations) {
      formErrors.value = err.response.data.violations.map(v => v.message)
    } else if (err.response && err.response.data && err.response.data['hydra:description']) {
      formErrors.value = [err.response.data['hydra:description']]
    } else {
      formErrors.value = ['Erreur lors de la sauvegarde de l\'adresse.']
    }
  } finally {
    formLoading.value = false
  }
}

const deleteAddress = async (id) => {
  if (confirm('Supprimer cette adresse ?')) {
    deletingId.value = id
    try {
      await axios.delete(`/api/addresses/${id}`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      })
      await fetchAddresses()
    } finally {
      deletingId.value = null
    }
  }
}

onMounted(() => {
  fetchAddresses()
  fetchUsers()
})
</script>

<template>
  <div class="p-8 min-h-screen">
    <h1 class="text-3xl font-extrabold mb-6 text-gray-800 flex items-center gap-2">
      Gestion des adresses (CRUD)
    </h1>
    <button
        @click="openAddForm"
        class="mb-6 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow transition"
    >
      Ajouter une adresse
    </button>
    <div v-if="loading" class="flex justify-center items-center h-40">
      <span class="loader"></span>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow-md overflow-hidden">
        <thead>
        <tr class="bg-blue-50 text-gray-700">
          <th class="py-3 px-4 text-left">ID</th>
          <th class="py-3 px-4 text-left">Rue</th>
          <th class="py-3 px-4 text-left">Ville</th>
          <th class="py-3 px-4 text-left">Code postal</th>
          <th class="py-3 px-4 text-left">Utilisateur</th>
          <th class="py-3 px-4 text-left">Type</th>
          <th class="py-3 px-4 text-left">Par défaut</th>
          <th class="py-3 px-4 text-left">Facturation par défaut</th>
          <th class="py-3 px-4 text-left">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr
            v-for="address in addresses"
            :key="address.id"
            class="hover:bg-blue-50 transition"
        >
          <td class="py-2 px-4">{{ address.id }}</td>
          <td class="py-2 px-4">{{ address.street }}</td>
          <td class="py-2 px-4">{{ address.city }}</td>
          <td class="py-2 px-4">{{ address.postalCode }}</td>
          <td class="py-2 px-4">
            <span v-if="address.user">{{ address.user.name }} {{ address.user.lastname }}</span>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4 text-black">
            <span v-if="address.type === 'shipping'">Livraison</span>
            <span v-else-if="address.type === 'billing'">Facturation</span>
            <span v-else>-</span>
          </td>
          <td class="py-2 px-4 text-black">
            <span v-if="address.type === 'shipping' && address.isDefault" class="text-green-600 font-bold">Oui</span>
            <span v-else-if="address.type === 'billing' && address.isDefaultBilling" class="text-green-600 font-bold">Oui</span>
            <span v-else class="text-gray-400">Non</span>
          </td>
          <td class="py-2 px-4">
            <span v-if="address.type === 'billing' && address.isDefaultBilling" class="text-green-600 font-bold">Oui</span>
            <span v-else class="text-gray-400">Non</span>
          </td>
          <td class="py-2 px-4 flex gap-2">
            <button
                @click="editAddress(address)"
                class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-black rounded"
            >Modifier</button>
            <button
                @click="deleteAddress(address.id)"
                :disabled="deletingId === address.id"
                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded"
            >
              <span v-if="deletingId === address.id" class="loader-xs"></span>
              <span v-else>Supprimer</span>
            </button>
          </td>
        </tr>
        <tr v-if="addresses.length === 0">
          <td colspan="9" class="text-center py-6 text-gray-400">Aucune adresse trouvée.</td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Formulaire d'ajout/modification -->
    <div
        v-if="showAddForm || editingAddress"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative form-modal-scroll">
        <h2 class="text-2xl font-bold mb-4 text-gray-700">
          {{ editingAddress ? 'Modifier l\'adresse' : 'Ajouter une adresse' }}
        </h2>
        <form @submit="handleSubmit" class="text-black">
          <div class="mb-4">
            <label class="block mb-1">Rue</label>
            <input v-model="form.street" required class="w-full border rounded px-3 py-2" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">Ville</label>
            <input v-model="form.city" required class="w-full border rounded px-3 py-2" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">Code postal</label>
            <input v-model="form.postalCode" required class="w-full border rounded px-3 py-2" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">Utilisateur</label>
            <select v-model="form.user" required class="w-full border rounded px-3 py-2">
              <option disabled value="">-- Choisir --</option>
              <option v-for="user in users" :key="user['@id']" :value="user['@id']">
                {{ user.name }} {{ user.lastname }} ({{ user.email }})
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Type</label>
            <select v-model="form.type" class="w-full border rounded px-3 py-2">
              <option value="shipping">Livraison</option>
              <option value="billing">Facturation</option>
            </select>
          </div>
          <div class="mb-4 flex gap-4">
            <label v-if="form.type === 'shipping'">
              <input type="checkbox" v-model="form.isDefault" />
              Par défaut
            </label>
            <label v-if="form.type === 'billing'">
              <input type="checkbox" v-model="form.isDefaultBilling" />
              Facturation par défaut
            </label>
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
              {{ editingAddress ? 'Enregistrer' : 'Ajouter' }}
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

:deep(table),
:deep(th),
:deep(td) {
  color: #111 !important;
}
</style>
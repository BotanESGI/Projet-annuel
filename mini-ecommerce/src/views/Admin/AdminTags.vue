<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const tags = ref([])
const showAddForm = ref(false)
const editingTag = ref(null)
const loading = ref(false)
const deletingId = ref(null)
const formLoading = ref(false)
const formErrors = ref([])

const form = ref({
  name: '',
  color: '#3b82f6'
})

const fetchTags = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('/api/tags', {
      headers: { Authorization: `Bearer ${token}` }
    })
    tags.value = res.data['hydra:member'] || []
  } finally {
    loading.value = false
  }
}

const openAddForm = () => {
  showAddForm.value = true
  editingTag.value = null
  Object.assign(form.value, { name: '', color: '#3b82f6' })
  formErrors.value = []
}

const editTag = (tag) => {
  editingTag.value = { ...tag }
  showAddForm.value = false
  Object.assign(form.value, {
    name: tag.name || '',
    color: tag.color || '#3b82f6'
  })
  formErrors.value = []
}

const cancelEdit = () => {
  editingTag.value = null
  showAddForm.value = false
  formErrors.value = []
}

const handleSubmit = async (e) => {
  e.preventDefault()
  formLoading.value = true
  formErrors.value = []
  const token = localStorage.getItem('token')
  const payload = {
    name: form.value.name,
    color: form.value.color
  }
  try {
    let endpoint = ''
    if (editingTag.value) {
      endpoint = `/api/tags/${editingTag.value.id}`
      await axios.put(endpoint, payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    } else {
      endpoint = '/api/tags'
      await axios.post(endpoint, payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    }
    showAddForm.value = false
    editingTag.value = null
    await fetchTags()
  } catch (err) {
    if (err.response && err.response.data && err.response.data.violations) {
      formErrors.value = err.response.data.violations.map(v => v.message)
    } else if (err.response && err.response.data && err.response.data['hydra:description']) {
      formErrors.value = [err.response.data['hydra:description']]
    } else {
      formErrors.value = ['Erreur lors de la sauvegarde du tag.']
    }
  } finally {
    formLoading.value = false
  }
}

const deleteTag = async (id) => {
  if (confirm('Supprimer ce tag ?')) {
    deletingId.value = id
    try {
      await axios.delete(`/api/tags/${id}`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      })
      await fetchTags()
    } finally {
      deletingId.value = null
    }
  }
}

onMounted(() => {
  fetchTags()
})
</script>

<template>
  <div class="p-8 min-h-screen text-black">
    <h1 class="text-3xl font-extrabold mb-6 text-black flex items-center gap-2">
      Gestion des tags (CRUD)
    </h1>
    <button
        @click="openAddForm"
        class="mb-6 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow transition"
    >
      Ajouter un tag
    </button>
    <div v-if="loading" class="flex justify-center items-center h-40">
      <span class="loader"></span>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow-md overflow-hidden text-black">
        <thead>
        <tr class="bg-blue-50 text-black">
          <th class="py-3 px-4 text-left">ID</th>
          <th class="py-3 px-4 text-left">Nom</th>
          <th class="py-3 px-4 text-left">Couleur</th>
          <th class="py-3 px-4 text-left">Produits associés</th>
          <th class="py-3 px-4 text-left">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr
            v-for="tag in tags"
            :key="tag.id"
            class="hover:bg-blue-50 transition"
        >
          <td class="py-2 px-4">{{ tag.id }}</td>
          <td class="py-2 px-4">{{ tag.name }}</td>
          <td class="py-2 px-4">
            <span
                class="inline-block w-6 h-6 rounded-full border"
                :style="{ backgroundColor: tag.color || '#eee' }"
                :title="tag.color"
            ></span>
            <span class="ml-2 text-xs">{{ tag.color }}</span>
          </td>
          <td class="py-2 px-4">
            <ul v-if="tag.products && tag.products.length">
              <li v-for="prod in tag.products" :key="prod.id">
                <router-link
                    :to="`/product/${prod.id}`"
                    class="text-blue-600 underline hover:text-blue-800"
                >
                  #{{ prod.id }} - {{ prod.name }}
                </router-link>
              </li>
            </ul>
            <span v-else class="text-gray-400 italic">Aucun</span>
          </td>
          <td class="py-2 px-4 flex gap-2">
            <button
                @click="editTag(tag)"
                class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-black rounded"
            >Modifier</button>
            <button
                @click="deleteTag(tag.id)"
                :disabled="deletingId === tag.id"
                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded"
            >
              <span v-if="deletingId === tag.id" class="loader-xs"></span>
              <span v-else>Supprimer</span>
            </button>
          </td>
        </tr>
        <tr v-if="tags.length === 0">
          <td colspan="5" class="text-center py-6 text-gray-400">Aucun tag trouvé.</td>
        </tr>
        </tbody>
      </table>
    </div>

    <div
        v-if="showAddForm || editingTag"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative form-modal-scroll text-black">
        <h2 class="text-2xl font-bold mb-4 text-black">
          {{ editingTag ? 'Modifier le tag' : 'Ajouter un tag' }}
        </h2>
        <form @submit="handleSubmit" class="text-black">
          <div class="mb-4">
            <label class="block mb-1">Nom</label>
            <input v-model="form.name" required class="w-full border rounded px-3 py-2" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">Couleur</label>
            <div class="flex items-center gap-2 mb-2">
              <input type="color" v-model="form.color" class="w-10 h-10 p-0 border rounded" />
              <span>{{ form.color }}</span>
            </div>
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
              {{ editingTag ? 'Enregistrer' : 'Ajouter' }}
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
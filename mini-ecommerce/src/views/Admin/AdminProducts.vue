<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const products = ref([])
const showAddForm = ref(false)
const editingProduct = ref(null)
const loading = ref(false)
const deletingId = ref(null)
const categories = ref([])
const tags = ref([])

const form = ref({
  name: '',
  price: '',
  description: '',
  image: '',
  imageFile: null,
  type: 'PhysicalProduct',
  defaultCategory: null,
  categories: [],
  tags: [],
  downloadLink: '',
  filesize: '',
  filetype: '',
  characteristics: {}
})

const newCharacteristic = ref({ key: '', value: '' })

const formLoading = ref(false)
const formErrors = ref([])

const fetchProducts = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/products')
    products.value = res.data['hydra:member'] || []
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  const res = await axios.get('/api/categories')
  categories.value = res.data['hydra:member'] || []
}
const fetchTags = async () => {
  const res = await axios.get('/api/tags')
  tags.value = res.data['hydra:member'] || []
}

const openAddForm = () => {
  showAddForm.value = true
  editingProduct.value = null
  Object.assign(form.value, {
    name: '', price: '', description: '', image: '', imageFile: null, type: 'PhysicalProduct',
    defaultCategory: null, categories: [], tags: [],
    downloadLink: '', filesize: '', filetype: '', characteristics: {}
  })
  newCharacteristic.value = { key: '', value: '' }
  formErrors.value = []
}

const editProduct = (product) => {
  editingProduct.value = { ...product }
  showAddForm.value = false
  Object.assign(form.value, {
    name: product.name || '',
    price: product.price || '',
    description: product.description || '',
    image: product.image ? withBaseUrl(product.image) : '',
    imageFile: null,
    type: product['@type'] || 'PhysicalProduct',
    defaultCategory: product.defaultCategory?.['@id'] || null,
    categories: (product.categories || []).map(cat => cat['@id']),
    tags: (product.tags || []).map(tag => tag['@id']),
    downloadLink: product.downloadLink || '',
    filesize: product.filesize || '',
    filetype: product.filetype || '',
    characteristics: product.characteristics ? { ...product.characteristics } : {}
  })
  newCharacteristic.value = { key: '', value: '' }
  formErrors.value = []
}

const cancelEdit = () => {
  editingProduct.value = null
  showAddForm.value = false
  formErrors.value = []
}

const onImageChange = async (e) => {
  const file = e.target.files[0]
  if (!file) return
  form.value.imageFile = file
  form.value.image = URL.createObjectURL(file)
}

const uploadImage = async () => {
  if (!form.value.imageFile) return form.value.image
  const formData = new FormData()
  formData.append('file', form.value.imageFile)
  const token = localStorage.getItem('token')
  const res = await axios.post('/api/upload', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
      Authorization: `Bearer ${token}`
    }
  })
  const url = res.data.url
  return url.startsWith('/images/') ? url.replace('/images/', '') : url
}
const addCharacteristic = () => {
  if (newCharacteristic.value.key && newCharacteristic.value.value) {
    form.value.characteristics[newCharacteristic.value.key] = newCharacteristic.value.value
    newCharacteristic.value = { key: '', value: '' }
  }
}
const removeCharacteristic = (key) => {
  delete form.value.characteristics[key]
}

const handleSubmit = async (e) => {
  e.preventDefault()
  formLoading.value = true
  formErrors.value = []
  if (!form.value.defaultCategory) {
    formErrors.value = ['Veuillez sélectionner une catégorie par défaut.']
    formLoading.value = false
    return
  }
  const token = localStorage.getItem('token')
  let imageUrl = form.value.image
  if (form.value.imageFile) {
    imageUrl = await uploadImage()
  }
  let payload = {
    name: form.value.name,
    price: parseFloat(form.value.price),
    description: form.value.description,
    image: imageUrl,
    defaultCategory: form.value.defaultCategory,
    categories: form.value.categories,
    tags: form.value.tags,
    product_type: form.value.type === 'DigitalProduct' ? 'digital' : 'physical'
  }
  if (form.value.type === 'DigitalProduct') {
    payload = {
      ...payload,
      downloadLink: form.value.downloadLink,
      filesize: form.value.filesize ? parseInt(form.value.filesize) : null,
      filetype: form.value.filetype
    }
  } else {
    payload = {
      ...payload,
      characteristics: form.value.characteristics
    }
  }
  try {
    let endpoint = ''
    if (editingProduct.value) {
      endpoint =
          form.value.type === 'DigitalProduct'
              ? `/api/digital_products/${editingProduct.value.id}`
              : `/api/physical_products/${editingProduct.value.id}`
      await axios.put(endpoint, payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    } else {
      endpoint =
          form.value.type === 'DigitalProduct'
              ? '/api/digital_products'
              : '/api/physical_products'
      await axios.post(endpoint, payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    }
    showAddForm.value = false
    editingProduct.value = null
    await fetchProducts()
  } catch (err) {
    if (err.response && err.response.data && err.response.data.violations) {
      formErrors.value = err.response.data.violations.map(v => v.message)
    } else if (err.response && err.response.data && err.response.data['hydra:description']) {
      formErrors.value = [err.response.data['hydra:description']]
    } else {
      formErrors.value = ['Erreur lors de la sauvegarde du produit.']
    }
  } finally {
    formLoading.value = false
  }
}

const withBaseUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `${window.location.protocol}//${window.location.hostname}/images/${path.replace(/^\/+|images\//, '')}`
}

const deleteProduct = async (id) => {
  if (confirm('Supprimer ce produit ?')) {
    deletingId.value = id
    try {
      await axios.delete(`/api/products/${id}`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      })
      window.location.reload()
    } finally {
      deletingId.value = null
    }
  }
}

onMounted(() => {
  fetchProducts()
  fetchCategories()
  fetchTags()
})
</script>

<template>
  <div class="p-8 min-h-screen">
    <h1 class="text-3xl font-extrabold mb-6 text-gray-800 flex items-center gap-2">
      Gestion des produits (CRUD)
    </h1>
    <button
        @click="openAddForm"
        class="mb-6 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow transition"
    >
      Ajouter un produit
    </button>
    <div v-if="loading" class="flex justify-center items-center h-40">
      <span class="loader"></span>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow-md overflow-hidden">
        <thead>
        <tr class="bg-blue-50 text-gray-700">
          <th class="py-3 px-4 text-left text-black min-w-[80px]">ID</th>
          <th class="py-3 px-4 text-left text-black min-w-[120px]">Image</th>
          <th class="py-3 px-4 text-left text-black min-w-[180px]">Nom</th>
          <th class="py-3 px-4 text-left text-black min-w-[250px]">Description</th>
          <th class="py-3 px-4 text-left text-black min-w-[100px]">Prix</th>
          <th class="py-3 px-4 text-left text-black min-w-[120px]">Type</th>
          <th class="py-3 px-4 text-left text-black min-w-[180px]">Catégories</th>
          <th class="py-3 px-4 text-left text-black min-w-[180px]">Catégorie par défaut</th>
          <th class="py-3 px-4 text-left text-black min-w-[180px]">Tags</th>
          <th class="py-3 px-4 text-left text-black min-w-[180px]">Lien téléchargement</th>
          <th class="py-3 px-4 text-left text-black min-w-[120px]">Taille fichier</th>
          <th class="py-3 px-4 text-left text-black min-w-[120px]">Type fichier</th>
          <th class="py-3 px-4 text-left text-black min-w-[180px]">Caractéristiques</th>
          <th class="py-3 px-4 text-left text-black min-w-[160px]">Créé le</th>
          <th class="py-3 px-4 text-left text-black min-w-[120px]">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr
            v-for="product in products"
            :key="product.id"
            class="hover:bg-blue-50 transition"
        >
          <td class="py-2 px-4 text-black min-w-[80px]">{{ product.id }}</td>
          <td class="py-2 px-4 text-black min-w-[120px]">
            <img v-if="product.image" :src="withBaseUrl(product.image)" class="w-12 h-12 object-cover rounded" />
            <span v-else class="text-gray-400 italic">Aucune</span>
          </td>
          <td class="py-2 px-4 font-medium text-black min-w-[180px]">{{ product.name }}</td>
          <td class="py-2 px-4 text-black max-w-xs truncate min-w-[250px]" :title="product.description">{{ product.description }}</td>
          <td class="py-2 px-4 text-black min-w-[100px]">{{ product.price }} €</td>
          <td class="py-2 px-4 text-black min-w-[120px]">
            <span v-if="product['@type'] === 'DigitalProduct'" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Digital</span>
            <span v-else-if="product['@type'] === 'PhysicalProduct'" class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Physique</span>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4 text-black min-w-[180px]">
            <template v-if="product.categories && product.categories.length">
              <span v-for="cat in product.categories" :key="cat.id" class="inline-block mr-1 px-2 py-1 rounded" :style="{ backgroundColor: cat.color || '#eee' }">
                {{ cat.name }}
              </span>
            </template>
            <span v-else class="text-gray-400 italic">Aucune</span>
          </td>
          <td class="py-2 px-4 text-black min-w-[180px]">
            <span v-if="product.defaultCategory" class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">
              {{ product.defaultCategory.name }}
            </span>
            <span v-else class="text-gray-400 italic">Aucune</span>
          </td>
          <td class="py-2 px-4 text-black min-w-[180px]">
            <template v-if="product.tags && product.tags.length">
              <span v-for="tag in product.tags" :key="tag.id" class="inline-block mr-1 px-2 py-1 rounded" :style="{ backgroundColor: tag.color || '#eee', color: '#111' }">
                {{ tag.name }}
              </span>
            </template>
            <span v-else class="text-gray-400 italic">Aucun</span>
          </td>
          <td class="py-2 px-4 text-black min-w-[180px]">
            <a v-if="product['@type'] === 'DigitalProduct' && product.downloadLink" :href="product.downloadLink" target="_blank" class="text-blue-600 underline">
              {{ product.downloadLink }}
            </a>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4 text-black min-w-[120px]">
            <span v-if="product['@type'] === 'DigitalProduct' && product.filesize">{{ product.filesize }} Ko</span>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4 text-black min-w-[120px]">
            <span v-if="product['@type'] === 'DigitalProduct' && product.filetype">{{ product.filetype }}</span>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4 text-black min-w-[180px]">
            <ul v-if="product['@type'] === 'PhysicalProduct' && product.characteristics && Object.keys(product.characteristics).length" class="list-disc pl-4">
              <li v-for="(val, key) in product.characteristics" :key="key">{{ key }} : {{ val }}</li>
            </ul>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4 text-black min-w-[160px]">
            <span v-if="product.createdAt">{{ new Date(product.createdAt).toLocaleString() }}</span>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4 flex gap-2 text-black min-w-[120px]">
            <router-link
                :to="`/product/${product.id}`"
                class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded"
                title="Afficher le produit"
                target="_blank"
            >
              Afficher
            </router-link>
            <button
                @click="editProduct(product)"
                class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-black rounded"
            >Modifier</button>
            <button
                @click="deleteProduct(product.id)"
                :disabled="deletingId === product.id"
                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded"
            >
              <span v-if="deletingId === product.id" class="loader-xs"></span>
              <span v-else>Supprimer</span>
            </button>
          </td>
        </tr>
        <tr v-if="products.length === 0">
          <td colspan="15" class="text-center py-6 text-gray-400">Aucun produit trouvé.</td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Formulaire d'ajout/modification -->
    <div
        v-if="showAddForm || editingProduct"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative form-modal-scroll">
        <h2 class="text-2xl font-bold mb-4 text-gray-700">
          {{ editingProduct ? 'Modifier le produit' : 'Ajouter un produit' }}
        </h2>
        <form @submit="handleSubmit" class="text-black">
          <div class="mb-4">
            <label class="block mb-1">Nom</label>
            <input v-model="form.name" required class="w-full border rounded px-3 py-2" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">Prix (€)</label>
            <input v-model="form.price" type="number" min="0" step="0.01" required class="w-full border rounded px-3 py-2" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">Description</label>
            <textarea v-model="form.description" required class="w-full border rounded px-3 py-2"></textarea>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Image</label>
            <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" @change="onImageChange" class="mb-2" />
            <div class="text-xs text-gray-500 mb-2">
              Formats acceptés : JPG, PNG, GIF, WEBP
            </div>
            <div v-if="form.image" class="mb-2">
              <img :src="form.image" alt="preview" class="w-20 h-20 object-cover rounded" />
            </div>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Type</label>
            <select v-model="form.type" class="w-full border rounded px-3 py-2">
              <option value="PhysicalProduct">Physique</option>
              <option value="DigitalProduct">Digital</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Catégories</label>
            <select v-model="form.categories" multiple class="w-full border rounded px-3 py-2">
              <option v-for="cat in categories" :key="cat['@id']" :value="cat['@id']">{{ cat.name }}</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Catégorie par défaut</label>
            <select v-model="form.defaultCategory" required class="w-full border rounded px-3 py-2">
              <option disabled value="">-- Choisir --</option>
              <option v-for="cat in categories" :key="cat['@id']" :value="cat['@id']">{{ cat.name }}</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Tags</label>
            <select v-model="form.tags" multiple class="w-full border rounded px-3 py-2">
              <option v-for="tag in tags" :key="tag['@id']" :value="tag['@id']">{{ tag.name }}</option>
            </select>
          </div>
          <div v-if="form.type === 'DigitalProduct'">
            <div class="mb-4">
              <label class="block mb-1">Lien de téléchargement</label>
              <input v-model="form.downloadLink" class="w-full border rounded px-3 py-2" />
            </div>
            <div class="mb-4">
              <label class="block mb-1">Taille du fichier (Ko)</label>
              <input v-model="form.filesize" type="number" min="0" class="w-full border rounded px-3 py-2" />
            </div>
            <div class="mb-4">
              <label class="block mb-1">Type de fichier</label>
              <input v-model="form.filetype" class="w-full border rounded px-3 py-2" />
            </div>
          </div>
          <div v-if="form.type === 'PhysicalProduct'">
            <label class="block mb-1">Caractéristiques</label>
            <div v-for="(val, key) in form.characteristics" :key="key" class="flex gap-2 mb-2">
              <input v-model="form.characteristics[key]" placeholder="Valeur" class="input border rounded px-2 py-1 flex-1" />
              <button type="button" @click="removeCharacteristic(key)" class="text-red-500">Supprimer</button>
            </div>
            <div class="flex gap-2 mb-2">
              <input v-model="newCharacteristic.key" placeholder="Nom" class="input border rounded px-2 py-1 flex-1" />
              <input v-model="newCharacteristic.value" placeholder="Valeur" class="input border rounded px-2 py-1 flex-1" />
              <button type="button" @click="addCharacteristic" class="bg-blue-500 text-white px-2 py-1 rounded">Ajouter</button>
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
              {{ editingProduct ? 'Enregistrer' : 'Ajouter' }}
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
:deep(.text-black),
:deep(form),
:deep(table),
:deep(th),
:deep(td),
:deep(label),
:deep(input),
:deep(select),
:deep(textarea),
:deep(button) {
  color: #111 !important;
}

:deep(button) {
  color: #fff !important;
}

.form-modal-scroll {
  max-height: 80vh;
  overflow-y: auto;
}
</style>
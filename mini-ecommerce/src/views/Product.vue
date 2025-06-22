<template>
  <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8 bg-white p-8 rounded-lg shadow">
      <div>
        <h1 class="text-center text-3xl font-extrabold text-gray-900 mb-4">
          Nos produits
          <span v-if="selectedCategory">dans la catégorie : {{ selectedCategory.name }}</span>
        </h1>
      </div>

      <div v-if="loading" class="flex flex-col items-center justify-center py-12">
        <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-900 text-lg">Chargement des produits...</p>
      </div>

      <div v-if="error" class="text-center text-red-600 bg-red-50 p-4 rounded-lg">{{ error }}</div>

      <div v-if="!loading && !error">
        <div class="mb-6">
          <p class="text-gray-700 mb-1 text-lg">Explorez nos catégories :</p>
          <div class="flex flex-wrap gap-3">
            <button
                @click="selectCategory(null)"
                class="px-5 py-3 rounded-full transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md"
                :class="selectedCategoryId === null ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'"
            >
              Tous les produits
            </button>

            <button
                v-for="category in categories"
                :key="category.id"
                @click="selectCategory(category)"
                class="px-5 py-3 rounded-full transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md flex items-center space-x-2"
                :class="selectedCategoryId === category.id ? 'text-white' : 'text-gray-800 bg-white'"
                :style="selectedCategoryId === category.id ? { backgroundColor: category.color } : { borderLeft: `4px solid ${category.color}` }"
            >
              <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: category.color }"></span>
              <span>{{ category.name }}</span>
            </button>
          </div>
        </div>

        <div class="mb-8">
          <p class="text-gray-700 mb-1 text-lg">Filtres de recherche :</p>
          <div class="bg-gray-50 rounded-xl shadow-sm p-6">
            <div class="flex flex-wrap items-center gap-4">
              <div class="relative flex-grow min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                  </svg>
                </div>
                <input v-model="filters.search" type="text" placeholder="Rechercher un produit"
                       class="pl-10 w-full text-gray-900 border border-gray-300 rounded-lg px-4 py-3 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-900">
              </div>

              <div class="w-[150px]">
                <select v-model="filters.type" class="w-full text-gray-900 border border-gray-300 rounded-lg px-4 py-3 focus:ring-blue-500 focus:border-blue-500 bg-white">
                  <option value="">Tous les types</option>
                  <option value="digital">Digital</option>
                  <option value="physical">Physique</option>
                </select>
              </div>

              <div class="flex space-x-2 w-[200px]">
                <div class="relative flex-1">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500">€</span>
                  </div>
                  <input v-model="filters.minPrice" type="number" placeholder="Min"
                         class="pl-8 w-full text-gray-900 border border-gray-300 rounded-lg px-4 py-3 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-900">
                </div>
                <div class="relative flex-1">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500">€</span>
                  </div>
                  <input v-model="filters.maxPrice" type="number" placeholder="Max"
                         class="pl-8 w-full text-gray-900 border border-gray-300 rounded-lg px-4 py-3 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-900">
                </div>
              </div>

              <div class="w-[150px]">
                <select v-model="filters.priceSort" class="w-full text-gray-900 border border-gray-300 rounded-lg px-4 py-3 focus:ring-blue-500 focus:border-blue-500 bg-white">
                  <option value="">Tri par prix</option>
                  <option value="asc">Prix croissant</option>
                  <option value="desc">Prix décroissant</option>
                </select>
              </div>

              <div class="w-[150px]">
                <select v-model="filters.dateSort" class="w-full text-gray-900 border border-gray-300 rounded-lg px-4 py-3 focus:ring-blue-500 focus:border-blue-500 bg-white">
                  <option value="">Tri par date</option>
                  <option value="asc">Plus anciens</option>
                  <option value="desc">Plus récents</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div v-if="filteredProducts.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="(product, index) in filteredProducts" :key="product.id"
               class="relative bg-white shadow rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
            <a :href="`/product/${product.id}?category=${selectedCategoryId || (product.defaultCategory ? product.defaultCategory.id : '')}`"
               :title="`Cliquer ici pour en savoir plus sur l'article ${product.name}`">
              <img loading="lazy" :src="product.image" :alt="`Image de ${product.name}`" class="w-full h-48 object-cover">
              <div class="p-4">
                <h2 class="text-xl font-semibold text-gray-900">#{{ index + 1 }} - {{ product.name }}</h2>
                <p class="text-gray-900 mt-2">{{ product.description }}</p>
                <p class="text-gray-900 font-bold mt-2">Prix: {{ formatPrice(product.price) }} €</p>
              </div>
            </a>
          </div>
        </div>

        <div v-else-if="!loading && !filteredProducts.length" class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <p class="text-gray-900 text-lg">Aucun produit disponible pour le moment ou correspondant à vos critères de recherche.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'

const route = useRoute()

const products = ref([])
const categories = ref([])
const loading = ref(true)
const error = ref(null)
const selectedCategoryId = ref(null)
const selectedCategory = ref(null)

const filters = ref({
  search: '',
  type: '',
  minPrice: '',
  maxPrice: '',
  priceSort: '',
  dateSort: '',
  category: null
})

const API_URL = '/api/products'
const CATEGORIES_URL = '/api/categories'

onMounted(async () => {

  const categoryId = route.query.category
  if (categoryId) {
    selectedCategoryId.value = parseInt(categoryId)
    filters.value.category = selectedCategoryId.value
  }
  await fetchCategories()

  await fetchProducts()

  if (selectedCategoryId.value) {
    selectedCategory.value = categories.value.find(c => c.id === selectedCategoryId.value)
  }
})

const fetchCategories = async () => {
  try {
    const response = await axios.get(CATEGORIES_URL)
    if (response.data && response.data['hydra:member']) {
      categories.value = response.data['hydra:member']
    } else {
      categories.value = response.data
    }
  } catch (err) {
    error.value = 'Erreur lors du chargement des catégories.'
    console.error('Erreur API catégories:', err)
  }
}

const fetchProducts = async () => {
  try {
    loading.value = true
    let url = API_URL + '?'

    if (filters.value.search) url += `search=${encodeURIComponent(filters.value.search)}&`
    if (filters.value.type) url += `type=${filters.value.type}&`
    if (filters.value.minPrice) url += `min_price=${filters.value.minPrice}&`
    if (filters.value.maxPrice) url += `max_price=${filters.value.maxPrice}&`
    if (filters.value.priceSort) url += `price_sort=${filters.value.priceSort}&`
    if (filters.value.dateSort) url += `date_sort=${filters.value.dateSort}&`
    if (filters.value.category) url += `category=${filters.value.category}&`

    const response = await axios.get(url)

    if (response.data && response.data['hydra:member']) {
      products.value = response.data['hydra:member']
    } else {
      products.value = response.data
    }
  } catch (err) {
    error.value = 'Erreur lors du chargement des produits.'
    console.error('Erreur API:', err)
  } finally {
    loading.value = false
  }
}

const selectCategory = (category) => {
  selectedCategory.value = category
  selectedCategoryId.value = category ? category.id : null
  filters.value.category = selectedCategoryId.value
  applyFilters()
}

const applyFilters = async () => {
  try {
    loading.value = true
    let url = API_URL + '?'

    if (filters.value.search) url += `search=${encodeURIComponent(filters.value.search)}&`
    if (filters.value.type) url += `type=${filters.value.type}&`
    if (filters.value.minPrice) url += `min_price=${filters.value.minPrice}&`
    if (filters.value.maxPrice) url += `max_price=${filters.value.maxPrice}&`
    if (filters.value.priceSort) url += `price_sort=${filters.value.priceSort}&`
    if (filters.value.dateSort) url += `date_sort=${filters.value.dateSort}&`
    if (filters.value.category) url += `category=${filters.value.category}&`

    const response = await axios.get(url)

    if (response.data && response.data['hydra:member']) {
      products.value = response.data['hydra:member']
    } else {
      products.value = response.data
    }
  } catch (err) {
    error.value = 'Erreur lors du filtrage des produits.'
    console.error('Erreur API:', err)
  } finally {
    loading.value = false
  }
}

const filteredProducts = computed(() => {
  let result = [...products.value]

  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase()
    result = result.filter(product =>
        product.name.toLowerCase().includes(searchLower) ||
        product.description.toLowerCase().includes(searchLower)
    )
  }

  if (filters.value.type) {
    result = result.filter(product => {
      if (filters.value.type === 'digital') return product['@type'] === 'DigitalProduct'
      if (filters.value.type === 'physical') return product['@type'] === 'PhysicalProduct'
      return true
    })
  }

  if (filters.value.category) {
    result = result.filter(product => {
      if (product.defaultCategory && product.defaultCategory.id === filters.value.category) {
        return true;
      }
      return product.categories && product.categories.some(category => category.id === filters.value.category);
    });
  }

  if (filters.value.minPrice) {
    result = result.filter(product => product.price >= parseFloat(filters.value.minPrice))
  }

  if (filters.value.maxPrice) {
    result = result.filter(product => product.price <= parseFloat(filters.value.maxPrice))
  }

  if (filters.value.priceSort) {
    result.sort((a, b) => {
      if (filters.value.priceSort === 'asc') return a.price - b.price
      return b.price - a.price
    })
  }

  if (filters.value.dateSort) {
    result.sort((a, b) => {

      if (!a.createdAt && !b.createdAt) return 0;
      if (!a.createdAt) return filters.value.dateSort === 'asc' ? 1 : -1;
      if (!b.createdAt) return filters.value.dateSort === 'asc' ? -1 : 1;

      const timestampA = new Date(a.createdAt).getTime();
      const timestampB = new Date(b.createdAt).getTime();

      return filters.value.dateSort === 'asc'
          ? timestampA - timestampB
          : timestampB - timestampA;
    });
  }

  return result
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(price)
}

watch(() => route.query, (newQuery) => {
  if (newQuery.category && newQuery.category !== filters.value.category) {
    const categoryId = parseInt(newQuery.category)
    selectedCategoryId.value = categoryId
    filters.value.category = categoryId
    selectedCategory.value = categories.value.find(c => c.id === categoryId)
    applyFilters()
  }
}, { deep: true })
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

::placeholder {
  color: #111827 !important;
  opacity: 1;
}

:-ms-input-placeholder {
  color: #111827 !important;
}

::-ms-input-placeholder {
  color: #111827 !important;
}
</style>
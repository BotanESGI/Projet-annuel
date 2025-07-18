<template>
  <div class="max-w-7xl mx-auto p-4">
    <div class="bg-white text-center text-gray-800 p-8 rounded-lg mb-8 shadow-md">
      <h1 class="text-4xl font-bold mb-2">Bienvenue</h1>
      <p class="text-xl mt-2">
        <span v-if="userLastName">Bonjour, <span class="font-bold text-gray-600">{{ userName }} {{ userLastName }}</span> !</span>
        <span v-else>Créer un compte et rejoignez-nous !</span>
      </p>
    </div>

    <div>
      <ul class="flex border-b mb-4 overflow-x-auto">
        <li v-for="tab in tabs" :key="tab.key"
            :class="['cursor-pointer py-2 px-4 transition-colors border-b-2', activeTab === tab.key ? 'font-bold text-blue-500 border-blue-500' : 'text-gray-600 border-transparent']"
            @click="activeTab = tab.key">
          {{ tab.label }}
        </li>
      </ul>
      <div v-if="loading" class="flex justify-center items-center py-10">
        <div class="flex flex-col items-center">
          <div class="loader border-4 border-blue-500 border-t-transparent rounded-full w-10 h-10 animate-spin mb-2"></div>
          <span class="text-blue-500 font-medium">Chargement...</span>
        </div>
      </div>
      <div v-else>
        <div v-if="activeTab === 'recentlyViewed'">
          <h3 class="text-black text-xl mb-2 font-semibold">Produits récemment consultés</h3>
          <div v-if="filterProducts(data.recentlyViewedProducts).length" class="flex gap-5 overflow-x-auto py-2">
            <div v-for="p in filterProducts(data.recentlyViewedProducts)" :key="p.id"
                 class="flex-none w-60 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-md transition-transform transform hover:scale-105 focus:ring-2 focus:ring-blue-400 outline-none">
              <a :href="'/product/' + p.id + '?category=' + (p.categoryId || (p.defaultCategory?.id ?? ''))"
                 :title="'Cliquer ici pour en savoir plus sur l\'article ' + p.name"
                 tabindex="0">
                <img :src="p.image || '/images/blank.jpg'" :alt="p.name" class="w-full h-40 object-cover bg-gray-100" />
                <div class="p-3">
                  <h3 class="text-gray-900 text-lg font-medium truncate mb-1">{{ p.name }}</h3>
                  <p class="text-sm text-gray-500 truncate mb-1">{{ p.description }}</p>
                  <strong class="text-gray-900 font-bold">Prix : {{ p.price }} €</strong>
                </div>
              </a>
            </div>
          </div>
          <p v-else class="text-gray-500 italic">Aucun produit récemment consulté.</p>
        </div>
        <div v-if="activeTab === 'bestRated'">
          <h3 class="text-black text-xl mb-2 font-semibold">Nos produits les mieux notés</h3>
          <div v-if="filterProducts(data.bestRatedProducts).length" class="flex gap-5 overflow-x-auto py-2">
            <div v-for="p in filterProducts(data.bestRatedProducts)" :key="p.id"
                 class="flex-none w-60 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-md transition-transform transform hover:scale-105 focus:ring-2 focus:ring-blue-400 outline-none">
              <a :href="'/product/' + p.id + '?category=' + (p.categoryId || (p.defaultCategory?.id ?? ''))"
                 :title="'Cliquer ici pour en savoir plus sur l\'article ' + p.name"
                 tabindex="0">
                <img :src="p.image || '/images/blank.jpg'" :alt="p.name" class="w-full h-40 object-cover bg-gray-100" />
                <div class="p-3">
                  <h3 class="text-gray-900 text-lg font-medium truncate mb-1">{{ p.name }}</h3>
                  <p class="text-sm text-gray-500 truncate mb-1">{{ p.description }}</p>
                  <strong class="text-gray-900 font-bold">Prix : {{ p.price }} €</strong>
                  <span class="block text-yellow-500 font-semibold">Note moyenne : {{ Number(p.avgRating).toFixed(1) }}</span>
                </div>
              </a>
            </div>
          </div>
          <p v-else class="text-gray-500 italic">Aucun produit trouvé.</p>
        </div>
        <div v-if="activeTab === 'cheapest'">
          <h3 class="text-black text-xl mb-2 font-semibold">Nos produits les moins chers</h3>
          <div v-if="filterProducts(data.cheapestProduct).length" class="flex gap-5 overflow-x-auto py-2">
            <div v-for="p in filterProducts(data.cheapestProduct)" :key="p.id"
                 class="flex-none w-60 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-md transition-transform transform hover:scale-105 focus:ring-2 focus:ring-blue-400 outline-none">
              <a :href="'/product/' + p.id + '?category=' + (p.categoryId || (p.defaultCategory?.id ?? ''))"
                 :title="'Cliquer ici pour en savoir plus sur l\'article ' + p.name"
                 tabindex="0">
                <img :src="p.image || '/images/blank.jpg'" :alt="p.name" class="w-full h-40 object-cover bg-gray-100" />
                <div class="p-3">
                  <h3 class="text-gray-900 text-lg font-medium truncate mb-1">{{ p.name }}</h3>
                  <p class="text-sm text-gray-500 truncate mb-1">{{ p.description }}</p>
                  <strong class="block text-blue-700">{{ p.price }} €</strong>
                </div>
              </a>
            </div>
          </div>
          <p v-else class="text-gray-500 italic">Aucun produit pas cher actuellement.</p>
        </div>
        <div v-if="activeTab === 'mostSold'">
          <h3 class="text-black text-xl mb-2 font-semibold">Produits les plus vendus</h3>
          <div v-if="filterProducts(data.mostSoldProducts).length" class="flex gap-5 overflow-x-auto py-2">
            <div v-for="p in filterProducts(data.mostSoldProducts)" :key="p.id"
                 class="flex-none w-60 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-md transition-transform transform hover:scale-105 focus:ring-2 focus:ring-blue-400 outline-none">
              <a :href="'/product/' + p.id + '?category=' + (p.categoryId || (p.defaultCategory?.id ?? ''))"
                 :title="'Cliquer ici pour en savoir plus sur l\'article ' + p.name"
                 tabindex="0">
                <img :src="p.image || '/images/blank.jpg'" :alt="p.name" class="w-full h-40 object-cover bg-gray-100" />
                <div class="p-3">
                  <h3 class="text-gray-900 text-lg font-medium truncate mb-1">{{ p.name }}</h3>
                  <p class="text-sm text-gray-500 truncate mb-1">{{ p.description }}</p>
                  <strong class="text-gray-900 font-bold">Prix : {{ p.price }} €</strong>
                  <span class="block text-green-600 font-semibold">Quantité vendue : {{ p.sales_count }}</span>
                </div>
              </a>
            </div>
          </div>
          <p v-else class="text-gray-500 italic">Aucun produit vendu jusqu'à présent.</p>
        </div>
        <div v-if="activeTab === 'latestProducts'">
          <h3 class="text-black text-xl mb-2 font-semibold">Derniers Ajouts</h3>
          <div v-if="filterProducts(data.latestProducts).length" class="flex gap-5 overflow-x-auto py-2">
            <div v-for="p in filterProducts(data.latestProducts)" :key="p.id"
                 class="flex-none w-60 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-md transition-transform transform hover:scale-105 focus:ring-2 focus:ring-blue-400 outline-none">
              <a :href="'/product/' + p.id + '?category=' + (p.categoryId || (p.defaultCategory?.id ?? ''))"
                 :title="'Cliquer ici pour en savoir plus sur l\'article ' + p.name"
                 tabindex="0">
                <img :src="p.image || '/images/blank.jpg'" :alt="p.name" class="w-full h-40 object-cover bg-gray-100" />
                <div class="p-3">
                  <h3 class="text-gray-900 text-lg font-medium truncate mb-1">{{ p.name }}</h3>
                  <p class="text-sm text-gray-500 truncate mb-1">{{ p.description }}</p>
                  <strong class="text-gray-900 font-bold">Prix : {{ p.price }} €</strong>
                </div>
              </a>
            </div>
          </div>
          <p v-else class="text-gray-500 italic">Aucun produit récemment ajouté.</p>
        </div>
      </div>
    </div>

    <div v-if="data.tags && data.tags.length" class="mt-8">
      <h3 class="text-black font-semibold mb-2">Tags</h3>
      <div class="flex gap-2 flex-wrap mb-4">
        <button
            v-for="tag in data.tags"
            :key="tag.id"
            class="px-4 py-1 rounded-full text-white text-sm font-medium"
            :style="{ backgroundColor: tag.color, opacity: selectedTag === tag.id ? 1 : 0.7 }"
            @click="fetchTagProducts(tag.id)"
        >
          {{ tag.name }}
        </button>
      </div>
      <div v-if="selectedTag">
        <div v-if="loadingTagProducts" class="flex justify-center items-center py-4">
          <div
              class="loader border-4 border-t-transparent rounded-full w-8 h-8 animate-spin mr-2"
              :style="{ borderColor: selectedTagColor, borderTopColor: 'transparent', borderLeftColor: selectedTagColor }"
          ></div>
          <span class="font-medium" :style="{ color: selectedTagColor }">Chargement...</span>
        </div>
        <div v-else>
          <div v-if="tagProducts.length" class="flex gap-5 overflow-x-auto py-2">
            <div v-for="p in tagProducts" :key="p.id"
                 class="flex-none w-60 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-md">
              <a :href="'/product/' + p.id"
                 :title="'Cliquer ici pour en savoir plus sur l\'article ' + p.name"
                 tabindex="0">
                <img :src="p.image || '/images/blank.jpg'" :alt="p.name" class="w-full h-40 object-cover bg-gray-100" />
                <div class="p-3">
                  <h3 class="text-gray-900 text-lg font-medium truncate mb-1">{{ p.name }}</h3>
                  <p class="text-sm text-gray-500 truncate mb-1">{{ p.description }}</p>
                  <strong class="text-gray-900 font-bold">Prix : {{ p.price }} €</strong>
                </div>
              </a>
            </div>
          </div>
          <p v-else class="text-gray-500 italic">Aucun produit pour ce tag.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'

const tabs = [
  { key: 'recentlyViewed', label: 'Récemment Consultés' },
  { key: 'bestRated', label: 'Les Mieux Notés' },
  { key: 'cheapest', label: 'Les Moins Chers' },
  { key: 'mostSold', label: 'Les Plus Vendus' },
  { key: 'latestProducts', label: 'Les Derniers Ajouts' }
]

const activeTab = ref('recentlyViewed')
const loading = ref(true)
const data = ref({
  bestRatedProducts: [],
  cheapestProduct: [],
  recentlyViewedProducts: [],
  mostSoldProducts: [],
  latestProducts: [],
  tags: []
})
const userName = ref('')
const userLastName = ref('')
const selectedTag = ref(null)
const tagProducts = ref([])
const loadingTagProducts = ref(false)

const selectedTagColor = computed(() => {
  const tag = data.value.tags.find(t => t.id === selectedTag.value)
  return tag ? tag.color : '#3b82f6'
})

async function fetchTagProducts(tagId) {
  selectedTag.value = tagId
  loadingTagProducts.value = true
  tagProducts.value = []
  try {
    const res = await fetch(`/api/products?tags.id=${tagId}`)
    const result = await res.json()
    tagProducts.value = result['hydra:member'] || []
  } catch (e) {
    tagProducts.value = []
  } finally {
    loadingTagProducts.value = false
  }
}

function isNotEmpty(obj) {
  return obj && typeof obj === 'object' && Object.keys(obj).length > 0 && obj.id
}

function filterProducts(products) {
  if (!Array.isArray(products)) return []
  return products.filter(isNotEmpty)
}

onMounted(async () => {
  userName.value = localStorage.getItem('userName') || ''
  userLastName.value = localStorage.getItem('userLastName') || ''
  loading.value = true
  try {
    const recentIds = JSON.parse(localStorage.getItem('recentlyViewedProducts') || '[]')
    let url = '/api/home'
    if (recentIds.length > 0) {
      url += '?' + recentIds.map(id => `recent[]=${id}`).join('&')
    }

    const res = await fetch(url)
    const result = await res.json()

    data.value = {
      bestRatedProducts: Array.isArray(result.bestRatedProducts)
          ? result.bestRatedProducts.filter(isNotEmpty)
          : [],
      cheapestProduct: Array.isArray(result.cheapestProduct)
          ? result.cheapestProduct.filter(isNotEmpty)
          : [],
      recentlyViewedProducts: Array.isArray(result.recentlyViewedProducts)
          ? result.recentlyViewedProducts.filter(isNotEmpty)
          : [],
      mostSoldProducts: Array.isArray(result.mostSoldProducts)
          ? result.mostSoldProducts.filter(isNotEmpty)
          : [],
      latestProducts: Array.isArray(result.latestProducts)
          ? result.latestProducts.filter(isNotEmpty)
          : [],
      tags: Array.isArray(result.tags) ? result.tags : []
    }

    if (data.value.tags.length > 0) {
      await fetchTagProducts(data.value.tags[0].id)
    }

  } catch (e) {
    console.error('Erreur lors du chargement des données:', e)
    data.value = {
      bestRatedProducts: [],
      cheapestProduct: [],
      recentlyViewedProducts: [],
      mostSoldProducts: [],
      latestProducts: [],
      tags: []
    }
  } finally {
    loading.value = false
  }
})

watch(activeTab, (newTab) => {
  if (window._paq) {
    window._paq.push([
      'trackEvent',
      'HomeTab',
      'Consultation',
      newTab
    ]);
  }
});
</script>

<style scoped>
.loader {
  border-left-color: #3b82f6;
}
</style>
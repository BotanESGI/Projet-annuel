<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const produits = ref([])
const loading = ref(true)
const error = ref(null)

// Remplace cette URL par celle de ton API réelle
const API_URL = 'https://api.exemple.com/produits'

onMounted(async () => {
  try {
    const response = await axios.get(API_URL)
    produits.value = response.data
  } catch (err) {
    error.value = 'Erreur lors du chargement des produits.'
    console.error(err)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Nos Produits</h1>

    <div v-if="loading">Chargement...</div>
    <div v-if="error" class="text-red-600">{{ error }}</div>

    <div v-if="!loading && produits.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
      <div v-for="produit in produits" :key="produit.id" class="border p-4 rounded shadow hover:shadow-md transition">
        <img :src="produit.image" alt="Image du produit" class="w-full h-48 object-cover mb-4 rounded" />
        <h2 class="text-lg font-semibold mb-2">{{ produit.nom }}</h2>
        <p class="text-gray-700 mb-2">{{ produit.description }}</p>
        <p class="text-blue-600 font-bold">{{ produit.prix }} €</p>
      </div>
    </div>

    <div v-else-if="!loading && !produits.length">Aucun produit trouvé.</div>
  </div>
</template>

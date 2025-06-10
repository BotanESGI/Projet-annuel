<template>
  <div class="min-h-screen flex justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl w-full space-y-8 bg-white p-8 rounded-lg shadow">
      <div>
        <h2 class="text-center text-3xl font-extrabold text-gray-900 mb-8">
          Nos Produits
        </h2>
      </div>

      <div v-if="loading" class="text-center">
        <p class="text-gray-600">Chargement...</p>
      </div>

      <div v-if="error" class="text-center text-red-600">{{ error }}</div>

      <div v-if="!loading && produits.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <div v-for="produit in produits" :key="produit.id" class="border p-4 rounded shadow hover:shadow-md transition">
          <img :src="produit.image" :alt="produit.nom" class="w-full h-48 object-cover mb-4 rounded" />
          <h3 class="text-lg font-semibold mb-2">{{ produit.nom }}</h3>
          <p class="text-gray-600 mb-2">{{ produit.description }}</p>
          <p class="text-blue-600 font-bold">{{ produit.prix }} €</p>
        </div>
      </div>

      <div v-else-if="!loading && !produits.length" class="text-center text-gray-600">
        Aucun produit disponible pour le moment.
      </div>
    </div>
  </div>
</template>

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
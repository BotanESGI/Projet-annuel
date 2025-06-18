<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full space-y-8">
      <h2 class="text-center text-3xl font-extrabold text-gray-900">Mon compte</h2>
      <AlertMessage :message="success" type="success" />
      <AlertMessage :message="error" type="error" />

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2 text-gray-700">Profil</h3>
            <p class="text-gray-600 mb-4">Modifier vos informations personnelles et votre mot de passe</p>
            <router-link
                to="/profile"
                class="w-full block py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-center"
            >
              Gérer mon profil
            </router-link>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2 text-gray-700">Adresses</h3>
            <p class="text-gray-600 mb-4">Gérer vos adresses de livraison et de facturation</p>
            <router-link
                to="/addresses"
                class="w-full block py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 text-center"
            >
              Gérer mes adresses
            </router-link>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2 text-gray-700">Commandes</h3>
            <p class="text-gray-600 mb-4">Consultez vos commandes passées et leur statut</p>
            <router-link
                to="/orders"
                class="w-full block py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 text-center"
            >
              Voir mes commandes
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AlertMessage from '@/components/AlertMessage.vue'

const success = ref('')
const error = ref('')

onMounted(() => {
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
  }

  const urlParams = new URLSearchParams(window.location.search)
  const successParam = urlParams.get('success')
  if (successParam) {
    success.value = decodeURIComponent(successParam)
  }
})
</script>

<style scoped>
.transition-shadow {
  transition: box-shadow 0.3s ease;
}
</style>
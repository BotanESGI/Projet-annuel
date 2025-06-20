<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div v-if="isLoading" class="text-center">
        <svg class="animate-spin h-12 w-12 mx-auto text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-4 text-gray-600">Vérification du compte en cours...</p>
      </div>

      <div v-else class="text-center">
        <div :class="{'text-green-600': tokenValid, 'text-red-600': !tokenValid}" class="text-6xl mb-4">
          <svg v-if="tokenValid" xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-900">
          {{ tokenValid ? 'Compte vérifié avec succès !' : 'Échec de la vérification' }}
        </h2>
        <p class="mt-2 text-gray-600">
          {{ message }}
        </p>

        <p v-if="tokenValid && countdown > 0" class="mt-3 text-blue-600">
          Redirection vers la page de connexion dans {{ countdown }} secondes...
        </p>

        <div v-if="!tokenValid" class="mt-6">
          <router-link
              to="/register"
              class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            S'inscrire
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const token = route.params.token

const isLoading = ref(true)
const tokenValid = ref(false)
const message = ref('')
const countdown = ref(6)
let intervalId = null

onMounted(async () => {
  if (!token) {
    tokenValid.value = false
    message.value = 'Token de vérification manquant'
    isLoading.value = false
    return
  }

  try {
    const response = await axios.get(`/api/verify-email/${token}`)
    tokenValid.value = response.data.valid
    message.value = response.data.message

    if (tokenValid.value) {
      intervalId = setInterval(() => {
        countdown.value--
        if (countdown.value <= 0) {
          clearInterval(intervalId)
          router.push('/login')
        }
      }, 1000)
    }
  } catch (err) {
    tokenValid.value = false
    message.value = err.response?.data?.message || 'Une erreur est survenue lors de la vérification du compte'
    console.error('Erreur lors de la vérification du compte:', err)
  } finally {
    isLoading.value = false
  }
})

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
})
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <AlertMessage :message="success" type="success" />
      <AlertMessage :message="error" type="error" />
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Réinitialisation<br>Mot de passe
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Entrez votre adresse e-mail pour recevoir un lien de réinitialisation
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="demanderReinitialisation">
        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Adresse e-mail
            </label>
            <input
                id="email"
                v-model="email"
                name="email"
                type="email"
                required
                :disabled="isLoading"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                placeholder="exemple@email.com"
            />
          </div>
        </div>

        <div>
          <button
              type="submit"
              :disabled="isLoading"
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isLoading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ isLoading ? 'Envoi en cours...' : 'Envoyer le lien' }}
          </button>
        </div>

        <div class="text-center space-y-2">
          <router-link to="/login" class="font-medium text-blue-600 hover:text-blue-500 block" :class="{ 'pointer-events-none opacity-50': isLoading }">
            Retour à la page de connexion
          </router-link>
          <router-link to="/register" class="font-medium text-blue-600 hover:text-blue-500 block" :class="{ 'pointer-events-none opacity-50': isLoading }">
            Pas encore de compte ? S'inscrire
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import AlertMessage from '@/components/AlertMessage.vue'
import { ref } from 'vue'
import axios from 'axios'

const email = ref('')
const error = ref('')
const success = ref('')
const isLoading = ref(false)

const demanderReinitialisation = async () => {
  if (isLoading.value) return
  error.value = ''
  success.value = ''

  if (!email.value) {
    error.value = 'Veuillez saisir votre adresse e-mail'
    return
  }

  try {
    isLoading.value = true
    await axios.post('/api/reset-password', {
      email: email.value
    })

    success.value = 'Si un compte existe avec cette adresse, un e-mail avec les instructions de réinitialisation a été envoyé à votre adresse'
    email.value = ''
  } catch (err) {
    if (err.response && err.response.data && err.response.data.error) {
      error.value = err.response.data.error
    } else {
      error.value = 'Une erreur est survenue lors de la demande de réinitialisation'
    }
    console.error(err)
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg);}
  to { transform: rotate(360deg);}
}
</style>
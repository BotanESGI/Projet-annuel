<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow">
      <h2 class="text-center text-3xl font-extrabold text-gray-900">Mon compte</h2>
      <AlertMessage :message="success" type="success" />
      <AlertMessage :message="error" type="error" />
      <form class="mt-8 space-y-6" @submit.prevent="mettreAJourCompte">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nom</label>
            <input
                v-model="nom"
                type="text"
                required
                :disabled="isLoading"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Prénom</label>
            <input
                v-model="lastname"
                type="text"
                required
                :disabled="isLoading"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
                v-model="email"
                type="email"
                required
                :disabled="isLoading"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
            <input
                v-model="password"
                type="password"
                :disabled="isLoading"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
            <p class="text-sm text-gray-500">Laissez vide pour ne pas modifier</p>
          </div>
        </div>
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
          {{ isLoading ? 'Chargement...' : 'Mettre à jour' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AlertMessage from '@/components/AlertMessage.vue'

const nom = ref('')
const lastname = ref('')
const email = ref('')
const password = ref('')
const success = ref('')
const error = ref('')
const isLoading = ref(true)

onMounted(async () => {
  try {
    const token = localStorage.getItem('token');

    const res = await axios.get('/api/me', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });

    nom.value = res.data.user.nom
    lastname.value = res.data.user.lastname
    email.value = res.data.user.email
  } catch (err) {
    console.error("Erreur:", err.response?.data)
    error.value = "Impossible de charger les informations."
  } finally {
    isLoading.value = false
  }
})

const mettreAJourCompte = async () => {
  if (isLoading.value) return

  success.value = ''
  error.value = ''
  isLoading.value = true

  try {
    const data = {
      nom: nom.value,
      lastname: lastname.value,
      email: email.value
    }
    if (password.value) data.password = password.value

    const res = await axios.put('/api/me', data, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    success.value = 'Informations mises à jour !'
    password.value = ''
    localStorage.setItem('user', JSON.stringify(res.data.user))
    window.dispatchEvent(new Event('auth-changed'))
  } catch (err) {
    error.value = err.response?.data?.errors
        ? Object.values(err.response.data.errors).join(', ')
        : "Erreur lors de la mise à jour."
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
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
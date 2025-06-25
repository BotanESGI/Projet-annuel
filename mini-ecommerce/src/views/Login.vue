<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <AlertMessage :message="success" type="success" />
      <AlertMessage :message="error" type="error" />
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Connexion à votre compte
        </h2>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="connexion">
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
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Mot de passe
            </label>
            <input
                id="password"
                v-model="password"
                name="password"
                type="password"
                required
                :disabled="isLoading"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                placeholder="Votre mot de passe"
            />
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
                id="remember-me"
                v-model="rememberMe"
                name="remember-me"
                type="checkbox"
                :disabled="isLoading"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
              Se souvenir de moi
            </label>
          </div>
          <div class="text-sm">
            <a href="/forget-password" class="font-medium text-blue-600 hover:text-blue-500">
              Mot de passe oublié ?
            </a>
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
            {{ isLoading ? 'Connexion en cours...' : 'Se connecter' }}
          </button>
        </div>

        <div class="text-center">
          <router-link to="/register" class="font-medium text-blue-600 hover:text-blue-500" :class="{ 'pointer-events-none opacity-50': isLoading }">
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
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const error = ref('')
const success = ref('')
const isLoading = ref(false)

const connexion = async () => {
  if (isLoading.value) return
  error.value = ''
  success.value = ''
  try {
    isLoading.value = true
    const response = await axios.post('/api/login_check', {
      username: email.value,
      password: password.value,
      remember: rememberMe.value
    })

    localStorage.setItem('token', response.data.token)
    localStorage.setItem('userId', response.data.userId)
    localStorage.setItem('userName', response.data.userName);

    window.dispatchEvent(new Event('auth-changed'))
    success.value = 'Connexion réussie ! Redirection...'
    setTimeout(() => {
      router.push('/')
    }, 2000)
  } catch (err) {
    if (err.response?.data?.message === "Votre compte n'est pas encore vérifié.") {
      error.value = "Votre compte n'est pas encore vérifié. Veuillez vérifier vos emails pour activer votre compte."
    } else {
      error.value = 'Email ou mot de passe incorrect'
    }
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
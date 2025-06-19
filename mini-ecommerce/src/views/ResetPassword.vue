<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div v-if="isLoading" class="text-center">
        <svg class="animate-spin h-12 w-12 mx-auto text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-4 text-gray-600">Vérification du token...</p>
      </div>

      <div v-else-if="!tokenValid" class="text-center">
        <div class="text-red-600 text-6xl mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Token invalide</h2>
        <p class="mt-2 text-gray-600">Ce lien de réinitialisation est invalide ou a expiré.</p>
        <div class="mt-6">
          <router-link to="/forget-password" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Demander un nouveau lien
          </router-link>
        </div>
      </div>

      <template v-else>
        <AlertMessage :message="success" type="success" />
        <AlertMessage :message="error" type="error" />
        <div>
          <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Réinitialiser votre mot de passe
          </h2>
          <p class="mt-2 text-center text-sm text-gray-600">
            Veuillez saisir votre nouveau mot de passe
          </p>
        </div>
        <form class="mt-8 space-y-6" @submit.prevent="reinitialiserMotDePasse">
          <div class="rounded-md shadow-sm space-y-4">
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700">
                Nouveau mot de passe
              </label>
              <input
                  id="password"
                  v-model="password"
                  name="password"
                  type="password"
                  required
                  :disabled="formLoading"
                  class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                  placeholder="Nouveau mot de passe"
              />
              <!-- Indicateur de force du mot de passe -->
              <div v-if="password" class="mt-2">
                <div class="flex space-x-1">
                  <div v-for="(level, index) in 4" :key="index"
                       class="h-1.5 w-1/4 rounded-full transition-colors"
                       :class="getPasswordStrengthColor(index)"></div>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                  Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.
                </p>
              </div>
            </div>
            <div>
              <label for="confirmPassword" class="block text-sm font-medium text-gray-700">
                Confirmer le mot de passe
              </label>
              <input
                  id="confirmPassword"
                  v-model="confirmPassword"
                  name="confirmPassword"
                  type="password"
                  required
                  :disabled="formLoading"
                  class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                  placeholder="Confirmer le mot de passe"
              />
            </div>
          </div>

          <div>
            <button
                type="submit"
                :disabled="formLoading"
                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="formLoading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              {{ formLoading ? 'Traitement en cours...' : 'Réinitialiser le mot de passe' }}
            </button>
          </div>

          <div class="text-center">
            <router-link to="/login" class="font-medium text-blue-600 hover:text-blue-500 block" :class="{ 'pointer-events-none opacity-50': formLoading }">
              Retour à la page de connexion
            </router-link>
          </div>
        </form>
      </template>
    </div>
  </div>
</template>

<script setup>
import AlertMessage from '@/components/AlertMessage.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const token = route.params.token

const password = ref('')
const confirmPassword = ref('')
const error = ref('')
const success = ref('')
const isLoading = ref(true)
const formLoading = ref(false)
const tokenValid = ref(false)

const validerMotDePasse = (mdp) => {
  if (mdp.length < 8) {
    return 'Le mot de passe doit contenir au moins 8 caractères'
  }

  if (!/[A-Z]/.test(mdp)) {
    return 'Le mot de passe doit contenir au moins une lettre majuscule'
  }

  if (!/[a-z]/.test(mdp)) {
    return 'Le mot de passe doit contenir au moins une lettre minuscule'
  }


  if (!/[0-9]/.test(mdp)) {
    return 'Le mot de passe doit contenir au moins un chiffre'
  }


  if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(mdp)) {
    return 'Le mot de passe doit contenir au moins un caractère spécial'
  }

  return null
}


const getPasswordStrength = (password) => {
  let strength = 0
  if (password.length >= 8) strength++
  if (/[A-Z]/.test(password)) strength++
  if (/[a-z]/.test(password)) strength++
  if (/[0-9]/.test(password)) strength++
  if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength++
  return Math.min(4, strength)
}

const getPasswordStrengthColor = (index) => {
  const strength = getPasswordStrength(password.value)
  if (index >= strength) return 'bg-gray-200'

  if (strength === 1) return 'bg-red-500'
  if (strength === 2) return 'bg-orange-500'
  if (strength === 3) return 'bg-yellow-500'
  return 'bg-green-500'
}

onMounted(async () => {
  if (!token) {
    tokenValid.value = false
    isLoading.value = false
    return
  }

  try {
    const response = await axios.get(`/api/reset-password/verify/${token}`)
    tokenValid.value = response.data.valid
  } catch (err) {
    tokenValid.value = false
    console.error('Erreur lors de la vérification du token:', err)
  } finally {
    isLoading.value = false
  }
})

const reinitialiserMotDePasse = async () => {
  if (formLoading.value) return
  error.value = ''
  success.value = ''

  if (!password.value) {
    error.value = 'Veuillez saisir un nouveau mot de passe'
    return
  }

  const erreurValidation = validerMotDePasse(password.value)
  if (erreurValidation) {
    error.value = erreurValidation
    return
  }

  if (password.value !== confirmPassword.value) {
    error.value = 'Les mots de passe ne correspondent pas'
    return
  }

  try {
    formLoading.value = true
    const response = await axios.post('/api/reset-password/confirm', {
      token: token,
      password: password.value
    })

    success.value = 'Votre mot de passe a été réinitialisé avec succès ! Redirection...'
    setTimeout(() => {
      router.push('/login')
    }, 2000)
  } catch (err) {
    if (err.response && err.response.data && err.response.data.error) {
      error.value = err.response.data.error
    } else {
      error.value = 'Une erreur est survenue lors de la réinitialisation du mot de passe'
    }
    console.error(err)
  } finally {
    formLoading.value = false
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
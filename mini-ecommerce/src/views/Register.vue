<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <AlertMessage :message="success" type="success" />
      <AlertMessage :message="error" type="error" />
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Créer un compte
        </h2>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="inscription">
        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label for="nom" class="block text-sm font-medium text-gray-700">
              Nom
            </label>
            <input
                id="nom"
                v-model="nom"
                name="nom"
                type="text"
                minlength="3"
                required
                :disabled="isLoading"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                placeholder="Dupont"
            />
          </div>

          <div>
            <label for="lastname" class="block text-sm font-medium text-gray-700">
              Prénom
            </label>
            <input
                id="lastname"
                v-model="lastname"
                name="lastname"
                type="text"
                minlength="3"
                required
                :disabled="isLoading"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                placeholder="Jean"
            />
          </div>

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
                placeholder="Choisissez un mot de passe"
            />
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
            <label for="password-confirm" class="block text-sm font-medium text-gray-700">
              Confirmer le mot de passe
            </label>
            <input
                id="password-confirm"
                v-model="passwordConfirm"
                name="password-confirm"
                type="password"
                required
                :disabled="isLoading"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                placeholder="Confirmez votre mot de passe"
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
            {{ isLoading ? 'Inscription en cours...' : 'S\'inscrire' }}
          </button>
        </div>

        <div class="text-center">
          <router-link
              to="/login"
              class="font-medium text-blue-600 hover:text-blue-500"
              :class="{ 'pointer-events-none opacity-50': isLoading }"
          >
            Déjà un compte ? Se connecter
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
const nom = ref('')
const lastname = ref('')
const email = ref('')
const password = ref('')
const passwordConfirm = ref('')
const error = ref('')
const success = ref('')
const isLoading = ref(false)

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

const validateForm = () => {
  if (!email.value || !password.value || !nom.value || !lastname.value) {
    error.value = 'Tous les champs sont obligatoires'
    return false
  }

  if (nom.value.length < 3) {
    error.value = 'Le nom doit contenir au moins 3 caractères'
    return false
  }

  if (lastname.value.length < 3) {
    error.value = 'Le prénom doit contenir au moins 3 caractères'
    return false
  }

  if (password.value !== passwordConfirm.value) {
    error.value = 'Les mots de passe ne correspondent pas'
    return false
  }

  const erreurValidation = validerMotDePasse(password.value)
  if (erreurValidation) {
    error.value = erreurValidation
    return false
  }

  return true
}

const inscription = async () => {
  if (isLoading.value) return

  error.value = ''
  success.value = ''

  if (!validateForm()) return

  try {
    isLoading.value = true

    const response = await axios.post('/api/register', {
      nom: nom.value,
      lastname: lastname.value,
      email: email.value,
      password: password.value
    })

    success.value = response.data.message || 'Inscription réussie ! Redirection...'

  } catch (err) {
    if (err.response) {
      if (err.response.data.errors) {
        error.value = Object.values(err.response.data.errors).join('\n')
      } else if (err.response.data.error) {
        error.value = err.response.data.error
      } else {
        error.value = 'Une erreur est survenue lors de l\'inscription'
      }
    } else {
      error.value = 'Une erreur de connexion est survenue'
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
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.whitespace-pre-line {
  white-space: pre-line;
}
</style>
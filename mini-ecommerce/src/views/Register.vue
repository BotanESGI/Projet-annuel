<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Créer un compte
        </h2>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="inscription">
        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label for="nom" class="block text-sm font-medium text-gray-700">
              Nom complet
            </label>
            <input
                id="nom"
                v-model="nom"
                name="nom"
                type="text"
                required
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                placeholder="Jean Dupont"
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
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
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
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                placeholder="Choisissez un mot de passe"
            />
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
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                placeholder="Confirmez votre mot de passe"
            />
          </div>
        </div>

        <div>
          <button
              type="submit"
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            S'inscrire
          </button>
        </div>

        <div class="text-center">
          <router-link to="/connexion" class="font-medium text-blue-600 hover:text-blue-500">
            Déjà un compte ? Se connecter
          </router-link>
        </div>
      </form>

      <div v-if="error" class="mt-4 text-red-600 text-center">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const nom = ref('')
const email = ref('')
const password = ref('')
const passwordConfirm = ref('')
const error = ref('')

const inscription = async () => {
  if (password.value !== passwordConfirm.value) {
    error.value = 'Les mots de passe ne correspondent pas'
    return
  }

  try {
    error.value = ''
    const response = await axios.post('https://api.exemple.com/register', {
      nom: nom.value,
      email: email.value,
      password: password.value
    })

    // Stocker le token si nécessaire
    localStorage.setItem('token', response.data.token)

    // Rediriger vers la page d'accueil
    router.push('/')
  } catch (err) {
    error.value = 'Une erreur est survenue lors de l\'inscription'
    console.error(err)
  }
}
</script>
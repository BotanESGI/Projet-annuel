<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full space-y-8 bg-white p-8 rounded-lg shadow">
      <div>
        <h2 class="text-center text-3xl font-extrabold text-gray-900">
          Mon compte
        </h2>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="mettreAJourCompte">
        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nom</label>
            <input
                v-model="utilisateur.nom"
                type="text"
                required
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
                v-model="utilisateur.email"
                type="email"
                required
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              Mot de passe
            </label>
            <input
                v-model="utilisateur.motdepasse"
                type="password"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            />
            <p class="mt-1 text-sm text-gray-500">
              Laissez vide pour ne pas modifier
            </p>
          </div>
        </div>

        <div>
          <button
              type="submit"
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Mettre à jour
          </button>
        </div>

        <div v-if="confirmation" class="text-green-600 text-center">
          Informations mises à jour !
        </div>
        <div v-if="erreur" class="text-red-600 text-center">
          {{ erreur }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const API_URL = 'https://exemple.com/api/utilisateur/123'

const utilisateur = ref({
  nom: '',
  email: '',
  motdepasse: ''
})

const confirmation = ref(false)
const erreur = ref(null)

onMounted(async () => {
  try {
    const res = await axios.get(API_URL)
    utilisateur.value.nom = res.data.nom
    utilisateur.value.email = res.data.email
  } catch (err) {
    erreur.value = "Impossible de charger les informations."
    console.error(err)
  }
})

const mettreAJourCompte = async () => {
  confirmation.value = false
  erreur.value = null

  try {
    const data = {
      nom: utilisateur.value.nom,
      email: utilisateur.value.email
    }

    if (utilisateur.value.motdepasse) {
      data.motdepasse = utilisateur.value.motdepasse
    }

    await axios.put(API_URL, data)
    confirmation.value = true
    utilisateur.value.motdepasse = ''
  } catch (err) {
    erreur.value = "Erreur lors de la mise à jour."
    console.error(err)
  }
}
</script>
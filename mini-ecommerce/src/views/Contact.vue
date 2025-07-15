<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full space-y-8 bg-white p-8 rounded-lg shadow">
      <div>
        <h2 class="text-center text-3xl font-extrabold text-gray-900">
          Contactez-nous
        </h2>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="envoyerFormulaire">
        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Nom
              <span class="text-red-500">*</span>
            </label>
            <input
                v-model="nom"
                type="text"
                required
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              Email
              <span class="text-red-500">*</span>
            </label>
            <input
                v-model="email"
                type="email"
                required
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              Message
              <span class="text-red-500">*</span>
            </label>
            <textarea
                v-model="message"
                required
                rows="5"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            ></textarea>
          </div>
        </div>

        <div>
          <button
              type="submit"
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Envoyer
          </button>
        </div>

        <div v-if="confirmation" class="text-green-600 text-center">
          Message envoyé avec succès !
        </div>
        <div v-if="erreur" class="text-red-600 text-center">
          {{ erreur }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const nom = ref('')
const email = ref('')
const message = ref('')
const confirmation = ref(false)
const erreur = ref(null)

// Mets ici l'URL de ton backend Symfony
const API_URL = '/api/contact'

const envoyerFormulaire = async () => {
  confirmation.value = false
  erreur.value = null

  try {
    const payload = {
      nom: nom.value,
      email: email.value,
      message: message.value
    }

    await axios.post(API_URL, payload)
    confirmation.value = true

    nom.value = ''
    email.value = ''
    message.value = ''
  } catch (err) {
    if (err.response && err.response.data && err.response.data.error) {
      erreur.value = err.response.data.error
    } else {
      erreur.value = 'Erreur lors de l\'envoi. Veuillez réessayer.'
    }
    console.error(err)
  }
}
</script>
<template>
  <div class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Contactez-nous</h1>

    <form @submit.prevent="envoyerFormulaire" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Nom</label>
        <input v-model="nom" type="text" required class="mt-1 w-full border rounded px-3 py-2" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input v-model="email" type="email" required class="mt-1 w-full border rounded px-3 py-2" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Message</label>
        <textarea v-model="message" required rows="5" class="mt-1 w-full border rounded px-3 py-2"></textarea>
      </div>

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Envoyer
      </button>

      <p v-if="confirmation" class="text-green-600 mt-4">Message envoyé avec succès !</p>
      <p v-if="erreur" class="text-red-600 mt-4">{{ erreur }}</p>
    </form>
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

const API_URL = 'https://exemple.com/api/contact'

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
    erreur.value = 'Erreur lors de l’envoi. Veuillez réessayer.'
    console.error(err)
  }
}
</script>

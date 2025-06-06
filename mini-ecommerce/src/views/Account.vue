<template>
  <div class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Mon compte</h1>

    <form @submit.prevent="mettreAJourCompte" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Nom</label>
        <input v-model="utilisateur.nom" type="text" required class="mt-1 w-full border rounded px-3 py-2" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input v-model="utilisateur.email" type="email" required class="mt-1 w-full border rounded px-3 py-2" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
        <input v-model="utilisateur.motdepasse" type="password" class="mt-1 w-full border rounded px-3 py-2" />
        <p class="text-sm text-gray-500">Laisse vide pour ne pas le modifier</p>
      </div>

      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Enregistrer
      </button>

      <p v-if="confirmation" class="text-green-600 mt-4">Informations mises à jour !</p>
      <p v-if="erreur" class="text-red-600 mt-4">{{ erreur }}</p>
    </form>
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

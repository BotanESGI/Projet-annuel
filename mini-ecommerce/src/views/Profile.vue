<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div v-if="isLoading" class="flex flex-col items-center justify-center w-full">
      <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <p class="mt-4 text-blue-600 font-semibold">Chargement du profil...</p>
    </div>
    <div v-else class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow">
      <h2 class="text-center text-3xl font-extrabold text-gray-900">Mon profil</h2>
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
                readonly
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 bg-gray-100 cursor-not-allowed focus:outline-none sm:text-sm"
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
            class="w-full block py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-center disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isLoading ? 'Chargement...' : 'Mettre à jour' }}
        </button>
        <div class="mt-8 border-t pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Supprimer mon compte</h3>
          <div class="space-y-4">
            <div>
              <button
                  type="button"
                  @click="deleteAccount('soft')"
                  :disabled="isLoading"
                  class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Supprimer mon compte (SOFT)
              </button>
              <p class="text-xs text-gray-500 mt-1">Votre compte sera supprimé mais vos données seront conservées.</p>
            </div>
            <div>
              <button
                  type="button"
                  @click="deleteAccount('hard')"
                  :disabled="isLoading"
                  class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Supprimer mon compte (HARD)
              </button>
              <p class="text-xs text-gray-500 mt-1">Cette action supprimera toutes vos données.</p>
            </div>
          </div>
        </div>
      </form>
      <hr>
      <div class="p-6 rounded-lg shadow mt-8">
        <h3 class="text-lg font-semibold mb-2 text-black">Double authentification (2FA)</h3>
        <div v-if="isLoading2FA" class="flex flex-col items-center justify-center py-4">
          <svg class="animate-spin h-8 w-8 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="mt-2 font-medium text-black">Traitement en cours...</p>
        </div>
        <div v-else>
          <div v-if="!is2FAEnabled && !enAttente2FA">
            <button @click="activer2FA" class="w-full bg-blue-600 text-white px-4 py-2 rounded" :disabled="isLoading2FA">
              Activer 2FA
            </button>
          </div>
          <div v-if="is2FAEnabled || enAttente2FA">
            <button @click="desactiver2FA" class="w-full bg-red-600 text-white px-4 py-2 rounded mb-2" :disabled="isLoading2FA">
              Désactiver 2FA
            </button>
          </div>
          <div v-if="qrCodeUrl" class="mt-4">
            <p class="text-black">Scanne ce QR code avec Google Authenticator :</p>
            <div class="flex justify-center my-2">
              <img :src="qrCodeUrl" alt="QR Code 2FA" class="mb-2" />
            </div>
            <p class="text-black text-center">Ou entre ce code manuellement : <strong>{{ secret }}</strong></p>
            <div class="mt-2">
              <label class="text-black">Tester 2FA (Code à 6 chiffres :)</label>
              <input v-model="code" maxlength="6" class="border rounded px-2 py-1 w-full mb-2 text-black" placeholder="Code 2FA" />
              <button @click="verifierCode" class="w-full bg-green-600 text-white px-4 py-2 rounded" :disabled="isLoading2FA">
                Tester le code
              </button>
            </div>
            <p v-if="message" :class="{'text-black': true, 'font-semibold': valid}" class="mt-2">{{ message }}</p>
          </div>
          <div v-if="is2FAEnabled && !qrCodeUrl">
            <p class="text-black">2FA déjà activé sur votre compte.</p>
            <br>
            <label class="text-black">Tester votre 2FA :</label>
            <input v-model="code" maxlength="6" class="border rounded px-2 py-1 w-full mb-2 text-black" placeholder="Code 2FA" />
            <button @click="verifierCode" class="w-full bg-green-600 text-white px-4 py-2 rounded" :disabled="isLoading2FA">
              Tester le code
            </button>
            <p v-if="message" :class="{'text-black': true, 'font-semibold': valid}" class="mt-2">{{ message }}</p>
          </div>
        </div>
      </div>
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

const is2FAEnabled = ref(false)
const qrCodeUrl = ref('')
const secret = ref('')
const code = ref('')
const message = ref('')
const valid = ref(false)
const isLoading2FA = ref(false)
const enAttente2FA = ref(false)

onMounted(async () => {
  try {
    const token = localStorage.getItem('token');
    const res = await axios.get('/api/account', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    nom.value = res.data.user.nom
    lastname.value = res.data.user.lastname
    email.value = res.data.user.email

    is2FAEnabled.value = !!res.data.user.totpSecret
  } catch (err) {
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
      lastname: lastname.value
    }
    if (password.value) data.password = password.value
    const res = await axios.put('/api/account', data, {
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

const deleteAccount = async (type) => {
  if (isLoading.value) return
  const confirmMessage = "Pour confirmer la suppression définitive de votre compte, veuillez taper 'SUPPRIMER'";
  const confirmation = prompt(confirmMessage);
  if (confirmation !== 'SUPPRIMER') {
    if (confirmation !== null) {
      error.value = "Confirmation incorrecte. Votre compte n'a pas été supprimé.";
    }
    return;
  }
  if (window._paq) {
    window._paq.push([
      'trackEvent',
      'Compte',
      'Suppression',
      type === 'hard' ? 'Suppression HARD' : 'Suppression SOFT'
    ]);
  }
  success.value = '';
  error.value = '';
  isLoading.value = true;
  try {
    await axios.delete(`/api/account/delete/${type}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.dispatchEvent(new Event('auth-changed'));
    window.location.href = '/';
  } catch (err) {
    error.value = err.response?.data?.errors?.general || `Erreur lors de la ${type === 'soft' ? 'désactivation' : 'suppression'} du compte.`;
    isLoading.value = false;
  }
}

const activer2FA = async () => {
  isLoading2FA.value = true
  message.value = ''
  try {
    const token = localStorage.getItem('token')
    const res = await axios.post('/api/2fa/activate', {}, { headers: { Authorization: `Bearer ${token}` } })
    qrCodeUrl.value = res.data.qrCodeUrl
    secret.value = res.data.secret
    is2FAEnabled.value = false // On attend la vérification du code pour activer
    enAttente2FA.value = true
  } catch (e) {
    message.value = "Erreur lors de la génération du QR code"
    valid.value = false
  } finally {
    isLoading2FA.value = false
  }
}

const verifierCode = async () => {
  isLoading2FA.value = true
  message.value = ''
  try {
    const token = localStorage.getItem('token')
    const res = await axios.post('/api/2fa/verify', { code: code.value }, { headers: { Authorization: `Bearer ${token}` } })
    if (res.data.valid) {
      message.value = "2FA testé avec succès !"
      valid.value = true
      is2FAEnabled.value = true
      enAttente2FA.value = false
      qrCodeUrl.value = ''
      secret.value = ''
      code.value = ''
    } else {
      message.value = "Code invalide"
      valid.value = false
    }
  } catch (e) {
    message.value = "Erreur lors du test"
    valid.value = false
  } finally {
    isLoading2FA.value = false
  }
}

const desactiver2FA = async () => {
  isLoading2FA.value = true
  message.value = ''
  try {
    const token = localStorage.getItem('token')
    await axios.post('/api/2fa/deactivate', {}, { headers: { Authorization: `Bearer ${token}` } })
    is2FAEnabled.value = false
    enAttente2FA.value = false
    qrCodeUrl.value = ''
    secret.value = ''
    code.value = ''
    message.value = "2FA désactivé avec succès."
  } catch (e) {
    message.value = "Erreur lors de la désactivation"
  } finally {
    isLoading2FA.value = false
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
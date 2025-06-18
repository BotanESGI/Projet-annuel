<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full space-y-8">
      <h2 class="text-center text-3xl font-extrabold text-gray-900">Mes adresses</h2>
      <AlertMessage :message="success" type="success" />
      <AlertMessage :message="error" type="error" />

      <div v-if="isLoading" class="flex justify-center">
        <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else>
        <div v-if="addresses.length === 0" class="bg-white rounded-lg shadow p-6 text-center">
          <p class="text-gray-500 mb-4">Vous n'avez pas encore d'adresse enregistrée.</p>
          <button
              @click="openAddressModal()"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Ajouter une adresse
          </button>
        </div>

        <div v-else class="space-y-4">
          <div class="flex justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Liste de vos adresses</h3>
            <button
                @click="openAddressModal()"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Ajouter une adresse
            </button>
          </div>

          <div v-for="address in addresses" :key="address.id" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex justify-between items-start">
              <div>
                <p class="font-medium text-gray-800">{{ address.street }}</p>
                <p class="text-gray-600">{{ address.postalCode }} {{ address.city }}</p>
                <div v-if="address.isDefault" class="mt-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Adresse par défaut
                  </span>
                </div>
              </div>
              <div class="flex space-x-2">
                <button
                    @click="openAddressModal(address)"
                    class="p-1 rounded-md hover:bg-gray-100 text-blue-600"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                    @click="confirmDeleteAddress(address.id)"
                    class="p-1 rounded-md hover:bg-gray-100 text-red-600"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
                <button
                    v-if="!address.isDefault"
                    @click="setDefaultAddress(address.id)"
                    class="p-1 rounded-md hover:bg-gray-100 text-green-600"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
    <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">
          {{ editingAddress ? 'Modifier l\'adresse' : 'Ajouter une adresse' }}
        </h3>
        <button @click="showModal = false" class="text-gray-400 hover:text-gray-500">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <AlertMessage v-if="modalError" :message="modalError" type="error" />
      <form @submit.prevent="saveAddress">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Rue</label>
            <input
                v-model="currentAddress.street"
                type="text"
                required
                :disabled="isLoadingAction"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                placeholder="123 rue de Paris"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Ville</label>
            <input
                v-model="currentAddress.city"
                type="text"
                required
                :disabled="isLoadingAction"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                placeholder="Paris"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Code postal</label>
            <input
                v-model="currentAddress.postalCode"
                type="text"
                required
                :disabled="isLoadingAction"
                class="mt-1 appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                :class="{'border-red-500': postalCodeError}"
                placeholder="75000"
            />
            <p v-if="postalCodeError" class="mt-1 text-sm text-red-600">
              {{ postalCodeError }}
            </p>
          </div>
          <div class="flex items-center">
            <input
                id="isDefault"
                v-model="currentAddress.isDefault"
                type="checkbox"
                :disabled="isLoadingAction"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
            <label for="isDefault" class="ml-2 block text-sm text-gray-900">
              Définir comme adresse par défaut
            </label>
          </div>
        </div>
        <div class="mt-6">
          <button
              type="submit"
              :disabled="isLoadingAction || postalCodeError !== ''"
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isLoadingAction" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ isLoadingAction ? 'Enregistrement...' : (editingAddress ? 'Mettre à jour' : 'Ajouter') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <div v-if="showDeleteConfirm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
    <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmer la suppression</h3>
      <p class="text-gray-500 mb-6">Êtes-vous sûr de vouloir supprimer cette adresse ? Cette action est irréversible.</p>
      <div class="flex justify-end space-x-4">
        <button
            @click="showDeleteConfirm = false"
            class="py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Annuler
        </button>
        <button
            @click="deleteAddress"
            :disabled="isLoadingAction"
            class="py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="isLoadingAction" class="inline-block mr-2">
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>
          {{ isLoadingAction ? 'Suppression...' : 'Supprimer' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import AlertMessage from '@/components/AlertMessage.vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const addresses = ref([])
const success = ref('')
const error = ref('')
const modalError = ref('')
const isLoading = ref(true)
const isLoadingAction = ref(false)
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const addressToDeleteId = ref(null)
const editingAddress = ref(false)
const postalCodeError = ref('')
const currentAddress = ref({
  street: '',
  city: '',
  postalCode: '',
  isDefault: false
})

watch(() => currentAddress.value.postalCode, (newValue) => {
  validatePostalCode(newValue)
})

const validatePostalCode = (code) => {
  const postalCodeRegex = /^\d{5}(-\d{4})?$/

  if (!code) {
    postalCodeError.value = 'Le code postal ne doit pas être vide.'
  } else if (!postalCodeRegex.test(code)) {
    postalCodeError.value = 'Le code postal doit être valide (format: 12345 ou 12345-6789).'
  } else {
    postalCodeError.value = ''
  }

  return !postalCodeError.value
}

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }

  try {
    const res = await axios.get('/api/addresses', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })

    addresses.value = res.data.addresses
  } catch (err) {
    console.error("Erreur:", err.response?.data)
    error.value = "Impossible de charger les adresses."
  } finally {
    isLoading.value = false
  }
})

const openAddressModal = (address) => {
  postalCodeError.value = ''
  modalError.value = ''

  if (address) {
    currentAddress.value = { ...address }
    editingAddress.value = true
  } else {
    currentAddress.value = {
      street: '',
      city: '',
      postalCode: '',
      isDefault: false
    }
    editingAddress.value = false
  }
  showModal.value = true
}

const saveAddress = async () => {
  if (isLoadingAction.value) return

  success.value = ''
  error.value = ''
  modalError.value = ''

  if (!validatePostalCode(currentAddress.value.postalCode)) {
    return
  }

  isLoadingAction.value = true

  try {
    const token = localStorage.getItem('token')
    let response

    if (editingAddress.value) {
      response = await axios.put(`/api/addresses/${currentAddress.value.id}`, currentAddress.value, {
        headers: { Authorization: `Bearer ${token}` }
      })

      const index = addresses.value.findIndex(a => a.id === currentAddress.value.id)
      if (index !== -1) {
        addresses.value[index] = response.data.address
      }

      success.value = "Adresse mise à jour avec succès!"
    } else {
      response = await axios.post('/api/addresses', currentAddress.value, {
        headers: { Authorization: `Bearer ${token}` }
      })

      addresses.value.push(response.data.address)

      success.value = "Adresse ajoutée avec succès!"
    }

    if (currentAddress.value.isDefault) {
      addresses.value.forEach(address => {
        if (address.id !== response.data.address.id) {
          address.isDefault = false
        }
      })
    }

    showModal.value = false
  } catch (err) {
    const errorMessage = err.response?.data?.errors
        ? Object.values(err.response.data.errors).join(', ')
        : "Erreur lors de l'enregistrement de l'adresse."
    error.value = errorMessage
    modalError.value = errorMessage
    console.error(err)
  } finally {
    isLoadingAction.value = false
  }
}

const confirmDeleteAddress = (id) => {
  addressToDeleteId.value = id
  showDeleteConfirm.value = true
}

const deleteAddress = async () => {
  if (isLoadingAction.value) return

  success.value = ''
  error.value = ''
  isLoadingAction.value = true

  try {
    const token = localStorage.getItem('token')

    await axios.delete(`/api/addresses/${addressToDeleteId.value}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const res = await axios.get('/api/addresses', {
      headers: { Authorization: `Bearer ${token}` }
    })
    addresses.value = res.data.addresses

    success.value = "Adresse supprimée avec succès!"
    showDeleteConfirm.value = false
  } catch (err) {
    error.value = err.response?.data?.errors
        ? Object.values(err.response.data.errors).join(', ')
        : "Erreur lors de la suppression de l'adresse."
    console.error(err)
  } finally {
    isLoadingAction.value = false
  }
}

const setDefaultAddress = async (id) => {
  if (isLoadingAction.value) return

  success.value = ''
  error.value = ''
  isLoadingAction.value = true

  try {
    const token = localStorage.getItem('token')

    await axios.put(`/api/addresses/${id}/default`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })

    addresses.value.forEach(address => {
      address.isDefault = address.id === id
    })

    success.value = "Adresse par défaut mise à jour avec succès!"
  } catch (err) {
    error.value = err.response?.data?.errors
        ? Object.values(err.response.data.errors).join(', ')
        : "Erreur lors de la mise à jour de l'adresse par défaut."
    console.error(err)
  } finally {
    isLoadingAction.value = false
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

.transition-shadow {
  transition: box-shadow 0.3s ease;
}
</style>
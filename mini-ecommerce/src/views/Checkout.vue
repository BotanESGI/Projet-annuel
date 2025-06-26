<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-lg p-8">
      <h2 class="text-xl font-bold text-center text-gray-900 mb-2">
        {{ step === 1 ? 'Étape 1/2 : Adresse de livraison et facturation' : 'Étape 2/2 : Paiement' }}
      </h2>
      <AlertMessage :message="error" type="error" />
      <div v-if="loadingAddresses" class="flex flex-col items-center py-8">
        <span class="loader mb-4"></span>
        <p class="text-gray-700 font-medium">Chargement des adresses...</p>
      </div>
      <div v-else>
        <template v-if="step === 1">
          <h3 class="font-semibold mb-4 text-lg text-gray-800">Sélectionnez une adresse de livraison :</h3>
          <div v-if="addresses.length === 0" class="bg-blue-50 rounded-lg p-6 text-center mb-4">
            <p class="text-gray-500 mb-2">Aucune adresse enregistrée.</p>
            <button
                @click="router.push('/addresses')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Ajouter une adresse
            </button>
          </div>
          <div v-else>
            <div v-for="address in addresses.filter(a => a.type === 'shipping')" :key="address.id" class="mb-3">
              <label
                  class="flex items-center bg-gray-50 rounded-lg px-4 py-3 shadow-sm border border-gray-200 cursor-pointer transition hover:bg-blue-50"
                  :class="{ 'ring-2 ring-blue-400': selectedAddressId === address.id }"
              >
                <input
                    type="radio"
                    v-model="selectedAddressId"
                    :value="address.id"
                    class="form-radio text-blue-600 h-5 w-5 mr-3"
                />
                <span class="flex-1 text-gray-800">
                  {{ address.street }}, {{ address.postalCode }} {{ address.city }}
                  <span v-if="address.isDefault" class="ml-2 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Par défaut</span>
                </span>
              </label>
            </div>
            <button
                @click="router.push('/addresses')"
                class="text-blue-600 underline text-sm mt-2 hover:text-blue-800 transition"
            >
              Gérer mes adresses
            </button>
          </div>
          <h3 class="font-semibold mb-4 text-lg text-gray-800 mt-8">Sélectionnez une adresse de facturation :</h3>
          <div v-if="addresses.filter(a => a.type === 'billing').length === 0" class="bg-blue-50 rounded-lg p-6 text-center mb-4">
            <p class="text-gray-500 mb-2">Aucune adresse de facturation enregistrée.</p>
            <button
                @click="router.push('/addresses')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Ajouter une adresse de facturation
            </button>
          </div>
          <div v-else>
            <div v-for="address in addresses.filter(a => a.type === 'billing')" :key="address.id" class="mb-3">
              <label
                  class="flex items-center bg-gray-50 rounded-lg px-4 py-3 shadow-sm border border-gray-200 cursor-pointer transition hover:bg-blue-50"
                  :class="{ 'ring-2 ring-blue-400': selectedBillingAddressId === address.id }"
              >
                <input
                    type="radio"
                    v-model="selectedBillingAddressId"
                    :value="address.id"
                    class="form-radio text-blue-600 h-5 w-5 mr-3"
                />
                <span class="flex-1 text-gray-800">
                  {{ address.street }}, {{ address.postalCode }} {{ address.city }}
                  <span v-if="address.isDefaultBilling" class="ml-2 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Par défaut</span>
                </span>
              </label>
            </div>
          </div>
        </template>
        <template v-else>
          <button
              class="mb-6 text-blue-600 underline text-sm hover:text-blue-800 transition"
              @click="step = 1"
              type="button"
          >
            ← Retour à l’étape 1 : adresses
          </button>
          <h3 class="font-semibold mb-4 text-lg text-gray-800">Saisissez vos informations de carte bancaire :</h3>
          <p class="text-gray-600 mb-4">Veuillez renseigner vos informations de paiement pour finaliser la commande.</p>
          <form @submit.prevent="confirmPayment" class="mt-8">
            <div id="card-element" class="mb-4"></div>
            <button
                type="submit"
                class="w-full bg-gradient-to-r from-green-500 to-green-400 text-white py-3 rounded-lg font-semibold text-lg shadow-md flex items-center justify-center transition hover:from-green-600 hover:to-green-500 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed"
                :disabled="loadingPay || !cardComplete"
            >
              <span v-if="loadingPay" class="loader-btn mr-2"></span>
              {{ loadingPay ? 'Paiement en cours...' : 'Payer' }}
            </button>
          </form>
        </template>
      </div>
      <button
          v-if="step === 1"
          class="mt-8 w-full bg-gradient-to-r from-blue-500 to-blue-400 text-white py-3 rounded-lg font-semibold text-lg shadow-md flex items-center justify-center transition hover:from-blue-600 hover:to-blue-500 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed"
          :disabled="!selectedAddressId || !selectedBillingAddressId || loadingPay || loadingAddresses || addresses.length === 0"
          @click="goToStep2"
      >
        Continuer
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import AlertMessage from '@/components/AlertMessage.vue'
import { loadStripe } from '@stripe/stripe-js'

const stripePromise = loadStripe('pk_test_51PX6nWRv1OMvXsRI1VbdcKh5DeMOtqWP3vP7T2KHGJD1SQkNvv1ZxroKuVQyBJWBshm0PxgZfzaDxkWOToiRQo3B00K1w4H7Si')

const router = useRouter()
const addresses = ref([])
const selectedAddressId = ref(null)
const selectedBillingAddressId = ref(null)
const loadingAddresses = ref(true)
const loadingPay = ref(false)
const error = ref('')
const clientSecret = ref(null)
const cardComplete = ref(false)
const step = ref(1)
let elements = null
let cardElement = null

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }
  try {
    const res = await axios.get('/api/addresses', {
      headers: { Authorization: `Bearer ${token}` }
    })
    addresses.value = res.data.addresses
    const def = addresses.value.find(a => a.isDefault)
    if (def) selectedAddressId.value = def.id
    const defBilling = addresses.value.find(a => a.isDefaultBilling)
    if (defBilling) selectedBillingAddressId.value = defBilling.id
  } catch (e) {
    error.value = "Impossible de charger les adresses."
  } finally {
    loadingAddresses.value = false
  }
})

const goToStep2 = async () => {
  step.value = 2
  await nextTick()
  const stripe = await stripePromise
  elements = stripe.elements()
  cardElement = elements.create('card')
  cardElement.on('change', (event) => {
    cardComplete.value = event.complete
    if (event.error) {
      error.value = event.error.message
    } else {
      error.value = ''
    }
  })
  cardElement.mount('#card-element')
}

const confirmPayment = async () => {
  loadingPay.value = true
  error.value = ''
  try {
    const res = await axios.post('/api/cart/payment-intent', {
      shippingAddressId: selectedAddressId.value,
      billingAddressId: selectedBillingAddressId.value
    }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    clientSecret.value = res.data.clientSecret

    const stripe = await stripePromise
    const { error: stripeError, paymentIntent } = await stripe.confirmCardPayment(clientSecret.value, {
      payment_method: {
        card: cardElement,
      }
    })
    if (stripeError) {
      error.value = stripeError.message
      return
    }
    if (!paymentIntent || paymentIntent.status !== 'succeeded') {
      error.value = "Le paiement n'a pas pu être confirmé. Veuillez réessayer."
      return
    }

    await axios.post('/api/cart/confirm-order', {
      shippingAddressId: selectedAddressId.value,
      billingAddressId: selectedBillingAddressId.value,
      paymentIntentId: paymentIntent.id
    }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })

    router.push('/orders?success=1')
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors du paiement ou de la commande."
  } finally {
    loadingPay.value = false
  }
}
</script>

<style scoped>
.loader, .loader-btn {
  border: 4px solid #e5e7eb;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  animation: spin 1s linear infinite;
  display: inline-block;
}
.loader-btn {
  width: 18px;
  height: 18px;
  margin-right: 8px;
  border-width: 3px;
}
@keyframes spin {
  from { transform: rotate(0deg);}
  to { transform: rotate(360deg);}
}
</style>
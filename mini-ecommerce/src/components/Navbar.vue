<template>
  <nav class="bg-white text-gray-800 shadow-md relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">
        <div class="flex-shrink-0 text-lg font-bold">
          Site E-commerce
        </div>

        <!-- Menu desktop -->
        <div class="hidden md:flex space-x-8 items-center">
          <router-link to="/" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="home" />
            <span>Accueil</span>
          </router-link>
          <router-link
              v-if="isAuthenticated && isAdmin"
              to="/backoffice"
              class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200"
          >
            <font-awesome-icon icon="cog" />
            <span>Back office</span>
          </router-link>
          <router-link to="/products" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="box-open" />
            <span>Nos produits</span>
          </router-link>
          <router-link to="/contact" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="envelope" />
            <span>Contact</span>
          </router-link>
          <router-link v-if="isAuthenticated" to="/account" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="user" />
            <span>Mon compte</span>
          </router-link>
          <router-link v-if="!isAuthenticated" to="/login" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="right-to-bracket" />
            <span>Connexion</span>
          </router-link>
          <router-link v-if="!isAuthenticated" to="/register" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="user-plus" />
            <span>Inscription</span>
          </router-link>
          <router-link v-if="isAuthenticated" to="/" @click.prevent="logout" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="right-from-bracket" />
            <span>Déconnexion</span>
          </router-link>
          <!-- Panier avec dropdown -->
          <div class="relative" @mouseenter="handleCartEnter" @mouseleave="handleCartLeave">
            <button class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded focus:outline-none">
              <font-awesome-icon icon="shopping-cart" />
              <span>Panier</span>
              <span v-if="cartItems.length" class="ml-1 bg-green-500 text-white text-xs rounded-full px-2 py-0.5">
                {{ cartItems.length }}
              </span>
            </button>
            <div
                v-if="showCart"
                class="absolute right-0 mt-0.5 w-80 max-h-[420px] bg-white border border-gray-200 rounded-xl shadow-2xl p-4 z-50 overflow-hidden"
                style="min-width: 320px;"
            >
              <p class="font-semibold mb-2 text-gray-800">Votre panier</p>
              <div v-if="loadingCart" class="flex justify-center py-2">
                <div class="loader border-4 border-blue-500 border-t-transparent rounded-full w-6 h-6 animate-spin"></div>
              </div>
              <div v-else-if="!cartItems.length" class="text-gray-500 text-center py-4">Aucun article dans le panier.</div>
              <ul v-else class="divide-y divide-gray-100 max-h-52 overflow-y-auto mb-2 pr-1">
                <li v-for="item in cartItems" :key="item.id" class="flex items-center py-2 gap-2">
                  <img :src="item.product.image" alt="" class="w-10 h-10 rounded object-cover border" />
                  <div class="flex-1 min-w-0">
                    <span class="font-medium text-sm truncate block">{{ item.product.name }}</span>
                    <div class="flex items-center gap-1 mt-1">
                      <div class="relative">
                        <input
                            type="number"
                            v-model.number="item.quantity"
                            min="1"
                            class="w-12 text-center border rounded text-xs py-0.5 pr-6"
                            @change="updateQuantity(item, item.quantity)"
                            :disabled="updatingItemId === item.id"
                        />
                        <span
                            v-if="updatingItemId === item.id"
                            class="absolute right-1 top-1/2 -translate-y-1/2 loader-mini"
                        ></span>
                      </div>
                      <button
                          @click="removeItem(item)"
                          class="ml-2 text-red-500 hover:underline text-xs relative"
                          :disabled="removingItemId === item.id"
                      >
                        <span
                            v-if="removingItemId === item.id"
                            class="loader-mini absolute left-0 top-1/2 -translate-y-1/2"
                            style="margin-right: 18px;"
                        ></span>
                        <span v-else>Supprimer</span>
                      </button>
                    </div>
                  </div>
                  <span class="text-xs font-semibold ml-2 whitespace-nowrap">{{ (item.product.price * item.quantity).toFixed(2) }} €</span>
                </li>
              </ul>
              <div class="mt-2 text-xs text-right border-t pt-2">
                <div class="flex justify-between mb-1">
                  <span>Total articles :</span>
                  <b>{{ totalQuantity }}</b>
                </div>
                <div class="flex justify-between">
                  <span>Total :</span>
                  <b>{{ totalPrice.toFixed(2) }} €</b>
                </div>
              </div>
              <router-link to="/cart" class="block mt-3 text-blue-600 underline text-xs text-right hover:text-blue-800">
                Voir le panier
              </router-link>
            </div>
          </div>
        </div>

        <!-- Hamburger mobile -->
        <div class="md:hidden">
          <button @click="isOpen = !isOpen" type="button" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-800">
            <svg
                v-if="!isOpen"
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg
                v-else
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Menu mobile -->
    <div v-if="isOpen" class="md:hidden px-2 pt-2 pb-3 space-y-1 bg-gray-50">
      <router-link to="/" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200" @click="isOpen = false">
        <font-awesome-icon icon="home" />
        <span>Accueil</span>
      </router-link>
      <router-link
          v-if="isAuthenticated && isAdmin"
          to="/backoffice"
          class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200"
          @click="isOpen = false"
      >
        <font-awesome-icon icon="cog" />
        <span>Back office</span>
      </router-link>
      <router-link to="/products" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200" @click="isOpen = false">
        <font-awesome-icon icon="box-open" />
        <span>Nos produits</span>
      </router-link>
      <router-link to="/contact" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200" @click="isOpen = false">
        <font-awesome-icon icon="envelope" />
        <span>Contact</span>
      </router-link>
      <router-link v-if="isAuthenticated" to="/account" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200" @click="isOpen = false">
        <font-awesome-icon icon="user" />
        <span>Mon compte</span>
      </router-link>
      <router-link v-if="!isAuthenticated" to="/login" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200" @click="isOpen = false">
        <font-awesome-icon icon="right-to-bracket" />
        <span>Connexion</span>
      </router-link>
      <router-link v-if="!isAuthenticated" to="/register" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200" @click="isOpen = false">
        <font-awesome-icon icon="user-plus" />
        <span>Inscription</span>
      </router-link>
      <router-link v-if="isAuthenticated" to="/" @click.prevent="logout; isOpen = false" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
        <font-awesome-icon icon="right-from-bracket" />
        <span>Déconnexion</span>
      </router-link>
      <router-link to="/cart" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200" @click="isOpen = false">
        <font-awesome-icon icon="shopping-cart" />
        <span>Panier</span>
        <span v-if="cartItems.length" class="ml-1 bg-green-500 text-white text-xs rounded-full px-2 py-0.5">
          {{ cartItems.length }}
        </span>
      </router-link>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const isOpen = ref(false)
const showCart = ref(false)
const isAuthenticated = ref(false)
const cartItems = ref([])
const loadingCart = ref(false)
const updatingItemId = ref(null)
const removingItemId = ref(null)
const router = useRouter()

const isAdmin = computed(() => {
  try {
    const roles = JSON.parse(localStorage.getItem('roles') || '[]')
    return roles.includes('ROLE_ADMIN')
  } catch {
    return false
  }
})

const checkAuth = () => {
  isAuthenticated.value = !!(localStorage.getItem('userName') && localStorage.getItem('userLastName'))
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('userName')
  localStorage.removeItem('userLastName')
  isAuthenticated.value = false
  cartItems.value = []
  window.dispatchEvent(new Event('auth-changed'))
  router.push('/login')
}

const fetchCart = async () => {
  loadingCart.value = true
  if (!isAuthenticated.value) {
    cartItems.value = []
    loadingCart.value = false
    return
  }
  try {
    const res = await axios.get('/api/cart', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    cartItems.value = res.data.items
  } catch {
    cartItems.value = []
  } finally {
    loadingCart.value = false
  }
}

const updateQuantity = async (item, newQty) => {
  if (newQty < 1) return
  updatingItemId.value = item.id
  try {
    await axios.put(`/api/cart/item/${item.id}`, { quantity: newQty }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    await fetchCart()
  } catch (e) {}
  finally {
    updatingItemId.value = null
  }
}

const removeItem = async (item) => {
  removingItemId.value = item.id
  try {
    await axios.delete(`/api/cart/item/${item.id}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    await fetchCart()
  } catch (e) {}
  finally {
    removingItemId.value = null
  }
}

const totalQuantity = computed(() =>
    cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
)
const totalPrice = computed(() =>
    cartItems.value.reduce((sum, item) => sum + item.product.price * item.quantity, 0)
)

const handleCartEnter = () => {
  showCart.value = true
  fetchCart()
}
const handleCartLeave = () => {
  showCart.value = false
}

onMounted(() => {
  checkAuth()
  window.addEventListener('auth-changed', checkAuth)
})
onUnmounted(() => {
  window.removeEventListener('auth-changed', checkAuth)
})
</script>

<style scoped>
.loader {
  border-left-color: #3b82f6;
  animation: spin 1s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg);}
}
::-webkit-scrollbar {
  width: 6px;
  background: #f3f4f6;
}
::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 4px;
}
.loader-mini {
  border: 2px solid #60a5fa;
  border-top: 2px solid transparent;
  border-radius: 50%;
  width: 14px;
  height: 14px;
  display: inline-block;
  animation: spin 1s linear infinite;
}
</style>
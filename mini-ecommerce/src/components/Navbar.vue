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

          <router-link to="/products" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="box-open" />
            <span>Nos produits</span>
          </router-link>

          <router-link to="/about" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="info-circle" />
            <span>À propos</span>
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

          <router-link v-if="isAuthenticated"  to="/"  @click.prevent="logout" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="right-from-bracket" />
            <span>Déconnexion</span>
          </router-link>



          <!-- Panier avec dropdown -->
          <div class="relative" @mouseenter="showCart = true" @mouseleave="showCart = false">
            <button class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded focus:outline-none">
              <font-awesome-icon icon="shopping-cart" />
              <span>Panier</span>
            </button>

            <div
                v-if="showCart"
                class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg p-4 z-50"
            >
              <p class="font-semibold mb-2">Votre panier</p>
              <p>Aucun article dans le panier.</p>
              <!-- Tu peux mettre ici une vraie liste d’articles -->
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
      <router-link to="/" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="home" />
        <span>Accueil</span>
      </router-link>
      <router-link to="/products" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="box-open" />
        <span>Nos produits</span>
      </router-link>
      <router-link to="/about" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="info-circle" />
        <span>À propos</span>
      </router-link>
      <router-link to="/contact" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="envelope" />
        <span>Contact</span>
      </router-link>
      <router-link v-if="isAuthenticated" to="/account" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="user" />
        <span>Mon compte</span>
      </router-link>
      <router-link v-if="!isAuthenticated" to="/login" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="right-to-bracket" />
        <span>Connexion</span>
      </router-link>
      <router-link v-if="!isAuthenticated" to="/register" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="user-plus" />
        <span>Inscription</span>
      </router-link>
      <router-link v-if="isAuthenticated"  to="/"  @click.prevent="logout" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
        <font-awesome-icon icon="right-from-bracket" />
        <span>Déconnexion</span>
      </router-link>
      <router-link to="/panier" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="shopping-cart" />
        <span>Panier</span>
      </router-link>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const isOpen = ref(false)
const showCart = ref(false)
const isAuthenticated = ref(false)
const router = useRouter()

const checkAuth = () => {
  isAuthenticated.value = !!localStorage.getItem('token')
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  isAuthenticated.value = false
  window.dispatchEvent(new Event('auth-changed'))
  router.push('/login')
}

onMounted(() => {
  checkAuth()
  window.addEventListener('auth-changed', checkAuth)
})
onUnmounted(() => {
  window.removeEventListener('auth-changed', checkAuth)
})
</script>
<template>
  <nav class="bg-white text-gray-800 shadow-md relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">
        <div class="flex-shrink-0 text-lg font-bold">
          Site E-commerce
        </div>

        <!-- Menu desktop -->
        <div class="hidden md:flex space-x-8 items-center">

          <a href="#" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="home" />
            <span>Accueil</span>
          </a>

          <a href="#" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="box-open" />
            <span>Nos produits</span>
          </a>

          <a href="#" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="info-circle" />
            <span>À propos</span>
          </a>

          <a href="#" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="envelope" />
            <span>Contact</span>
          </a>

          <a href="#" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
            <font-awesome-icon icon="user" />
            <span>Mon compte</span>
          </a>

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
      <a href="#" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="home" />
        <span>Accueil</span>
      </a>
      <a href="#" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="box-open" />
        <span>Nos produits</span>
      </a>
      <a href="#" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="info-circle" />
        <span>À propos</span>
      </a>
      <a href="#" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="envelope" />
        <span>Contact</span>
      </a>
      <a href="#" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="user" />
        <span>Mon compte</span>
      </a>
      <a href="#" class="flex items-center space-x-2 block px-3 py-2 rounded hover:bg-gray-200">
        <font-awesome-icon icon="shopping-cart" />
        <span>Panier</span>
      </a>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'

const isOpen = ref(false)
const showCart = ref(false)
</script>

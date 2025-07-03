<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from './components/Navbar.vue'
import BackofficeNavbar from './components/BackofficeNavbar.vue'

const route = useRoute()
const isBackoffice = computed(() => route.path.startsWith('/backoffice'))
</script>

<template>
  <div :class="isBackoffice ? 'flex min-h-screen bg-white' : 'bg-white min-h-screen'">
    <template v-if="isBackoffice">
      <BackofficeNavbar />
      <router-view />
    </template>
    <template v-else>
      <Navbar />
      <router-view />
    </template>
  </div>
</template>

<style>
body {
  margin: 0;
  background-color: white;
}
</style>
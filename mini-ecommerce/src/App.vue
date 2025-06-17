<script setup>
import Navbar from './components/Navbar.vue'
</script>

<template>
  <div class="bg-white min-h-screen">
    <Navbar />
    <router-view></router-view>
  </div>
</template>

<style>
body {
  margin: 0;
  background-color: white;
}
</style>
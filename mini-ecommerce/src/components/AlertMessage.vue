<template>
  <div v-if="message" :class="alertClass" class="mb-4">
    <div :class="contentClass" role="alert">
      {{ message }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
  message: String,
  type: {
    type: String,
    default: 'success'
  }
})

const alertClass = computed(() =>
    props.type === 'success' ? '' : ''
)

const contentClass = computed(() =>
    props.type === 'success'
        ? 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-center'
        : 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-center whitespace-pre-line'
)
</script>
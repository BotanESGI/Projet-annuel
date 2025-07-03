<template>
  <main class="flex-1 bg-gray-50 p-6">
    <h1 class="text-2xl font-bold text-gray-700">Bienvenue sur le panneau d'administration</h1>
    <p class="mt-2 text-gray-600">Utilisez le menu à gauche pour naviguer entre les différentes sections.</p>

    <div v-if="loading" class="flex justify-center items-center h-64">
      <span class="loader"></span>
    </div>

    <!-- Graphe -->
    <div v-else class="bg-white p-4 shadow-md rounded-lg mt-6">
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Évolution des Commandes</h2>
      <canvas ref="ordersChart" class="w-full h-48"></canvas>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Chart from 'chart.js/auto'

const orders = ref([])
const loading = ref(true)
const ordersChart = ref(null)

const fetchOrders = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await fetch('/api/orders', {
      headers: { Authorization: `Bearer ${token}` }
    })
    if (res.ok) {
      const data = await res.json()
      orders.value = data.map(order => ({
        date: order.date,
        amount: order.total
      }))
    }
  } catch (e) {
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchOrders()
  if (orders.value.length && ordersChart.value) {
    const ctx = ordersChart.value.getContext('2d')
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: orders.value.map(o => o.date),
        datasets: [{
          label: 'Montant des Commandes',
          data: orders.value.map(o => o.amount),
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderWidth: 2,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        }
      }
    })
  }
})
</script>

<style scoped>
.loader {
  border: 6px solid #e5e7eb;
  border-top: 6px solid #3b82f6;
  border-radius: 50%;
  width: 48px;
  height: 48px;
  animation: spin 1s linear infinite;
  display: inline-block;
}
@keyframes spin {
  to { transform: rotate(360deg);}
}
</style>
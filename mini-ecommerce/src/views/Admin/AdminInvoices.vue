<template>
  <div class="p-8 min-h-screen text-black">
    <h1 class="text-3xl font-bold mb-6 text-black">Gestion des factures (CRUD)</h1>
    <button @click="openForm()" class="mb-4 px-4 py-2 bg-blue-600 text-white rounded">Ajouter une facture</button>
    <div v-if="loading" class="flex justify-center items-center h-40">
      <span class="loader"></span>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white rounded shadow text-black">
        <thead>
        <tr>
          <th class="py-3 px-4 text-left">ID</th>
          <th class="py-3 px-4 text-left">Montant</th>
          <th class="py-3 px-4 text-left">Utilisateur</th>
          <th class="py-3 px-4 text-left">Commande</th>
          <th class="py-3 px-4 text-left">PDF</th>
          <th class="py-3 px-4 text-left">ID Paiement</th>
          <th class="py-3 px-4 text-left">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="invoice in invoices" :key="invoice.id" class="text-black">
          <td class="py-2 px-4">{{ invoice.id }}</td>
          <td class="py-2 px-4">{{ invoice.totalAmount }} €</td>
          <td class="py-2 px-4">
            <span v-if="invoice.user">{{ invoice.user.name }} {{ invoice.user.lastname }}</span>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4">
            <span v-if="invoice.order">#{{ invoice.order.id }} ({{ invoice.order.total }} €)</span>
            <span v-else class="text-gray-400 italic">-</span>
          </td>
          <td class="py-2 px-4">
            <button
                v-if="invoice.pdfPath"
                @click="downloadInvoicePdf(invoice)"
                class="text-blue-600 underline hover:text-blue-800"
                type="button"
            >
              Télécharger le PDF
            </button>
            <span v-else class="text-gray-400 italic">Aucun</span>
          </td>
          <td class="py-2 px-4">{{ invoice.paymentId }}</td>
          <td class="py-2 px-4 flex gap-2">
            <button
                @click="openForm(invoice)"
                class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-black rounded"
            >Modifier</button>
            <button
                @click="deleteInvoice(invoice.id)"
                :disabled="deleteLoadingId === invoice.id"
                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded"
            >
              <span v-if="deleteLoadingId === invoice.id" class="loader-xs"></span>
              <span v-else>Supprimer</span>
            </button>
          </td>
        </tr>
        <tr v-if="invoices.length === 0">
          <td colspan="7" class="text-center py-6 text-gray-400">Aucune facture trouvée.</td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Formulaire d'ajout/modification -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative text-black">
        <h2 class="text-2xl font-bold mb-4 text-black">{{ editingInvoice ? 'Modifier' : 'Ajouter' }} une facture</h2>
        <form @submit="handleSubmit" class="text-black">
          <div class="mb-4">
            <label class="block mb-1">Montant total</label>
            <input v-model="form.totalAmount" required type="number" step="0.01" class="w-full border rounded px-3 py-2" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">Utilisateur</label>
            <select v-model="form.user" required class="w-full border rounded px-3 py-2">
              <option disabled value="">-- Choisir --</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }} {{ user.lastname }} ({{ user.email }})
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Commande (optionnel)</label>
            <select v-model="form.order" class="w-full border rounded px-3 py-2">
              <option value="">-- Choisir --</option>
              <option v-for="order in orders" :key="order.id" :value="order.id">
                #{{ order.id }} ({{ order.total }} €)
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Chemin du PDF</label>
            <input v-model="form.pdfPath" required class="w-full border rounded px-3 py-2" />
          </div>
          <div class="mb-4">
            <label class="block mb-1">ID Paiement</label>
            <input v-model="form.paymentId" required class="w-full border rounded px-3 py-2" />
          </div>
          <div v-if="formErrors.length" class="mb-4">
            <ul class="bg-red-100 text-red-700 rounded px-4 py-2">
              <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
            </ul>
          </div>
          <div class="flex justify-end gap-2 mt-6">
            <button type="button" @click="cancelForm" class="px-4 py-2 bg-gray-300 rounded">Annuler</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded" :disabled="formLoading">
              <span v-if="formLoading" class="loader-xs mr-2"></span>
              {{ editingInvoice ? 'Enregistrer' : 'Ajouter' }}
            </button>
          </div>
        </form>
        <button
            @click="cancelForm"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl"
            title="Fermer"
        >×</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const invoices = ref([])
const users = ref([])
const orders = ref([])
const showForm = ref(false)
const editingInvoice = ref(null)
const formLoading = ref(false)
const formErrors = ref([])
const loading = ref(false)
const deleteLoadingId = ref(null)

const form = ref({
  totalAmount: '',
  user: null,
  order: null,
  pdfPath: '',
  paymentId: ''
})

const fetchInvoices = async () => {
  loading.value = true
  const token = localStorage.getItem('token')
  try {
    const res = await axios.get('/api/admin/invoices', {
      headers: { Authorization: `Bearer ${token}` }
    })
    invoices.value = res.data || []
  } finally {
    loading.value = false
  }
}

const fetchUsers = async () => {
  const token = localStorage.getItem('token')
  const res = await axios.get('/api/users', {
    headers: { Authorization: `Bearer ${token}` }
  })
  users.value = res.data['hydra:member'] || []
}

const fetchOrders = async () => {
  const token = localStorage.getItem('token')
  const res = await axios.get('/api/orders', {
    headers: { Authorization: `Bearer ${token}` }
  })
  orders.value = res.data || []
}

const openForm = (invoice = null) => {
  showForm.value = true
  editingInvoice.value = invoice
  if (invoice) {
    Object.assign(form.value, {
      totalAmount: invoice.totalAmount,
      user: invoice.user?.id || null,
      order: invoice.order?.id || null,
      pdfPath: invoice.pdfPath || '',
      paymentId: invoice.paymentId || ''
    })
  } else {
    Object.assign(form.value, {
      totalAmount: '',
      user: null,
      order: null,
      pdfPath: '',
      paymentId: ''
    })
  }
  formErrors.value = []
}

const cancelForm = () => {
  showForm.value = false
  editingInvoice.value = null
  formErrors.value = []
}

const handleSubmit = async (e) => {
  e.preventDefault()
  formLoading.value = true
  formErrors.value = []

  if (
      !form.value.totalAmount ||
      !form.value.user ||
      !form.value.pdfPath ||
      !form.value.paymentId
  ) {
    formErrors.value = ['Tous les champs sont obligatoires.']
    formLoading.value = false
    return
  }

  const token = localStorage.getItem('token')
  let payload = {
    totalAmount: form.value.totalAmount,
    user: form.value.user,
    pdfPath: form.value.pdfPath,
    paymentId: form.value.paymentId
  }
  if (!editingInvoice.value && form.value.order) {
    payload.order = form.value.order
  }
  try {
    if (editingInvoice.value) {
      await axios.put(`/api/admin/invoices_update/${editingInvoice.value.id}`, payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    } else {
      await axios.post('/api/admin/invoices_create', payload, {
        headers: { Authorization: `Bearer ${token}` }
      })
    }
    showForm.value = false
    editingInvoice.value = null
    await fetchInvoices()
  } catch (err) {
    if (err.response && err.response.data && err.response.data.violations) {
      formErrors.value = err.response.data.violations
    } else if (err.response && err.response.data && err.response.data['hydra:description']) {
      formErrors.value = [err.response.data['hydra:description']]
    } else {
      formErrors.value = ['Erreur lors de la sauvegarde de la facture.']
    }
  } finally {
    formLoading.value = false
  }
}

const deleteInvoice = async (id) => {
  if (confirm('Supprimer cette facture ?')) {
    deleteLoadingId.value = id
    const token = localStorage.getItem('token')
    try {
      await axios.delete(`/api/admin/invoices_delete/${id}`, {
        headers: { Authorization: `Bearer ${token}` }
      })
      await fetchInvoices()
    } finally {
      deleteLoadingId.value = null
    }
  }
}

// Téléchargement du PDF via Axios (blob)
const downloadInvoicePdf = async (invoice) => {
  try {
    const token = localStorage.getItem('token')
    // Si pdfPath est une URL API, utilise-la, sinon adapte l’URL
    let url = invoice.pdfPath
    if (!/^https?:\/\//.test(url) && !url.startsWith('/api/')) {
      url = `/api/invoices/${invoice.id}/download`
    }
    const response = await axios.get(url, {
      headers: { Authorization: `Bearer ${token}` },
      responseType: 'blob'
    })
    const blobUrl = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = blobUrl
    link.setAttribute('download', `facture-${invoice.id}.pdf`)
    document.body.appendChild(link)
    link.click()
    window.URL.revokeObjectURL(blobUrl)
    document.body.removeChild(link)
  } catch (e) {
    alert('Erreur lors du téléchargement du PDF')
    console.error(e)
  }
}

onMounted(() => {
  fetchInvoices()
  fetchUsers()
  fetchOrders()
})
</script>

<style scoped>
.loader {
  border: 4px solid #e5e7eb;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  animation: spin 1s linear infinite;
  display: inline-block;
}
.loader-xs {
  border: 2px solid #e5e7eb;
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  width: 16px;
  height: 16px;
  animation: spin 1s linear infinite;
  display: inline-block;
  vertical-align: middle;
}
@keyframes spin {
  to { transform: rotate(360deg);}
}
</style>
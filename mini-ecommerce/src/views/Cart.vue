<template>
  <AlertMessage :message="cartMessage" :type="cartMessageType" />
  <div v-if="loading" class="flex flex-col items-center justify-center py-16">
    <div class="loader mb-4"></div>
    <p class="text-gray-800 text-lg font-medium">Chargement du panier...</p>
  </div>
  <div v-else-if="error" class="alert-error">{{ error }}</div>
  <div v-else class="cart-container">
    <h2 class="cart-title">Votre panier</h2>
    <div v-if="cartItems.length === 0" class="cart-empty">
      <img src="https://cdn-icons-png.flaticon.com/512/2038/2038854.png" alt="Panier vide" class="cart-empty-img" />
      <p>Votre panier est vide.</p>
    </div>
    <div v-else>
      <div v-for="item in cartItems" :key="item.id" class="cart-item">
        <img :src="item.product.image" :alt="`Image de ${item.product.name}`" class="cart-item-img">
        <div class="cart-item-info">
          <h3 class="cart-item-title">{{ item.product.name }}</h3>
          <p class="cart-item-desc">{{ item.product.description }}</p>
          <div class="cart-item-actions">
            <span class="cart-item-price">{{ formatPrice(item.product.price) }} €</span>
            <span class="cart-item-qty-label">Quantité :</span>
            <input type="number" v-model.number="item.quantity" min="1" class="cart-item-qty" @change="updateQuantity(item)">
            <button @click="removeItem(item)" class="cart-item-remove">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon-trash" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Supprimer
            </button>
          </div>
        </div>
        <div class="cart-item-total">
          {{ formatPrice(item.product.price * item.quantity) }} €
        </div>
      </div>
      <div class="cart-summary-row-outer">
        <button @click="clearCart" :disabled="cartItems.length === 0 || clearingCart" class="cart-clear-btn">
          <span v-if="clearingCart" class="loader-btn"></span>
          <svg xmlns="http://www.w3.org/2000/svg" class="icon-clear" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Vider le panier
        </button>
        <div class="cart-summary">
          <div class="cart-summary-box">
            <div>
              <div class="cart-summary-row">
                <span>Total articles :</span>
                <span>{{ totalQuantity }}</span>
              </div>
              <div class="cart-summary-row">
                <span>Total :</span>
                <span class="cart-summary-total">{{ formatPrice(totalPrice) }} €</span>
              </div>
            </div>
            <button @click="checkout" :disabled="cartItems.length === 0 || checkingOut" class="cart-checkout-btn">
              <span v-if="checkingOut" class="loader-btn"></span>
              Passer la commande
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import AlertMessage from '@/components/AlertMessage.vue'

const cartItems = ref([])
const loading = ref(true)
const error = ref(null)
const cartMessage = ref('')
const cartMessageType = ref('success')
const checkingOut = ref(false)
const clearingCart = ref(false)
const router = useRouter()

const fetchCart = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get('/api/cart', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    cartItems.value = response.data.items
  } catch (err) {
    error.value = 'Impossible de charger le panier. Veuillez réessayer plus tard.'
  } finally {
    loading.value = false
  }
}

const updateQuantity = async (item) => {
  if (item.quantity < 1) item.quantity = 1
  try {
    await axios.put(`/api/cart/item/${item.id}`, {
      quantity: item.quantity
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    cartMessage.value = 'Quantité mise à jour'
    cartMessageType.value = 'success'
    await fetchCart()
  } catch (err) {
    cartMessage.value = 'Erreur lors de la mise à jour de la quantité'
    cartMessageType.value = 'error'
  }
}

const removeItem = async (item) => {
  try {
    await axios.delete(`/api/cart/item/${item.id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    cartMessage.value = 'Produit retiré du panier'
    cartMessageType.value = 'success'
    await fetchCart()
  } catch (err) {
    cartMessage.value = 'Erreur lors de la suppression du produit'
    cartMessageType.value = 'error'
  }
}

const clearCart = async () => {
  clearingCart.value = true
  cartMessage.value = ''
  try {
    await axios.delete('/api/cart', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    cartMessage.value = 'Panier vidé'
    cartMessageType.value = 'success'
    await fetchCart()
  } catch (err) {
    cartMessage.value = 'Erreur lors du vidage du panier'
    cartMessageType.value = 'error'
  } finally {
    clearingCart.value = false
  }
}

const checkout = async () => {
  checkingOut.value = true
  cartMessage.value = ''
  try {
    await axios.post('/api/cart/checkout', {}, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    cartMessage.value = 'Commande passée avec succès'
    cartMessageType.value = 'success'
    setTimeout(() => {
      router.push('/orders')
    }, 1500)
  } catch (err) {
    cartMessage.value = 'Erreur lors de la validation de la commande'
    cartMessageType.value = 'error'
  } finally {
    checkingOut.value = false
  }
}

const totalPrice = computed(() =>
    cartItems.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0)
)
const totalQuantity = computed(() =>
    cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
)

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2).replace('.', ',')
}

onMounted(fetchCart)
</script>

<style scoped>
.cart-container {
  max-width: 900px;
  margin: 32px auto;
  background: #f9fafb;
  border-radius: 18px;
  box-shadow: 0 4px 32px 0 rgba(0,0,0,0.07);
  padding: 32px 24px;
}
.cart-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 28px;
  color: #22223b;
}
.cart-empty {
  text-align: center;
  color: #6b7280;
  padding: 32px 0;
}
.cart-empty-img {
  width: 80px;
  margin-bottom: 12px;
  opacity: 0.7;
}
.cart-item {
  display: flex;
  align-items: center;
  border-bottom: 2px solid #e5e7eb;
  padding: 18px 0;
  transition: background 0.2s;
  background: #fff;
  position: relative;
}
.cart-item:not(:last-child)::after {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  bottom: -1px;
  height: 2px;
  background: linear-gradient(90deg, #e0e7ef 0%, #c7d2fe 100%);
  opacity: 0.7;
  z-index: 1;
}
.cart-item:hover {
  background: #f3f4f6;
  box-shadow: 0 2px 8px 0 rgba(37,99,235,0.04);
}
.cart-item-img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 10px;
  margin-right: 20px;
  box-shadow: 0 2px 8px 0 rgba(0,0,0,0.06);
}
.cart-item-info {
  flex: 1;
}
.cart-item-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #22223b;
  margin-bottom: 2px;
}
.cart-item-desc {
  color: #6b7280;
  font-size: 0.97rem;
  margin-bottom: 6px;
}
.cart-item-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}
.cart-item-price {
  color: #22223b;
  font-weight: 500;
}
.cart-item-qty-label {
  margin-left: 12px;
  color: #22223b;
}
.cart-item-qty {
  width: 54px;
  padding: 4px 8px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
  margin-left: 4px;
  color: #22223b;
  background: #f3f4f6;
  transition: border 0.2s;
}
.cart-item-qty:focus {
  border: 1.5px solid #2563eb;
  outline: none;
}
.cart-item-remove {
  margin-left: 16px;
  color: #ef4444;
  background: none;
  border: none;
  font-weight: 500;
  cursor: pointer;
  border-radius: 6px;
  padding: 4px 10px;
  transition: background 0.2s, color 0.2s;
  display: flex;
  align-items: center;
  gap: 4px;
}
.cart-item-remove:hover {
  background: #fee2e2;
  color: #b91c1c;
}
.icon-trash {
  width: 18px;
  height: 18px;
  margin-right: 2px;
}
.cart-item-total {
  font-size: 1.1rem;
  font-weight: 700;
  color: #22223b;
  margin-left: 32px;
  min-width: 90px;
  text-align: right;
}
.cart-summary-row-outer {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-top: 32px;
  gap: 24px;
}
.cart-summary {
  display: flex;
  justify-content: flex-end;
  flex: 1;
}
.cart-summary-box {
  background: #f3f4f6;
  border-radius: 14px;
  padding: 28px 32px;
  min-width: 320px;
  box-shadow: 0 2px 8px 0 rgba(0,0,0,0.04);
}
.cart-summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  font-size: 1.08rem;
  color: #22223b;
}
.cart-summary-total {
  font-weight: 700;
  font-size: 1.25rem;
  color: #2563eb;
}
.cart-checkout-btn {
  width: 100%;
  margin-top: 18px;
  background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 12px 0;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 2px 8px 0 rgba(34,197,94,0.08);
  transition: background 0.2s, box-shadow 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}
.cart-checkout-btn:hover:enabled {
  background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
}
.cart-checkout-btn:disabled {
  background: #bbf7d0;
  color: #6ee7b7;
  cursor: not-allowed;
}
.cart-clear-btn {
  background: #fff;
  color: #ef4444;
  border: 1.5px solid #ef4444;
  border-radius: 8px;
  padding: 8px 18px 8px 10px;
  font-weight: 500;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, border 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-left: 0;
  margin-top: 0;
}
.cart-clear-btn:hover:enabled {
  background: #fee2e2;
  color: #b91c1c;
  border-color: #b91c1c;
}
.cart-clear-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.icon-clear {
  width: 18px;
  height: 18px;
  margin-right: 6px;
  margin-left: -4px;
  flex-shrink: 0;
}
.alert-error {
  background: #fee2e2;
  color: #b91c1c;
  padding: 18px;
  border-radius: 10px;
  text-align: center;
  font-weight: 500;
  margin: 32px 0;
}
.loader, .loader-btn {
  border: 4px solid #60a5fa;
  border-top: 4px solid transparent;
  border-radius: 50%;
  width: 28px;
  height: 28px;
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
@media (max-width: 700px) {
  .cart-container, .cart-content {
    padding: 12px 4px;
  }
  .cart-summary-row-outer {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }
  .cart-summary-box {
    min-width: unset;
    padding: 18px 8px;
  }
  .cart-item-img {
    width: 60px;
    height: 60px;
    margin-right: 10px;
  }
  .cart-item-total {
    margin-left: 10px;
    min-width: 60px;
    font-size: 1rem;
  }
  .icon-clear {
    width: 16px;
    height: 16px;
    margin-right: 4px;
    margin-left: -2px;
  }
}
</style>
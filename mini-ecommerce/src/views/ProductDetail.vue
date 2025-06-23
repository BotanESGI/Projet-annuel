<template>
  <div v-if="loading" class="flex flex-col items-center justify-center py-12">
    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
    <p class="text-gray-900 text-lg">Chargement des détails du produit...</p>
  </div>

  <div v-else-if="error" class="text-center text-red-600 bg-red-50 p-4 rounded-lg">{{ error }}</div>

  <div v-else class="text-black">
    <!-- Fil d'Ariane -->
    <nav class="max-w-6xl mx-auto p-4 bg-white shadow-md rounded-lg flex flex-col md:flex-row mb-4">
      <ol class="flex items-center space-x-2 text-sm">
        <li>
          <router-link to="/" class="text-blue-600 hover:underline">Accueil</router-link>
        </li>
        <li>
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 24 24">
            <path d="M9 5l7 7-7 7" />
          </svg>
        </li>
        <li>
          <router-link to="/products" class="text-blue-600 hover:underline">Produits</router-link>
        </li>
        <li v-if="sourceCategory && sourceCategory.id">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 24 24">
            <path d="M9 5l7 7-7 7" />
          </svg>
        </li>
        <li v-if="sourceCategory && sourceCategory.id">
          <router-link :to="`/products?category=${sourceCategory.id}`" class="text-blue-600 hover:underline">
            {{ sourceCategory.name }}
          </router-link>
        </li>
        <li>
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 24 24">
            <path d="M9 5l7 7-7 7" />
          </svg>
        </li>
        <li class="text-black">{{ product.name }}</li>
      </ol>
    </nav>

    <!-- Informations du produit -->
    <div class="max-w-6xl mx-auto p-6 bg-white shadow-md rounded-lg flex flex-col md:flex-row">
      <div class="relative md:w-1/2">
        <span class="absolute top-2 left-2 bg-gray-800 text-white text-sm font-medium px-2 py-1 rounded-lg z-10 border border-white">
          {{ productType }}
        </span>
        <img :src="product.image" :alt="`Image de ${product.name}`" class="w-full h-auto max-h-80 object-cover rounded-lg mb-6">
      </div>
      <div class="md:w-1/2 md:pl-6">
        <div class="mb-2">
          <div class="flex flex-wrap gap-2">
            <span v-if="product.tags && product.tags.length > 0"
                  v-for="tag in product.tags"
                  :key="tag.id"
                  class="text-sm font-medium px-3 py-1 rounded-full"
                  :style="{ backgroundColor: tag.color, color: tag.color === '#FFFFFF' ? '#000000' : '#FFFFFF' }">
              {{ tag.name }}
            </span>
            <span v-else class="text-black italic">Aucun tag disponible pour ce produit.</span>
          </div>
        </div>
        <h2 class="text-3xl font-bold mb-2 text-black">{{ product.name }}</h2>
        <div class="mb-1">
          <p class="text-black">{{ product.description }}</p>
        </div>
        <div class="text-black mb-4">
          <p>Produit ajouté le : {{ formatDate(product.createdAt) }}</p>
        </div>

        <p class="text-lg font-semibold text-black mb-4">{{ formatPrice(product.price) }} €</p>

        <!-- Quantité et bouton ajouter au panier -->
        <div class="flex items-center mb-4">
          <input type="number" v-model="quantity" min="1" class="border rounded-lg p-2 w-16 mr-2 text-black">
          <button @click="addToCart" class="bg-green-500 text-white rounded-lg px-4 py-2 hover:bg-green-600">
            Ajouter au panier
          </button>
        </div>
      </div>
    </div>

    <!-- Caractéristiques du produit -->
    <div class="max-w-6xl mx-auto p-6 mt-10 bg-white shadow-md rounded-lg">
      <h2 class="text-xl font-semibold pb-3 text-black">Caractéristiques du produit</h2>
      <ul class="list-disc list-inside text-black">
        <template v-if="isPhysicalProduct">
          <li v-for="(value, key) in product.characteristics" :key="key">
            {{ formatKey(key) }} : {{ value }}
          </li>
        </template>
        <template v-else>
          <li>Taille du fichier : {{ product.filesize }} Mo</li>
          <li>Type de fichier : {{ product.filetype }}</li>
        </template>
      </ul>
    </div>

    <!-- Avis produits -->
    <div class="max-w-6xl mx-auto p-6 mt-10 bg-white shadow-md rounded-lg">
      <h2 class="text-xl font-semibold pb-3 text-black">Avis clients</h2>
      <div v-if="loadingReviews" class="flex justify-center py-4">
        <div class="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <div v-else-if="errorReviews" class="text-center text-red-600 bg-red-50 p-4 rounded-lg">
        {{ errorReviews }}
      </div>

      <div v-else-if="reviews.length === 0" class="text-center text-gray-600 p-4">
        Aucun avis pour ce produit pour le moment.
      </div>

      <div v-else>
        <div v-for="review in reviews" :key="review.id" class="border-b border-gray-200 py-4">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center">
              <div class="flex text-yellow-400 mr-2">
                <span v-for="i in 5" :key="i" class="text-lg">
                  <span v-if="i <= review.rating">★</span>
                  <span v-else>☆</span>
                </span>
              </div>
              <span class="font-medium text-black">
              {{ review.user ? (review.user.name ? `${review.user.name} ${review.user.lastname || ''}` : 'Utilisateur') : 'Utilisateur anonyme' }}
              </span>
            </div>
            <span class="text-sm text-gray-500">{{ formatDate(review.datePublication) }}</span>
          </div>
          <p class="text-black">{{ review.content }}</p>
        </div>
      </div>

      <div v-if="isAuthenticated" class="mt-6 pt-4 border-t border-gray-200">
        <h3 class="font-medium text-lg mb-3 text-black">Ajouter un avis</h3>
        <textarea
            v-model="newReview.content"
            class="w-full p-3 border rounded-lg mb-3 text-black"
            rows="3"
            placeholder="Partagez votre expérience avec ce produit..."
        ></textarea>

        <div class="flex items-center mb-4">
          <span class="mr-3 text-black">Note :</span>
          <div class="flex text-gray-400">
            <button
                v-for="i in 5"
                :key="i"
                @click="newReview.rating = i"
                class="text-2xl focus:outline-none"
                :class="{ 'text-yellow-400': i <= newReview.rating }"
            >
              ★
            </button>
          </div>
        </div>

        <button
            @click="submitReview"
            class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600"
            :disabled="submittingReview"
        >
          <span v-if="submittingReview">Envoi en cours...</span>
          <span v-else>Soumettre mon avis</span>
        </button>
      </div>

      <div v-else class="mt-6 p-4 bg-gray-50 rounded-lg text-center">
        <p class="text-black">Connectez-vous pour laisser un avis sur ce produit.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

export default {
  name: 'ProductDetail',
  setup() {
    const route = useRoute();
    const sourceCategory = ref({});
    const productId = computed(() => route.params.id);
    const product = ref({});
    const loading = ref(true);
    const error = ref(null);
    const quantity = ref(1);

    const reviews = ref([]);
    const loadingReviews = ref(true);
    const errorReviews = ref(null);
    const newReview = ref({
      content: '',
      rating: 0
    });
    const submittingReview = ref(false);

    const fetchProductData = async () => {
      loading.value = true;
      error.value = null;
      try {
        const response = await axios.get(`/api/products/${productId.value}`);
        product.value = response.data;

        if (!sourceCategory.value.id && product.value.defaultCategory) {
          sourceCategory.value = product.value.defaultCategory;
        }
      } catch (err) {
        console.error('Erreur lors du chargement des données:', err);
        error.value = 'Impossible de charger les détails du produit. Veuillez réessayer plus tard.';
      } finally {
        loading.value = false;
      }
    };

    const fetchReviews = async () => {
      loadingReviews.value = true;
      errorReviews.value = null;
      try {
        const response = await axios.get(`/api/reviews?product=${productId.value}`);
        reviews.value = response.data['hydra:member'];
      } catch (err) {
        console.error('Erreur lors du chargement des avis:', err);
        errorReviews.value = 'Impossible de charger les avis. Veuillez réessayer plus tard.';
      } finally {
        loadingReviews.value = false;
      }
    };

    const submitReview = async () => {
      if (!newReview.value.content || newReview.value.rating === 0) {
        alert('Veuillez saisir un commentaire et une note');
        return;
      }

      submittingReview.value = true;
      try {
        await axios.post('/api/reviews', {
          content: newReview.value.content,
          rating: newReview.value.rating,
          product: `/api/products/${productId.value}`
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });

        newReview.value.content = '';
        newReview.value.rating = 0;

        await fetchReviews();

        alert('Votre avis a été soumis et sera visible après modération. Merci !');
      } catch (err) {
        console.error('Erreur lors de la soumission de l\'avis:', err);
        alert('Erreur lors de la soumission de l\'avis. Veuillez réessayer.');
      } finally {
        submittingReview.value = false;
      }
    };

    onMounted(async () => {
      try {
        if (route.query.category) {
          const sourceCategoryId = parseInt(route.query.category);
          const response = await axios.get(`/api/categories/${sourceCategoryId}`);
          sourceCategory.value = response.data;
        }
      } catch (err) {
        console.error("Erreur lors de la récupération de la catégorie source:", err);
      } finally {
        await fetchProductData();
        await fetchReviews();
      }
    });

    const productType = computed(() => {
      if (!product.value) return '';
      if (product.value['@type']?.includes('PhysicalProduct')) return 'Produit physique';
      if (product.value['@type']?.includes('DigitalProduct')) return 'Produit digital';
      return 'Type de produit inconnu';
    });

    const isPhysicalProduct = computed(() => {
      return product.value['@type']?.includes('PhysicalProduct');
    });

    const isAuthenticated = computed(() => {
      return !!localStorage.getItem('token');
    });

    const isAdmin = computed(() => {
      const roles = localStorage.getItem('roles');
      return roles && roles.includes('ROLE_ADMIN');
    });

    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('fr-FR');
    };

    const formatPrice = (price) => {
      return parseFloat(price).toFixed(2).replace('.', ',');
    };

    const formatKey = (key) => {
      return key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
    };

    const addToCart = async () => {
      if (!isAuthenticated.value) {
        alert('Veuillez vous connecter pour ajouter des produits au panier');
        return;
      }

      try {
        await axios.post(`${import.meta.env.VITE_API_URL}/api/cart/add`, {
          productId: productId.value,
          quantity: quantity.value
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        alert('Produit ajouté au panier avec succès');
      } catch (err) {
        console.error('Erreur lors de l\'ajout au panier:', err);
        alert('Erreur lors de l\'ajout au panier');
      }
    };

    return {
      product,
      loading,
      error,
      quantity,
      productId,
      productType,
      isPhysicalProduct,
      isAuthenticated,
      isAdmin,
      formatDate,
      formatPrice,
      formatKey,
      addToCart,
      sourceCategory,
      reviews,
      loadingReviews,
      errorReviews,
      newReview,
      submittingReview,
      fetchReviews,
      submitReview
    };
  }
}
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
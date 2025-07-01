<template>
  <AlertMessage :message="cartMessage" :type="cartMessageType" />
  <div v-if="loading" class="flex flex-col items-center justify-center py-12">
    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
    <p class="text-gray-900 text-lg">Chargement des détails du produit...</p>
  </div>
  <div v-else-if="error" class="text-center text-red-600 bg-red-50 p-4 rounded-lg">{{ error }}</div>
  <div v-else class="text-black">
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
          <div class="flex items-center mb-2 min-h-[28px]">
            <template v-if="loadingReviews">
              <span class="w-6 h-6 border-2 border-yellow-400 border-t-transparent rounded-full animate-spin mr-2"></span>
              <span class="text-gray-500 text-sm">Chargement de la note...</span>
            </template>
            <template v-else>
              <span v-for="i in 5" :key="i" class="text-xl">
                <span :class="i <= Math.round(averageRating) ? 'text-yellow-400' : 'text-gray-300'">★</span>
              </span>
                <span class="ml-2 text-sm text-gray-600" v-if="reviews.length > 0">
                {{ averageRating.toFixed(1) }} / 5 ({{ reviews.filter(r => r.status === 'VALIDATED').length }} avis)
                </span>
              <span class="ml-2 text-sm text-gray-600" v-else>
                Pas encore d'avis
              </span>
            </template>
          </div>
        </div>
        <p class="text-lg font-semibold text-black mb-4">{{ formatPrice(product.price) }} €</p>
        <div class="flex items-center mb-4">
          <input type="number" v-model="quantity" min="1" class="border rounded-lg p-2 w-16 mr-2 text-black">
          <button @click="addToCart" :disabled="addingToCart" class="bg-green-500 text-white rounded-lg px-4 py-2 hover:bg-green-600 flex items-center">
            <span v-if="addingToCart" class="animate-spin inline-block mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
            Ajouter au panier
          </button>
        </div>
      </div>
    </div>
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
        <div v-for="review in displayedReviews" :key="review.id" class="flex gap-4 items-start border-b border-gray-100 py-6 group hover:bg-gray-50 transition">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-200 to-blue-400 flex items-center justify-center text-xl font-bold text-white shadow">
              <span v-if="review.user && review.user.name">{{ review.user.name.charAt(0).toUpperCase() }}</span>
              <span v-else>U</span>
            </div>
          </div>
          <div class="flex-1">
            <div class="flex items-center justify-between gap-2 mb-1">
              <div class="flex items-center gap-2">
                <span class="font-medium text-black">
                  {{ review.user ? (review.user.name ? `${review.user.name} ${review.user.lastname || ''}` : 'Utilisateur') : 'Utilisateur anonyme' }}
                </span>
                <span v-if="isMyReview(review) && review.status === 'PENDING'" class="px-2 py-0.5 text-xs bg-blue-100 text-blue-400 rounded-full border border-blue-300">
                  Votre avis (en attente de validation)
                </span>
                <span v-else-if="isMyReview(review) && review.status === 'REJECTED'" class="px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded-full border border-red-300">
                  Rejeté
                </span>
              </div>
              <div v-if="isMyReview(review)" class="flex gap-2 ml-4">
                <button v-if="!editingReview || editingReview.id !== review.id" @click="editReview(review)" class="flex items-center text-xs text-blue-600 hover:underline">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2h6" />
                  </svg>
                  Modifier
                </button>
                <button @click="deleteReview(review)" class="flex items-center text-xs text-red-600 hover:underline" :disabled="deletingReviewId === review.id">
                  <span v-if="deletingReviewId === review.id" class="animate-spin inline-block mr-1 w-4 h-4 border-2 border-red-600 border-t-transparent rounded-full"></span>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Supprimer
                </button>
              </div>
            </div>
            <div class="flex items-center gap-1 mb-1">
              <span v-for="i in 5" :key="i" class="text-lg">
                <span v-if="i <= review.rating" class="text-yellow-400">★</span>
                <span v-else class="text-gray-300">★</span>
              </span>
              <span class="text-sm text-gray-500 ml-2">{{ formatDate(review.datePublication) }}</span>
            </div>
            <div v-if="editingReview && editingReview.id === review.id">
              <textarea v-model="editReviewContent" rows="3" class="w-full border rounded-lg p-2 mb-2 text-black"></textarea>
              <div class="flex items-center gap-1 mb-2">
                <span v-for="i in 5" :key="i" class="cursor-pointer text-2xl"
                      @click="editReviewRating = i">
                  <span :class="[editReviewRating >= i ? 'text-yellow-400' : 'text-gray-300']">★</span>
                </span>
                <span class="text-sm text-gray-500 ml-2">{{ editReviewRating ? `${editReviewRating} / 5` : '' }}</span>
              </div>
              <button @click="submitEditReview" :disabled="submittingReview" class="bg-blue-500 text-white rounded-lg px-4 py-2 mr-2">
                <span v-if="submittingReview" class="animate-spin inline-block mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                Enregistrer
              </button>
              <button @click="cancelEditReview" class="bg-gray-300 text-black rounded-lg px-4 py-2">Annuler</button>
            </div>
            <div v-else>
              <p class="text-black leading-relaxed">{{ review.content }}</p>
            </div>
          </div>
        </div>
        <div v-if="reviews.length > displayLimit" class="mt-4 flex justify-center">
          <button
              @click="showAllReviews = !showAllReviews"
              class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors shadow"
          >
            {{ showAllReviews ? 'Voir moins' : `Voir plus (${reviews.length - displayLimit} avis supplémentaires)` }}
          </button>
        </div>
      </div>
      <div class="mt-6">
        <template v-if="isAuthenticated && hasPurchasedProduct">
          <div class="pt-6 border-t border-gray-200">
            <h3 class="font-medium text-lg mb-3 text-black">Ajouter un avis</h3>
            <div v-if="reviewMessage" :class="reviewMessageClass">
              {{ reviewMessage }}
            </div>
            <div class="flex flex-col md:flex-row gap-4 items-start">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-xl font-bold text-white shadow">
                  <span>{{ currentUserInitial }}</span>
                </div>
              </div>
              <div class="flex-1">
                <textarea
                    v-model="newReview.content"
                    rows="3"
                    class="w-full border rounded-lg p-2 mb-2 text-black"
                    placeholder="Votre avis (au moins 10 caractères)"
                    :disabled="submittingReview"
                ></textarea>
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-1">
                    <span v-for="i in 5" :key="i" class="cursor-pointer text-2xl"
                          @mouseenter="hoverRating = i"
                          @mouseleave="hoverRating = 0"
                          @click="newReview.rating = i">
                      <span :class="[(hoverRating || newReview.rating) >= i ? 'text-yellow-400' : 'text-gray-300']">★</span>
                    </span>
                  </div>
                  <span class="text-sm text-gray-500">{{ newReview.rating ? `${newReview.rating} / 5` : '' }}</span>
                </div>
                <button
                    @click="submitReview"
                    :disabled="submittingReview"
                    class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 disabled:opacity-50"
                >
                  <span v-if="submittingReview" class="animate-spin inline-block mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                  Poster mon avis
                </button>
              </div>
            </div>
          </div>
        </template>
        <template v-else-if="isAuthenticated && checkingPurchaseStatus">
          <div class="p-4 bg-gray-50 rounded-lg text-center">
            <div class="flex flex-col items-center justify-center py-4">
              <div class="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin mb-2"></div>
              <p class="text-gray-700">Vérification de votre statut d'achat...</p>
            </div>
          </div>
        </template>
        <template v-else-if="isAuthenticated && !hasPurchasedProduct">
          <div class="p-4 bg-yellow-50 rounded-lg text-center">
            <p class="text-black">Vous devez acheter ce produit avant de pouvoir laisser un avis.</p>
          </div>
        </template>
        <template v-else>
          <div class="p-4 bg-gray-50 rounded-lg text-center">
            <p class="text-black">Connectez-vous pour laisser un avis sur ce produit.</p>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import AlertMessage from '@/components/AlertMessage.vue'
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'ProductDetail',
  components: { AlertMessage },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const sourceCategory = ref({});
    const productId = computed(() => route.params.id);
    const product = ref({});
    const loading = ref(true);
    const error = ref(null);
    const quantity = ref(1);
    const addingToCart = ref(false);

    const cartMessage = ref('');

    const reviewMessageClass = computed(() => {
      if (reviewMessage.value && reviewMessage.value.toLowerCase().includes('succès')) {
        return 'mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200';
      }
      return 'mb-4 p-3 rounded bg-red-50 text-red-700 border border-red-200';
    });

    const reviews = ref([]);
    const loadingReviews = ref(true);
    const errorReviews = ref(null);
    const newReview = ref({
      content: '',
      rating: 0
    });
    const submittingReview = ref(false);
    const displayLimit = ref(5);
    const showAllReviews = ref(false);
    const hasPurchasedProduct = ref(false);
    const checkingPurchaseStatus = ref(true);
    const cartMessageType = ref('success')
    const reviewMessage = ref('');

    const editingReview = ref(null);
    const editReviewContent = ref('');
    const editReviewRating = ref(0);

    const deletingReviewId = ref(null);

    const displayedReviews = computed(() => {
      const currentUserId = Number(localStorage.getItem('userId'));
      return showAllReviews.value
          ? reviews.value.filter(
              review =>
                  review.status === 'VALIDATED' ||
                  (review.status === 'PENDING' && review.user && review.user.id === currentUserId) ||
                  (review.status === 'REJECTED' && review.user && review.user.id === currentUserId)
          )
          : reviews.value
              .filter(
                  review =>
                      review.status === 'VALIDATED' ||
                      (review.status === 'PENDING' && review.user && review.user.id === currentUserId) ||
                      (review.status === 'REJECTED' && review.user && review.user.id === currentUserId)
              )
              .slice(0, displayLimit.value);
    });

    function isMyReview(review) {
      const currentUserId = Number(localStorage.getItem('userId'));
      return review.user && review.user.id === currentUserId;
    }

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
        errorReviews.value = 'Impossible de charger les avis. Veuillez réessayer plus tard.';
      } finally {
        loadingReviews.value = false;
      }
    };

    const checkPurchaseStatus = async () => {
      if (!isAuthenticated.value) {
        checkingPurchaseStatus.value = false;
        return;
      }
      checkingPurchaseStatus.value = true;
      try {
        const response = await axios.get(`/api/check-purchase/${productId.value}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        hasPurchasedProduct.value = response.data.hasPurchased;
      } catch (err) {
        hasPurchasedProduct.value = false;
      } finally {
        checkingPurchaseStatus.value = false;
      }
    };

    const submitReview = async () => {
      reviewMessage.value = '';
      if (!newReview.value.content || newReview.value.content.length < 10) {
        reviewMessage.value = "Veuillez saisir un commentaire d'au moins 10 caractères";
        return;
      }
      if (newReview.value.rating === 0) {
        reviewMessage.value = 'Veuillez attribuer une note';
        return;
      }
      submittingReview.value = true;
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          reviewMessage.value = "Erreur d'authentification. Veuillez vous reconnecter.";
          return;
        }
        await axios.post('/api/reviews', {
          content: newReview.value.content,
          rating: newReview.value.rating,
          product: `/api/products/${productId.value}`
        }, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        newReview.value.content = '';
        newReview.value.rating = 0;
        await fetchReviews();
        reviewMessage.value = 'Votre avis a été soumis et sera visible après modération. Merci !';
      } catch (err) {
        if (err.response && err.response.data) {
          if (err.response.data.violations) {
            const violations = err.response.data.violations.map(v => `${v.property}: ${v.message}`).join('\n');
            reviewMessage.value = `Erreurs de validation:\n${violations}`;
          } else {
            reviewMessage.value = `Erreur: ${err.response.status} - ${err.response.data['hydra:description'] || JSON.stringify(err.response.data)}`;
          }
        } else {
          reviewMessage.value = 'Erreur lors de la soumission de l\'avis. Veuillez réessayer.';
        }
      } finally {
        submittingReview.value = false;
      }
    };

    const editReview = (review) => {
      editingReview.value = review;
      editReviewContent.value = review.content;
      editReviewRating.value = review.rating;
    };

    const cancelEditReview = () => {
      editingReview.value = null;
      editReviewContent.value = '';
      editReviewRating.value = 0;
    };

    const submitEditReview = async () => {
      reviewMessage.value = '';
      if (!editReviewContent.value || editReviewContent.value.length < 10) {
        reviewMessage.value = "Veuillez saisir un commentaire d'au moins 10 caractères";
        return;
      }
      if (editReviewRating.value === 0) {
        reviewMessage.value = 'Veuillez attribuer une note';
        return;
      }
      submittingReview.value = true;
      try {
        await axios.put(`/api/reviews/${editingReview.value.id}`, {
          content: editReviewContent.value,
          rating: editReviewRating.value
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        await fetchReviews();
        reviewMessage.value = 'Votre avis a été modifié avec succès et sera visible après modération.';
        cancelEditReview();
      } catch (err) {
        reviewMessage.value = 'Erreur lors de la modification de l\'avis.';
      } finally {
        submittingReview.value = false;
      }
    };

    const deleteReview = async (review) => {
      reviewMessage.value = '';
      deletingReviewId.value = review.id;
      try {
        await axios.delete(`/api/reviews/${review.id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        await fetchReviews();
        reviewMessage.value = 'Avis supprimé avec succès';
      } catch (err) {
        reviewMessage.value = 'Erreur lors de la suppression de l\'avis';
      } finally {
        deletingReviewId.value = null;
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
      } finally {
        await fetchProductData();
        await fetchReviews();
        await checkPurchaseStatus();

        const id = productId.value;
        if (id) {
          const recentlyViewedProducts = JSON.parse(localStorage.getItem('recentlyViewedProducts') || '[]');
          const index = recentlyViewedProducts.indexOf(Number(id));
          if (index > -1) {
            recentlyViewedProducts.splice(index, 1);
          }
          recentlyViewedProducts.unshift(Number(id));
          if (recentlyViewedProducts.length > 10) {
            recentlyViewedProducts.pop();
          }
          localStorage.setItem('recentlyViewedProducts', JSON.stringify(recentlyViewedProducts));
        }
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

    const averageRating = computed(() => {
      if (!reviews.value.length) return 0;
      const sum = reviews.value
          .filter(r => r.status === 'VALIDATED')
          .reduce((acc, r) => acc + r.rating, 0);
      const count = reviews.value.filter(r => r.status === 'VALIDATED').length;
      return count ? (sum / count) : 0;
    });

    const formatKey = (key) => {
      return key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
    };

    const addToCart = async () => {
      if (!isAuthenticated.value) {
        cartMessage.value = 'Veuillez vous connecter pour ajouter des produits au panier'
        cartMessageType.value = 'error'
        return
      }
      addingToCart.value = true
      cartMessage.value = ''
      try {
        await axios.post(`/api/cart/add`, {
          productId: productId.value,
          quantity: quantity.value
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        cartMessage.value = 'Produit ajouté au panier avec succès'
        cartMessageType.value = 'success'
        setTimeout(() => {
          router.push('/cart')
        }, 4000)
      } catch (err) {
        cartMessage.value = "Erreur lors de l'ajout au panier"
        cartMessageType.value = 'error'
      } finally {
        addingToCart.value = false
      }
    }

    const hoverRating = ref(0);

    const currentUserInitial = computed(() => {
      const name = localStorage.getItem('userName');
      if (name && name.length > 0) {
        return name.charAt(0).toUpperCase();
      }
      return 'U';
    });

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
      addingToCart,
      sourceCategory,
      reviews,
      loadingReviews,
      errorReviews,
      newReview,
      submittingReview,
      fetchReviews,
      submitReview,
      hoverRating,
      displayLimit,
      showAllReviews,
      displayedReviews,
      hasPurchasedProduct,
      checkingPurchaseStatus,
      isMyReview,
      currentUserInitial,
      editReview,
      deleteReview,
      reviewMessage,
      reviewMessageClass,
      editingReview,
      editReviewContent,
      editReviewRating,
      cancelEditReview,
      submitEditReview,
      deletingReviewId,
      cartMessage,
      cartMessageType,
      averageRating
    };
  }
}
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg);}
  to { transform: rotate(360deg);}
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.group:hover .shadow {
  box-shadow: 0 4px 16px 0 rgba(0,0,0,0.08);
}
</style>
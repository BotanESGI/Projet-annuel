<template>
  <aside class="w-64 min-h-screen bg-gray-800 text-white flex flex-col py-8 px-4">
    <div class="text-2xl font-bold mb-8 flex items-center gap-2">
      <font-awesome-icon icon="cog" />
      Back Office
    </div>
    <nav class="flex flex-col gap-2">
      <router-link to="/backoffice" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="tachometer-alt" />
        <span>Tableau de bord</span>
      </router-link>
      <router-link to="/admin/products" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="box-open" />
        <span>Produits</span>
      </router-link>
      <router-link to="/admin/orders" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="shopping-cart" />
        <span>Commandes</span>
      </router-link>
      <router-link to="/admin/invoices" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="file-invoice" />
        <span>Factures</span>
      </router-link>
      <router-link to="/admin/cart" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="shopping-basket" />
        <span>Panier</span>
      </router-link>
      <router-link to="/admin/users" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="users" />
        <span>Utilisateurs</span>
      </router-link>
      <router-link to="/admin/addresses" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="map-marker-alt" />
        <span>Adresses</span>
      </router-link>
      <router-link to="/admin/categories" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="tags" />
        <span>Catégories</span>
      </router-link>
      <router-link to="/admin/reviews" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="star" />
        <span>Avis</span>
      </router-link>
      <router-link to="/admin/tags" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="tag" />
        <span>Tags</span>
      </router-link>
      <router-link to="/admin/" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700">
        <font-awesome-icon icon="arrow-left" />
        <span>Retour au site</span>
      </router-link>
      <button
          v-if="isAuthenticated"
          @click="logout"
          class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 text-left w-full"
      >
        <font-awesome-icon icon="right-from-bracket" />
        <span>Déconnexion</span>
      </button>
    </nav>
  </aside>
</template>
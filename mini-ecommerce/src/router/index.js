import { createRouter, createWebHistory } from 'vue-router'
import Product from '../views/Product.vue'
import Contact from '../views/Contact.vue'
import Account from '../views/Account.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Profile from '../views/Profile.vue'
import Addresses from '../views/Addresses.vue'
import Orders from '../views/Orders.vue'
import ForgetPassword from '../views/ForgetPassword.vue'
import ResetPassword from '../views/ResetPassword.vue'
import AccountVerification from '../views/AccountVerification.vue'
import ProductDetail from '../views/ProductDetail.vue'
import Cart from '../views/Cart.vue'
import Checkout from '../views/Checkout.vue'
import OrderConfirmation from '../views/OrderConfirmation.vue'
import OrderDetail from '../views/OrderDetail.vue'
import Home from '../views/Home.vue'
import Backoffice from '../views/Backoffice.vue'
import AdminProducts from '../views/Admin/AdminProducts.vue'
import AdminAddresses from '../views/Admin/AdminAddresses.vue'
import AdminTags from '../views/Admin/AdminTags.vue'
import AdminCategory from "@/views/Admin/AdminCategory.vue";
import AdminReviews from "@/views/Admin/AdminReviews.vue";
import AdminUsers from "@/views/Admin/AdminUsers.vue";
import AdminInvoices from "@/views/Admin/AdminInvoices.vue";
import AdminCarts from "@/views/Admin/AdminCarts.vue";
import AdminOrders from "@/views/Admin/AdminOrders.vue";

const routes = [
    {
        path: '/',
        name: 'Accueil',
        component: Home,
        meta: {
            title: 'Accueil - Mini Ecommerce',
            description: 'Bienvenue sur Mini Ecommerce, votre boutique en ligne.',
            keywords: ['bytmeuh', 'accueil', 'mini ecommerce', 'boutique en ligne', 'ecommerce']
        }
    },
    {
        path: '/products',
        name: 'Produits',
        component: Product,
        meta: {
            title: 'Nos produits - Mini Ecommerce',
            description: 'Découvrez tous nos produits disponibles à la vente.',
            keywords: ['bytmeuh', 'produits', 'catalogue', 'ecommerce', 'boutique']
        }
    },
    {
        path: '/contact',
        name: 'Contact',
        component: Contact,
        meta: {
            title: 'Contactez-nous - Mini Ecommerce',
            description: 'Contactez notre équipe pour toute question ou demande.',
            keywords: ['bytmeuh', 'contact', 'support', 'service client', 'aide']
        }
    },
    {
        path: '/account',
        name: 'Compte',
        component: Account,
        meta: {
            requiresAuth: true,
            title: 'Mon compte - Mini Ecommerce',
            description: 'Gérez votre compte et vos informations personnelles.',
            keywords: ['bytmeuh', 'compte', 'profil', 'utilisateur', 'paramètres']
        }
    },
    {
        path: '/profile',
        name: 'Profile',
        component: Profile,
        meta: {
            requiresAuth: true,
            title: 'Profil - Mini Ecommerce',
            description: 'Consultez et modifiez votre profil utilisateur.',
            keywords: ['bytmeuh', 'profil', 'compte', 'utilisateur', 'ecommerce']
        }
    },
    {
        path: '/addresses',
        name: 'Adresses',
        component: Addresses,
        meta: {
            requiresAuth: true,
            title: 'Mes adresses - Mini Ecommerce',
            description: 'Gérez vos adresses de livraison et de facturation.',
            keywords: ['bytmeuh', 'adresses', 'livraison', 'facturation', 'profil']
        }
    },
    {
        path: '/orders',
        name: 'Commandes',
        component: Orders,
        meta: {
            requiresAuth: true,
            title: 'Mes commandes - Mini Ecommerce',
            description: 'Consultez l\'historique de vos commandes.',
            keywords: ['bytmeuh', 'commandes', 'historique', 'achat', 'ecommerce']
        }
    },
    {
        path: '/login',
        name: 'Connexion',
        component: Login,
        meta: {
            guestOnly: true,
            title: 'Connexion - Mini Ecommerce',
            description: 'Connectez-vous à votre compte Mini Ecommerce.',
            keywords: ['bytmeuh', 'connexion', 'login', 'authentification', 'compte']
        }
    },
    {
        path: '/register',
        name: 'Inscription',
        component: Register,
        meta: {
            guestOnly: true,
            title: 'Inscription - Mini Ecommerce',
            description: 'Créez un compte pour profiter de tous nos services.',
            keywords: ['bytmeuh', 'inscription', 'compte', 'nouvel utilisateur', 'register']
        }
    },
    {
        path: '/forget-password',
        name: 'Mot de passe oublié',
        component: ForgetPassword,
        meta: {
            guestOnly: true,
            title: 'Mot de passe oublié - Mini Ecommerce',
            description: 'Réinitialisez votre mot de passe.',
            keywords: ['bytmeuh', 'mot de passe', 'réinitialisation', 'sécurité', 'authentification']
        }
    },
    {
        path: '/reset-password/:token',
        name: 'Réinitialisation du mot de passe',
        component: ResetPassword,
        props: true,
        meta: {
            guestOnly: true,
            title: 'Réinitialisation du mot de passe - Mini Ecommerce',
            description: 'Choisissez un nouveau mot de passe pour votre compte.',
            keywords: ['bytmeuh', 'reset password', 'sécurité', 'utilisateur', 'compte']
        }
    },
    {
        path: '/account-verification/:token',
        name: 'Vérification du compte',
        component: AccountVerification,
        props: true,
        meta: {
            guestOnly: true,
            title: 'Vérification du compte - Mini Ecommerce',
            description: 'Vérifiez votre compte pour l\'activer.',
            keywords: ['bytmeuh', 'vérification', 'email', 'activation', 'compte']
        }
    },
    {
        path: '/product/:id',
        name: 'Detail du produit',
        component: ProductDetail,
        props: true,
        meta: {
            title: 'Détail du produit - Mini Ecommerce',
            description: 'Découvrez les détails de ce produit.',
            keywords: ['bytmeuh', 'produit', 'fiche produit', 'description', 'achat']
        }
    },
    {
        path: '/cart',
        name: 'Panier',
        component: Cart,
        meta: {
            requiresAuth: true,
            title: 'Mon panier - Mini Ecommerce',
            description: 'Consultez et gérez les produits dans votre panier.',
            keywords: ['bytmeuh', 'panier', 'produits', 'commande', 'ecommerce']
        }
    },
    {
        path: '/checkout',
        name: 'Tunnel de commande',
        component: Checkout,
        meta: {
            requiresAuth: true,
            fromCartOnly: true,
            title: 'Commander - Mini Ecommerce',
            description: 'Finalisez votre commande en toute sécurité.',
            keywords: ['bytmeuh', 'checkout', 'paiement', 'commande', 'panier']
        }
    },
    {
        path: '/order/:id/confirmation',
        name: 'Confirmation de commande',
        component: OrderConfirmation,
        meta: {
            requiresAuth: true,
            title: 'Confirmation de commande - Mini Ecommerce',
            description: 'Votre commande a bien été prise en compte.',
            keywords: ['bytmeuh', 'confirmation', 'commande', 'paiement', 'réussite']
        }
    },
    {
        path: '/orders/:id',
        name: 'Détail de la commande',
        component: () => OrderDetail,
        meta: {
            requiresAuth: true,
            title: 'Détail de la commande - Mini Ecommerce',
            description: 'Consultez le détail de votre commande.',
            keywords: ['bytmeuh', 'commande', 'détail', 'facture', 'ecommerce']
        }
    },
    {
        path: '/backoffice',
        name: 'Back Office',
        component: Backoffice,
        meta: {
            requiresAuth: true,
            title: 'Back Office - Mini Ecommerce',
            description: 'Espace d\'administration du site.',
            keywords: ['bytmeuh', 'admin', 'backoffice', 'gestion', 'ecommerce']
        }
    },
    {
        path: '/backoffice/products',
        name: 'Admin Gestion des Produits',
        component: AdminProducts,
        meta: {
            requiresAuth: true,
            title: 'Admin - Produits - Mini Ecommerce',
            description: 'Gérez les produits du site.',
            keywords: ['bytmeuh', 'admin', 'produits', 'gestion', 'inventaire']
        }
    },
    {
        path: '/backoffice/addresses',
        name: 'Admin Gestion des Adresses',
        component: AdminAddresses,
        meta: {
            requiresAuth: true,
            title: 'Admin - Adresses - Mini Ecommerce',
            description: 'Gérez les adresses des utilisateurs.',
            keywords: ['bytmeuh', 'admin', 'adresses', 'utilisateurs', 'livraison']
        }
    },
    {
        path: '/backoffice/tags',
        name: 'Admin Gestion des Tags',
        component: AdminTags,
        meta: {
            requiresAuth: true,
            title: 'Admin - Tags - Mini Ecommerce',
            description: 'Gérez les tags des produits.',
            keywords: ['bytmeuh', 'admin', 'tags', 'produits', 'classification']
        }
    },
    {
        path: '/backoffice/category',
        name: 'Admin Gestion des Catégories',
        component: AdminCategory,
        meta: {
            requiresAuth: true,
            title: 'Admin - Catégories - Mini Ecommerce',
            description: 'Gérez les catégories de produits.',
            keywords: ['bytmeuh', 'admin', 'catégories', 'produits', 'gestion']
        }
    },
    {
        path: '/backoffice/reviews',
        name: 'Admin Gestion des Avis',
        component: AdminReviews,
        meta: {
            requiresAuth: true,
            title: 'Admin - Avis - Mini Ecommerce',
            description: 'Gérez les avis des clients.',
            keywords: ['bytmeuh', 'admin', 'avis', 'clients', 'feedback']
        }
    },
    {
        path: '/backoffice/users',
        name: 'Admin Gestion des Utilisateurs',
        component: AdminUsers,
        meta: {
            requiresAuth: true,
            title: 'Admin - Utilisateurs - Mini Ecommerce',
            description: 'Gérez les utilisateurs du site.',
            keywords: ['bytmeuh', 'admin', 'utilisateurs', 'gestion', 'comptes']
        }
    },
    {
        path: '/backoffice/invoices',
        name: 'Admin Gestion des Factures',
        component: AdminInvoices,
        meta: {
            requiresAuth: true,
            title: 'Admin - Factures - Mini Ecommerce',
            description: 'Gérez les factures des commandes.',
            keywords: ['bytmeuh', 'admin', 'factures', 'paiement', 'gestion']
        }
    },
    {
        path: '/backoffice/cart',
        name: 'Admin Gestion des paniers',
        component: AdminCarts,
        meta: {
            requiresAuth: true,
            title: 'Admin - Paniers - Mini Ecommerce',
            description: 'Gérez les paniers des utilisateurs.',
            keywords: ['bytmeuh', 'admin', 'paniers', 'utilisateurs', 'commande']
        }
    },
    {
        path: '/backoffice/orders',
        name: 'Admin Gestion des commandes',
        component: AdminOrders,
        meta: {
            requiresAuth: true,
            title: 'Admin - Commandes - Mini Ecommerce',
            description: 'Gérez les commandes du site.',
            keywords: ['bytmeuh', 'admin', 'commandes', 'gestion', 'ecommerce']
        }
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem('token')

    if (to.meta.guestOnly && isAuthenticated) {
        next({ path: '/' })
    }
    else if (to.meta.requiresAuth && !isAuthenticated) {
        next({ path: '/login' })
    }
    else if (to.meta.fromCartOnly && from.path !== '/cart') {
        next({ path: '/cart' })
    }
    else if (to.path === '/backoffice' && !JSON.parse(localStorage.getItem('roles') || '[]').includes('ROLE_ADMIN')) {
        next({ path: '/' })
    }
    else {
        next()
    }
})

export default router

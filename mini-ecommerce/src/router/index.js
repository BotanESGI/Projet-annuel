import { createRouter, createWebHistory } from 'vue-router'
import Product from '../views/Product.vue'
import About from '../views/About.vue'
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

const routes = [
    {
        path: '/products',
        name: 'Produits',
        component: Product
    },
    {
        path: '/about',
        name: 'A propos',
        component: About
    },
    {
        path: '/contact',
        name: 'Contact',
        component: Contact
    },
    {
        path: '/account',
        name: 'Compte',
        component: Account,
        meta: { requiresAuth: true }
    },
    {
        path: '/profile',
        name: 'Profile',
        component: Profile,
        meta: { requiresAuth: true }
    },
    {
        path: '/addresses',
        name: 'Adresses',
        component: Addresses,
        meta: { requiresAuth: true }
    },
    {
        path: '/orders',
        name: 'Commandes',
        component: Orders,
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'Connexion',
        component: Login,
        meta: { guestOnly: true }
    },
    {
        path: '/register',
        name: 'Inscription',
        component: Register,
        meta: { guestOnly: true }
    },
    {
        path: '/forget-password',
        name: 'Mot de passe oublié',
        component: ForgetPassword,
        meta: { guestOnly: true }
    },
    {
        path: '/reset-password/:token',
        name: 'Réinitialisation du mot de passe',
        component: ResetPassword,
        props: true,
        meta: { guestOnly: true }
    },
    {
        path: '/account-verification/:token',
        name: 'Vérification du compte',
        component: AccountVerification,
        props: true,
        meta: { guestOnly: true }
    },
    {
        path: '/product/:id',
        name: 'Detail du produit',
        component: ProductDetail,
        props: true
    },
    {
        path: '/cart',
        name: 'Panier',
        component: Cart,
        meta: { requiresAuth: true }
    },
    {
        path: '/checkout',
        name: 'Tunnel de commande',
        component: Checkout,
        meta: { requiresAuth: true, fromCartOnly: true }
    },
    {
        path: '/order/:id/confirmation',
        name: 'Confirmation de commande',
        component: OrderConfirmation,
        meta: { requiresAuth: true }
    },
    {
        path: '/orders/:id',
        name: 'Détail de la commande',
        component: () => OrderDetail,
        meta: { requiresAuth: true }
    },
    {
        path: '/',
        name: 'Accueil',
        component: () => Home,
    },
    {
        path: '/backoffice',
        name: 'Back Office',
        component: Backoffice,
        meta: { requiresAuth: true }
    },
    {
        path: '/backoffice/products',
        name: 'Admin Gestion des Produits',
        component: AdminProducts,
        meta: { requiresAuth: true }
    },
    {
        path: '/backoffice/addresses',
        name: 'Admin Gestion des Adresses',
        component: AdminAddresses,
        meta: { requiresAuth: true }
    },
    {
        path: '/backoffice/tags',
        name: 'Admin Gestion des Tags',
        component: AdminTags,
        meta: { requiresAuth: true }
    },
    {
        path: '/backoffice/category',
        name: 'Admin Gestion des Catégories',
        component: AdminCategory,
        meta: { requiresAuth: true }
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
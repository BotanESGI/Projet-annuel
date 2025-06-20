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

const routes = [
    {
        path: '/products',
        name: 'Produits',
        component: Product
    },
    {
        path: '/about',
        name: 'APropos',
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
    else {
        next()
    }
})

export default router
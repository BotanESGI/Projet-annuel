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
        name: 'Addresses',
        component: Addresses,
        meta: { requiresAuth: true }
    },
    {
        path: '/orders',
        name: 'Orders',
        component: Orders,
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guestOnly: true }
    },
    {
        path: '/register',
        name: 'Register',
        component: Register,
        meta: { guestOnly: true }
    }
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
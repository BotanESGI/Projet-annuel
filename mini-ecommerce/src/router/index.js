import { createRouter, createWebHistory } from 'vue-router'
import Product from '../views/Product.vue'
import About from '../views/About.vue'
import Contact from '../views/Contact.vue'
import Account from '../views/Account.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'

const routes = [
    {
        path: '/produits',
        name: 'Produits',
        component: Product
    },
    {
        path: '/a-propos',
        name: 'APropos',
        component: About
    },
    {
        path: '/contact',
        name: 'Contact',
        component: Contact
    },
    {
        path: '/compte',
        name: 'Compte',
        component: Account
    },
    {
        path: '/connexion',
        name: 'Login',
        component: Login
    },
    {
        path: '/inscription',
        name: 'Register',
        component: Register
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router

import { createRouter, createWebHistory } from 'vue-router'
import Product from '../views/Product.vue'
import About from '../views/About.vue'
import Contact from '../views/Contact.vue'
import Account from '../views/Account.vue'

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
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router

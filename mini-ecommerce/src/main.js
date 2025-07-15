import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {
    faHome,
    faBoxOpen,
    faInfoCircle,
    faEnvelope,
    faUser,
    faShoppingCart,
    faRightToBracket,
    faUserPlus,
    faRightFromBracket,
    faCog,
    faTachometerAlt,
    faFileInvoice,
    faShoppingBasket,
    faUsers,
    faMapMarkerAlt,
    faTags,
    faTag,
    faStar,
    faArrowLeft
} from '@fortawesome/free-solid-svg-icons'

library.add(
    faHome,
    faBoxOpen,
    faInfoCircle,
    faEnvelope,
    faUser,
    faShoppingCart,
    faRightToBracket,
    faUserPlus,
    faRightFromBracket,
    faCog,
    faTachometerAlt,
    faFileInvoice,
    faShoppingBasket,
    faUsers,
    faMapMarkerAlt,
    faTags,
    faTag,
    faStar,
    faArrowLeft
)

import router from './router'

import VueMatomo from 'vue-matomo'

const app = createApp(App)

app.use(router)

// Configuration de vue-matomo
app.use(VueMatomo, {
    host: import.meta.env.VITE_MATOMO_HOST,
    siteId: import.meta.env.VITE_MATOMO_SITEID,
    router,
    enableLinkTracking: true,
    requireConsent: false,
    trackInitialView: true,
})

app.component('font-awesome-icon', FontAwesomeIcon)

router.afterEach((to) => {
    if (to.meta && to.meta.title) {
        document.title = to.meta.title
    } else {
        document.title = 'Mini Ecommerce'
    }
    if (to.meta && to.meta.description) {
        let descriptionTag = document.querySelector('meta[name="description"]')
        if (!descriptionTag) {
            descriptionTag = document.createElement('meta')
            descriptionTag.setAttribute('name', 'description')
            document.head.appendChild(descriptionTag)
        }
        descriptionTag.setAttribute('content', to.meta.description)
    }
    if (to.meta && to.meta.keywords) {
        let keywordsTag = document.querySelector('meta[name="keywords"]')
        if (!keywordsTag) {
            keywordsTag = document.createElement('meta')
            keywordsTag.setAttribute('name', 'keywords')
            document.head.appendChild(keywordsTag)
        }
        keywordsTag.setAttribute('content', to.meta.keywords)
    }
})

app.mount('#app')
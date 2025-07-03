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

const app = createApp(App)

app.use(router)

app.component('font-awesome-icon', FontAwesomeIcon)

app.mount('#app')
import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faHome, faBoxOpen, faInfoCircle, faEnvelope, faUser, faShoppingCart, faRightToBracket, faUserPlus, faRightFromBracket } from '@fortawesome/free-solid-svg-icons'
library.add(faHome, faBoxOpen, faInfoCircle, faEnvelope, faUser, faShoppingCart, faRightToBracket, faUserPlus, faRightFromBracket)
import router from './router'

library.add(faHome, faBoxOpen, faInfoCircle, faEnvelope, faUser, faShoppingCart)

const app = createApp(App)

app.use(router)

app.component('font-awesome-icon', FontAwesomeIcon)

app.mount('#app')

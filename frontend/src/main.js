import { createApp } from 'vue'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'vue-loading-overlay/dist/css/index.css'
import "vue-toastification/dist/index.css";
import 'vue-image-zoomer/dist/style.css';
import './style.css'
import App from './App.vue' // import App component
import router from './router/index.js'  // import router
import {LoadingPlugin} from 'vue-loading-overlay'
import VueDOMPurifyHTML from 'vue-dompurify-html' 
import VueImageZoomer from 'vue-image-zoomer'  // ✅ After Pinia
import Toast from "vue-toastification";
import { createPinia } from 'pinia'  // import pinia
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'

const pinia = createPinia() // create pinia instance
pinia.use(piniaPluginPersistedstate) // use pinia plugin persisted state

// Configure axios (if needed)
import axios from 'axios'
axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

import { useAuthStore } from './stores/useAuthStore'

const app = createApp(App)
    app.use(router)
    app.use(VueDOMPurifyHTML) // use vue dompurify html
    app.use(VueImageZoomer) // use vue image zoomer
    app.use(LoadingPlugin) // use loading plugin for loading spinner
    app.use(Toast) // use toast
    app.use(pinia) // use pinia

    // Initialize axios headers from persisted state
    const authStore = useAuthStore()
    // if access_token is set, set it in the axios headers by default
    authStore.initializeAxiosHeaders()

    app.mount('#app') // mount app to the div with id app
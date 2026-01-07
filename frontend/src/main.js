import { createApp } from 'vue'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'vue-loading-overlay/dist/css/index.css'
import 'vue-image-zoomer/dist/style.css'
import './style.css'
import App from './App.vue' // import App component
import router from './router/index.js'  // import router
import {LoadingPlugin} from 'vue-loading-overlay'
import VueDOMPurifyHTML from 'vue-dompurify-html' 
import VueImageZoomer from 'vue-image-zoomer'  // ✅ After Pinia
import 'vue-image-zoomer/dist/style.css';
import { createPinia } from 'pinia'  // ✅ First

const pinia = createPinia()

// Configure axios (if needed)
import axios from 'axios'
axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// create app
const app = createApp(App)
app.use(VueDOMPurifyHTML)
// use vue image zoomer
app.use(VueImageZoomer)
// use loading plugin for loading spinner
app.use(LoadingPlugin)
// use router
app.use(router)
// use pinia
app.use(pinia)
// mount app to the div with id app
app.mount('#app') 
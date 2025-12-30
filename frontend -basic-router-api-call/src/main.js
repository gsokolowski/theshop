import { createApp } from 'vue'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap-icons/font/bootstrap-icons.css'
import './style.css'
import App from './App.vue'
import router from './router/index.js'  // import router
import { createPinia } from 'pinia'

const pinia = createPinia()

// Configure axios (if needed)
import axios from 'axios'
axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// create app
const app = createApp(App)
// use router
app.use(router)
// use pinia
app.use(pinia)
// mount app to the div with id app
app.mount('#app') 
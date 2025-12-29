import { createApp } from 'vue'
import 'bootstrap/dist/css/bootstrap.min.css'
import * as bootstrap from 'bootstrap'  // Fixed Bootstrap import
import 'bootstrap-icons/font/bootstrap-icons.css'
import './style.css'
import App from './App.vue'
import router from './router/index.js'  // import router

// Configure axios (if needed)
import axios from 'axios'
axios.defaults.baseURL = 'http://127.0.0.1:8000/api'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// create app
const app = createApp(App)

// use router
app.use(router)

// make bootstrap available globally
app.config.globalProperties.$bootstrap = bootstrap

app.mount('#app')
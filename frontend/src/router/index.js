// generate router for the frontend all components here
import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import Login from '../components/auth/Login.vue'
import Register from '../components/auth/Register.vue'
import About from '../components/About.vue'
import Product from '../components/products/Product.vue'
import Cart from '../components/cart/Cart.vue'

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/about', name: 'about', component: About },
  { path: '/product/:slug', name: 'product', component: Product },
  { path: '/cart', name: 'cart', component: Cart },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
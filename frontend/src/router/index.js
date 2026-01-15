// generate router for the frontend all components here
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'
import Home from '../components/Home.vue'
import Login from '../components/auth/Login.vue'
import Register from '../components/auth/Register.vue'
import About from '../components/About.vue'
import Product from '../components/products/Product.vue'
import Cart from '../components/cart/Cart.vue'
import Profile from '../components/profile/Profile.vue'
import Orders from '../components/profile/Orders.vue'
import Checkout from '../components/checkout/Checkout.vue'

function checkIfUserIsLoggedIn() {
  const authStore = useAuthStore()
  if( ! authStore.getIsUserLoggedIn) return '/login' // if user is not logged in, redirect to login page
}

function checkIfUserIsLoggedOut() {
  const authStore = useAuthStore()
  if(authStore.getIsUserLoggedIn) return '/'; // if user is logged in, redirect to home page
}

const routes = [
  { path: '/', name: 'home', component: Home},
  { path: '/login', name: 'login', component: Login, beforeEnter: checkIfUserIsLoggedOut },
  { path: '/register', name: 'register', component: Register, beforeEnter: checkIfUserIsLoggedOut },
  { path: '/profile', name: 'profile', component: Profile, beforeEnter: checkIfUserIsLoggedIn },
  { path: '/about', name: 'about', component: About },
  { path: '/product/:slug', name: 'product', component: Product },
  { path: '/cart', name: 'cart', component: Cart },
  { path: '/user/orders', name: 'orders', component: Orders, beforeEnter: checkIfUserIsLoggedIn },
  { path: '/checkout', name: 'checkout', component: Checkout, beforeEnter: checkIfUserIsLoggedIn },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
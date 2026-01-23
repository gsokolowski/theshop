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
import Checkout from '../components/checkout/Checkout.vue'
import SuccessPayment from '../components/payment/SuccessPayment.vue'
import UserOrders from '../components/profile/UserOrders.vue'

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
  { path: '/checkout', name: 'checkout', component: Checkout, beforeEnter: checkIfUserIsLoggedIn },
  { path: '/success/payment/:hash', name: 'successPayment', component: SuccessPayment, beforeEnter: checkIfUserIsLoggedIn },
  { path: '/user/orders', name: 'userOrders', component: UserOrders, beforeEnter: checkIfUserIsLoggedIn },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
<template>
    <div>
        <h1>My Orders</h1>
        
        <!-- Loading state -->
        <div v-if="authStore.isLoading" class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        
        <!-- No orders -->
        <div v-else-if="!orders || orders.length === 0" class="alert alert-info">
            You have no orders yet.
        </div>
        
        <!-- Orders list -->
        <div v-else>
            <div v-for="order in orders" :key="order.id" class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Order #{{ order.id }}</h5>
                    <p class="card-text">
                        <strong>Quantity:</strong> {{ order.qty }}<br>
                        <strong>Total:</strong> ${{ order.total }}<br>
                        <strong>Created:</strong> {{ order.created_at }}<br>
                        <strong>Status:</strong> {{ order.deliverd_at ? 'Delivered' : 'Pending' }}
                    </p>
                    <!-- src="http://127.0.0.1:8000/storage/images/products/1767489488_thumbnail_9.png.png" -->
                    <!-- Products in this order -->
                    <div v-if="order.products && order.products.length > 0">
                        <h6>Products:</h6>
                        <div class="row g-2">
                            <div v-for="product in order.products" :key="product.id" class="col-12 col-md-6">
                                <div class="d-flex align-items-center border rounded p-2">
                                    <img 
                                        :src="getThumbnailUrl(product.thumbnail)" 
                                        :alt="product.name" 
                                        class="img-fluid rounded me-3" 
                                        style="width: 80px; height: 80px; object-fit: cover;"
                                    >
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ product.name }}</div>
                                        <div class="text-muted">${{ product.price }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Coupon used -->
                    <div v-if="order.coupon">
                        <strong>Coupon:</strong> {{ order.coupon.code }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../../stores/useAuthStore'

const authStore = useAuthStore()

// Access orders reactively
const orders = computed(() => authStore.user?.orders || [])


// Helper function to construct thumbnail URL
const getThumbnailUrl = (thumbnail) => {
    if (!thumbnail) return ''
    
    // If thumbnail already starts with http, return as is
    if (thumbnail.startsWith('http://') || thumbnail.startsWith('https://')) {
        return thumbnail
    }
    
    // Otherwise, prefix with base URL
    // Remove leading slash if present
    const path = thumbnail.startsWith('/') ? thumbnail.slice(1) : thumbnail
    return `http://127.0.0.1:8000/storage/${path}`
}
</script>

<style scoped>
/* Your styles here */
</style>
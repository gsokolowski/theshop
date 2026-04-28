<template>
    <div class="row my-5">
        <ProfileSidebar />
        <div class="col-md-8">
            <Spinner :store="wishlistStore" />
            <div class="card-body" v-if="wishlistItems.length > 0">
                <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Sizes</th>
                            <th>Colors</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(item, index) in wishlistItems"
                            :key="item.id"
                        >
                            <td>{{ index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img 
                                        :src="item.product.thumbnail" 
                                        :alt="item.product.name"
                                        class="img-thumbnail me-2"
                                        style="width: 60px; height: 60px; object-fit: cover;"
                                    >
                                    <router-link 
                                        :to="{ name: 'product', params: { slug: item.product.slug } }"
                                        class="text-decoration-none text-dark"
                                    >
                                        {{ item.product.name }}
                                    </router-link>
                                </div>
                            </td>
                            <td>${{ item.product.price }}</td>
                            <td>
                                <span 
                                    :class="`badge ${item.product.status_badge.class === 'success' ? 'bg-success' : 'bg-warning'}`"
                                >
                                    {{ item.product.status_badge.label }}
                                </span>
                            </td>
                            <td>{{ item.product.category.name }}</td>
                            <td>{{ item.product.brand.name }}</td>
                            <td>
                                <span 
                                    v-for="size in item.product.sizes"
                                    :key="size.id"
                                    class="badge bg-light text-dark me-1"
                                >
                                    {{ size.name }}
                                </span>
                            </td>
                            <td>
                                <div 
                                    v-for="color in item.product.colors"
                                    :key="color.id"
                                    class="d-inline-block border border-light-subtle border-1 rounded me-1"
                                    :style="{
                                        backgroundColor: color.name,
                                        width: '25px',
                                        height: '25px'
                                    }"
                                    :title="color.name"
                                ></div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button 
                                        class="btn btn-sm btn-primary"
                                        @click="handleAddToCart(item)"
                                        :disabled="!item.product.status || wishlistStore.isLoading"
                                    >
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                    <button 
                                        class="btn btn-sm btn-danger"
                                        @click="handleDeleteItem(item.id)"
                                        :disabled="wishlistStore.isLoading"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
            <Alert v-else content="No wishlist items yet!" bgColor="primary" />
        </div>
        
        <!-- Add to Cart Modal -->
        <AddToCartModal 
            v-if="selectedProduct"
            :product="selectedProduct"
            :show="showModal"
            @close="handleCloseModal"
            @add-to-cart="handleAddToCartConfirm"
        />
    </div>
</template>

<script setup>
import { useWishlistStore } from '../../stores/useWishlistStore'
import { useCartStore } from '../../stores/useCartStore'
import ProfileSidebar from './ProfileSidebar.vue'
import Alert from '../layouts/Alert.vue'
import Spinner from '../common/Spinner.vue'
import AddToCartModal from '../wishlist/AddToCartModal.vue'
import { computed, onMounted, ref } from 'vue'
import { useToast } from 'vue-toastification'

const wishlistStore = useWishlistStore()
const cartStore = useCartStore()
const toast = useToast()

const wishlistItems = computed(() => wishlistStore.getWishlistItems)
const showModal = ref(false)
const selectedProduct = ref(null)
const selectedWishlistItemId = ref(null)

// Fetch wishlist on mount
onMounted(async () => {
    try {
        await wishlistStore.fetchWishlist()
    } catch (error) {
        console.error('Error loading wishlist:', error)
    }
})

// Handle delete item with confirmation
const handleDeleteItem = async (wishlistId) => {
    if (confirm('Are you sure you want to remove this item from your wishlist?')) {
        try {
            await wishlistStore.removeFromWishlist(wishlistId)
        } catch (error) {
            console.error('Error removing from wishlist:', error)
        }
    }
}

// Handle add to cart button click - open modal
const handleAddToCart = (item) => {
    if (!item.product.status) {
        toast.warning('Product is out of stock')
        return
    }
    selectedProduct.value = item.product
    selectedWishlistItemId.value = item.id
    showModal.value = true
}

// Handle modal close
const handleCloseModal = () => {
    showModal.value = false
    selectedProduct.value = null
    selectedWishlistItemId.value = null
}

// Handle add to cart confirmation from modal
const handleAddToCartConfirm = async (cartData) => {
    try {
        // Create cart item object
        const item = {
            reference: `${cartData.product.id}-${cartData.color.id}-${cartData.size.id}`,
            product: cartData.product,
            qty: cartData.qty,
            color: cartData.color,
            size: cartData.size
        }
        
        // Add to cart
        await cartStore.addToCart(item)
        
        // Remove product from wishlist after successfully adding to cart
        if (selectedWishlistItemId.value) {
            try {
                await wishlistStore.removeFromWishlist(selectedWishlistItemId.value)
            } catch (wishlistError) {
                console.error('Error removing from wishlist:', wishlistError)
                // Don't show error toast - cart was added successfully
            }
        }
        
        // Close modal
        handleCloseModal()
        
        toast.success('Item added to cart successfully')
    } catch (error) {
        console.error('Error adding to cart:', error)
    }
}
</script>

<style scoped>
</style>

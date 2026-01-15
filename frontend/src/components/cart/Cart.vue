<template>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body" v-if="cartTotalItems > 0"> <!-- display card body if cart has items -->
                    <h5 class="card-title">Cart</h5>
                    <table class="table table-bordered"> 
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Subtotal</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in cartItems" :key="item.reference">
                                <td>{{ index + 1 }}</td>
                                <td><img :src="item.product.thumbnail" :alt="item.product.name" class="img-fluid rounded" style="width: 100px; height: 100px;"></td>
                                <td>{{ item.product.name }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-caret-up"
                                        @click="cartStore.increaseQuantity(item)"
                                        :style="{cursor: 'pointer'}"
                                        ></i>
                                        <span class="mx-2">
                                            {{ item.qty }}
                                        </span>
                                        <i class="bi bi-caret-down"
                                            @click="cartStore.decreaseQuantity(item)"
                                            :style="{cursor: 'pointer'}"
                                        ></i>
                                    </div>
                                </td>
                                <td>{{ item.product.price }}</td>
                                <td>
                                    <div class="border border-light-subtle border-1 rounded" :style="{
                                        backgroundColor:item.color.name,
                                        width:'25px',
                                        height:'25px',
                                        }"></div>
                                </td>
                                <td>
                                    <span class="bg-light text-dark me-2 p-1 fw-bold">
                                        {{ item.size.name }}
                                    </span>
                                </td>
                                <td>${{ (item.product.price * item.qty).toFixed(2) }}</td>
                                <td>
                                    <button class="btn btn-light btn-sm" 
                                        @click="cartStore.removeItem(item)"
                                        title="Remove Item">
                                        <i class="fbi bi-cart-x"></i>
                                    </button>                                    
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-end">
                                    <span class="fw-bold">Total:</span> ${{ cartTotalPrice.toFixed(2) }}
                                </td>
                                <td class="text-end">
                                    <button 
                                    class="btn btn-primary" 
                                    @click="router.push('/checkout')"
                                    :disabled="cartTotalItems === 0"
                                    :class="{'disabled': cartTotalItems === 0}"
                                    >Checkout</button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-end">
                                    <button class="btn btn-light" v-if="cartTotalItems > 0" @click="router.push('/')">Continue Shopping</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info d-flex justify-content-between align-items-center" role="alert">
                        <span>Your cart is empty.</span>
                        <button class="btn btn-primary" @click="router.push('/')">Continue Shopping</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { useCartStore } from '../../stores/useCartStore'
    import { useRouter } from 'vue-router'
    import { computed } from 'vue'

    const cartStore = useCartStore()
    // define the computed properties
    const cartItems = computed(() => cartStore.cartItems)
    const cartTotalItems = computed(() => cartItems.value.length)
    const cartTotalPrice = computed(() => cartItems.value.reduce((total, item) => total + (item.product.price * item.qty), 0))

    const router = useRouter()

</script>

<style scoped>

</style>
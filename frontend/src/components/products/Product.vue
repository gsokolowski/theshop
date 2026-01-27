<template>
    <div class="row">
        <Spinner :store="productDetailsStore" />   
        <div class="text-start mb-2 mt-2">
            <button class="btn" @click="router.push('/')">
                <i class="bi bi-arrow-left"></i> Back
            </button>
        </div>
    </div>
    <div v-if="isLoading">
        <h1>Loading...</h1>
    </div>
    <div v-else-if="product && product.thumbnail && imagesReady">       
        <div class="row">
            <div class="col-6 mb-3">
                <!-- Thumbnail image -->
                <div class="mb-3 rounded">
                    <VueImageZoomer 
                        v-if="product.thumbnail && imagesReady" 
                        :key="product.slug"
                        :regular="product.thumbnail" 
                    />
                </div>
                <!-- Other images below thumbnail -->
                <div v-if="productImages && productImages.length > 0" class="row g-2 rounded">
                    <div 
                        v-for="productImage in productImages"
                        :key="productImage.id"
                        class="col-6"
                    >
                    <VueImageZoomer 
                        v-if="productImage.src && imagesReady"
                        img-class="img-fluid rounded"   
                        :regular="productImage.src" 
                        :key="productImage.id"
                    />
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card mb-2" style="max-width: 320px">
                    <div class="card-body">
                        <h5 class="card-title">{{ product.name }}</h5>
                        <p class="card-text">{{ product.description}}</p>
                        <p class="card-text fw-bold">Brand: {{ product.brand.name }}</p>
                        <p class="card-text fw-bold">Category: {{ product.category.name }}</p>
                        <div class="mb-2">
                            <span class="badge bg-success" v-if="productDetailsStore.product?.status">
                                In Stock
                            </span>
                            <span class="badge bg-warning" v-else>
                                Out Stock
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="h6 mb-0 mt-2">Select Color</span>
                        </div>
                        <div class="d-flex flex-wrap justify-content-start">
                            <div 
                                :class="`${data.chosenColor?.id === color.id ? 'border border-light-subtle shadow-sm border-2 rounded' : ''}  mb-1 me-1`" 
                                v-for="color in productDetailsStore.product?.colors"
                                :key="color.id"
                                :style="{
                                    backgroundColor:color.name,
                                    width:'30px',
                                    height:'30px',
                                    cursor:'pointer'
                                }"
                                @click="setChosenColor(color)"
                            >
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="h6 mb-0">Select Size</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="d-flex flex-wrap justify-content-start align-items-center my-3">
                            <button 
                                :class="`${data.chosenSize?.id === size.id ? 'btn btn-primary mb-3 mx-1 rounded-0' : 'btn btn-sm btn-outline-secondary mb-3 mx-1'}`"
                                v-for="size in productDetailsStore.product?.sizes"
                                :key="size.id"
                                @click="setChosenSize(size)"
                            >
                                {{ size.name }}
                            </button>
                        </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0 mt-2">${{ product.price }}</span>
                            <div>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-half text-warning"></i>
                                <small class="text-muted">({{ product.reviews.length }})</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between bg-light">
                        <div>
                            <input type="number" 
                                v-model="data.qty" 
                                min="1"
                                :max="product.qty"
                                class="form-control"
                            >
                        </div>
                        <button class="btn btn-danger btn-sm" 
                            @click="handleAddToCart"
                            :disabled="!data.chosenColor || !data.chosenSize || !data.qty "
                        >
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-heart"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">                    
            <ReviewList />
            <AddReview :rating="data.rating" :max-rating="5" :increment="1" />
        </div>
    </div>
</template>

<script setup>
    import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
    import { useCartStore } from '../../stores/useCartStore'
    import { onMounted, computed, watch, reactive, ref, nextTick } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import { useToast } from 'vue-toastification'
    import Spinner from '../common/Spinner.vue'
    import AddReview from '../reviews/AddReview.vue'
    import ReviewList from '../reviews/ReviewList.vue'
    
    const route = useRoute() // to get the slug from the route
    const router = useRouter() // to get back to the products list
    const toast = useToast()

    const productDetailsStore = useProductDetailsStore()
    const cartStore = useCartStore()

    // Use computed to make it reactive
    const product = computed(() => productDetailsStore.getProduct)
    const productImages = computed(() => productDetailsStore.getProductImages)
    const isLoading = computed(() => productDetailsStore.getIsLoading)


    // ✅ Track when images are ready
    const imagesReady = ref(false)

    // Function to fetch product and track when images are ready
    const fetchProduct = async () => {
        imagesReady.value = false
        productDetailsStore.fetchProduct(route.params.slug)
        
        // ✅ Wait for DOM to update
        await nextTick()
        await nextTick() // Double nextTick to ensure VueImageZoomer can access DOM
        imagesReady.value = true
    }

    onMounted(() => {
        fetchProduct() // fetch the product from the API on mount
    })

    // Watches route.params.slug for changes. When the slug changes, it calls fetchProduct() to load the new product.
    watch(() => route.params.slug, async (newSlug) => {
        if (newSlug) {
            await fetchProduct()
        }
    })

    //define the data object
    const data = reactive({
        qty: 1,
        chosenColor: null,
        chosenSize: null
    })

    //set the chosen color by user
    const setChosenColor = (color) => {
        data.chosenColor = color
        console.log('Data', data)
    }

    //set the chosen size by user
    const setChosenSize = (size) => {
        data.chosenSize = size
        console.log('Data', data)
    }

    // Button calls Add to cart handler when clicked
    const handleAddToCart = () => {
        // Check if product is out of stock
        if (!productDetailsStore.product?.status) {
            toast.warning("Product is out of stock")
            return
        }        
        // item to add to the cart
        const item = { // you can do verification here
            // crate unique reference for the item
            reference: `${productDetailsStore.getProduct.id}-${data.chosenColor.id}-${data.chosenSize.id}`,
            product: productDetailsStore.getProduct, // or product.value passing the whole product object
            qty: data.qty, // quantity
            color: data.chosenColor, // color object
            size: data.chosenSize // size object
        }

        // send the item to the cartStore.addToCart(item)
        cartStore.addToCart(item)
    }
    
</script>

<style scoped>

</style>
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
    <div v-else-if="product && product.thumbnail">       
        <div class="row">
            <div class="col-6 mb-3">
            <!-- Thumbnail image -->
            <div class="mb-3 rounded">
                <VueImageZoomer 
                    v-if="product.thumbnail" 
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
                <div class="d-flex flex-wrap justify-content-start">
                    <div class="border border-light-subtle shadow-sm border-2 rounded mb-1 me-1" 
                        v-for="color in product.colors"
                        :key="color.id"
                        @click="setChosenColor(color)"
                        :style="{
                            backgroundColor:color.name,
                            width:'30px',
                            height:'30px',
                            cursor:'pointer'
                        }"
                        >
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <div 
                        v-for="size in product.sizes"
                        :key="size.id"
                        @click="setChosenSize(size)"
                        :style="{
                            width: '35px',
                            height: '35px',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            cursor: 'pointer',
                            fontSize: '14px',
                            fontWeight: 'normal'
                        }">
                        {{ size.name }}
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
                <button class="btn btn-danger btn-sm"><i class="bi bi-cart-plus"></i> Add to Cart</button>
                <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-heart"></i></button>
            </div>
        </div>

        </div>
    </div>
    </div>
</template>

<script setup>
    import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
    import { onMounted, computed, watch, reactive } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import Spinner from '../layouts/Spinner.vue'
    import { VueImageZoomer } from 'vue-image-zoomer'
    import 'vue-image-zoomer/dist/style.css';

    const route = useRoute() // to get the slug from the route
    const router = useRouter() // to get back to the products list

    const productDetailsStore = useProductDetailsStore()

    // ✅ Use computed to make it reactive
    const product = computed(() => productDetailsStore.getProduct)
    const productImages = computed(() => productDetailsStore.getProductImages)
    const isLoading = computed(() => productDetailsStore.getIsLoading)

    // ✅ Function to fetch product
    const fetchProduct = () => {
        productDetailsStore.fetchProduct(route.params.slug) // fetch the product from the API
    }

    // ✅ Fetch on mount
    onMounted(() => {
        fetchProduct()
    })
    
    // ✅ Watch for route changes (when navigating between products)
    watch(() => route.params.slug, (newSlug) => {
        if (newSlug) {
            fetchProduct()
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
        console.log('Chosen color:', data.chosenColor)
    }

    //set the chosen size by user
    const setChosenSize = (size) => {
        data.chosenSize = size
        console.log('Chosen size:', data.chosenSize)
    }
    
</script>

<style scoped>

</style>
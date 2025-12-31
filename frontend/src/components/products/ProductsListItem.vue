<template>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
<div class="col-md-6">
    <div class="card mb-2" style="max-width: 320px">
        <img :src="product.thumbnail" class="card-img-top" alt="Product Image">
        <div class="card-body">
            <h5 class="card-title">{{ product.name }}</h5>
            <p class="card-text" v-dompurify-html="product.description.substring(0,50)"></p>
            <div class="d-flex justify-content-between align-items-center">
                <span class="h5 mb-0">${{ product.price }}</span>
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
            <button class="btn btn-danger btn-sm"><i class="bi bi-cart-plus"></i> Add to Cart</button>
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-heart"></i></button>
        </div>
    </div>
</div>
</template>

<script setup>

// define the props for the component  it is passed from the ProductsList component
const props = defineProps({
    product: {
        type: Object,        // Prop must be an Object
        required: true       // Prop is required (must be passed)
    }
})

console.log(props.product) // log the product to the console

</script>

<style scoped>
.product-list-item {
    border: 1px solid #ccc;
    padding: 10px;
    margin: 10px;
    border-radius: 5px;
}
</style>
<template>
    <!-- Bootstrap Modal -->
    <Teleport to="body">
        <div 
            v-if="show"
            class="modal fade show d-block" 
            tabindex="-1"
            style="z-index: 1055;"
        >
            <div class="modal-backdrop fade show" @click="handleClose"></div>
            <div class="modal-dialog modal-dialog-centered" @click.stop style="z-index: 1056;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add to Cart</h5>
                        <button type="button" class="btn-close" @click="handleClose"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="product" class="d-flex mb-3">
                            <img 
                                :src="product.thumbnail" 
                                :alt="product.name"
                                class="img-thumbnail me-3"
                                style="width: 100px; height: 100px; object-fit: cover;"
                            >
                            <div>
                                <h6>{{ product.name }}</h6>
                                <p class="mb-0 text-muted">${{ product.price }}</p>
                            </div>
                        </div>
                        
                        <!-- Color Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Color</label>
                            <div class="d-flex flex-wrap gap-2">
                                <div 
                                    v-for="color in product?.colors || []"
                                    :key="color.id"
                                    :class="`${data.chosenColor?.id === color.id ? 'border border-primary border-2 shadow-sm' : 'border border-light-subtle border-1'} rounded`"
                                    :style="{
                                        backgroundColor: color.name,
                                        width: '40px',
                                        height: '40px',
                                        cursor: 'pointer'
                                    }"
                                    @click="setChosenColor(color)"
                                    :title="color.name"
                                ></div>
                            </div>
                            <small class="text-danger" v-if="!data.chosenColor && showValidation">Please select a color</small>
                        </div>
                        
                        <!-- Size Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Size</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button 
                                    v-for="size in product?.sizes || []"
                                    :key="size.id"
                                    :class="`${data.chosenSize?.id === size.id ? 'btn btn-primary' : 'btn btn-outline-secondary'} btn-sm`"
                                    @click="setChosenSize(size)"
                                >
                                    {{ size.name }}
                                </button>
                            </div>
                            <small class="text-danger" v-if="!data.chosenSize && showValidation">Please select a size</small>
                        </div>
                        
                        <!-- Quantity Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantity</label>
                            <input 
                                type="number" 
                                v-model.number="data.qty" 
                                min="1"
                                :max="product?.qty || 1"
                                class="form-control"
                                style="max-width: 150px;"
                            >
                            <small class="text-muted">Available: {{ product?.qty || 0 }}</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="handleClose">Cancel</button>
                        <button 
                            type="button" 
                            class="btn btn-primary"
                            @click="handleAddToCart"
                            :disabled="!data.chosenColor || !data.chosenSize || !data.qty || (product && !product.status)"
                        >
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { reactive, watch } from 'vue'
import { useToast } from 'vue-toastification'

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    show: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'add-to-cart'])

const toast = useToast()

const data = reactive({
    qty: 1,
    chosenColor: null,
    chosenSize: null
})

const showValidation = reactive({
    value: false
})

// Reset form when modal opens/closes
watch(() => props.show, (newVal) => {
    if (newVal) {
        // Reset form when modal opens
        data.qty = 1
        data.chosenColor = null
        data.chosenSize = null
        showValidation.value = false
    }
})

// Set the chosen color
const setChosenColor = (color) => {
    data.chosenColor = color
    showValidation.value = false
}

// Set the chosen size
const setChosenSize = (size) => {
    data.chosenSize = size
    showValidation.value = false
}

// Handle close
const handleClose = () => {
    emit('close')
}

// Handle add to cart
const handleAddToCart = () => {
    // Validate
    if (!data.chosenColor || !data.chosenSize || !data.qty) {
        showValidation.value = true
        toast.error('Please select color, size, and quantity')
        return
    }
    
    // Check if product is in stock
    if (!props.product?.status) {
        toast.warning('Product is out of stock')
        return
    }
    
    // Check if quantity exceeds available stock
    if (data.qty > props.product.qty) {
        toast.error(`Maximum available quantity is ${props.product.qty}`)
        return
    }
    
    // Emit add-to-cart event with selected data
    emit('add-to-cart', {
        product: props.product,
        color: data.chosenColor,
        size: data.chosenSize,
        qty: data.qty
    })
}
</script>

<style scoped>
</style>

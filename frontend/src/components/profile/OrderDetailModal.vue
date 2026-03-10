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
            <div class="modal-dialog modal-dialog-centered modal-lg" @click.stop style="z-index: 1056;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order #{{ order?.id }}</h5>
                        <button type="button" class="btn-close" @click="handleClose"></button>
                    </div>
                    <div class="modal-body" v-if="order">
                        <!-- Order Summary -->
                        <div class="card mb-4">
                            <div class="card-header fw-bold">Order Summary</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Total:</strong> ${{ order.total }}</p>
                                        <p class="mb-1"><strong>Order Date:</strong> {{ order.created_at }}</p>
                                        <p class="mb-1">
                                            <strong>Delivered:</strong>
                                            <span v-if="order.deliverd_at">{{ order.deliverd_at }}</span>
                                            <span v-else class="text-muted">Pending...</span>
                                        </p>
                                    </div>
                                    <div class="col-md-6" v-if="order.coupon">
                                        <p class="mb-1"><strong>Coupon:</strong> {{ order.coupon.name }} ({{ order.coupon.discount }}% off)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Cards -->
                        <h6 class="fw-bold mb-3">Products</h6>
                        <div class="row g-3">
                            <div
                                v-for="product in order.products"
                                :key="product.id"
                                class="col-12"
                            >
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <img
                                                    :src="product.thumbnail || 'https://via.placeholder.com/150'"
                                                    :alt="product.name"
                                                    class="img-thumbnail"
                                                    style="object-fit: cover; width: 100%; height: 150px;"
                                                >
                                            </div>
                                            <div class="col-md-9">
                                                <h6 class="card-title">{{ product.name }}</h6>
                                                <p class="card-text text-muted small" v-if="product.description">
                                                    {{ product.description }}
                                                </p>
                                                <div class="mt-2">
                                                    <span class="fw-bold">${{ product.price }}</span>
                                                    <span class="ms-3">Qty: {{ order.qty }}</span>
                                                    <span
                                                        v-if="getSizeName(product.pivot?.size_id, product.sizes)"
                                                        class="ms-3 bg-light px-2 py-1 rounded"
                                                    >
                                                        Size: {{ getSizeName(product.pivot.size_id, product.sizes) }}
                                                    </span>
                                                    <span v-if="product.pivot?.color_id" class="ms-2 d-inline-block align-middle">
                                                        <span class="me-1">Color:</span>
                                                        <span
                                                            class="border border-secondary rounded d-inline-block"
                                                            :style="{
                                                                backgroundColor: getColorName(product.pivot.color_id, product.colors),
                                                                width: '20px',
                                                                height: '20px',
                                                                verticalAlign: 'middle'
                                                            }"
                                                            :title="getColorName(product.pivot.color_id, product.colors)"
                                                        ></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    order: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['close'])

const getColorName = (colorId, colors) => {
    if (!colorId || !colors || !Array.isArray(colors)) return '#ccc'
    const color = colors.find(c => c.id === colorId)
    return color ? color.name : '#ccc'
}

const getSizeName = (sizeId, sizes) => {
    if (!sizeId || !sizes || !Array.isArray(sizes)) return null
    const size = sizes.find(s => s.id === sizeId)
    return size ? size.name : null
}

const handleClose = () => {
    emit('close')
}
</script>

<style scoped>
</style>

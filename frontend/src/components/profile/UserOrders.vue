<template>
    <div class="row my-5">
        <ProfileSidebar />
        <div class="col-md-8">
            <div class="card-body" v-if="orders.length">
                <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr class="align-middle">
                            <th>#</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Total</th>
                            <th>Order Date</th>
                            <th>Delivered at</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(order,index) in orders.slice(0,data.ordersToShow)"
                            :key="order.id"
                        >
                            <td class="align-middle">{{ index + 1 }}</td>
                            <td class="align-middle">
                                <div class="d-flex flex-column gap-1 justify-content-center">
                                    <div
                                        v-for="product in order.products"
                                        :key="product.id"
                                    >
                                        <router-link
                                            :to="{ name: 'product', params: { slug: product.slug } }"
                                            class="link-primary text-decoration-none"
                                        >
                                            {{ product.name }}
                                        </router-link>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex flex-column gap-1 justify-content-center">
                                    <span
                                        v-for="product in order.products"
                                        :key="product.id"
                                        class="text-body"
                                    >
                                        ${{ product.price }}
                                    </span>
                                </div>
                            </td>
                            <td class="align-middle">{{ order.qty }}</td>
                            <td class="align-middle">
                                <div class="d-flex flex-column gap-1 justify-content-center align-items-start">
                                    <div 
                                        v-for="product in order.products"
                                        :key="product.id"
                                    >
                                        <div 
                                            v-if="product.pivot?.color_id"
                                            class="order-swatch border border-secondary-subtle rounded"
                                            :style="{
                                                backgroundColor: getColorName(product.pivot.color_id, product.colors),
                                            }"
                                            :title="getColorName(product.pivot.color_id, product.colors)"
                                        ></div>
                                        <div 
                                            v-else 
                                            class="order-swatch d-inline-flex align-items-center justify-content-center text-muted border border-secondary-subtle rounded"
                                        >-</div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex flex-column gap-1 justify-content-center align-items-start">
                                    <span 
                                        v-for="product in order.products"
                                        :key="product.id"
                                        class="order-swatch d-inline-flex align-items-center justify-content-center bg-light text-dark fw-bold rounded border border-secondary-subtle"
                                        style="font-size: 0.875rem;"
                                    >
                                        {{ getSizeName(product.pivot?.size_id, product.sizes) || '-' }}
                                    </span>
                                </div>
                            </td>
                            <td class="align-middle">${{ order.total }}</td>
                            <td class="align-middle">{{ order.created_at }}</td>
                            <td class="align-middle">
                                <span class="badge bg-success rounded-0"
                                    v-if="order.deliverd_at"
                                >
                                    {{ order.deliverd_at }}
                                </span>
                                <i v-else class="text-muted">Pending...</i>
                            </td>
                            <td class="align-middle">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    @click="viewOrder(order.id)"
                                    :disabled="data.isLoadingOrder"
                                >
                                    View
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
            <Alert v-else-if="!data.isLoading" content="No orders yet!" bgColor="primary" />
            <div class="d-flex justify-content-center" v-if="data.isLoading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <button type="button" class="btn btn-sm btn-dark mt-3"
                    v-if="data.ordersToShow < orders.length"
                    @click="loadMoreOrders"
                >
                    <i class="bi bi-arrow-clockwise"></i> Load more
                </button>
            </div>
        </div>
        <OrderDetailModal
            :show="showOrderModal"
            :order="selectedOrder"
            @close="closeOrderModal"
        />
    </div>
</template>

<script setup>
    import ProfileSidebar from "./ProfileSidebar.vue"
    import OrderDetailModal from "./OrderDetailModal.vue"
    import Alert from "../layouts/Alert.vue"
    import { ref, reactive, onMounted } from "vue"
    import axios from "axios"
    import { useToast } from "vue-toastification"

    const toast = useToast()

    const orders = ref([])
    const showOrderModal = ref(false)
    const selectedOrder = ref(null)

    const data = reactive({
        ordersToShow: 4,
        isLoading: false,
        isLoadingOrder: false
    })

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

    const loadMoreOrders = () => {
        if (data.ordersToShow < orders.value.length) {
            data.ordersToShow = data.ordersToShow + 4
        }
    }

    const viewOrder = async (orderId) => {
        data.isLoadingOrder = true
        try {
            const response = await axios.get(`/orders/${orderId}`)
            selectedOrder.value = response.data.data?.order ?? null
            showOrderModal.value = true
        } catch (error) {
            console.error('Error fetching order:', error)
            toast.error('Failed to load order details')
        } finally {
            data.isLoadingOrder = false
        }
    }

    const closeOrderModal = () => {
        showOrderModal.value = false
        selectedOrder.value = null
    }

    onMounted(async () => {
        data.isLoading = true
        try {
            const response = await axios.get('/orders')
            orders.value = response.data.data?.orders ?? []
        } catch (error) {
            console.error('Error fetching orders:', error)
            orders.value = []
        } finally {
            data.isLoading = false
        }
    })
</script>

<style scoped>
/* Same footprint as size chip: keeps color swatch and size box aligned */
.order-swatch {
    width: 2.25rem;
    height: 2.25rem;
    flex-shrink: 0;
    box-sizing: border-box;
}
</style>